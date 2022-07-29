var app = getApp();
Page({
  data: {
    isWxapp: true,
    isLogin: false,
    userInfo: {
      id: 0,
      avatar: '/assets/images/avatar.png',
      nickname: '游客',
      balance: 0,
      score: 0,
      level: 0
    }
  },
  onLoad: function () {
    var that = this;
  },
  onShow: function () {
    var that = this;
    if (app.globalData.userInfo) {
      that.setData({ userInfo: app.globalData.userInfo, isWxapp: that.isWxapp(), isLogin:true });
    }
  },
  login: function () {
    var that = this;
    wx.getUserProfile({
      lang:'zh',
      desc:'授权用户信息',
      success:function(res){
        app.login(res.userInfo,function () {
          that.setData({ userInfo: app.globalData.userInfo, isWxapp: that.isWxapp(), isLogin:true });
        });
      },
      fail:function(e){
        app.info(JSON.stringify(e));
      }
    })
  },
  logout: function () {
    var that = this;
    app.logout(function () {
      that.setData({ userInfo: {
          id: 0,
          avatar: '/assets/images/avatar.png',
          nickname: '游客',
          balance: 0,
          score: 0,
          level: 0
        }, isWxapp: that.isWxapp(), isLogin: false });
    });
  },
  isWxapp: function () {
    return app.globalData.userInfo ? app.globalData.userInfo.username.match(/^u\d+$/) : true;
  },
  showTips: function (event) {
    var tips = {
      balance: '余额',
      score: '积分',
      level: '等级',
    };
    var type = event.currentTarget.dataset.type;
    var content = tips[type];
    wx.showModal({
      title: '温馨提示',
      content: content,
      showCancel: false
    });
  },
  //点击头像上传
  uploadAvatar: function () {
    if (!app.globalData.userInfo) {
      app.error("请登录后再操作");
      return false;
    }
    var that = this;
    wx.chooseImage({
      success: function (res) {
        var tempFilePaths = res.tempFilePaths;
        var formData = app.globalData.config.upload.multipart;
        formData.token = app.globalData.userInfo.token;
        wx.uploadFile({
          url: app.globalData.config.upload.uploadurl,
          filePath: tempFilePaths[0],
          name: 'file',
          formData: formData,
          success: function (res) {
            if(res.statusCode != 200){
              app.error(res.errMsg);
              return;
            }
            var row = JSON.parse(res.data);
            if (row.code == 1) {
              app.request("/addons/cms/wxapp.user/avatar", { avatar: row.data.url }, function (data, ret) {
                app.success('头像上传成功!');
                app.globalData.userInfo = data.userInfo;
                that.setData({ userInfo: data.userInfo, isWxapp: that.isWxapp()});
              }, function (data, ret) {
                app.error(ret.msg);
              });
            }
          }, error: function (res) {
            app.error("上传头像失败!");
          }
        });
      }, error: function (res) {
        app.error("上传头像失败!");
      }
    });
  },
  goVip(e){
    if(!this.data.userInfo.is_install_vip){
      app.error('请安装VIP会员插件或启用此插件');
      return;
    }
    if(!this.data.isLogin){
      app.error('请登录后再操作');
      return;
    }
    wx.navigateTo({
      url: e.currentTarget.dataset.url,
    })
  },
  goPage(e){
    if(!this.data.isLogin){
      app.error('请登录后再操作');
      return;
    }
    wx.navigateTo({
      url: e.currentTarget.dataset.url,
    });
  },
  checkLogin(e){
    return;
      console.log(e);
      return false;
  }
})
