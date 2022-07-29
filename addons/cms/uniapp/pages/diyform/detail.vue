<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar :title="diyform.title || '详情'" ref="navbar"></fa-navbar>
		<view class="u-p-30">
			<block v-for="(item, index) in fieldsList" :key="index">
				<!--  -->
				<view class="u-p-b-30" v-if="['string', 'text', 'number', 'city'].indexOf(item.type) != -1">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">{{ item.value }}</view>
				</view>
				<!-- 编辑器 -->
				<view class="u-p-b-30" v-if="item.type == 'editor'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">
						<u-parse :html="item.value" @linkpress="navigate"></u-parse>
					</view>
				</view>
				<!-- 时间 -->
				<view class="u-p-b-30" v-if="['date', 'time', 'datetime', 'datetimerange'].indexOf(item.type) != -1">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">{{ item.value }}</view>
				</view>
				<!-- 数组 -->
				<view class="u-p-b-30" v-if="item.type == 'array'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="u-p-t-30">
						<u-table>
							<u-tr class="u-tr">
								<u-th class="u-th" v-text="item.setting.key"></u-th>
								<u-th class="u-th" v-text="item.setting.value"></u-th>
							</u-tr>
							<u-tr class="u-tr" v-for="(arr, ak) in item.value" :key="ak">
								<u-td class="u-td">{{ ak }}</u-td>
								<u-td class="u-td">{{ arr }}</u-td>
							</u-tr>
						</u-table>
					</view>
				</view>
				<!-- 单选 -->
				<view class="u-p-b-30" v-if="item.type == 'radio' || item.type == 'select'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">{{ item.content_list[item.value] }}</view>
				</view>
				<!-- 多选 -->
				<view class="u-p-b-30" v-if="item.type == 'checkbox' || item.type == 'selects'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">{{ item | checkboxmat }}</view>
				</view>
				<!-- 开关 -->
				<view class="u-p-b-30" v-if="item.type == 'switch'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="content u-tips-color">{{ item.value }}</view>
				</view>
				<!-- 单图 -->
				<view class="u-p-b-30" v-if="item.type == 'image'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="contents u-flex u-flex-wrap">
						<image :src="item.value" @click="preview(item.value)" mode="aspectFill"></image>
					</view>
				</view>
				<!-- 多图 -->
				<view class="u-p-b-30" v-if="item.type == 'images'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="contents u-flex u-flex-wrap">
						<image v-for="(res, ids) in item.value" :src="res" :key="res" mode="aspectFill" @click="preview(item.value, ids)"></image>
					</view>
				</view>
				<!-- 单文件 -->
				<view class="u-p-b-30" v-if="item.type == 'file'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="file u-flex u-flex-wrap">
						<view class="opeate u-text-center" @click="download(item.value, item.name)">
							<view class="u-text-weight">{{ item.value | fileNameMat }}</view>
							<view class="">下载</view>
						</view>
					</view>
					<view class="u-m-t-30" v-if="downtips && downame == item.name">
						<u-alert-tips type="warning" title="下载成功,保存路径为:" :close-able="true" :show="downtips" :description="description" @close="downtips = false"></u-alert-tips>
					</view>
				</view>
				<!-- 多文件 -->
				<view class="u-p-b-30" v-if="item.type == 'files'">
					<view class="title u-text-weight">{{ item.title }}:</view>
					<view class="file u-flex u-flex-wrap">
						<view class="opeate u-text-center" v-for="(file, fk) in item.value" :key="fk" @click="download(file, item.name)">
							<view class="u-text-weight">{{ file | fileNameMat }}</view>
							<view class="">下载</view>
						</view>
					</view>
					<view class="u-m-t-30" v-if="downtips && downame == item.name">
						<u-alert-tips type="warning" title="下载成功,保存路径为:" :close-able="true" :show="downtips" :description="description" @close="downtips = false"></u-alert-tips>
					</view>
				</view>
			</block>
		</view>
		<u-gap height="120" bg-color="#fff"></u-gap>
		<view class="u-p-30 footer u-border-top u-flex u-row-around" v-if="diyform.isedit==1">
			<!-- <view class="u-flex u-row-center btn" :style="{ backgroundColor: theme.bgColor, color: theme.color }" @click="collection(diydata.id,'diyform')">
				<u-icon name="heart-fill"></u-icon>
				<text class="u-m-l-10">收藏</text>
			</view> -->
			<view class="u-flex u-row-center btn" :style="{ backgroundColor: theme.bgColor, color: theme.color }" @click="edit">
				<u-icon name="edit-pen"></u-icon>
				<text class="u-m-l-10">编辑</text>
			</view>
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	import { vote } from '@/common/fa.mixin.js';
	var _self;
	export default {
		mixins: [vote],
		created() {
			_self = this;
		},
		onLoad(e) {
			let query = e || {};
			this.form_id = query.form_id || 0;
			this.diyname = query.diyname || '';
			this.getformShow();
		},
		data() {
			return {
				form_id: 0,
				diyname: '',
				diydata: {},
				diyform: {},
				fieldsList: [],
				downtips: false,
				description: '',
				downame: ''
			};
		},
		methods: {
			edit() {
				this.$u.route('/pages/publish/diyform', {
					diyname: this.diyname,
					form_id: this.form_id
				});
			},
			getformShow() {
				this.$api.formShow({
					form_id: this.form_id,
					diyname: this.diyname
				}).then(res => {
					if (res.code) {
						this.diydata = res.data.diydata;
						this.fieldsList = res.data.fieldsList;
						this.diyform = res.data.diyform;
					} else {
						this.$u.toast(res.msg);
					}
				});
			},
			preview(img, index = 0) {
				let arr = [];
				if (typeof img == 'string') {
					arr.push(img);
				} else {
					arr = img;
				}
				uni.previewImage({
					current: index,
					urls: arr,
					longPressActions: {
						itemList: ['发送给朋友', '保存图片', '收藏'],
						success: function(data) {},
						fail: function(err) {
							console.log(err.errMsg);
						}
					}
				});
			},
			download(url, name) {
				let that = this;
				that.downame = name;
				// #ifndef H5
				uni.downloadFile({
					url: url,
					success: res => {
						if (res.statusCode === 200) {
							// #ifndef H5
							uni.saveFile({
								tempFilePath: res.tempFilePath,
								success: function(res) {
									that.downtips = true;
									that.description = res.savedFilePath;
								},
								fail(err) {
									console.log(err);
								}
							});
							// #endif
						}
					}
				});
				// #endif
				// #ifdef H5
				window.open(url);
				// #endif
			}
		},
		filters: {
			checkboxmat: function(item) {
				let val = item.value.split(',');
				let arr = [];
				val.forEach(it => {
					arr.push(item.content_list[it]);
				});
				return arr.join(',');
			},
			fileNameMat: function(value) {
				let index1 = value.lastIndexOf('.');
				let index2 = value.length;
				return value.substring(index1, index2);
			}
		}
	};
</script>

<style lang="scss">
	page {
		background-color: #ffffff;
	}

	.content {
		background-color: #f3f3f3;
		padding: 30rpx;
		margin-top: 20rpx;
		border-radius: 5rpx;
	}

	.contents {
		display: flex;

		image {
			width: 150rpx;
			height: 150rpx;
			margin-top: 30rpx;
			margin-right: 20rpx;
		}
	}

	.file {
		.opeate {
			background-color: #e3e3e3;
			width: 150rpx;
			height: 150rpx;
			line-height: 70rpx;
			margin-top: 30rpx;
			margin-right: 20rpx;
		}
	}

	.u-alert-desc {
		word-break: break-word;
	}

	.footer {
		position: fixed;
		width: 100%;
		bottom: 0;
		background-color: #ffffff;

		.btn {
			border-radius: 50rpx;
			height: 60rpx;
			line-height: 60rpx;
			width: 80vw;
		}
	}
</style>
