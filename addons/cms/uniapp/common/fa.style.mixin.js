module.exports = {
	computed: {
		cmsTitleStyle() {
			return val => {
				let style = {};
				if (val && val.includes('b')) {
					style.fontWeight = 'bold';
				}
				if (val && val.includes('#')) {
					style.color = val.replace('b', '').replace('|', '');
				}
				return style;
			}
		},
		theme() {
			if (this.vuex_theme.value) {
				return this.vuex_theme.value;
			}
			return {};
		},
		lightColor() {
			let color = '#f5f5f5';
			if (this.vuex_theme.value) {
				let theme = this.vuex_theme.value;
				let colorArr = this.$u.colorGradient(theme.bgColor, theme.color, 10);
				color = colorArr[9] || '#f5f5f5';
			}
			return color;
		},
		faBorderColor() {
			let color = '#f5f5f5';
			if (this.vuex_theme.value) {
				let theme = this.vuex_theme.value;
				let colorArr = this.$u.colorGradient(theme.bgColor, theme.color, 10);
				color = colorArr[5] || '#f5f5f5';
			}
			return color;
		},
	},
}
