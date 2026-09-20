<template>
  <common-question 
    ref="commonQuestionRef"
    page-title="模拟考试" 
    questionType="mock"
    :question-params="{uid: questionUid}"
  >
    <!-- 模拟考试特有底部提交区域 -->
    <template #examFooter>
      <view
        class="submit-area"
      >
        <view class="tn-flex tn-flex-row-between tn-padding-left tn-padding-right">
          <view style="width: 70%;">
            {{ formattedTime }}
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
</template>

<script>
import commonQuestion from '@/subpages/exam/components/commonQuestion.vue'
export default {
  name: 'QuestionMock',
  components: {
    commonQuestion
  },
  data() {
    return {
      questionUid: '',
      mainColor: getApp().globalData.mainColor,
      showSubmitResult: false, // 答题结果弹窗
      showSubmitTitle: '答题结果', // 答题结果弹窗标题
      showSubmitContent: '', // 答题结果提示内容
      button: [ // 答题结果按钮
        {
          text: '重新作答',
          backgroundColor: 'tn-bg-indigo',
          fontColor: '#FFFFFF',
        },
        {
          text: '返回首页',
          backgroundColor: getApp().globalData.mainColor,
          fontColor: '#FFFFFF'
        }
      ],
      seconds: 0,
      timerId: null,
      config: {},
      childComponent: null
    }
  },
  computed: {
    formattedTime() {
      const hours = Math.floor(this.seconds / 3600);
      const minutes = Math.floor((this.seconds % 3600) / 60);
      const secs = this.seconds % 60;
      return [hours, minutes, secs]
        .map(v => v.toString().padStart(2, '0'))
        .join(':');
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
    
    console.log('[模拟考试] onLoad - questionUid:', this.questionUid)
    console.log('[模拟考试] onLoad - option:', option)
    
    // 检测 restore 参数，如果为 true 则恢复进度
    const shouldRestore = option.restore === 'true'
    
    console.log('[模拟考试] onLoad - shouldRestore:', shouldRestore)
    
    // 延迟执行，确保组件已挂载
    setTimeout(() => {
      console.log('[模拟考试] setTimeout 开始执行')
      
      // 修复：使用 ref 获取子组件
      this.childComponent = this.$refs.commonQuestionRef
      console.log('[模拟考试] childComponent:', this.childComponent)
      
      if (this.childComponent) {
        // 如果需要恢复进度
        if (shouldRestore) {
          console.log('[模拟考试] 进入恢复进度分支')
          const savedProgress = getApp().globalData.savedProgress
          if (savedProgress) {
            console.log('[模拟考试] 恢复进度', savedProgress)
            this.childComponent.restoreProgress(savedProgress)
            // 清除全局状态
            getApp().globalData.savedProgress = null
            getApp().globalData.shouldRestoreProgress = false
          }
        } else {
          // 修复：如果不是恢复进度，则加载新题目
          console.log('[模拟考试] 进入新练习分支')
          console.log('[模拟考试] 开始新练习，加载题目')
          this.childComponent.startNewPractice()
        }
        // 开始计时器
        this.startTimer()
      } else {
        console.error('[模拟考试] 未找到 childComponent')
      }
    }, 100)
  },
  beforeUnmount() {
    clearInterval(this.timerId)
  },
  methods: {
    // 提交作答
    submitExam() {
      if (!this.childComponent || !this.childComponent.questionList || !this.childComponent.questionList.length) {
        this.$func.showToast('题目数据获取失败')
        return
      }
      
      const questionList = this.childComponent.questionList
      questionList.forEach((value, index) => {
        let user_answer = []
        value.option.forEach((v, k) => {
          if (v.is_selected) {
            user_answer.push(v.check)
          }
        })
        value.user_answer = user_answer
      })
      
      uni.showLoading({
        title: '结果结算中...',
        mask: true
      })
      
      this.$api.apiMockExaminationSave({
        option: JSON.stringify(questionList),
        time: this.seconds,
        history_uid: this.questionUid
      }).then(res => {
        uni.hideLoading()
        if (res.code === 1) {
          this.showSubmitResult = true
          this.showSubmitContent = res.data.msg
          this.config = res.data.config
          return
        }
        this.$func.showToast(res.msg)
      })
    },
    clickModalConfirm(event) {
      this.showSubmitResult = false
      if (event.index === 1 && this.config.library_uid) {
        this.$func.redirectTo('/subpages/exam/questionContent?uid=' + this.config.library_uid)
      }
    },
    startTimer() {
      this.timerId = setInterval(() => {
        this.seconds++
      }, 1000);
    }
  }
}
</script>

<style lang="scss" scoped>
.submit-area {
  position: fixed;
  bottom: 0rpx;
  width: 100%;
  height: 100rpx;
  line-height: 100rpx;
  background-color: aliceblue;
}
</style>

