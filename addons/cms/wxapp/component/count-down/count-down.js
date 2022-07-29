// component/count-down/count-down.js
Component({
	/**
	 * 组件的属性列表
	 */
	lifetimes: {
		attached: function () {
			// 在组件实例进入页面节点树时执行
			// 如果自动倒计时		
			this.setItemStyle();
			this.properties.autoplay && this.properties.timestamp && this.start();
		},
		detached: function () {
			// 在组件实例被从页面节点树移除时执行
			clearInterval(this.properties.timer);
			this.properties.timer = null;
		},
	},
	// 以下是旧式的定义方式，可以保持对 <2.2.3 版本基础库的兼容
	attached: function () {
		// 在组件实例进入页面节点树时执行
		// 如果自动倒计时
		this.properties.autoplay && this.properties.timestamp && this.start();
	},
	detached: function () {
		// 在组件实例被从页面节点树移除时执行
		clearInterval(this.properties.timer);
		this.properties.timer = null;
	},
	//数据监听
	observers: {
		'height,borderColor,bgColor,fontSize,color':function(){
			this.setItemStyle();
		}
	},
	properties: {
		timestamp: {
			type:Number,
			value:0,
			observer:function(newVal,oldVal,changePath){			
				 // 如果倒计时间发生变化，清除定时器，重新开始倒计时
				this.clearTimer();
				this.start();
			}
		},
		autoplay:{
			type:Boolean,
			value:true
		},
		// 用英文冒号(colon)或者中文(zh)当做分隔符，false的时候为中文，如："11:22"或"11时22秒"
		separator: {
			type: String,
			value: 'colon'
		},
		// 分隔符的大小，单位rpx
		separatorSize: {
			type: [Number, String],
			value: 30
		},
		// 分隔符颜色
		separatorColor: {
			type: String,
			value: "#303133"
		},
		// 字体颜色
		color: {
			type: String,
			value: '#303133'
		},
		// 字体大小，单位rpx
		fontSize: {
			type: [Number, String],
			value: 30
		},
		// 背景颜色
		bgColor: {
			type: String,
			value: '#fff'
		},
		// 数字框高度，单位rpx
		height: {
			type: [Number, String],
			value: 'auto'
		},
		// 是否显示数字框
		showBorder: {
			type: Boolean,
			value: false
		},
		// 边框颜色
		borderColor: {
			type: String,
			value: '#303133'
		},
		showSeconds:{
			type:Boolean,
			value:true
		},
		showMinutes:{
			type:Boolean,
			value:true
		},
		showHours:{
			type:Boolean,
			value:true
		},
		showDays:{
			type:Boolean,
			value:true
		},
		hideZeroDay:{
			type:Boolean,
			value:false
		},
	},

	/**
	 * 组件的初始数据
	 */
	data: {
		d: '00', // 天的默认值
		h: '00', // 小时的默认值
		i: '00', // 分钟的默认值
		s: '00', // 秒的默认值
		timer: null, // 定时器
		seconds: 0, // 记录不停倒计过程中变化的秒数
		itemStyle:'',
		letterStyle:''
	},

	/**
	 * 组件的方法列表
	 */
	methods: {
		setItemStyle(){
			let style = '';
			if(this.height) {
				style += `height:${this.properties.height}rpx`;
				style += `width:${this.properties.height}rpx`;
			}
			if(this.properties.showBorder) {
				style += `border-style:solid;`
				style += `border-color:${this.properties.borderColor};`
				style += `border-width:1px;`
			}
			if(this.properties.bgColor) {
				style += `background-color:${this.properties.bgColor};`
			}			
			let ls = '';
			if(this.properties.fontSize) ls += `font-size:${this.properties.fontSize}rpx;`
			if(this.properties.color) ls += `color:${this.properties.color};`
			this.setData({itemStyle:style,letterStyle:ls});
		},
		// 倒计时
		start() {
			// 避免可能出现的倒计时重叠情况		
			this.clearTimer();
			if (this.properties.timestamp <= 0) return;
			this.properties.seconds = Number(this.properties.timestamp);
			this.formatTime(this.properties.seconds);
			this.properties.timer = setInterval(() => {
				this.properties.seconds--;
				// 发出change事件			
				this.triggerEvent('change', {seconds:this.properties.seconds}, {})
				if (this.properties.seconds < 0) {
					return this.end();
				}
				this.formatTime(this.properties.seconds);
			}, 1000);
		},
		// 格式化时间
		formatTime(seconds) {		
			// 小于等于0的话，结束倒计时
			seconds <= 0 && this.end();
			let [day, hour, minute, second] = [0, 0, 0, 0];
			day = Math.floor(seconds / (60 * 60 * 24));
			// 判断是否显示“天”参数，如果不显示，将天部分的值，加入到小时中
			// hour为给后面计算秒和分等用的(基于显示天的前提下计算)
			hour = Math.floor(seconds / (60 * 60)) - day * 24;
			// showHour为需要显示的小时
			let showHour = null;
			if (this.properties.showDays) {
				showHour = hour;
			} else {
				// 如果不显示天数，将“天”部分的时间折算到小时中去
				showHour = Math.floor(seconds / (60 * 60));
			}
			minute = Math.floor(seconds / 60) - hour * 60 - day * 24 * 60;
			second = Math.floor(seconds) - day * 24 * 60 * 60 - hour * 60 * 60 - minute * 60;
			// 如果小于10，在前面补上一个"0"
			showHour = showHour < 10 ? '0' + showHour : showHour;
			minute = minute < 10 ? '0' + minute : minute;
			second = second < 10 ? '0' + second : second;
			day = day < 10 ? '0' + day : day;
			this.setData({
				d:day,
				h:showHour,
				i:minute,
				s:second
			})
			
		},
		// 停止倒计时
		end() {
			this.clearTimer();	
			this.triggerEvent('end', {}, {})
		},
		// 清除定时器
		clearTimer() {
			if (this.properties.timer) {
				// 清除定时器
				clearInterval(this.properties.timer);
				this.properties.timer = null;
			}
		}
	}
})