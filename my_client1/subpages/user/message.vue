<template>
	<view class="template-message">
    <!-- 顶部自定义导航 -->
    <tn-nav-bar fixed :bottomShadow="false" backTitle=" ">
      <view class="" @click="showModal">
        <text class="tn-text-lg">消息中心</text>
        <text class="tn-text-xl tn-padding-left-sm tn-icon-group-circle"></text>  
      </view>
    </tn-nav-bar>
    
    <!-- <tn-nav-bar fixed :bottomShadow="false">消息通知</tn-nav-bar> -->
    
    <!-- 方式1 start-->
    <view class="tn-flex tn-message-fixed" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="tn-flex-1 tn-padding-sm tn-margin-xs tn-radius" @click="switchType(1)">
        <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
          <view class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-orange tn-color-white">
            <view class="tn-icon-topics-fill">
              <tn-badge v-if="unreadCounts.comment > 0" backgroundColor="#E72F8C" fontColor="#FFFFFF" :absolute="true" :fontSize="22">
                <text>{{ unreadCounts.comment > 99 ? '99+' : unreadCounts.comment }}</text>
              </tn-badge>
            </view>
          </view>  
          <view class="tn-color-black tn-text-center">
            <text class="tn-text-ellipsis">评论</text>
          </view>
        </view>
      </view>
      <view class="tn-flex-1 tn-padding-sm tn-margin-xs tn-radius" @click="switchType(2)">
        <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
          <view class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-red tn-color-white">
            <view class="tn-icon-like-fill">
              <tn-badge v-if="unreadCounts.like > 0" backgroundColor="#E72F8C" fontColor="#FFFFFF" :absolute="true" :fontSize="22">
                <text>{{ unreadCounts.like > 99 ? '99+' : unreadCounts.like }}</text>
              </tn-badge>
            </view>
          </view>  
          <view class="tn-color-black tn-text-center">
            <text class="tn-text-ellipsis">爱心</text>
          </view>
        </view>
      </view>
      <view class="tn-flex-1 tn-padding-sm tn-margin-xs tn-radius" @click="switchType(3)">
        <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
          <view class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-cyan tn-color-white">
            <view class="tn-icon-praise-fill">
              <tn-badge v-if="unreadCounts.reward > 0" backgroundColor="#E72F8C" fontColor="#FFFFFF" :absolute="true" :fontSize="22">
                <text>{{ unreadCounts.reward > 99 ? '99+' : unreadCounts.reward }}</text>
              </tn-badge>
            </view>
          </view>  
          <view class="tn-color-black tn-text-center">
            <text class="tn-text-ellipsis">赞赏</text>
          </view>
        </view>
      </view>
      <view class="tn-flex-1 tn-padding-sm tn-margin-xs tn-radius" @click="switchType(4)">
        <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
          <view class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-blue tn-color-white">
            <view class="tn-icon-notice-fill">
              <tn-badge v-if="unreadCounts.system > 0" backgroundColor="#E72F8C" fontColor="#FFFFFF" :absolute="true" :fontSize="22">
                <text>{{ unreadCounts.system > 99 ? '99+' : unreadCounts.system }}</text>
              </tn-badge>
            </view>
          </view>  
          <view class="tn-color-black tn-text-center">
            <text class="tn-text-ellipsis">系统</text>
          </view>
        </view>
      </view>
    </view>
    <!-- 方式1 end-->
    
    <tn-modal v-model="show1" :custom="true">
      <view class="custom-modal-content">
        <view class="tn-text-center tn-padding-top-sm tn-text-xxl tn-text-bold">提 示</view>
        <view class="tn-text-center tn-padding-top tn-text-lg tn-color-gray">确定将所有消息标为已读吗？</view>
        <view class="tn-flex tn-flex-row-between tn-margin-top-xl">
          <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
            <tn-button backgroundColor="#00FFC6" padding="40rpx 0" width="90%" shadow fontBold shape="round" @click="show1 = false">
              <text class="tn-color-black">取 消</text>
            </tn-button>
          </view>
          <view class="tn-flex-1 justify-content-item tn-margin-xs tn-text-center">
            <tn-button backgroundColor="#FFF00D" padding="40rpx 0" width="90%" shadow fontBold shape="round" @click="markAllRead">
              <text class="tn-color-black">确 定</text>
            </tn-button>
          </view>
        </view>
      </view>
    </tn-modal>
    
    <view class="tn-safe-area-inset-bottom tn-margin-bottom-sm" style="margin-top: 260rpx;" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 加载提示 -->
      <view v-if="loading && pageNo === 1" class="tn-padding tn-text-center">
        <tn-loading type="cycle" color="#0E7DFF" />
        <view class="tn-text-sm tn-color-gray tn-margin-top-sm">加载中...</view>
      </view>
      
      <!-- 消息列表 -->
      <tn-swipe-action v-if="!loading || pageNo > 1">
        <tn-swipe-action-item 
          v-for="(item, index) in messageList" 
          :key="index" 
          :name="index" 
          :options="options"
          @click="handleSwipeAction($event, item.id, index)"
        >
          <view 
            class="tn-flex tn-flex-row-between tn-flex-col-center tn-padding"
            :class="{'message-unread': item.is_read === 0}"
            @click="handleMessageClick(item, index)"
          >
            <view class="justify-content-item">
              <view class="tn-flex tn-flex-col-center tn-flex-row-left">
                <view class="logo-pic">
                  <view class="logo-image">
                    <view 
                      class="tn-shadow-blur" 
                      :style="'background-image:url(' + getSenderAvatar(item) + ');width: 110rpx;height: 110rpx;background-size: cover;'"
                    />
                  </view>
                </view>
                <view class="tn-padding-right tn-color-black">
                  <view class="tn-padding-right tn-padding-left-sm tn-text-lg tn-text-bold">
                    {{ item.title }}
                  </view>
                  <view class="tn-padding-right tn-padding-top-xs tn-text-ellipsis tn-padding-left-sm">
                    <text class="tn-color-grey">{{ item.content }}</text>
                  </view>
                </view>
              </view>
            </view>
            <view class="justify-content-item">
              <view  class="tn-flex tn-flex-row-right">
                <tn-badge v-if="item.is_read === 0" backgroundColor="tn-cool-bg-color-1" fontColor="tn-color-white"  :radius="60">
                  <text style="font-size: 16rpx; line-height: 20rpx;">未读</text>
                </tn-badge>
              </view>
              <view class="tn-flex tn-flex-row-right">
                <view class="tn-text-sm tn-color-gray">{{ item.time_ago }}</view>
              </view>
              
            </view>
          </view>
        </tn-swipe-action-item>
      </tn-swipe-action>
      
      <!-- 空状态 -->
      <view v-if="!loading && messageList.length === 0" class="tn-padding-xl tn-text-center">
        <view class="tn-icon-tips tn-text-xxl tn-color-gray" />
        <view class="tn-text-sm tn-color-gray tn-margin-top">暂无消息</view>
      </view>
      
      <!-- 加载更多提示 -->
      <view v-if="loading && pageNo > 1" class="tn-padding tn-text-center">
        <view class="tn-text-sm tn-color-gray">加载中...</view>
      </view>
      
      <view v-if="!hasMore && messageList.length > 0" class="tn-padding tn-text-center">
        <view class="tn-text-sm tn-color-gray">没有更多了</view>
      </view>
    </view>
    
	</view>
</template>

<script>
  import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
  import messageApi from '@/util/api/message.js'
  
	export default {
    name: 'TemplateMessage',
    mixins: [template_page_mixin],
		data() {
			return {
        show1: false,
        // 消息类型: 1-评论 2-爱心 3-赞赏 4-系统
        currentType: null, // null表示显示所有类型
        // 未读数量
        unreadCounts: {
          comment: 0,
          like: 0,
          reward: 0,
          system: 0
        },
        // 消息列表
        messageList: [],
        // 分页参数
        pageNo: 1,
        pageSize: 20,
        hasMore: true,
        loading: false,
        // 滑动删除选项
				options: [{
				    icon: 'star',
				    style: {
				      backgroundColor: '#FFA726',
				      width: '80rpx',
				      height: '80rpx',
				      margin: '0 12rpx',
				      borderRadius: '100rpx'
				    }
				  },
				  {
				    icon: 'delete',
				    style: {
				      backgroundColor: '#E83A30',
				      width: '80rpx',
				      height: '80rpx',
				      margin: '0 12rpx',
				      borderRadius: '100rpx'
				    }
				  }
				]
			}
		},
		onLoad() {
      this.fetchUnreadCount()
      this.fetchMessageList()
		},
    // 下拉刷新
    onPullDownRefresh() {
      this.pageNo = 1
      this.messageList = []
      this.fetchMessageList().then(() => {
        uni.stopPullDownRefresh()
      })
    },
    // 上拉加载
    onReachBottom() {
      if (this.hasMore && !this.loading) {
        this.pageNo++
        this.fetchMessageList()
      }
    },
		methods: {
      /**
       * 获取未读消息数量
       */
      async fetchUnreadCount() {
        try {
          const res = await messageApi.apiMessageUnreadCount()
          if (res.code === 1) {
            this.unreadCounts = res.data
          }
        } catch (error) {
          console.error('获取未读数量失败:', error)
        }
      },
      
      /**
       * 获取消息列表
       */
      async fetchMessageList() {
        if (this.loading) return
        
        this.loading = true
        try {
          const params = {
            page_no: this.pageNo,
            page_size: this.pageSize
          }
          
          // 如果选中了特定类型，添加type参数
          if (this.currentType !== null) {
            params.type = this.currentType
          }
          
          console.log('[fetchMessageList] 请求参数:', params)
          
          const res = await messageApi.apiMessageList(params)
          console.log('[fetchMessageList] 响应数据:', res)
          
          // 增加空值判断，防止500错误导致崩溃
          if (res && res.code === 1 && res.data) {
            let lists = res.data.lists || []
            
            // 处理每条消息的 extra 字段
            lists = lists.map(item => {
              // 如果 extra 是字符串，尝试解析为对象
              if (typeof item.extra === 'string') {
                try {
                  item.extra = JSON.parse(item.extra)
                } catch (error) {
                  console.error('解析消息extra失败:', error, item.extra)
                  item.extra = {}
                }
              } else if (!item.extra) {
                item.extra = {}
              }
              return item
            })
            
            if (this.pageNo === 1) {
              this.messageList = lists
            } else {
              this.messageList = this.messageList.concat(lists)
            }
            this.hasMore = res.data.more || false
            console.log('[fetchMessageList] 消息数量:', this.messageList.length, '是否有更多:', this.hasMore)
          } else {
            console.error('消息列表响应异常:', res)
            this.$func.showToast(res && res.msg ? res.msg : '加载失败')
          }
        } catch (error) {
          console.error('获取消息列表失败:', error)
          this.$func.showToast('加载失败，请稍后重试')
        } finally {
          this.loading = false
        }
      },
      
      /**
       * 切换消息类型
       */
      switchType(type) {
        console.log('[switchType] 切换类型:', type)
        this.currentType = type
        this.pageNo = 1
        this.messageList = []
        this.hasMore = true  // 重置加载更多标志
        this.fetchMessageList()
      },
      
      /**
       * 弹出模态框 - 全部已读
       */
      showModal(event) {
        this.openModal()
      },
      
      /**
       * 打开模态框
       */
      openModal() {
        this.show1 = true
      },
      
      /**
       * 全部已读
       */
      async markAllRead() {
        try {
          const res = await messageApi.apiMessageMarkRead({ message_ids: null })
          if (res.code === 1) {
            this.$func.showToast('已全部标记为已读')
            this.show1 = false
            // 刷新列表和未读数量
            this.messageList.forEach(item => {
              item.is_read = 1
            })
            this.fetchUnreadCount()
          }
        } catch (error) {
          this.$func.showToast('操作失败')
        }
      },
      
      /**
       * 处理滑动操作
       */
      handleSwipeAction(e, messageId, index) {
        const action = e.content.text
        
        if (action === '已读') {
          this.markRead([messageId], index)
        } else if (action === '删除') {
          this.deleteMessage([messageId], index)
        }
      },
      
      /**
       * 标记已读
       */
      async markRead(messageIds, index) {
        try {
          const res = await messageApi.apiMessageMarkRead({ message_ids: messageIds })
          if (res.code === 1) {
            this.$func.showToast('已标记为已读')
            // 更新列表中的状态
            if (index !== undefined) {
              this.messageList[index].is_read = 1
            }
            this.fetchUnreadCount()
          }
        } catch (error) {
          this.$func.showToast('操作失败')
        }
      },
      
      /**
       * 删除消息
       */
      async deleteMessage(messageIds, index) {
        try {
          const res = await messageApi.apiMessageDelete({ message_ids: messageIds })
          if (res.code === 1) {
            this.$func.showToast('删除成功')
            // 从列表中移除
            if (index !== undefined) {
              this.messageList.splice(index, 1)
            }
            this.fetchUnreadCount()
          }
        } catch (error) {
          this.$func.showToast('删除失败')
        }
      },
      
      /**
       * 获取发送者头像
       */
      getSenderAvatar(item) {
        try {
          // 处理 extra 可能是字符串的情况
          let extra = item.extra
          if (typeof extra === 'string') {
            extra = JSON.parse(extra)
          }
          return extra?.sender_avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'
        } catch (error) {
          console.error('解析头像失败:', error)
          return 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'
        }
      },
      
      /**
       * 点击消息项
       */
      handleMessageClick(message, index) {
        // 如果未读，标记为已读
        if (message.is_read === 0) {
          this.markRead([message.id], index)
        }
        
        // 根据消息类型跳转到对应页面
        let extra = message.extra || {}
        
        // 处理 extra 可能是字符串的情况
        if (typeof extra === 'string') {
          try {
            extra = JSON.parse(extra)
          } catch (error) {
            console.error('解析extra失败:', error)
            extra = {}
          }
        }
        
        // 评论相关消息，跳转到详情页
        if ([1, 2, 3].includes(message.type)) {
          if (extra.qid) {
            // 跳转到试题或文章详情
            const type = message.sub_type === 'article_comment' ? 'article' : 'question'
            const url = type === 'article' 
              ? `/subpages/news/content?id=${extra.qid}`
              : `/subpages/exam/question?uid=${extra.qid}`
            this.$func.navigatorTo(url)
          }
        }
      }
		}
	}
</script>

<style lang="scss" scoped>
	.template-message{
	}
  
  .tn-message-fixed{
    position: fixed;
    background-color: rgba(255,255,255,1);
    box-shadow: 0rpx 0rpx 30rpx 0rpx rgba(0, 0, 0, 0.07);
    top: 0;
    width: 100%;
    transition: all 0.25s ease-out;
    z-index: 100;
  }
  
  /* 未读消息高亮 */
  .message-unread {
    background-color: #f0f9ff;
  }
  
  /* 图标容器1 start */
  .icon1 {
    &__item {
      width: 30%;
      background-color: #FFFFFF;
      border-radius: 10rpx;
      padding: 30rpx;
      margin: 20rpx 10rpx;
      transform: scale(1);
      transition: transform 0.3s linear;
      transform-origin: center center;
      
      &--icon {
        width: 90rpx;
        height: 90rpx;
        font-size: 60rpx;
        border-radius: 50%;
        margin-bottom: 18rpx;
        position: relative;
        z-index: 1;
        
        &::after {
          content: " ";
          position: absolute;
          z-index: -1;
          width: 100%;
          height: 100%;
          left: 0;
          bottom: 0;
          border-radius: inherit;
          opacity: 1;
          transform: scale(1, 1);
          background-size: 100% 100%;
          background-image: url(https://resource.tuniaokj.com/images/cool_bg_image/icon_bg5.png);
        }
      }
    }
  }
  
  /* 用户头像 start */
  .logo-image {
    width: 90rpx;
    height: 90rpx;
    position: relative;
    overflow: hidden;
    border-radius: 50%;
  }
  
  .logo-pic {
    background-size: cover;
    background-repeat: no-repeat;
    // background-attachment:fixed;
    background-position: top;
    // box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.15);
    border-radius: 50%;
    overflow: hidden;
    // background-color: #FFFFFF;
  }
</style>
