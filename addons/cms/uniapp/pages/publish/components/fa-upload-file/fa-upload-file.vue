<template>
	<view>
		<!-- #ifdef APP-PLUS -->
		<fa-file ref="faFile" @up-success="onAppSuccess"></fa-file>
		<!-- #endif -->
		<view class="u-flex u-flex-wrap" v-if="isDom">
			<view class="u-m-r-20 u-m-b-20" v-for="(item, index) in fileList" :key="index">
				<view class="fa-file fa-flex u-row-right">
					<view class="u-delete-icon" @click="delfile(index)"><u-icon name="close" color="#ffffff" size="20"></u-icon></view>
					<text class="u-tips-color u-m-b-15" v-text="getFileType(item)"></text>
				</view>
			</view>
			<view class="u-m-r-20 u-m-b-20" v-if="(fileType == 'many' && fileList.length >= 1) || fileList.length == 0">
				<view class="fa-file fa-flex u-row-center u-col-center u-p-t-10" @click="onUpload">
					<u-icon name="plus" size="40" color="#606266"></u-icon>
					<view class="select-color">选择文件</view>
				</view>
			</view>
		</view>
		<view ref="input" class="input" style="display: none;"></view>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-upload-file',
	mixins: [Emitter],
	props: {
		value:{
			type:String,
			default:''
		},
		fileType: {
			type: String,
			default: 'single'
		},
		//页面展示
		isDom:{
			type:Boolean,
			default:false
		},
		showValue:{
			type:Array,
			default(){
				return []
			}
		}
	},
	watch: {
		fileList:{
			// immediate: true,
			deep:true,
			handler:function(val){
				let value = val.join(',')
				this.$emit('input', value);
				setTimeout(() => {
					this.dispatch('u-form-item', 'on-form-blur', value);
				}, 50);
			}
		},
		showValue:{
			immediate:true,
			handler:function(newValue){
				if(newValue.length){
					this.fileList = newValue;
				}
			}
		}
	},
	computed:{
		getFileType(){
			return item=>{
				let url = item.split('.');
				return '.'+url[1];
			}
		}
	},
	data() {
		return {
			fileList: []
		};
	},
	mounted() {
		// #ifdef H5
		var input = document.createElement('input')
		input.type = 'file'
		input.onchange = (event) => {
			 this.$api.goUpload({
				 file:event.target.files[0]
			 }).then(res=>{
			 	this.onSuccess(res)
			 })
		}
		this.$refs.input.$el.appendChild(input)
		// #endif
	},
	methods: {
		/* 上传 */
		onUpload() {
			// #ifdef MP-WEIXIN
				this.wxChooseFile();
			// #endif
			// #ifdef H5
				this.h5ChooseFile()
			// #endif
			// #ifdef APP-PLUS
				var formData = {};
				let isObj = this.$u.test.object(this.vuex_config.upload.multipart);
				if (isObj) {
					formData = this.vuex_config.upload.multipart;
				}
				this.$refs.faFile.upload({
					// nvue页面使用时请查阅nvue获取当前webview的api，当前示例为vue窗口
					currentWebview: this.$mp.page.$getAppWebview(),
					//调试时ios有跨域，需要后端开启跨域并且接口地址不要使用http://localhost/
					url: this.vuex_config.upload.uploadurl,
					//默认file,上传文件的key
					name: 'file',
					header: {
						token: this.vuex_token || '',
						uid: this.vuex_user.id || 0
					},
					formData: formData
					//...其他参数
				});
			// #endif
		},
		//移除文件
		delfile(index) {
			this.fileList.splice(index,1);
		},
		// #ifdef MP-WEIXIN
			wxChooseFile() {
				wx.chooseMessageFile({
					count: 1,
					type: 'file',
					success: ({tempFiles}) => {
						let [{path:filePath,name:fileName}] = tempFiles;
						this.$api.goUpload({filePath:filePath}).then(res=>{
							this.onSuccess(res)
						})
					},
					fail:function(err){
						console.log(err)
					}
				})
			},
		// #endif
		// #ifdef H5
			h5ChooseFile(){
				this.$refs.input.$el.firstChild.click();
			},
		// #endif
		// #ifndef APP-PLUS
			onSuccess(res) {
				this.$u.toast(res.msg)
				if(res.code){
					if(this.isDom){
						this.fileList.push(res.data.url);
					}else{
						this.$emit('success',res.data.url);
					}
				}
			}
		// #endif
		// #ifdef APP-PLUS
			onAppSuccess(res){
				if(this.$u.test.jsonString(res.data)){
					res = JSON.parse(res.data);
					this.$u.toast(res.msg)
					if(res.code){
						if(this.isDom){
							this.fileList.push(res.data.url);
						}else{
							this.$emit('success',res.data.url);
						}
					}
				}else{
					this.$u.toast('上传失败！');
				}
			}
		// #endif
	}
};
</script>

<style lang="scss">
.fa-file {
	background-color: #f4f5f6;
	width: 150rpx;
	height: 150rpx;
	border-radius: 10rpx;
	.select-color {
		color: #606266;
	}
	text {
		font-size: 40rpx;
	}
}
.fa-flex {
	display: flex;
	flex-direction: column;
	align-items: center;
	position: relative;
	.u-delete-icon {
		background-color: #fa3534;
		width: 45rpx;
		height: 45rpx;
		border-radius: 100px;
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		top: 15rpx;
		right: 15rpx;
	}
}
</style>
