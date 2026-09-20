## 修复内容

### 1. 完善编辑笔记功能
**问题**：当前使用`apiCommonAddComment`（添加评论API）来编辑笔记，这是错误的
**修复方案**：使用`apiCommonEditComment` API来实现真实的编辑功能
**修改位置**：`submitEdit`方法（第611-641行）

### 2. 完善删除笔记功能
**问题**：当前使用模拟删除，没有调用真实的API
**修复方案**：使用`apiCommonDeleteComment` API来实现真实的删除功能
**修改位置**：`confirmDelete`方法（第658-688行）

## 技术实现

### 1. 编辑功能实现
```javascript
// 提交编辑
submitEdit() {
  if (!this.editContent.trim()) {
    this.$func.showToast('请输入笔记内容');
    return;
  }

  this.submittingEdit = true;

  // 使用正确的编辑API
  this.$api.apiCommonEditComment({
    id: this.editingNoteId, // 笔记ID
    content: this.editContent // 编辑后的内容
  }).then(res => {
    if (res && res.code === 1) {
      this.$func.showToast('笔记更新成功');
      this.closeEditPopup();
      this.onRefresh(); // 刷新列表
    } else {
      this.$func.showToast(res && res.msg || '笔记更新失败');
    }
  }).catch(error => {
    console.error('[MyNoteList] 更新笔记失败:', error);
    this.$func.showToast('网络请求失败');
  }).finally(() => {
    this.submittingEdit = false;
  });
}
```

### 2. 删除功能实现
```javascript
// 确认删除
confirmDelete() {
  this.deletingNote = true;
  
  // 使用真实的删除API
  this.$api.apiCommonDeleteComment({
    id: this.deletingNoteId // 笔记ID
  }).then(res => {
    if (res && res.code === 1) {
      this.$func.showToast('笔记删除成功');
      this.noteList.splice(this.deletingNoteIndex, 1);
      this.closeDeletePopup();
    } else {
      this.$func.showToast(res && res.msg || '笔记删除失败');
    }
  }).catch(error => {
    console.error('[MyNoteList] 删除笔记失败:', error);
    this.$func.showToast('网络请求失败');
  }).finally(() => {
    this.deletingNote = false;
  });
}
```

## 修复验证

1. 编辑笔记：点击编辑按钮，修改内容后提交，笔记应更新成功
2. 删除笔记：点击删除按钮，确认后笔记应删除成功
3. 列表刷新：编辑或删除后，列表应自动刷新，显示最新状态
4. 错误处理：网络请求失败时应显示友好提示

## 项目规则检查

检查了以下文件，均符合项目规则：
- `CommentLogic.php`：注释完整，有参数检查和错误处理
- `CommentController.php`：方法定义清晰，参数处理正确，响应返回规范
- `comment.js`：API封装完整，注释清晰，符合项目命名规范

这个修复计划将确保myNoteList.vue中的编辑和删除功能能够正常工作，调用正确的API，提供良好的用户体验。