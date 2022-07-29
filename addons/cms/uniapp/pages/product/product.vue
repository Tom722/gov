<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar :title="vuex_table_title || '产品'" ref="navbar"></fa-navbar>
		<view class="u-p-20 u-bg-white u-flex u-col-center" v-if="is_show">
			<view class="u-flex-1">
				<fa-search :mode="2"></fa-search>
			</view>
			<view class="u-p-l-15 u-p-r-5 u-flex u-col-center" v-if="is_order">
				<fa-orderby-select :filterList="filterList" :orderList="orderList" :multiple="true" @change="goOrderBy"></fa-orderby-select>
			</view>
		</view>
		<!-- 分类 -->
		<view class="u-border-top" v-if="isTab">
			<!-- #ifdef MP-BAIDU -->
			<fa-u-tabs :list="tabList" :bar-width="tabwidth" :active-color="theme.bgColor" name="title" :is-scroll="true" :current="current" @change="change"></fa-u-tabs>
			<!-- #endif -->
			<!-- #ifndef MP-BAIDU -->
			<u-tabs :list="tabList" :bar-width="tabwidth" :active-color="theme.bgColor" name="title" :is-scroll="true" :current="current" @change="change"></u-tabs>
			<!-- #endif -->
		</view>
		<view class="u-flex u-flex-wrap u-p-30">
			<view class="product-item" v-for="(item, index) in archivesList" :key="index" @click="goDetail(item.id)">
				<u-image width="100%" border-radius="8" height="210" :src="item.image"></u-image>
				<view class="title">{{ item.title }}</view>
			</view>
		</view>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60" v-if="is_empty">
			<u-empty text="暂无内容展示" mode="list"></u-empty>
		</view>
		<!-- 加载更多 -->
		<view class="u-p-b-30" v-if="archivesList.length">
			<u-loadmore bg-color="#f4f6f8" :status="status" />
		</view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		computed: {
			is_order() {
				return this.filterList.length > 0 || this.orderList.length > 0;
			}
		},
		data() {
			return {
				tabwidth: 40,
				current: 0,
				status: 'nomore',
				page: 1,
				channel_id: 0,
				filterList: [],
				orderList: [],
				archivesList: [],
				has_more: false,
				scrollTop: 0,
				is_update: false,
				is_show: false,
				params: {},
				query: {},
				isTab: false,
				tabList: [],
				is_empty: false,
				channel: {}
			};
		},
		onLoad(e) {
			let query = e;
			if (JSON.stringify(query) == '{}') {
				query = e;
			}
			if (query && JSON.stringify(query) != '{}') {
				this.params = query;
			} else {
				this.params = {
					channel: -1,
					model: -1,
					menu_index: 2
				};
			}
			this.getCategory();
			this.getArchives();
		},
		onShow() {
			// #ifdef MP-BAIDU
			if (this.channel) {
				this.setPagesInfo();
			}
			// #endif
		},
		methods: {
			change(index) {
				//重设Bar宽度
				this.tabwidth = this.$util.strlen(this.tabList[index].title) * 30;
				this.current = index;
				this.channel_id = this.tabList[index].id;
				this.is_update = true;
				this.page = 1;
				this.getArchives();
			},
			goOrderBy(e) {
				this.page = 1;
				this.is_update = true;
				this.query = e;
				this.getArchives();
			},
			getArchives: async function() {
				let data = {
					page: this.page,
					...this.params,
					...this.query
				};
				if (this.channel_id) {
					data.channel = this.channel_id;
				}
				let res = await this.$api.getArchives(data);
				this.status = 'nomore';
				uni.stopPullDownRefresh();
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}

				let { filterList, orderList, pageList, channel } = res.data;
				this.filterList = filterList;
				this.orderList = orderList;
				this.channel = channel;
				// #ifdef MP-BAIDU
				if (this.channel) {
					this.setPagesInfo();
				}
				// #endif
				if (this.is_update) {
					this.archivesList = [];
					this.is_update = false;
				}
				this.is_show = true;
				this.has_more = pageList.current_page < pageList.last_page;
				this.archivesList = [...this.archivesList, ...pageList.data];
				this.is_empty = !this.archivesList.length;
			},
			// #ifdef MP-BAIDU
			setPagesInfo() {
				swan.setPageInfo({
					title: this.channel.seotitle,
					keywords: this.channel.keywords,
					description: this.channel.description,
					releaseDate: this.$u.timeFormat(this.channel.createtime, 'yyyy-mm-dd hh:MM:ss'),
					image: this.channel.image,
					success: res => {
						console.log('setPageInfo success', res);
					},
					fail: err => {
						console.log('setPageInfo fail', err);
					}
				});
			},
			// #endif
			goDetail(id) {
				this.$u.route('/pages/product/detail', { id: id });
			},
			getCategory() {
				this.$api.getCategory({ ...this.params }).then(res => {
					if (res.code == 1) {
						this.tabList = res.data;
						this.isTab = true; //百度小程序要先有值
					} else {
						this.$u.toast(res.msg);
					}
				});
			}
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		//下拉刷新
		onPullDownRefresh() {
			this.is_update = true;
			this.page = 1;
			this.getArchives();
		},
		onReachBottom() {
			if (this.has_more) {
				this.status = 'loading';
				this.page = ++this.page;
				this.getArchives();
			}
		}
	};
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}

	.product-item {
		width: 50%;
		position: relative;

		.title {
			position: absolute;
			font-size: 20rpx;
			bottom: 20rpx;
			background: #000;
			width: auto;
			max-width: 50%;
			padding-left: 8rpx;
			padding-right: 8rpx;
			color: #f8f8f8;
			opacity: 0.7;
			height: 50rpx;
			line-height: 50rpx;
			overflow: hidden;
			text-align: center;
			border-top-right-radius: 6rpx;
			border-bottom-right-radius: 6rpx;
		}
	}

	.product-item:nth-child(2n + 1) {
		padding-right: 15rpx;
	}

	.product-item:nth-child(2n) {
		padding-left: 15rpx;
	}

	.product-item:not(:last-child) {
		margin-bottom: 30rpx;
	}
</style>
