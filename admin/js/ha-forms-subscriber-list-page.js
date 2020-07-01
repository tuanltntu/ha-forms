(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	document.addEventListener('DOMContentLoaded', function() {
		if(document.getElementById('ha-core')){		
			new Vue({
				el: '#ha-core',
                mixins: [HA_Mixin],
				data: {
					cols: ['name', 'email', 'phone','form', 'status'],
					columns: [{
						title: HA.translator.columns.name,
						dataIndex: 'name',
						scopedSlots: { customRender: 'name' },
					},{
						title: HA.translator.columns.email,
						dataIndex: 'email',
						scopedSlots: { customRender: 'email' },
					},{
						title: HA.translator.columns.phone,
						dataIndex: 'phone',
						scopedSlots: { customRender: 'phone' },
					},{
						title: HA.translator.columns.form,
						dataIndex: 'form',
						scopedSlots: { customRender: 'form' },
					},{
						title: HA.translator.columns.status,
						dataIndex: 'status',
						scopedSlots: { customRender: 'status' },
					},{
						title: HA.translator.columns.operation,
						dataIndex: 'operation',
						width: '130px',
						scopedSlots: { customRender: 'operation' },
					}],
					query: '',
					dataList: {
						items: [],
						pages: 0,
						total: 0
					},
					pageSize: 20,
					itemView: {},
					modalView: false,
					modalLoading: false,
				},
				methods: {
					remove(item){
						var that = this;
						that.request(that.HA.api.subscriber_remove, {data: item.key}, function(response){
							if(response.success){
								that.dataList.items = that.dataList.items.filter( el => el.key !== item.key ); 
							}
						}, 'POST');
					},
					view(item){
						this.itemView = item;
						this.modalView = true;
					},
					edit(item){
						this.modalLoading = true;
						var that = this;
						that.request(that.HA.api.subscriber_save, {data: item}, function(response){
							that.modalLoading = false;
							that.modalView = false;
							if(response.success && response.data){
								that.itemView = response.data; 
							}
						}, 'POST');
					},
					find(){
						var that = this;
						if(that.query){
							that.request(that.HA.api.subscriber_find, {data: {data: that.query, size: that.pageSize}}, function(response){
								if(response.success && response.data){
									that.dataList = response.data;
								}
							}, 'POST');
						}
					},
					paginate(page, pageSize){
						var that = this;
						that.request(that.HA.api.subscriber_list, {data: {page: page, size: pageSize}}, function(response){
							if(response.success && response.data){
								that.dataList = response.data;
							}
						}, 'POST');
					},
					closeModal(){ this.modalView = false; },
				},
				mounted (){
					this.paginate(1, this.pageSize);
					
				}
			});
		}
	});
})( jQuery );
