# 完善 examEdit/index.vue 筛选和排序功能

## 实现目标

使用 Tuni UI 组件完善 examEdit/index.vue 中的筛选和排序功能，参考 countdown/list.vue 的实现方式，并从相关 API 获取筛选数据。

## 实现方案

### 1. 添加筛选和排序状态变量

* 在 `data()` 中添加筛选相关状态变量：`statusFilter`, `typeFilter`, `levelFilter`, `chapterFilter`, `knowledgeFilter`

* 添加排序相关状态变量：`sortBy`, `currentSortLabel`

* 添加筛选选项数据结构：`statusFilterOptions`, `typeFilterOptions`, `levelFilterOptions`, `chapterFilterOptions`, `knowledgeFilterOptions`

* 添加排序选项数据结构：`sortOptions`

* 添加选择器显示状态：`showStatusSelect`, `showTypeSelect`, `showLevelSelect`, `showChapterSelect`, `showKnowledgeSelect`, `showSortSelect`

### 2. 实现筛选和排序选择组件

* 在模板中添加 `tn-select` 组件用于筛选和排序选择

* 参考 countdown/list.vue 的实现方式，为每个筛选条件添加选择器

* 确保选择器的显示和隐藏逻辑正确

### 3. 实现筛选和排序事件处理方法

* 实现 `onStatusSelectConfirm`, `onTypeSelectConfirm`, `onLevelSelectConfirm`, `onChapterSelectConfirm`, `onKnowledgeSelectConfirm` 方法

* 实现 `onSortSelectConfirm` 方法

* 实现筛选和排序变更时的列表刷新逻辑

### 4. 修改 API 调用以支持筛选和排序

* 修改 `fetchExamList` 方法，添加筛选和排序参数

* 确保 API 调用时包含正确的筛选和排序参数

### 5. 添加 API 调用以获取筛选选项数据

* 添加 `loadFilterOptions` 方法，从 API 获取章节、知识点等筛选选项

* 确保筛选选项数据的加载和更新逻辑正确

## 具体修改步骤

### 步骤1：添加筛选和排序状态变量

* 在 `data()` 中添加筛选和排序相关的状态变量

* 添加筛选选项和排序选项的数据结构

### 步骤2：添加计算属性

* 添加 `currentStatusLabel`, `currentTypeLabel`, `currentLevelLabel`, `currentChapterLabel`, `currentKnowledgeLabel`, `currentSortLabel` 计算属性

* 确保计算属性能正确显示当前选中的筛选和排序选项

### 步骤3：添加筛选和排序选择组件

* 在模板中添加 `tn-select` 组件用于筛选和排序选择

* 参考 countdown/list.vue 的实现方式，为每个筛选条件添加选择器

### 步骤4：实现筛选和排序事件处理方法

* 实现筛选和排序选择确认的方法

* 实现筛选和排序变更时的列表刷新逻辑

### 步骤5：修改 fetchExamList 方法

* 修改 `fetchExamList` 方法，添加筛选和排序参数

* 确保 API 调用时包含正确的筛选和排序参数

### 步骤6：添加 API 调用以获取筛选选项数据

* 添加 `loadFilterOptions` 方法，从 API 获取章节、知识点等筛选选项

* 在组件初始化时调用该方法加载筛选选项数据

### 步骤7：测试和优化

* 测试筛选和排序功能是否正常工作

* 确保 API 调用正确，筛选和排序参数能正确传递

* 优化筛选和排序的用户体验，确保操作流畅

## 技术要点

* 优先使用 Tuni UI 组件，保持与项目其他部分的一致性

* 参考 countdown/list.vue 的实现方式，确保代码结构清晰

* 从相关 API 获取筛选数据，确保筛选选项的准确性

* 确保筛选和排序功能不影响页面性能

* 考虑不同网络速度下的用户体验，避免筛选选项加载过慢导致用户等待

* 保持代码的可维护性，确保筛选和排序逻辑与业务逻辑分离

