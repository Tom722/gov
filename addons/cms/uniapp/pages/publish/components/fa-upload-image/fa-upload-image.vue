<template>
	<view>
		<u-upload
			:action="vuex_config.upload.uploadurl"
			:file-list="fileList"
			:header="header"
			:form-data="formdata"
			@on-uploaded="successUpload"
			@on-error="errorUpload"
			@on-remove="remove"
			:max-count="imgType=='single'?1:99"
			width="150"
			height="150"
		></u-upload>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-upload-image',
	mixins: [Emitter],
	props: {
		value:{
			type:String,
			default:''
		},
		imgType: {
			type: String,
			default: 'single'
		},
		fileList:{
			type:Array,
			default(){
				return []
			}
		}
	},
	created() {
		this.header = {
			token: this.vuex_token || '',
			uid: this.vuex_user.id || 0
		};
		let isObj = this.$u.test.object(this.vuex_config.upload.multipart);
		if (isObj) {
			this.formdata = this.vuex_config.upload.multipart;
		}
	},
	data() {
		return {
			header: {},
			formdata: {},

		};
	},
	methods: {
		successUpload(e) {
			console.log(e)
			this.changes(e)
		},
		remove(index, lists, name){
			this.changes(lists)
		},
		changes(e){
			console.log(e)
			let urls = [];
			e.map(item => {
				//编辑时，已存在的，response不存在
				if(!item.response){
					urls.push(item.url)
				}else if (item.response.code) {
					urls.push(item.response.data.url);
				}
			});
			let value = urls.join(',');
			this.$emit('input', value);
			setTimeout(() => {
				this.dispatch('u-form-item', 'on-form-blur', value);
			}, 50);
		},
		errorUpload(e) {
			this.$u.toast('上传失败了！');
		}
	}
};
</script>

<style lang="scss"></style>
