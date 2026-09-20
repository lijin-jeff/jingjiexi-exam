## 检查发现的问题

### 1. 小程序 API 兼容问题
- **clientX/Y**: 第305-306行、343-344行使用 `touch.clientX/Y`，小程序中应使用 `pageX/Y`
- **process.env**: 第64行使用 `process.env.NODE_ENV`，小程序中不存在
- **closest()**: 第371-374行使用 `target.closest()`，小程序中不支持 DOM API

### 2. 状态锁死锁风险
- 已使用 `try-finally` 修复，但需统一超时释放逻辑
- 需新增 `onUnload` 钩子支持

### 3. 全局单例污染
- 第880-882行创建全局实例 `const swipeHandler = new SwipeHandler()`
- 应改为组件内实例化

### 4. 定时器内存泄漏
- `smartDebounce` 中的 `timeoutId` 是闭包变量，未挂载到实例
- `destroy()` 方法未清理 `smartDebounce` 的定时器

### 5. 入参校验不足
- 第98-103行解构赋值缺少类型校验
- 需增加 `typeof activeQuestionsLength === 'number'` 等校验

### 6. 滑动/滚动冲突
- 缺少 `touchstart` 时禁用原生滚动的逻辑
- 未处理 `touchstop` 事件

### 7. 程序化跳转兼容
- 需新增 `isProgrammaticJump` 标记
- `handleSwiperChange` 中需跳过程序化跳转触发的事件

## 修复方案

### 阶段1：小程序API兼容
1. 添加 `getTouchPos()` 方法兼容 clientX/Y 和 pageX/Y
2. 使用 `wx.getAccountInfoSync()` 判断环境替代 process.env
3. 使用 `wx.createSelectorQuery()` 替代 closest()

### 阶段2：架构调整
1. 移除全局实例导出，改为类导出
2. 在 commonQuestion.vue 中组件内实例化
3. 在 onUnload 中调用 destroy()

### 阶段3：完善定时器管理
1. 将 smartDebounce 的 timeoutId 挂载到实例
2. 在 destroy() 中清理所有定时器

### 阶段4：入参校验
1. 所有解构赋值增加类型校验
2. 增加参数合法性检查

### 阶段5：滑动/滚动冲突
1. 在 touchstart 时返回禁用滚动标志
2. 添加 touchstop 事件处理

### 阶段6：程序化跳转
1. 新增 isProgrammaticJump 状态
2. 在 handleSwiperChange 中检测并跳过