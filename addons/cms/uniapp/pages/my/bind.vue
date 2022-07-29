<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="绑定账号" ref="navbar"></fa-navbar>
		<view class="">
			<u-cell-group>
				<u-cell-item
					v-for="(item, index) in thirdList"
					:key="index"
					icon="weixin-fill"
					:icon-style="{ color: theme.bgColor }"
					:title="item.name"
					:value="item.bind ? '已绑定' : '未绑定'"
					@click="bindThird(item)"
				></u-cell-item>
			</u-cell-group>
		</view>
		<u-modal v-model="show" content="确认是否解绑该账号？" :show-cancel-button="true" @confirm="confirm"></u-modal>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
export default {
	onShow(){
		this.getBindList();
	},
	data() {
		return {
			thirdList: [],
			show: false,
			row: {}
		};
	},
	methods: {
		getBindList() {
			this.$api.getBindList().then(res => {
				console.log(res);
				if (res.code == 1) {
					let list = [];
					// #ifdef MP-WEIXIN
					let row = res.data.find(item => {
						return item.apptype == 'miniapp';
					});
					list.push({
						name: '微信小程序',
						type: 'wxapp',
						icon: 'weixin-circle-fill',
						iconColor: '#40BA49',
						bind: row
					});
					// #endif

					// #ifdef H5
					if (this.$util.isWeiXinBrowser()) {
						let row = res.data.find(item => {
							return item.apptype == 'mp';
						});
						list.push({
							name: '微信公众号',
							type: 'wechat',
							icon: 'weixin-circle-fill',
							iconColor: '#40BA49',
							bind: row
						});
					}
					// #endif
					// #ifdef APP-PLUS
					let row = res.data.find(item => {
						return item.apptype == 'native';
					});
					console.log(row);
					list.push({
						name: '微信',
						type: 'wechat',
						icon: 'weixin-circle-fill',
						iconColor: '#40BA49',
						bind: row
					});
					// #endif

					this.thirdList = list;
				}
			});
		},
		//解绑账号
		confirm() {
			this.$api.goUnbind({ apptype: this.row.apptype }).then(res => {
				this.$u.toast(res.msg);
				if (res.code == 1) {
					setTimeout(() => {
						this.getBindList();
					}, 1000);
				}
			});
		},
		// #ifdef H5
		bindThird: async function(e) {
			if (!e.bind) {
				let res = await this.$api.getAuthUrl({
					platform: 'wechat',
					url: window.location.origin + '/pages/login/auth'
				});
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				window.location.href = res.data;
			} else {
				//提示是否解绑
				this.row = e.bind;
				this.show = true;
			}
		},
		// #endif
		// #ifdef MP-WEIXIN
		bindThird: function(e) {
			if (!e.bind) {
				this.$u.route('/pages/login/wxlogin');
			} else {
				//提示是否解绑
				this.row = e.bind;
				this.show = true;
			}
		},
		// #endif
		// #ifdef APP-PLUS
		bindThird: function(e) {
			if (!e.bind) {
				let that = this;
				var all, Service;
				// 1.发送请求获取code
				plus.oauth.getServices(
					function(Services) {
						all = Services;
						Object.keys(all).some(key => {
							if (all[key].id == 'weixin') {
								Service = all[key];
							}
						});
						Service.authorize(
							async function(e) {
								let res = await that.$api.goAppLogin({ code: e.code, scope: e.scope });
								if (!res.code) {
									that.$u.toast(res.msg);
									return;
								}
								if (res.data.user) {
									that.$u.vuex('vuex_token', res.data.user.token);
									uni.navigateBack({
										delta:1
									})
									return;
								}
								that.$u.vuex('vuex_third', res.data.third);
								that.$u.route('/pages/login/register', { bind: 'bind' } );
							},
							function(e) {
								that.$u.toast('授权失败！');
							}
						);
					},
					function(err) {
						console.log(err);
						that.$u.toast('授权失败！');
					}
				);
			} else {
				//提示是否解绑
				this.row = e.bind;
				this.show = true;
			}
		}
		// #endif
	}
};
</script>

<style lang="scss">
page {
	background-color: #ffffff;
}
</style>
