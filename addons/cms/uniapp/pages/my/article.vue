<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="我发布的文章" ref="navbar"></fa-navbar>
		<!-- 分类 -->
		<view class="u-border-top" v-if="isTab">
			<!-- #ifdef MP-BAIDU -->
			<fa-u-tabs :list="tabList" :active-color="theme.bgColor" :bar-width="tabwidth" name="name" :is-scroll="true" :current="current" @change="change"></fa-u-tabs>
			<!-- #endif -->
			<!-- #ifndef MP-BAIDU -->
			<u-tabs :list="tabList" :active-color="theme.bgColor" :bar-width="tabwidth" name="name" :is-scroll="true" :current="current" @change="change"></u-tabs>
			<!-- #endif -->
		</view>
		<!-- 文章列表 -->
		<u-swipe-action v-for="(res, index) in list" :show="res.show" :index="index" :key="res.id" @click="click" @open="open(index)" :options="options">
			<view class="article" @click="goDetail(res)">
				<view class="left"><image :src="res.image" mode="aspectFill"></image></view>
				<view class="right">
					<view class="content u-line-2">
					<view class="u-m-r-10 flag" v-if="res.status == 'hidden'">
						<u-tag text="审核中" type="error" size="mini" /></view>
						<text :style="[cmsTitleStyle(res.style)]">{{ res.title }}</text>
					</view>
					<view class="u-flex u-flex-wrap">
						<view class="u-m-r-10" v-for="(item, ik) in res.taglist" :key="ik">
							<u-tag
								:text="item.name"
								shape="circle"
								:bg-color="lightColor"
								:border-color="faBorderColor"
								:color="theme.bgColor"
								type="info"
								size="mini"
								mode="light"
							/>
						</view>
					</view>
					<view class="u-p-t-10 u-tips-color">发布时间：{{ res.publishtime | date('yyyy-mm-dd hh:MM:ss') }}</view>
				</view>
			</view>
		</u-swipe-action>
		<!-- 加载更多 -->
		<view class="u-p-t-30 u-p-b-30" v-if="list.length"><u-loadmore bg-color="#ffffff" :status="has_more ? status : 'nomore'" /></view>
		<!-- 空数据 -->
		<view class="fa-empty" v-if="!list.length"><u-empty></u-empty></view>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
export default {
	onLoad() {
		this.is_update = true;
		this.getmyArchives();
	},
	data() {
		return {
			isTab: false,
			tabwidth: 40,
			tabList: [],
			current: 0,
			scrollTop: 0,
			list: [],
			status: 'loadmore',
			has_more: false,
			page: 1,
			channel_id: 0,
			is_update: false,
			options: []
		};
	},
	methods: {
		change(index) {
			//重设Bar宽度
			this.tabwidth = this.$util.strlen(this.tabList[index].name) * 30;
			this.current = index;
			this.channel_id = this.tabList[index].id;
			this.page = 1;
			this.is_update = true;
			this.getmyArchives();
		},
		getmyArchives() {
			this.$api.myArchives({ page: this.page, channel_id: this.channel_id }).then(res => {
				this.status = 'loadmore';
				if (!res.code) {
					this.$u.toast(res.msg);
					return;
				}
				if (this.is_update) {
					this.list = [];
					this.is_update = false;
				}
				if (this.tabList.length <= res.data.channelList.length) {
					this.tabList = [{ name: '全部', id: 0 }, ...res.data.channelList];
					this.options = [
						{
							text: '编辑',
							style: {
								backgroundColor: this.theme.bgColor
							}
						},
						{
							text: '删除',
							style: {
								backgroundColor: '#dd524d'
							}
						}
					];
				}
				let archivesList = res.data.archivesList;
				archivesList.data.map(function(item) {
					item.show = false;
				});
				this.list = this.list.concat(archivesList.data);
				this.has_more = archivesList.last_page > archivesList.current_page;
				if (!this.isTab) {
					this.isTab = true;
				}
			});
		},
		click(index, index1) {
			if (index1 == 1) {
				this.$api.deleteArchives({id:this.list[index].id}).then(res=>{
					this.$u.toast(res.msg);
					if(res.code){
						this.list.splice(index, 1);
					}
				})
			} else {
				this.$u.route('/pages/publish/channel',
					{ archives_id: this.list[index].id }
				);
			}
		},
		open(index) {
			this.list[index].show = true;
			this.list.map((val, idx) => {
				if (index != idx) this.list[idx].show = false;
			});
		},
		goDetail(item) {
			if (item.model_id == 2) {
				this.$u.route('/pages/product/detail',
					{ id: item.id }
				);
			} else {
				this.$u.route('/pages/article/detail',
					{ id: item.id }
				);
			}
		}
	},
	onPageScroll(e) {
		this.scrollTop = e.scrollTop;
	},
	onReachBottom() {
		if (this.has_more) {
			this.status = 'loading';
			this.page++;
			this.getmyArchives();
		}
	}
};
</script>

<style lang="scss">
page {
	background-color: #ffffff;
}
</style>
<style lang="scss" scoped>
.article {
	display: flex;
	padding: 30rpx;
	border-bottom: 1px solid #eee;
	.left {
		image {
			width: 200rpx;
			height: 150rpx;
			background-color: #f2f2f2;
			border-radius: 10rpx;
		}
	}
	.right {
		flex: 1;
		padding-left: 20rpx;
		font-size: 28rpx;

		.content {
			margin-bottom: 10rpx;
			.flag{
				display: inline-block;
			}
		}
	}
}
</style>
