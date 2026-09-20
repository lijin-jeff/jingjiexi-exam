# 整合订阅功能到UserSubscribeController

## 1. 分析现有代码结构
- **控制器**：`UserSubscribeController` 继承自 `BaseApiController`，提供统一的订阅/取消订阅和状态查询功能
- **逻辑层**：`SubscribeLogic` 处理具体的订阅业务逻辑，支持多种订阅类型
- **模型层**：`UserSubscribe` 模型已存在，用于处理订阅记录
- **响应方式**：使用 `success()` 和 `fail()` 方法返回统一格式响应

## 2. 整合新功能
将以下三个新方法添加到 `UserSubscribeController` 中：

### 2.1 recordSubscribe() 方法
- **功能**：记录用户一次性订阅授权状态
- **调整**：
  - 使用 `userId` 而非 `openid`（项目现有认证机制）
  - 使用 `success()`/`fail()` 方法返回响应
  - 调整参数获取方式以符合项目规范

### 2.2 checkCanPush() 方法
- **功能**：校验模板是否可推送
- **调整**：
  - 使用 `userId` 而非 `openid`
  - 添加核心判断逻辑：未推送 + 7天内有效期
  - 使用项目统一响应格式

### 2.3 updatePushStatus() 方法
- **功能**：推送消息后更新状态
- **调整**：
  - 使用 `userId` 而非 `openid`
  - 调整参数获取和响应方式

## 3. 代码调整要点
- **模型引用**：调整为正确的命名空间 `app\common\model\user\userSubscribe`
- **参数获取**：使用 `$this->request->post()`/`$this->request->get()` 而非 `Request::post()`/`Request::get()`
- **响应格式**：统一使用 `success()`/`fail()` 方法
- **命名规范**：遵循项目现有命名规范
- **错误处理**：使用 try-catch 包裹核心业务逻辑

## 4. 整合后的文件结构
```php
class UserSubscribeController extends BaseApiController
{
    // 现有方法：subscribe() 和 status()
    
    // 新添加方法：recordSubscribe()
    // 新添加方法：checkCanPush()
    // 新添加方法：updatePushStatus()
}
```

## 5. 技术要点
- 保持代码风格一致性
- 遵循项目现有架构设计
- 确保新功能与现有功能兼容
- 不破坏现有业务逻辑
- 使用项目现有工具类和方法