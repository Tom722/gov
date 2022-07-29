<template>
	<view class="">
		<!-- 顶部导航 -->
		<fa-navbar :title="vuex_table_title || '首页'" ref="navbar"></fa-navbar>
		<!-- 搜索 -->
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
			<fa-u-tabs :list="tabList" :active-color="theme.bgColor" :bar-width="tabwidth" name="title" :is-scroll="true" :current="current" @change="change"></fa-u-tabs>
			<!-- #endif -->
			<!-- #ifndef MP-BAIDU -->
			<u-tabs :list="tabList" :active-color="theme.bgColor" :bar-width="tabwidth" name="title" :is-scroll="true" :current="current" @change="change"></u-tabs>
			<!-- #endif -->
		</view>
		<!-- 轮播图 -->
		<view class="" v-if="is_show">
			<u-swiper :title="true" border-radius="0" height="320" :list="bannerList" @click="openPage"></u-swiper>
		</view>
		<!-- 列表 -->
		<fa-article-item :archives-list="archivesList"></fa-article-item>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60" v-if="is_empty">
			<u-empty text="暂无内容展示" mode="list"></u-empty>
		</view>
		<!-- 加载更多 -->
		<view class="u-p-30" v-if="archivesList.length">
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
			bannerList() {
				if (this.vuex_config.bannerList) {
					return this.vuex_config.bannerList;
				}
				return [];
			},
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
				is_show: false,
				has_more: false,
				scrollTop: 0,
				is_update: false,
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
					model: -1
				}
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
					this.is_update = false;
					this.archivesList = [];
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
			getCategory() {
				this.$api.getCategory({
					...this.params
				}).then(res => {
					if (res.code == 1) {
						this.tabList = res.data;
						this.isTab = true; //百度小程序要先有值
					} else {
						this.$u.toast(res.msg);
					}
				});
			},
			openPage(index) {
				let path = this.bannerList[index].url;
				if (path == '/' || !path) {
					return;
				}
				if (['p', 'i', 'o'].includes(path.substr(0, 1))) {
					path = '/' + path;
				}
				if (path.indexOf('http') != -1) {
					this.$u.vuex('vuex_webs', this.bannerList[index]);
					path = '/pages/webview/webview';
				}
				let data = {},
					arr = path.split('?');
				if (arr[1]) {
					let q = arr[1].split('&');
					for (let item of q) {
						let param = item.split('=');
						data[param[0]] = param[1];
					}
				}
				this.$u.route(arr[0], data);
			},

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
</style>
