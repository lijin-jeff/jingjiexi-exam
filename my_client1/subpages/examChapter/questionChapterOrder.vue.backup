<template>
  <common-question 
    page-title="章节练习" 
    question-type="chapter"
    :question-params="{uid: questionUid, chapterUid: chapterUid}"
  />
</template>

<script>
import commonQuestion from '@/subpages/exam/components/commonQuestion.vue'
export default {
  name: 'QuestionChapterOrder',
  components: {
    commonQuestion
  },
  data() {
    return {
      questionUid: '',
      chapterUid: ''
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
    this.chapterUid = option.chapter || ''
  }
}
</script>

<style lang="scss" scoped>
/* 样式已移至commonQuestion组件 */
</style>

