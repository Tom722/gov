<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="我发表的评论" ref="navbar"></fa-navbar>
		<view class="comment" v-for="(res, index) in commentList" :key="res.id" @click="goDetail(res.archives)">
			<view class="left">
				<image :src="res.archives && res.archives.image" mode="aspectFill"></image>
			</view>
			<view class="right">
				<view class="content u-line-2" :style="[cmsTitleStyle(res.archives && res.archives.style)]">{{res.archives && res.archives.title }}</view>
				<view class="date u-m-b-10 u-tips-color">
					{{res.create_date}}
				</view>
				<view class="reply-box">
					<view class="item">
						<view class="u-tips-color">
							<rich-text :nodes="res.content"></rich-text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{color:theme.bgColor}" :custom-style="{backgroundColor:lightColor}"></u-back-top>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60 u-p-b-60" v-if="is_empty">
			<u-empty text="您还没有评论哦.." mode="list"></u-empty>
		</view>
		<!-- 更多 -->
		<view class="u-p-t-30 u-p-b-30" v-if="commentList.length">
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
				commentList: [],
				page: 1,
				has_more: false,
				status: 'loadmore',
				scrollTop: 0,
				is_empty: false
			};
		},
		onLoad() {
			this.getComment();
		},
		methods: {
			// 评论列表
			getComment: async function() {
				let res = await this.$api.getMyComment({
					page: this.page
				});
				this.status = 'loadmore';
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				this.commentList = this.commentList.concat(res.data.commentList.data);
				this.has_more = res.data.commentList.last_page > res.data.commentList.current_page;
				this.is_empty = !this.commentList.length;
			},
			goDetail(item) {
				let p = item.model_id == 2 ? '/pages/product/detail' : '/pages/article/detail';
				this.$u.route(p, {
					id: item.id
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
				this.getComment();
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
				background-color: rgb(242, 242, 242);
				border-radius: 12rpx;

				.item {
					padding: 20rpx;
					word-break: break-word;
				}
			}
		}
	}
</style>
