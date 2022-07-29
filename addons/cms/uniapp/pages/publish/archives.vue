<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="发布文章" ref="navbar"></fa-navbar>
		<view class="u-p-30" v-if="showForm">
			<u-form :model="form" :rules="rules" ref="uForm" :errorType="errorType">
				<!-- 系统字段 -->
				<u-form-item :label-position="labelPosition" label="副栏目：" label-width="130" v-if="contributefields.indexOf('channel_ids') != -1">
					<fa-selects :fa-list="secondList" title="请选择副栏目" checkeType="selects" :showValue="archives && archives.channel_ids" v-model="form.channel_ids"></fa-selects>
				</u-form-item>
				<!--  -->
				<u-form-item :label-position="labelPosition" label="标题：" prop="title" :required="rules.title && rules.title.length > 0" label-width="130">
					<u-input :border="border" placeholder="请输入标题" v-model="form.title" type="text"></u-input>
				</u-form-item>
				<!--  -->
				<u-form-item :label-position="labelPosition" label="略缩图：" label-width="130" v-if="contributefields.indexOf('image') != -1">
					<fa-upload-image v-model="form.image" :file-list="imageList.image || []"></fa-upload-image>
				</u-form-item>
				<!--  -->
				<u-form-item :label-position="labelPosition" label="组图：" label-width="130" v-if="contributefields.indexOf('images') != -1">
					<fa-upload-image v-model="form.images" imgType="many" :file-list="imageList.images || []">
					</fa-upload-image>
				</u-form-item>
				<!--  -->
				<u-form-item :label-position="labelPosition" label="标签：" label-width="130" v-if="contributefields.indexOf('tags') != -1">
					<fa-tags v-model="form.tags" :tagList="archives && archives.tags"></fa-tags>
				</u-form-item>
				<!--  -->
				<!-- #ifdef MP-WEIXIN || H5 || APP-PLUS -->
				<u-form-item :label-position="labelPosition" label-width="130" label="内容：" v-if="contributefields.indexOf('content') != -1">
					<fa-editor v-model="form.content" :html="field_values.content"></fa-editor>
				</u-form-item>
				<!-- #endif -->
				<!--  -->
				<u-form-item :label-position="labelPosition" label-width="130" label="关键字：" v-if="contributefields.indexOf('keywords') != -1">
					<u-input type="text" :border="border" placeholder="请填写关键字" v-model="form.keywords"></u-input>
				</u-form-item>
				<!--  -->
				<u-form-item :label-position="labelPosition" label-width="130" label="描述：" v-if="contributefields.indexOf('description') != -1">
					<u-input type="textarea" :border="border" placeholder="请填写描述" v-model="form.description"></u-input>
				</u-form-item>

				<!-- 自定义字段 -->
				<block v-for="(item, index) in fields" :key="index">
					<!-- 字符 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'string'">
						<u-input type="text" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 文本 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'text'">
						<u-input type="textarea" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 编辑器 -->
					<!-- #ifdef MP-WEIXIN || H5 || APP-PLUS -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'editor'">
						<fa-editor v-model="form[item.name]" :html="field_values[item.name]"></fa-editor>
					</u-form-item>
					<!-- #endif -->
					<!-- 数组 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'array' && item.name != 'downloadurl'">
						<fa-array :faKey="item.setting.key" :faVal="item.setting.value" v-model="form[item.name]" :showValue="field_values[item.name]"></fa-array>
					</u-form-item>
					<!-- 数组（下载） -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'array' && item.name == 'downloadurl'">
						<fa-array-download v-model="form[item.name]" :showValue="field_values[item.name]" :contentList="item.content_list"></fa-array-download>
					</u-form-item>
					<!-- 日期 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'date'">
						<u-input :border="border" type="select" :select-open="showPicker && mode == 'date'" v-model="form[item.name]" :placeholder="'请选择' + item.title" @click="selectPicker('date', item.name)"></u-input>
					</u-form-item>
					<!-- 时间 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'time'">
						<u-input :border="border" type="select" :select-open="showPicker && mode == 'time'" v-model="form[item.name]" :placeholder="'请选择' + item.title" @click="selectPicker('time', item.name)"></u-input>
					</u-form-item>
					<!-- 日期时间 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'datetime'">
						<u-input :border="border" type="select" :select-open="showPicker && mode == 'datetime'" v-model="form[item.name]" :placeholder="'请选择' + item.title" @click="selectPicker('datetime', item.name)"></u-input>
					</u-form-item>
					<!-- 日期区间 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'datetimerange'">
						<u-input :border="border" type="select" :select-open="calendarShow" v-model="form[item.name]" :placeholder="'请选择' + item.title" @click="
								calendarShow = true;
								time_field = item.name;
							"></u-input>
					</u-form-item>
					<!-- 数字 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'number'">
						<u-input type="number" :border="border" :placeholder="'请填写' + item.title" v-model="form[item.name]"></u-input>
					</u-form-item>
					<!-- 多选框 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'checkbox'">
						<fa-check-radio :faList="item.content_list" v-model="form[item.name]" :checkValue="field_values[item.name] || item.defaultvalue"></fa-check-radio>
					</u-form-item>
					<!-- 单选框 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'radio'">
						<fa-check-radio :faList="item.content_list" type="radio" v-model="form[item.name]" :checkValue="field_values[item.name] || item.defaultvalue"></fa-check-radio>
					</u-form-item>
					<!-- 列表单选 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'select'">
						<fa-selects :fa-list="item.content_list" :title="item.title" :checkeType="item.type" :showValue="field_values[item.name] || item.defaultvalue" v-model="form[item.name]">
						</fa-selects>
					</u-form-item>
					<!-- 列表多选 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'selects'">
						<fa-selects :fa-list="item.content_list" :title="item.title" :checkeType="item.type" :showValue="field_values[item.name] || item.defaultvalue" v-model="form[item.name]">
						</fa-selects>
					</u-form-item>
					<!-- 单图 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'image'">
						<fa-upload-image v-model="form[item.name]" :file-list="imageList[item.name] || []">
						</fa-upload-image>
					</u-form-item>
					<!-- 多图 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'images'">
						<fa-upload-image v-model="form[item.name]" imgType="many" :file-list="imageList[item.name] || []"></fa-upload-image>
					</u-form-item>
					<!-- #ifdef APP-PLUS || H5 || MP-WEIXIN -->
					<!-- 单文件 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'file'">
						<fa-upload-file v-model="form[item.name]" :isDom="true" :showValue="imageList[item.name] || []">
						</fa-upload-file>
					</u-form-item>
					<!-- 多文件 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'files'">
						<fa-upload-file v-model="form[item.name]" fileType="many" :isDom="true" :showValue="imageList[item.name] || []"></fa-upload-file>
					</u-form-item>
					<!-- #endif -->
					<!-- 开关 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'switch'">
						<fa-switch v-model="form[item.name]" :defvalue="field_values[item.name] || 0"></fa-switch>
					</u-form-item>
					<!-- 关联城市 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'city'">
						<u-input :border="border" type="select" :select-open="cityShow" v-model="form[item.name]" :placeholder="'请选择' + item.title" @click="
								cityShow = true;
								city_field = item.name;
							"></u-input>
					</u-form-item>
					<!-- 关联单选 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'selectpage'">
						<fa-selectpages :fa-id="item.id" :title="item.title" :checkeType="item.type" :showField="item.setting.field" :keyField="item.setting.primarykey" :showValue="(form[item.name] ? form[item.name] : field_values[item.name]) || item.defaultvalue" v-model="form[item.name]"></fa-selectpages>
					</u-form-item>
					<!-- 关联多选 -->
					<u-form-item :label-position="labelPosition" label-width="130" :prop="item.name" :label="item.title" :required="rules[item.name] && rules[item.name].length > 0" v-if="item.type == 'selectpages'">
						<fa-selectpages :fa-id="item.id" :title="item.title" :checkeType="item.type" :showField="item.setting.field" :keyField="item.setting.primarykey" :showValue="(form[item.name] ? form[item.name] : field_values[item.name]) || item.defaultvalue" v-model="form[item.name]"></fa-selectpages>
					</u-form-item>
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
	import FaArrayDownload from './components/fa-array-download/fa-array-download.vue'
	import FaCheckRadio from './components/fa-check-radio/fa-check-radio.vue'
	import FaCitys from './components/fa-citys/fa-citys.vue'
	import FaEditor from './components/fa-editor/fa-editor.vue'
	import FaFile from './components/fa-file/fa-file.vue'
	import FaSelectpages from './components/fa-selectpages/fa-selectpages.vue'
	import FaSelects from './components/fa-selects/fa-selects.vue'
	import FaSwitch from './components/fa-switch/fa-switch.vue'
	import FaTags from './components/fa-tags/fa-tags.vue'
	import FaUploadFile from './components/fa-upload-file/fa-upload-file.vue'
	import FaUploadImage from './components/fa-upload-image/fa-upload-image.vue'
	export default {
		components: {
			FaArray,
			FaArrayDownload,
			FaCheckRadio,
			FaCitys,
			FaEditor,
			FaFile,
			FaSelectpages,
			FaSelects,
			FaSwitch,
			FaTags,
			FaUploadFile,
			FaUploadImage
		},
		mixins: [formRule],
		onLoad(e) {
			let query = e || {};
			this.archives_id = query.archives_id || 0;
			this.getChannelFields();
		},
		data() {
			return {
				labelPosition: 'top',
				border: false,
				errorType: ['message'],
				showForm: false,
				contributefields: [], //系统可投稿字段
				fields: [], //自定义可投稿字段
				// 系统表单字段
				form: {},
				rules: {},
				secondList: [],
				calendarShow: false,
				showPicker: false,
				mode: '',
				time_field: '',
				params: {},
				cityShow: false,
				city_field: '',
				archives_id: '',
				field_values: {}, //附表的数据
				archives: {}, //主表的数据
				imageList: {} //图片展示
			};
		},
		methods: {
			//获取字段
			getChannelFields() {
				this.$api.getChannelFields({
					channel_id: this.vuex_channel_id,
					archives_id: this.archives_id
				}).then(res => {
					if (res.code) {
						this.secondList = res.data.secondList;
						this.field_values = res.data.values;
						this.archives = res.data.archives;
						let sys = res.data.contributefields;
						let custom = res.data.fields;
						//渲染系统字段
						let form_sys = {
							channel_ids: '',
							title: '',
							channel_id: this.vuex_channel_id,
							id: this.archives_id
						};
						for (let i in sys) {
							form_sys[sys[i]] = '';
						}
						//渲染自定义字段
						let custom_form = {};
						let rules = {
							title: [{
								required: true,
								message: '请输入标题',
								// 可以单个或者同时写两个触发验证方式
								trigger: ['change', 'blur']
							}]
						};
						let number_arr = [];
						custom.map(item => {
							// console.log(item)
							custom_form[item.name] = item.defaultvalue || '';
							if (item.type == 'number') {
								number_arr.push(item.name);
							}
							//追加自定义表单验证
							rules[item.name] = this.getRules(item);
						});

						this.form = Object.assign(form_sys, custom_form);
						this.rules = rules;
						//赋值
						this.contributefields = sys;
						this.fields = custom;
						//渲染表单数据
						this.setFormData(res.data.archives, res.data.values, number_arr);
						this.showForm = true;
						//设置表单验证规则
						this.$nextTick(() => {
							this.$refs.uForm.setRules(this.rules);
						});
					}
				});
			},
			//编辑渲染数据
			setFormData(archives, values, number_arr) {
				//给from值
				for (let i in this.form) {
					if (values && values[i]) {
						if (number_arr.indexOf(i) != -1) {
							this.form[i] = values[i] + '';
						} else {
							this.form[i] = values[i];
						}
					}
					if (archives && archives[i]) {
						this.form[i] = archives[i];
					}
				}
				//系统图片赋值
				if (archives && archives.image) {
					this.imageList.image = [{
						url: this.cdnurl(archives.image)
					}];
				}
				if (archives && archives.images) {
					let images = archives.images.split(',');
					let urls = [];
					images.forEach(item => {
						urls.push({
							url: this.cdnurl(item)
						});
					});
					this.imageList.images = urls;
				}
				//自定义图片字段赋值
				this.fields.forEach(item => {
					if (item.type == 'image' && values[item.name]) {
						this.imageList[item.name] = [{
							url: this.cdnurl(values[item.name])
						}];
					}
					if (item.type == 'images' && values[item.name]) {
						let images = values[item.name].split(',');
						let urls = [];
						images.forEach(item => {
							urls.push({
								url: this.cdnurl(item)
							});
						});
						this.imageList[item.name] = urls;
					}
					if (item.type == 'file') {
						this.imageList[item.name] = values[item.name] ? [values[item.name]] : [];
					}
					if (item.type == 'files') {
						this.imageList[item.name] = values[item.name] ? values[item.name].split(',') : [];
					}
				});
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
						this.$set(this.form, this.time_field, e.year + '-' + e.month + '-' + e.day + ' ' + e.hour + ':' + e
							.minute + ':' + e.second);
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
						this.$api.archivesPost(this.form).then(res => {
							this.$u.toast(res.msg);
							if (res.code) {
								setTimeout(() => {
									uni.navigateBack({
										delta: 2,
										success: () => {
											let page = getCurrentPages().pop();
											if (!page) {
												return;
											} else {
												page.onLoad(page.options);
											}
										}
									})
								}, 1500);
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

<style lang="scss">
	page {
		background-color: #ffffff;
	}
</style>
