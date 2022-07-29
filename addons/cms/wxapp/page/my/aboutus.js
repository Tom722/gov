var app = getApp();
Page({

  data: {
    pageInfo: ''
  },

  onLoad: function (options) {
    var that = this;
    //这里读取关闭我们信息
    app.request('/addons/cms/wxapp.my/aboutus', function (data) {
      that.setData({ pageInfo: data.pageInfo });
    });
  },
})