## 问题描述

54题滑动到53题已经加载题块了，但53题滑动到52题还会加载题块。

## 根本原因

`preloadPreviousChunk` 方法的块判断逻辑错误：

```javascript
// 第2253行
if (expectedStartIndex < currentListLength) {
  return  // 错误地认为已加载
}
```

用 `swiperList.length` 判断块是否已加载，但 `swiperList` 可能是非连续的。

## 修复方案

### 修改 `preloadPreviousChunk` 方法（第2248-2255行）

```javascript
// 原代码
// 检查是否已经加载过该块
const currentListLength = this.swiperList.length
const expectedStartIndex = chunkIndex * chunkSize

// 检查是否已经加载过该块
if (expectedStartIndex < currentListLength) {
  return
}

// 新代码
// 检查目标块的所有题目是否都已经在 swiperList 中
const chunkStartNum = chunkIndex * chunkSize;
const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
let loadedCount = 0;
for (let i = chunkStartNum; i < chunkEndNum; i++) {
  const uid = this.simplifiedQuestionList[i]?.uid;
  if (uid && this.swiperList.some(item => item.uid === uid)) {
    loadedCount++;
  }
}
if (loadedCount === chunkEndNum - chunkStartNum) {
  console.log('[preloadPreviousChunk] 块已加载:', chunkIndex);
  return;
}
```

### 同样修复 `preloadNextChunk` 方法

## 需要修改的文件

- `c:\Users\44343\jingjiexi\my_client1\subpages\exam\components\commonQuestion.vue`

## 影响范围

- 只影响 `preloadPreviousChunk` 和 `preloadNextChunk` 方法
- 不会影响其他组件或页面
- 修复后不会重复预加载已加载的块

## 验证方式

1. 从第54题左滑到第53题
2. 观察是否预加载第4块
3. 从第53题左滑到第52题
4. 观察是否还会预加载 - 应该不加载