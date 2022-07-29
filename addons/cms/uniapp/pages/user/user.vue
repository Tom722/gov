<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar :title="userInfo.nickname || '用户'" :border-bottom="false" ref="navbar"></fa-navbar>
		<!-- 会员中心 -->
		<view class="u-p-t-30 u-p-b-50 home" :style="[{ backgroundColor: theme.bgColor || '#374486' }]">
			<view class="userinfo">
				<u-avatar size="120" :show-sex="true" :sex-icon="userInfo.gender == 1 ? 'man' : 'woman'" :src="url || userInfo.avatar"></u-avatar>
				<view class="u-m-t-10 u-p-l-80 u-p-r-80 u-line-1">{{ userInfo.nickname || '' }}</view>
				<view class="u-m-t-10 u-p-l-80 u-p-r-80 u-line-2">{{ userInfo.bio || '这家伙很懒，什么也没写！' }}</view>
			</view>
			<view class="corrugated">
				<view class="wave-top wave-item" :style="[{ backgroundImage: 'url(' + wavetop + ')' }]"></view>
				<view class="wave-middle wave-item" :style="[{ backgroundImage: 'url(' + wavemiddle + ')' }]"></view>
				<view class="wave-bottom wave-item" :style="[{ backgroundImage: 'url(' + wavebottom + ')' }]"></view>
			</view>
		</view>
		<!-- 他的文章和评论 -->
		<!-- #ifdef MP-BAIDU -->
		<fa-u-tabs :list="tableList" :active-color="theme.bgColor" :bar-width="tabWidth" :is-scroll="false" :current="current" @change="change"></fa-u-tabs>
		<!-- #endif -->
		<!-- #ifndef MP-BAIDU -->
		<u-tabs :list="tableList" :active-color="theme.bgColor" :bar-width="tabWidth" :is-scroll="false" :current="current" @change="change"></u-tabs>
		<!-- #endif -->
		<u-gap height="1" bg-color="#f4f6f8"></u-gap>
		<u-gap height="30" bg-color="#fff"></u-gap>
		<view class="" v-if="current">
			<view class="comment" v-for="(res, index) in list" :key="res.id" @click="goDetail(res.archives)">
				<view class="left">
					<image :src="res.archives && res.archives.image" mode="aspectFill"></image>
				</view>
				<view class="right">
					<view class="content u-line-2" :style="[cmsTitleStyle(res.archives && res.archives.style)]">{{ res.archives && res.archives.title }}</view>
					<view class="reply-box">
						<view class="item">
							<view class="u-tips-color">
								<rich-text :nodes="res.content"></rich-text>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="" v-else>
			<view class="list u-bg-white">
				<view class="item u-flex u-p-30 u-border-bottom" v-for="(item, index) in list" :key="index" @click="goDetail(item)">
					<view class="">
						<u-image width="220rpx" border-radius="10" height="160rpx" :src="item.image"></u-image>
					</view>
					<view class="u-m-l-20 content">
						<view class="u-text-weight">
							<text class="u-line-2" :style="[cmsTitleStyle(item.style)]">{{ item.title }}</text>
						</view>
						<view class="u-p-t-15 u-flex">
							<view class="u-m-r-10" v-for="(it, idx) in item.taglist" :key="idx">
								<u-tag :text="it.name" shape="circle" :bg-color="lightColor" :border-color="faBorderColor" :color="theme.bgColor" />
							</view>
						</view>
						<view class="u-p-t-15 u-tips-color u-line-2">{{ item.description }}</view>
						<view class="u-p-t-15 u-tips-color">{{ item.createtime && item.createtime | timeFrom('yyyy-mm-dd') }}</view>
					</view>
				</view>
			</view>
		</view>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60" v-if="!loading && list.length==0">
			<u-empty :text="current==0?'暂无文章':'暂无评论'" mode="list"></u-empty>
		</view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 加载更多 -->
		<view class="u-p-30" v-if="list.length">
			<u-loadmore bg-color="#ffffff" :status="status" />
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	import { avatar } from '@/common/fa.mixin.js';
	export default {
		mixins: [avatar],
		computed: {
			wavetop() {
				return this.$u.http.config.baseUrl + '/assets/addons/cms/img/wave-top.png';
			},
			wavemiddle() {
				return this.$u.http.config.baseUrl + '/assets/addons/cms/img/wave-mid.png';
			},
			wavebottom() {
				return this.$u.http.config.baseUrl + '/assets/addons/cms/img/wave-bot.png';
			},
			isBind() {
				return false;
			}
		},
		onLoad(e) {
			let query = e || {};
			this.user_id = query.user_id || query.uid || '';
			this.getUserIndex();
		},
		data() {
			return {
				url: '',
				tabWidth: 150,
				userInfo: {},
				user_id: 0,
				tableList: [{
						name: `他的文章(0)`,
						type: 'archives'
					},
					{
						name: `他的评论(0)`,
						type: 'comments'
					}
				],
				current: 0,
				loading: true,
				list: [],
				is_update: false,
				has_more: false,
				status: 'nomore',
				scrollTop: 0,
				page: 1
			};
		},
		methods: {
			goDetail(item) {
				let type = 'navigateTo';

				// #ifdef MP-WEIXIN
				//优化当pages超过10个时的处理
				let pages = getCurrentPages();
				type = pages.length >= 9 ? 'reLaunch' : type;
				// #endif

				if (item.model_id == 2) {
					this.$u.route({ url: '/pages/product/detail', params: { id: item.id }, type: type });
				} else {
					this.$u.route({ url: '/pages/article/detail', params: { id: item.id }, type: type });
				}
			},
			getUserIndex() {
				this.loading = true;
				this.$api
					.getUserInfo({
						user_id: this.user_id,
						type: this.tableList[this.current].type,
						page: this.page
					})
					.then(res => {
						this.loading = false;
						if (!res.code) {
							this.$u.toast(res.msg);
							setTimeout(() => {
								uni.navigateBack({
									delta: 1
								})
							}, 1000)
							return;
						}
						this.status = 'nomore';
						this.tableList = [{
								name: `他的文章(${res.data.archives})`,
								type: 'archives'
							},
							{
								name: `他的评论(${res.data.comments})`,
								type: 'comments'
							}
						];
						this.userInfo = res.data.user;
						this.list = [...this.list, ...res.data.list.data];
						console.log(this.list);
						this.has_more = res.data.list.last_page > res.data.list.current_page;
					});
			},
			change(index) {
				this.tabWidth = this.$util.strlen(this.tableList[index].name) * 30;
				this.current = index;
				this.page = 1;
				this.list = [];
				this.getUserIndex();
			}
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		onReachBottom() {
			if (this.has_more && !this.loading) {
				this.status = 'loading';
				this.page++;
				this.getUserIndex();
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
	.home {
		position: relative;

		.userinfo {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding: 30rpx 0;
			z-index: 100;
			height: 310rpx;
			color: #ffffff;
		}

		.corrugated {
			bottom: -2rpx;
			left: 0;
			position: absolute;
			width: 100%;
			height: 50%;
			overflow: hidden;
			z-index: 0;

			.wave-item {
				position: absolute;
				width: 200%;
				left: 0;
				height: 200rpx;
				background-repeat: repeat no-repeat;
				background-position: 0 bottom;
				transform-origin: center bottom;
			}

			.wave-top {
				opacity: 0.5;
				animation: wave-animation 3s;
				animation-delay: 1s;
				background-size: 50% 60rpx;
				z-index: 15;
			}

			.wave-middle {
				opacity: 0.75;
				animation: wavemove 10s linear infinite;
				background-size: 50% 80rpx;
				z-index: 10;
			}

			.wave-bottom {
				animation: wavemove 15s linear infinite;
				background-size: 50% 45rpx;
				z-index: 5;
			}
		}
	}

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

	@keyframes wavemove {
		0% {
			transform: translateX(0) translateZ(0) scaleY(1);
		}

		50% {
			transform: translateX(-25%) translateZ(0) scaleY(0.55);
		}

		100% {
			transform: translateX(-50%) translateZ(0) scaleY(1);
		}
	}
</style>
