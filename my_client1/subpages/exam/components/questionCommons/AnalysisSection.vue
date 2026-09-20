<template>
  <!-- 试题解析组件 -->
  <view class="analysis-section">
    <!-- 按顺序显示四个功能区域 -->
    <view>
      <!-- 章节区域 -->
      <view class="section-block">
        <view class="section-header">
          <text class="tn-icon-folder tn-color-indigo tn-margin-right-xs" />
          <text class="section-title tn-text-bold tn-text-lg">
            章节信息
          </text>
        </view>
        <view class="content-wrapper tn-padding-left-sm tn-padding-right-sm">
          <view
            v-if="formattedChapter"
            class="chapter-info"
          >
            <text>{{ formattedChapter }}</text>
          </view>
          <view
            v-else
            class="no-content"
          >
            <text class="tn-color-grey">
              暂无章节信息
            </text>
          </view>
        </view>
      </view>
    </view>
      
    <!-- 试题解析区域 -->
    <view class="section-block">
      <view class="section-header">
        <text class="tn-icon-notebook tn-color-blue tn-margin-right-xs" />
        <text class="section-title tn-text-bold tn-text-lg">
          试题解析
        </text>
      </view>
      <view class="content-wrapper tn-padding-left-sm tn-padding-right-sm">
        <mp-html
          v-if="analysis"
          class="chapter-info"
          :content="analysis"
          :lazy-load="true"
        />
        <view v-else class="no-content">
          <text class="tn-color-grey">
            暂无解析内容
          </text>
        </view>
      </view>
    </view>

      
    <!-- 名师点评区域 -->
    <view class="section-block">
      <view class="section-header">
        <text class="tn-icon-education tn-color-cyan--dark tn-margin-right-xs" />
        <text class="section-title tn-text-bold tn-text-lg">
          名师点评
        </text>
      </view>
      <view class="content-wrapper tn-padding-left-sm tn-padding-right-sm">
        <mp-html
          v-if="commentaries"
          class="chapter-info"
          :content="commentaries"
          :lazy-load="true"
        />
        <view
          v-else
          class="no-content"
        >
          <text class="tn-color-grey">
            暂无名师点评
          </text>
        </view>
      </view>
    </view>
      
    <!-- 知识点区域 -->
    <view class="section-block">
      <view class="section-header">
        <text class="tn-icon-tip tn-color-purple tn-margin-right-xs" />
        <text class="section-title tn-text-bold tn-text-lg">
          考核知识点
        </text>
      </view>
      <view class="content-wrapper tn-padding-left-sm tn-padding-right-sm">
        <view
          v-if="knowledge && (Array.isArray(knowledge) ? knowledge.length > 0 : true)" 
          class="knowledge-tags"
        >
          <view class="tn-tag-content  tn-text-justify">
            <view v-for="(item, index) in knowledge" 
            :key="index" 
            class="tn-tag-content__item tn-margin-right tn-margin-bottom-xs tn-round tn-text-sm" 
            :class="['tn-color-white', $tn.color.getRandomColorClass('bg')+'--dark']"
            @click="handleKnowledgeClick(item)"
            >
              <text class="tn-tag-content__item--prefix">#</text> {{ item.title }}
            </view>
          </view>
        </view>
        <view
          v-else
          class="no-content"
        >
          <text class="tn-color-grey">
            暂无知识点关联
          </text>
        </view>
      </view>
    </view>
    
    <!-- 知识点弹窗 -->
    <tn-popup 
      v-model="showKnowledgePopup" 
      mode="center"
      width="80%"
      :border-radius="20"
      :safe-area-inset-bottom="true"
    >
      <view class="knowledge-popup">
        <view class="popup-title tn-padding">
          <text class="tn-text-bold tn-text-xl">
            {{ currentKnowledge.title }}
          </text>
        </view>
        <scroll-view class="popup-content tn-padding" scroll-y="true">
          <mp-html class="tn-text-df" :content="currentKnowledge.content"/>
        </scroll-view>
        <view class="popup-footer tn-padding">
          <tn-button 
            size="sm" 
            fontSize="32"
            shape="round"
            width="100%"
            @click="showKnowledgePopup = false"
          >
            关闭
          </tn-button>
        </view>
      </view>
    </tn-popup>
  </view>
</template>

<script>
export default {
  name: 'AnalysisSection',
  props: {
    // 题目ID
    questionId: {
      type: String,
      default: ''
    },
    // 题目名称
    questionName: {
      type: String,
      default: ''
    },
    // 章节信息
    chapter: {
      type: [String, Object],
      default: ''
    },
    // 解析内容
    analysis: {
      type: String,
      default: ''
    },
    // 名师点评
    commentaries: {
      type: String,
      default: ''
    },
    // 知识点对象或数组
    knowledge: {
      type: [Object, Array, String],
      default: null
    },
  },
  data() {
    return {
      editAnalysis: this.analysis || '',
      editCommentaries: this.commentaries || '',
      editKnowledgeTitle: this.getKnowledgeTitle(),
      editChapter: this.formatChapter(this.chapter) || '',
      showKnowledgePopup: false,
      currentKnowledge: {
        title: '',
        content: ''
      }
    }
  },
  computed: {
    // 知识点标题
    knowledgeTitle() {
      if (!this.knowledge) return ''
      if (typeof this.knowledge === 'object') return this.knowledge.title || ''
      return String(this.knowledge)
    },
    
    // 知识点内容
    knowledgeContent() {
      if (!this.knowledge) return ''
      if (typeof this.knowledge === 'object') return this.knowledge.content || ''
      return ''
    },
    
    // 格式化后的章节显示文本
    formattedChapter() {
      return this.formatChapter(this.chapter)
    }
  },
  watch: {
    analysis: {
      handler(newVal) {
          this.editAnalysis = newVal;
      },
      immediate: true
    },
    commentaries: {
      handler(newVal) {
          this.editCommentaries = newVal;
      },
      immediate: true
    },
    knowledge: {
      handler(newVal) {
          this.editKnowledgeTitle = this.getKnowledgeTitle(newVal)
      },
      immediate: true,
      deep: true
    },
    chapter: {
      handler(newVal) {
          this.editChapter = this.formatChapter(newVal);
      },
      immediate: true,
      deep: true
    },
  },
  methods: {
    // 安全获取知识点标题
    getKnowledgeTitle(knowledge = this.knowledge) {
      if (!knowledge) return ''
      if (Array.isArray(knowledge)) return knowledge.join(',')
      if (typeof knowledge === 'object') return knowledge.title || ''
      return String(knowledge)
    },
    
    // 格式化章节路径
    formatChapter(chapterData = this.chapter) {
      if (!chapterData) return ''
      if (typeof chapterData === 'string') return chapterData
      
      // 处理对象类型的章节数据
      const chapters = []
      
      // 修复：递归获取所有父级章节标题（支持对象格式）
      const collectParentChapters = (parentData) => {
        if (!parentData) return
        
        // 处理数组格式（向下兼容）
        if (Array.isArray(parentData)) {
          parentData.forEach(parent => {
            if (parent && parent.title) {
              chapters.push(parent.title)
              if (parent.parent) {
                collectParentChapters(parent.parent)
              }
            }
          })
        }
        // 处理对象格式（当前后端返回的格式）
        else if (typeof parentData === 'object' && parentData.title) {
          chapters.push(parentData.title)
          // 递归处理父级的父级
          if (parentData.parent) {
            collectParentChapters(parentData.parent)
          }
        }
      }
      
      // 添加父级章节（按从根到父的顺序）
      if (chapterData.parent) {
        collectParentChapters(chapterData.parent)
      }
      
      // 反转数组确保从根到当前章节的顺序
      chapters.reverse()
      
      // 添加当前章节标题
      if (chapterData.title) {
        chapters.push(chapterData.title)
      }

      // 用 > 连接章节路径
      return chapters.join(' > ')
    },    
    // 处理知识点点击
    handleKnowledgeClick(knowledgeItem) {
      if (knowledgeItem && typeof knowledgeItem === 'object' && knowledgeItem.content) {
        this.currentKnowledge = {
          title: knowledgeItem.title,
          content: knowledgeItem.content
        }
        this.showKnowledgePopup = true
      }
    },
    
    // 保存章节信息
    saveChapter() {
      this.$emit('save-chapter', this.editChapter);
    },
    
    // 保存解析
    saveAnalysis() {
      this.$emit('save-analysis', this.editAnalysis);
    },
    
    // 保存名师点评
    saveCommentaries() {
      this.$emit('save-commentaries', this.editCommentaries);
    },
    
    // 保存知识点
    saveKnowledge() {
      // 将逗号分隔的字符串转换为标签数组
      const tags = this.editKnowledgeTitle.split(',').map(tag => tag.trim()).filter(tag => tag);
      this.$emit('save-knowledge', tags.length > 1 ? tags : tags[0] || '');
    },
    
    // 取消编辑
    cancelEdit() {
      this.$emit('cancel-edit');
    }
  }
}
</script>

<style scoped lang="scss">
.analysis-section {
  background-color: #F8F9FA;
  overflow: hidden;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  margin-bottom: 20rpx;
}

.section-block {
    background-color: #FFFFFF;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.03);
    overflow: hidden;
    transition: all 0.3s ease;
  }

.section-header {
  background-color: #F8F9FA;
  padding: 20rpx 30rpx;
  border-bottom: 1rpx solid #E9ECEF;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.section-title {
  color: #333333;
  font-size: 32rpx;
  flex: 1;
}

/* 内容包装器样式 */
.content-wrapper {
  background-color: #FFFFFF;
  border-left: none;
  margin-bottom: 0;
}

/* 富文本样式优化 */
.mp-html img {
  max-width: 100% !important;
  height: auto !important;
  border-radius: 8rpx;
  margin: 16rpx 0;
}

.mp-html p {
  margin-bottom: 16rpx;
  line-height: 1.6;
  color: #333333;
}

.mp-html pre {
  background-color: #f5f5f5;
  padding: 16rpx;
  border-radius: 8rpx;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  font-size: 28rpx;
  margin: 16rpx 0;
}

/* 编辑模式样式 */
.edit-mode .analysis-textarea,
.edit-mode .comment-textarea,
.edit-mode .knowledge-input,
.edit-mode .chapter-input {
  width: 100%;
  border-radius: 10rpx;
  transition: all 0.3s ease;
  border: 1rpx solid #E9ECEF;
  min-height: 200rpx;
}

.edit-mode .analysis-textarea:focus,
.edit-mode .comment-textarea:focus,
.edit-mode .knowledge-input:focus,
.edit-mode .chapter-input:focus {
  border-color: #1677ff;
  box-shadow: 0 0 0 2rpx rgba(22, 119, 255, 0.2);
}

.edit-mode .edit-actions {
  gap: 20rpx;
  margin-top: 16rpx;
}

/* 空内容样式 */
.no-content {
  padding: 20rpx 0;
  text-align: center;
  color: #999;
}


.tn-color-grey {
  cursor: pointer;
  transition: all 0.3s ease;
}

/* 章节信息样式 */
.chapter-info {
  font-size: 28rpx;
  color: #333;
  padding: 16rpx 0;
  display: flex;
  align-items: center;
  line-height: 2;
}

/* 知识点标签样式 */
.knowledge-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 20rpx;
  padding: 20rpx 0;
  
  .tag-prefix,
  .tag-suffix {
    color: inherit;
    font-weight: bold;
    margin: 0 4rpx;
  }
}

.knowledge-tag {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* 知识点弹窗样式 */
.knowledge-popup {
  background-color: #FFFFFF;
  border-radius: 20rpx;
  overflow: hidden;
  
  .popup-title {
    text-align: center;
    border-bottom: 1rpx solid #F0F0F0;
  }
  
  .popup-content {
    max-height: 600rpx;
    overflow-y: scroll;
    -webkit-overflow-scrolling: touch;
    line-height: 1.8;
  }
  
  .popup-footer {
    text-align: center;
    border-top: 1rpx solid #F0F0F0;
  }
}

/* 响应式设计 */
@media screen and (max-width: 375px) {
  .section-header {
    padding: 16rpx 20rpx;
  }
  
  .section-title {
    font-size: 30rpx;
  }
}

@media screen and (min-width: 414px) {
  .section-block {
    margin-bottom: 40rpx;
  }
  
  .section-header {
    padding: 24rpx 36rpx;
  }
}
  /* 标签内容 start*/
  .tn-tag-content {
    &__item {
      display: inline-block;
      line-height: 45rpx;
      padding: 5rpx 20rpx;
      
      &--prefix {
        padding-right: 10rpx;
      }  
    }
  }
  /* 标签内容 end*/
</style>