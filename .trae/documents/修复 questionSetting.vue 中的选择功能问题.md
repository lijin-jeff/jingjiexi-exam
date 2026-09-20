# 修复 questionSetting.vue 中的选择功能问题

## 问题分析

### 1. 难易程度选择功能失效问题
- **根本原因**：变量名冲突
- **具体表现**：在 `updateSelectedQuestionCount` 方法中，第 626 行设置 `this.exam_type = LevelvalueMap[this.examLevelValue]`，但第 636 行又设置 `this.exam_type = this.selectedQuestionTypesValue.join(',')`，导致难易程度设置被题型值覆盖
- **影响范围**：难易程度选择完全失效，始终使用题型值作为 API 参数

### 2. 题目数量选择功能的动态变化问题
- **根本原因**：缺少动态更新逻辑
- **具体表现**：当用户选择不同题型、题目类型或难易程度时，题目数量选择器没有根据当前条件动态更新可选范围和默认值
- **影响范围**：用户可能选择超出实际可用题数的数量，导致 API 调用异常或显示错误

## 修复方案

### 1. 修复难易程度选择功能
- **变量名调整**：将难易程度对应的变量从 `exam_type` 改为 `exam_level`，避免与题型变量冲突
- **API 参数修正**：在 API 调用中使用正确的变量名传递难易程度参数
- **状态管理优化**：确保 `onExamLevelChange` 事件能正确更新状态并触发相关逻辑

### 2. 实现题目数量选择的动态变化
- **动态更新逻辑**：在 `updateSelectedQuestionCount` 方法中，根据 API 返回的实际可用题数，动态更新 `questionCountList` 中各选项的 `disabled` 状态
- **默认值调整**：当实际可用题数小于当前选择的数量时，自动调整默认值为最接近的可用选项
- **联动机制**：确保题型、题目类型、难易程度的变化都能触发题目数量选择器的更新

## 具体修改点

### 修改点 1：变量名调整
- **文件**：`c:\Users\44343\jingjiexi\my_client1\subpages\exam\questionSetting.vue`
- **位置**：`updateSelectedQuestionCount` 方法
- **修改**：将 `this.exam_type = LevelvalueMap[this.examLevelValue]` 改为 `this.exam_level = LevelvalueMap[this.examLevelValue]`
- **API 参数**：在 `apiSelectedQuestionCount` 和 `submitConfig` 中使用 `exam_level` 参数

### 修改点 2：动态更新题目数量选择
- **文件**：`c:\Users\44343\jingjiexi\my_client1\subpages\exam\questionSetting.vue`
- **位置**：`updateSelectedQuestionCount` 方法
- **修改**：在 API 回调中，根据返回的可用题数，更新 `questionCountList` 中各选项的 `disabled` 状态
- **默认值处理**：当当前选择的数量超出可用范围时，自动调整 `questionCountValue` 为合适的默认值

### 修改点 3：事件处理优化
- **文件**：`c:\Users\44343\jingjiexi\my_client1\subpages\exam\questionSetting.vue`
- **位置**：各选择事件处理方法
- **修改**：确保所有选择事件都能正确触发 `updateSelectedQuestionCount` 方法，保持状态同步

## 预期效果

1. **难易程度选择功能**：用户选择不同难易程度时，系统能正确应用筛选条件，API 调用中包含正确的难度参数

2. **题目数量选择功能**：当用户选择不同筛选条件时，题目数量选择器会根据实际可用题数动态更新可选范围，超出范围的选项会被禁用，默认值会自动调整为合适的选项

3. **整体联动性**：所有筛选条件之间保持良好的联动关系，确保用户选择的合理性和系统响应的准确性