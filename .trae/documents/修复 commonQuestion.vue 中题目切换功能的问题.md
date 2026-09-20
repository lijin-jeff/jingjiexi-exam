## 问题分析

通过分析 `commonQuestion.vue` 文件中与题目切换相关的代码，我发现了以下可能影响按钮切换和滑动切换功能的问题：

### 1. 虚拟列表与真实索引同步问题
- 在 `handleSwiperChange` 中通过计算虚拟索引变化确定真实索引
- 在 `handlePrevQuestion` 和 `handleNextQuestion` 中直接更新 `swiperCurrentIndex`
- 可能导致虚拟列表与真实索引不同步，特别是快速切换时

### 2. 自动保存时机不一致
- `handleNextQuestion` 和 `handleQuestionChange` 会调用 `triggerAutoSave`
- `handlePrevQuestion` 不会调用自动保存
- 可能导致上一题进度未及时保存

### 3. 虚拟列表边界处理问题
- 当 `currentIndex` 接近列表开头或结尾时，`virtualSwiperList` 长度小于3
- `virtualCurrentIndex` 始终返回1，可能导致边界情况下索引计算错误

### 4. 缓存清除一致性问题
- 不同切换方法中清除缓存的方式可能不一致
- 可能导致某些情况下缓存未正确清除

## 修复方案

### 1. 统一自动保存逻辑
- 在 `handlePrevQuestion` 中添加 `triggerAutoSave()` 调用
- 确保所有题目切换操作都会保存当前进度

### 2. 优化虚拟列表边界处理
- 修改 `handleSwiperChange` 方法，根据虚拟列表长度动态调整索引计算
- 确保在边界情况下也能正确计算真实索引

### 3. 增强缓存管理
- 统一所有切换方法中的缓存清除逻辑
- 确保每次切换都能正确清除旧缓存，避免数据混淆

### 4. 改进 lastSwiperIndex 管理
- 确保 `lastSwiperIndex` 在所有切换场景中都能正确更新
- 避免因索引管理不当导致的切换错误

### 5. 添加边界检查
- 在所有切换方法中添加边界检查，确保索引不会越界
- 提高代码的健壮性

## 预期效果

通过以上修复，预期可以解决以下问题：
- 确保按钮切换和滑动切换功能正常工作
- 避免因虚拟列表边界处理不当导致的切换错误
- 保证所有题目切换操作都能及时保存进度
- 提高代码的一致性和可维护性

修复将集中在 `handleSwiperChange`、`handlePrevQuestion` 和 `handleNextQuestion` 三个核心方法上，确保它们的行为一致且正确。