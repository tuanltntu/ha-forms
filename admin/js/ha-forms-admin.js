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
					previewVisible: false,
					previewImage: '',
					isMediaLoading: false,
					modalField: false,
					modalFieldError: '',
					field: {type: 'text'},
					params: {
						title: '',
						config: {
							sub_title: '',
							button: 'Submit',
							hide_label: 0,
							hide_title: 0,
							fields: {
								name: {name: 'Name', hidden: 0, required: 1},
								phone: {name: 'Phone', hidden: 0, required: 1},
								email: {name: 'Email', hidden: 0, required: 1},
							},
							css: '',
							thank_you_page: '',
						},
						fields: [],
						integrate: {},
						html: '',
					},
					fieldList: []
				},
				methods: {
					save(){
						var that = this;
						that.request(that.HA.api.form_save, {data: that.params}, function(response){
							if(response.success && response.data.item){
								that.params = response.data.item;
								that.HA.title = response.data.title;
								that.render();
							}
						}, 'POST');
					},
					addField(){
						if(this.field.name && this.field.type){
							if((this.field.type == 'radio' || this.field.type == 'select') && !this.field.options){
								this.modalFieldError = this.HA.translator.field_error;
								return;
							}
							this.field.name = this.field.name.trim();
							this.params.fields.push({
								key: this.convert(this.field.name),
								name: this.field.name,
								type: this.field.type,
								required: this.field.required,
								options: this.field.options ? this.field.options : '',
								default_value: this.field.default_value ? this.field.default_value : '',
							});
							this.field = {type: 'text'};
							this.modalField = false;
							this.render();
						}else{
							this.modalFieldError = this.HA.translator.field_required;
						}
					},
					remove(item){
						this.params.fields = this.params.fields.filter( el => el.key !== item.key );
						this.render();
					},
					render(){
						const that = this;
						var html = '';
						if(this.params.config.css){
							html += '<style>'+ this.params.config.css +'</style>';
						}
						html += '<div class="haf-form">';
						
						if(!this.params.config.hide_title){
							if(this.params.title)
								html += '<h3 class="haf-title center">'+ this.params.title +'</h3>';
							if(this.params.config.sub_title)
								html += '<p class="haf-sub-title center">'+ this.params.config.sub_title +'</p>';
						}
						
						html += '<div class="haf-message haf-error"></div>';
						html += '<div class="haf-message haf-success"></div>';
						html += '<div class="haf-loader"></div>';
						
						for(var x in this.params.config.fields){
							var e = this.params.config.fields[x];
							if(!e.hidden){
								var _required = e.required ? ' *' : '';
								var _required_class = e.required ? ' haf-required' : '';
								html += '<div class="haf-field haf-input-text">';
								html += !that.params.config.hide_label ? '<label for="haf-'+ x +'-field">'+ e.name + _required +'</label>' : '';
								html += '<input type="'+ (x == 'phone' ? 'number' : 'text') +'" class="haf-input'+ _required_class +'" ' + (that.params.config.hide_label ? 'placeholder="'+ e.name + _required +'"' : '') +' name="ha_'+ x +'">';
								html += '</div>';
							}
						}
						
						this.params.fields.forEach(function(e){
							e.key = that.convert(e.key);
							
							var required = e.required ? '*' : '';
							var required_class = e.required ? ' haf-required' : '';
							
							html += '<div class="haf-field haf-input-'+ e.type +'">';
							
							if((!that.params.config.hide_label && e.type != 'hidden') || e.type == 'radio')
								html += '<label for="haf-'+ e.key +'-field">'+ e.name + required +'</label>';
							
							switch(e.type){
								case 'text':
									html += '<input type="text" value="'+ e.default_value +'" class="haf-input'+ required_class +'" ' + (that.params.config.hide_label ? 'placeholder="'+ e.name + required +'"' : '') +' name="ha_'+ e.key +'">';
									break;
								case 'textarea':
									html += '<textarea rows="3" class="haf-input'+ required_class +'" ' + (that.params.config.hide_label ? 'placeholder="'+ e.name + required +'"' : '') +' name="ha_'+ e.key +'">'+ e.default_value +'</textarea>';
									break;
								case 'radio':
									if(e.options){
										var options = e.options.split('\n');
										for(var i=0,len = options.length; i<len; i++){
											var parts = options[i].split(':');
											if(parts.length > 1){
												var name = parts[1].trim();
												var value = parts[0].trim();
												if(name && value)
													html += '<label class="haf-radio-container"><input class="haf-input'+ required_class +'" type="radio" value="'+ value +'" name=ha_'+ e.key +'  '+ (e.default_value == value ? 'checked' : '') +'>'+ name +'</label>';
											}
										}
									}
									break;
								case 'select':
									if(e.options){
										html += '<select class="haf-input'+ required_class +'" name="ha_'+ e.key +'">';
										var options = e.options.split('\n');
										for(var i=0,len = options.length; i<len; i++){
											var parts = options[i].split(':');
											if(parts.length > 1){
												var name = parts[1].trim();
												var value = parts[0].trim();
												if(name && value)
													html += '<option value="'+ value +'" '+ (e.default_value == value ? 'selected' : '') +'>'+ name +'</option>';
											}
										}
										html += '</select>';
									}
									break;
								case 'hidden':
									html += '<input type="hidden" value="'+ e.default_value +'" class="haf-input'+ required_class +'" ' + (that.params.config.hide_label ? 'placeholder="'+ e.name + required +'"' : '') +' name="ha_'+ e.key +'">';
									break;	
								default: break;	
							}
							
							html += '</div>';
						});
						
						if(this.params.config.button)
							html += '<div class="haf-field haf-submit center"><button class="haf-button">'+ this.params.config.button +'</button></div>';
						
						html += '</div>';
						this.params.html = html;
					},
                    getFieldList(){
						this.fieldList = [];
						if(!this.params.config.fields.name.hidden) this.fieldList.push('name');
						if(!this.params.config.fields.email.hidden) this.fieldList.push('email');
						if(!this.params.config.fields.phone.hidden) this.fieldList.push('phone');
						for(var i = 0, len = this.params.fields.length; i<len; i++){
                            this.fieldList.push(this.params.fields[i].key);
						}
                    }
				},
				mounted (){
					if(this.HA.data.item){
						this.params = this.HA.data.item;
					}
					this.render();
					this.getFieldList();
				}
			});
		}
	});

})( jQuery );
