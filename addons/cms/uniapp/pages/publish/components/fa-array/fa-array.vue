<template>
	<view class="fa-array">
		<view class="u-flex">
			<view class="u-flex-5">
				<view class="">{{ faKey }}</view>
			</view>
			<view class="u-flex-6 u-p-l-10">
				<view class="">{{ faVal }}</view>
			</view>
		</view>
		<view class="u-flex u-m-t-15" v-for="(item, index) in list" :key="index">
			<view class="u-flex-5"><u-input v-model="item.key" :trim="trim" :border="border" /></view>
			<view class="u-m-l-15 u-m-r-15 u-flex-5"><u-input v-model="item.value" :trim="trim" :border="border" /></view>
			<view class="u-p-l-15 u-p-r-15 u-text-center close" @click="del(index)"><u-icon name="close" color="#ffffff" size="30"></u-icon></view>
		</view>
		<view class="u-text-right u-m-t-20">
			<u-button
				size="mini"
				type="primary"
				:custom-style="{ backgroundColor: lightColor, color: theme.bgColor, border: '1px solid ' + faBorderColor }"
				throttle-time="0"
				@click="add"
			>
				<u-icon name="plus" :color="theme.bgColor" size="25"></u-icon>
				<text class="u-m-l-5">追加</text>
			</u-button>
		</view>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-array',
	mixins: [Emitter],
	props: {
		value:{
			type:String,
			default:''
		},
		faKey: {
			type: String,
			default: '键'
		},
		faVal: {
			type: String,
			default: '值'
		},		
		showValue: {
			type: [String, Object],
			default: ''
		}
	},
	watch: {
		list: {
			deep: true,
			handler: function(newValue) {
				let obj = {};
				newValue.map(item => {
					if (item.key && item.value) {
						obj[item.key] = item.value;
					}
				});
				let value = this.$u.test.empty(obj)?'':JSON.stringify(obj);
				this.$emit('input', value);
				setTimeout(() => {
					this.dispatch('u-form-item', 'on-form-blur', value);
				}, 50);
			}
		},
		showValue: {
			immediate: true,
			handler(val) {
				if (val) {
					if (this.$u.test.object(val)) {
						let arr = [];
						for (let i in val) {
							arr.push({
								key: i,
								value: val[i]
							});
						}
						if (arr.length > 0) {
							this.list = arr;
						}
					} else {
						let o = JSON.parse(val);
						let arr = [];
						for (let i in o) {
							arr.push({
								key: i,
								value: o[i]
							});
						}
						if (arr.length > 0) {
							this.list = arr;
						}
					}
				}
			}
		}
	},
	data() {
		return {
			border: true,
			trim: true,
			list: [
				{
					key: '',
					value: ''
				}
			]
		};
	},
	methods: {
		add() {
			this.list.push({
				key: '',
				value: ''
			});
		},
		del(index) {
			this.list.splice(index, 1);
		},
		getData() {
			return this.list;
		}
	}
};
</script>

<style lang="scss" scoped>
.fa-array{
	width: 100%;
}
.close {
	background: #2c3e50;
	border-radius: 10rpx;
}
</style>
