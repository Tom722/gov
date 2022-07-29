const upload = async function(vm, {
	// #ifdef APP-PLUS || H5
	files,
	// #endif
	// #ifdef H5
	file,
	// #endif
	// #ifdef MP-ALIPAY
	fileType,
	// #endif
	filePath,
	name,
	formData
}) {
	return new Promise((resolve, reject) => {
		uni.showLoading({
			mask: true,
			title: '上传中'
		});
		let data = {
			url: vm.vuex_config.upload.uploadurl,
			header: {
				token: vm.vuex_token || '',
				uid: vm.vuex_user.id || 0
			},
			name: 'file',
			complete: function() {
				uni.hideLoading();
			},
			success: uploadFileRes => {
				try {
					var res = uploadFileRes.data;
					if (vm.$u.test.jsonString(res)) {
						resolve(JSON.parse(res))
					}
					if (vm.$u.test.object(res)) {
						resolve(res)
					}
				} catch (e) {
					reject(uploadFileRes.data);
				}
			},
			fail: (e) => {
				reject(e);
			}
		};
		// #ifdef H5
		//有文件对象，一般是H5
		if (file) {
			data.file = file;
		}
		// #endif
		//文件路径
		if (filePath) {
			data.filePath = filePath;
		}
		let isObj = vm.$u.test.object(vm.vuex_config.upload.multipart);
		if (isObj && formData) {
			data.formData = Object.assign(formData, vm.vuex_config.upload.multipart);
		} else if (isObj) {
			data.formData = vm.vuex_config.upload.multipart;
		} else if (formData) {
			data.formData = formData;
		}
		uni.uploadFile(data);
	})

}
const install = (Vue, vm) => {
	vm.$api.getConfig = async (params = {}) => await vm.$u.get('/addons/cms/api.common/init', params);
	vm.$api.getCaptcha = async (params = {}) => await vm.$u.get('/addons/cms/api.common/captcha', params);
	vm.$api.getEmsSend = async (params = {}) => await vm.$u.post('/addons/cms/api.ems/send', params);
	vm.$api.getSmsSend = async (params = {}) => await vm.$u.post('/addons/cms/api.sms/send', params);
	vm.$api.getArchives = async (params = {}) => await vm.$u.get('/addons/cms/api.archives/index', params);
	vm.$api.getArchivesDetail = async (params = {}) => await vm.$u.post('/addons/cms/api.archives/detail', params);
	vm.$api.getArchivesVote = async (params = {}) => await vm.$u.post('/addons/cms/api.archives/vote', params);
	vm.$api.getArchivesOrder = async (params = {}) => await vm.$u.post('/addons/cms/api.archives/order', params);
	vm.$api.getChannel = async (params = {}) => await vm.$u.get('/addons/cms/api.archives/get_channel', params);
	vm.$api.getChannelFields = async (params = {}) => await vm.$u.get('/addons/cms/api.archives/get_channel_fields', params);
	vm.$api.archivesPost = async (params = {}) => await vm.$u.post('/addons/cms/api.archives/archives_post', params);
	vm.$api.myArchives = async (params = {}) => await vm.$u.get('/addons/cms/api.archives/my', params);
	vm.$api.deleteArchives = async (params = {}) => await vm.$u.post('/addons/cms/api.archives/delete', params);
	vm.$api.getUserIndex = async (params = {}) => await vm.$u.get('/addons/cms/api.user/index', params);
	vm.$api.getUserProfile = async (params = {}) => await vm.$u.post('/addons/cms/api.user/profile', params);
	vm.$api.goUserLogout = async (params = {}) => await vm.$u.get('/addons/cms/api.user/logout', params);
	vm.$api.goUserAvatar = async (params = {}) => await vm.$u.post('/addons/cms/api.user/avatar', params);
	vm.$api.getUserInfo = async (params = {}) => await vm.$u.post('/addons/cms/api.user/userInfo', params);
	vm.$api.getMyComment = async (params = {}) => await vm.$u.get('/addons/cms/api.my/comment', params);
	vm.$api.getMyboutus = async (params = {}) => await vm.$u.get('/addons/cms/api.my/aboutus', params);
	vm.$api.getOrder = async (params = {}) => await vm.$u.get('/addons/cms/api.my/order', params);
	vm.$api.getMyagree = async (params = {}) => await vm.$u.get('/addons/cms/api.my/agreement', params);
	vm.$api.goCommentPost = async (params = {}) => await vm.$u.post('/addons/cms/api.comment/post', params);
	vm.$api.goCommentIndex = async (params = {}) => await vm.$u.post('/addons/cms/api.comment/index', params);
	vm.$api.goLogin = async (params = {}) => await vm.$u.post('/addons/cms/api.login/login', params);
	vm.$api.mobilelogin = async (params = {}) => await vm.$u.post('/addons/cms/api.login/mobilelogin', params);
	vm.$api.goRegister = async (params = {}) => await vm.$u.post('/addons/cms/api.login/register', params);
	vm.$api.goResetpwd = async (params = {}) => await vm.$u.post('/addons/cms/api.login/resetpwd', params);
	vm.$api.gowxLogin = async (params = {}) => await vm.$u.post('/addons/cms/api.login/wxLogin', params);
	vm.$api.goAppLogin = async (params = {}) => await vm.$u.post('/addons/cms/api.login/appLogin', params);
	vm.$api.getWechatMobile = async (params = {}) => await vm.$u.post('/addons/cms/api.login/getWechatMobile', params);
	vm.$api.getAuthUrl = async (params = {}) => await vm.$u.get('/addons/third/api/getAuthUrl', params);
	vm.$api.goAuthCallback = async (params = {}) => await vm.$u.post('/addons/third/api/callback', params);
	vm.$api.getBindList = async (params = {}) => await vm.$u.get('/addons/third/api/getBindList', params);
	vm.$api.goUnbind = async (params = {}) => await vm.$u.post('/addons/third/api/unbind', params);
	vm.$api.goThirdAccount = async (params = {}) => await vm.$u.post('/addons/third/api/account', params);
	vm.$api.getMoneyLogs = async (params = {}) => await vm.$u.get('/addons/cms/api.the_logs/money', params);
	vm.$api.getScoreLogs = async (params = {}) => await vm.$u.get('/addons/cms/api.the_logs/score', params);
	vm.$api.selectpage = async (params = {}) => await vm.$u.get('/addons/cms/api.common/selectpage', params);
	vm.$api.search = async (params = {}) => await vm.$u.get('/addons/cms/api.search/index', params);
	vm.$api.signinConfig = async (params = {}) => await vm.$u.get('/addons/signin/api.index/index', params);
	vm.$api.monthSign = async (params = {}) => await vm.$u.get('/addons/signin/api.index/monthSign', params);
	vm.$api.dosign = async (params = {}) => await vm.$u.get('/addons/signin/api.index/dosign', params);
	vm.$api.fillup = async (params = {}) => await vm.$u.get('/addons/signin/api.index/fillup', params);
	vm.$api.signRank = async (params = {}) => await vm.$u.get('/addons/signin/api.index/rank', params);
	vm.$api.signLog = async (params = {}) => await vm.$u.get('/addons/signin/api.index/signLog', params);
	vm.$api.formField = async (params = {}) => await vm.$u.get('/addons/cms/api.diyform/index', params);
	vm.$api.postForm = async (params = {}) => await vm.$u.post('/addons/cms/api.diyform/postForm', params);
	vm.$api.formList = async (params = {}) => await vm.$u.get('/addons/cms/api.diyform/formList', params);
	vm.$api.formShow = async (params = {}) => await vm.$u.get('/addons/cms/api.diyform/show', params);
	vm.$api.tagIndex = async (params = {}) => await vm.$u.get('/addons/cms/api.tag/index', params);
	vm.$api.getCategory = async (params = {}) => await vm.$u.get('/addons/cms/api.common/getCategory', params);
	vm.$api.getSigned = async (params = {}) => await vm.$u.post('/addons/cms/api.user/getSigned', params);
	vm.$api.getVipIndex = async (params = {}) => await vm.$u.get('/addons/vip/api.index/index', params);
	vm.$api.goVipSubmit = async (params = {}) => await vm.$u.get('/addons/vip/api.order/submit', params);
	vm.$api.getCollection = async (params = {}) => await vm.$u.get('/addons/cms/api.collection/index', params);
	vm.$api.addCollection = async (params = {}) => await vm.$u.post('/addons/cms/api.collection/create', params);
	vm.$api.delCollection = async (params = {}) => await vm.$u.post('/addons/cms/api.collection/delete', params);
	vm.$api.specialList = async (params = {}) => await vm.$u.get('/addons/cms/api.special/special', params);
	vm.$api.specialIndex = async (params = {}) => await vm.$u.get('/addons/cms/api.special/index', params);
	vm.$api.getPageDetail = async (params = {}) => await vm.$u.get('/addons/cms/api.page/detail', params);
	vm.$api.goUpload = async (params = {}) => await upload(vm, params);

}

export default {
	install
}
