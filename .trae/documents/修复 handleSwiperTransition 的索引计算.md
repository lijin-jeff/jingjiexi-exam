## 问题分析

**发现的问题**：

`handleSwiperTransition` 使用 `swiperCurrentIndex` 直接计算上一题/下一题的预测索引：

```javascript
const currentRealIndex = this.swiperCurrentIndex;
let predictedIndex = currentRealIndex;

if (dx > 0) {
  // 向左滑，上一题
  predictedIndex = Math.max(currentRealIndex - 1, 0);
} else {
  // 向右滑，下一题
  predictedIndex = Math.min(currentRealIndex + 1, listLength - 1);
}

this.transitionQuestionIndex = predictedIndex;
```

但 `swiperCurrentIndex` 是题目在 `swiperList` 中的索引（加载顺序），而上一题/下一题应该基于 `displayQuestionIndex`（题号顺序）计算。

## 修复方案

修改 `handleSwiperTransition`，基于 `displayQuestionIndex` 计算预测的索引：

```javascript
handleSwiperTransition(e) {
  const dx = e.detail.dx || 0;
  
  if (Math.abs(dx) > 350) {
    this.isUserSwiping = false;
    return;
  }
  
  this.isUserSwiping = true;
  
  // 🔑 关键修复：基于 displayQuestionIndex 计算预测索引
  const currentQuestionNum = this.displayQuestionIndex;
  let predictedQuestionNum = currentQuestionNum;
  
  if (Math.abs(dx) > 20) {
    if (dx > 0) {
      // 向左滑，上一题
      predictedQuestionNum = Math.max(currentQuestionNum - 1, 0);
    } else {
      // 向右滑，下一题
      predictedQuestionNum = Math.min(currentQuestionNum + 1, this.simplifiedQuestionList.length - 1);
    }
    
    // 在 swiperList 中查找预测题目的索引
    const predictedUid = this.simplifiedQuestionList[predictedQuestionNum]?.uid;
    if (predictedUid) {
      const predictedIndex = this.swiperList.findIndex(item => item.uid === predictedUid);
      if (predictedIndex !== -1) {
        this.transitionQuestionIndex = predictedIndex;
      }
    }
  }
}
```

这样可以确保滑动过程中的预测索引基于题号顺序，和 `activeQuestions`、`swipeCurrent`、`displayQuestionIndex` 保持一致。
