<template>
  <common-question
    page-title="试题收藏" 
    question-type="collection" 
    :question-params="{uid: questionUid}"
  >
    <template #special-features>
      <view
        class="remove-button"
        @click="removeCollection"
      >
        移除收藏
      </view>
    </template>
  </common-question>
</template>

<script>
import commonQuestion from '@/subpages/exam/components/commonQuestion.vue'
	export default {
		name: 'QuestionCollection',
		components: {
			commonQuestion
		},
		data() {
			return {
				questionUid: ''
			}
		},
		onLoad(option) {
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.questionUid = option.uid || ''
		},
		methods: {
			// 移除试题收藏
			removeCollection() {
				// 获取子组件实例
				const child = this.$children.find(child => child.$options.name === 'commonQuestion')
				if (child && child.swiperList && child.swiperList[child.swiperCurrentIndex]) {
					this.$api.apiQuestionCollection({
						library_uid: this.questionUid,
						question_uid: child.swiperList[child.swiperCurrentIndex].uid,
						action: 2
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							// 通知子组件重新获取数据
							child.fetchQuestionList()
						}
					})
				}
			}
		}
	}
</script>

<style lang="scss" scoped>
	.remove-button {
		position: fixed;
		bottom: 30rpx;
		left: 50%;
		transform: translateX(-50%);
		padding: 20rpx 80rpx;
		background-color: $view-theme;
		color: #fff;
		border-radius: 40rpx;
		z-index: 9999;
	}
</style>

