<template>
	<view class="fa-array-download">
		<view class="u-flex">
			<view class="u-flex-2"><view class="">来源</view></view>
			<view class="u-flex-7 u-p-l-10"><view class="">地址</view></view>
			<view class="u-flex-4 u-p-l-10"><view class="">密码</view></view>
		</view>
		<view class="u-flex u-m-t-15" v-for="(item, index) in list" :key="index">
			<view class="u-flex-2"><u-input v-model="item.name" :trim="trim" placeholder="来源" :border="border" :clearable="false" /></view>
			<view class="u-flex-6 u-m-l-10 u-m-r-10 "><u-input v-model="item.url" placeholder="请输入地址" :trim="trim" :border="border" /></view>
			<view class="u-p-l-10 u-p-r-10 u-m-r-10 u-text-center close" @click="uploadFile(index)">
				<u-icon name="arrow-upward" color="#ffffff" size="30"></u-icon>
			</view>
			<view class="u-flex-3 u-m-r-10 "><u-input placeholder="密码" v-model="item.password" :trim="trim" :border="border" /></view>
			<view class="u-p-l-10 u-p-r-10 u-text-center close" @click="del(index)"><u-icon name="close" color="#ffffff" size="30"></u-icon></view>
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
		<fa-upload-file ref="file" @success="uploadSuccess"></fa-upload-file>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
import FaUploadFile from '../fa-upload-file/fa-upload-file.vue'
export default {
	name: 'fa-array-download',
	components:{FaUploadFile},
	mixins: [Emitter],
	props: {
		contentList: {
			type: [Array, Object],
			default() {
				return [];
			}
		},
		showValue: {
			type: String,
			default() {
				return '';
			}
		}
	},
	watch: {
		contentList: {
			immediate: true,
			handler: function(val) {
				if (val) {
					let list = [];
					for (let i in val) {
						list.push({
							name: i,
							url: '',
							password: ''
						});
					}
					this.list = list;
				}
			}
		},
		list: {
			deep: true,
			handler: function(newValue) {
				let value = this.$u.test.empty(newValue)?'':JSON.stringify(newValue);			
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
					this.list = JSON.parse(val);
				}
			}
		}
	},
	data() {
		return {
			border: true,
			trim: true,
			listIndex: 0,
			list: [
				{
					name: '',
					url: '',
					password: ''
				}
			]
		};
	},
	methods: {
		add() {
			this.list.push({
				name: '',
				url: '',
				password: ''
			});
		},
		del(index) {
			this.list.splice(index, 1);
		},
		uploadFile(index) {
			this.listIndex = index;
			this.$refs.file.onUpload();
		},
		uploadSuccess(e) {
			this.$set(this.list[this.listIndex], 'url', e);
		},
		getData() {
			return this.list;
		}
	}
};
</script>

<style lang="scss" scoped>
.fa-array-download{
	width: 100%;
}
.close {
	background: #2c3e50;
	border-radius: 10rpx;
}
</style>
