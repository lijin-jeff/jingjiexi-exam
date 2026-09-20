<template>
  <view class="container">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view
			class="tn-custom-nav-bar__back"
			>
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
			{{ formattedTime }}
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <common-question
      :question-type="'examination'"
      :question-params="{uid: questionUid}"
      @update:questionList="questionList = $event"
      @submitExam="submitExam"
    >
      <template #footer>
        <view
          style="position: fixed; bottom: 0rpx; width: 100%; height: 100rpx;line-height: 100rpx; background-color: aliceblue;"
        >
          <view class="tn-flex tn-flex-row-between tn-padding-left tn-padding-right">
            <view
              style="width: 70%;"
              class="tn-flex tn-flex-row-left"
            >
              <view>
                <tn-button
                  font-color="#fff"
                  :background-color="mainColor"
                  @click="examOptionChange(1)"
                >
                  上一题
                </tn-button>
              </view>
              <view class="tn-padding-left">
                <tn-button
                  font-color="#fff"
                  :background-color="mainColor"
                  @click="examOptionChange(2)"
                >
                  下一题
                </tn-button>
              </view>
            </view>
            <view style="width: 30%;">
              <tn-button
                width="100%"
                font-color="#fff"
                :background-color="mainColor"
                @click="submitExam"
              >
                提交作答
              </tn-button>
            </view>
          </view>
          <view class="tn-tabbar-height" />
        </view>
      </template>
    </common-question>

    <!-- 答题结果弹窗开始-->
    <view class="">
      <tn-modal
        v-model="showSubmitResult"
        :title="showSubmitTitle"
        width="70%"
        :mask-closeable="false"
        :content="submitInfo.alter_msg"
        :button="button"
        @click="clickModalConfirm"
      />
    </view>
    <!-- 答题结果弹窗结束 -->
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import commonQuestion from '@/subpages/exam/components/commonQuestion.vue'

	export default {
		name: 'QuestionOrder',
		components: {
			commonQuestion
		},
		mixins: [template_page_mixin],
		data() {
			return {
				showSubmitResult: false, // 答题结果弹窗
				showSubmitTitle: '考试结果', // 答题结果弹窗标题
				button: [ // 答题结果按钮
					{
						text: '答题历史',
						backgroundColor: 'tn-bg-indigo',
						fontColor: '#FFFFFF',
					},
					{
						text: '返回首页',
						backgroundColor: getApp().globalData.mainColor,
						fontColor: '#FFFFFF'
					}
				],
				submitInfo: {}, // 考试结果信息
				mainColor: getApp().globalData.mainColor,
				questionUid: '', // 考试uid
				questionList: [], // 全量数组
				examinationInfo: {}, // 考试基础信息
				seconds: 0,
				timerId: null,
				submit_time: '', // 答题时间
			}
		},
		computed: {
			formattedTime() {
				const hours = Math.floor(this.seconds / 3600);
				const minutes = Math.floor((this.seconds % 3600) / 60);
				const secs = this.seconds % 60;
				this.submit_time = [hours, minutes, secs]
					.map(v => v.toString().padStart(2, '0'))
					.join(':');
				return this.submit_time
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
			this.fetchExamination()
			
			// 等待commonQuestion组件初始化后调用其fetchQuestionList方法
			setTimeout(() => {
				const commonQuestionComp = this.$children.find(item => item.$options.name === 'commonQuestion')
				if (commonQuestionComp && commonQuestionComp.fetchQuestionList) {
					commonQuestionComp.fetchQuestionList()
					this.startTimer()
				}
			}, 500)
		},
		methods: {
			// 提交考试数据
			submitExam() {
				console.log(this.questionList)
				this.$api.apiSubmitExamination({
					uid: this.questionUid,
					option: this.questionList,
					submit_time: this.submit_time
				}).then(res => {
					if (res.code === 1) {
						this.submitInfo = res.data
						this.showSubmitResult = true
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			clickModalConfirm(event) {
				this.showSubmitResult = false
				if (event.index === 1) {
					this.$func.tnRelunch('/subpages/examination/examinationQuestionLib')
				} else {
					this.$func.tnRelunch('/subpages/examHistory/examinationHistory')
				}
			},
			// 拉取考试基础信息
			fetchExamination() {
				uni.showLoading({
					title: '努力加载考试详情...',
					mask: true
				})
				this.$api.apiExaminationContent({
					uid: this.questionUid,
				}).then(res => {
					uni.hideLoading()
					if (res.code === 1) {
						this.examinationInfo = res.data
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			startTimer() {
				this.timerId = setInterval(() => {
					this.seconds++
				}, 1000);
			},
			examOptionChange(type) { // 试题手动上下切换选项
				const commonQuestionComp = this.$children.find(item => item.$options.name === 'commonQuestion')
				if (commonQuestionComp && commonQuestionComp.examOptionChange) {
					commonQuestionComp.examOptionChange(type)
				}
			}
		}
	}
</script>

<style lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.container {
		display: flex;
		flex-direction: column;
		height: 100%;
		background-color: #f0f2f5;
		/* 页面背景色*/
	}
</style>

