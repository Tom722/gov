<template>
	<view>
		<!-- 顶部导航 -->
		<fa-navbar title="选择栏目" ref="navbar"></fa-navbar>
		<view class="u-p-30">
			<u-form>
				<u-form-item label-position="left" label="栏目：" label-width="130">
					<fa-selects
						:fa-list="selectList"
						title="请选择栏目"
						checkeType="select"
						:showValue="channel_id"
						v-model="channel_id"
					></fa-selects>
				</u-form-item>
			</u-form>
		</view>
		<view class="u-p-30 u-m-t-80">
			<u-button type="primary" hover-class="none" :custom-style="{ backgroundColor: theme.bgColor, color: theme.color }" shape="circle" @click="next">
				下一步
			</u-button>
		</view>
		<!-- 底部导航 -->
		<fa-tabbar></fa-tabbar>
	</view>
</template>

<script>
import FaSelects from './components/fa-selects/fa-selects.vue'
export default {
	components:{
		FaSelects
	},
	onLoad(e) {
		let query = e || {};
		this.archives_id = query.archives_id || 0;
		this.getChannel();
	},
	data() {
		return {
			selectShow: false,
			archives_id: 0,
			channel_id: '',
			selectList: []
		};
	},
	watch: {
		channel_id(newValue, oldValue) {
			this.$u.vuex('vuex_channel_id',newValue);
		}
	},
	methods: {
		getChannel: async function() {
			let res = await this.$api.getChannel({ archives_id: this.archives_id });
			if (res.code) {
				this.selectList = res.data.channel;
				//编辑的时候赋值
				if (res.data.channel_id && this.archives_id) {
					this.selectList.some(item => {
						if (item.id == res.data.channel_id) {
							this.channel_id = res.data.channel_id;
							return true;
						}
					});
				}
			}
		},
		next() {
			if (!this.channel_id) {
				this.$u.toast('请先选择栏目！');
				return;
			}
			this.$u.route('/pages/publish/archives',
				{ archives_id: this.archives_id }
			);
		}
	}
};
</script>

<style lang="scss">
page {
	background-color: #ffffff;
}
.richColor {
	color: #909399;
}
</style>
