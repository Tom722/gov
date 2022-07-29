function strlen(value) {
	//中文、中文标点、全角字符按1长度，英文、英文符号、数字按0.5长度计算
	let cnReg = /([\u4e00-\u9fa5]|[\u3000-\u303F]|[\uFF00-\uFF60])/g;
	let mat = value.match(cnReg);
	let length = 0;
	if (mat) {
		return (length = mat.length + (value.length - mat.length) * 0.5);
	} else {
		return (length = value.length * 0.5);
	}
}

/**
 *
 *  判断是否在微信浏览器 true是
 */
function isWeiXinBrowser() {
	// #ifdef H5
	let ua = window.navigator.userAgent.toLowerCase()
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		return true
	} else {
		return false
	}
	// #endif
	return false
}


/**
 * 获取url参数
 * @param {*} name
 * @param {*}
 * @returns
 */
function getQueryString(name, url) {
	var url = url || window.location.href
	var reg = new RegExp('(^|&|/?)' + name + '=([^&|/?]*)(&|/?|$)', 'i')
	var r = url.substr(1).match(reg)
	if (r != null) {
		return r[2]
	}
	return null
}

//路径转化
function getPath(path) {
	return path?path.split('?').shift():path;
}

//复制内容
function uniCopy({
	content,
	success,
	error
}) {

	content = typeof content === 'string' ? content : content.toString() // 复制内容，必须字符串，数字需要转换为字符串

	/**
	 * 小程序端 和 app端的复制逻辑
	 */
	//#ifndef H5
	uni.setClipboardData({
		data: content,
		success: function() {
			success("复制成功~")
		},
		fail: function() {
			error("复制失败~")
		}
	});
	//#endif

	/**
	 * H5端的复制逻辑
	 */
	// #ifdef H5
	if (!document.queryCommandSupported('copy')) { //为了兼容有些浏览器 queryCommandSupported 的判断
		// 不支持
		error('浏览器不支持')
	}
	let textarea = document.createElement("textarea")
	textarea.value = content
	textarea.readOnly = "readOnly"
	document.body.appendChild(textarea)
	textarea.select() // 选择对象
	textarea.setSelectionRange(0, content.length) //核心
	let result = document.execCommand("copy") // 执行浏览器复制命令
	if (result) {
		success("复制成功~")
	} else {
		error("复制失败，请检查h5中调用该方法的方式，是不是用户点击的方式调用的，如果不是请改为用户点击的方式触发该方法，因为h5中安全性，不能js直接调用！")
	}
	textarea.remove()
	// #endif
}


//设置缓存
function setDb(name, value, db_time = 7200) {
	let time = (new Date()).getTime();
	let data = {
		value: value,
		time: time,
		db_time: db_time
	}
	uni.setStorageSync(name, data);
}
//获取缓存
function getDb(name) {
	try {
		let res = uni.getStorageSync(name);
		if (!res) {
			return '';
		}
		let time = (new Date()).getTime();
		if ((time - res.time) / 1000 >= res.db_time) {
			uni.removeStorageSync(name);
			return '';
		}
		return res.value;
	} catch (e) {
		//TODO handle the exception
		return '';
	}
}

/**
 * 下载图片
 */
function getCachedImage(image_url) {
	return new Promise((resolve, reject) => {
		let arr = image_url.split('/');
		let image_name = arr[arr.length - 1];
		var u = getDb('cms' + image_name);
		if (u) {
			resolve(u);
		} else {
			// 本地没有缓存 需要下载
			uni.downloadFile({
				url: image_url,
				success: res => {
					if (res.statusCode === 200) {
						uni.saveFile({
							tempFilePath: res.tempFilePath,
							success: function(res) {
								setDb('cms' + image_name, res.savedFilePath)
								resolve(res.savedFilePath);
							}
						});
					} else {
						reject('下载失败');
					}
				},
				fail: function() {
					reject('下载失败');
				}
			});
		}
	});
}
/**
 * 重设tabbar
 * @param {Object} tablist
 */
function setTabbar(tablist) {
	tablist.list.forEach((item, index) => {
		uni.setTabBarItem({
			index: index,
			text: item.text,
			iconPath: item.image,
			selectedIconPath: item.selectedImage,
			pagePath: item.path
		})
	})
	uni.setTabBarStyle({
		color: tablist.color,
		selectedColor: tablist.selectColor,
		backgroundColor: tablist.bgColor,
		borderStyle: 'black'
	})
}

export {
	strlen,
	isWeiXinBrowser,
	getQueryString,
	getPath,
	uniCopy,
	setDb,
	getDb,
	getCachedImage,
	setTabbar
}
