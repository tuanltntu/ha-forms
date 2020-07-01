(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
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
	$(document).ready(function(){
		function form_valid(e){
			var chk = 1;
			$('.haf-message').html('');
			e.each(function(i, _e){
				if($(_e).hasClass('haf-required')){
					if($(_e).attr('type') == 'radio' || $(_e).attr('type') == 'checkbox'){
						if(!$(e).is(':checked')){
							$(_e).closest('.haf-field').addClass('haf-error');
						}else{
							$(_e).closest('.haf-field').removeClass('haf-error');
						}
					}else{
						if(!$(_e).val()){
							$(_e).parent().addClass('haf-error');
							chk = 0;
						}else{
							$(_e).parent().removeClass('haf-error');
						}
					}
				}
			});
			return chk ? true : false;
		}
		$('.haf-input').on('blur', function(){
			$(this).parent().removeClass('haf-error');
		});
		
		$('.haf-input-radio, .haf-input-radio').on('click', function(){
			$(this).removeClass('haf-error');
		});
		
		$('.haf-input').on('keypress', function (e) {
			var key = e.which || e.keyCode;
			if (key === 13) {
				$(this).closest('.haf-form').find('.haf-button').trigger('click');
			}
		});
		
		$('.haf-form').on('click', '.haf-button', function(){
			var that = $(this);
			var _form = that.closest('.haf-form');
			that.attr('disabled', true);
			_form.find('.haf-loader').show();
			var params = {};
			var items = _form.find('[name*=haf_]');
			
			if(!form_valid(items)){
				_form.find('.haf-loader').hide();
				that.attr('disabled', false);
				return;
			}
			
			var len = items.length - 1;
			params.data = {};
			
			items.each(function(i, e){
				params.data[$(e).attr('name')] = $(this).val();
				if(i == len){
					params['haf_form_id'] = that.find('[name=haf_form_id]').val();
					params['haf_form_name'] = that.find('[name=haf_form_name]').val();
					params['haf_url'] = that.find('[name=haf_url]').val();
					params['haf_nonce'] = that.find('[name=haf_nonce]').val();
					$.ajax({
						type : "post",
						url: HA.ajax_url,
						data : {
							action: HA.form_submit,
							params: params
						},
						success: function(response){
							_form.find('.haf-loader').hide();
							if(response.success){
								_form.find('.haf-field [name*=haf_]').val('');
								if(response.data.redirect_url){
									location.href = response.data.redirect_url;
								}
								if(response.data.message){
									_form.find('.haf-success').html('<div>' + response.data.message + '</div>');
								}
								_form.find('.haf-field').remove();
							}else{
								if(response.data.message){
									_form.find('.haf-error').html('<div>' + response.data.message + '</div>');
								}
							}
						}
					});
				}
			});
			setTimeout(function(){
				that.attr('disabled', false);
			}, 3000);
		});
	});
})( jQuery );
