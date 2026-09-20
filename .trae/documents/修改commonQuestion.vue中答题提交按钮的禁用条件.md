1. **目标**：修改commonQuestion.vue文件中第306-315行的答题提交按钮，当questions\_type=7且mode='reviewOnly'时禁用该按钮

2. **修改内容**：

   * 在tn-button组件上添加:disabled属性

   * 禁用条件：`questionParams.questions_type === 7 && currentMode === 'reviewOnly'`

   * questionParams.questions\_type：从组件的questionParams数据属性中获取，代表题目类型

   * currentMode：组件的当前模式，'reviewOnly'表示背题模式

3. **代码变更**：

   ```vue
   <tn-button 
     v-if="swiperCurrentIndex === swiperList.length - 1"
     size="sm" 
     shape="round"
     font-color="tn-color-white"
     background-color="tn-bg-red"
     :disabled="questionParams.questions_type === 7 && currentMode === 'reviewOnly'"
     @click="handleSubmitExam"
   >
     答题提交
   </tn-button>
   ```

4. **预期效果**：

   * 当题目类型为7且当前模式为背题模式时，答题提交按钮将被禁用

   * 禁用状态下，按钮不可点击，视觉上会有禁用样式

   * 其他情况下，按钮保持正常可用状态

5. **技术要点**：

   * 使用Vue的动态绑定语法:disabled

   * 正确引用组件的data属性

   * 确保条件判断的准确性（严格相等比较）

