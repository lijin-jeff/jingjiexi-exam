## 问题分析

从analysis.vue跳转后commonQuestion.vue不加载题目的根本原因：

1. **API调用错误**：在`fetchQuestionList`方法中，使用`this.questionType`来选择API方法，但analysis.vue传递的examSettings中缺少`questionType`属性
2. **初始化逻辑问题**：mounted钩子中只有当`shouldStartNewPractice`为true时才调用`startNewPractice()`，但从全局状态获取`currentExamSettings`时也应该调用
3. **questionType初始化问题**：`this.questionType`直接从`this.questionParams.questionType`赋值，没有默认值

## 修复方案

1. **修复fetchQuestionList方法**：
   - 直接使用`this.questionParams.questions_type`作为API选择的依据，而不是依赖`this.questionType`
   - 或者为`this.questionType`设置默认值，确保API选择逻辑正常执行

2. **修复mounted钩子的初始化逻辑**：
   - 当从全局状态获取到`currentExamSettings`时，也应该调用`startNewPractice()`
   - 确保所有初始化路径都会触发题目加载

3. **修复questionType初始化**：
   - 在data中为`this.questionType`设置默认值
   - 或者在watch中监听`this.questionParams`的变化，及时更新`this.questionType`

4. **添加必要的调试日志**：
   - 在关键位置添加调试日志，方便跟踪问题

## 具体修改点

1. **修改commonQuestion.vue的fetchQuestionList方法**：
   - 将API选择逻辑从依赖`this.questionType`改为直接使用`this.questionParams.questions_type`
   - 或者确保`this.questionType`始终有有效值

2. **修改commonQuestion.vue的mounted钩子**：
   - 当从全局状态获取到`currentExamSettings`时，设置`shouldStartNewPractice = true`
   - 确保`startNewPractice()`被正确调用

3. **修改commonQuestion.vue的questionType初始化**：
   - 在data中为`this.questionType`添加默认值
   - 或者在watch中监听`this.questionParams`的变化，及时更新`this.questionType`

## 预期效果

修复后，从analysis.vue跳转commonQuestion.vue时，会：
1. 正确初始化questionParams
2. 正确选择API方法
3. 成功调用API获取题目
4. 正常渲染题目列表