<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar :title="name" ref="navbar"></fa-navbar>
		<!-- 分类 -->
		<view class="u-bg-white u-border-bottom">
			<fa-orderby :tabList="tabList" showField="title" :activeColor="theme.bgColor" @change="orderChange"></fa-orderby>
		</view>
		<fa-article-item :archivesList="pageList"></fa-article-item>
		<!-- 空数据 -->
		<view class="u-p-60" v-if="!pageList.length">
			<u-empty text="没有更多了"></u-empty>
		</view>
		<!-- 加载更多 -->
		<view class="u-p-30" v-if="pageList.length"><u-loadmore bg-color="#f4f6f8" :status="status" /></view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	export default {
		onLoad(e) {
			let query = e || {};
			this.name = query.name || '标签';
			this.getTagIndex();
		},
		data() {
			return {
				name:'',
				tabList:[],
				page:1,
				pageList:[],
				current: 0,
				has_more: false,
				scrollTop: 0,
				status: 'nomore',
				orderby:'default',
				orderway:'asc',
				is_order:false
			}
		},
		methods: {
			getTagIndex(){
				this.$api.tagIndex({name:this.name,page:this.page,orderby:this.orderby,orderway:this.orderway}).then(res=>{
					this.status = 'nomore';
					if(res.code){
						//有数据过渡切换
						if(this.is_order){
							this.pageList = [];
							this.is_order = false;
						}
						this.tabList = res.data.orderList;
						this.pageList = [...this.pageList,...res.data.pageList.data];
						this.has_more = res.data.pageList.last_page>res.data.pageList.current_page;
					}
				})
			},
			orderChange(e){
				this.page = 1;
				this.is_order = true;
				this.orderby = e.orderby;
				this.orderway = e.orderway;
				this.getTagIndex();
			}
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		onReachBottom() {
			if (this.has_more) {
				this.status = 'loading';
				this.page = ++this.page;
				this.getTagIndex();
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}
</style>
