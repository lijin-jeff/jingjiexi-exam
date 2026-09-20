## 问题分析

从日志可以看出：
- `currentQuestionNum: 56`（当前题号57，索引56）
- `newQuestionNum: 55`（新题号56，索引55）
- `swiperIndex: 0`（Swiper滑动到索引0）

当用户在57题向左滑动时，`handleSwiperChange` 使用 `displayQuestionIndex` 计算新题号，导致从56减到55，跳过了56题。

## 根本原因

`handleSwiperChange` 仍然依赖 `displayQuestionIndex` 和 `simplifiedQuestionList`：
```javascript
const currentQuestionNum = this.displayQuestionIndex;
if (swiperIndex === 0 && currentQuestionNum > 0) {
  newQuestionNum = currentQuestionNum - 1;
}
```

## 修复方案

修改 `handleSwiperChange`，让它直接基于 `swiperCurrentIndex` 和 `swiperList` 计算，与 `swipeCurrent` 和 `activeQuestions` 保持一致：

```javascript
// 获取滑动后的swiper索引
const swiperIndex = e.detail.current;
const currentRealIndex = this.swiperCurrentIndex;
let newRealIndex = currentRealIndex;

// 基于 swiperIndex 和 activeQuestions 长度计算新的实际索引
const activeLength = this.activeQuestions.length;
if (swiperIndex === 0 && currentRealIndex > 0) {
  // 向左滑，上一题
  newRealIndex = currentRealIndex - 1;
} else if (swiperIndex === activeLength - 1 && currentRealIndex < this.swiperList.length - 1) {
  // 向右滑，下一题
  newRealIndex = currentRealIndex + 1;
}
```

这样就不再依赖 `simplifiedQuestionList`，保持与之前修改的 `swipeCurrent` 和 `activeQuestions` 逻辑一致。