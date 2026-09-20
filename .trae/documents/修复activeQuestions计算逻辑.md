## 问题确认

`activeQuestions` 使用 `swiperList.slice(start, end + 1)` 获取题目，但当 `swiperList` 顺序与 `simplifiedQuestionList` 不一致时，获取的题目顺序也是错的。

例如：
- 57题在 `swiperList` 中的索引是15
- `activeQuestions` = `swiperList.slice(14, 17)` = [`swiperList[14]`, `swiperList[15]`, `swiperList[16]`]
- 如果 `swiperList[14]` 是42题，用户向左滑动就会看到42题

## 修复方案

修改 `activeQuestions` 计算属性：
1. 获取当前题号（`displayQuestionIndex`）
2. 从 `simplifiedQuestionList` 获取前一题、当前题、后一题的uid
3. 在 `swiperList` 中查找这些uid对应的题目
4. 按顺序返回包含这3个题目的数组

## 修改位置

文件：`c:\Users\44343\jingjiexi\my_client1\subpages\exam\components\commonQuestion.vue`

修改 `activeQuestions` 计算属性（第780-808行）