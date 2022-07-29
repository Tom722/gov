<template>
	<view>
		<view class="" v-if="type == 'checkbox'">
			<u-checkbox-group @change="checkboxGroupChange" :width="radioCheckWidth" :wrap="radioCheckWrap">
				<u-checkbox v-model="item.checked" :active-color="theme.bgColor" v-for="(item, index) in list" :key="index" :name="item.value">
					{{ item.lable }}
				</u-checkbox>
			</u-checkbox-group>
		</view>
		<view class="" v-else>
			<u-radio-group v-model="radioValue" @change="radioGroupChange">
				<u-radio v-for="(item, index) in list" :active-color="theme.bgColor" :key="index" :name="item.value">{{ item.lable }}</u-radio>
			</u-radio-group>
		</view>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-check-radio',
	mixins: [Emitter],
	props: {
		value:{
			type:[String,Number],
			default:''
		},
		faList: {
			type: [Object, Array],
			default: []
		},
		type: {
			type: String,
			default: 'checkbox'
		},		
		checkValue: {
			type: [String, Array],
			default: ''
		}
	},
	watch: {
		faList: {
			immediate: true,
			handler(val) {
				let value = [];
				if (this.type == 'checkbox') {
					value = this.checkValue ? this.checkValue.split(',') : [];
				} else {
					this.radioValue = this.checkValue;
				}
				let list = [];
				for (let i in val) {
					list.push({
						lable: val[i],
						value: i,
						checked: value.indexOf(i) != -1
					});
				}
				this.list = list;
			}
		}
	},
	data() {
		return {
			radioCheckWidth: 'auto',
			radioCheckWrap: false,
			list: [],
			radioValue: ''
		};
	},
	methods: {
		checkboxGroupChange(e) {
			this.sendValue(e.join(','));
		},
		radioGroupChange(e) {
			this.sendValue(e);
		},
		sendValue(value){
			this.$emit('input', value);
			setTimeout(() => {
				this.dispatch('u-form-item', 'on-form-blur',value);
			}, 50);
		}
	}
};
</script>

<style lang="scss"></style>
