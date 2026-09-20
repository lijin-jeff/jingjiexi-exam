## 问题分析

**发现的问题**：

`handlePrevQuestion` 和 `handleNextQuestion` 使用 `swiperCurrentIndex` 直接计算上一题/下一题：

```javascript
handlePrevQuestion() {
  const prevIndex = this.swiperCurrentIndex - 1;
  if (prevIndex >= 0) {
    this.swiperCurrentIndex = prevIndex;
  }
}

handleNextQuestion() {
  const nextIndex = this.swiperCurrentIndex + 1;
  if (nextIndex < this.swiperList.length) {
    this.swiperCurrentIndex = nextIndex;
  }
}
```

但 `swiperCurrentIndex` 是题目在 `swiperList` 中的索引（加载顺序），而上一题/下一题应该基于 `displayQuestionIndex`（题号顺序）计算。

如果 `swiperList` 的顺序和 `simplifiedQuestionList` 不一致，使用 `swiperCurrentIndex` 计算上一题/下一题就会出错。

## 修复方案

修改 `handlePrevQuestion` 和 `handleNextQuestion`，基于 `displayQuestionIndex` 计算：

```javascript
handlePrevQuestion() {
  // 基于 displayQuestionIndex 计算上一题
  const currentQuestionNum = this.displayQuestionIndex;
  const prevQuestionNum = currentQuestionNum - 1;
  
  if (prevQuestionNum >= 0 && this.simplifiedQuestionList) {
    const prevUid = this.simplifiedQuestionList[prevQuestionNum]?.uid;
    if (prevUid) {
      const prevIndex = this.swiperList.findIndex(item => item.uid === prevUid);
      if (prevIndex !== -1) {
        this.swiperCurrentIndex = prevIndex;
      }
    }
  }
}

handleNextQuestion() {
  // 基于 displayQuestionIndex 计算下一题
  const currentQuestionNum = this.displayQuestionIndex;
  const nextQuestionNum = currentQuestionNum + 1;
  
  if (nextQuestionNum < this.simplifiedQuestionList.length && this.simplifiedQuestionList) {
    const nextUid = this.simplifiedQuestionList[nextQuestionNum]?.uid;
    if (nextUid) {
      const nextIndex = this.swiperList.findIndex(item => item.uid === nextUid);
      if (nextIndex !== -1) {
        this.swiperCurrentIndex = nextIndex;
      }
    }
  }
}
```

这样可以确保上一题/下一题的计算基于题号顺序，和 `activeQuestions`、`swipeCurrent`、`displayQuestionIndex` 保持一致。