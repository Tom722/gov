//代理全局变量
var watchGlobalData = function (app) {
  let handler = {
    get(target, key) {
      return target[key];
    },
    set(target, key, value) {
      target[key] = value;
      app.watch.$emit(key, value);
      return true;
    }
  }
  app.globalData = new Proxy({
    userInfo: null,
    config: null,
    token: '',
    indexTabList: [],
    newsTabList: [],
    productTabList: [],
    bannerList: []
  }, handler)
}

App({
  //请注意小程序只支持https
  apiUrl: 'http://www.fa.com',
  si: 0,
  //小程序启动
  onLaunch: function () {
    var that = this;
    //加入代理全局变量
    watchGlobalData(that);
    that.request('/addons/cms/wxapp.common/init', {}, function (data, ret) {
      that.globalData.config = data.config;
      that.globalData.indexTabList = data.indexTabList;
      that.globalData.bannerList = data.bannerList;
      that.globalData.newsTabList = data.newsTabList;
      that.globalData.productTabList = data.productTabList;

      //如果需要一进入小程序就要求授权登录,可在这里发起调用
      if (wx.getStorageSync("token")) {
        that.check(function (ret) {});
      }
    }, function (data, ret) {
      that.error(ret.msg);
    });
  },
  //投票
  vote: function (event, cb) {
    var that = this;
    if (!that.globalData.userInfo) {
      that.error('请登录后再发表意见');
      return;
    }
    var id = event.currentTarget.dataset.id;
    var type = event.currentTarget.dataset.type;
    var vote = wx.getStorageSync("vote") || [];
    if (vote.indexOf(id) > -1) {
      that.info("你已经发表过意见了,请勿重复操作");
      return;
    }
    vote.push(id);
    wx.setStorageSync("vote", vote);
    this.request('/addons/cms/wxapp.archives/vote', {
      id: id,
      type: type
    }, function (data, ret) {
      typeof cb == "function" && cb(data);
    }, function (data, ret) {
      that.error(ret.msg);
    });
  },
  //判断是否登录
  check: function (cb) {
    var that = this;
    if (this.globalData.userInfo) {
      typeof cb == "function" && cb(this.globalData.userInfo);
    } else {
      that.login({}, function () {})
    }
  },
  //登录
  login: function (userInfo, cb) {
    var that = this;
    var token = wx.getStorageSync('token') || '';
    //调用登录接口
    wx.login({
      success: function (res) {
        if (res.code) {
          //发起网络请求
          wx.request({
            url: that.apiUrl + '/addons/cms/wxapp.user/login',
            data: {
              code: res.code,
              rawData: JSON.stringify(userInfo),
              token: token
            },
            method: 'post',
            header: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            success: function (lres) {
              var response = lres.data
              if (response.code == 1) {
                that.globalData.userInfo = response.data.userInfo;
                wx.setStorageSync('token', response.data.userInfo.token);
                typeof cb == "function" && cb(that.globalData.userInfo);
              } else {
                wx.setStorageSync('token', '');
                console.log("用户登录失败")
                that.showLoginModal(cb);
              }
            }
          });
        } else {
          that.showLoginModal(cb);
        }
      }
    });
  },
  //显示登录或授权提示
  showLoginModal: function (cb) {
    var that = this;
    if (!that.globalData.userInfo) {
      //获取用户信息
      wx.showModal({
        title: '温馨提示',
        content: '当前无法获取到你的个人信息，部分操作可能受到限制',
        confirmText: "重新登录",
        cancelText: "暂不登录",
        success: function (res) {
          if (res.confirm) {
            wx.getUserProfile({
              lang: 'zh',
              desc: '授权用户信息',
              success: function (res) {
                that.login(res.userInfo, function () {});
              },
              fail: function (e) {
                that.info(JSON.stringify(e));
              }
            })
          } else {
            console.log('用户暂不登录');
          }
        }
      });
    } else {
      typeof cb == "function" && cb(that.globalData.userInfo);
    }
  },
  //退出
  logout: function (cb) {
    var that = this;
    var token = wx.getStorageSync('token') || '';
    //发起网络请求
    wx.request({
      url: that.apiUrl + '/addons/cms/wxapp.user/logout',
      data: {
        token: token
      },
      method: 'post',
      header: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      success: function (lres) {
        var response = lres.data
        if (response.code == 1) {
          that.globalData.userInfo = null;
          wx.setStorageSync('token', "");
          typeof cb == "function" && cb(that.globalData.userInfo);
        } else {
          wx.setStorageSync('token', '');
        }
      }
    });
  },
  //发起网络请求
  request: function (url, data, success, error) {
    var that = this;
    if (typeof data == 'function') {
      success = data;
      error = success;
      data = {};
    }
    if (this.globalData.userInfo) {
      data['user_id'] = this.globalData.userInfo.id;
      data['token'] = this.globalData.userInfo.token;
    }
    //移除最前的/
    // while (url.charAt(0) === '/')
    //   url = url.slice(1);
    this.loading(true);
    let cookie = wx.getStorageSync('cookieKey');
    let header = {
      "Content-Type": "application/x-www-form-urlencoded"
    };
    if (cookie) {
      header.Cookie = cookie;
    }
    if (this.globalData.__token__) {
      data.__token__ = this.globalData.__token__;
    }
    data._ajax = 1;
    wx.request({
      url: this.apiUrl + url,
      data: data,
      method: 'post',
      header: header,
      success: function (res) {
        that.loading(false);
        var code, msg, json;
        if (res && res.header) {
          if (res.header['Set-Cookie']) {
            wx.setStorageSync('cookieKey', res.header['Set-Cookie']); //保存Cookie到Storage
          }
          if (res.header['__token__']) {
            that.globalData.__token__ = res.header['__token__'];
          }
        }
        if (res.statusCode === 200) {
          json = res.data;
          if (json.code === 1) {
            typeof success === 'function' && success(json.data, json);
          } else {
            typeof error === 'function' && error(json.data, json);
          }
        } else {
          json = typeof res.data === 'object' ? res.data : {
            code: 0,
            msg: '发生一个未知错误',
            data: null
          };
          typeof error === 'function' && error(json.data, json);
        }
      },
      fail: function (res) {
        that.loading(false);
        console.log("fail:", res);
        typeof error === 'function' && error(null, {
          code: 0,
          msg: '',
          data: null
        });
      }
    });
  },
  //构造CDN地址
  cdnurl: function (url) {
    return url.toString().match(/^https?:\/\/(.*)/i) ? url : this.globalData.config.upload.cdnurl + url;
  },
  //文本提示
  info: function (msg, cb) {
    wx.showToast({
      title: msg,
      icon: 'none',
      duration: 2000,
      complete: function () {
        typeof cb == "function" && cb();
      }
    });
  },
  //成功提示
  success: function (msg, cb) {
    wx.showToast({
      title: msg,
      icon: 'success',
      image: '/assets/images/ok.png',
      duration: 2000,
      complete: function () {
        typeof cb == "function" && cb();
      }
    });
  },
  //错误提示
  error: function (msg, cb) {
    wx.showToast({
      title: msg,
      icon: 'none',
      // image: '/assets/images/error.png',
      duration: 2000,
      complete: function () {
        typeof cb == "function" && cb();
      }
    });
  },
  //警告提示
  warning: function (msg, cb) {
    wx.showToast({
      title: msg,
      image: '/assets/images/warning.png',
      duration: 2000,
      complete: function () {
        typeof cb == "function" && cb();
      }
    });
  },
  //Loading
  loading: function (msg) {
    if (typeof msg == 'boolean') {
      if (!msg) {
        if (!this.si) {
          return;
        }
        clearTimeout(this.si);
        wx.hideLoading({});
        return;
      }
    }
    msg = typeof msg == 'undefined' || typeof msg == 'boolean' ? '加载中' : msg;
    this.globalData.loading = true;
    if (this.si) {
      return;
    }
    this.si = setTimeout(function () {
      wx.showLoading({
        title: msg
      });
    }, 300);

  },
  watch: function (method = null) {

  },
  //全局信息
  globalData: {},
  //事件触发
  watch: (function () {
    let events = {};
    return {
      $once(name, callback) {
        console.log(name)
        events[name] = callback;
      },
      $emit(key, value) {
          let fn = events[key];
          if(fn == undefined){
            return;
          }
          if (typeof fn == 'function') {
            fn(value)
          }
          delete events[key];
      }
    }
  }()),

})
