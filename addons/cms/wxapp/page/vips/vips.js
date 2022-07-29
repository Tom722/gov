var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userInfo: {},
    tab: {
      list: [],
      selectedId: 0,
      height: 50,
      scroll: true
    },
    vipInfo: {},
    vip: 0,
    pricedata: [],
    rightdata: [],
    selectIndex: [],
    isDisabled:false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

    options.vip && this.setData({
      vip: options.vip
    });
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo
      });
      this.getVipIndex();
    } else {
      app.info('请先登录', function () {
        wx.navigateBack({
          delta: 1,
        })
      })
    }
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },
  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },
  getIsDefault(value){
    return [true,1,'1','yes','true'].indexOf(value) != -1;
  },
  getVipIndex() {
    let that = this;
    app.request('/addons/vip/api.index/index', function (res, err) {
      //有vip等级传来，自动到VIP
      let list = [];
      res.vipList.map(function (item, index) {
        if (item.level == that.data.vip) {
          that.setData({
            ['tab.selectedId']: index
          })
        }
        list.push({
          id: index,
          level: item.level,
          title: item.name,
          pricedata: item.pricedata,
          rightdata: item.rightdata
        })
      });
      that.setData({
        'tab.list': list,
        vipInfo: res.vipInfo || {}
      })
      that.setPriceData();
    }, function (res, err) {
      app.error(err.msg);
    });
  },
  //渲染数据
  setPriceData() {
    let index = this.data.tab.selectedId;
    let list = this.data.tab.list;
    list.length > index && (this.setData({
      pricedata: list[index].pricedata,
      rightdata: list[index].rightdata,
      isDisabled:(this.data.vipInfo.level && list[index].level < this.data.vipInfo.level)
    }));

    if (typeof this.data.selectIndex[index] == 'undefined') {
      let defaultIndex = 0;
      //渲染默认的index
      this.data.pricedata.some((item, index) => {
        if (this.getIsDefault(item.default)) {
          defaultIndex = index;
          return true;
        }
      });
      this.setData({
        [`selectIndex[${index}]`]: defaultIndex
      });
    }


  },
  //选择
  selectVip(e) {
    this.setData({
      [`selectIndex[${this.data.tab.selectedId}]`]: e.currentTarget.dataset.index
    });
  },
  _handleZanTabChange(e) {
    this.setData({
      ['tab.selectedId']: e.currentTarget.dataset.itemId
    })
    this.setPriceData();
  },
  //购买或者续费
  goBuy(e) {
    let that = this;
    let type = e.currentTarget.dataset.type;
    let data = {
      paytype: 'wechat',
      level: this.data.tab.list[this.data.tab.selectedId].level,
      days: this.data.pricedata[this.data.selectIndex[this.data.tab.selectedId]].days,
      method: 'miniapp'
    };
    //续费
    if (type == 1) {
      this.data.tab.list.some((item, index) => {
        if (item.level == this.data.vipInfo.level) {
          data.level = item.level;
          //如果选择过，用选择的，没有用默认的天数
          if (typeof this.data.selectIndex[index] != 'undefined') {
            data.days = item.pricedata[this.data.selectIndex[index]].days;
          } else {
            item.pricedata.some(res => {
              if (this.getIsDefault(res.default)) {
                data.days = res.days;
                return true;
              }
            });
          }
          return true;
        }
      });
    }
    app.request('/addons/vip/api.order/submit', data, function (res, err) {
      wx.requestPayment({
        provider: 'wxpay',
        timeStamp: res.timeStamp,
        nonceStr: res.nonceStr,
        package: res.package,
        signType: res.signType,
        paySign: res.paySign,
        success: res => {
          app.info('支付成功！');
          that.getVipIndex();
        },
        fail: err => {
          app.error('fail:' + JSON.stringify(err));
        }
      });
    }, function (res, err) {
      app.info(err.msg, function () {       
        if (res == 'bind') {
          wx.navigateTo({
            url: '/page/my/bind',
          })
        }
      });
    })
  },
  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})