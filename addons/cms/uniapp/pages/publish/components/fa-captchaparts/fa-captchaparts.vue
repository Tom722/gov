<template>
	<view class="" :style="customStyle">
		<u-form-item :required ="true" :label-position="labelPosition" prop="captcha" label-width="150" label="验证码">
			<view class="u-m-r-15"><u-input v-model="captcha" /></view>
			<u-image slot="right" width="250rpx" height="60rpx" :src="img_url" @click="getCaptcha"></u-image>
		</u-form-item>
	</view>
</template>

<script>
export default {
	name: 'fa-captchaparts',
	props: {
		value: {
			type: String,
			default: ''
		},		
		ident: {
			type: [String, Number],
			default: ''
		},
		labelPosition: {
			type: String,
			default: 'top'
		},
		customStyle: {
			type: Object,
			default() {
				return {};
			}
		}
	},
	watch: {
		captcha(newValue, oldValue) {
			this.$emit('input', newValue);
		}
	},
	mounted() {
		this.getCaptcha();
	},
	data() {
		return {
			captcha: '',
			img_url: ''
		};
	},
	methods: {
		getCaptcha() {
			this.$api.getCaptcha({ ident: this.ident }).then(res => {
				if (res.code == 1) {
					this.img_url = res.data;
				}
			});
		}
	}
};
</script>

<style lang="scss"></style>
