<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="我的收藏" ref="navbar"></fa-navbar>
		<u-swipe-action v-for="(res, index) in list" :show="res.show" :index="index" :key="res.id" @open="open" @click="click" :options="options">
			<view class="comment" @click="goDetail(res)">
				<view class="left">
					<image :src="res.image" mode="aspectFill"></image>
				</view>
				<view class="right">
					<view class="content u-line-2">{{ res.title }}</view>
					<view class="reply-box">
						<view class="u-tips-color">收藏于:{{ res.create_date }}</view>
					</view>
				</view>
			</view>
		</u-swipe-action>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60 u-p-b-60" v-if="is_empty">
			<u-empty text="您还没有收藏哦..." mode="list"></u-empty>
		</view>
		<!-- 更多 -->
		<view class="u-p-t-30 u-p-b-30" v-if="list.length">
			<u-loadmore :status="has_more ? status : 'nomore'" />
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				list: [],
				page: 1,
				has_more: false,
				status: 'loadmore',
				scrollTop: 0,
				is_empty: false,
				show: false,
				options: [{
					text: '删除',
					style: {
						backgroundColor: '#dd524d'
					}
				}]
			};
		},
		onLoad() {
			this.getCollection();
		},
		methods: {
			//列表
			getCollection() {
				this.$api.getCollection({
					page: this.page
				}).then(res => {
					this.status = 'loadmore';
					if (res.code == 1) {
						let { collectionList } = res.data;
						collectionList.data.map(item => {
							item.show = false;
						});
						this.list = [...this.list, ...collectionList.data];
						this.has_more = collectionList.last_page > collectionList.current_page;
						this.is_empty = !this.list.length;
					}
				});
			},
			goDetail(item) {
				let p = '';
				switch (item.type) {
					case 'page':
						break;
					case 'diyform':
						p = '/pages/diyform/detail'
						break;
					case 'archives':
						p = '/pages/article/detail';
					case 'special':
						p = '/pages/special/detail';
					default:
						break;
				}
				if (p.length > 0) {
					this.$u.route(p, {
						id: item.aid
					});
				} else {
					this.$u.toast("暂不支持查看");
				}
			},
			click(index, index1) {
				this.$api
					.delCollection({
						id: this.list[index].id
					})
					.then(res => {
						this.$u.toast(res.msg);
						if (res.code == 1) {
							this.list.splice(index, 1);
							this.is_empty = !this.list.length;
						}
					});
			},
			open(index) {
				// 先将正在被操作的swipeAction标记为打开状态，否则由于props的特性限制，
				// 原本为'false'，再次设置为'false'会无效
				this.list[index].show = true;
				this.list.map((val, idx) => {
					if (index != idx) this.list[idx].show = false;
				});
			}
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		//底部加载更多
		onReachBottom() {
			if (this.has_more) {
				this.status = 'loading';
				this.page++;
				this.getCollection();
			}
		}
	};
</script>

<style>
	page {
		background-color: #ffffff;
	}
</style>
<style lang="scss" scoped>
	.comment {
		display: flex;
		padding: 30rpx;
		border-bottom: 1px solid #eee;
		width: 100vw;

		.left {
			image {
				width: 180rpx;
				height: 130rpx;
				background-color: #f2f2f2;
				border-radius: 10rpx;
			}
		}

		.right {
			flex: 1;
			padding-left: 20rpx;
			font-size: 28rpx;

			.content {
				margin-bottom: 10rpx;
			}

			.reply-box {
				word-break: break-word;
			}
		}
	}
</style>
