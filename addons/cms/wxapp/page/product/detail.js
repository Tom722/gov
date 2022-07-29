var app = getApp();
Page({

  data: {
    userInfo: null,
    archivesInfo: {
      article: {}
    },
    commentList: [],
    loading: false,
    nodata: false,
    nomore: false,
    form: {
      quotepid: 0,
      message: '',
      focus: false
    }
  },
  page: 1,

  onLoad: function (options) {
    var that = this;
    that.setData({
      userInfo: app.globalData.userInfo
    });
    app.request('/addons/cms/wxapp.archives/detail', {
      id: options.id
    }, function (data, ret) {
      var content = data.archivesInfo.content;
      //图片列表需要拆分为数组
      data.archivesInfo.productlist = (data.archivesInfo.productdata && data.archivesInfo.productdata.split(/\,/)) || [];
      data.archivesInfo.productlist.forEach(function (item, index) {
        data.archivesInfo.productlist[index] = app.cdnurl(item);
      });
      console.log(data.archivesInfo.productlist);
      that.setData({
        archivesInfo: data.archivesInfo,
        commentList: data.commentList
      });
      that.page++;
    }, function (data, ret) {
      app.error(ret.msg);
    });
  },
  preview: function (event) {
    var that = this;
    wx.previewImage({
      current: event.currentTarget.dataset.url,
      urls: that.data.archivesInfo.productlist
    });
  },
  reply: function (event) {
    var that = this;
    var pid = event.currentTarget.dataset.pid;
    var nickname = event.currentTarget.dataset.nickname;
    that.setData({
      form: {
        quotepid: pid,
        message: '@' + nickname + ' ',
        focus: true
      }
    });
  },
  login: function (event) {
    var that = this;
    wx.getUserProfile({
      lang: 'zh',
      desc: '授权用户信息',
      success: function (res) {
        console.log(res)
        app.login(res.userInfo, function () {
          that.setData({
            userInfo: app.globalData.userInfo
          });
        });
      },
      fail: function (e) {
        app.info(JSON.stringify(e));
      }
    })
  },
  formSubmit: function (event) {
    var that = this;
    var pid = event.currentTarget.dataset.pid;
    if (!app.globalData.userInfo) {
      app.error('请登录后再评论');
      return;
    }
    if (event.detail.value.message == '') {
      app.error('内容不能为空');
      return;
    }
    app.request('/addons/cms/wxapp.comment/post', {
      aid: this.data.archivesInfo.id,
      pid: this.data.form.quotepid,
      username: event.detail.value.username,
      content: event.detail.value.content
    }, function (data, ret) {
      app.info(ret.msg);
      that.setData({
        form: {
          quotepid: 0,
          message: '',
          focus: false
        },
        commentList: [],
        nodata: false,
        nomore: false
      });
      if (that.data.commentList.length < 10) {
        that.page = 1;
      } else {
        that.data.commentList = that.data.commentList.slice(0, 10);
        that.page = 2;
      }
      that.onReachBottom();
    }, function (data, ret) {
      app.error(ret.msg);
    });
  },
  vote: function (event) {
    var that = this;
    app.vote(event, function (data) {
      that.setData({
        ['archivesInfo.likes']: data.likes,
        ['archivesInfo.dislikes']: data.dislikes,
        ['archivesInfo.likeratio']: data.likeratio
      });
    });
  },
  onReachBottom: function () {
    var that = this;
    this.loadComment(function (data) {
      if (data.commentList.length == 0) {
        //app.info("暂无更多数据");
      }
    });
  },
  loadComment: function (cb) {
    var that = this;
    if (that.data.nomore == true || that.data.loading == true) {
      return;
    }
    this.setData({
      loading: true
    });
    app.request('/addons/cms/wxapp.comment', {
      aid: this.data.archivesInfo.id,
      page: this.page
    }, function (data, ret) {
      that.setData({
        loading: false,
        nodata: that.page == 1 && data.commentList.length == 0 ? true : false,
        nomore: that.page > 1 && data.commentList.length == 0 ? true : false,
        commentList: that.page > 1 ? that.data.commentList.concat(data.commentList) : data.commentList,
      });
      that.page++;
      typeof cb == 'function' && cb(data);
    }, function (data, ret) {
      that.setData({
        loading: false
      });
      app.error(ret.msg);
    });
  },

  share: function () {
    wx.showShareMenu({});
  },
  onShareAppMessage: function () {
    return {
      title: this.data.archivesInfo.title,
      desc: this.data.archivesInfo.intro,
      path: '/page/product/detail?id=' + this.data.archivesInfo.id
    }
  },
})