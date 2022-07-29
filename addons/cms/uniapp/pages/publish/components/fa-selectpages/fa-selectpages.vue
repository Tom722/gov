<template>
	<view class="selectpage">
		<view class="" v-if="checkeType == 'selectpage'">
			<u-input type="select" :select-open="show" v-model="page_lable" :placeholder="'请选择' + title" @click="show = true"></u-input>
		</view>
		<view class="" v-if="checkeType == 'selectpages'">
			<view class="select-pages u-flex u-flex-wrap" @click="show = true">
				<view class="u-m-r-10" v-for="(tag, tak) in pagesLable" :key="tak">
					<u-tag :text="tag[showField]" :bg-color="lightColor" :border-color="faBorderColor" :color="theme.bgColor" type="success" />
				</view>
				<view class="u-light-color" v-text="'请选择' + title" v-if="!pagesLable.length"></view>
			</view>
		</view>
		<u-popup v-model="show" :popup="false" @close="close" mode="bottom" height="700">
			<view class="u-flex u-flex-column">
				<view class="fa-column u-p-l-30 u-p-r-30 u-p-t-20 u-p-b-20 u-border-bottom">
					<u-search placeholder="搜索" v-model="q_word" :show-action="false"></u-search>
				</view>
				<view class="fa-column u-flex-1 u-flex fa-scroll">
					<scroll-view scroll-y="true" :style="{ height: scrollHg + 'px', width: '100vw' }" @scrolltolower="goLower">
						<!-- 多选 -->
						<view v-if="checkeType == 'selectpages'">
							<checkbox-group>
								<u-cell-item :arrow="false" v-for="(item, index) in list" :key="index" :title="item[showField]" @click.self="selectCell(index)">
									<checkbox
										slot="right-icon"
										shape="square"
										:color="theme.bgColor"
										:value="item[keyField] + ''"
										:checked="item.checked"
									></checkbox>
								</u-cell-item>
							</checkbox-group>
						</view>
						<!-- 单选 -->
						<view class="" v-else>
							<u-radio-group v-model="radio_value">
								<u-cell-item :arrow="false" v-for="(item, index) in list" :key="index" :title="item[showField]" @click.self="selectCell(index)">
									<u-radio slot="right-icon" :active-color="theme.bgColor" :name="item[keyField] + ''"></u-radio>
								</u-cell-item>
							</u-radio-group>
						</view>
						<view class="u-p-10"><u-loadmore :status="status" /></view>
					</scroll-view>
				</view>
				<view class="fa-column select-footer u-text-center">
					<u-gap height="10" bg-color="#eaeaec"></u-gap>
					<view class="u-p-10 u-flex u-row-around">
						<view class="u-flex-1" v-if="checkeType == 'selectpages'" @click="clearAll"><text>清空</text></view>
						<!-- <view class="u-flex-1" @click="allSelect"> -->
						<!-- <text>全选</text> -->
						<!-- </view> -->
						<view class="u-flex-1" @click="confirm">
							<text>{{ checkeType == 'selectpages' ? '确定' : '取消' }}</text>
						</view>
					</view>
				</view>
			</view>
		</u-popup>
	</view>
</template>

<script>
import Emitter from '@/uview-ui/libs/util/emitter.js';
export default {
	name: 'fa-selectpages',
	mixins: [Emitter],
	props: {
		value:{
			type:[String,Number],
			default:''
		},
		//查询id
		faId: {
			type: [Number, String],
			default: ''
		},
		//显示字段
		showField: {
			type: String,
			default: ''
		},
		//保存的键
		keyField: {
			type: String,
			default: ''
		},
		//提示
		title: {
			type: String,
			default: ''
		},		
		checkeType: {
			type: String,
			default: 'selectpage'
		},
		//默认的值
		showValue: {
			type: [String, Number],
			default: ''
		}
	},
	watch: {
		//弹出高度
		show(newValue, oldValue) {
			if (newValue) {
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
				if (!this.list.length) {
					this.page = 1;
					this.getSelectPages();
				}
			} else {
				this.sendChange();
			}
		},
		//默认的数据
		showValue: {
			immediate: true,
			handler(val) {
				//第一次渲染默认就好
				if (val && !this.isFirst) {
					this.isFirst = true;
					this.getInitSelect();
				}
			}
		},
		//搜索
		q_word(newValue, oldValue) {
			this.list = [];
			this.page = 1;
			this.getSelectPages();
		}
	},
	data() {
		return {
			show: false,
			list: [],
			radio_value: '',
			scrollHg: 0,
			q_word: '',
			pageNum: 0,
			page: 1,
			totalPage: 0,
			status: 'loadmore',

			isFirst: false,

			page_lable: '',
			pagesLable: [], //初始化的值
			ids_ing: [], //已经加载的值
			ids: []
		};
	},
	methods: {
		close() {
			this.show = false;
		},
		//初始化值
		getInitSelect() {
			if (this.showValue) {
				let param = {
					id: this.faId,
					pageNumber: this.page,
					q_word: this.q_word,
					keyValue: this.showValue
				};
				this.$api.selectpage(param).then(res => {
					if (this.checkeType == 'selectpage') {
						this.page_lable = res.list[0][this.showField];
						this.radio_value = res.list[0][this.keyField] + '';
					} else {
						this.pagesLable = res.list;
						let ids = [];
						res.list.forEach(item => {
							ids.push(item[this.keyField]);
						});
						this.ids = ids;
					}
				});
			}
		},
		//获取数据
		getSelectPages() {
			if (!this.faId) {
				return;
			}			
			let param = { id: this.faId, pageNumber: this.page, q_word: this.q_word };
			this.$api.selectpage(param).then(res => {
				this.status = res.total == 0 || this.page >= this.totalPage ? 'nomore' : 'loadmore';
				let list = [];
				if (this.checkeType == 'selectpages') {
					res.list.forEach(item => {
						item.checked = this.ids.indexOf(item[this.keyField]) != -1;
						list.push(item);
						//已选的
						this.pagesLable.forEach(it => {
							if (item[this.keyField] == it[this.keyField]) {
								this.ids_ing.push(it); //在已选的已经加载的数据
							}
						});
					});
				} else {
					list = res.list;
				}
				//一页的数量，取第一次就好
				if (!this.pageNum) {
					this.pageNum = list.length;
				}
				this.totalPage = Math.ceil(res.total / this.pageNum);
				this.list = [...this.list, ...list];
			});
		},
		//选择
		selectCell(index) {
			if (this.checkeType == 'selectpages') {
				this.$set(this.list[index], 'checked', !this.list[index].checked);
			} else {
				//单选
				this.radio_value = this.list[index][this.keyField];
				this.page_lable = this.list[index][this.showField];
				this.$emit('input', this.radio_value);				
				this.close();
				setTimeout(() => {
					this.dispatch('u-form-item', 'on-form-blur', this.radio_value);
				}, 50);
			}
		},
		//加载更多
		goLower(e) {
			if (this.page == this.totalPage) {
				return;
			}
			this.status = 'loading';
			this.page++;
			this.getSelectPages();
		},
		//多选确定
		confirm() {
			if (this.checkeType == 'selectpages') {
				//先取未加载的数据的集合
				let data = this.pagesLable.filter(item => {					
					if (
						this.$u.test.empty(this.ids_ing) || this.ids_ing.find(it => {
							return item[this.keyField] == it[this.keyField];
						})
					) {
						return false;
					} else {
						return true;
					}
				});
				
				let ids = [];
				let res = [];
				
				this.list.forEach(item => {
					if (item.checked) {
						ids.push(item[this.keyField]);
						res.push(item);
					}
				});
				
				//追加未加载的选项
				data.forEach(item => {
					ids.push(item[this.keyField]);
					res.push(item);
				});
				
				this.pagesLable = res;
				this.ids = ids;
								
				this.$emit('input', ids.join(','));
				setTimeout(() => {
					this.dispatch('u-form-item', 'on-form-blur', ids.join(','));
				}, 50);
			}
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
			this.pagesLable = [];
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
		// upPage(){
		// 	if(this.page==1){
		// 		return;
		// 	}
		// 	this.page--;
		// },
		// nextPage(){
		// 	if(this.page == this.totalPage){
		// 		return;
		// 	}
		// 	this.page++;

		// 	if(!this.list[this.page-1]){
		// 		this.getSelectPages();
		// 	}
		// }
	}
};
</script>

<style lang="scss" scoped>
.selectpage {
	width: 100%;
}
.select-pages {
	width: 100%;
	border: 1px solid #dcdfe6;
	padding: 5rpx 10rpx;
}
.u-flex-column {
	flex-direction: column;
	height: 100%;
	.fa-column {
		width: 100%;
	}
}
</style>
