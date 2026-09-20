# 将commonQuestion.vue剥离为独立页面的可行性分析

## 1. 组件现状分析

### 1.1 组件角色
commonQuestion.vue目前作为questionSetting.vue的子组件，通过条件渲染（`v-if="showQuestion"`）控制显示与隐藏，主要负责答题界面的展示和交互。

### 1.2 核心功能
- 答题界面展示（支持多种题型：单选、多选、判断、填空、简答、案例题）
- 题目切换与导航
- 答案提交与评分
- 进度保存与恢复
- 多种答题模式（答题模式、学练结合、背题模式）
- 计时器功能

### 1.3 初始化依赖
- 从全局状态获取examSettings（`getApp().globalData.currentExamSettings`）
- 从本地存储获取questionParams
- 通过props或refs调用`startNewPractice`或`restoreProgress`方法启动

## 2. 剥离可行性分析

### 2.1 技术可行性
✅ **组件结构完整**：commonQuestion.vue具有独立的template、script和style，符合Vue页面的基本结构
✅ **依赖关系清晰**：主要依赖全局状态和本地存储，可通过页面参数传递替代
✅ **生命周期完整**：包含onLoad、mounted、beforeDestroy等完整的生命周期钩子
✅ **功能独立**：答题功能与设置功能分离，可独立运行

### 2.2 功能完整性
✅ **答题核心功能完整**：包含题目加载、答案提交、进度保存等所有答题必需功能
✅ **初始化逻辑可调整**：可将原有的外部调用方法（`startNewPractice`、`restoreProgress`）集成到组件生命周期中
✅ **模式支持完整**：保留所有答题模式的支持

### 2.3 依赖关系调整
| 现有依赖 | 调整方案 |
|---------|---------|
| 全局状态传递examSettings | 改为页面参数传递 |
| questionSetting.vue调用startNewPractice | 集成到组件onLoad钩子中 |
| questionSetting.vue调用restoreProgress | 集成到组件onLoad钩子中 |
| 本地存储questionParams | 保留，作为备选初始化方案 |

### 2.4 路由配置
需要在路由配置中添加commonQuestion页面的路由，支持从questionSetting.vue跳转到该页面。

## 3. 实施建议

### 3.1 组件调整
1. **修改组件初始化逻辑**：将onLoad钩子改为从页面参数获取初始化数据
2. **集成启动方法**：将`startNewPractice`和`restoreProgress`方法的调用集成到组件生命周期中
3. **添加页面标题**：根据答题模式动态设置页面标题
4. **优化参数传递**：使用页面参数替代全局状态传递

### 3.2 路由配置
```javascript
// 在路由配置文件中添加
{
  path: '/subpages/exam/commonQuestion',
  name: 'commonQuestion',
  component: () => import('@/subpages/exam/components/commonQuestion.vue')
}
```

### 3.3 questionSetting.vue调整
1. **移除commonQuestion组件引入**
2. **移除showQuestion状态变量**
3. **修改submitConfig方法**：从条件渲染改为页面跳转，传递examSettings作为查询参数
4. **调整进度恢复逻辑**：通过页面参数传递进度数据

### 3.4 其他相关页面调整
检查所有调用commonQuestion组件的地方，改为页面跳转方式

## 4. 优势与收益

### 4.1 提高组件复用性
- 可从多个入口进入答题页面
- 支持直接分享答题页面链接
- 便于在其他功能模块中复用

### 4.2 分离关注点
- 答题功能与设置功能解耦
- 便于独立维护和测试
- 代码结构更清晰

### 4.3 优化用户体验
- 支持直接访问答题页面
- 减少页面切换的闪烁
- 提高页面加载速度

## 5. 潜在风险与解决方案

| 风险 | 解决方案 |
|------|---------|
| 进度保存与恢复逻辑需要调整 | 确保页面参数传递完整，保留本地存储作为备选 |
| 现有调用点需要修改 | 全面检查代码，修改所有调用commonQuestion组件的地方 |
| 页面参数传递安全性 | 验证页面参数的有效性，添加默认值处理 |
| 全局状态依赖问题 | 逐步移除全局状态依赖，改为显式参数传递 |

## 6. 结论

将commonQuestion.vue剥离为独立页面是**可行的**，具有技术可行性和功能完整性。通过适当的调整，可以实现组件的独立运行，提高复用性和可维护性，优化用户体验。

建议按照上述实施建议逐步推进，确保平滑过渡，减少对现有功能的影响。