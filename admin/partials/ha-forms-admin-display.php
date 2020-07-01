<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://tuanltntu.com
 * @since      1.0.0
 *
 * @package    Ha_Forms
 * @subpackage Ha_Forms/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<style>
	.form-preview{padding: 20px;background: #eee;width: 100%; height: 100%;}
	.add_field{margin: 15px 0;}
	.haf-remove-field{
		float: right;
		top: -5px;
		right: 5px;
	}
	.ha-core .haf-tag{
		height: auto;
		font-size: 24px;
		line-height: normal;
	}
</style>
<?php Ha_Helpers::get_header(); ?>
<div class="media-overlay" v-if="isMediaLoading"><a-spin size="large"></a-spin></div>
<a-row :gutter="20">
	<a-col :sm="12" :md="12" :xs="24">
		<a-row :gutter="20">
			<a-col :sm="12" :md="12" :xs="24">
				<label class="lb-title"><?php _e('Title*', $this->plugin_name); ?></label>
				<a-input class="input-control" v-model="params.title"></a-input>
			</a-col>
			<a-col :sm="12" :md="12" :xs="24">
				<label class="lb-title"><?php _e('Sub title', $this->plugin_name); ?></label>
				<a-input class="input-control" v-model="params.config.sub_title"></a-input>
			</a-col>
		</a-row>
		<a-tabs default-active-key="1">
			<a-tab-pane key="1">
				<span slot="tab">
					<a-icon type="tool"></a-icon>
					<?php _e('Builder', $this->plugin_name); ?>
				</span>
				<div class="input-control">
					<label class="lb-title"><?php _e('Submit button*', $this->plugin_name); ?></label>
					<a-input class="input-control" v-model="params.config.button"></a-input>
				</div>
				<div class="input-control">
					<label class="lb-title"><?php _e('Thank you page', $this->plugin_name); ?></label>
					<a-select
						show-search
						class="input-control"
						style="width: 100%"
						placeholder="<?php _e('Select a page', $this->plugin_name); ?>"
						v-model="params.config.thank_you_page"
					  >
						<a-select-option v-for="item in HA.data.pages" :key="item.id">{{ item.title }}</a-select-option>
					</a-select>
				</div>
				<div class="input-control">
					<label class="lb-title"><?php _e('Fields', $this->plugin_name); ?></label>
					<a-collapse accordion>
						<a-collapse-panel :show-arrow="false" :header="'<?php _e('Name', $this->plugin_name); ?>' + (params.config.fields.name.required ? '*' : '')" key="1">
							<div class="input-control">
								<a-switch id="name_field_hide" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.name.hidden" ></a-switch>
								<label class="lb-title" for="name_field_hide"><?php _e('Hide', $this->plugin_name); ?></label>	
							</div>
							<div class="input-control">
								<a-switch id="name_field_required" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.name.required" ></a-switch>
								<label class="lb-title" for="name_field_required"><?php _e('Required', $this->plugin_name); ?></label>	
							</div>
						</a-collapse-panel>
						<a-collapse-panel :show-arrow="false" :header="'<?php _e('Email', $this->plugin_name); ?>' + (params.config.fields.email.required ? '*' : '')" key="2">
							<div class="input-control">
								<a-switch id="email_field_hide" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.email.hidden" ></a-switch>
								<label class="lb-title" for="email_field_hide"><?php _e('Hide', $this->plugin_name); ?></label>	
							</div>
							<div class="input-control">
								<a-switch id="email_field_required" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.email.required" ></a-switch>
								<label class="lb-title" for="email_field_required"><?php _e('Required', $this->plugin_name); ?></label>	
							</div>
						</a-collapse-panel>
						<a-collapse-panel :show-arrow="false" :header="'<?php _e('Phone', $this->plugin_name); ?>' + (params.config.fields.phone.required ? '*' : '')" key="3">
							<div class="input-control">
								<a-switch id="phone_field_hide" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.phone.hidden" ></a-switch>
								<label class="lb-title" for="phone_field_hide"><?php _e('Hide', $this->plugin_name); ?></label>	
							</div>
							<div class="input-control">
								<a-switch id="phone_field_required" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.fields.phone.required" ></a-switch>
								<label class="lb-title" for="phone_field_required"><?php _e('Required', $this->plugin_name); ?></label>	
							</div>
						</a-collapse-panel>
						<a-collapse-panel :show-arrow="false" v-for="item in params.fields" :key="item.key">
							<template slot="header">
								{{ item.name + (item.required ? '*' : '') + (item.type == 'hidden' ? ' (<?php _e('hidden', $this->plugin_name); ?>)' : '') }}
								<a-button shape="circle" class="haf-remove-field" type="danger" @click="remove(item)"><a-icon type="delete"></a-icon></a-button>
							</template>
							<div class="input-control">
								<a-switch :id="item.key" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="item.required" ></a-switch>
								<label class="lb-title" :for="item.key"><?php _e('Required', $this->plugin_name); ?></label>	
							</div>
							<div class="input-control">
								<label class="lb-title"><?php _e('Field name*', $this->plugin_name); ?></label>
								<a-input class="input-control" v-model="item.name"></a-input>
							</div>
							<div class="input-control">
								<label class="lb-title"><?php _e('Type*', $this->plugin_name); ?></label>
								<a-select style="width: 100%" default-value="text" v-model="item.type">
									<a-select-option value="text">Text</a-select-option>
									<a-select-option value="textarea">Textarea</a-select-option>
									<a-select-option value="radio">Radio</a-select-option>
									<a-select-option value="select">Dropdown list</a-select-option>
									<a-select-option value="hidden">Hidden</a-select-option>
								</a-select>
							</div>
							<div class="input-control" v-if="item.type == 'radio' || item.type == 'select'">
								<label class="lb-title"><?php _e('Values(each value per line)', $this->plugin_name); ?></label>
								<a-textarea :rows="4" v-model="item.options" placeholder="value:label - vd: red: <?php _e('Red', $this->plugin_name); ?>"></a-textarea>
							</div>
							<div class="input-control">
								<label class="lb-title"><?php _e('Default value', $this->plugin_name); ?></label>
								<a-input class="input-control" v-model="item.default_value"></a-input>
							</div>
						</a-collapse-panel>
					</a-collapse>
					<div class="center">
						<a-button class="add_field" type="dashed" icon="plus" @click="modalField = true; modalFieldError = ''"><?php _e('Add new', $this->plugin_name); ?></a-button>
					</div>
					<a-modal title="<?php _e('New field', $this->plugin_name); ?>" v-model="modalField" @ok="addField">
						<div class="input-control" v-if="modalFieldError">
							<a-alert type="error" :message="modalFieldError" banner></a-alert>
						</div>
						<div class="input-control">
							<a-switch id="field_required" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="field.required" ></a-switch>
							<label class="lb-title" for="field_required"><?php _e('Required', $this->plugin_name); ?></label>	
						</div>
						<div class="input-control">
							<label class="lb-title"><?php _e('Field name', $this->plugin_name); ?></label>
							<a-input class="input-control" v-model="field.name"></a-input>
						</div>
						<div class="input-control">
							<label class="lb-title"><?php _e('Type', $this->plugin_name); ?></label>
							<a-select style="width: 100%" default-value="text" v-model="field.type">
								<a-select-option value="text">Text</a-select-option>
								<a-select-option value="textarea">Textarea</a-select-option>
								<a-select-option value="radio">Radio</a-select-option>
								<a-select-option value="select">Dropdown list</a-select-option>
								<a-select-option value="hidden">Hidden</a-select-option>
							</a-select>
						</div>
						<div class="input-control" v-if="field.type == 'radio' || field.type == 'select'">
							<label class="lb-title"><?php _e('Values(each value per line)', $this->plugin_name); ?></label>
							<a-textarea :rows="4" v-model="field.options" placeholder="value:label - vd: red: <?php _e('Red', $this->plugin_name); ?>"></a-textarea>
						</div>
						<div class="input-control">
							<label class="lb-title"><?php _e('Default value', $this->plugin_name); ?></label>
							<a-input class="input-control" v-model="field.default_value"></a-input>
						</div>
					</a-modal>
				</div>
				
			</a-tab-pane>
			<a-tab-pane key="2">
				<span slot="tab">
					<a-icon type="setting"></a-icon>
					<?php _e('Settings', $this->plugin_name); ?>
				</span>
				<div class="input-control">
					<a-switch id="hide_label" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.hide_label" ></a-switch>
					<label class="lb-title" for="hide_label"><?php _e('Use placeholder', $this->plugin_name); ?></label>	
				</div>
				<div class="input-control">
					<a-switch id="title_hide" checked-children="<?php _e('On', $this->plugin_name); ?>" un-checked-children="<?php _e('Off', $this->plugin_name); ?>" v-model="params.config.hide_title" ></a-switch>
					<label class="lb-title" for="title_hide"><?php _e('Hide title', $this->plugin_name); ?></label>	
				</div>
				<div class="input-control">
					<label class="lb-title"><?php _e('Custom css', $this->plugin_name); ?></label>
					<a-tag>.haf-form</a-tag>
					<a-tag>.haf-field</a-tag>
					<a-tag>.haf-input</a-tag>
					<a-tag>.haf-button</a-tag>
					<a-textarea :rows="5" v-model="params.config.css"></a-textarea>
				</div>
			</a-tab-pane>
            <?php do_action(HA_CORE . '_form_tab'); ?>
		</a-tabs>
	</a-col>
	<a-col :sm="12" :md="12" :xs="24">
		<a-divider><a-button class="ha-btn" icon="sync" @click="render" type="primary"><?php _e('Preview', $this->plugin_name); ?></a-button></a-divider>
		<template v-if="params.id">
			<div class="center" style="margin-bottom: 25px;" id="haf-form-shortcode" @click="copy('haf-form-shortcode')">
				<a-tag color="#f50" class="haf-tag">[haf-form id="{{ params.id }}"]</a-tag>
			</div>
		</template>
		<div class="form-preview">
			<div v-if="params.html" v-html="params.html"></div>
		</div>
	</a-col>
</a-row>
<?php Ha_Helpers::get_footer(); ?>
