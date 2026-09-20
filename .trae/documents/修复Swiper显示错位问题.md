## 问题分析

当直接跳转到最后一题（第57题，索引56）时：
1. `activeQuestions` 从3个题目变为2个题目（最后一题模式）
2. `swipeCurrent` 返回1（应该显示 `activeQuestions[1]`）
3. 但Swiper实际显示的是 `activeQuestions[0]`（第56题）

## 根本原因

Swiper的 `current` 属性值没有变化（都是1），Vue认为不需要重新渲染。但 `activeQuestions` 数组的内容变了，导致Swiper显示错位。

## 解决方案

在 `activeQuestions` 长度变化时，强制Swiper跳转到正确的位置。可以通过在watcher中检测 `activeQuestions` 长度变化，然后使用 `swiperDisplayIndex` 来触发Swiper跳转。

具体修改：
1. 在 `swiperCurrentIndex` 的watcher中，检测 `activeQuestions` 长度变化
2. 如果长度从3变为2（中间题→最后一题），强制设置 `swiperDisplayIndex` 为1
3. 使用 `$nextTick` 确保DOM更新后再跳转

## 代码修改

修改 `swiperCurrentIndex` 的watcher：

```javascript
swiperCurrentIndex: {
  handler(newVal, oldVal) {
    console.log('[watch] swiperCurrentIndex变化:', { newVal, oldVal });
    
    // 立即检查activeQuestions
    const oldLength = this.activeQuestions?.length;
    console.log('[watch] 立即检查activeQuestions:', {
      activeQuestionsLength: oldLength,
      activeQuestionsUids: this.activeQuestions?.map((q, i) => `${i}:${q?.uid?.slice(-8) || '?'}`)
    });
    
    // 强制更新组件，确保activeQuestions重新计算
    this.$nextTick(() => {
      const newLength = this.activeQuestions?.length;
      console.log('[watch] $nextTick后:', {
        activeQuestionsLength: newLength,
        activeQuestionsUids: this.activeQuestions?.map((q, i) => `${i}:${q?.uid?.slice(-8) || '?'}`)
      });
      
      // 🔑 关键修复：如果activeQuestions长度从3变为2，强制跳转到正确的位置
      if (oldLength === 3 && newLength === 2) {
        console.log('[watch] activeQuestions长度从3变为2，强制跳转到index=1');
        // 使用swiperDisplayIndex触发Swiper跳转
        this.swiperDisplayIndex = 1;
      }
    });
  }
}
```

## 备选方案

如果上述方案不生效，可以考虑：
1. 使用 `v-if` 控制Swiper的重新渲染
2. 使用 `key` 属性强制Swiper重新创建
3. 修改 `swipeCurrent` 计算属性，在长度变化时返回不同的值（如先返回-1再返回1）