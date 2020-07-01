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
	.ant-card-hoverable img{max-width: calc(100% + 48px);margin: -24px -24px 20px;}
	.ant-card-body{position: relative;}
	.card-item{margin-bottom: 20px !important;}
	.haf-modal-view .ant-modal-body{background: #f2f2f2;}
</style>
<?php Ha_Helpers::get_header(); ?>

	<a-row>
		<a-col :sm="{ span: 12, offset: 6 }" :md="{ span: 12, offset: 6 }" :xs="{ span: 24}">
			<a-input-search class="card-item" v-model="query" :placeholder="HA.translator.search_placeholder" @search="find" :enter-button="HA.translator.search_button"></a-input-search>
		</a-col>
	</a-row>
	<a-row :gutter="20">
		<a-col :sm="24" :md="24" :xs="24">
			<template v-if="winWidth > 768">
			<a-table :columns="columns" :pagination="false" :data-source="dataList.items" :loading="isLoading" bordered>
				<template v-for="col in cols" :slot="col" slot-scope="text, record, index">
					<div v-if="col == 'shortcode'" :id="'haf-tag' + index" @click="copy('haf-tag' + index)"><a-tag color="#f50">{{ text }}</a-tag></div>
					<div v-else-if="col != 'image'"> {{ text }}</div>
					<img v-else :src="text" width="100px" >
				</template>
				<template slot="operation" slot-scope="text, record, index">
					<span>
						<a-button icon="eye" :title="HA.translator.view" @click="view(record)"></a-button>
					</span>
					<span>
						<a-button icon="edit" :title="HA.translator.edit" @click="edit(record)"></a-button>
					</span>
					<span>
						<a-popconfirm :title="HA.translator.popconfirm" @confirm="remove(record)" :ok-text="HA.translator.ok_text" :cancel-text="HA.translator.cancel_text">
							<a-button icon="delete" :title="HA.translator.delete"></a-button>
						</a-popconfirm>
					</span>
				</template>
			</a-table>
			</template>
			<template v-else>
				<template v-for="item in dataList.items">
					<a-card hoverable class="card-item">
						<template class="ant-card-actions" slot="actions">
							<span>
								<a-button icon="eye" @click="view(item)">{{ HA.translator.view }}</a-button>
							</span>
							<span>
								<a-button icon="edit" @click="edit(item)">{{ HA.translator.edit }}</a-button>
							</span>
							<span>
								<a-popconfirm :title="HA.translator.popconfirm" @confirm="remove(item)" :ok-text="HA.translator.ok_text" :cancel-text="HA.translator.cancel_text">
									<a-button icon="delete">{{ HA.translator.delete }}</a-button>
								</a-popconfirm>
							</span>
						</template>
						<a-card-meta :title="item.title">
							<template slot="description">
								<p>{{ item.thank_you_page }}</p>
								<a-tag color="#f50">{{ item.shortcode }}</a-tag>
							</template>
						</a-card-meta>
					</a-card>
				</template>
			</template>
			<div class="lnd-section center" v-if="dataList.pages > 1">
				<a-pagination :show-quick-jumper="true" :page-size="pageSize" :total="dataList.total" @change="paginate"></a-pagination>
			</div>
		</a-col>
	</a-row>
	<a-modal
		:title="itemView.title"
		v-model="modalView"
		@ok="edit(itemView)"
		:ok-text="HA.translator.edit"
		:cancel-text="HA.translator.close"
		:confirm-loading="modalLoading"
		class="haf-modal-view"
	>
		<div v-html="itemView.html"></div>
	</a-modal>
<?php Ha_Helpers::get_footer(); ?>
