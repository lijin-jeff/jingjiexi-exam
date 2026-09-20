<template>
  <view class="auto-rewarded-ad-container" />
</template>

<script>
	export default {
		props: {
			// 广告单元 ID（必传）
			adUnitId: {
				type: String,
				required: true,
			},
		},
		data() {
			return {
				isLoading: false, // 加载状态
				isError: false, // 错误状态
				rewardedVideoAd: null, // 广告实例
			};
		},
		watch: {
			// 监听 adUnitId 变化，自动触发广告加载和播放
			adUnitId(newVal) {
				if (newVal) {
					this.initAndPlayAd();
				}
			},
		},
		mounted() {
			// 如果初始传入 adUnitId，立即播放
			if (this.adUnitId) {
				this.initAndPlayAd();
			}
		},
		beforeUnmount() {
			// 组件销毁时清理广告实例
			if (this.rewardedVideoAd) {
				this.rewardedVideoAd.destroy();
			}
		},
		methods: {
			// 初始化广告并播放
			async initAndPlayAd() {
				// 销毁旧的广告实例
				if (this.rewardedVideoAd) {
					this.rewardedVideoAd.destroy();
				}
				try {
					// 创建新的广告实例
					this.rewardedVideoAd = uni.createRewardedVideoAd({
						adUnitId: this.adUnitId,
					});

					// 监听广告事件
					this.rewardedVideoAd.onLoad(() => {
						this.isLoading = false;
						this.$emit("ad-loaded");
					});

					this.rewardedVideoAd.onError((err) => {
						uni.showToast({
							title: err,
							icon: 'none'
						})
						this.$emit("ad-error", err);
					});

					this.rewardedVideoAd.onClose((res) => {
						console.log('广告观看状态', res)
						if (res && res.isEnded) {
							this.$emit("ad-complete");
						} else {
							this.$emit("ad-close");
						}
					});

					// 加载并显示广告
					await this.rewardedVideoAd.load();
					await this.rewardedVideoAd.show();

				} catch (err) {
					uni.showToast({
						title: err,
						icon: 'none'
					})
					this.$emit("ad-error", err);
				}
			},
		},
	};
</script>

<style scoped>
	.auto-rewarded-ad-container {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.ad-tip {
		margin-top: 10px;
		font-size: 14px;
		color: #ff0000;
	}
</style>