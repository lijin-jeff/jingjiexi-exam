## 问题分析

1. **错误信息**：`SQLSTATE[42S22]: Column not found: 1054 Unknown column 'uid' in 'where clause'`
2. **错误位置**：`c:\Users\44343\jingjiexi\server\app\api\logic\resource\ResourceLogic.php` 第180行
3. **根本原因**：在查询 `User` 模型时使用了不存在的 `uid` 字段作为条件
4. **模型设计差异**：
   - `User` 模型使用 `id` 作为主键
   - `TenantResource` 模型使用 `uid` 作为主键
   - 这导致了命名不一致问题

## 修复方案

1. **修改查询条件**：将 `ResourceLogic.php` 中查询 `User` 模型的条件从 `['uid', '=', $params['user_uid']]` 改为 `['id', '=', $params['user_uid']]`

2. **确认数据类型**：确保 `$params['user_uid']` 是 `User` 模型的 `id` 字段值（从控制器代码可以确认，`$params['user_uid']` 是从 `$this->userId` 获取的，即当前登录用户的 ID）

## 修复步骤

1. 打开 `c:\Users\44343\jingjiexi\server\app\api\logic\resource\ResourceLogic.php` 文件
2. 定位到第180行的查询条件
3. 将 `['uid', '=', $params['user_uid']]` 改为 `['id', '=', $params['user_uid']]`
4. 保存文件

## 预期效果

1. 修复后，积分下载API将不再出现SQL错误
2. 用户可以正常使用积分下载资源
3. 系统将正确扣除用户积分并更新资源下载次数

## 验证方法

1. 调用积分下载API `https://cx4vmw1d.allpp.cn/api/resource.resource/resourceDownloadPoints?uid=6964f5e879852`
2. 检查API返回状态码是否为200
3. 检查API返回数据是否包含正确的下载链接
4. 检查用户积分是否正确扣除
5. 检查资源下载次数是否正确更新

## 影响范围

1. 仅影响积分下载功能
2. 不影响其他功能模块
3. 修复后所有依赖积分下载的功能将恢复正常