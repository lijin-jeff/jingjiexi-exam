# 检查prepareQuestionQuery方法是否正确处理共用参数和查询条件

## 分析现状

1. **prepareQuestionQuery方法**：
   - 已实现，处理通用参数并构建查询条件
   - 返回query对象、处理后的参数和排序方法

2. **getQuestionStructure方法**：
   - 未使用prepareQuestionQuery方法
   - 自行构建查询条件
   - 支持按比例获取题目

3. **orderOptionList方法**：
   - 未使用prepareQuestionQuery方法
   - 自行构建查询条件
   - 支持按比例获取题目和多种筛选条件

## 问题分析

1. **参数处理**：
   - prepareQuestionQuery方法已处理大部分共用参数
   - 但getQuestionStructure和orderOptionList方法未使用它

2. **查询条件构建**：
   - prepareQuestionQuery方法已构建大部分查询条件
   - 但两个方法未使用它，导致代码冗余

3. **排序规则**：
   - prepareQuestionQuery方法只返回randomType作为排序方法
   - 可能不满足getQuestionStructure方法的排序需求

4. **按比例获取题目**：
   - prepareQuestionQuery方法未处理按比例获取题目的逻辑
   - 两个方法都需要此逻辑

## 解决方案

1. **修改getQuestionStructure方法**：
   - 使用prepareQuestionQuery方法处理参数和构建查询条件
   - 保持其特有的排序逻辑

2. **修改orderOptionList方法**：
   - 使用prepareQuestionQuery方法处理参数和构建查询条件
   - 保持其特有的按比例获取题目逻辑

3. **验证修改后的方法**：
   - 确保两个方法都能正确使用prepareQuestionQuery方法
   - 确保查询结果一致

## 预期结果

- getQuestionStructure和orderOptionList方法使用prepareQuestionQuery方法处理共用参数
- 两个方法的查询条件构建逻辑一致
- 代码冗余减少，维护性提高
- 查询结果一致，避免导航和显示错误