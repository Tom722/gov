<template>
	<view class="">
		<!-- 顶部导航 -->
		<fa-navbar :title="is_bind ? '创建或绑定账号' : '注册'" ref="navbar"></fa-navbar>
		<view class="login">
			<view class="u-text-center" v-if="is_bind"><u-avatar :size="150" :src="vuex_third.avatar"></u-avatar></view>
			<view class="u-text-center u-p-t-20" v-if="is_bind">{{ vuex_third.nickname }}</view>
			<view class="u-m-t-30" v-if="!is_wx_phone || !is_bind">
				<u-form :model="form" ref="uForm">
					<block v-if="!is_bind">
						<u-form-item :label-position="labelPosition" label="用户名:" prop="username" label-width="120">
							<u-input :border="border" placeholder="请填写用户名" v-model="form.username" />
						</u-form-item>
						<u-form-item :label-position="labelPosition" label="密 码:" prop="password" label-width="120">
							<u-input :password-icon="true" :border="border" type="password" v-model="form.password" placeholder="请输入密码"></u-input>
						</u-form-item>
					</block>
					<u-form-item :label-position="labelPosition" label="手机号:" prop="mobile" label-width="120">
						<u-input :border="border" placeholder="请输入手机号" v-model="form.mobile" type="number"></u-input>
					</u-form-item>
					<u-form-item :label-position="labelPosition" label="验证码:" prop="code" label-width="120">
						<u-input :border="border" placeholder="请输入验证码" v-model="form.code" type="text"></u-input>
						<u-button
							hover-class="none"
							type="primary"
							slot="right"
							:custom-style="{ backgroundColor: theme.bgColor, color: theme.color }"
							size="mini"
							@click="getCode"
						>
							{{ codeTips }}
						</u-button>
					</u-form-item>
				</u-form>
			</view>
			<view v-if="is_wx_phone && is_bind" class="" style="height: 150rpx;"></view>

			<view class="u-p-t-30 u-text-center u-flex">
				<u-checkbox :active-color="theme.bgColor" v-model="agreeChecked" name="agree">阅读并同意</u-checkbox>
				<text class="u-font-30 agree" @click="agreement" :style="[{ color: theme.bgColor }]">《用户协议》</text>
			</view>

			<view class="u-m-t-80" v-if="!is_wx_phone || !is_bind">
				<u-button hover-class="none" type="primary" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" shape="circle" @click="register">
					{{ is_bind ? '立即绑定' : '注册' }}
				</u-button>
			</view>

			<!-- #ifdef MP-WEIXIN -->
			<view class="u-m-t-60" v-if="is_bind && is_wx_phone">
				<u-button
					hover-class="none"
					open-type="getPhoneNumber"
					type="primary"
					:custom-style="{ backgroundColor: theme.bgColor, color: theme.color }"
					shape="circle"
					@getphonenumber="getPhoneNumber"
				>
					使用微信手机号码授权
				</u-button>
			</view>
			<view class="u-m-t-30 u-flex u-row-right" v-if="is_bind">
				<text v-text="is_wx_phone ? '使用其他手机号码权' : '使用微信手机号码授权'" @click="is_wx_phone = !is_wx_phone"></text>
			</view>
			<!-- #endif -->
		</view>
		<u-verification-code seconds="60" ref="uCode" @change="codeChange"></u-verification-code>
		<u-toast ref="uToast" />
	</view>
</template>

<script>
import { loginfunc } from '@/common/fa.mixin.js';
export default {
	mixins: [loginfunc],
	onLoad(e) {
		let query = e || {};
		this.is_bind = query.bind || '';
		if (!this.is_bing) {
			this.labelPosition = 'left';
			this.rules.username = [
				{
					required: true,
					message: '请输入用户名',
					trigger: ['change', 'blur']
				}
			];
			this.rules.password = [
				{
					required: true,
					message: '请输入密码',
					trigger: 'change'
				}
			];
		}
	},
	onReady() {
		if (!this.is_bind) {
			this.$refs.uForm.setRules(this.rules);
		}
	},
	watch: {
		is_wx_phone(newValue, oldValue) {
			if (!newValue) {
				this.$nextTick(() => {
					this.$refs.uForm.setRules(this.rules);
				});
			}
		}
	},
	data() {
		return {
			is_bind: '', //判断是绑定账号还是注册账号
			// #ifdef MP-WEIXIN
			is_wx_phone: true, //微信小程序默认为手机号码授权登录，不显示表单
			// #endif
			// #ifndef MP-WEIXIN
			is_wx_phone: false, //非微信小程序，需要显示表单
			// #endif
			agreeChecked: false,
			labelPosition: 'top',
			border: false,
			form: {
				username: '',
				password: '',
				mobile: '',
				code: ''
			},
			rules: {
				mobile: [
					{
						required: true,
						message: '请输入手机号码',
						trigger: 'change'
					},
					{
						// 自定义验证函数，见上说明
						validator: (rule, value, callback) => {
							return this.$u.test.mobile(value);
						},
						message: '手机号码不正确',
						trigger: ['change', 'blur']
					}
				],
				code: [
					{
						required: true,
						message: '请输入短信验证码',
						trigger: 'change'
					}
				]
			},
			codeTips: '',
			errorType: ['message']
		};
	},
	methods: {
		agreement() {
			this.$u.route('/pages/my/agreement');
		},
		codeChange(text) {
			this.codeTips = text;
		},
		// 获取验证码
		getCode: async function() {
			if (!this.$u.test.mobile(this.form.mobile)) {
				this.$u.toast('手机号码格式不正确！');
				return;
			}
			if (this.$refs.uCode.canGetCode) {
				let res = await this.$api.getSmsSend({
					mobile: this.form.mobile,
					event: this.is_bind ? 'bind' : 'register'
				});
				this.$u.toast(res.msg);
				if (res.code) {
					this.$refs.uCode.start();
				}
			} else {
				this.$u.toast('倒计时结束后再发送');
			}
		},
		register() {
			if (!this.agreeChecked) {
				this.$refs.uToast.show({
					title: '请阅读并同意遵守《用户协议》',
					type: 'error'
				});
				return;
			}
			this.$refs.uForm.validate(valid => {
				if (valid) {
					this.is_bind ? this.goBind() : this.goReg();
				} else {
					this.$u.toast('验证失败');
				}
			});
		},
		// #ifdef MP-WEIXIN
		getPhoneNumber(e) {
			if (!this.agreeChecked) {
				this.$refs.uToast.show({
					title: '请阅读并同意遵守《用户协议》',
					type: 'error'
				});
				return;
			}
			let that = this;
			uni.login({
				provider: 'weixin',
				success: function(loginRes) {
					console.log(loginRes);
					that.$api.getWechatMobile({ encryptedData: e.detail.encryptedData, iv: e.detail.iv, code: loginRes.code }).then(function(res) {
						if (res.code == 1) {
							that.$u.vuex('vuex_token', res.data.token);
							that.success(3);
						} else {
							that.$u.toast(res.msg);
						}
					});
				},
				fail(e) {
					console.log(e);
				}
			});
		},
		// #endif
		//去注册
		goReg: async function() {
			if (this.vuex_wx_uid) {
				this.form.wx_user_id = this.vuex_wx_uid;
			}
			let res = await this.$api.goRegister(this.form);
			if (!res.code) {
				this.$u.toast(res.msg);
				return;
			}
			this.$u.vuex('vuex_token', res.data.token);
			this.success(3);
		},
		//绑定账号
		goBind: async function() {
			let res = await this.$api.goThirdAccount(this.form);
			if (!res.code) {
				this.$u.toast(res.msg);
				return;
			}
			let delta = 2;
			// #ifdef MP-WEIXIN
			delta = 3;
			// #endif
			if (res.data.userinfo) {
				this.$u.vuex('vuex_token', res.data.userinfo.token);
				this.success(delta);
			}
		}
	}
};
</script>

<style>
page {
	background-color: #ffffff;
}
.login {
	padding: 20% 10%;
}
.agree {
	margin-left: -25rpx;
}
</style>
