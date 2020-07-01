<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://tuanltntu.com
 * @since      1.0.0
 *
 * @package    Ha_Forms
 * @subpackage Ha_Forms/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ha_Forms
 * @subpackage Ha_Forms/admin
 * @author     Jack Le <https://tuanltntu.com>
 */
class Ha_Forms_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	
	public $table_forms = 'ha_forms';
	public $table_users = 'ha_form_users';
	public $subscriber_status = [];	
	 
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->subscriber_status = [
			'pending'	=> __('Pending', $this->plugin_name),
			'process'	=> __('Process', $this->plugin_name),
			'done'		=> __('Done', $this->plugin_name),
		];
		add_shortcode('haf-form', [$this, 'form_shortcode']);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ha_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ha_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ha-forms-admin.css', array('ha-core'), $this->version, 'all' );
		wp_register_style( $this->plugin_name . '-public', plugin_dir_url( dirname(__FILE__) ) . 'public/css/ha-forms-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ha_Forms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ha_Forms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ha-forms-admin.js', array('ha-core'), $this->version, false );
		wp_register_script( $this->plugin_name . '-list', plugin_dir_url( __FILE__ ) . 'js/ha-forms-list-page.js', array('ha-core'), $this->version, false );
		wp_register_script( $this->plugin_name . '-subscribers', plugin_dir_url( __FILE__ ) . 'js/ha-forms-subscriber-list-page.js', array('ha-core'), $this->version, false );
	}

	public function admin_menu(){
		add_submenu_page(HA_MENU, __('Forms', $this->plugin_name), __('Forms', $this->plugin_name),'manage_options', $this->plugin_name, [$this, 'form_page']);
		add_submenu_page(HA_MENU, __('All', $this->plugin_name), __('All', $this->plugin_name),'manage_options', $this->plugin_name . '-list', [$this, 'form_list_page']);
		add_submenu_page(HA_MENU, __('Subscribers', $this->plugin_name), __('Subscribers', $this->plugin_name),'manage_options', $this->plugin_name . '-subscribers', [$this, 'subscriber_list_page']);
	}
	
	public function add_menu($menu){
		$menu[] = [
			'key'		=> 'form',
			'title'		=> __('Forms', $this->plugin_name),
			'icon'		=> 'profile',
			'order'		=> 15,
			'children'	=> [
				[
					'key'	=> $this->plugin_name,
					'title'	=> __('New form', $this->plugin_name),
				],
				[
					'key'	=> $this->plugin_name . '-list',
					'title'	=> __('All forms', $this->plugin_name),
				],
				[
					'key'	=> $this->plugin_name . '-subscribers',
					'title'	=> __('Subscribers', $this->plugin_name),
				],
			]
		];
		
		return $menu;
	}
	
	/* Forms */
	public function form_page(){
		$item = '';
		if(isset($_REQUEST['pid']) && $pid = intval($_REQUEST['pid'])){
			global $wpdb;
			$table = $wpdb->prefix . $this->table_forms;
			if( $wpdb->get_var("SHOW TABLES LIKE '" . $table ."'") == $table ){
				$item = $wpdb->get_row( "SELECT * FROM $table WHERE id = $pid", ARRAY_A);
				if($item){
					$item = $this->form_data($item);
				}
			}
		}
		
		$listPages = [];
		$pages = get_pages( array( 'sort_column' => 'post_date', 'sort_order' => 'desc' ) );
		foreach( $pages as $page ) {		
			$listPages[] = [
				'id'	=> $page->ID,
				'title'	=> $page->post_title,
			];
		}
		
		add_action('ha-core-header-controls', function(){
			echo '<a-button class="ha-btn" icon="save" @click="save" type="primary" :loading="isLoading">
						<span v-if="params.id">
							'. __('Save changes', $this->plugin_name) .'
						</span>
						<span v-else>
							'. __('Add new', $this->plugin_name) .'
						</span>
					</a-button>';
		});
		
		$localize = [
			'nonce'			=> wp_create_nonce($this->plugin_name),
			'menu_items'	=> Ha_Helpers::get_menu_items(),
			'currentPage'	=> $item ? [] : [$this->plugin_name],
			'currentOpen'	=> ['form'],
			'menu_url'		=> get_admin_url() . 'admin.php?page=',
			'is_admin'		=> is_super_admin(),
			'id'			=> $item ? $item['id'] : '',
			'title' 		=> $item ? __('Edit form', $this->plugin_name) : __('New form', $this->plugin_name),
			'logo'			=> HA_LOGO,
			'api'	=> [
				'form_save'	=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'form_save',
			],
			'data'	=> [
				'item' 	=> $item,
				'pages'	=> $listPages
			],
            'translator' => [
                'field_error'   => __('Please fill values for this field', $this->plugin_name),
                'field_required'   => __('Please fill all field requires', $this->plugin_name),
            ]
		];
		
		wp_enqueue_style($this->plugin_name);
		wp_enqueue_style( $this->plugin_name . '-public');
		wp_enqueue_script($this->plugin_name);
		wp_localize_script($this->plugin_name, 'HA', $localize);
		
		include 'partials/ha-forms-admin-display.php';
	}
	
	public function form_save(){
		$data = Ha_Helpers::verify_nonce($this->plugin_name);
		
		if(isset($data['html'])) unset($data['html']);
		
		$requires = [
			'title'				=> __('Title', $this->plugin_name),
		];
		$errors = [];
		$input = Ha_Helpers::clean($data, $requires, $errors);
		
		$input = apply_filters(HA_CORE . 'form_save_input', $input, $data);
		
		$optionFields = [];
		foreach($data['fields'] as $k=>$_item){
			if($_item['type'] == 'radio' || $_item['type'] == 'select'){
				$optionFields[$k] = sanitize_textarea_field($data['fields'][$k]['options']);
			}
		}
		
		foreach($input['fields'] as $k=>$_item){
			if($_item['type'] == 'radio' || $_item['type'] == 'select'){
				$input['fields'][$k]['options'] = $optionFields[$k];
			}
		}
		
		global $wpdb;
		
		if($input){
			$table_forms = $wpdb->prefix . $this->table_forms;
			$table_users = $wpdb->prefix . $this->table_users;
			if( $wpdb->get_var("SHOW TABLES LIKE '" . $table_forms ."'") != $table_forms ){
				
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				
				$sql = "CREATE TABLE IF NOT EXISTS `" . $table_forms . "` (
							`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
							`title` varchar(255) DEFAULT NULL,
							`config` longtext DEFAULT NULL,
							`fields` longtext DEFAULT NULL,
							`integrate` longtext DEFAULT NULL,
							`updated_at` TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
                            `created_at` TIMESTAMP NOT NULL DEFAULT NOW(),
							PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
				dbDelta($sql);		
				$sql = "CREATE TABLE IF NOT EXISTS `" . $table_users . "` (
							`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
							`form_id` bigint(20) NOT NULL,
							`name` varchar(255) DEFAULT NULL,
							`phone` varchar(255) DEFAULT NULL,
							`email` varchar(255) DEFAULT NULL,
							`options` longtext DEFAULT NULL,
							`note` longtext DEFAULT NULL,
							`status` varchar(255) DEFAULT NULL,
							`updated_at` TIMESTAMP NOT NULL DEFAULT NOW() ON UPDATE NOW(),
                            `created_at` TIMESTAMP NOT NULL DEFAULT NOW(),
							PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";		
				dbDelta($sql);
			}
			
			if(!isset($input['id'])){
				$done = $wpdb->insert( 
					$table_forms,
					array(
						'title' 	=> $input['title'],
						'config' 	=> json_encode($input['config'], JSON_UNESCAPED_UNICODE ),
						'fields' 	=> json_encode($input['fields'], JSON_UNESCAPED_UNICODE ),
						'integrate' => json_encode($input['integrate'], JSON_UNESCAPED_UNICODE ),
					),
					array('%s', '%s', '%s', '%s')
				);
				if(false !== $done) $input['id'] = $wpdb->insert_id;
			}else{
				$done = $wpdb->update( 
					$table_forms,
					array(
						'title' 	=> $input['title'],
						'config' 	=> json_encode($input['config'], JSON_UNESCAPED_UNICODE ),
						'fields' 	=> json_encode($input['fields'], JSON_UNESCAPED_UNICODE ),
						'integrate' => json_encode($input['integrate'], JSON_UNESCAPED_UNICODE ),
					),
					array( 'id' => $input['id']),
					array('%s', '%s', '%s', '%s')
				);
			}
			
			if(false !== $done){
				wp_send_json_success([
					'message' 	=> isset($input['id']) ? __('Updated', $this->plugin_name) : __('Success', $this->plugin_name),
					'item' 		=> $this->form_data($input, 1),
					'title'		=> __('Edit form', $this->plugin_name),
				]);
			}
			
			wp_send_json_error(['message'	=> __('Error!!!. Please try again', $this->plugin_name)]);
		}
	}
	
	public function form_data($form, $format = false){
		if(!$format){
			$form['config'] = json_decode($form['config'], 1);
			$form['fields'] = json_decode($form['fields'], 1);
			$form['integrate'] = json_decode($form['integrate'], 1);
		}
		$form['config']['thank_you_page'] = intval($form['config']['thank_you_page']);
		$form['config']['hide_label'] = (boolean) $form['config']['hide_label'];
		$form['config']['hide_title'] = (boolean) $form['config']['hide_title'];
		foreach($form['fields'] as $k=>$item){
		    if(isset($form['fields'][$k]['required']))
			    $form['fields'][$k]['required'] = (boolean) $form['fields'][$k]['required'];
		}
		foreach($form['config']['fields'] as $k=>$item){
			$form['config']['fields'][$k]['required'] = (boolean) $form['config']['fields'][$k]['required'];
			$form['config']['fields'][$k]['hidden'] = (boolean) $form['config']['fields'][$k]['hidden'];
		}

		return apply_filters(HA_CORE . 'form_data', $form);
	}
	
	public function form_items($params){
		global $wpdb;
		$results = [
			'items'	=> [],
			'pages'	=> 0,
			'total'	=> 0,
		];
		
		$size = (isset($params['size']) && $params['size']) ? intval($params['size']) : 20;
		$size = $size > 50 ? 50 : $size;
		$paged = (isset($params['page']) && $params['page']) ? intval($params['page']) : 1;
		
		$table = $wpdb->prefix . $this->table_forms;
		$offset = ($paged - 1)*$size;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$table}";
		if(isset($params['query']) && $params['query']){
			$sql .= " WHERE title like '%{$params['query']}%'";
		}
		$sql .=	" LIMIT ".$offset.", ".$size."; ";   

		$results = $wpdb->get_results($sql, ARRAY_A);
		if($results){
			
			foreach($results as $item){
				$item = $this->form_data($item);
				$thank_you_page = $item['config']['thank_you_page'] ? get_permalink($item['config']['thank_you_page']) : '';
				$results['items'][] = [
					'key'				=> $item['id'],
					'title'				=> $item['title'],
					'thank_you_page'	=> $thank_you_page ? $thank_you_page : '',
					'shortcode'			=> '[haf-form id="'. $item['id'] .'"]',
					'html'				=> $this->form_render($item)
				];
			}
			$results['total'] = intval($wpdb->get_var( "SELECT FOUND_ROWS();" ));
			$results['pages'] = ceil($results['total'] / $size);
		}
		
		return $results;
	}
	
	public function form_list_page(){
		
		$localize = [
			'nonce'			=> wp_create_nonce($this->plugin_name),
			'menu_items'	=> Ha_Helpers::get_menu_items(),
			'currentPage'	=> [$this->plugin_name . '-list'],
			'currentOpen'	=> ['form'],
			'menu_url'		=> get_admin_url() . 'admin.php?page=',
			'title' 		=> __('All forms'),
			'logo'			=> HA_LOGO,
			'link_edit'		=> get_admin_url() . 'admin.php?page=' . $this->plugin_name . '&pid=',
			'api'	=> [
				'form_remove'	=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'form_remove',
				'form_find'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'form_find',
				'form_list'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'form_list',
			],
            'translator' => [
                'columns'    => [
                    'title'             => __('Title', $this->plugin_name),
                    'thank_you_page'    => __('Thank you page', $this->plugin_name),
                    'shortcode'         => __('Shortcode', $this->plugin_name),
                    'operation'         => __('Actions', $this->plugin_name),
                ],
                'search_placeholder'    => __('Form name', $this->plugin_name),
                'search_button'         => __('Search', $this->plugin_name),
                'popconfirm'            => __('Are you sure ?', $this->plugin_name),
                'ok_text'               => __('OK', $this->plugin_name),
                'cancel_text'           => __('Cancel', $this->plugin_name),
                'delete'                => __('Delete', $this->plugin_name),
                'view'                  => __('View', $this->plugin_name),
                'edit'                  => __('Edit', $this->plugin_name),
                'modal_title'           => __('Form preview', $this->plugin_name),
            ]
		];
		
		wp_enqueue_style($this->plugin_name);
		wp_enqueue_style($this->plugin_name . '-public');
		wp_enqueue_script($this->plugin_name . '-list');
		wp_localize_script($this->plugin_name . '-list', 'HA', $localize);
		
		include 'partials/ha-forms-list-page.php';
	}
	
	public function form_list(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		if(isset($input['page']) && isset($input['size'])){
			
			$size = $input['size'] ? intval($input['size']) : 20;
			$size = $size > 50 ? 50 : $size;
			$paged = (isset($input['page']) && $input['page']) ? intval($input['page']) : 1;
			
			$results = $this->form_items([
				'size'	=> $size,
				'page'	=> $paged,
			]);
			
			wp_send_json_success($results);
		}
		wp_send_json_error(['message'	=> __('Error!!!. Please try again', $this->plugin_name)]);
	}
	
	public function form_find(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		$query = sanitize_text_field($input['data']);
		$size = (isset($input['size']) && $input['size']) ? intval($input['size']) : 20;
		
		if($query){
			$results = $this->form_items([
				'size'	=> $size,
				'query'	=> $query,
			]);
			wp_send_json_success($results);
		}
		
		wp_send_json_error(['message'	=> __('Error!!!. Please try again', $this->plugin_name)]);
	}
	
	public function form_remove(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		
		$form_id = apply_filters($this->plugin_name . 'prevent_form_remove', intval($input));
				
		if(!$form_id){
			wp_send_json_error([
				'message'	=> __("This form can't remove", $this->plugin_name),
			]);
		}
		global $wpdb;	
		$table = $wpdb->prefix . $this->table_forms;
		$results = $wpdb->delete($table, array( 'id' => $form_id));
		if(false !== $results){
			wp_send_json_success([
				'message'	=> __('Deleted', $this->plugin_name),
			]);
		}
		wp_send_json_error([
			'message'	=> __('Error', $this->plugin_name),
		]);	
	}
	
	public function form_render($params){
		$html = '';
		if($params){
			global $wpdb, $wp;
			
			if($params['config']['css']){
				$html .= '<style>'. $params['config']['css'] .'</style>';
			}
			
			$html .= '<div class="haf-form">';
			
			if(!$params['config']['hide_title']){
				if($params['title'])
					$html .= '<h3 class="haf-title center">'. $params['title'] .'</h3>';
				if($params['config']['sub_title'])
					$html .= '<p class="haf-sub-title center">'. $params['config']['sub_title'] .'</p>';
			}
			
			$html .= '<div class="haf-message haf-error"></div>';
			$html .= '<div class="haf-message haf-success"></div>';
			$html .= '<div class="haf-loader"></div>';
			
			foreach($params['config']['fields'] as $x=>$e){
				if(!$e['hidden']){
					$_required = $e['required'] ? ' *' : '';
					$_required_class = $e['required'] ? ' haf-required' : '';
					$html .= '<div class="haf-field haf-input-text">';
					$html .= !$params['config']['hide_label'] ? '<label for="haf-'. $x .'-field">'. $e['name'] . $_required .'</label>' : '';
					$html .= '<input type="'. ($x == 'phone' ? 'number' : 'text') .'" class="haf-input'. $_required_class .'" ' . ($params['config']['hide_label'] ? 'placeholder="'. $e['name'] . $_required . '"' : '') . ' name="haf_'. $x . '">';
					$html .= '</div>';
				}
			}
			
			foreach($params['fields'] as $e){
				
				$required = (isset($e['required']) && $e['required']) ? '*' : '';
				$required_class = (isset($e['required']) && $e['required']) ? ' haf-required' : '';
				
				$html .= '<div class="haf-field haf-input-'. $e['type'] .'">';
				
				if((!$params['config']['hide_label'] && $e['type'] != 'hidden') || $e['type'] == 'radio')
					$html .= '<label for="haf-'. $e['key'] .'-field">'. $e['name'] . $required .'</label>';
				
				switch($e['type']){
					case 'text':
						$html .= '<input type="text" value="'. $e['default_value'] .'" class="haf-input'. $required_class .'" ' . ($params['config']['hide_label'] ? 'placeholder="'. $e['name'] . $required .'"' : '') .' name="haf_'. $e['key'] .'">';
						break;
					case 'textarea':
						$html .= '<textarea rows="3" class="haf-input'. $required_class .'" ' . ($params['config']['hide_label'] ? 'placeholder="'. $e['name'] . $required .'"' : '') .' name="haf_'. $e['key'] .'">'. $e['default_value'] .'</textarea>';
						break;
					case 'radio':
						if($e['options']){
                            $options = preg_split('/\n|\r\n?/', $e['options']);
							for($i=0,$len = count($options); $i<$len; $i++){
								$parts = explode(':', $options[$i]);
								if(count($parts) > 1 && $parts[1] && $parts[0]){
									$name = trim($parts[1]);
									$value = trim($parts[0]);
									$html .= '<label class="haf-radio-container"><input class="haf-input'. $required_class .'" type="radio" value="'. $value .'" name=haf_'. $e['key'] .' '. ($e['default_value'] == $value ? 'checked' : ''). '>'. $name .'</label>';
								}
							}
						}
						break;
					case 'select':
						if($e['options']){
							$html .= '<select class="haf-input'. $required_class .'" name="haf_'. $e['key'] .'">';
							$options = preg_split('/\n|\r\n?/', $e['options']);
							for($i=0,$len = count($options); $i<$len; $i++){
								$parts = explode(':', $options[$i]);
								if(count($parts) > 1 && $parts[1] && $parts[0]){
									$name = trim($parts[1]);
									$value = trim($parts[0]);
									$html .= '<option value="'. $value .'" '. ($e['default_value'] == $value ? 'selected' : ''). '>'. $name .'</option>';
								}
							}
							$html .= '</select>';
						}
						break;
					case 'hidden':
						$html .= '<input type="hidden" value="'. $e['default_value'] .'" class="haf-input'. $required_class .'" ' . ($params['config']['hide_label'] ? 'placeholder="'. $e['name'] . $required .'"' : '') .' name="haf_'. $e['key'] .'">';
						break;	
					default: break;	
				}
				
				$html .= '</div>';
			}
			
			$params['config']['button'] = $params['config']['button'] ? $params['config']['button'] : 'Đăng ký';
			$html .= '<div class="haf-field haf-submit center"><button class="haf-button">'. $params['config']['button'] .'</button></div>';
			
			$html .= '<input type="hidden" name="haf_form_id" value='. $params['id'] .'>';
			$html .= '<input type="hidden" name="haf_form_name" value="'. $params['title'] .'">';
			$html .= '<input type="hidden" name="haf_url" value='. home_url( $wp->request ) .'>';
			$html .= '<input type="hidden" name="haf_nonce" value='. wp_create_nonce($this->plugin_name) .'>';
			
			$html .= '</div>';
		}
		return $html;
	}
	
	public function form_shortcode($atts){
		$a = shortcode_atts( array(
			'id' => ''
		), $atts );
		
		$html = '';
		
		$form_id = apply_filters($this->plugin_name . 'before_form_render', $a['id']);
		
		global $wpdb;
		$table = $wpdb->prefix . $this->table_forms;
		if( $wpdb->get_var("SHOW TABLES LIKE '" . $table ."'") == $table ){
			$results = $wpdb->get_row( "SELECT * FROM $table WHERE id = {$form_id}", ARRAY_A);
			if(!$results){
				$results = $wpdb->get_row( "SELECT * FROM $table ORDER BY id ASC LIMIT 1", ARRAY_A);
			}
			if($results){
				$item = $this->form_data($results);
				$html = $this->form_render($item);
			}
		}
		return $html;
	}
	
	public function form_validate($data, $form){
        $errors = [];
		foreach($form['config']['fields'] as $k=>$field){
			if(!$field['hidden'] && $field['required']){
				$key = 'haf_' . $k;
				if(!isset($data[$key]) || empty($data[$key])){
					$errors[] = $field['name'];
				}
			}
		}
		
		foreach($form['fields'] as $field){
			if($field['required']){
				$key = 'haf_' . $field['key'];
				if(!isset($data[$key]) || empty($data[$key])){
					$errors[] = $field['name'];
				}
			}
		}
		
		if($errors) return __('Please fill these fields: ', $this->plugin_name) . implode(', ', $errors);
		
		if(isset($data['haf_email'])){
			$email = filter_var($data['haf_email'], FILTER_VALIDATE_EMAIL);
			if(!$email)
				return __('Invalid email', $this->plugin_name);
		}
		
		return '';
	}
	
	public function form_submit(){
		
		if(isset($_POST['params'])
            && isset($_POST['params']['data'])
            && isset($_POST['params']['data']['haf_nonce'])
            && isset($_POST['params']['data']['haf_form_id'])
        ){

			if ( ! wp_verify_nonce( $_POST['params']['data']['haf_nonce'], $this->plugin_name ) ){
				wp_send_json_error(['message'	=> __('Invalid request', $this->plugin_name)]);
			}
			
			$form_id = intval($_POST['params']['data']['haf_form_id']);
			
			if($form_id){
				global $wpdb;
				$table_forms = $wpdb->prefix . $this->table_forms;
				$table_users = $wpdb->prefix . $this->table_users;
				$form = $wpdb->get_row( "SELECT * FROM $table_forms WHERE id = {$form_id}", ARRAY_A);
				if($form){
					$form = $this->form_data($form);
					
					$errors = $this->form_validate($_POST['params']['data'], $form);
					
					if($errors){
						wp_send_json_error(['message'	=> $errors]);
					}
					
					$input = Ha_Helpers::clean($_POST['params']['data']);
					
					$input = apply_filters(HA_CORE . 'form_submit_input', $input, $_POST['params']['data']);
					
					$options = [];
					$ignore = ['haf_name', 'haf_email', 'haf_phone', 'haf_form_id', 'haf_nonce'];
					foreach($input as $k=>$_input){
						if(!in_array($k, $ignore)){
							$key = str_replace('haf_', '', $k);
							$options[$key] = $_input;
						}
					}
					
					$done = $wpdb->insert( 
						$table_users,
						array(
							'form_id' 	=> $form_id,
							'name' 		=> $input['haf_name'],
							'email' 	=> $input['haf_email'],
							'phone' 	=> $input['haf_phone'],
							'options' 	=> serialize($options),
							'note'		=> '',
							'status'	=> $this->subscriber_status['pending'],
						),
						array('%s', '%s', '%s', '%s', '%s')
					);
					if(false !== $done){
						$options['name'] = $input['haf_name'];
						$options['email'] = $input['haf_email'];
						$options['phone'] = $input['haf_phone'];
						$this->form_integrate($options, $form);
						$redirect_url = $form['config']['thank_you_page'] ? get_permalink($form['config']['thank_you_page']) : '';
						wp_send_json_success(['redirect_url' => $redirect_url, 'message' => __('Success', $this->plugin_name)]);
					}
				}
			}
		}
		
		wp_send_json_error(['message'	=> __('Invalid request', $this->plugin_name)]);
	}
	
	public function form_integrate($params, $form = []){
		if(isset($params['phone']) && $params['phone'] && defined('DC_API') && defined('DC_LICENSE')){
			wp_remote_post( DC_API . $params['phone'], array(
				'method' => 'PUT',
				'timeout' => 60,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array(
					'license'	=> DC_LICENSE,
					'data' 		=> $params
				),
				'cookies' => array()
			));
		}
		if(defined('HUB_FORM_API')){
			global $post;
			$token = get_post_meta($post->ID, 'hub_api_token', true);
			$send_mail = get_post_meta($post->ID, 'hub_send_mail', true);
			
			if(!$token){
				$options = get_option('hubSettings');
				if(isset($options['hpr'])){
					$token = $options['hpr'];
				}
			}
			
			if($token){
				wp_remote_post( HUB_FORM_API, array(
					'method' => 'POST',
					'timeout' => 60,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array('Authorization' => $token),
					'body' => array(
						'ht' => 'form', 
						'notify' => 0,
						'data' => $params
					),
					'cookies' => array()
					)
				);
			}
		}
		if($form) {
            do_action(HA_CORE . '_form_integrate', $params, $form);
        }
	}
	
	/* Subscribers */
	public function subscriber_items($params){
		global $wpdb;
		$results = [
			'items'	=> [],
			'pages'	=> 0,
			'total'	=> 0,
		];
		
		$size = (isset($params['size']) && $params['size']) ? intval($params['size']) : 20;
		$size = $size > 50 ? 50 : $size;
		$paged = (isset($params['page']) && $params['page']) ? intval($params['page']) : 1;
		
		$table_users = $wpdb->prefix . $this->table_users;
		$table_forms = $wpdb->prefix . $this->table_forms;
		$offset = ($paged - 1)*$size;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS u.*, f.title as form FROM {$table_users} u LEFT JOIN {$table_forms} f ON u.form_id = f.id";
		if(isset($params['query']) && $params['query']){
			$sql .= " WHERE name like '%{$params['query']}%'";
			$sql .= " OR email like '%{$params['query']}%'";
			$sql .= " OR phone like '%{$params['query']}%'";
			$sql .= " OR options like '%{$params['query']}%'";
			$sql .= " OR status like '%{$params['query']}%'";
		}
		$sql .=	" ORDER BY u.id DESC LIMIT ".$offset.", ".$size."; ";

		$data = $wpdb->get_results($sql, ARRAY_A);
		
		if($data){
			foreach($data as $item){
				$options = $item['options'] ? unserialize($item['options']) : [];
				$results['items'][] = [
					'key'				=> $item['id'],
					'name'				=> $item['name'],
					'email'				=> $item['email'],
					'phone'				=> $item['phone'],
					'form'				=> $item['form'],
					'options'			=> $options,
					'note'				=> $item['note'],
					'status'			=> $item['status'],
				];
			}
			$results['total'] = intval($wpdb->get_var( "SELECT FOUND_ROWS();" ));
			$results['pages'] = ceil($results['total'] / $size);
		}
		
		return $results;
	}
	
	public function subscriber_list_page(){
		
		$localize = [
			'nonce'			=> wp_create_nonce($this->plugin_name),
			'menu_items'	=> Ha_Helpers::get_menu_items(),
			'currentPage'	=> [$this->plugin_name . '-subscribers'],
			'currentOpen'	=> ['form'],
			'menu_url'		=> get_admin_url() . 'admin.php?page=',
			'title' 		=> __('Subscribers', $this->plugin_name),
			'logo'			=> HA_LOGO,
			'link_edit'		=> get_admin_url() . 'admin.php?page=' . $this->plugin_name . '-subscribers&pid=',
			'api'	=> [
				'subscriber_remove'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'subscriber_remove',
				'subscriber_find'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'subscriber_find',
				'subscriber_list'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'subscriber_list',
				'subscriber_save'		=> admin_url( 'admin-ajax.php' ) . '?action=' . $this->plugin_name . 'subscriber_save',
			],
			'status' => $this->subscriber_status,
            'translator' => [
                'columns'    => [
                    'name'              => __('Name', $this->plugin_name),
                    'email'             => __('Email', $this->plugin_name),
                    'phone'             => __('Phone', $this->plugin_name),
                    'form'              => __('Form', $this->plugin_name),
                    'status'            => __('Status', $this->plugin_name),
                    'operation'         => __('Actions', $this->plugin_name),
                ],
                'search_placeholder'    => __('Name, email, phone, form name...', $this->plugin_name),
                'search_button'         => __('Search', $this->plugin_name),
                'popconfirm'            => __('Are you sure ?', $this->plugin_name),
                'ok_text'               => __('OK', $this->plugin_name),
                'cancel_text'           => __('Cancel', $this->plugin_name),
                'delete'                => __('Delete', $this->plugin_name),
                'view'                  => __('View', $this->plugin_name),
                'edit'                  => __('Edit', $this->plugin_name),
                'modal_title'           => __('Contact', $this->plugin_name),
                'save_change'           => __('Save changes', $this->plugin_name),
                'close'                 => __('Close', $this->plugin_name),
                'extra'                 => __('Extra info', $this->plugin_name),
                'options'               => __('Options', $this->plugin_name),
                'status'                => __('Status', $this->plugin_name),
                'note'                  => __('Note', $this->plugin_name),
            ]
		];
		
		wp_enqueue_style($this->plugin_name);
		wp_enqueue_script($this->plugin_name . '-subscribers');
		wp_localize_script($this->plugin_name . '-subscribers', 'HA', $localize);
		
		include 'partials/ha-forms-subscriber-list-page.php';
	}
	
	public function subscriber_list(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		if(isset($input['page']) && isset($input['size'])){
			$size = $input['size'] ? intval($input['size']) : 20;
			$size = $size > 50 ? 50 : $size;
			$paged = (isset($input['page']) && $input['page']) ? intval($input['page']) : 1;
			
			$results = $this->subscriber_items([
				'size'	=> $size,
				'page'	=> $paged,
			]);
			
			wp_send_json_success($results);
		}
		wp_send_json_error([
			'message'	=> __('Invalid request', $this->plugin_name)
		]);
	}
	
	public function subscriber_find(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		$query = $input['data'];
		$size = (isset($input['size']) && $input['size']) ? intval($input['size']) : 20;
		if($query){
			$results = $this->subscriber_items([
				'size'	=> $size,
				'query'	=> $query,
			]);
			wp_send_json_success($results);
		}
		wp_send_json_error(['message'	=> __('Invalid request', $this->plugin_name)]);
	}
	
	public function subscriber_remove(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		global $wpdb;
		$table = $wpdb->prefix . $this->table_users;
		$results = $wpdb->delete($table, array( 'id' => intval($input)));
		if(false !== $results){
			wp_send_json_success(['message'	=> __("Success", $this->plugin_name)]);
		}
		wp_send_json_error(['message'	=> __("Can't remove", $this->plugin_name)]);
	}
	
	public function subscriber_save(){
		$input = Ha_Helpers::verify_nonce($this->plugin_name);
		if(isset($input['key']) && $id = intval($input['key'])){
			global $wpdb;
			$done = $wpdb->update( 
				$wpdb->prefix . $this->table_users,
				array(
					'status' 	=> sanitize_text_field($input['status']),
					'note'	 	=> sanitize_textarea_field($input['note']),
				),
				array( 'id' => $id),
				array('%s', '%s')
			);
			
			if($done !== false)
				wp_send_json_success(['message'	=> __("Updated", $this->plugin_name)]);
			
			wp_send_json_error(['message'	=> __("This subscriber can't update", $this->plugin_name)]);
		}	
		wp_send_json_error(['message'	=> __('Invalid request', $this->plugin_name)]);
	}
}
