<template>
  <view class="container">
    <tn-nav-bar
      fixed
      custom-back
      :background-color="mainColor"
    >
      <template #back>
        <view class="tn-custom-nav-bar__back">
          <text
            class="icon tn-icon-left"
            @click="goBack"
          />
          <text
            class="icon tn-icon-home-capsule-fill"
            @click="goHome"
          />
        </view>
      </template>
      <view class="tn-flex tn-flex-col-center tn-flex-row-center">
        <text class="tn-text-bold tn-text-xl tn-color-white">
          订单评价
        </text>
      </view>
    </tn-nav-bar>
    <!-- 商品评价 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="section product-review">
        <view class="rating-item">
          <text class="label">
            商品评价
          </text>
          <view class="stars">
            <tn-rate
              v-model="productRating"
              :min-count="1"
              :size="48"
              :count="productRatingCount"
              :active-color="mainColor"
            />
          </view>
          <text class="rating-text">
            {{ getRatingText(productRating) }}
          </text>
        </view>
        <!-- 评价输入框 -->
        <view class="review-input-area">
          <view class="input-placeholder">
            <textarea
              v-model="reviewText"
              class="review-textarea fs-14"
              :maxlength="77"
              placeholder-style="color:#ccc;"
              placeholder="写出您的感受..."
            />
          </view>
          <view class="incentive-text">
            评价超过 <text class="highlight">
              20
            </text> 个字可获<text class="highlight">
              10
            </text> 积分
          </view>
          <view
            class="upload-button"
            @click="chooseMedia"
          >
            <view class="camera-icon-wrapper">
              <text class="icon camera-icon tn-icon-camera" />
            </view>
            <text class="upload-text">
              添加订单图片
            </text>
          </view>
          <view class="tn-padding-bottom">
            <view class="image-grid">
              <view
                v-for="(image, index) in fileList"
                :key="index"
                class="image-item"
              >
                <image
                  class="preview-image"
                  :src="image"
                  mode="aspectFill"
                  @click="previewImage(image)"
                />
                <view
                  class="delete-icon"
                  @click.stop="deleteImage(index)"
                >
                  ×
                </view>
              </view>
            </view>
          </view>
          <view
            class="anonymous-section"
            @click="toggleAnonymous"
          >
            <view
              class="checkbox"
              :class="{ checked: isAnonymous }"
            >
              <text
                v-if="isAnonymous"
                class="checkmark"
              >
                ✓
              </text>
            </view>
            <text class="anonymous-text">
              匿名评价
            </text>
          </view>
        </view>
      </view>
    </view>
    <!-- 分隔线-->
    <view class="divider" />

    <!-- 物流服务评价 -->
    <view class="section logistics-review">
      <view class="logistics-header">
        <text class="title">
          物流服务评价
        </text>
        <text class="subtitle">
          满意请给5星哦
        </text>
      </view>
      <view
        v-for="(item, key) in logisticsRatings"
        :key="key"
        class="rating-item"
      >
        <text class="label">
          {{ item.label }}
        </text>
        <view class="stars">
          <tn-rate
            v-model="item.rating"
            :min-count="1"
            :size="48"
            :count="productRatingCount"
            :active-color="mainColor"
          />
        </view>
        <text class="rating-text">
          {{ getRatingText(item.rating) }}
        </text>
      </view>
    </view>
    <button class="submit-button">
      提交评价
    </button>
  </view>
</template>

<script>
	import templatePageMixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'OrderEvaluate',
		mixins: [templatePageMixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				productRating: 5, // 商品评分
				productRatingCount: 5, // 商品总分
				reviewText: '', // 评价内容
				isAnonymous: true, // 是否匿名
				logisticsRatings: {
					packaging: {
						label: '快递包裹',
						rating: 5
					},
					deliverySpeed: {
						label: '送货速度',
						rating: 5
					},
					deliveryStaff: {
						label: '配送员服务',
						rating: 5
					},
				},
				fileList: [
					'https://cdn.image.tutudati.com/uploads/images/20241122/20241122235256c9ebd5597.jpeg',
					'https://cdn.image.tutudati.com/uploads/images/20241122/20241122235256c9ebd5597.jpeg',
				]
			}
		},
		onLoad() {
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
		},
		methods: {
			deleteImage(index) {
				uni.showModal({
					title: '提示',
					content: '确定要删除这张图片吗？',
					success: (res) => {
						if (res.confirm) {
							this.fileList.splice(index, 1);
						}
					}
				})
			},
			previewImage(url) {
				this.$func.previewImage(url)
			},
			// 设置评分
			setRating(type, rating) {
				if (type === 'product') {
					this.productRating = rating;
				} else if (this.logisticsRatings[type]) {
					this.logisticsRatings[type].rating = rating;
				}
			},
			// 根据评分获取描述文字
			getRatingText(rating) {
				switch (rating) {
					case 1:
						return '很差';
					case 2:
						return '较差';
					case 3:
						return '一般';
					case 4:
						return '满意';
					case 5:
						return '非常满意';
					default:
						return '';
				}
			},
			// 切换匿名状态			
			toggleAnonymous() {
				this.isAnonymous = !this.isAnonymous;
			},
			// 选择图片/视频
			chooseMedia() {
				let _that = this
				if (_that.fileList.length > 8) {
					this.$func.showToast('最多支持上传9张图片')
					return
				}
				let selectNum = 9 - (_that.fileList.length)
				uni.chooseImage({
					count: selectNum,
					sizeType: ['original', 'compressed'],
					sourceType: ['album', 'camera'],
					success: (res) => {
						_that.fileList.push(...res.tempFilePaths)
						console.log('选择成功:', res.tempFilePaths);
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.container {
		background-color: #fff;
		padding-bottom: 40rpx;
		font-size: 28rpx;
		color: #333;
	}

	.section {
		padding: 30rpx;
	}

	/* 评分项公共样式*/
	.rating-item {
		display: flex;
		align-items: center;
		margin-bottom: 30rpx;

		&:last-child {
			margin-bottom: 0;
		}
	}

	.label {
		width: 180rpx;
		/* 固定标签宽度 */
		color: #333;
		font-size: 30rpx;
	}

	.stars {
		display: flex;
		align-items: center;
		margin: 0 15rpx;
	}

	.star {
		font-size: 44rpx;
		/* 星星大小 */
		color: #ccc;
		/* 未选中颜色 */
		margin: 0 5rpx;
		transition: color 0.2s;
		position: relative;
		/* 用于 sparkle 效果定位 */
	}

	.star.filled {
		color: $view-theme;
		/* 选中颜色 (橙红色 */
	}

	/* 模拟 sparkle 效果 (简化版) */
	.star.sparkle::before,
	.star.sparkle::after {
		content: '⭐';
		/* 或者使用伪元素绘制 */
		position: absolute;
		font-size: 16rpx;
		opacity: 0.8;
		animation: sparkle 1.5s infinite;
	}

	.star.sparkle::before {
		top: -10rpx;
		left: 0;
		animation-delay: 0.2s;
	}

	.star.sparkle::after {
		bottom: -5rpx;
		right: -5rpx;
		animation-delay: 0.7s;
	}

	@keyframes sparkle {

		0%,
		100% {
			transform: scale(0.8);
			opacity: 0.5;
		}

		50% {
			transform: scale(1.2);
			opacity: 1;
		}
	}

	.rating-text {
		color: #666;
		font-size: 26rpx;
		margin-left: 10rpx;
	}

	/* 商品评价特定样式 */
	.product-review {
		.rating-item {
			margin-bottom: 20rpx;
		}
	}

	/* 评价输入框*/
	.review-input-area {
		margin-top: 10rpx;
	}

	.input-placeholder {
		color: #999;
		font-size: 26rpx;
		display: flex;
		align-items: center;
		margin-bottom: 20rpx;

		.review-textarea {
			width: 100%;
			height: 150rpx;
			border: 1rpx solid #eee;
			padding: 15rpx;
			border-radius: 8rpx;
			font-size: 26rpx;
		}
	}

	.icon {
		margin-right: 10rpx;
		font-size: 32rpx;
		/* 图标大小 */
	}

	.pencil-icon {
		color: #666;
	}

	.incentive-text {
		font-size: 24rpx;
		color: #999;
		margin-bottom: 30rpx;
	}

	.highlight {
		color: $view-theme;
		/* 橙红色*/
		font-weight: bold;
		margin: 0 4rpx;
	}

	.upload-button {
		display: inline-flex;
		/* 使其宽度自适应内容 */
		flex-direction: column;
		align-items: center;
		justify-content: center;
		width: 100%;
		height: 180rpx;
		background-color: #f7f7f7;
		border-radius: 12rpx;
		border: 1rpx dashed #ddd;
		margin-bottom: 30rpx;
		cursor: pointer;
	}

	.camera-icon-wrapper {
		margin-bottom: 10rpx;
	}

	.camera-icon {
		font-size: 50rpx;
		color: #999;
	}

	.upload-text {
		font-size: 24rpx;
		color: #999;
	}

	.anonymous-section {
		display: flex;
		align-items: center;
		cursor: pointer;
	}

	.checkbox {
		width: 36rpx;
		height: 36rpx;
		border-radius: 50%;
		/* 圆形 */
		border: 1rpx solid #ccc;
		display: flex;
		align-items: center;
		justify-content: center;
		margin-right: 15rpx;
		transition: all 0.3s;
	}

	.checkbox.checked {
		background-color: $view-theme;
		/* 选中背景色*/
		border-color: $view-theme;
	}

	.checkmark {
		color: #fff;
		font-size: 24rpx;
		font-weight: bold;
		line-height: 1;
		/* 确保垂直居中 */
	}

	.anonymous-text {
		font-size: 28rpx;
		color: #333;
	}

	/* 分隔线*/
	.divider {
		height: 20rpx;
		background-color: #f8f8f8;
		/* 页面背景色*/
	}

	/* 物流服务评价 */
	.logistics-review {
		.logistics-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 30rpx;
		}

		.title {
			font-size: 30rpx;
			font-weight: bold;
			color: #333;
		}

		.subtitle {
			font-size: 24rpx;
			color: #999;
		}

		.rating-item .label {
			width: 150rpx;
			/* 物流标签稍短 */
		}
	}

	/* 提交按钮样式 (可选 */
	.submit-button {
		margin: 40rpx 30rpx;
		background-color: $view-theme;
		color: #fff;
		border-radius: 40rpx;
		font-size: 32rpx;
		height: 80rpx;
		line-height: 80rpx;
	}

	// 订单图片开始	
	.image-grid {
		display: flex;
		flex-wrap: wrap;
		gap: 15rpx;
		/* 网格项之间的间距 */
	}

	.image-item {
		/* 计算宽度：容器宽度 - 总间距 / 每行数量 */
		width: calc((100% - 30rpx) / 3);
		/* 利用 padding-bottom 撑开高度，使其等于宽度，实现正方形*/
		height: 0;
		padding-bottom: calc((100% - 30rpx) / 3);
		position: relative;
		border-radius: 12rpx;
		/* 轻微圆角 */
		overflow: hidden;
		/* 隐藏图片超出圆角的部分*/
		background-color: #f0f0f0;
		/* 图片加载前的占位背景色*/
		box-sizing: border-box;
	}

	.preview-image {
		/* 绝对定位铺满父容器 .image-item */
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 100%;
	}

	.delete-icon {
		position: absolute;
		top: 8rpx;
		/* 调整删除按钮位置 */
		right: 8rpx;
		width: 36rpx;
		height: 36rpx;
		background-color: rgba(0, 0, 0, 0.6);
		/* 半透明黑色背景 */
		color: #fff;
		/* 白色 '×' 号*/
		border-radius: 50%;
		/* 圆形 */
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24rpx;
		line-height: 1;
		/* 确保 '×' 垂直居中 */
		cursor: pointer;
		/* 鼠标悬停时显示小图*/
		z-index: 2;
		/* 确保在图片之上*/
	}

	/* 添加按钮的特定样式*/
	.add-button {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		border: 1rpx dashed #ccc;
		/* 虚线边框 */
		background-color: #f7f7f7;
		color: #999;
		padding-bottom: 0;
		/* 覆盖正方形技巧的 padding-bottom */
		/* 显式设置高度，确保与图片项一致*/
		height: calc((100vw - 40rpx - 30rpx) / 3);
		/* 假设页面padding左右10rpx */
		cursor: pointer;
	}

	.add-icon {
		font-size: 50rpx;
		line-height: 1;
		margin-bottom: 10rpx;
	}

	.add-text {
		font-size: 24rpx;
	}

	// 订单图片结束
</style>

