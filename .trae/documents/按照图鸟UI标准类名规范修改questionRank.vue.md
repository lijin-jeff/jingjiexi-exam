修改 `questionRank.vue` 文件第272行的类名，使其符合图鸟UI标准规范：

**修改内容：**
- 将 `tn-p-20` 改为 `tn-padding-sm`（sm基准值对应20rpx）
- 将 `tn-justify-center` 改为 `tn-flex-row-center`（水平居中对齐的标准格式）

**修改前：**
```vue
<view class="tn-p-20 tn-flex tn-justify-center">
```

**修改后：**
```vue
<view class="tn-padding-sm tn-flex tn-flex-row-center">
```

这样修改后，代码将完全符合图鸟UI的类名规范，确保样式的正确性和可维护性。