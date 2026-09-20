<template>
  <view>
    <!-- 水平排列的tab和图标 -->
    <view class="libtablist tn-flex tn-flex-row-between tn-flex-col-center tn-padding-left tn-padding-right">
      <view class="tn-flex-1" style="overflow: hidden;">
        <tn-tabs :list="filteredScrollList" :current="current" @change="onTabChange" barWidth="80" activeColor="#000000" isScroll="true" bold="true"
          :fontSize="32">
        </tn-tabs>
      </view>
      <view class="tn-margin-left" style="margin-left: 20rpx;" @click="onManagerClick">
          <text class="tn-icon-align tn-text-4xl"></text>
      </view>
    </view>

    <!-- 科目管理弹窗 -->
    <tn-popup v-model="localVisible" mode="bottom" height="85%" :closeOnClickOverlay="true" :closeOnPressEscape="true" @close="onPopupClose">
      <view class="custom-navbar" :style="{ backgroundColor: mainColor, height: '88rpx', display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 30rpx' }">
        <view style="display: flex; align-items: center; width: 120rpx;" @click="handleClose">
          <text class="tn-icon-left tn-color-white" style="font-size: 36rpx; margin-right: 10rpx;"></text>
          <text class="tn-color-white" style="font-size: 28rpx;">返回</text>
        </view>
        <view style="flex: 1; text-align: center;">
          <text class="tn-text-bold tn-text-xl tn-color-white" style="font-size: 36rpx;">{{ title }}</text>
        </view>
        <view style="width: 120rpx; text-align: right;">
          <tn-button 
            backgroundColor="transparent" 
            fontColor="#ffffff" 
            fontSize="32" 
            padding="0 20rpx" 
            @click="handleSave"
            :plain="false"
            size="sm"
          >
            完成
          </tn-button>
        </view>
      </view>
      
      <view class="business-content"  style="background-color:#f5f5f5">
        <scroll-view class="subject-scroll" scroll-y>
          <view class="subject-section tn-margin tn-bg-white tn-border-radius tn-shadow-sm">
            <view class="section-title-text text-center">管理我的科目</view>
            <view class="section-desc text-center">长按科目拖动调整顺序，点击删除</view>
            
            <!-- 拖拽容器 -->
            <view 
              v-if="localMySubjects.length > 0" 
              class="my-subjects-drag-container"
              ref="dragContainer"
            >
              <!-- 修复：使用简单key值，兼容非H5平台 -->
              <view 
                v-for="(subject, index) in localMySubjects" 
                :key="index"
                class="subject-item subject-item-add"
                @touchstart="handleTouchStart($event, index)"
                @touchmove.stop.prevent="handleTouchMove($event, index)"
                @touchend="handleTouchEnd($event, index)"
                @touchcancel="handleTouchEnd($event, index)"
                :class="{ 
                  'dragging': dragState.isTouchDragging && dragState.currentIndex === index, 
                  'drag-over': dragState.dragOverIndex === index 
                }"
                :style="{ 
                  transform: dragState.isTouchDragging && dragState.currentIndex === index 
                    ? `translate(${dragState.offsetX}px, ${dragState.offsetY}px) scale(1.03)` 
                    : 'none',
                  zIndex: dragState.isTouchDragging && dragState.currentIndex === index ? 100 : 'auto',
                  opacity: dragState.isTouchDragging && dragState.currentIndex === index ? 0.8 : 1,
                  transition: dragState.isSorting ? 'transform 0.3s ease' : 'all 0.1s ease'
                }"
              >
                <view class="subject-item-text">{{ subject.name }}</view>
                <view class="subject-item-delete" @click.stop="handleRemove(index)">
                  <text class="tn-icon-close tn-text-sm"></text>
                </view>
              </view>
            </view>
              
            <view v-if="localMySubjects.length === 0" class="empty-subjects tn-padding tn-text-center">
              暂无科目，点击下方科目添加
            </view>
          </view>
          
          <view class="subject-section tn-margin tn-bg-white tn-border-radius tn-shadow-sm">
            <view class="section-title-text text-center">全部科目</view>
            
            <view class="all-subjects tn-flex tn-flex-wrap tn-padding">
              <view v-if="loading" class="loading-question-lib tn-margin tn-flex tn-flex-col tn-items-center tn-justify-center w-full">
                <tn-loading type="cycle" color="#0E7DFF" />
                <text class="loading-text tn-margin-top">加载中...</text>
              </view>
              
              <view v-else-if="!loading && allSubjects.length === 0" class="empty-question-lib tn-margin tn-flex tn-flex-col tn-items-center tn-justify-center w-full">
                <tn-empty mode="list" text="暂无科目" text-size="28" />
              </view>
              
              <!-- 修复：使用简单key值，兼容非H5平台 -->
              <view 
                v-for="(subject, index) in allSubjects" 
                :key="index"
                class="subject-item subject-item-add tn-margin-xs"
                @click="handleAdd(subject)"
                :class="{ 'subject-item-added': isSubjectAdded((subject && subject.id) || (subject && subject.uid)) }"
              >
                <view class="subject-item-text">{{ subject.name || subject.title || '未命名科目' }}</view>
                <view class="subject-item-add-icon">
                  <text v-if="!isSubjectAdded((subject && subject.id) || (subject && subject.uid))" class="tn-icon-plus tn-text-sm"></text>
                  <text v-else class="tn-icon-check tn-text-sm"></text>
                </view>
              </view>
            </view>
          </view>
        </scroll-view>
      </view>
    </tn-popup>
  </view>
</template>

<script>
export default {
  name: 'SubjectTabsManager',
  props: {
    scrollList: { type: Array, default: () => [] },
    current: { type: Number, default: 0 },
    visible: { type: Boolean, default: false },
    mySubjects: { type: Array, default: () => [] },
    allSubjects: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    title: { type: String, default: '编辑科目' },
    mainColor: { type: String, default: '#42B476' },
    storageKey: { type: String, default: '' },
    autoSave: { type: Boolean, default: false },
    showToast: { type: Boolean, default: true },
    longPressTime: { type: Number, default: 300 }
  },
  data() {
    return {
      localMySubjects: this.mySubjects.filter(subject => subject && typeof subject === 'object' && (subject.id || subject.uid) && (subject.name || subject.title)),
      localVisible: this.visible,
      dragState: {
        isTouchDragging: false,
        isLongPressTriggered: false,
        isSorting: false,
        startIndex: -1,
        currentIndex: -1,
        dragOverIndex: -1,
        draggedItem: null,
        startX: 0,
        startY: 0,
        offsetX: 0,
        offsetY: 0,
        itemHeight: 80, // 固定项高度（rpx转px后约40px）
        longPressTimer: null
      }
    }
  },
  computed: {
    filteredScrollList() {
      // 确保 scrollList 是数组
      if (!Array.isArray(this.scrollList)) {
        return [{ name: '无考试科目' }]
      }
      const filtered = this.scrollList.filter(item => item && typeof item === 'object' && item.name)
      return filtered.length ? filtered : [{ name: '无考试科目' }]
    },
  },
  watch: {
    mySubjects: {
      handler(newVal) {
        this.localMySubjects = newVal.filter(subject => subject && typeof subject === 'object' && (subject.id || subject.uid) && (subject.name || subject.title))
      },
      deep: true
    },
    visible: {
      handler(newVal) {
        this.localVisible = newVal
      },
      immediate: true
    }
  },
  beforeUnmount() {
    this.clearLongPressTimer();
  },
  methods: {
    // 基础方法
    onTabChange(index) { this.$emit('tab-change', index) },
    onManagerClick() { this.$emit('manager-click') },
    onPopupClose() { this.$emit('update:visible', false); this.$emit('close') },
    handleClose() { this.localVisible = false; this.$emit('update:visible', false); this.$emit('close') },
    
    handleSave() {
      this.storageKey && uni.setStorageSync(this.storageKey, this.localMySubjects);
      this.localVisible = false;
      this.$emit('update:visible', false);
      this.$emit('close');
      this.showSuccessToast('保存成功');
      this.$emit('save', this.localMySubjects);
    },
    
    handleRemove(index) {
      this.localMySubjects.splice(index, 1);
      this.autoSave && this.storageKey && uni.setStorageSync(this.storageKey, this.localMySubjects);
      this.$emit('remove', index, [...this.localMySubjects]);
    },
    
    handleAdd(subject) {
      const processedSubject = {
        id: subject.id || subject.uid || Date.now().toString(), // 确保有唯一id
        name: subject.name || subject.title || '未命名科目',
        ...subject
      };
      
      if (!this.isSubjectAdded(processedSubject.id)) {
        this.localMySubjects.push(processedSubject);
        this.autoSave && this.storageKey && uni.setStorageSync(this.storageKey, this.localMySubjects);
        this.$emit('add', processedSubject, [...this.localMySubjects]);
      }
    },
    
    isSubjectAdded(subjectId) {
      return this.localMySubjects.some(item => item.id === subjectId);
    },
    
    showSuccessToast(message) {
      if (this.showToast) {
        if (getApp()?.globalData?.$func?.showToast) {
          getApp().globalData.$func.showToast(message);
        } else {
          uni.showToast({ title: message, icon: 'success' });
        }
      }
    },

    // ===================== 核心拖拽逻辑（保持优化后版本） =====================
    clearLongPressTimer() {
      if (this.dragState.longPressTimer) {
        clearTimeout(this.dragState.longPressTimer);
        this.dragState.longPressTimer = null;
      }
    },

    handleTouchStart(event, index) {
      try {
        this.clearLongPressTimer();
        
        // 兼容多端触摸事件
        const touch = event.touches?.[0] || event.changedTouches?.[0];
        if (!touch) return;

        // 记录起始位置
        const startX = touch.clientX || touch.pageX || 0;
        const startY = touch.clientY || touch.pageY || 0;

        // 初始化拖拽状态
        this.dragState = {
          ...this.dragState,
          isTouchDragging: false,
          isLongPressTriggered: false,
          startIndex: index,
          currentIndex: index,
          dragOverIndex: -1,
          draggedItem: this.localMySubjects[index],
          startX,
          startY,
          offsetX: 0,
          offsetY: 0
        };

        // 长按触发
        this.dragState.longPressTimer = setTimeout(() => {
          this.dragState.isLongPressTriggered = true;
          uni.showToast({ title: '可拖动调整顺序', icon: 'none', duration: 1000 });
        }, this.longPressTime);

      } catch (e) {
        console.error('触摸开始异常:', e);
        this.clearLongPressTimer();
      }
    },

    handleTouchMove(event, index) {
      try {
        // 前置校验
        if (!this.dragState.isLongPressTriggered || index !== this.dragState.currentIndex) return;
        
        // 阻止默认行为
        event.preventDefault?.();
        
        const touch = event.touches?.[0] || event.changedTouches?.[0];
        if (!touch) return;

        // 计算偏移量
        const currentX = touch.clientX || touch.pageX || 0;
        const currentY = touch.clientY || touch.pageY || 0;
        const offsetX = currentX - this.dragState.startX;
        const offsetY = currentY - this.dragState.startY;

        // 更新偏移（限制范围）
        this.dragState.offsetX = offsetX;
        this.dragState.offsetY = Math.max(-20, Math.min(200, offsetY));

        // 触发拖拽状态
        if (!this.dragState.isTouchDragging) {
          const moveDistance = Math.sqrt(Math.pow(offsetX, 2) + Math.pow(offsetY, 2));
          if (moveDistance > 5) {
            this.dragState.isTouchDragging = true;
          }
        }

        // 计算目标索引
        if (this.dragState.isTouchDragging) {
          const { startIndex, itemHeight } = this.dragState;
          const step = Math.round(offsetY / (itemHeight / 2));
          const targetIndex = Math.max(0, Math.min(this.localMySubjects.length - 1, startIndex + step));
          
          if (targetIndex !== this.dragState.dragOverIndex && targetIndex !== startIndex) {
            this.dragState.dragOverIndex = targetIndex;
          }
        }

      } catch (e) {
        console.error('触摸移动异常:', e);
      }
    },

    handleTouchEnd(event, index) {
      try {
        this.clearLongPressTimer();
        
        // 未触发拖拽，直接重置
        if (!this.dragState.isLongPressTriggered || !this.dragState.isTouchDragging) {
          this.resetDragState();
          return;
        }

        const { startIndex, dragOverIndex } = this.dragState;
        
        // 执行排序
        if (dragOverIndex !== -1 && dragOverIndex !== startIndex) {
          this.dragState.isSorting = true;
          
          // 复制数组并排序
          const newSubjects = [...this.localMySubjects];
          const [draggedItem] = newSubjects.splice(startIndex, 1);
          newSubjects.splice(dragOverIndex, 0, draggedItem);
          
          // 强制响应式更新（解决排序不生效问题）
          this.localMySubjects = [];
          this.$nextTick(() => {
            this.localMySubjects = newSubjects;
            this.$emit('sort', [...this.localMySubjects]);
            this.autoSave && this.storageKey && uni.setStorageSync(this.storageKey, this.localMySubjects);
            
            // 动画结束后重置
            setTimeout(() => {
              this.dragState.isSorting = false;
              this.resetDragState();
            }, 300);
          });
        } else {
          this.resetDragState();
        }

      } catch (e) {
        console.error('触摸结束异常:', e);
        this.resetDragState();
      }
    },

    resetDragState() {
      this.dragState = {
        isTouchDragging: false,
        isLongPressTriggered: false,
        isSorting: false,
        startIndex: -1,
        currentIndex: -1,
        dragOverIndex: -1,
        draggedItem: null,
        startX: 0,
        startY: 0,
        offsetX: 0,
        offsetY: 0,
        itemHeight: 80,
        longPressTimer: null
      };
    }
  }
}
</script>

<style lang="scss" scoped>
.libtablist {
  width: 100%;
  align-items: center;
  background-color: #ffffff;
  box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.07);
  border-radius: 20rpx;
  margin: 1rpx 0 10rpx 0;
  min-width: 100%;
  flex-basis: 100%;
  flex-shrink: 0;
}

.business-content {
  background-color: #f5f5f5;
}

.subject-scroll {
  height: calc(100% - 88rpx);
  -webkit-overflow-scrolling: touch;
}

.subject-section {
  margin: 20rpx;
  background-color: #ffffff;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.05);
}

.section-title-text {
  font-size: 32rpx;
  font-weight: bold;
  color: #333333;
  text-align: center;
  padding: 24rpx 0 8rpx;
}

.section-desc {
  font-size: 24rpx;
  color: #999999;
  text-align: center;
  padding: 0 0 16rpx;
}

.my-subjects-drag-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  padding: 0 20rpx 20rpx 20rpx;
  box-sizing: border-box;
  position: relative;
  min-height: 100rpx;
}

.subject-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20rpx;
  background-color: #f5f5f5;
  border-radius: 12rpx;
  margin: 10rpx;
  box-sizing: border-box;
  user-select: none;
  -webkit-user-select: none;
  transition: all 0.1s ease;
  cursor: grab;
  min-height: 70rpx;
  min-width: 120rpx;
}

.subject-item-text {
  font-size: 28rpx;
  color: #333333;
  flex: 1;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.subject-item-delete {
  margin-left: 16rpx;
  color: #ff4d4f;
}

.subject-item-add {
  background-color: #e8f4ff;
  color: #1890ff;
  cursor: pointer;
  transition: all 0.3s ease;
  width: auto;
  min-width: 0;
}

.subject-item-added {
  background-color: #f6ffed;
  color: #52c41a;
}

.subject-item-add-icon {
  margin-left: 16rpx;
}

.empty-subjects {
  width: 100%;
  text-align: center;
  color: #999999;
  font-size: 28rpx;
  padding: 40rpx 0;
}

.loading-question-lib,
.empty-question-lib {
  width: 100%;
  padding: 60rpx 0;
}

.loading-text {
  margin-top: 20rpx;
  font-size: 28rpx;
  color: #999999;
}

.tn-tabs__bar {
  margin-left: -20rpx !important;
}

.tn-tabs__slider {
  left: 20rpx !important;
}

.tn-tabs__item--active {
  margin-left: 0 !important;
}

/* 拖拽样式 */
.subject-item.dragging {
  transform: scale(1.03) !important;
  opacity: 0.8 !important;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
  pointer-events: none;
  z-index: 100 !important;
  transition: transform 0.1s ease, opacity 0.1s ease;
}

.subject-item.drag-over {
  border: 2rpx dashed #0E7DFF;
  background-color: rgba(14, 125, 255, 0.08);
  transition: all 0.2s ease;
}

.subject-item {
  transition: transform 0.3s ease, opacity 0.2s ease, background-color 0.2s ease;
}
</style>