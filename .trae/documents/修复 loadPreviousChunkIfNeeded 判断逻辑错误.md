## 问题描述

从53题左滑到52题、51题、50题，每滑动一题都加载一个题块，这是不合理的。应该只在滑动到未加载的块时才加载。

## 根本原因

1. `loadPreviousChunkIfNeeded` 和 `loadNextChunkIfNeeded` 的块判断逻辑错误
2. 用 `swiperList.length` 判断块是否已加载，但 `swiperList` 可能是非连续的

## 修复方案

### 修复1：修改 `loadPreviousChunkIfNeeded`（第3495-3500行）

```javascript
// 原代码
const expectedStartIndex = previousChunkIndex * chunkSize;
if (expectedStartIndex < this.swiperList.length) {
  console.log('[loadPreviousChunkIfNeeded] 上一块已加载:', previousChunkIndex);
  return;
}

// 新代码
// 检查上一块的所有题目是否都已经在 swiperList 中
const chunkStartNum = previousChunkIndex * chunkSize;
const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
let loadedCount = 0;
for (let i = chunkStartNum; i < chunkEndNum; i++) {
  const uid = this.simplifiedQuestionList[i]?.uid;
  if (uid && this.swiperList.some(item => item.uid === uid)) {
    loadedCount++;
  }
}
if (loadedCount === chunkEndNum - chunkStartNum) {
  console.log('[loadPreviousChunkIfNeeded] 上一块已加载:', previousChunkIndex);
  return;
}
```

### 修复2：修改 `loadNextChunkIfNeeded`（类似逻辑）

```javascript
// 检查下一块的所有题目是否都已经在 swiperList 中
const chunkStartNum = nextChunkIndex * chunkSize;
const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
let loadedCount = 0;
for (let i = chunkStartNum; i < chunkEndNum; i++) {
  const uid = this.simplifiedQuestionList[i]?.uid;
  if (uid && this.swiperList.some(item => item.uid === uid)) {
    loadedCount++;
  }
}
if (loadedCount === chunkEndNum - chunkStartNum) {
  console.log('[loadNextChunkIfNeeded] 下一块已加载:', nextChunkIndex);
  return;
}
```

## 需要修改的文件

- `c:\Users\44343\jingjiexi\my_client1\subpages\exam\components\commonQuestion.vue`

## 影响范围

- 只影响 `loadPreviousChunkIfNeeded` 和 `loadNextChunkIfNeeded` 方法
- 不会影响其他组件或页面
- 修复后同一块内的滑动不会重复加载

## 验证方式

1. 从第53题左滑到第52题（同一块）
2. 观察是否还会加载题块 - 应该不加载
3. 从第50题左滑到第49题（跨块）
4. 观察是否应该加载第4块