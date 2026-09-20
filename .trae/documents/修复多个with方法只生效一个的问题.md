## 问题分析
在`TenantResourceCategoryLists.php`文件中，当使用多个`with`方法进行关联预加载时，只有一个会生效。这是因为在ThinkPHP中，多个独立的`with`方法调用可能会导致关联预加载被覆盖，而不是合并。

## 解决方案
将多个`with`方法合并为一个数组形式的`with`调用，确保所有关联预加载都能被正确处理。

## 修复代码
修改`c:\Users\44343\jingjiexi\server\app\tenantapi\lists\exam\resource\TenantResourceCategoryLists.php`文件中的查询构建器部分：

1. 将第55-64行的多个`with`方法调用合并为一个
2. 使用数组形式包含所有关联预加载
3. 确保`children`关联内部的嵌套`with`也能正确工作

## 预期效果
修复后，所有关联预加载（`children`和`examCategory`）都会被正确执行，子分类数据会包含在返回结果中，解决了数据库中有parent_uid不为空的数据但未查询出来的问题。