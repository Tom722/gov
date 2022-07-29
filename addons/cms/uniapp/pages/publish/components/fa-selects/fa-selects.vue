<template>
	<view class="selects">
		<view class="" style="min-height: 70rpx;" @click="show = true">
			<rich-text :class="!lists_lable ? 'richColor' : ''" :nodes="nodes(lists_lable || '请选择' + title)"></rich-text>
		</view>
		<u-popup v-model="show" :popup="false" mode="bottom" height="600" @close="close">
			<view class="u-flex u-flex-column">
				<view class="fa-column u-flex-1 u-flex fa-scroll">
					<scroll-view scroll-y="true" :style="[{ height: scrollHg + 'px', width: '100vw' }]">
						<!-- 多选-->
						<view v-if="checkeType == 'selects'">
							<checkbox-group>
								<u-cell-item :arrow="false" v-for="(item, index) in list" :key="index" @click.self="selectCell(index)">
									<view slot="title"><rich-text :nodes="nodes(item[showField])"></rich-text></view>
									<checkbox
										slot="right-icon"
										shape="square"
										:class="item.disabled == true ? 'fa-disabled' : ''"
										:checked="item.checked"
										:color="theme.bgColor"
										:disabled="item.disabled == true"
									></checkbox>
								</u-cell-item>
							</checkbox-group>
						</view>
						<!-- 单选 -->
						<view class="" v-else>
							<u-radio-group style="width: 100%;" v-model="radio_value">
								<u-cell-item :arrow="false" v-for="(item, index) in list" :key="index" @click.self="selectCell(index)">
									<view slot="title"><rich-text :nodes="nodes(item[showField])"></rich-text></view>
									<u-radio slot="right-icon" :active-color="theme.bgColor" :name="item[keyField]" :disabled="item.disabled == true"></u-radio>
								</u-cell-item>
							</u-radio-group>
						</view>
					</scroll-view>
				</view>
				<view class="fa-column select-footer u-text-center" v-if="checkeType == 'selects'">
					<u-gap height="10" bg-color="#eaeaec"></u-gap>
					<view class="u-p-10 u-flex u-row-around">
						<view class="u-flex-1" v-if="checkeType == 'selects'" @click="clearAll"><text>清空</text></view>
						<!-- <view class="u-flex-1" @click="allSelect"> -->
						<!-- <text>全选</text> -->
						<!-- </view> -->
						<view class="u-flex-1" @click="confirm"><text>确定</text></view>
					</view>
				</view>
				<view class="fa-column select-footer u-text-center" v-if="checkeType != 'selects'">
					<u-gap height="5" bg-color="#eaeaec"></u-gap>
					<view class="u-p-10 u-flex u-row-around">
						<view class="u-flex-1" @click="close"><text>取消</text></view>
					</view>
				</view>
			</view>
		</u-popup>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-selects',
	mixins: [Emitter],
	props: {
		value: {
			type: [String, Number],
			default: false
		},
		//数据源
		faList: {
			type: [Object, Array, String],
			default: ''
		},
		//选择类型
		checkeType: {
			type: String,
			default: 'select'
		},
		title: {
			type: String,
			default: ''
		},
		//显示的字段
		showField: {
			type: String,
			default: 'name'
		},
		//取值得字段
		keyField: {
			type: String,
			default: 'id'
		},		
		//默认的值
		showValue: {
			type: [String, Number],
			default: ''
		}
	},
	computed: {
		nodes() {
			return title => {
				return [
					{
						name: 'div',
						children: [
							{
								type: 'text',
								text: title
							}
						]
					}
				];
			};
		}
	},
	watch: {
		faList: {
			immediate: true,
			handler(val) {
				if (this.$u.test.array(val)) {
					if (val.length > 0 && this.$u.test.object(val[0])) {
						this.list = JSON.parse(JSON.stringify(val));
					} else {
						this.list = [];
						for (let i in val) {
							this.list.push({
								name: val[i],
								id: i
							});
						}
					}
				} else if (this.$u.test.object(val)) {
					this.list = [];
					for (let i in val) {
						this.list.push({
							name: val[i],
							id: i
						});
					}
				}
				if (this.checkeType == 'selects') {
					this.list.forEach((item, index) => {
						this.$set(this.list[index], 'checked', false);
					});
				}
				// 单选的默认值
				if (this.showValue && this.checkeType == 'select') {
					this.list.forEach((item, index) => {
						if (item[this.keyField] == this.showValue) {
							this.radio_value = this.showValue;
							this.lists_lable = item[this.showField];
						}
					});
				}
				// 多选的默认值
				if (this.showValue && this.checkeType == 'selects') {
					let arr = this.showValue.split(',');
					let lables = [];
					this.list.forEach((item, index) => {
						arr.forEach(id => {
							if (item[this.keyField] == id) {
								this.$set(this.list[index], 'checked', !this.list[index].checked);
								lables.push(item[this.showField]);
							}
						});
					});
					this.lists_lable = lables.join(',');
				}
			}
		},
		//显示高度
		show(newValue, oldValue) {
			if (newValue) {
				this.show = true;
				this.$nextTick(() => {
					setTimeout(() => {
						uni.createSelectorQuery()
							.in(this)
							.select('.fa-scroll')
							.boundingClientRect(rect => {
								console.log(rect);
								if (rect) {
									this.scrollHg = rect.height;
								}
							})
							.exec();
					}, 100); //在百度直接获取不到，需要延时
				});
			} else {
				this.sendChange();
			}
		}
	},
	data() {
		return {
			show: false,
			radio_value: '',
			checkbox_value: '',
			scrollHg: 200,
			lists_lable: '',
			list: []
		};
	},
	methods: {
		close() {
			this.show = false;
		},
		selectCell(index) {
			if (this.list[index].disabled == true) {
				return;
			}
			if (this.checkeType == 'selects') {
				this.$set(this.list[index], 'checked', !this.list[index].checked);
			} else {
				//单选值确定
				this.radio_value = this.list[index][this.keyField] || '';
				this.lists_lable = this.list[index][this.showField] || '';
				this.$emit('input', this.radio_value);				
				this.close();
				setTimeout(() => {
					this.dispatch('u-form-item', 'on-form-blur', this.radio_value);
				}, 50);
			}
		},
		//多选的确定
		confirm() {
			let lable = [];
			let ids = [];
			this.list.forEach(item => {
				if (item.checked) {
					lable.push(item.name);
					ids.push(item.id);
				}
			});
			this.lists_lable = lable.join(',');
			this.checkbox_value = ids.join(',');
			this.$emit('input', this.checkbox_value);
			setTimeout(() => {
				this.dispatch('u-form-item', 'on-form-blur', this.checkbox_value);
			}, 50);
			this.close();
		},
		//全选
		allSelect() {
			this.list.map(item => {
				item.checked = true;
			});
		},
		//清空
		clearAll() {
			this.list.map(item => {
				item.checked = false;
			});
		},
		//派发事件
		sendChange() {
			setTimeout(() => {
				if (this.checkeType == 'select') {
					this.dispatch('u-form-item', 'on-form-change', this.radio_value);
				} else {
					this.dispatch('u-form-item', 'on-form-change', this.checkbox_value);
				}
			}, 50);
		}
	}
};
</script>

<style lang="scss" scoped>
.selects {
	width: 100%;
}
.richColor {
	color: #909399;
}
.u-flex-column {
	flex-direction: column;
	height: 100%;
	.fa-column {
		width: 100%;
	}
}
</style>
