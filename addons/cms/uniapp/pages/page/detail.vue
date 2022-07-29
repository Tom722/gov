<template>
	<view class="u-bg-white">
		<!-- 顶部导航 -->
		<fa-navbar :title="pageInfo.title" ref="navbar"></fa-navbar>
		<view class="u-p-30 u-border-bottom">
			<view class="u-font-40"><text v-text="pageInfo.title"></text></view>
		</view>
		<view class="u-p-30 u-line-height">
			<u-parse :html="pageInfo.content" :tag-style="vuex_parse_style" :domain="vuex_config.upload ? vuex_config.upload.cdnurl : ''"></u-parse>
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		onLoad: async function(e) {
			let diyname = e.diyname || '';
			let res = await this.$api.getPageDetail({
				diyname: diyname
			});
			if (!res.code) {
				this.$u.toast(res.msg);
				return;
			}
			this.pageInfo = res.data.pageInfo;
		},
		data() {
			return {
				pageInfo: {}
			};
		},
		methods: {}
	};
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}
</style>
