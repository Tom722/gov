<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="个人中心" :border-bottom="false" ref="navbar"></fa-navbar>
		<!-- 会员中心 -->
		<view class="u-p-t-30 u-p-b-50 home" :style="[{ backgroundColor: theme.bgColor || '#374486' }]">
			<view class="userinfo" :style="[{ height: vuex_user.vip > 0 ? '370rpx' : '310rpx' }]">
				<block v-if="vuex_token">
					<u-avatar
						size="120"
						:show-sex="vuex_user.vip == 0"
						:show-level="vuex_user.vip > 0"
						:sex-icon="vuex_user.gender == 1 ? 'man' : 'woman'"
						:src="vuex_user.avatar"
						@click="chooseAvatar"
					></u-avatar>
					<view class="u-skeleton-fillet u-m-t-15 u-p-l-80 u-p-r-80 u-line-1">{{ vuex_user.nickname || '' }}</view>
					<view class="u-m-t-15" v-if="vuex_user.vip > 0">
						<u-tag
							type="success"
							:text="vuex_user.vipInfo && vuex_user.vipInfo.name ? vuex_user.vipInfo.name : ''"
							mode="light"
							shape="circle"
							size="mini"
						/>
					</view>

					<view class="u-skeleton-fillet u-m-t-15 u-p-l-80 u-p-r-80 u-line-2">{{ vuex_user.bio || '这家伙很懒，什么也没写！' }}</view>
				</block>
				<block v-else>
					<u-avatar size="120" src="0"></u-avatar>
					<view class="u-m-t-30"><u-button hover-class="none" size="mini" @click="goPage('/pages/login/mobilelogin')">立即登录</u-button></view>
				</block>
			</view>
			<view class="corrugated">
				<view class="wave-top wave-item" :style="[{ backgroundImage: 'url(' + wavetop + ')', height: vuex_user.vip > 0 ? '230rpx' : '200rpx' }]"></view>
				<view
					class="wave-middle wave-item"
					:style="[{ backgroundImage: 'url(' + wavemiddle + ')', height: vuex_user.vip > 0 ? '230rpx' : '200rpx' }]"
				></view>
				<view
					class="wave-bottom wave-item"
					:style="[{ backgroundImage: 'url(' + wavebottom + ')', height: vuex_user.vip > 0 ? '230rpx' : '200rpx' }]"
				></view>
			</view>
		</view>
		<!-- 统计 -->
		<view class="u-flex u-text-center u-p-l-30 u-p-r-30 u-p-t-50 u-p-b-50 u-bg-white">
			<view class="u-flex-4" @click="goPage('/pages/my/moneylog', true)">
				<view class="u-text-weight u-font-28">
					￥
					<text v-text="vuex_user.money || 0"></text>
				</view>
				<view class="u-m-t-20">余额</view>
			</view>
			<view class="u-flex-4 u-border-left u-border-right" @click="goPage('/pages/my/scorelog', true)">
				<view class="u-text-weight u-font-28"><text v-text="vuex_user.score || 0"></text></view>
				<view class="u-m-t-20">积分</view>
			</view>
			<view class="u-flex-4">
				<view class="u-text-weight u-font-28">
					Lv.
					<text v-text="vuex_user.level || 0"></text>
				</view>
				<view class="u-m-t-20">等级</view>
			</view>
		</view>
		<!-- 导航 -->
		<view class="u-m-t-30">
			<u-cell-group>
				<u-cell-item icon="chat-fill" title="我发表的评论" @click="goPage('/pages/my/comment', true)"></u-cell-item>
				<u-cell-item icon="level" title="我的VIP会员" @click="goPage('/pages/my/member', true)"></u-cell-item>
				<u-cell-item icon="heart-fill" title="我的收藏" @click="goPage('/pages/my/collection', true)"></u-cell-item>
				<u-cell-item icon="order" title="我的消费订单" @click="goPage('/pages/my/order', true)"></u-cell-item>
				<u-cell-item icon="pushpin-fill" title="每日一签" @click="goPage('/pages/signin/signin', true)"></u-cell-item>
				<u-cell-item icon="edit-pen-fill" title="我发布的文章" @click="goPage('/pages/my/article', true)"></u-cell-item>
				<u-cell-item icon="account-fill" title="个人资料" @click="goPage('/pages/my/profile', true)"></u-cell-item>
				<!-- <u-cell-item icon="man-add-fill" v-if="isBind" title="绑定账号" @click="goPage('/pages/my/bind', true)"></u-cell-item> -->
				<u-cell-item icon="edit-pen" title="发布文章" @click="goPage('/pages/publish/channel', true)"></u-cell-item>
				<u-cell-item icon="hourglass" title="专题列表" @click="goPage('/pages/special/special')"></u-cell-item>
				<u-cell-item icon="file-text-fill" title="自定义表单列表" @click="goPage('/pages/diyform/lists')"></u-cell-item>
				<u-cell-item icon="account" title="关于我们" @click="goPage('/pages/my/aboutus')"></u-cell-item>
				<u-cell-item icon="backspace" v-if="vuex_token" title="退出登录" @click="goPage('out')"></u-cell-item>
			</u-cell-group>
		</view>
		<u-top-tips ref="uTips" :navbar-height="statusBarHeight + navbarHeight"></u-top-tips>
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
	onShow() {
		if (this.vuex_token) {
			this.getUserIndex();
		} else {
			this.$u.vuex('vuex_user', {});
		}
		//移除事件监听
		uni.$off('uAvatarCropper', this.upload);
	},
	data() {
		return {
			// 状态栏高度，H5中，此值为0，因为H5不可操作状态栏
			statusBarHeight: uni.getSystemInfoSync().statusBarHeight,
			// 导航栏内容区域高度，不包括状态栏高度在内
			navbarHeight: 44,
			form: {
				avatar: ''
			}
		};
	},
	methods: {
		getUserIndex: async function() {
			let res = await this.$api.getUserIndex();
			uni.stopPullDownRefresh();
			if (!res.code) {
				this.$u.toast(res.msg);
				return;
			}
			this.$u.vuex('vuex_user', res.data.userInfo || {});
		},
		goPage: async function(path, auth) {
			if (auth && !this.vuex_token) {
				this.$u.toast('请先登录再操作！');
				return;
			}
			if (path == 'out') {
				let res = await this.$api.goUserLogout();
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				//退出成功
				this.$u.vuex('vuex_user', { avatar: '' }); //清除vuex_user
				this.$u.vuex('vuex_token', ''); //清除用户token
				return;
			}
			if (path == '/pages/my/member' && !this.vuex_user.is_install_vip) {
				this.$refs.uTips.show({
					title: '请先安装VIP会员插件并启用',
					type: 'error',
					duration: '3000'
				});

				return;
			}
			if (path == '/pages/signin/signin' && !this.vuex_user.is_install_signin) {
				this.$refs.uTips.show({
					title: '请先安装会员签到插件并启用',
					type: 'error',
					duration: '3000'
				});

				return;
			}
			this.$u.route(path);
		},
		editAvatar: async function() {
			let res = await this.$api.goUserAvatar({
				avatar: this.form.avatar
			});
		}
	},
	//下拉刷新
	onPullDownRefresh() {
		if (this.vuex_token) {
			this.getUserIndex();
		} else {
			uni.stopPullDownRefresh();
			this.$u.toast('请先登录');
			this.$u.vuex('vuex_user', {});
		}
	}
};
</script>

<style lang="scss">
page {
	background-color: #f4f6f8;
}
.home {
	position: relative;
	.userinfo {
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 30rpx 0;
		z-index: 100;
		.u-skeleton-fillet {
			color: #ffffff;
			width: 100vw;
			text-align: center;
		}
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
