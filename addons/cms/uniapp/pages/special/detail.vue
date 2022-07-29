<template>
	<view class="special">
		<!-- 顶部导航 -->
		<fa-navbar title="专题" ref="navbar"></fa-navbar>
		<view class="thumb">
			<image src="https://picsum.photos/id/450/1110/300" mode="aspectFill"></image>			
		</view>
		<view class="u-p-30 u-border-bottom">
			<view class="title u-font-36"><text v-text="info.title"></text></view>
			<view class="tags u-m-t-30"><u-tag size="mini" type="info" :text="info.label"></u-tag></view>
			<view class="intro u-tips-color u-p-30">
				<u-icon name="arrow-right-double"></u-icon>
				<text class="u-m-l-5" v-text="info.intro"></text>
			</view>
		</view>
		<!-- 专题文章列表 -->
		<fa-article-item :archives-list="archivesList"></fa-article-item>
		<!-- 为空 -->
		<view class="u-m-t-60 u-p-t-60" v-if="is_empty"><u-empty text="暂无内容展示" mode="list"></u-empty></view>
		<!-- 加载更多 -->
		<view class="u-p-30" v-if="archivesList.length"><u-loadmore bg-color="#ffffff" :status="status" /></view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
export default {
	onLoad(e) {
		this.id = e.id || '';
		this.diyname = e.diyname || '';
		this.getSpecial();
	},
	data() {
		return {
			id: '',
			diyname: '',
			info: {},
			archivesList: [],
			page: 1,
			has_more: false,
			scrollTop: 0,
			status: 'nomore',
			is_empty: false
		};
	},
	methods: {
		getSpecial() {
			this.$api.specialIndex({ 
				id: this.id, 
				diyname: this.diyname ,
				page:this.page
			}).then(({ code, data:res, msg }) => {
				if (code) {
					this.info = res.special;
					this.status = 'nomore';
					uni.stopPullDownRefresh();
					if (!code) {
						return;
					}
					const pageList  = res.archivesList;					
					if (this.is_update) {
						this.is_update = false;
						this.archivesList = [];
					}
					this.is_show = true;
					this.has_more = pageList.current_page < pageList.last_page;
					this.archivesList = [...this.archivesList, ...pageList.data];
					this.is_empty = !this.archivesList.length;
				}
			});
		}
	},
	onPageScroll(e) {
		this.scrollTop = e.scrollTop;
	},
	//下拉刷新
	onPullDownRefresh() {		
		this.is_update = true;
		this.page = 1;
		this.getSpecial();
	},
	onReachBottom() {
		if (this.has_more) {
			this.status = 'loading';
			this.page = ++this.page;
			this.getSpecial();
		}
	}
};
</script>

<style lang="scss" scoped>
.special {
	.thumb {		
		image {
			width: 100%;
			height: 300rpx;
		}		
	}
	.intro{
		background-color: #F4F6F8;
		margin-top: 30rpx;
	}
}
</style>
