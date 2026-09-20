<template>
  <!-- 做题笔记组件 -->
  <view class="comment-container"> 
    <view class="comment-header">
      <view class="tn-flex tn-flex-row-between tn-flex-col-center">
        <view class="header-title">
          <text class="tn-icon-notebook tn-color-blue" />
          <text class="tn-text-bold tn-text-lg">
            做题笔记
          </text>
        </view>
        <!-- 发布入口：右上角“发布笔记”按钮 -->
        <tn-button 
		  fontColor="tn-color-white"
		  size="sm"
          fontSize="32" 
		  padding="30rpx"
          :backgroundColor="mainColor"
          :shadow="true"
          @click="openCommentPopup(0)"
        >
          <text class="tn-icon-edit-write tn-margin-right-xs" />
          发布
        </tn-button>
      </view>
    </view>
 
    <!-- 笔记列表区域：使用tu-list组件支持下拉刷新 -->
    <tn-list-view 
      v-model="refreshingComments" 
      :loading="loadingComments && !refreshingComments" 
      :finished="!hasMoreComments"
      finished-text="没有更多笔记了"
      class="comment-list"
      @refresh="onRefresh"
      @load="onCommentLoadMore"
    >
      <!-- 动态笔记列表 -->
      <view
        v-for="(comment, index) in commentList"
        :key="comment.id"
        class="comment-item"
      >
        <view
          v-if="comment.pid == 0"
          class="comment-card"
        >
          <!-- 图标logo/头像：使用tu-avatar组件 -->
          <view class="tn-flex tn-flex-row-between tn-margin-top">
            <view class="tn-flex items-center">
              <!-- 用户头像：固定大小40rpx -->
              <tn-avatar 
                :size="40" 
                :src="comment.user.avatar || userAvatar" 
                class="tn-margin-right"
                @click="tl('/subpages/user/userdata')"
              />
              <view class="flex-1">
                <view
                  class="tn-padding-right"
                  @click="openCommentPopup(comment.id)"
                >
                  <view class="tn-padding-right tn-text-df tn-text-bold tn-color-black" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block;">
                    {{ comment.user.nickname || '匿名用户' }}
                  </view>
                    <view
                      class="tn-padding-right tn-text-ellipsis tn-text-xs tn-color-gray"
                      style="padding-top: 5rpx;"
                    >
                      {{ formatTime(comment.create_time) }}
                    </view>
                </view>
              </view>
            </view>
            <!-- 主笔记点赞和赞赏 -->
            <view class="tn-flex items-center">
              <!-- 点赞按钮 -->
              <view 
                class="tn-flex items-center justify-center comment-like-btn" 
                @click="comment && comment.id && comment.qid && handleLike(comment.id, comment.qid, index)"
              >
                <text :class="[ comment.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                <text
                  class="tn-text-xs tn-margin-left-xs"
                  :class="{ 'tn-color-red': comment.user_like }"
                >
                  {{ comment.likes || 0 }}
                </text>
              </view>
              <!-- 赞赏按钮 -->
              <view 
                class="tn-flex items-center justify-center comment-reward-btn" 
                @click="comment && comment.id && openRewardPopup(comment.id, index)"
              >
                <text class="tn-icon-refund tn-color-orange" />
                <text class="tn-text-xs tn-margin-left-xs tn-color-orange">
                  {{ comment.reward_integral || 0 }}
                </text>
              </view>
            </view>
          </view>
          <!-- 主笔记内容 -->
          <view
            class="comment-content"
            @click="openCommentPopup(comment.id)"
          >
            {{ comment.content }}
          </view>
                  
          <!-- 点赞用户列表和评论按钮容器 -->
          <view class="tn-flex tn-flex-row-between tn-flex-col-center action-row">
            <!-- 点赞用户列表（显示头像组） -->
            <view  class="like-users-container flex-1">
              <view  v-if="comment.like_users && comment.like_users.length > 0"  class="tn-flex tn-flex-row-left tn-flex-col-center">
                <text class="tn-icon-like-fill tn-color-red tn-text-xs" style="margin-right: 10rpx;" />
                <view class="tn-flex tn-flex-col-center" style="flex: 1; white-space: nowrap; overflow: hidden;">
                  <tn-avatar-group 
                    :lists="comment.like_users.map(u => ({ src: u.avatar }))" 
                    size="sm"
                    :max-count="8"
                  />
				  <text class="tn-color-gray tn-text-xs" style="margin-left: 10rpx; white-space: nowrap;">
                  {{ comment.likes }}人点赞
                </text>
                </view>
                
              </view>
            </view>
                     
            <!-- 评论按钮 -->
            <view class="comment-actions">
              <text
                class="comment-action-btn"
                @click="openCommentPopup(comment.id)"
              >
                <text class="tn-icon-message" style="margin-right: 5rpx;" />
                评论
              </text>
            </view>
          </view>
        </view>
        <!-- 若存在父笔记，展示回复内容 -->
        <view
          v-if="comment.children && comment.children.length > 0"
          class="child-comments"
        >
          <!-- 显示前3条子评论 -->
          <view 
            v-for="(child, childIndex) in comment.children" 
            :key="child.id" 
            class="child-comment-item"
          >
            <view class="tn-flex items-center">
              <view class="flex-1" @click="goToNoteDetail(comment.id)">
                <text class="tn-text-bold tn-padding-right-xs nickname-text">
                  {{ child.user.nickname || '匿名用户' }}:
                </text>
                <text class="comment-text">
                  {{ child.content }}
                </text>
              </view>
            </view>
            <view class="tn-flex tn-flex-row-between">
              <view class="tn-text-xs tn-color-gray">
                {{ formatTime(child.create_time) }}
              </view>
              <!-- 子笔记点赞和赞赏 -->
              <view class="tn-flex items-center">
                <!-- 点赞按钮 -->
                <view
                  class="tn-flex items-center tn-text-xs"
                  @click.stop="child && child.id && child.qid && handleLike(child.id, child.qid, {parentIndex: index, childIndex: childIndex})"
                >
                  <text class="tn-margin-right-xs">
                    {{ child.likes || 0 }}
                  </text>
                  <text :class="[child.user_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like-lack tn-color-gray']" />
                </view>
                <!-- 赞赏按钮 -->
                <view
                  class="tn-flex items-center tn-text-xs"
                  @click.stop="child && child.id && openRewardPopup(child.id, {parentIndex: index, childIndex: childIndex})"
                >
                  <text class="tn-margin-right-xs tn-color-orange">
                    {{ child.reward_integral || 0 }}
                  </text>
                  <text class="tn-icon-refund tn-color-orange" />
                </view>
              </view>
            </view>
          </view>
          
          <!-- 查看更多回复按钮 -->
          <view 
            v-if="comment.children_count > 3"
            class="view-more-btn"
            @click="goToNoteDetail(comment.id)"
          >
            <text class="tn-text-sm tn-color-blue">
              查看全部{{ comment.children_count }}条回复 >
            </text>
          </view>
        </view>
      </view>
    
      <!-- 空状态 -->
      <view
        v-if="!loadingComments && !refreshingComments && commentList.length === 0"
        class="empty-state"
      >
        <text class="tn-icon-comment tn-text-xxl tn-color-gray" />
        <text class="tn-margin-top tn-text-df tn-color-gray">
          暂无笔记，快来抢沙发吧~
        </text>
      </view>
    </tn-list-view>

    <!-- 笔记输入框弹窗 -->
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
            <text class="tn-text-lg tn-text-bold">
              想说点什么
            </text>
          </view>
          <view class="tn-text-df tn-color-gray">
            <text class="tn-margin-right-xs">
              {{ commentContent.length }}/500字
            </text>
            <text class="tn-icon-keyboard" />
          </view>
        </view>
        <view class="textarea-wrapper">
          <textarea
            v-model="commentContent"
            maxlength="500"
            placeholder="说点什么 , 万一火了呢"
            placeholder-style="color:#AAAAAA"
            class="comment-textarea"
          />
        </view>
        <!-- 提交按钮 -->
        <view class="popup-footer">
          <tn-button 
            shape="round" 
            type="default"
            :plain="true"
            :shadow="true"
            @click="closeCommentPopup"
          >
            关 闭
          </tn-button>
          <tn-button 
			fontColor="tn-color-white"
            shape="round" 
            background-color="tn-bg-blue"
            :shadow="true"
            :disabled="!commentContent.trim() || submittingComment" 
            @click="submitComment"
          >
            {{ submittingComment ? '发送中...' : '发 送' }}
          </tn-button>
        </view>
      </view>
    </tn-popup>
    
    <!-- 赞赏积分弹窗 -->
    <tn-popup 
      v-model="showRewardPopup" 
      width="85%"
      mode="center" 
      :border-radius="30"
      :safe-area-inset-bottom="true"
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
              :shadow="true"
              :disabled="rewardingComment" 
              @click="submitReward"
            >
              {{ rewardingComment ? '赞赏中...' : '确认赞赏' }}
            </tn-button>
          </view>
        </view>
      </view>
    </tn-popup>
  </view>
</template>
<script>
	export default {
	name: 'CommentSection',
    components: {
    },
    props: {
			questionUid: {
				type: String,
				default: ''
			},
			libraryUid: {
				type: String,
				default: ''
			},
			categoryUid: {
				type: String,
				default: ''
			},
			currentMode: {
				type: String,
				default: 'learnPractice' // normal, learnPractice, reviewOnly
			}
		},
		data() {
			return {
        		mainColor: getApp().globalData.mainColor,
				popupshow: false,
				commentId: 0, // 当前回复的评论id
				groupAvatarList: [],
				groupList: [],
				searchWhere: { // 文章查询条件
					id: '',
					type: 1,
				},
				relArticleContent: [],
				articleContent: {},
				commentContent: '',
				commentList: [],
				loadingComments: false,
				submittingComment: false,
				userInfo: null,
				userAvatar: '', // 用户头像，从globalData获取
				showBackToTop: false,
				hasMoreComments: true,
				// 性能优化：懒加载相关
				commentsLoaded: false, // 标记评论是否已加载
				refreshingComments: false,
				page_no: 1,
				page_size: 5, // 优化：首次只加载5条笔记，减少网络请求大小
				initialPageSize: 5, // 首次加载数量
				normalPageSize: 10, // 后续加载数量
				// 赞赏相关
				showRewardPopup: false, // 赞赏弹窗显示状态
				rewardCommentId: null, // 当前要赞赏的评论id
				rewardCommentIndex: null, // 当前要赞赏的评论索引
				rewardOptions: [10, 20, 50, 100, 200], // 赞赏积分选项
				selectedReward: 10, // 当前选中的赞赏积分
				rewardingComment: false, // 赞赏提交中
			}
		},
		mounted() {
			// 页面加载时初始化数据
			this.initUserInfo();
			
			// 🔑 修复：简化逻辑，直接加载笔记
			// 原因：组件能显示就说明 shouldShowComment 已经返回 true
			// IntersectionObserver 在 scroll-view 中可能无法正确触发
			if (!this.commentsLoaded) {
				this.commentsLoaded = true;
				this.initComments();
			}
		},
		beforeDestroy() {
			// 组件销毁时清理资源
		},
		watch: {
			// 监听questionUid变化，切换题目时重新加载笔记
			questionUid: {
				handler(newUid, oldUid) {
					if (newUid && newUid !== oldUid) {
						// 重置笔记列表状态
						this.commentList = [];
						this.loadingComments = false;
						this.hasMoreComments = true;
						this.refreshingComments = false;
						this.page_no = 1;
						this.page_size = this.initialPageSize;
						
						// 🔑 修复：切换题目时直接加载，不依赖 IntersectionObserver
						// 原因：组件已经显示（通过 v-if="shouldShowComment(item)"）
						if (!this.commentsLoaded) {
							this.commentsLoaded = true;
						}
						this.initComments();
					}
				},
				immediate: false
			}
		},
		onPageScroll(e) {
			// 监听页面滚动，控制返回顶部按钮显示
			this.showBackToTop = e.scrollTop > 300;
		},
			methods: {
			// 初始化用户信息
			initUserInfo() {
				try {
					// 从globalData获取用户信息
					const userInfo = getApp().globalData.userInfo;
					if (userInfo && userInfo.avatar) {
						this.userAvatar = userInfo.avatar;
					} else {
						// 使用默认头像
						this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
					}
				} catch (error) {
					console.error('[CommentSection] 获取用户信息失败:', error);
					this.userAvatar = 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png';
				}
			},
			
			// 返回顶部
			backToTop() {
				uni.pageScrollTo({
					scrollTop: 0,
					duration: 300
				});
			},
			
			// 计算子评论高度（用于 ReadMore 组件）
			calculateChildrenHeight(children) {
				// 每个子评论项的预估高度：大约 150rpx（根据实际样式调整）
				// 包括：头像(32rpx) + 内容(padding 20rpx * 2) + 点赞区(margin-top) + 间距
				const itemHeight = 150; // rpx
				return children.length * itemHeight;
			},
			
			// 笔记加载更多
			onCommentLoadMore() {
			  if (!this.loadingComments && this.hasMoreComments) {
			    // 立即设置加载状态，防止快速连续触发
			    this.loadingComments = true;
			    this.page_no++;
			    // 后续加载使用较大的page_size
			    this.page_size = this.normalPageSize;
			    this.getCommentList();
			  }
			},
			
			// 获取笔记列表
			getCommentList(refresh = false) {
			  
			  // 参数检查：确保questionUid不为空
			  if (!this.questionUid) {
			    console.error('获取笔记失败：questionUid为空');
			    this.$func.showToast('获取笔记失败：参数错误');
			    return;
			  }
			  
			  // 双重检查加载状态 已有笔记加载请求进行中，取消重复请求
			  if (!refresh && this.loadingComments) {
			    return;
			  }
			  
			  // 如果没有更多数据且不是刷新，则不再请求
			  if (!this.hasMoreComments && !refresh) {
			    return;
			  }
			  
			  // 刷新时重置分页
			  if (refresh) {
			    this.hasMoreComments = true;
			    this.refreshingComments = true;
			  } else if (!this.loadingComments) {
			    // 非刷新模式且未设置加载状态时才设置
			    this.loadingComments = true;
			  }
			  
			  try {
				  this.$api.apiCommonCommentList({
				    qid: this.questionUid,
				    type: this.searchWhere.type,
				    page_no: this.page_no,
				    page_size: this.page_size,
				  }).then(res => {
				    if (res && res.code === 1) {
				      const newComments = res.data.lists || [];
			      // 刷新时替换数据，加载更多时追加数据
			      if (refresh) {
			        this.commentList = newComments;
			      } else {
			        this.commentList = [...this.commentList, ...newComments];
			      }
				      
				      // 判断是否还有更多数据
				      this.hasMoreComments = newComments.length === this.page_size;
				    } else {
				      if (refresh) {
				        this.commentList = [];
				      }
				      this.$func.showToast(res && res.msg || '获取笔记列表失败');
				    }
				  }).catch(error => {
				    console.error('获取笔记列表失败:', error);
				    if (refresh) {
				      this.commentList = [];
				    }
				    this.$func.showToast('网络请求失败，请稍后重试');
				  }).finally(() => {
				    this.loadingComments = false;
				    this.refreshingComments = false;
				  })
			  } catch (error) {
			    console.error('获取笔记列表异常:', error);
			    this.loadingComments = false;
			    this.refreshingComments = false;
			    this.$func.showToast('获取笔记列表失败，请稍后重试');
			  }
			},
			
			// 下拉刷新
			onRefresh() {
				// 重置分页并刷新
				this.page_no = 1;
				this.getCommentList(true)
			},
			
			// 初始化笔记数据
			initComments() {
				this.page_no = 1;
				this.page_size = this.initialPageSize; // 首次加载使用较小的数量
				this.getCommentList();
			},
					
						
			// 收藏/取消收藏
			handleCollection() {
				if (this.articleContent.collect === false) {
					this.$api.apiArticleAddCollect({
						id: this.questionUid,
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							this.articleContent.collect = true
							this.$func.showToast('收藏成功')
						}
					})
				} else if (this.articleContent.collect === true) {
					this.$api.apiArticleCancelCollect({
						id: this.questionUid,
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							this.articleContent.collect = false
							this.$func.showToast('取消收藏成功')
						}
					})
				}
			},
			
			// 点赞/取消点赞
			handleLike(commentId, qid, index) {
			  
			  try {
			    // 首先检查参数是否有效
			    if (!qid) {
			      console.error('点赞参数无效:', {commentId, qid});
			      this.$func.showToast('参数错误');
			      return;
			    }
			    
			    // 判断是文章点赞还是笔记点赞
			    if (!commentId) {
			      // 文章点赞逻辑
			      if (this.articleContent && this.articleContent.like === false) {
			        this.$api.apiCommonAddLike({
			          type: this.searchWhere.type,
			          qid: qid,
			        }).then(res => {
			          if (res.code === 1) {
			            this.articleContent.like = true
			            this.$func.showToast('点赞成功')
			          } else {
			            this.$func.showToast(res.msg)
			          }
			        })
			      } else if (this.articleContent && this.articleContent.like === true) {
			        this.$api.apiCommonCancelLike({
			          type: this.searchWhere.type,
			          qid: qid,
			        }).then(res => {
			          if (res.code === 1) {
			            this.articleContent.like = false
			            this.$func.showToast('取消点赞成功')
			          } else {
			            this.$func.showToast(res.msg)
			          }
			        })
			      }
			    } else {
			      // 笔记点赞逻辑
			      // 先检查commentList是否存在且有效
			      if (!this.commentList || !Array.isArray(this.commentList)) {
			        console.error('笔记列表数据无效');
			        this.$func.showToast('数据加载中，请稍候');
			        return;
			      }
			      
			      // 判断是否是子笔记
			      const isChildComment = typeof index === 'object' && index.parentIndex !== undefined;
			      
			      // 找到要更新的笔记
			      let targetComment = null;
			      let parentComment = null;
			      
			      // 检查index是否为undefined
			      if (index === undefined) {
			        console.error('笔记索引未提供:', {commentId, qid});
			        this.$func.showToast('操作失败，请刷新页面重试');
			        return;
			      }
			      
			      // 尝试获取目标笔记
			      if (isChildComment) {
			        // 检查父笔记和子笔记索引是否有效
			        if (typeof index.parentIndex === 'number' && 
			            index.parentIndex >= 0 && 
			            index.parentIndex < this.commentList.length) {
			          parentComment = this.commentList[index.parentIndex];
			          // 确保父笔记存在children数组
			          if (parentComment && 
			              Array.isArray(parentComment.children) && 
			              typeof index.childIndex === 'number' && 
			              index.childIndex >= 0 && 
			              index.childIndex < parentComment.children.length) {
			            targetComment = parentComment.children[index.childIndex];
			          }
			        }
			      } else {
			        // 为主笔记添加索引有效性检查
			        if (typeof index === 'number' && index >= 0 && index < this.commentList.length) {
			          targetComment = this.commentList[index];
			        }
			      }
			      
			      // 在访问user_like前检查targetComment是否存在
			      if (!targetComment) {
			        console.error('未找到目标笔记:', {isChildComment, index, commentId});
			        this.$func.showToast('笔记数据不存在或已更新');
			        return;
			      }			      
			      // 执行点赞或取消点赞操作
			      if (targetComment.user_like === false) {
			        this.$api.apiCommonAddLike({
			          comment_id: commentId,
			          type: this.searchWhere.type,
			          qid: qid,
			        }).then(res => {
			          try {
			            if (res.code === 1) {
			              // 更新笔记的点赞状态，增加安全检查
			              if (isChildComment && parentComment) {
			                if (parentComment.children && parentComment.children[index.childIndex]) {
			                  this.$set(parentComment.children[index.childIndex], 'user_like', true);
			                  this.$set(parentComment.children[index.childIndex], 'likes', (parentComment.children[index.childIndex].likes || 0) + 1);
			                }
			              } else if (typeof index === 'number' && index < this.commentList.length) {
			                this.$set(this.commentList[index], 'user_like', true);
			                this.$set(this.commentList[index], 'likes', (this.commentList[index].likes || 0) + 1);
			              }
			              this.$func.showToast('点赞成功')
			              
			              // 触发点赞事件
			              this.$emit('note-liked', {
			                commentId,
			                qid,
			                index,
			                liked: true
			              })
			            } else {
			              this.$func.showToast(res.msg)
			            }
			          } catch (error) {
			            console.error('更新点赞状态失败:', error);
			          }
			        })
			      } else {
			        this.$api.apiCommonCancelLike({
			          comment_id: commentId,
			          type: this.searchWhere.type,
			          qid: qid,
			        }).then(res => {
			          try {
			            if (res.code === 1) {
			              // 更新笔记的点赞状态，增加安全检查
			              if (isChildComment && parentComment) {
			                if (parentComment.children && parentComment.children[index.childIndex]) {
			                  this.$set(parentComment.children[index.childIndex], 'user_like', false);
			                  this.$set(parentComment.children[index.childIndex], 'likes', Math.max(0, (parentComment.children[index.childIndex].likes || 0) - 1));
			                }
			              } else if (typeof index === 'number' && index < this.commentList.length) {
			                this.$set(this.commentList[index], 'user_like', false);
			                this.$set(this.commentList[index], 'likes', Math.max(0, (this.commentList[index].likes || 0) - 1));
			              }
			              this.$func.showToast('取消点赞成功')
			              
			              // 触发点赞事件
			              this.$emit('note-liked', {
			                commentId,
			                qid,
			                index,
			                liked: false
			              })
			            } else {
			              this.$func.showToast(res.msg)
			            }
			          } catch (error) {
			            console.error('更新取消点赞状态失败:', error);
			          }
			        })
			      }
			    }
			  } catch (error) {
			    console.error('点赞操作发生异常:', error);
			    this.$func.showToast('操作失败，请重试');
			  }
			},
			
			// 打开笔记弹窗
			openCommentPopup(commentId) {
				//判断是否登录
				this.$api.apiUserInfo().then(res => {
					if (res.code === 1) {
						this.userInfo = res.data;
						this.commentId = commentId;
						this.popupshow = true;
					} else {
						this.$func.showToast('请先登录');
					}
				}).catch(() => {
					this.$func.showToast('网络异常，请稍后重试');
				})
			},
			
			// 关闭笔记弹窗
			closeCommentPopup() {
				this.popupshow = false;
				this.commentContent = '';
				this.commentId = ''; // 重置commentId
			},
			
			/**
			 * 打开赞赏弹窗
			 * @param {number} commentId - 评论id
			 * @param {number|object} index - 评论索引，可能是数字或包含parentIndex和childIndex的对象
			 */
			openRewardPopup(commentId, index) {

				// 检查参数
				if (!commentId) {
					console.error('赞赏参数错误: commentId为空', {
						commentId,
						type: typeof commentId
					});
					this.$func.showToast('参数错误，评论id为空');
					return;
				}
				
				// 检查登录状态
				this.$api.apiUserInfo().then(res => {
					if (res.code === 1) {
						this.userInfo = res.data;
						this.rewardCommentId = commentId;
						this.rewardCommentIndex = index;
						this.selectedReward = this.rewardOptions[0]; // 默认选中第一个
						this.showRewardPopup = true;
					} else {
						this.$func.showToast('请先登录');
					}
				}).catch(() => {
					this.$func.showToast('网络异常，请稍后重试');
				});
			},
			
			/**
			 * 关闭赞赏弹窗
			 */
			closeRewardPopup() {
				this.showRewardPopup = false;
				this.rewardCommentId = null;
				this.rewardCommentIndex = null;
				this.selectedReward = this.rewardOptions[0];
			},
			
			/**
			 * 提交赞赏
			 */
			async submitReward() {
				if (this.rewardingComment) {
					return;
				}
				
				if (!this.rewardCommentId || !this.selectedReward) {
					this.$func.showToast('参数错误');
					return;
				}
				
				try {
					this.rewardingComment = true;
					
					// 调用赞赏API
					const res = await this.$api.apiCommentReward({
						comment_id: this.rewardCommentId,
						integral: this.selectedReward
					});
					
					if (res && res.code === 1) {
						this.$func.showToast('赞赏成功');
										
						// 更新评论的赞赏积分
						this.updateCommentRewardIntegral(
							this.rewardCommentIndex, 
							this.selectedReward
						);
										
						// 触发赞赏成功事件
						this.$emit('comment-rewarded', {
							commentId: this.rewardCommentId,
							integral: this.selectedReward,
							index: this.rewardCommentIndex
						});
										
						// 关闭弹窗
						this.closeRewardPopup();
					} else {
						// 处理各种失败情况
						const errorMsg = res && res.msg ? res.msg : '赞赏失败';
						this.$func.showToast(errorMsg);
					}
				} catch (error) {
					console.error('赞赏失败:', error);
					this.$func.showToast('网络请求失败，请稍后重试');
				} finally {
					this.rewardingComment = false;
				}
			},
			
			/**
			 * 更新评论的赞赏积分
			 * @param {number|object} index - 评论索引
			 * @param {number} integral - 赞赏积分数量
			 */
			updateCommentRewardIntegral(index, integral) {
				try {
					// 判断是否是子评论
					const isChildComment = typeof index === 'object' && 
						index.parentIndex !== undefined;
					
					if (isChildComment) {
						// 更新子评论
						const parentComment = this.commentList[index.parentIndex];
						if (parentComment && 
							Array.isArray(parentComment.children) && 
							parentComment.children[index.childIndex]) {
							const currentReward = parentComment.children[index.childIndex].reward_integral || 0;
							this.$set(
								parentComment.children[index.childIndex], 
								'reward_integral', 
								currentReward + integral
							);
						}
					} else {
						// 更新主评论
						if (typeof index === 'number' && 
							index >= 0 && 
							index < this.commentList.length) {
							const currentReward = this.commentList[index].reward_integral || 0;
							this.$set(
								this.commentList[index], 
								'reward_integral', 
								currentReward + integral
							);
						}
					}
				} catch (error) {
					console.error('更新赞赏积分失败:', error);
				}
			},
			
			// 时间格式化函数：如"1小时前"、"刚刚"
			formatTime(timeStr) {
				if (!timeStr) return '';
				
				// 修复：将 "2025-12-18 11:21:05" 格式转换为 "2025/12/18 11:21:05" 以兼容 iOS
				// iOS 不支持 "yyyy-MM-dd HH:mm:ss" 格式，需要将 "-" 替换为 "/"
				const formattedTimeStr = timeStr.replace(/-/g, '/');
				
				const now = new Date();
				const time = new Date(formattedTimeStr);
				
				// 检查日期是否有效
				if (isNaN(time.getTime())) {
					console.error('无效的日期格式:', timeStr);
					return timeStr; // 返回原始字符串
				}
				
				const diffInSeconds = Math.floor((now - time) / 1000);
				
				// 1分钟内显示"刚刚"
				if (diffInSeconds < 60) {
					return '刚刚';
				}
				
				// 1小时内显示"X分钟前"
				const diffInMinutes = Math.floor(diffInSeconds / 60);
				if (diffInMinutes < 60) {
					return `${diffInMinutes}分钟前`;
				}
				
				// 24小时内显示"X小时前"
				const diffInHours = Math.floor(diffInMinutes / 60);
				if (diffInHours < 24) {
					return `${diffInHours}小时前`;
				}
				
				// 7天内显示"X天前"
				const diffInDays = Math.floor(diffInHours / 24);
				if (diffInDays < 7) {
					return `${diffInDays}天前`;
				}
				
				// 超过7天显示具体日期
				return `${time.getFullYear()}-${(time.getMonth() + 1).toString().padStart(2, '0')}-${time.getDate().toString().padStart(2, '0')}`;
			},
			
			// 提交笔记
			submitComment() {
				if(!this.commentContent.trim()) {
					this.$func.showToast('请输入笔记内容')
					return
				}
				if(this.commentContent.length > 500) {
					this.$func.showToast('笔记内容不能超过500字符')
					return
				}
	
				this.submittingComment = true;
				this.$api.apiCommonAddComment({
					library_uid: this.libraryUid,// 题库分类uid
					category_uid: this.categoryUid,// 科目uid
					qid: this.questionUid,
					pid: this.commentId,
					type: this.searchWhere.type,
					content: this.commentContent
				}).then(res => {
					// 修复：添加空值检查
					if (!res) {
						console.error('提交笔记响应为undefined');
						this.$func.showToast('网络请求异常，请稍后重试');
						return;
					}
					
					if (res.code === 1) {
						this.$func.showToast('笔记发布成功')
						
						// 触发笔记添加成功事件
						this.$emit('note-added', {
							questionUid: this.questionUid,
							libraryUid: this.libraryUid,
							categoryUid: this.categoryUid,
							commentId: this.commentId,
							content: this.commentContent,
							response: res.data
						})
						
						// 关闭弹窗并重置状态
						this.closeCommentPopup();
						
						// 笔记发布成功后1s内刷新列表
						setTimeout(() => {
							this.onRefresh();
						}, 500); // 500ms，确保1秒内完成
					} else {
						this.$func.showToast(res.msg || '发布失败')
					}
				}).catch(error => {
					console.error('提交笔记失败:', error);
					this.$func.showToast('网络请求失败，请稍后重试');
				}).finally(() => {
					this.submittingComment = false;
				})
			},
			
			// 跳转
			tl(url) {
				if(url === '') {
					this.$func.showToast('暂未开放')
					return
				}
				this.$func.navigatorTo(url)
			},
			
			// 跳转到笔记详情页面
			goToNoteDetail(commentId) {
				if (!commentId) {
					console.error('评论ID为空');
					return;
				}
				
				uni.navigateTo({
					url: `/subpages/examOther/noteDetail?comment_id=${commentId}`
				});
			},
			
			// 获取当前登录用户的昵称
			getUserNickname() {
				const userInfo = getApp().globalData.userInfo;
				return userInfo ? userInfo.nickname : '';
			}
		}
	}
</script>
<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";

.comment-container {
  background-color: #F8F9FA;
}

.comment-header {
  padding:15rpx;
  background-color: #FFFFFF;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.05);
  border-bottom: 1rpx solid #E9ECEF;
}

/* 笔记项样式 */
.comment-item {
  position: relative;
  transition: all 0.3s ease;
}

/* 笔记内容样式 */
.comment-content {
  word-break: break-word;
  margin: 20rpx 0;
  line-height: 1.8;
  padding-left: 50rpx;
  font-size: 30rpx;
  color: #333333;
}

/* 点赞用户列表和评论按钮容器 */
.action-row {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  margin: 10rpx 0 20rpx 50rpx;
  overflow: hidden;
}

/* 点赞用户列表样式 */
.like-users-container {
  border-radius: 8rpx;
  line-height: 1.6;
  margin: 0;
  padding: 10rpx 15rpx;
  overflow: hidden;
  white-space: nowrap;
  flex: 1;
  margin-right: 15rpx;
}

/* 评论操作按钮样式 */
.comment-actions {
  padding: 0;
  margin: 0;
  text-align: right;
  white-space: nowrap;
  
  .comment-action-btn {
    display: inline-block;
    padding: 8rpx 16rpx;
    border-radius: 20rpx;
    background-color: #F8F9FA;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    
    /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
  }
}

/* 点赞和赞赏按钮通用样式 */
.comment-like-btn,
.comment-reward-btn {
  padding: 8rpx 16rpx;
  border-radius: 20rpx;
  transition: all 0.3s ease;
  
  /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
}

/* 子评论样式 */
.child-comments {
  margin-top: 20rpx;
  padding-left: 50rpx;
}

.child-comment-item {
  transition: all 0.3s ease;
  padding: 20rpx;
  background-color: #F8F9FA;
  border-radius: 12rpx;
  margin: 5rpx 20rpx;
  
  &:not(:last-child) {
    margin-bottom: 15rpx;
  }
  
  /* 昵称和内容容器样式 */
  .flex-1 {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    width: 100%;
  }
  
  /* 昵称样式 */
  .nickname-text {
    margin-right: 8rpx;
    white-space: nowrap;
    line-height: 1.6;
  }
  
  /* 内容样式 */
  .comment-text {
    flex: 1;
    line-height: 1.6;
    min-width: 0;
    word-break: break-word;
  }
}

.view-more-btn {
  padding: 20rpx;
  margin: 10rpx 20rpx;
  text-align: center;
  border-radius: 12rpx;
  background-color: #F0F7FF;
  transition: all 0.3s ease;
  
  /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
}

.comment-text {
  line-height: 30rpx;
  word-break: break-word;
}

/* 空状态样式 */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 120rpx 0;
  color: #999999;
  text-align: center;
  background-color: #FFFFFF;
  margin: 0 20rpx;
  border-radius: 16rpx;
}

/* 列表样式 */
.comment-list {
  padding-bottom: 120rpx;
}

/* 对齐样式 */
.items-center {
  align-items: center;
}

.flex-1 {
  flex: 1;
}

/* 响应式适配 */
@media screen and (min-width: 414px) {
  .comment-item {
    margin: 25rpx 20rpx;
  }
  
  .comment-content {
    font-size: 30rpx;
    margin: 25rpx 0;
  }
}

@media screen and (max-width: 375px) {
  .comment-item {
    margin: 15rpx 12rpx;
  }
  
  .comment-content {
    font-size: 28rpx;
    margin: 15rpx 0;
  }
  
  .popup-container {
    padding: 20rpx;
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
    cursor: pointer;
    
    /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
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
    cursor: pointer;
    transition: all 0.3s ease;
    
    &.selected {
      background-color: #FFF5E6;
    }
    
    /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
    
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
</style>