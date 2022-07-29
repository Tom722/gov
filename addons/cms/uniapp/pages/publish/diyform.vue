<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar :title="diyform.title" ref="navbar"></fa-navbar>
		<view class="u-p-30" v-if="showForm">
			<u-form :model="form" :rules="rules" ref="uForm" :errorType="errorType">
				<!-- 自定义字段 -->
				<block v-for="(item, index) in fields" :key="index">
					<!-- 字符 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'string'"
					>
						<u-input type="text" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 文本 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'text'"
					>
						<u-input type="textarea" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 编辑器 -->
					<!-- #ifdef MP-WEIXIN || H5 || APP-PLUS -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'editor'"
					>
						<fa-editor v-model="form[item.name]" :html="item.value"></fa-editor>
					</u-form-item>
					<!-- #endif -->
					<!-- 数组 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'array'"
					>
						<fa-array
							:faKey="item.setting.key"
							:faVal="item.setting.value"
							v-model="form[item.name]"
							:showValue="item.value || item.content_list"
						></fa-array>
					</u-form-item>
					<!-- 日期 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'date'"
					>
						<u-input
							:border="border"
							type="select"
							:select-open="showPicker && mode == 'date'"
							v-model="form[item.name]"
							:placeholder="'请选择' + item.title"
							@click="selectPicker('date', item.name)"
						></u-input>
					</u-form-item>
					<!-- 时间 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'time'"
					>
						<u-input
							:border="border"
							type="select"
							:select-open="showPicker && mode == 'time'"
							v-model="form[item.name]"
							:placeholder="'请选择' + item.title"
							@click="selectPicker('time', item.name)"
						></u-input>
					</u-form-item>
					<!-- 日期时间 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'datetime'"
					>
						<u-input
							:border="border"
							type="select"
							:select-open="showPicker && mode == 'datetime'"
							v-model="form[item.name]"
							:placeholder="'请选择' + item.title"
							@click="selectPicker('datetime', item.name)"
						></u-input>
					</u-form-item>
					<!-- 日期区间 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'datetimerange'"
					>
						<u-input
							:border="border"
							type="select"
							:select-open="calendarShow"
							v-model="form[item.name]"
							:placeholder="'请选择' + item.title"
							@click="
								calendarShow = true;
								time_field = item.name;
							"
						></u-input>
					</u-form-item>
					<!-- 数字 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'number'"
					>
						<u-input type="number" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 多选框 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'checkbox'"
					>
						<fa-check-radio :faList="item.content_list" v-model="form[item.name]" :checkValue="item.value || item.defaultvalue"></fa-check-radio>
					</u-form-item>
					<!-- 单选框 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'radio'"
					>
						<fa-check-radio :faList="item.content_list" type="radio" v-model="form[item.name]" :checkValue="item.value || item.defaultvalue"></fa-check-radio>
					</u-form-item>
					<!-- 列表单选 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'select'"
					>
						<fa-selects
							:fa-list="item.content_list"
							:title="item.title"
							:checkeType="item.type"
							:showValue="item.value || item.defaultvalue"
							v-model="form[item.name]"
						></fa-selects>
					</u-form-item>
					<!-- 列表多选 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'selects'"
					>
						<fa-selects
							:fa-list="item.content_list"
							:title="item.title"
							:checkeType="item.type"
							:showValue="item.value || item.defaultvalue"
							v-model="form[item.name]"
						></fa-selects>
					</u-form-item>
					<!-- 单图 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'image'"
					>
						<fa-upload-image v-model="form[item.name]" :file-list="item.value"></fa-upload-image>
					</u-form-item>
					<!-- 多图 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'images'"
					>
						<fa-upload-image v-model="form[item.name]" imgType="many" :file-list="item.value"></fa-upload-image>
					</u-form-item>
					<!-- #ifdef APP-PLUS || H5 || MP-WEIXIN -->
					<!-- 单文件 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'file'"
					>
						<fa-upload-file v-model="form[item.name]" :isDom="true" :showValue="item.value"></fa-upload-file>
					</u-form-item>
					<!-- 多文件 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'files'"
					>
						<fa-upload-file v-model="form[item.name]" fileType="many" :isDom="true" :showValue="item.value"></fa-upload-file>
					</u-form-item>
					<!-- #endif -->
					<!-- 开关 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'switch'"
					>
						<fa-switch v-model="form[item.name]" :defvalue="item.value || 0"></fa-switch>
					</u-form-item>
					<!-- 关联城市 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'city'"
					>
						<u-input
							:border="border"
							type="select"
							:select-open="cityShow"
							v-model="form[item.name]"
							:placeholder="'请选择' + item.title"
							@click="
								cityShow = true;
								city_field = item.name;
							"
						></u-input>
					</u-form-item>
					<!-- 关联单选 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'selectpage'"
					>
						<fa-selectpages
							:fa-id="item.id"
							:title="item.title"
							:checkeType="item.type"
							:showField="item.setting.field"
							:keyField="item.setting.primarykey"
							:showValue="(form[item.name] ? form[item.name] : item.value) || item.defaultvalue"
							v-model="form[item.name]"
						></fa-selectpages>
					</u-form-item>
					<!-- 关联多选 -->
					<u-form-item
						:label-position="labelPosition"
						label-width="130"
						:prop="item.name"
						:label="item.title"
						:required="rules[item.name] && rules[item.name].length > 0"
						v-if="item.type == 'selectpages'"
					>
						<fa-selectpages
							:fa-id="item.id"
							:title="item.title"
							:checkeType="item.type"
							:showField="item.setting.field"
							:keyField="item.setting.primarykey"
							:showValue="(form[item.name] ? form[item.name] : item.value) || item.defaultvalue"
							v-model="form[item.name]"
						></fa-selectpages>
					</u-form-item>
				</block>
				<block v-if="diyform.iscaptcha">
					<fa-captchaparts ref="captcha" label-position="top" :ident="diyform.id" :custom-style="{ paddingTop: '15rpx' }" v-model="form.captcha"></fa-captchaparts>
				</block>
			</u-form>
			<view class="u-p-30">
				<u-button type="primary" hover-class="none" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" shape="circle" @click="submit">
					提交
				</u-button>
			</view>
		</view>
		<u-picker v-model="showPicker" mode="time" :params="params" @confirm="pickerResult"></u-picker>
		<u-calendar v-model="calendarShow" mode="range" @change="calendarResult" max-date="3000-01-01"></u-calendar>
		<!-- 城市 -->
		<fa-citys v-model="cityShow" @city-change="cityResult"></fa-citys>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
import { formRule } from '@/common/fa.mixin.js';
import FaArray from './components/fa-array/fa-array.vue'
import FaCaptchaparts from './components/fa-captchaparts/fa-captchaparts.vue'
import FaCheckRadio from './components/fa-check-radio/fa-check-radio.vue'
import FaCitys from './components/fa-citys/fa-citys.vue'
import FaEditor from './components/fa-editor/fa-editor.vue'
import FaFile from './components/fa-file/fa-file.vue'
import FaSelectpages from './components/fa-selectpages/fa-selectpages.vue'
import FaSelects from './components/fa-selects/fa-selects.vue'
import FaSwitch from './components/fa-switch/fa-switch.vue'
import FaUploadFile from './components/fa-upload-file/fa-upload-file.vue'
import FaUploadImage from './components/fa-upload-image/fa-upload-image.vue'
export default {
	components:{
		FaArray,
		FaCheckRadio,
		FaCitys,
		FaEditor,
		FaFile,
		FaSelectpages,
		FaSelects,
		FaSwitch,
		FaUploadFile,
		FaUploadImage,
		FaCaptchaparts
	},
	mixins: [formRule],
	onLoad(e) {
		let query = e || {};
		this.diyname = query.diyname || '';
		this.form_id = query.form_id || '';
		this.init();
	},
	data() {
		return {
			diyname: '',
			form_id: '',
			labelPosition: 'top',
			border: false,
			errorType: ['message'],
			showForm: false,
			// 表单字段
			fields: [],
			//表单信息
			diyform: {},
			form: {},
			rules: {},

			calendarShow: false,
			showPicker: false,
			mode: '',
			time_field: '',
			params: {},
			cityShow: false,
			city_field: '',

			imageList: {}
		};
	},
	methods: {
		init() {
			this.$api.formField({ diyname: this.diyname, form_id: this.form_id }).then(res => {
				if (res.code) {
					this.diyform = res.data.diyform;
					this.fields = res.data.fields;

					//渲染自定义字段
					let custom_form = {};
					let rules = {
						title: [
							{
								required: true,
								message: '请输入标题',
								// 可以单个或者同时写两个触发验证方式
								trigger: ['change', 'blur']
							}
						]
					};
					this.fields.map(item => {
						// console.log(item)
						//表单赋值
						if (item.type == 'number') {
							custom_form[item.name] = item.value || item.defaultvalue || 0;
						} else {
							custom_form[item.name] = item.value || item.defaultvalue || '';
						}

						//单图赋值
						if (item.type == 'image') {
							if (item.value) {
								item.value = [
									{
										url: this.cdnurl(item.value)
									}
								];
							} else {
								item.value = [];
							}
						}
						//多图赋值
						if (item.type == 'images') {
							if (item.value) {
								let images = item.value.split(',');
								let urls = [];
								images.forEach(it => {
									urls.push({
										url: this.cdnurl(it)
									});
								});
								item.value = urls;
							} else {
								item.value = [];
							}
						}
						//单文件
						if (item.type == 'file') {
							item.value = item.value ? [item.value] : [];
						}
						//多文件
						if (item.type == 'files') {
							if (item.value) {
								item.value = item.value.split(',');
							} else {
								item.value = [];
							}
						}
						//追加自定义表单验证
						rules[item.name] = this.getRules(item);
					});
					//如果需要验证码
					if(this.diyform.iscaptcha){
						custom_form.captcha = '';
						rules.captcha = [{
							required: true,
							message: '请输入验证码',
							trigger: ['change', 'blur']
						}];
					}
					this.form = custom_form;
					this.rules = rules;

					this.showForm = true;
					//设置表单验证规则
					// console.log(this.form, this.rules,this.fields);
					this.$nextTick(() => {
						this.$refs.uForm.setRules(this.rules);
					});
				} else {
					this.$u.toast(res.msg);
					setTimeout(()=>{
						uni.navigateBack({
							delta:1
						})
					},1500)
				}
			});
		},
		//标签数据
		tagsChange(e) {
			this.$set(this.form, 'tags', e.join(','));
		},
		//时间显示
		selectPicker(mode, field) {
			this.mode = mode;
			this.time_field = field;
			switch (mode) {
				case 'date':
					this.params = {
						year: true,
						month: true,
						day: true,
						hour: false,
						minute: false,
						second: false
					};
					break;
				case 'time':
					this.params = {
						year: false,
						month: false,
						day: false,
						hour: true,
						minute: true,
						second: true
					};
					break;
				case 'datetime':
					this.params = {
						year: true,
						month: true,
						day: true,
						hour: true,
						minute: true,
						second: true
					};
					break;
			}
			this.showPicker = true;
		},
		//时间的选择结果
		pickerResult(e) {
			switch (this.mode) {
				case 'date':
					this.$set(this.form, this.time_field, e.year + '-' + e.month + '-' + e.day);
					break;
				case 'time':
					this.$set(this.form, this.time_field, e.hour + ':' + e.minute + ':' + e.second);
					break;
				case 'datetime':
					this.$set(this.form, this.time_field, e.year + '-' + e.month + '-' + e.day + ' ' + e.hour + ':' + e.minute + ':' + e.second);
					break;
			}
		},
		//时间范围选择的结果
		calendarResult(e) {
			this.$set(this.form, this.time_field, e.startDate + ' 00:00:00 - ' + e.endDate + ' 23:59:59');
		},
		//城市选择
		cityResult(e) {
			this.$set(this.form, this.city_field, e.province.label + '/' + e.city.label + '/' + e.area.label);
		},
		//提交
		submit: async function() {
			console.log('验证开始', this.form);
			//校验
			this.$refs.uForm.validate(valid => {
				if (valid) {
					console.log('验证通过', this.form);
					this.form.diyname = this.diyform.diyname;
					this.form.form_id = this.form_id;
					this.$api.postForm(this.form).then(res => {
						this.$u.toast(res.msg);
						if (res.code) {
							setTimeout(() => {
								uni.navigateBack({
									success: () => {
										let page = getCurrentPages().pop(); //跳转页面成功之后
										if (!page) {
											return;
										} else {
											page.onLoad(page.options); // page自带options对象.
										}
									}
								})
							}, 1500);
						}else{
							this.$refs.captcha.getCaptcha();
						}
					});
				} else {
					console.log('验证失败', this.form);
				}
			});
		}
	}
};
</script>

<style></style>
