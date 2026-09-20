## 问题分析

用户反馈：题号显示正确（56题），但内容显示错误（55题）。

从日志看，`handleSwiperChange` 的索引计算已经正确：

* `currentRealIndex: 16`（第17题）

* `newRealIndex: 15`（第16题）

但题号显示和内容仍然不匹配。

## 根本原因

**`displayQuestionIndex`** **仍然依赖** **`simplifiedQuestionList`**：

```javascript
displayQuestionIndex() {
  const currentQuestion = this.swiperList[this.swiperCurrentIndex];
  if (currentQuestion && currentQuestion.uid && this.simplifiedQuestionList) {
    const index = this.simplifiedQuestionList.findIndex(item => item.uid === currentQuestion.uid);
    if (index !== -1) {
      return index;  // 返回在 simplifiedQuestionList 中的索引
    }
  }
  return this.swiperCurrentIndex;
}
```

而 `activeQuestions` 现在基于 `swiperCurrentIndex` 直接计算。如果 `simplifiedQuestionList` 的顺序与 `swiperList` 不一致，就会出现题号与内容不匹配。

## 修复方案

修改 `displayQuestionIndex`，让它直接返回 `swiperCurrentIndex`，与 `activeQuestions` 保持一致：

```javascript
displayQuestionIndex() {
  // 🔑 关键修复：直接返回 swiperCurrentIndex，与 activeQuestions 保持一致
  // 避免使用 simplifiedQuestionList，防止题号与内容不一致
  return this.swiperCurrentIndex;
}
```

这样题号显示和内容渲染都基于同一个索引 `swiperCurrentIndex`，确保一致性。
