## 修复tn-select组件默认选中状态问题

### 问题分析
所有的tn-select组件会记录上一次选中的状态，打开后会自动选中，需要根据当前绑定的值设置defaultValue属性。

### 解决方案
为每个tn-select组件添加defaultValue属性，根据当前绑定的变量值动态计算默认选中的索引。

### 具体修改

1. **章节选择组件** (line 39)
   - 添加defaultValue属性，根据selectedChapter的值计算默认选中索引
   - 对于多列联动模式，需要处理父子章节的选中状态

2. **难度选择组件** (line 61)
   - 添加defaultValue属性，根据selectedDifficulty的值计算默认选中索引

3. **知识点选择组件** (line 107)
   - 添加defaultValue属性，处理多选模式的默认选中

4. **标签选择组件** (line 130)
   - 添加defaultValue属性，处理多选模式的默认选中

### 技术实现
根据Tuniao UI文档，defaultValue是一个数组，用于设置默认选中的值：
- 单列模式：defaultValue数组长度为1，元素为选中项的索引
- 多列联动模式：defaultValue数组长度与列数相同，元素为每列选中项的索引
- 多选模式：需要特殊处理选中项

通过计算属性或方法动态生成defaultValue数组，确保每次打开select组件时都能正确显示当前选中的状态。