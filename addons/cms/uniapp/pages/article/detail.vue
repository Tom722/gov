<template>
	<view class="">
		<!-- 顶部导航 -->
		<fa-navbar title="详情" ref="navbar"></fa-navbar>
		<!-- 内容 -->
		<view class="" v-if="isRender">
			<view class="u-p-30 u-bg-white">
				<view class="u-font-40" :style="[cmsTitleStyle(archivesInfo.style)]"><text v-text="archivesInfo.title"></text></view>
				<view class="u-m-t-20 u-font-22 u-tips-color"><text v-text="archivesInfo.create_date"></text></view>
				<!--  -->
				<view class="u-flex u-row-between u-m-t-20 u-font-24 detail-tag">
					<view class="u-flex">
						<view class="u-flex u-col-center u-m-r-30" v-if="archivesInfo.user" @click="toUser(archivesInfo.user_id)">
							<u-avatar size="40" :src="archivesInfo.user.avatar"></u-avatar>
							<view class="u-font-22 u-m-l-10 u-line-1" style="max-width: 70px;">
								{{ archivesInfo.user.nickname }}
							</view>
						</view>
						<view class="">
							<u-icon name="thumb-up-fill" color="#aaa" size="20"></u-icon>
							<text class="u-m-l-5 u-m-r-5" v-text="archivesInfo.likes"></text>
							点赞
						</view>
						<view class="u-m-l-30">
							<u-icon name="chat-fill" color="#aaa" size="20"></u-icon>
							<text class="u-m-l-5 u-m-r-5" v-text="archivesInfo.comments"></text>
							评论
						</view>
						<view class="u-m-l-30">
							<u-icon name="eye-fill" color="#aaa" size="20"></u-icon>
							<text class="u-m-l-5 u-m-r-5" v-text="archivesInfo.views"></text>
							浏览
						</view>
					</view>
					<view class="">
						<!-- #ifdef MP-WEIXIN -->
						<button class="share-btn" open-type="share">
							<u-icon name="share-fill"></u-icon>
							<text class="u-p-l-5">分享</text>
						</button>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<button class="share-btn" @click="copyUrl">
							<u-icon name="share-fill"></u-icon>
							<text class="u-p-l-5">分享</text>
						</button>
						<!-- #endif -->
						<!-- #ifdef APP-PLUS -->
						<button class="share-btn" @click="openShare">
							<u-icon name="share-fill"></u-icon>
							<text class="u-p-l-5">分享</text>
						</button>
						<!-- #endif -->
					</view>
				</view>
			</view>
			<view class="u-flex u-flex-wrap u-p-l-30 u-p-r-30 u-p-t-30" v-if="isShow">
				<view class="product-images" v-for="(item, index) in imagesList" :key="index">
					<u-image width="100%" height="220" :src="item" @click="lookImage(index)"></u-image>
				</view>
			</view>
			<u-gap height="10" bg-color="#f4f6f8"></u-gap>
			<view class="u-p-30 u-bg-white u-line-height">
				<u-parse :html="archivesInfo.content" :tag-style="vuex_parse_style" :domain="vuex_config.upload ? vuex_config.upload.cdnurl : ''" @linkpress="navigate"></u-parse>
			</view>

			<view class="u-p-30 u-bg-white">
				<view class="u-flex u-flex-wrap">
					<view class="u-m-r-10 u-m-b-15" v-for="(item, index) in archivesInfo.taglist" :key="index">
						<u-tag :text="item.name" shape="circle" type="info" mode="light" @click="goTag(item.name)" />
					</view>
				</view>
				<view class="u-flex u-row-right u-m-t-5">
					<view class="">
						<u-button type="primary" hover-class="none" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" size="mini" shape="circle" @click="collection(id, 'archives')">
							<u-icon name="heart-fill"></u-icon>
							<text class="u-p-l-5" v-text="`收藏`"></text>
						</u-button>
					</view>
					<view class="u-m-l-15">
						<u-button type="primary" hover-class="none" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" size="mini" shape="circle" @click="likes">
							<u-icon name="thumb-up"></u-icon>
							<text class="u-p-l-5" v-text="`点赞（${archivesInfo.likes || 0}）`"></text>
						</u-button>
					</view>
				</view>
			</view>
			<!-- 下载 -->
			<view class="u-flex u-border-top u-p-30 u-bg-white" v-if="archivesInfo.model_id == 3 && (archivesInfo.ispaid || archivesInfo.price == 0)">
				<view class="u-m-l-15" v-for="(item, index) in downloadurl" :key="index">
					<u-button hover-class="none" type="primary" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" size="mini" shape="circle" @click="download(item)">
						<text class="u-p-r-5" v-text="item.name == 'local' ? '本地下载' : item.name == 'baidu' ? '百度下载' : '其他下载'"></text>
					</u-button>
				</view>
			</view>
			<view class="u-p-30" v-if="downtips">
				<u-alert-tips type="warning" title="下载成功,保存路径为:" :close-able="true" :show="downtips" :description="description" @close="downtips = false"></u-alert-tips>
			</view>
			<view class="u-border-top u-bg-white u-p-30 u-flex u-row-center" v-if="archivesInfo.price > 0 && !archivesInfo.ispaid">
				<u-button hover-class="none" type="primary" size="medium" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color, width: '60vw' }" v-if="!vuex_token" @click="goLogin">
					请登录再进行付费
				</u-button>
				<u-button hover-class="none" type="primary" size="medium" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color, width: '60vw' }" v-else @click="goPay">
					立即付费阅读
				</u-button>
			</view>

			<u-gap height="20" bg-color="#f4f6f8"></u-gap>
			<view class="u-bg-white u-p-30" v-if="archivesInfo.iscomment">
				<view class="u-p-b-10 u-tips-color">发表评论</view>
				<view class="">
					<u-input v-model="content" type="textarea" placeholder="请输入评论内容" :border="false" />
				</view>
				<view class="u-flex u-row-center">
					<u-button hover-class="none" type="primary" size="medium" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color, width: '60vw' }" v-if="!vuex_token" @click="goLogin">
						立即登录
					</u-button>
					<u-button hover-class="none" type="primary" size="medium" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color, width: '60vw' }" v-else @click="submit">
						立即评论
					</u-button>
				</view>
			</view>
			<u-gap height="20" bg-color="#f4f6f8"></u-gap>
			<view class="u-p-30 u-bg-white">
				<view class="u-p-b-10 u-tips-color">评论列表</view>
				<view class="comment" v-for="(item, index) in commentList" :key="item.id">
					<view class="left" @click="toUser(item.user_id)">
						<image :src="item.user && item.user.avatar" mode="aspectFill"></image>
					</view>
					<view class="right">
						<view class="top">
							<view class="u-light-color">
								<u-icon name="account-fill" color="#c0c4cc"></u-icon>
								<text class="u-p-l-10 name u-line-1">{{ item.user && item.user.nickname }}</text>
								<text class="u-m-l-30">{{ item.create_date }}</text>
							</view>
							<view class="replay" @click="replay(item)">
								<u-icon name="chat" :size="30"></u-icon>
								<view class="opeate u-m-l-5">回复</view>
							</view>
						</view>
						<view class="content">
							<rich-text :nodes="item.content"></rich-text>
						</view>
					</view>
				</view>
				<view class="" v-if="!commentList.length && archivesInfo.iscomment">
					<u-empty text="暂无评论"></u-empty>
				</view>
				<view class="" v-if="!archivesInfo.iscomment">
					<u-empty text="评论已关闭"></u-empty>
				</view>
			</view>
			<!-- 支付 -->
			<fa-payment ref="faPayment" :article-id="id" :article-title="archivesInfo.title" :money="archivesInfo.price" :vip="(archivesInfo.channel && archivesInfo.channel.vip) || 0" @success="paySuccess"></fa-payment>
			<!-- #ifdef APP-PLUS -->
			<view class="">
				<fa-app-share ref="faShare" :title="archivesInfo.title" :summary="archivesInfo.title" :imageUrl="archivesInfo.image" :href="archivesInfo.fullurl"></fa-app-share>
			</view>
			<!-- #endif -->
		</view>
		<!-- 其他下载 -->
		<u-modal v-model="dowshow">
			<view class="u-p-30 u-flex u-row-center u-col-center">
				<u-link :href="downurl" font-size="35">点我下载</u-link>
				<text class="u-m-l-20 u-tips-color" v-if="baucode" v-text="'复制提取码：' + baucode" @click="copydown"></text>
			</view>
		</u-modal>
		<!-- 回到顶部 -->
		<u-back-top :scroll-top="scrollTop" :icon-style="{ color: theme.bgColor }" :custom-style="{ backgroundColor: lightColor }"></u-back-top>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
	import { vote } from '@/common/fa.mixin.js';
	// #ifdef H5
	import { weixinShare } from '@/common/fa.weixin.mixin.js';
	// #endif
	export default {
		mixins: [
			vote,
			// #ifdef H5
			weixinShare
			// #endif
		],
		onLoad(e) {
			let query = e || {};
			this.id = query.id || 0;
			this.diyname = query.diyname || '';
			this.getArchivesDetail();
		},
		onShow() {
			// #ifdef MP-BAIDU
			if (this.archivesInfo.id) {
				this.setPagesInfo();
			}
			// #endif
		},
		computed: {
			isShow() {
				return this.archivesInfo.images != '';
			},
			isPay() {
				return parseInt(this.archivesInfo.price) > 0;
			}
		},
		watch: {
			content(newValue, oldValue) {
				if (!newValue) {
					this.pid = 0;
				}
			}
		},
		data() {
			return {
				id: 0,
				archivesInfo: {},
				channelInfo: {},
				commentList: [],
				imagesList: [],
				downloadurl: {},
				content: '',
				isRender: false,
				dowshow: false,
				downtips: false,
				pid: 0,
				description: '',
				downkey: '',
				downurl: '',
				baucode: '',
				diyname: '',
				scrollTop: 0,
				page: 1,
				has_more: true
			};
		},
		methods: {
			getArchivesDetail: async function() {
				let res = await this.$api.getArchivesDetail({
					id: this.id,
					diyname: this.diyname
				});
				if (!res.code) {
					this.$u.toast(res.msg || "文档未找到");
					setTimeout(() => {
						this.$refs.navbar.goBack();
					}, 1500);
					// this.$u.route({
					// 	type:'back',
					// 	delta: 1
					// });
					return;
				}
				this.archivesInfo = res.data.archivesInfo || {};
				this.channelInfo = res.data.channelInfo || [];
				this.commentList = res.data.commentList || [];
				this.downloadurl = res.data.archivesInfo.downloadurl || {};
				this.imagesList = res.data.archivesInfo.images.split(',');
				// #ifdef MP-WEIXIN
				this.$u.mpShare = {
					title: this.archivesInfo.title,
					imageUrl: this.archivesInfo.image,
					path: '/pages/article/detail?id=' + this.id
				};
				// #endif
				this.isRender = true;
				this.$u.vuex('vuex__token__', res.data.__token__);
				uni.setNavigationBarTitle({
					title: this.archivesInfo.title
				});
				// #ifdef MP-BAIDU
				this.setPagesInfo();
				// #endif
				// #ifdef H5
				if (this.$util.isWeiXinBrowser()) {
					this.wxShare({
						title: this.archivesInfo.title,
						desc: this.archivesInfo.description,
						link: window.location.href,
						img: this.archivesInfo.image
					});
				}
				// #endif
			},
			// #ifdef MP-BAIDU
			setPagesInfo() {
				let sitename = (this.vuex_config && this.vuex_config.sitename) || '';
				swan.setPageInfo({
					title: this.archivesInfo.title + '-' + sitename,
					articleTitle: this.archivesInfo.title + '-' + sitename,
					keywords: this.archivesInfo.keywords,
					description: this.archivesInfo.description,
					releaseDate: this.$u.timeFormat(this.archivesInfo.publishtime, 'yyyy-mm-dd hh:MM:ss'),
					image: this.archivesInfo.images,
					likes: this.archivesInfo.likes,
					comments: this.archivesInfo.comments,
					collects: this.archivesInfo.views,
					success: res => {
						console.log('setPageInfo success', res);
					},
					fail: err => {
						console.log('setPageInfo fail', err);
					}
				});
			},
			// #endif
			goCommentIndex() {
				this.$api.goCommentIndex({
					page: this.page,
					aid: this.archivesInfo.id
				}).then(res => {
					if (res.code == 1) {
						this.has_more = res.data.commentList.length > 0;
						this.commentList = [...this.commentList, ...res.data.commentList];
					}
				});
			},
			//回复
			replay(item) {
				if (!item.user) {
					this.$u.toast('用户不存在');
					return;
				}
				this.content = `@${item.user.nickname} `;
				this.pid = item.id;
			},
			//评论
			submit: async function() {
				if (!this.content) {
					this.$u.toast('请输入评论内容！');
					return;
				}
				let res = await this.$api.goCommentPost({
					content: this.content,
					aid: this.id,
					pid: this.pid //回复的用户上一条ID
				});
				this.$u.toast(res.msg);
				if (!res.code) {
					return;
				}
				this.content = '';
				if (res.data && res.data.comment) {
					this.commentList = [res.data.comment, ...this.commentList];
				}
			},
			//登录
			goLogin() {
				this.$u.route('/pages/login/mobilelogin');
			},
			toUser(user_id) {
				this.$u.route('/pages/user/user?user_id=' + user_id);
			},
			//支付
			goPay() {
				this.$refs.faPayment.show();
			},
			paySuccess() {
				this.getArchivesDetail();
			},
			//下载
			download(item) {
				let that = this;
				console.log(item)
				switch (item.name) {
					case 'local':
						// #ifndef H5
						uni.downloadFile({
							url: item.url,
							success: res => {
								if (res.statusCode === 200) {
									uni.saveFile({
										tempFilePath: res.tempFilePath,
										success: function(res) {
											that.downtips = true;
											that.description = res.savedFilePath;
										},
										fail: err => {
											this.$u.toast("存储文件失败");
										}
									});
								} else {
									this.$u.toast("下载失败，错误码：" + res.statusCode);
								}
							},
							fail: err => {
								this.$u.toast("下载失败");
							}
						});
						// #endif
						// #ifdef H5
						window.open(this.cdnurl(item.url));
						// #endif

						break;
					case 'baidu':
						// #ifdef APP-PLUS || H5
						if (item.password) {
							this.dowshow = true;
							this.downurl = item.url;
							this.baucode = item.password;
						} else {
							this.$u.vuex('vuex_webs', {
								title: '百度网盘下载',
								url: item.url
							});
							this.$u.route('/pages/webview/webview');
						}
						// #endif
						// #ifdef MP
						this.dowshow = true;
						this.downurl = item.url;
						this.baucode = item.password;
						// #endif
						break;
				}
			},
			copydown() {
				this.$util.uniCopy({
					content: this.baucode,
					success: () => {
						this.$u.toast('提取码复制成功！');
					}
				});
			},
			goTag(name) {

				let type = 'navigateTo';

				// #ifdef MP-WEIXIN
				//优化当pages超过10个时的处理
				let pages = getCurrentPages();
				type = pages.length >= 9 ? 'reLaunch' : type;
				// #endif

				this.$u.route({ url: '/pages/tag/tag', params: { name: name }, type: type });
			},
			// #ifdef APP-PLUS
			openShare() {
				this.$refs.faShare.show();
			}
			// #endif
		},
		onPageScroll(e) {
			this.scrollTop = e.scrollTop;
		},
		onReachBottom() {
			if (this.archivesInfo && this.archivesInfo.iscomment && this.has_more) {
				this.page += 1;
				this.goCommentIndex();
			}
		}
	};
</script>

<style lang="scss">
	page {
		background-color: #f4f6f8;
	}

	.detail-tag {
		color: #aaa;
	}

	.comment {
		background-color: #ffffff;
		display: flex;
		padding: 30rpx 0;

		.left {
			image {
				width: 64rpx;
				height: 64rpx;
				border-radius: 50%;
				background-color: #f2f2f2;
			}
		}

		.right {
			flex: 1;
			padding-left: 20rpx;

			.top {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 10rpx;

				.replay {
					display: flex;
					align-items: center;
					color: #9a9a9a;
					font-size: 26rpx;

					.opeate {
						margin-right: 4rpx;
						color: #9a9a9a;
					}
				}

				.name {
					max-width: 100rpx;
				}
			}

			.content {
				margin-bottom: 10rpx;
				word-break: break-word;
			}
		}
	}

	.comment:not(:last-child) {
		border-bottom: 1px solid #eee;
	}

	.product-images {
		width: 50%;
		margin-bottom: 30rpx;
	}

	.product-images:nth-child(2n) {
		padding-left: 15rpx;
	}

	.product-images:nth-child(2n + 1) {
		padding-right: 15rpx;
	}

	.u-alert-desc {
		word-break: break-word;
	}
</style>
