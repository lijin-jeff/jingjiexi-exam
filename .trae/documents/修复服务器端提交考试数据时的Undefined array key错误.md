## 问题分析
1. **title字段错误**：数据库要求'title'字段必须有值，但在$examinationHistory数组初始化时没有添加默认值，只有在特定条件分支下才会设置
2. **calculateUserIntegral方法未使用**：第1671-1672行定义了calculateUserIntegral方法，但在processUserAnswers方法中没有被调用，导致代码重复和冗余

## 修复方案
### 1. 修复title字段错误
在$examinationHistory数组初始化时添加默认title值，确保在所有代码路径中都有title值

### 2. 优化calculateUserIntegral方法使用
在processUserAnswers方法中调用calculateUserIntegral方法，替换重复的积分计算代码，提高代码复用性和可维护性

## 修复步骤
1. **修改ExaminationLogic.php文件**：
   - 在第944行初始化$examinationHistory数组时添加默认title值
   - 在processUserAnswers方法中调用calculateUserIntegral方法，替换重复的积分计算代码

### 预期效果
- 修复"Field 'title' doesn't have a default value"错误
- 提高代码复用性和可维护性
- 确保考试历史记录保存成功

## 具体修改代码

### 1. 添加title字段默认值
```php
// 在第944行的$examinationHistory数组中添加title字段
'accuracy_rate'      => 0, // 正确率（%）
'create_time'        => $nowMillis, // 创建时间（13位时间戳）
'update_time'        => $now, // 更新时间（10位时间戳）
'delete_time'        => null, // 删除时间（10位时间戳）
'title'              => date('Y-m-d H:i:s', $now), // 默认标题，确保所有情况都有值
```

### 2. 在processUserAnswers方法中调用calculateUserIntegral方法

#### 替换第1733-1746行的积分计算代码：
```php
// 根据题目类型加分
// 使用calculateUserIntegral方法计算积分
self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
```

#### 替换第1775-1788行的积分计算代码：
```php
// 根据题目类型和特定配置加分
// 使用calculateUserIntegral方法计算积分
self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
```

#### 替换传统答案比较方法中的积分计算代码（约第1849行附近）：
```php
// 根据题目类型和特定配置加分
// 使用calculateUserIntegral方法计算积分
self::calculateUserIntegral($questionExamType, $questionUid, $scoreConfigs, $examinationHistory);
```