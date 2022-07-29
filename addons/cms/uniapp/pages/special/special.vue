<template>
	<view class="special">
		<!-- 顶部导航 -->
		<fa-navbar title="专题" ref="navbar"></fa-navbar>
		<view class="list" v-if="!type">
			<view class="item u-flex u-p-30" v-for="(item, index) in list" :key="index" @click="goPage('/pages/special/detail?diyname=' + item.diyname)">
				<image class="thumb" :src="item.image" mode="aspectFill"></image>
				<view class="boxtext u-flex-1 u-p-l-30">
					<view class="title u-line-1 u-font-16" v-text="item.title"></view>
					<view class="u-p-t-10 u-line-2">
						<text v-text="item.intro" class="u-line-2 u-tips-color"></text>
					</view>
					<view class="u-p-t-10">
						<view v-if="false">
							<u-tag
								:text="item.label"
								size="mini"
								:bg-color="theme.lightColor"
								:border-color="theme.faBorderColor"
								:color="theme.bgColor"
								shape="circle"
								mode="light"
							/>
						</view>
					</view>
					<view class="u-p-t-10 u-flex u-row-between u-tips-color">
						<view>
							<u-icon name="eye-fill"></u-icon>
							<text class="u-m-l-5" v-text="item.views"></text>
							浏览
						</view>
						<view class="u-m-l-30">
							<u-icon name="clock"></u-icon>
							<text class="u-m-l-10" v-text="item.create_date"></text>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="u-p-l-30 u-p-r-30 u-p-t-30 grid" v-if="type">
			<view
				class="item u-m-b-30 u-border-bottom"
				v-for="(item, index) in list"
				:key="index"
				@click="goPage('/pages/special/detail?diyname=' + item.diyname)"
			>
				<view class="content">
					<image class="maxthumb" :src="item.image" mode="aspectFill"></image>
					<view class="box">
						<view class="title u-line-1 u-font-16"><text v-text="item.title"></text></view>
					</view>
				</view>
				<view class="u-p-t-15 u-p-b-30">
					<view class="u-flex u-row-between">
						<view class="">
							<u-tag
								:text="item.label"
								size="mini"
								:bg-color="theme.lightColor"
								:border-color="theme.faBorderColor"
								:color="theme.bgColor"
								shape="circle"
								mode="light"
							/>
						</view>
						<view class="u-flex">
							<view class="u-tips-color">
								<u-icon name="eye-fill"></u-icon>
								<text class="u-m-l-5" v-text="item.views"></text>
							</view>
							<view class="u-tips-color u-m-l-30">
								<u-icon name="clock"></u-icon>
								<text class="u-m-l-5" v-text="item.create_date"></text>
							</view>
						</view>
					</view>
					<view class="u-p-t-15 u-line-3">{{ item.intro }}</view>
				</view>
			</view>
		</view>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60" v-if="is_empty"><u-empty text="暂无内容展示" mode="list"></u-empty></view>
		<!-- 加载更多 -->
		<view class="u-p-b-30" v-if="list.length"><u-loadmore bg-color="#fff" :status="status" /></view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
export default {
	onLoad(e) {
		this.getSpecialList();
	},
	data() {
		return {
			id: '',
			type: 0,
			list: [],
			page: 1,
			is_update: false,
			has_more: false,
			scrollTop: 0,
			status: 'nomore',
			is_empty: false
		};
	},
	methods: {
		getSpecialList() {
			this.$api
				.specialList({
					page: this.page
				})
				.then(({ code, data: res, msg }) => {
					uni.stopPullDownRefresh();
					if (code) {
						if (this.is_update) {
							this.list = [];
							this.is_update = false;
						}
						this.list = [...this.list, ...res.data];
						this.has_more = res.current_page < res.last_page;
						this.is_empty = !this.list.length;
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
		this.getSpecialList();
	},
	onReachBottom() {
		if (this.has_more) {
			this.status = 'loading';
			this.page = ++this.page;
			this.getSpecialList();
		}
	}
};
</script>

<style lang="scss" scoped>
.list {
	padding-bottom: 30rpx;
	.item {
		border-bottom: 1px solid #f4f6f8;
		.thumb {
			width: 250rpx;
			height: 200rpx;
			border-radius: 10rpx;
		}
		.boxtext{
			height: 200rpx;
			width: calc(100% - 250rpx);
		}
	}
}
.content {
	position: relative;
	.maxthumb {
		width: 100%;
		height: 300rpx;
		vertical-align: middle;
		border-radius: 10rpx;
	}
	.box {
		position: absolute;
		width: 100%;
		padding: 30rpx;
		left: 0;
		bottom: 0;
		background-color: rgba($color: #000000, $alpha: 0.3);
		color: #ffffff;
		border-radius: 10rpx;
	}
}
</style>
