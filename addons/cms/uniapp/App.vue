<script>
import md5Libs from "uview-ui/libs/function/md5";
export default {
	onLaunch: async function() {
		console.log('uview 版本', this.$u.config.v);
		// #ifdef H5
		if(window.location.hash != ''){
			 let search = window.location.search.substring(1);
			    try{
			        if(search.indexOf('hashpath') != -1){
			            let sea = JSON.parse('{"' + decodeURIComponent(search).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"') + '"}');
			            if(sea.hashpath && sea.code && sea.state){
			                window.location.href = window.location.origin + window.location.pathname+'#'+sea.hashpath+'?code='+sea.code+'&state='+sea.state
			            }
			        }
			    }catch(e){
			        //TODO handle the exception
			    }
		}
		// #endif

		let res = await this.$api.getConfig();
		if (!res.code) {
			return;
		}
		//主题做缓存
		let theme_key = md5Libs.md5(JSON.stringify(res.data.theme));
		if(!this.vuex_theme.key || this.vuex_theme.key != theme_key){
			this.$u.vuex('vuex_theme', {key:theme_key,value:res.data.theme});
		}
		this.$u.vuex('vuex_config', res.data);
		// #ifdef APP-PLUS
		let tabbar = this.$u.deepClone(this.vuex_config.tabbar);
		if (!tabbar) {
			this.$util.setTabbar(this.vuex_tabbar);
			return;
		}
		let num = 0;
		for (let i in tabbar.list) {
			try {
				let imagepath = await this.$util.getCachedImage(tabbar.list[i].image);
				let selectedImage = await this.$util.getCachedImage(tabbar.list[i].selectedImage);
				tabbar.list[i].image = imagepath;
				tabbar.list[i].selectedImage = selectedImage;
				num = i;
			} catch (e) {
				console.log(e);
			}
		}
		//全部下载成功使用服务器的导航，否则启用本地备用导航
		if (parseInt(num) + 1 == tabbar.list.length) {
			this.$util.setTabbar(tabbar);
		} else {
			this.$util.setTabbar(this.vuex_tabbar);
		}
		// #endif
	},
	onShow: function() {
		console.log('App Show');
	},
	onHide: function() {
		console.log('App Hide');
	}
};
</script>

<style lang="scss">
@import 'uview-ui/index.scss';
/*每个页面公共css */
.u-bg-white {
	background-color: #ffffff;
}
.u-text-weight {
	font-weight: bold;
}

.u-line-height {
	line-height: 50rpx;
}
//数据为空的样式
.fa-empty {
	padding-top: 45%;
}

.share-btn {
	padding: 0;
	margin: 0;
	border: 0;
	background-color: transparent;
	line-height: inherit;
	border-radius: 0;
	font-size: inherit;
	color: #999;
}
.share-btn::after {
	border: none;
}

// #ifdef MP-BAIDU
.u-radio__icon-wrap,.u-checkbox__icon-wrap{
	line-height: 0;
}
// #endif

</style>
