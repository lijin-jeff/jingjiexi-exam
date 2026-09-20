## 问题分析
1. **现状**：项目中多个页面都单独定义了`goHome`方法，存在代码冗余
2. **原因**：虽然已存在`template_page_mixin`公共mixin且包含`goHome`方法，但：
   - 部分页面可能未使用该mixin
   - mixin中`goHome`使用`uni.reLaunch`，而有些页面自己定义的使用`uni.switchTab`
3. **影响**：代码重复，维护成本高

## 解决方案
1. **统一`goHome`方法实现**：
   - 修改`template_page_mixin.js`中的`goHome`方法，使用`uni.switchTab`替代`uni.reLaunch`
   - 确保与大部分页面的实现一致

2. **检查并修复页面使用**：
   - 确认所有需要`goHome`功能的页面都已引入`template_page_mixin`
   - 移除页面中单独定义的`goHome`方法

3. **验证修改效果**：
   - 确保所有页面的`goHome`功能正常
   - 检查是否有页面因修改出现异常

## 具体实施步骤
1. **修改公共mixin**：
   - 打开`libs/mixin/template_page_mixin.js`
   - 将`goHome`方法的实现从`uni.reLaunch`改为`uni.switchTab`

2. **清理页面重复定义**：
   - 搜索所有页面中单独定义的`goHome`方法
   - 移除这些重复定义，依赖mixin中的实现

3. **验证修改**：
   - 检查各页面功能是否正常
   - 确保导航到首页的行为一致

## 预期效果
- 所有页面共享同一个`goHome`方法实现
- 代码冗余减少，维护成本降低
- 导航行为统一，用户体验一致

## 技术要点
- 利用Vue的mixin机制实现方法复用
- 统一API调用，避免不同API导致的行为差异
- 确保mixin在所有需要的页面中正确引入