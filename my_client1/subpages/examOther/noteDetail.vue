<template>
  <view class="note-detail-page">
    <!-- 顶部导航栏 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <view slot="back" class='tn-custom-nav-bar__back' @click="goBack">
          <text class='icon tn-icon-left'></text>
          <text class='icon tn-icon-home-capsule-fill'></text>
        </view>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
          <text class="tn-text-bold tn-text-xl tn-color-white">笔记详情</text>
        </view>
      </tn-nav-bar>
    </view>

    <!-- 页面内容 -->
    <view class="page-content" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 加载中 -->
      <view v-if="loading" class="loading-container">
        <tn-loading mode="flower" />
        <text class="tn-margin-top tn-color-gray">加载中...</text>
      </view>

      <!-- 笔记详情 -->
      <view v-else-if="noteDetail" class="note-detail-container">
        <!-- 笔记卡片 -->
        <view class="note-card">
          <!-- 用户信息 -->
          <view class="tn-flex tn-flex-row-between tn-margin-top">
            <view class="tn-flex items-center">
              <tn-avatar 
                :size="40" 
                :src="noteDetail.user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'" 
                class="tn-margin-right"
              />
              <view class="flex-1">
                <view class="tn-padding-right tn-text-df tn-text-bold tn-color-black" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
                  {{ noteDetail.user.nickname || '匿名用户' }}
                </view>
                <view class="tn-padding-right tn-text-xs tn-color-gray" style="padding-top: 5rpx;">
                  {{ formatTime(noteDetail.create_time) }}
                </view>
              </view>
            </view>
            
            <!-- 点赞和赞赏 -->
            <view class="tn-flex items-center" style="gap: 30rpx;">
              <!-- 点赞按钮 -->
              <view 
                class="tn-flex items-center justify-center comment-like-btn" 
                @click="handleLike"
              >
                <text :class="[noteDetail.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                <text class="tn-text-xs tn-margin-left-xs" :class="{ 'tn-color-red': noteDetail.user_like }">
                  {{ noteDetail.likes || 0 }}
                </text>
              </view>
              <!-- 赞赏按钮 -->
              <view 
                class="tn-flex items-center justify-center comment-reward-btn" 
                @click="openRewardPopup"
              >
                <text class="tn-icon-refund tn-color-orange" />
                <text class="tn-text-xs tn-margin-left-xs tn-color-orange">
                  {{ noteDetail.reward_integral || 0 }}
                </text>
              </view>
            </view>
          </view>

          <!-- 笔记内容 -->
          <view class="note-content">
            {{ noteDetail.content }}
          </view>
          <!-- 试题题目 -->
          <view v-if="noteDetail.type === 1 && listType === 1" class="question-title-container tn-margin-bottom-sm" @click="goToQuestionDetail(noteDetail.qid || noteDetail.question_id || (noteDetail.question && noteDetail.question.id) || noteDetail.id)">
            <text class="question-prefix">题目：</text>
            <mp-html class="question-text" :content="(noteDetail.question && noteDetail.question.title) || '无标题'"/>
            <text class="question-arrow tn-icon-right tn-text-lg tn-color-blue"></text>
          </view>
          <!-- 点赞用户头像组 -->
          <view v-if="likeUsers && likeUsers.length > 0" class="like-users-section">
            <view class="tn-flex tn-flex-row-between">
              <view class="tn-flex items-center">
                <view style="margin-right: 10rpx;">
                  <tn-avatar-group :lists="likeUsers" size="sm"></tn-avatar-group>
                </view>
                <text class="tn-color-grey tn-text-df">等{{ noteDetail.likes || 0 }}人点赞</text>
              </view>
            </view>
          </view>
        </view>

        <!-- 子评论列表 -->
        <view class="replies-container">
          <view class="replies-header">
            <text class="tn-text-lg tn-text-bold">全部回复 ({{ noteDetail.children_count || 0 }})</text>
          </view>

          <!-- 回复列表 -->
          <view v-if="noteDetail.children && noteDetail.children.length > 0" class="replies-list">
            <view 
              v-for="(child, index) in noteDetail.children" 
              :key="child.id" 
              class="reply-item"
            >
              <view class="tn-flex items-center">
                <tn-avatar 
                  :size="32" 
                  :src="child.user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'"
                  class="tn-margin-right"
                />
                <view class="flex-1">
                  <text class="tn-text-bold tn-padding-right-xs" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
                    {{ child.user.nickname || '匿名用户' }}:
                  </text>
                  <text class="reply-text">
                    {{ child.content }}
                  </text>
                </view>
              </view>
              
              <view class="tn-flex tn-flex-row-between tn-margin-top">
                <view class="tn-text-xs tn-color-gray">
                  {{ formatTime(child.create_time) }}
                </view>
                
                <!-- 子评论点赞、赞赏和回复按钮 -->
                <view class="tn-flex items-center" style="gap: 20rpx;">
                  <!-- 回复按钮 -->
                  <view
                    class="tn-flex items-center tn-text-xs reply-btn"
                    @click="openReplyPopup(child)"
                  >
                    <text class="tn-icon-comment tn-color-gray" />
                    <text class="tn-margin-left-xs tn-color-gray">回复</text>
                  </view>
                  <!-- 点赞按钮 -->
                  <view
                    class="tn-flex items-center tn-text-xs"
                    @click="handleChildLike(child, index)"
                  >
                    <text class="tn-margin-right-xs">
                      {{ child.likes || 0 }}
                    </text>
                    <text :class="[child.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                  </view>
                  <!-- 赞赏按钮 -->
                  <view
                    class="tn-flex items-center tn-text-xs"
                    @click="openChildRewardPopup(child, index)"
                  >
                    <text class="tn-margin-right-xs tn-color-orange">
                      {{ child.reward_integral || 0 }}
                    </text>
                    <text class="tn-icon-refund tn-color-orange" />
                  </view>
                </view>
              </view>
              
              <!-- 三级评论（子评论的回复）-->
              <view v-if="child.replies && child.replies.length > 0" class="nested-replies">
                <view 
                  v-for="(reply, rIndex) in child.replies" 
                  :key="reply.id" 
                  class="nested-reply-item"
                >
                  <view class="tn-flex items-start">
                    <tn-avatar 
                      :size="28" 
                      :src="reply.user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'"
                      class="tn-margin-right-sm"
                    />
                    <view class="flex-1">
                      <view>
                        <text class="tn-text-sm tn-text-bold tn-padding-right-xs" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
                          {{ reply.user.nickname || '匿名用户' }}:
                        </text>
                        <text class="tn-text-sm reply-text">
                          {{ reply.content }}
                        </text>
                      </view>
                      
                      <view class="tn-flex tn-flex-row-between tn-margin-top-xs">
                        <view class="tn-text-xs tn-color-gray">
                          {{ formatTime(reply.create_time) }}
                        </view>
                        
                        <!-- 三级评论操作按钮 -->
                        <view class="tn-flex items-center" style="gap: 20rpx;">
                          <!-- 点赞按钮 -->
                          <view
                            class="tn-flex items-center tn-text-xs"
                            @click="handleReplyLike(reply, index, rIndex)"
                          >
                            <text class="tn-margin-right-xs">
                              {{ reply.likes || 0 }}
                            </text>
                            <text :class="[reply.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                          </view>
                          <!-- 赞赏按钮 -->
                          <view
                            class="tn-flex items-center tn-text-xs"
                            @click="openReplyRewardPopup(reply, index, rIndex)"
                          >
                            <text class="tn-margin-right-xs tn-color-orange">
                              {{ reply.reward_integral || 0 }}
                            </text>
                            <text class="tn-icon-refund tn-color-orange" />
                          </view>
                        </view>
                      </view>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>

          <!-- 空状态 -->
          <view v-else class="empty-replies">
            <text class="tn-icon-comment tn-text-xl tn-color-gray" />
            <text class="tn-margin-top tn-text-sm tn-color-gray">暂无回复</text>
          </view>
        </view>

       
      </view>

      <!-- 错误状态 -->
      <view v-else class="error-container">
        <text class="tn-icon-error tn-text-xxl tn-color-gray" />
        <text class="tn-margin-top tn-text-df tn-color-gray">笔记不存在或已删除</text>
        <tn-button 
          class="tn-margin-top-lg"
          :backgroundColor="mainColor"
          fontColor="tn-color-white"
          @click="goBack"
        >
          返回
        </tn-button>
      </view>
    </view>

    <!-- 回复输入弹窗 -->
    <tn-popup 
      v-model="popupshow" 
      width="90%"
      mode="center" 
      height="650rpx"
      :border-radius="30"
      :safe-area-inset-bottom="true"
    >
      <view class="popup-container">
        <view class="popup-header">
          <view class="tn-flex items-center">
            <tn-avatar 
              :size="45" 
              :src="userAvatar" 
              class="tn-margin-right"
            />
            <view>
              <text class="tn-text-lg tn-text-bold">
                {{ replyTarget ? '回复评论' : '回复笔记' }}
              </text>
              <text v-if="replyTarget" class="tn-text-sm tn-color-gray tn-margin-left-xs">
                @{{ replyTarget.user.nickname }}
              </text>
            </view>
          </view>
          <view class="tn-text-df tn-color-gray">
            <text class="tn-margin-right-xs">{{ commentContent.length }}/500字</text>
            <text class="tn-icon-keyboard" />
          </view>
        </view>
        <view class="textarea-wrapper">
          <textarea
            v-model="commentContent"
            maxlength="500"
            :placeholder="replyTarget ? `回复 @${replyTarget.user.nickname}` : '说点什么，万一火了呢'"
            placeholder-style="color:#AAAAAA"
            class="comment-textarea"
            :focus="popupshow"
            :auto-height="true"
          />
        </view>
        <view class="popup-footer">
          <tn-button 
            shape="round" 
            type="default"
            :plain="true"
            @click="closeCommentPopup"
          >
            关 闭
          </tn-button>
          <tn-button 
            fontColor="tn-color-white"
            shape="round" 
            :backgroundColor="mainColor"
            :shadow="true"
            :disabled="!commentContent.trim() || submittingComment" 
            @click="submitComment"
          >
            {{ submittingComment ? '发送中...' : '发 送' }}
          </tn-button>
        </view>
      </view>
    </tn-popup>

    <!-- 赞赏弹窗 -->
    <tn-popup 
      v-model="showRewardPopup" 
      width="85%"
      mode="center" 
      :border-radius="30"
    >
      <view class="reward-popup-container">
        <view class="reward-header">
          <text class="tn-text-xl tn-text-bold">赞赏积分</text>
          <text class="tn-icon-close" @click.stop="closeRewardPopup" />
        </view>
        
        <view class="reward-content">
          <view class="reward-tips">
            <text class="tn-icon-info-circle tn-color-blue tn-margin-right-xs" />
            <text class="tn-text-sm tn-color-gray">赞赏将扣除你的积分，增加给作者</text>
          </view>
          
          <view class="reward-options">
            <view 
              v-for="(option, idx) in rewardOptions" 
              :key="idx"
              class="reward-option-item"
              :class="{'selected': selectedReward === option}"
              :style="{borderColor: mainColor}"
              @click="selectedReward = option"
            >
              <view class="tn-flex tn-flex-col-center">
                <text class="tn-text-xl tn-text-bold">{{ option }}</text>
                <text class="tn-text-xs tn-margin-top-xs">积分</text>
              </view>
              <view v-if="selectedReward === option" class="selected-icon" :style="{backgroundColor: mainColor}">
                <text class="tn-icon-check tn-color-white" />
              </view>
            </view>
          </view>
          
          <view class="reward-footer">
            <tn-button 
              shape="round" 
              type="default"
              :plain="true"
              @click.stop="closeRewardPopup"
            >
              取消
            </tn-button>
            <tn-button 
              fontColor="tn-color-white"
              shape="round" 
              :backgroundColor="mainColor" 
              :disabled="rewardingComment" 
              @click="submitReward"
            >
              {{ rewardingComment ? '赞赏中...' : '确认赞赏' }}
            </tn-button>
          </view>
        </view>
      </view>
    </tn-popup>

     <!-- 底部点赞和分享按钮 -->
        <view class="tn-flex tn-flex-row-between tn-footerfixed">
          <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
            <tn-button 
              backgroundColor="#00FFC6" 
              padding="40rpx 0" 
              width="90%" 
              shadow 
              fontBold
              @click="handleFooterLike"
            >
              <text :class="[noteDetail.user_like ? 'tn-icon-like-fill' : 'tn-icon-like-lack', 'tn-padding-right-xs tn-color-black']"></text>
              <text class="tn-color-black">{{ noteDetail.user_like ? '已点赞' : '点 赞' }}</text>
            </tn-button>
          </view>
          <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
            <tn-button 
              backgroundColor="#FFF00D" 
              padding="40rpx 0" 
              width="90%" 
              shadow 
              fontBold 
              open-type="share"
            >
              <text class="tn-icon-share-triangle tn-padding-right-xs tn-color-black"></text>
              <text class="tn-color-black">分 享</text>
            </tn-button>
          </view>
        </view>
        
        <view class='tn-tabbar-height'></view>
        
  </view>
</template>

<script>
export default {
  name: 'NoteDetail',
  data() {
    return {
      mainColor: getApp().globalData.mainColor,
      commentId: 0,
      noteDetail: null,
      loading: true,
      userAvatar: '',
      popupshow: false,
      commentContent: '',
      submittingComment: false,
      replyTarget: null, // 回复目标（子评论）
      likeUsers: [], // 点赞用户头像列表
      // 赞赏相关
      showRewardPopup: false,
      rewardCommentId: null,
      rewardCommentIndex: null,
      rewardReplyIndex: null, // 三级评论索引
      rewardCommentType: null, // 'child' 或 'reply'
      rewardOptions: [10, 20, 50, 100, 200],
      selectedReward: 10,
      rewardingComment: false,
      listType: 0, // 0: 我的笔记列表，1: 其他用户笔记列表
    }
  },
  onLoad(options) {
    if (options.comment_id) {
      this.commentId = parseInt(options.comment_id);
      this.listType = parseInt(options.listType || 0);
      this.initUserInfo();
      this.loadNoteDetail();
    } else {
      this.$func.showToast('参数错误');
      setTimeout(() => {
        uni.navigateBack();
      }, 1500);
    }
  },
  methods: {
    // 初始化用户信息
    initUserInfo() {
      try {
        const userInfo = getApp().globalData.userInfo;
        if (userInfo && userInfo.avatar) {
          this.userAvatar = userInfo.avatar;
        } else {
          this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
        }
      } catch (error) {
        console.error('[NoteDetail] 获取用户信息失败:', error);
        this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
      }
    },
    
    // 加载笔记详情
    async loadNoteDetail() {
      try {
        this.loading = true;
        const res = await this.$api.apiCommentDetail({
          comment_id: this.commentId
        });
        
        if (res && res.code === 1) {
          this.noteDetail = res.data;
          // 直接使用后端返回的点赞用户数据
          this.parseLikeUsers();
        } else {
          this.$func.showToast(res && res.msg || '获取笔记详情失败');
          this.noteDetail = null;
        }
      } catch (error) {
        console.error('[NoteDetail] 加载笔记详情失败:', error);
        this.$func.showToast('网络请求失败');
        this.noteDetail = null;
      } finally {
        this.loading = false;
      }
    },
    
    // 解析后端返回的点赞用户数据
    parseLikeUsers() {
      if (!this.noteDetail || !this.noteDetail.like_users) {
        this.likeUsers = [];
        return;
      }
      
      // 将后端返回的 like_users 转换为 tn-avatar-group 所需的格式
      this.likeUsers = this.noteDetail.like_users.map(user => ({
        src: user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'
      }));
    },
    
    // 加载点赞用户头像（废弃，使用后端直接返回的数据）
    async loadLikeUsers() {
      if (!this.noteDetail || !this.noteDetail.id) return;
      
      try {
        // 调用API获取点赞用户列表（最多5个）
        const res = await this.$api.apiCommentLikeUsers({
          comment_id: this.noteDetail.id,
          limit: 5
        });
        
        if (res && res.code === 1 && res.data && res.data.length > 0) {
          this.likeUsers = res.data.map(user => ({
            src: user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'
          }));
        } else {
          this.likeUsers = [];
        }
      } catch (error) {
        console.error('[NoteDetail] 加载点赞用户失败:', error);
        this.likeUsers = [];
      }
    },
    
    // 打开回复弹窗（回复子评论）
    openReplyPopup(child) {
      this.$api.apiUserInfo().then(res => {
        if (res.code === 1) {
          this.replyTarget = child;
          this.commentContent = '';
          this.popupshow = true;
        } else {
          this.$func.showToast('请先登录');
        }
      }).catch(() => {
        this.$func.showToast('网络异常，请稍后重试');
      });
    },
    
    // 打开回复弹窗（主笔记）
    openCommentPopup() {
      this.$api.apiUserInfo().then(res => {
        if (res.code === 1) {
          this.replyTarget = null;
          this.commentContent = '';
          this.popupshow = true;
        } else {
          this.$func.showToast('请先登录');
        }
      }).catch(() => {
        this.$func.showToast('网络异常，请稍后重试');
      });
    },
    
    // 关闭回复弹窗
    closeCommentPopup() {
      this.popupshow = false;
      this.commentContent = '';
      this.replyTarget = null;
    },
    
    // 提交回复
    async submitComment() {
      if (!this.commentContent.trim()) {
        this.$func.showToast('请输入回复内容');
        return;
      }
      
      try {
        this.submittingComment = true;
              
        // 如果是回复主评论，使用主评论ID作为pid
        // 如果是回复子评论，使用子评论ID作为pid，创建三级评论
        const pid = this.replyTarget ? this.replyTarget.id : this.commentId;
              
        const res = await this.$api.apiCommonAddComment({
          qid: this.noteDetail.qid,
          pid: pid,
          type: this.noteDetail.type,
          content: this.commentContent
        });
        
        if (res && res.code === 1) {
          this.$func.showToast('回复成功');
          this.closeCommentPopup();
          // 刷新笔记详情
          await this.loadNoteDetail();
        } else {
          this.$func.showToast(res && res.msg || '回复失败');
        }
      } catch (error) {
        console.error('[NoteDetail] 提交回复失败:', error);
        this.$func.showToast('网络请求失败');
      } finally {
        this.submittingComment = false;
      }
    },
    
    // 底部点赞按钮点击
    handleFooterLike() {
      this.handleLike();
    },
    
    // 主笔记点赞
    handleLike() {
      if (!this.noteDetail) return;
      
      const apiMethod = this.noteDetail.user_like 
        ? this.$api.apiCommonCancelLike 
        : this.$api.apiCommonAddLike;
      
      apiMethod({
        comment_id: this.noteDetail.id,
        type: this.noteDetail.type,
        qid: this.noteDetail.qid
      }).then(res => {
        if (res && res.code === 1) {
          this.noteDetail.user_like = !this.noteDetail.user_like;
          this.noteDetail.likes = this.noteDetail.user_like 
            ? (this.noteDetail.likes || 0) + 1 
            : Math.max(0, (this.noteDetail.likes || 0) - 1);
          this.$func.showToast(this.noteDetail.user_like ? '点赞成功' : '取消点赞');
          // 重新加载评论详情以更新点赞用户列表
          this.loadNoteDetail();
        } else {
          this.$func.showToast(res && res.msg || '操作失败');
        }
      }).catch(error => {
        console.error('[NoteDetail] 点赞失败:', error);
        this.$func.showToast('网络请求失败');
      });
    },
    
    // 子评论点赞
    handleChildLike(child, index) {
      if (!child) return;
      
      const apiMethod = child.user_like 
        ? this.$api.apiCommonCancelLike 
        : this.$api.apiCommonAddLike;
      
      apiMethod({
        comment_id: child.id,
        type: this.noteDetail.type,
        qid: this.noteDetail.qid
      }).then(res => {
        if (res && res.code === 1) {
          this.$set(this.noteDetail.children[index], 'user_like', !child.user_like);
          this.$set(
            this.noteDetail.children[index], 
            'likes', 
            !child.user_like 
              ? (child.likes || 0) + 1 
              : Math.max(0, (child.likes || 0) - 1)
          );
          this.$func.showToast(!child.user_like ? '点赞成功' : '取消点赞');
        } else {
          this.$func.showToast(res && res.msg || '操作失败');
        }
      }).catch(error => {
        console.error('[NoteDetail] 子评论点赞失败:', error);
        this.$func.showToast('网络请求失败');
      });
    },
    
    // 三级评论点赞
    handleReplyLike(reply, childIndex, replyIndex) {
      if (!reply) return;
      
      const apiMethod = reply.user_like 
        ? this.$api.apiCommonCancelLike 
        : this.$api.apiCommonAddLike;
      
      apiMethod({
        comment_id: reply.id,
        type: this.noteDetail.type,
        qid: this.noteDetail.qid
      }).then(res => {
        if (res && res.code === 1) {
          this.$set(this.noteDetail.children[childIndex].replies[replyIndex], 'user_like', !reply.user_like);
          this.$set(
            this.noteDetail.children[childIndex].replies[replyIndex], 
            'likes', 
            !reply.user_like 
              ? (reply.likes || 0) + 1 
              : Math.max(0, (reply.likes || 0) - 1)
          );
          this.$func.showToast(!reply.user_like ? '点赞成功' : '取消点赞');
        } else {
          this.$func.showToast(res && res.msg || '操作失败');
        }
      }).catch(error => {
        console.error('[NoteDetail] 三级评论点赞失败:', error);
        this.$func.showToast('网络请求失败');
      });
    },
    
    // 打开赞赏弹窗（主笔记）
    openRewardPopup() {
      this.$api.apiUserInfo().then(res => {
        if (res.code === 1) {
          this.rewardCommentId = this.noteDetail.id;
          this.rewardCommentIndex = null;
          this.selectedReward = this.rewardOptions[0];
          this.showRewardPopup = true;
        } else {
          this.$func.showToast('请先登录');
        }
      }).catch(() => {
        this.$func.showToast('网络异常，请稍后重试');
      });
    },
    
    // 打开赞赏弹窗（子评论）
    openChildRewardPopup(child, index) {
      this.$api.apiUserInfo().then(res => {
        if (res.code === 1) {
          this.rewardCommentId = child.id;
          this.rewardCommentIndex = index;
          this.rewardCommentType = 'child'; // 标记为子评论
          this.selectedReward = this.rewardOptions[0];
          this.showRewardPopup = true;
        } else {
          this.$func.showToast('请先登录');
        }
      }).catch(() => {
        this.$func.showToast('网络异常，请稍后重试');
      });
    },
    
    // 打开赞赏弹窗（三级评论）
    openReplyRewardPopup(reply, childIndex, replyIndex) {
      this.$api.apiUserInfo().then(res => {
        if (res.code === 1) {
          this.rewardCommentId = reply.id;
          this.rewardCommentIndex = childIndex;
          this.rewardReplyIndex = replyIndex; // 保存三级评论索引
          this.rewardCommentType = 'reply'; // 标记为三级评论
          this.selectedReward = this.rewardOptions[0];
          this.showRewardPopup = true;
        } else {
          this.$func.showToast('请先登录');
        }
      }).catch(() => {
        this.$func.showToast('网络异常，请稍后重试');
      });
    },
    
    // 关闭赞赏弹窗
    closeRewardPopup() {
      this.showRewardPopup = false;
      this.rewardCommentId = null;
      this.rewardCommentIndex = null;
      this.rewardReplyIndex = null;
      this.rewardCommentType = null;
      this.selectedReward = this.rewardOptions[0];
    },
    
    // 提交赞赏
    async submitReward() {
      if (this.rewardingComment) return;
      
      try {
        this.rewardingComment = true;
        const res = await this.$api.apiCommentReward({
          comment_id: this.rewardCommentId,
          integral: this.selectedReward
        });
        
        if (res && res.code === 1) {
          this.$func.showToast('赞赏成功');
          
          // 更新赞赏积分
          if (this.rewardCommentIndex === null) {
            // 主笔记
            this.noteDetail.reward_integral = 
              (this.noteDetail.reward_integral || 0) + this.selectedReward;
          } else if (this.rewardCommentType === 'reply' && this.rewardReplyIndex !== null) {
            // 三级评论
            this.$set(
              this.noteDetail.children[this.rewardCommentIndex].replies[this.rewardReplyIndex],
              'reward_integral',
              (this.noteDetail.children[this.rewardCommentIndex].replies[this.rewardReplyIndex].reward_integral || 0) + this.selectedReward
            );
          } else {
            // 子评论
            this.$set(
              this.noteDetail.children[this.rewardCommentIndex],
              'reward_integral',
              (this.noteDetail.children[this.rewardCommentIndex].reward_integral || 0) + this.selectedReward
            );
          }
          
          // 关闭弹窗
          this.closeRewardPopup();
        } else {
          this.$func.showToast(res && res.msg || '赞赏失败');
        }
      } catch (error) {
        console.error('[NoteDetail] 赞赏失败:', error);
        this.$func.showToast('网络请求失败');
      } finally {
        this.rewardingComment = false;
      }
    },
    
    // 时间格式化
    formatTime(timeStr) {
      if (!timeStr) return '';
      
      const formattedTimeStr = timeStr.replace(/-/g, '/');
      const now = new Date();
      const time = new Date(formattedTimeStr);
      
      if (isNaN(time.getTime())) {
        return timeStr;
      }
      
      const diffInSeconds = Math.floor((now - time) / 1000);
      
      if (diffInSeconds < 60) return '刚刚';
      
      const diffInMinutes = Math.floor(diffInSeconds / 60);
      if (diffInMinutes < 60) return `${diffInMinutes}分钟前`;
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) return `${diffInHours}小时前`;
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 7) return `${diffInDays}天前`;
      
      return `${time.getFullYear()}-${(time.getMonth() + 1).toString().padStart(2, '0')}-${time.getDate().toString().padStart(2, '0')}`;
    },
    
    // 返回上一页
    goBack() {
      uni.navigateBack();
    },
    
    // 前往笔记详情页
    goToNoteDetail(commentId) {
      uni.navigateTo({
        url: `/subpages/examOther/noteDetail?comment_id=${commentId}`
      });
    },
    
    // 跳转到试题详细信息页面
    goToQuestionDetail(questionUid) {
      // 准备传递给commonQuestion.vue的参数
      const examSettings = {
        uid: (this.noteDetail && this.noteDetail.question && this.noteDetail.question.uid) || (this.noteDetail && this.noteDetail.question_uid) || '',
        analysis_quid: questionUid,
        question_count: 1,
        questions_note: 1,
        questions_type: 0, // 0表示笔记练习
        mode: 'reviewOnly',
        practice_mode: 3
      };
      
      console.log('[noteDetail] examSettings:', examSettings);
      
      // 直接跳转到答题页面，不需要经过设置步骤
      uni.navigateTo({
        url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
      });
    }
  },
  // 分享功能
  onShareAppMessage() {
    return {
      title: this.noteDetail ? `${this.noteDetail.user.nickname}的笔记` : '精解析题库',
      path: `/subpages/examOther/noteDetail?comment_id=${this.commentId}`,
      imageUrl: this.noteDetail && this.noteDetail.user ? this.noteDetail.user.avatar : ''
    };
  },
  
  onShareTimeline() {
    return {
      title: this.noteDetail ? `${this.noteDetail.user.nickname}的笔记` : '精解析题库',
      query: `comment_id=${this.commentId}`,
      imageUrl: this.noteDetail && this.noteDetail.user ? this.noteDetail.user.avatar : ''
    };
  }
}
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";

.note-detail-page {
  min-height: 100vh;
  background-color: #F8F9FA;
}

.page-content {
  padding-bottom: 120rpx;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 200rpx 40rpx;
  text-align: center;
}

.note-detail-container {
  padding: 20rpx;
}

.note-card {
  background-color: #FFFFFF;
  border-radius: 16rpx;
  padding: 20rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
}

.note-content {
  margin: 20rpx 0;
  padding-left: 50rpx;
  line-height: 1.8;
  font-size: 30rpx;
  color: #333333;
  word-break: break-word;
}

.like-users-section {
  padding-top: 20rpx;
  margin-top: 20rpx;
  border-top: 1rpx solid #F0F0F0;
}

.replies-container {
  background-color: #FFFFFF;
  border-radius: 16rpx;
  padding: 30rpx;
  min-height: 300rpx;
}

.replies-header {
  padding-bottom: 20rpx;
  border-bottom: 2rpx solid #F0F0F0;
  margin-bottom: 20rpx;
}

.replies-list {
  .reply-item {
    padding: 20rpx 0;
    border-bottom: 1rpx solid #F5F5F5;
    
    &:last-child {
      border-bottom: none;
    }
    
    /* 确保昵称和内容水平对齐 */
    > .tn-flex {
      width: 100%;
    }
    
    /* 昵称和内容容器样式 */
    .flex-1 {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      
      /* 昵称样式 */
      > text:first-child {
        margin-right: 8rpx;
        white-space: nowrap;
        line-height: 1.6;
      }
      
      /* 内容样式 */
      > .reply-text {
        flex: 1;
        line-height: 1.6;
        min-width: 0;
      }
    }
  }
}

.reply-text {
  line-height: 1.6;
  word-break: break-word;
  color: #333333;
}

.empty-replies {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 100rpx 0;
  color: #999999;
}

.reply-btn {
  padding: 4rpx 12rpx;
  border-radius: 12rpx;
  background-color: #F8F9FA;
  transition: all 0.3s ease;
  
  &.hover-class {
    transform: scale(0.95);
    background-color: #E9ECEF;
  }
}

    /* 底部悬浮按钮 start*/
    .tn-tabbar-height {
    	min-height: 100rpx;
    	height: calc(120rpx + env(safe-area-inset-bottom) / 2);
    }
    .tn-footerfixed {
      position: fixed;
      width: 100%;
      bottom: calc(30rpx + env(safe-area-inset-bottom));
      z-index: 1024;
      box-shadow: 0 1rpx 6rpx rgba(0, 0, 0, 0);
      
    }
    /* 底部悬浮按钮 end*/


.reply-input-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #FFFFFF;
  padding: 20rpx;
  display: flex;
  align-items: center;
  gap: 20rpx;
  box-shadow: 0 -4rpx 20rpx rgba(0, 0, 0, 0.05);
  padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
  
  .input-wrapper {
    flex: 1;
    background-color: #F8F9FA;
    border-radius: 40rpx;
    padding: 20rpx 30rpx;
    
    .placeholder-text {
      color: #AAAAAA;
      font-size: 28rpx;
    }
  }
}

.comment-like-btn,
.comment-reward-btn {
  padding: 8rpx 16rpx;
  border-radius: 20rpx;
  transition: all 0.3s ease;
  
  &.hover-class {
    transform: scale(0.95);
    opacity: 0.8;
  }
}

.items-center {
  align-items: center;
}

.flex-1 {
  flex: 1;
}

/* 三级评论样式 */
.nested-replies {
  margin-top: 20rpx;
  padding-left: 60rpx;
  border-left: 2rpx solid #F5F5F5;
}

.nested-reply-item {
  padding: 20rpx 20rpx;
  transition: all 0.3s ease;
  background-color: #F8F9FA;
  border-radius: 12rpx;
  margin-bottom: 5rpx;
  
  &:last-child {
    border-bottom: none;
  }
  
  /* 确保昵称和内容水平对齐 */
  > .tn-flex {
    width: 100%;
  }
  
  /* 昵称和内容容器样式 */
  .flex-1 {
    > view:first-child {
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      
      /* 昵称样式 */
      > text:first-child {
        margin-right: 8rpx;
        white-space: nowrap;
        line-height: 1.6;
      }
      
      /* 内容样式 */
      > .reply-text {
        flex: 1;
        line-height: 1.6;
        min-width: 0;
      }
    }
  }
}

/* 弹窗样式 */
.popup-container {
  display: flex;
  flex-direction: column;
  height: 100%;
  padding: 30rpx;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 30rpx;
  border-bottom: 2rpx solid #F0F0F0;
  margin-bottom: 30rpx;
}

.textarea-wrapper {
  flex: 1;
  background-color: #F8F9FA;
  border-radius: 12rpx;
  padding: 20rpx;
  margin-bottom: 30rpx;
  
  .comment-textarea {
    width: 100%;
    font-size: 28rpx;
    line-height: 1.6;
  }
}

.popup-footer {
  display: flex;
  justify-content: space-between;
  gap: 20rpx;
}

/* 赞赏弹窗样式 */
.reward-popup-container {
  padding: 40rpx;
}

.reward-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40rpx;
  
  .tn-icon-close {
    font-size: 40rpx;
    color: #909399;
    
    &.hover-class {
      opacity: 0.6;
    }
  }
}

.reward-content {
  .reward-tips {
    display: flex;
    align-items: center;
    padding: 20rpx;
    background-color: #F0F9FF;
    border-radius: 12rpx;
    margin-bottom: 40rpx;
  }
  
  .reward-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20rpx;
    margin-bottom: 40rpx;
  }
  
  .reward-option-item {
    position: relative;
    padding: 30rpx 20rpx;
    border: 2rpx solid #E9ECEF;
    border-radius: 16rpx;
    text-align: center;
    transition: all 0.3s ease;
    
    &.selected {
      background-color: #FFF5E6;
    }
    
    &.hover-class {
      transform: scale(0.95);
    }
    
    .selected-icon {
      position: absolute;
      top: -8rpx;
      right: -8rpx;
      width: 40rpx;
      height: 40rpx;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24rpx;
    }
  }
  
  .reward-footer {
    display: flex;
    justify-content: space-between;
    gap: 20rpx;
  }
}
/* 试题题目容器样式 */
.question-title-container {
  display: flex;
  align-items: center;
  background-color: #E3F2FD;
  border-radius: 15rpx;
  padding: 10rpx 25rpx;
  cursor: pointer;
  transition: all 0.3s ease;
}

/* 题目前缀样式 */
.question-prefix {
  font-size: 28rpx;
  color: #1976D2;
  font-weight: bold;
}

/* 题目文本样式 */
.question-text {
  flex: 1;
  font-size: 26rpx;
  color: #1976D2;
  line-height: 1.6;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* 题目箭头样式 */
.question-arrow {
  margin-left: 10rpx;
  color: #1976D2;
  font-size: 32rpx;
}
</style>
