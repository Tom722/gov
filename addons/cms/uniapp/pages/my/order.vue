<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="我的消费订单" ref="navbar"></fa-navbar>
		<view class="list u-bg-white">
			<view class="item u-flex u-col-top u-p-30 u-border-bottom" v-for="(item,index) in list" :key="index"
				@click="goDetail(item.archives)">
				<view class="">
					<u-image width="180rpx" border-radius="10" height="130rpx" :src="item.archives.image"
						mode="aspectFill"></u-image>
				</view>
				<view class="u-m-l-20 content">
					<view class="u-text-weight" :style="[cmsTitleStyle(item.archives.style)]"><text
							class="u-line-2">{{item.archives.title}}</text></view>
					<view class="u-p-t-15">
						<u-tag :text="item.title" shape="circle" :bg-color="lightColor" :border-color="faBorderColor"
							:color="theme.bgColor" />
					</view>
					<view class="u-p-t-15 u-type-error">
						支付金额：{{item.payamount}}
					</view>
					<view class="u-p-t-15 u-tips-color">{{item.createtime}}</view>
				</view>
			</view>
		</view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{color:theme.bgColor}"
			:custom-style="{backgroundColor:lightColor}"></u-back-top>
		<!-- 更多 -->
		<view class="u-p-t-30 u-p-b-30">
			<u-loadmore bg-color="#f4f6f8" :status="has_more ? status : 'nomore'" />
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		onLoad() {
			this.getMyOrder();
		},
		data() {
			return {
				list: [],
				page: 1,
				has_more: false,
				status: 'loadmore',
				scrollTop: 0
			};
		},
		methods: {
			getMyOrder: async function() {
				let res = await this.$api.getOrder({
					page: this.page
				});
				this.status = 'loadmore';
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				this.list = [...this.list, ...res.data.orderList.data];
				this.has_more = res.data.last_page > res.data.current_page;
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
				this.getMyOrder();
			}
		}
	};
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}
</style>
