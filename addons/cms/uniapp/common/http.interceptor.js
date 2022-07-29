//免登录接口
let noLoginUrl = [
	'/addons/cms/api.common/init',
	'/addons/cms/api.common/captcha',
	'/addons/cms/api.ems/send',
	'/addons/cms/api.sms/send',
	'/addons/cms/api.archives/index',
	'/addons/cms/api.archives/detail',
	'/addons/cms/api.login/login',
	'/addons/cms/api.login/mobilelogin',
	'/addons/cms/api.login/register',
	'/addons/cms/api.login/resetpwd',
	'/addons/cms/api.login/wxLogin',
	'/addons/cms/api.login/appLogin',
	'/addons/cms/api.login/getWechatMobile',
	'/addons/cms/api.my/aboutus',
	'/addons/cms/api.my/agreement',
	'/addons/third/api/getAuthUrl',
	'/addons/third/api/callback',
	'/addons/third/api/account',
	'/addons/cms/api.search/index',
	'/addons/cms/api.tag/index',
	'/addons/cms/api.common/getCategory',
	'/addons/cms/api.user/getSigned',
	'/addons/cms/api.user/userInfo',
	'/addons/cms/api.comment/index',
	'/addons/vip/api.index/index',
	'/addons/cms/api.diyform/formList',
	'/addons/cms/api.diyform/show',
	'/addons/cms/api.diyform/index',
	'/addons/cms/api.common/selectpage',
	'/addons/cms/api.diyform/postForm',
	'/addons/cms/api.special/special',
	'/addons/cms/api.special/index',
	'/addons/cms/api.page/detail'
];

//设置session_id
const getSessionId = function(vm) {
	let session = vm.$util.getDb('session');
	if (!session) {
		let guid = vm.$u.guid();
		vm.$util.setDb('session', guid);
		return guid;
	}
	return session;
}

// 这里的vm，就是我们在vue文件里面的this，所以我们能在这里获取vuex的变量，比如存放在里面的token
// 同时，我们也可以在此使用getApp().globalData，如果你把token放在getApp().globalData的话，也是可以使用的
const install = (Vue, vm) => {
	let url = 'http://www.fa.com'
	// #ifdef H5
	if (typeof window.fastUrl !== 'undefined') {
		url = window.fastUrl;
	}
	// #endif
	Vue.prototype.$u.http.setConfig({
		baseUrl: url,
		header: {
			'content-type': 'application/json'
		},
		originalData: true
	});
	// 请求拦截，配置Token等参数
	Vue.prototype.$u.http.interceptor.request = (config) => {
		//在需要登录的接口，请求前判断token 是否存在,不存在则到登录
		let url = config.url.split('?').shift();
		console.log(noLoginUrl.includes(url), url)
		if (!noLoginUrl.includes(url) && !vm.vuex_token) {
			vm.$u.route('/pages/login/mobilelogin');
			return false;
		}
		config.header.token = vm.vuex_token;
		//设置session_id
		config.header.sid = getSessionId(vm);
		config.header.uid = vm.vuex_user.id || 0;
		const res = uni.getSystemInfoSync();
		config.header.platform = res.platform || '';
		config.header.model = res.model || '';
		config.header.brand = res.brand || '';
		config.header['x-requested-with'] = 'xmlhttprequest';
		if (config.method == 'POST') {
			config.data['__token__'] = vm.vuex__token__;
		}
		return config;
	}
	// 响应拦截，判断状态码是否通过
	Vue.prototype.$u.http.interceptor.response = (res) => {
		//返回__token__,设置
		if (res.header && res.header.__token__) {
			vm.$u.vuex('vuex__token__', res.header.__token__);
		}
		let result = res.data || {};
		if (result.data && result.data.__token__) {
			vm.$u.vuex('vuex__token__', result.data.__token__);
		}
		switch (result.code) {
			case 1:
			case 0:
				return result;
				break;
			case 401:
				//需要登录的接口，当token 过期时，到登录页面
				vm.$u.vuex('vuex_token', '');
				vm.$u.route('/pages/login/mobilelogin');
				return result;
				break;
			case 403: //没有权限访问
				uni.showToast({
					icon: "none",
					title: result.msg
				})
				return result;
				break;
			default:
				if (res.statusCode == 200) {
					return res.data;
				} else {
					console.error(res)
					vm.$u.toast('网络请求错误！');
					return false;
				}
		}

	}
}

export default {
	install
}
