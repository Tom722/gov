<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="列表" ref="navbar"></fa-navbar>
		<!-- 搜索 -->
		<view class="u-p-20 u-bg-white u-flex u-col-center">
			<view class="u-flex-1">
				<fa-search :mode="2" :custom="true" @change="search"></fa-search>
			</view>
			<view class="u-p-l-15 u-p-r-5 u-flex u-col-center" v-if="is_show">
				<fa-orderby-select :orderList="orderList" :filterList="filterList" showField="title" :multiple="true" @change="goOrderBy"></fa-orderby-select>
			</view>
		</view>
		<view class="u-p-30">
			<view class="form-list">
				<view class="item u-border-top u-p-30 u-m-b-30 u-bg-white" v-for="(item, index) in list" :key="index" @click="goDetail(item.id)">
					<view class="u-m-b-10 u-text-weight title"><text v-text="item.title ? item.title : item.name"></text></view>
					<view class="u-line-3 u-tips-color">{{ item.content }}</view>
					<view class="u-flex u-flex-wrap u-m-t-30">
						<block v-if="item.images">
							<view :class="item.images.length == 1 ? 'image' : 'images'" v-for="(res, ik) in item.images" :key="ik">
								<u-image width="100%" height="100%" :src="res"></u-image>
							</view>
						</block>
						<block v-else>
							<view :class="item.image.length == 1 ? 'image' : 'images'" v-for="(res, ik) in item.image" :key="ik">
								<u-image width="100%" height="100%" :src="res"></u-image>
							</view>
						</block>
					</view>
					<view class="u-flex u-font-28">{{ item.createtime | timeFrom }}</view>
				</view>
			</view>
		</view>
		<!-- 加载更多 -->
		<view class="u-p-b-30" v-if="list.length">
			<u-loadmore bg-color="#f4f6f8" :status="has_more ? status : 'nomore'" />
		</view>
		<!-- 空数据 -->
		<view class="fa-empty" v-if="!list.length">
			<u-empty></u-empty>
		</view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 发布留言 -->
		<fa-add :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }" @click="publish">
		</fa-add>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		onLoad(e) {
			let query = e || {};
			this.diyname = query.diyname || '';
			this.getformList();
		},
		data() {
			return {
				diyname: '',
				is_update: false,
				scrollTop: 0,
				list: [],
				status: 'loadmore',
				has_more: false,
				page: 1,
				orderList: [],
				filterList: [],
				is_show: false,
				query: {},
				keyword: ''
			};
		},
		methods: {
			getformList() {
				let data = {
					page: this.page,
					diyname: this.diyname,
					keyword: this.keyword,
					...this.query
				};
				this.$api.formList(data).then(res => {
					if (res.code) {
						if (this.is_update) {
							this.list = [];
							this.is_update = false;
						}
						let { orderList, filterList, pageList } = res.data;
						this.orderList = orderList;
						this.filterList = filterList;
						this.has_more = pageList.current_page < pageList.last_page;
						this.list = [...this.list, ...pageList.data];
						this.is_show = true;
					} else {
						this.$u.toast(res.msg);
					}
				});
			},
			goOrderBy(e) {
				this.query = e;
				this.page = 1;
				this.is_update = true;
				this.getformList();
			},
			search(e) {
				this.keyword = e;
				this.page = 1;
				this.is_update = true;
				this.getformList();
			},
			goDetail(id) {
				this.$u.route('/pages/diyform/detail', {
					form_id: id,
					diyname: this.diyname
				});
			},
			publish() {
				this.$u.route('/pages/publish/diyform', {
					diyname: this.diyname
				});
			}
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		onReachBottom() {
			if (this.has_more) {
				this.status = 'loading';
				this.page++;
				this.getformList();
			}
		}
	};
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}

	.form-list {
		width: 100%;
		box-shadow: 0 0 5rpx rgba(0, 134, 243, 0.1);
		border-radius: 5rpx;

		.item {
			margin-bottom: 2rpx;

			.images {
				width: 30%;
				height: 200rpx;
				padding-bottom: 30rpx;

				&:nth-child(3n-1) {
					margin-left: 30rpx;
					margin-right: 30rpx;
				}
			}

			.image {
				width: 100%;
				height: 300rpx;
				padding-bottom: 30rpx;

				image {
					border-radius: 10rpx;
					width: 100%;
					height: 100%;
				}
			}
		}
	}
</style>
