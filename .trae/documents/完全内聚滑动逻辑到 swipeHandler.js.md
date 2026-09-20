## 当前问题

虽然手势识别已经迁移到 swipeHandler.js，但 commonQuestion.vue 中仍有大量滑动处理逻辑：
1. handleSwiperTransition - 滑动过程中处理
2. handleSwiperChange - 滑动完成处理（200+行）
3. handleSwiperRealChange - 实际位置变化
4. 边界状态切换处理
5. 滑动loading状态管理
6. 错误恢复机制

## 优化方案

将以下逻辑完全迁移到 swipeHandler.js：

1. **滑动过程处理** (handleSwiperTransition)
   - 迁移到 swipeHandler.handleSwiperTransition
   - 返回预测题号给外部更新UI

2. **滑动完成处理** (handleSwiperChange)
   - 迁移到 swipeHandler.handleSwiperChange
   - 包含：防抖、加载等待、方向计算、索引更新
   - 通过回调通知外部更新swiperCurrentIndex

3. **边界状态处理**
   - 迁移到 swipeHandler.handleBoundaryTransition

4. **错误恢复**
   - 迁移到 swipeHandler.recoverFromError

5. **loading状态**
   - 由 swipeHandler 统一管理

commonQuestion.vue 只保留：
- 调用 swipeHandler 的方法
- 根据回调更新UI状态（swiperCurrentIndex等）
- 业务逻辑（保存答案、预加载等）