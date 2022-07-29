<template>
	<view><u-switch v-model="switchCheck" :active-color="theme.bgColor" @change="switchChange"></u-switch></view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-switch',
	mixins: [Emitter],
	props: {
		value: {
			type: [String, Number],
			default: 0
		},
		defvalue: {
			type: [String, Number],
			default: 0
		}
	},
	watch: {
		defvalue: {
			immediate: true,
			handler(newValue, oldValue) {
				this.switchCheck = newValue == 1;
			}
		}
	},
	data() {
		return {
			switchCheck: false
		};
	},
	methods: {
		switchChange(e) {
			let value = e ? '1' : '0';
			this.$emit('input', value);
			setTimeout(() => {
				this.dispatch('u-form-item', 'on-form-blur', value);
			}, 50);
		}
	}
};
</script>

<style lang="scss"></style>
