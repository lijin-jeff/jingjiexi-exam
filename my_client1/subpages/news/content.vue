<template>
  <view class="template-news tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <template #back>
          <view
            class="tn-custom-nav-bar__back"
            style="width:200rpx;"
          >
            <text
              class="icon tn-icon-left"
              @click="goBack"
            />
            <text
              class="icon tn-icon-home-capsule-fill"
              @click="goHome"
            />
          </view>
        </template>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center">
          <text class="tn-text-bold tn-text-xl tn-color-white">
            图文详情
          </text>
        </view>
      </tn-nav-bar>
    </view>

    <!-- 内容区域开始-->
    <view
      class="tn-margin-top-xs"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <!-- 返回顶部按钮 -->
      <tn-fab 
        v-if="showBackToTop" 
        :bottom="120" 
        :right="20" 
        :show-mask="false" 
        :btn-list="backToTopBtnList" 
        @click="handleFabBtnClick"
      />
			
      <view class="nav_title--wrap">
        <view class="nav_title tn-padding-sm">
          {{ articleContent.title || '加载中..' }}
        </view>
      </view>

      <view class="tn-flex tn-flex-wrap tn-margin-left-sm tn-margin-right-sm tn-color-grey">
        <view class="tn-padding-right-lg">
          <text class="tn-icon-eye" />
          <text class="">
            {{ articleContent.click || 0 }}
          </text>
        </view>
        <view class="tn-padding-right-lg">
          <text class="tn-icon-my-simple" />
          <text class="">
            {{ articleContent.author || '未知作者' }}
          </text>
        </view>
        <view>
          <text class="tn-icon-calendar" />
          <text class="">
            {{ articleContent.create_time || '' }}
          </text>
        </view>
      </view>

      <view
        class="tn-padding-right-sm tn-padding-left-sm tn-margin-top-sm"
      >
        <mp-html
          v-if="articleContent.content"
          :content="articleContent.content"
          @parse="onHtmlParse"
        />
        <view
          v-else
          class="tn-text-center tn-padding-xl tn-color-grey"
        >
          <text
            class="tn-icon-loading tn-text-lg"
            style="display: block;margin-bottom: 10rpx;"
          />
          内容加载中..
        </view>
      </view>
      <view
        v-if="articleContent.is_external_link === 1 && articleContent.external_link"
        class="external-link-container"
      >
        <tn-button
          type="primary"
          shape="round"
          size="large"
          backgroundColor="#007AFF"
          fontColor="#FFFFFF"
          shadow
          @click="openExternalLink"
        >
          <text class="tn-icon-external-link tn-margin-right-xs"></text>
          点击跳转外部链接
        </tn-button>
      </view>
    </view>
    <!-- 内容区域结束 -->

    <!-- 底部菜单开始-->
    <view
      class="tn-flex"
      style="height: 100rpx; width: 100%; line-height: 100rpx; position: fixed; bottom: 0rpx;background-color: #fff;z-index: 1024;"
    >
      <view
        class="tn-flex tn-flex-col-center"
        style="width: 50%;font-size: 30rpx;"
      >
        <view class="tn-flex tn-flex-direction-row tn-padding-left-lg">
          <text class="tn-icon-my-add tn-text-bold" />
        </view>
        <view class="tn-padding-left-sm">
          <text>{{ articleContent.author || '' }}</text>
        </view>
      </view>
      <view
        class="tn-flex tn-flex-col-center tn-flex-row-right"
        style="width: 45%; line-height: 40rpx; text-align: center;"
      >
        <!-- <view
          class="bottom-menu"
          @click="subscribe"
        >
          <view class="bottom-menu--icon">
            <text class="tn-icon-notice" />
          </view>
          <view class="bottom-menu--text">
            <text>订阅</text>
          </view>
        </view> -->
        <view
          class="bottom-menu"
          @click="handleCollection"
        >
          <view class="bottom-menu--icon">
            <text
              v-if="articleContent.collect"
              class="tn-icon-star-fill tt-text-main-color"
            />
            <text
              v-else
              class="tn-icon-star"
            />
          </view>
          <view class="bottom-menu--text">
            <text>在看</text>
          </view>
        </view>
        <view
          class="bottom-menu"
          @click="openCommentPopup(0)"
        >
          <view class="bottom-menu--icon">
            <text class="tn-icon-message" />
          </view>
          <view class="bottom-menu--text">
            <text>写留言</text>
          </view>
        </view>
      </view>
      <view class="tn-margin-bottom-xl" />
    </view>
    <!-- 底部菜单结束 -->

    <!-- 评论区域开始-->
    <view
      class="tn-margin"
      style="padding-bottom: 120rpx;"
    >
	<!-- 评论列表头 -->
      <view class="comment-title tn-padding-sm tn-text-center tn-text-df tn-text-bold tn-color-black">
       —— 评论列表 ——
      </view>
      <!-- 评论列表 -->
      <view v-if="commentList.length > 0">
        <view
          v-for="(comment, index) in commentList"
          :key="comment.id"
          class="comment-item"
        >
          <view v-if="comment.pid == 0">
            <!-- 图标logo/头像 -->
            <view class="tn-flex tn-flex-row-between tn-flex-col-center tn-margin-top-xl">
              <view class="justify-content-item">
                <view class="tn-flex tn-flex-col-center tn-flex-row-left">
                  <view class="logo-pic tn-shadow">
                    <view class="logo-image">
                      <view
                        class="tn-shadow-blur"
                        :style="{backgroundImage: `url(${comment.user.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'})`}"
                        style="width: 60rpx;height: 60rpx;background-size: cover;"
                        @click="tl('/subpages/user/userdata')"
                      />
                    </view>
                  </view>
                  <view
                    class="tn-padding-right tn-padding-left-sm"
                    @click="openCommentPopup(comment.id)"
                  >
                    <view class="tn-padding-right tn-text-df tn-text-bold tn-color-black">
                      {{ comment.user.nickname || '匿名用户' }}
                    </view>
                    <view
                      class="tn-padding-right tn-text-ellipsis tn-text-xs tn-color-gray"
                      style="padding-top: 5rpx;"
                    >
                      {{ comment.create_time || '' }}
                    </view>
                  </view>
                </view>
              </view>
              <!-- 主评论点赞-->
              <view 
                class="justify-content-item tn-flex-row-center tn-flex-col-center tn-color-gray comment-like-btn" 
                style="z-index: 10; padding: 10rpx 20rpx;"
                @click="handleLike(comment.id, comment.qid, index)"
              >
                <view class="tn-text-center">
                  <!-- 合并条件判断，使用动态class切换点赞状态-->
                  <text :class="[ 'tn-padding-xs', { 'tn-icon-like-fill tn-color-red': comment.user_like, 'tn-icon-like-lack tn-color-gray': !comment.user_like }]" />
                </view>
                <view class="tn-text-center">
                  <text :class="['tn-text-xs', { 'tn-color-red': comment.user_like }]">
                    {{ comment.likes || 0 }}
                  </text>
                </view>
              </view>
            </view>
            <!-- 评论内容 -->
            <view
              class=""
              style="word-break: break-word;margin: 20rpx 30rpx 10rpx 80rpx;"
              @click="openCommentPopup(comment.id)"
            >
              {{ comment.content }}
            </view>
            
            <!-- 点赞用户列表（显示前5名） -->
            <view 
              v-if="comment.like_users && comment.like_users.length > 0" 
              class="like-users-container"
              style="margin: 10rpx 30rpx 20rpx 80rpx;"
            >
              <view class="tn-flex tn-flex-row-left tn-flex-col-center">
                <text class="tn-icon-like-fill tn-color-red tn-text-xs" style="margin-right: 10rpx;" />
                <view class="tn-flex tn-flex-wrap">
                  <text 
                    v-for="(liker, likerIndex) in comment.like_users" 
                    :key="liker.user_id"
                    class="tn-text-xs tn-color-gray"
                  >
                    {{ liker.nickname }}<text v-if="likerIndex < comment.like_users.length - 1">、</text>
                  </text>
                  <text 
                    v-if="comment.likes > 5" 
                    class="tn-text-xs tn-color-gray"
                  >
                    等{{ comment.likes }}人点赞
                  </text>
                  <text 
                    v-else-if="comment.like_users.length > 0" 
                    class="tn-text-xs tn-color-gray"
                  >
                    点赞
                  </text>
                </view>
              </view>
            </view>
          </view>
          <!-- 若存在父评论，展示回复内容-->
          <view v-if="comment.children && comment.children.length > 0">
            <!-- 使用ReadMore组件实现折叠，超过3个子评论时折叠 -->
            <tn-read-more
              v-if="comment.children.length > 3"
              :show-height="calculateChildrenHeight(comment.children.slice(0, 3))"
              show-more-text="展开更多回复"
              show-less-text="收起回复"
              font-size="24"
              text-color="#909399"
            >
              <view
                v-for="(child, childIndex) in comment.children"
                :key="child.id"
                class="tn-bg-gray--light tn-padding-sm"
                style="margin: 20rpx 30rpx 30rpx 80rpx;border-radius: 10rpx;"
              >
                <text class="tn-text-bold tn-padding-right-xs">
                  {{ child.user.nickname || '匿名用户' }}:
                </text>
                <text style="line-height: 40rpx;word-break: break-word;">
                  {{ child.content }}
                </text>
                <view class="tn-flex tn-flex-row-between tn-margin-top-xs">
                  <view
                    class="justify-content-item tn-text-xs tn-color-gray"
                    style="padding-top: 5rpx;"
                  >
                    {{ child.create_time || '' }}
                  </view>
                  <!-- 子评论点赞- 传递父评论和子评论索引 -->
                  <view
                    class="justify-content-item tn-text-xs tn-color-gray"
                    @click="handleLike(child.id, child.qid, {parentIndex: index, childIndex: childIndex})"
                  >
                    <text :class="['tn-padding-xs', { 'tn-color-red': child.user_like }]">
                      {{ child.likes || 0 }}
                    </text>
                    <text :class="['tn-icon-like-lack', { 'tn-icon-like-fill tn-color-red': child.user_like }]" />
                  </view>
                </view>
                
                <!-- 子评论点赞用户列表 -->
                <view 
                  v-if="child.like_users && child.like_users.length > 0" 
                  class="like-users-container"
                  style="margin-top: 10rpx;"
                >
                  <view class="tn-flex tn-flex-row-left tn-flex-col-center">
                    <text class="tn-icon-like-fill tn-color-red tn-text-xs" style="margin-right: 8rpx;" />
                    <view class="tn-flex tn-flex-wrap">
                      <text 
                        v-for="(liker, likerIndex) in child.like_users" 
                        :key="liker.user_id"
                        class="tn-text-xs tn-color-gray"
                        style="font-size: 22rpx;"
                      >
                        {{ liker.nickname }}<text v-if="likerIndex < child.like_users.length - 1">、</text>
                      </text>
                      <text 
                        v-if="child.likes > 5" 
                        class="tn-text-xs tn-color-gray"
                        style="font-size: 22rpx;"
                      >
                        等{{ child.likes }}人
                      </text>
                    </view>
                  </view>
                </view>
              </view>
            </tn-read-more>
            
            <!-- 子评论不超过3个时直接显示 -->
            <view v-else>
              <view
                v-for="(child, childIndex) in comment.children"
                :key="child.id"
                class="tn-bg-gray--light tn-padding-sm"
                style="margin: 20rpx 30rpx 30rpx 80rpx;border-radius: 10rpx;"
              >
                <text class="tn-text-bold tn-padding-right-xs">
                  {{ child.user.nickname || '匿名用户' }}:
                </text>
                <text style="line-height: 40rpx;word-break: break-word;">
                  {{ child.content }}
                </text>
                <view class="tn-flex tn-flex-row-between tn-margin-top-xs">
                  <view
                    class="justify-content-item tn-text-xs tn-color-gray"
                    style="padding-top: 5rpx;"
                  >
                    {{ child.create_time || '' }}
                  </view>
                  <!-- 子评论点赞- 传递父评论和子评论索引 -->
                  <view
                    class="justify-content-item tn-text-xs tn-color-gray"
                    @click="handleLike(child.id, child.qid, {parentIndex: index, childIndex: childIndex})"
                  >
                    <text :class="['tn-padding-xs', { 'tn-color-red': child.user_like }]">
                      {{ child.likes || 0 }}
                    </text>
                    <text :class="['tn-icon-like-lack', { 'tn-icon-like-fill tn-color-red': child.user_like }]" />
                  </view>
                </view>
                
                <!-- 子评论点赞用户列表 -->
                <view 
                  v-if="child.like_users && child.like_users.length > 0" 
                  class="like-users-container"
                  style="margin-top: 10rpx;"
                >
                  <view class="tn-flex tn-flex-row-left tn-flex-col-center">
                    <text class="tn-icon-like-fill tn-color-red tn-text-xs" style="margin-right: 8rpx;" />
                    <view class="tn-flex tn-flex-wrap">
                      <text 
                        v-for="(liker, likerIndex) in child.like_users" 
                        :key="liker.user_id"
                        class="tn-text-xs tn-color-gray"
                        style="font-size: 22rpx;"
                      >
                        {{ liker.nickname }}<text v-if="likerIndex < child.like_users.length - 1">、</text>
                      </text>
                      <text 
                        v-if="child.likes > 5" 
                        class="tn-text-xs tn-color-gray"
                        style="font-size: 22rpx;"
                      >
                        等{{ child.likes }}人
                      </text>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
				
        <!-- LoadMore组件 -->
        <tn-load-more 
          v-if="hasMoreComments || loadingComments" 
          :status="loadingComments ? 'loading' : 'loadmore'" 
          :load-text="loadText" 
          @click="onCommentLoadMore"
        />
				
        <!-- 没有更多数据时显示-->
        <tn-load-more 
          v-else 
          status="nomore" 
          :load-text="loadText" 
        />
      </view>
			
      <!-- 空状态-->
      <view
        v-else-if="!loadingComments"
        class="tn-text-center tn-padding-xl tn-color-grey"
      >
        <text
          class="tn-icon-comment tn-text-lg"
          style="display: block;margin-bottom: 10rpx;"
        />
        暂无评论，快来抢沙发吧~
      </view>
			
      <!-- 初始加载中状态-->
      <view
        v-if="loadingComments && commentList.length === 0"
        class="tn-text-center tn-padding-xl tn-color-grey"
      >
        <text
          class="tn-icon-loading tn-text-lg"
          style="display: block;margin-bottom: 10rpx;"
        />
        评论加载中...
      </view>
    </view>
    <!-- 评论区域结束 -->

    <!-- 评论输入框 -->
    <tn-popup
      v-model="popupshow"
      mode="bottom"
      height="650rpx"
    >
      <view class="tn-flex tn-flex-row-between tn-flex-col-center tn-padding-top tn-margin">
        <view class="tn-flex justify-content-item">
          <view
            class="tn-bg-black tn-color-white tn-text-center"
            style="border-radius: 100rpx;margin-right: 8rpx;width: 45rpx;height: 45rpx;line-height: 45rpx;"
          >
            <!-- 用户头像 -->
            <view class="avatar-all">
              <view
                class="tn-shadow-blur"
                :style="{backgroundImage: `url(${userAvatar})`}"
                style="border-radius: 100rpx;width: 45rpx;height: 45rpx;background-size: cover;"
              />
            </view>
          </view>
          <view class="tn-text-lg tn-padding-right-xs tn-text-bold">
            想说点什么?
          </view>
        </view>
        <view class="justify-content-item tn-text-df tn-color-grey">
          <text class="tn-padding-xs">
            {{ commentContent.length }}/500
          </text>
          <text class="tn-icon-keyboard-circle" />
        </view>
      </view>
      <view
        class="tn-margin tn-bg-gray--light tn-padding"
        style="border-radius: 10rpx;"
      >
        <textarea
          v-model="commentContent"
          maxlength="500"
          placeholder="说点什么?, 万一火了..."
          placeholder-style="color:#AAAAAA"
        />
      </view>
      <!-- 提交按钮 -->
      <view
        class="tn-padding-sm"
        style="display: flex;justify-content: space-between;"
      >
        <tn-button
          shape="round"
          background-color="tn-cool-bg-color-15--reverse"
          width="100%"
          shadow
          @click="popupshow = false"
        >
          <text
            class="tn-color-white"
            hover-class="tn-hover"
            :hover-stay-time="150"
          >
            取消
          </text>
        </tn-button>
        <tn-button
          shape="round"
          background-color="tn-cool-bg-color-9"
          width="100%"
          shadow
          :disabled="!commentContent.trim() || submittingComment"
          @click="submitComment"
        >
          <text
            class="tn-color-white"
            hover-class="tn-hover"
            :hover-stay-time="150"
          >
            {{ submittingComment ? '发送中...' : '发送' }}
          </text>
        </tn-button>
      </view>
    </tn-popup>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	
	export default {
		name: 'TemplateNews',
		components: {
	},
		mixins: [template_page_mixin],
		onReachBottom() {
			// 页面滚动到底部时自动触发加载更多评论
			this.onCommentLoadMore();
		},
		onPullDownRefresh() {
			// 下拉刷新时重新加载内容		this.onRefresh();
		},
		data() {
			return {
				popupshow: false,
				commentId: 0,
				liking: false, 
				groupAvatarList: [],
				groupList: [],
				searchWhere: { // 文章查询条件
					id: '',
					type: 2,
				},
				relArticleContent: [], // 相关文章列表
				articleContent: {},
				commentContent: '',
				commentList: [],
				loadingComments: false,
				submittingComment: false,
				collecting: false, // 用于收藏操作的节流控制				
				userAvatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png', // 默认头像
				showBackToTop: false,
				hasMoreComments: true,
				refreshingComments: false,
				
				// 返回顶部按钮配置
				backToTopBtnList: [
					{
						icon: 'totop-fill',
						iconSize: 60,
						bgColor: '#01BEFF',
						textColor: '#FFF',
						text: ''
					}
				],
				
				// 添加loadingContent状态变量				
				loadingContent: false,
				
				// LoadMore组件的文本配置				
				loadText: {
					loadmore: '点击加载更多',
					loading: '正在加载中..',
					nomore: '没有更多评论了'
				},
				page_no: 1,
				page_size: 20,
			}
		},
		computed: {
			computedMainColor() {
				// 避免每次渲染都访问全局数据
				return getApp().globalData.mainColor || '#007aff';
			}
		},
		onLoad(params) {
			this.searchWhere.id = params.id || ''
			this.searchWhere.type = params.type || 2
			// 获取用户信息
			this.userAvatar = uni.getStorageSync("avatar") || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png'
			this.fetchContent()
		},
		onPageScroll(e) {
			// 监听页面滚动，控制返回顶部按钮显示			
			this.showBackToTop = e.scrollTop > 300;
		},
		methods: {
			// 返回顶部
			backToTop() {
				uni.pageScrollTo({
					scrollTop: 0,
					duration: 300
				});
			},
			
			// 计算子评论高度（用于 ReadMore 组件）
			calculateChildrenHeight(children) {
				// 每个子评论项的预估高度：大约 120rpx
				// 包括：padding(tn-padding-sm) + 内容 + margin + 点赞区域
				const itemHeight = 120; // rpx
				return children.length * itemHeight;
			},
			// 处理悬浮按钮点击事件
			handleFabBtnClick(data) {
				// 点击返回顶部按钮
				if (data && data.index === 0) {
					this.backToTop();
				}
			},
			
			// 刷新页面
			// 修改 onRefresh 方法
			onRefresh() {
			  // 不在这里立即停止下拉刷新，而是在数据加载完成后停止
			  this.fetchContent(true); // 增加参数表示是通过下拉刷新触发			
			  },
			
			// 修改 fetchContent 方法
			fetchContent(isPullRefresh = false) {
			  // 添加加载状态节流控制			  
			  if (this.loadingContent) {
			    return;
			  }
			  
			  this.loadingContent = true;
			  
			  if (!isPullRefresh) {
			    uni.showLoading({
			      title: "努力加载中...",
			      icon: "none"
			    })
			  }
			  
			  this.$api.apiArticleContent(this.searchWhere).then(res => {
			    if (res.code === 1) {
			      this.articleContent = res.data
			      // 文章加载成功后获取评论列表，下拉刷新时传入true重置评论
			      this.getCommentList(isPullRefresh)
			      return
			    }
			    this.$func.showToast(res.msg || '获取文章内容失败')
			  }).catch(error => {
			    console.error('获取文章内容失败:', error)
			    this.$func.showToast('网络请求失败，请稍后重试')
			  }).finally(() => {
			    if (!isPullRefresh) {
			      uni.hideLoading()
			    }
			    // 在请求完成后停止下拉刷新动画
			    if (isPullRefresh) {
			      uni.stopPullDownRefresh();
			    }
			    this.loadingContent = false;
			  })
			},
			
			// 评论加载更多
			onCommentLoadMore() {
			  console.log('触发onCommentLoadMore，loadingComments:', this.loadingComments, 'hasMoreComments:', this.hasMoreComments);
			  if (!this.loadingComments && this.hasMoreComments) {
			    // 立即设置加载状态，防止快速连续触发			    
				this.loadingComments = true;
			    this.getCommentList();
			  }
			},
			
			// 获取评论列表
			getCommentList(refresh = false) {
			  console.log('进入getCommentList方法，refresh:', refresh);
			  
			  // 双重检查加载状态			  
			  if (!refresh && this.loadingComments) {
			    console.log('已有评论加载请求进行中，取消重复请求');
			    return;
			  }
			  
			  // 如果没有更多数据且不是刷新，则不再请求			  
			  if (!this.hasMoreComments && !refresh) {
			    console.log('没有更多评论数据，不请求');
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

			  
			  this.$api.apiCommonCommentList({
			    qid: this.searchWhere.id,
			    type: this.searchWhere.type,
			    page_no: this.page_no++,
			    page_size: this.page_size,
			  }).then(res => {
			    if (res.code === 1) {
			      const newComments = res.data.lists || [];
			      
			      // 刷新时替换数据，加载更多时追加数据			      
				  if (refresh) {
			        this.commentList = newComments;
			      } else {
			        this.commentList = [...this.commentList, ...newComments];
			      }
			      
			      // 判断是否还有更多数据
			      this.hasMoreComments = newComments.length === this.pageSize;
			      

			    } else {
			      if (refresh) {
			        this.commentList = [];
			      }
			      this.$func.showToast(res.msg || '获取评论列表失败');
			    }
			  }).catch(error => {
			    if (refresh) {
			      this.commentList = [];
			    }
			    this.$func.showToast('网络请求失败，请稍后重试');
			  }).finally(() => {
			    this.loadingComments = false;
			    this.refreshingComments = false;
			  })
			},
			
			// 评论刷新
			onCommentRefresh() {
				this.getCommentList(true)
			},
			
			// 返回上一页			
			goBack() {
				uni.navigateBack()
			},
			
			// 跳转
			tl(url) {
				console.log(url, '跳转地址')
				if(url === '') {
					this.$func.showToast('暂未开放')
					return
				}
				this.$func.navigatorTo(url)
			},
			
			// 订阅（需要实现）
			subscribe() {
				if (this.$func.currentPlatform() !== 'wechat_mini') {
					this.$func.showToast('订阅支持微信小程序')
					return
				}
				this.$func.templateSubscribe('article_update', {
					id: this.searchWhere.id,
					type: this.searchWhere.type,
				})
			},
			// 收藏/取消收藏
			handleCollection() {
				if (this.articleContent.collect === false) {
					this.$api.apiArticleAddCollect({
						id: this.searchWhere.id,
					}).then(res => {
						this.$func.showToast(res.msg)
						if (res.code === 1) {
							this.articleContent.collect = true
							this.$func.showToast('收藏成功')
						}
					})
				} else if (this.articleContent.collect === true) {
					this.$api.apiArticleCancelCollect({
						id: this.searchWhere.id,
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
			  console.log('点赞按钮被点击', {commentId, qid, index});
			  
			  try {
			    // 首先检查参数是否有效			    
				if (!qid) {
			      console.error('点赞参数无效:', {commentId, qid});
			      this.$func.showToast('参数错误');
			      return;
			    }
			    
			    // 判断是文章点赞还是评论点赞			    
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
			      // 评论点赞逻辑
			      // 先检查commentList是否存在且有效			      
				  if (!this.commentList || !Array.isArray(this.commentList)) {
			        console.error('评论列表数据无效');
			        this.$func.showToast('数据加载中，请稍后重试');
			        return;
			      }
			      
			      // 判断是否是子评论
			      const isChildComment = typeof index === 'object' && index.parentIndex !== undefined;
			      
			      // 找到要更新的评论
			      let targetComment = null;
			      let parentComment = null;
			      
			      // 检查index是否为undefined
			      if (index === undefined) {
			        console.error('评论索引未提供', {commentId, qid});
			        this.$func.showToast('操作失败，请刷新页面重试');
			        return;
			      }
			      
			      // 尝试获取目标评论
			      if (isChildComment) {
			        // 检查父评论和子评论索引是否有效
			        if (typeof index.parentIndex === 'number' && 
			            index.parentIndex >= 0 && 
			            index.parentIndex < this.commentList.length) {
			          parentComment = this.commentList[index.parentIndex];
			          // 确保父评论存在children数组
			          if (parentComment && 
			              Array.isArray(parentComment.children) && 
			              typeof index.childIndex === 'number' && 
			              index.childIndex >= 0 && 
			              index.childIndex < parentComment.children.length) {
			            targetComment = parentComment.children[index.childIndex];
			          }
			        }
			      } else {
			        // 为主评论添加索引有效性检查			        
					if (typeof index === 'number' && index >= 0 && index < this.commentList.length) {
			          targetComment = this.commentList[index];
			        }
			      }
			      
			      // 在访问user_like前检查targetComment是否存在
			      if (!targetComment) {
			        console.error('未找到目标评论', {isChildComment, index, commentId});
			        this.$func.showToast('评论数据不存在或已更新，请刷新页面重试');
			        return;
			      }
			      
			      console.log('找到目标评论:', targetComment);
			      
			      // 执行点赞或取消点赞操作			      
				  if (targetComment.user_like === false) {
			        this.$api.apiCommonAddLike({
			          comment_id: commentId,
			          type: this.searchWhere.type,
			          qid: qid,
			        }).then(res => {
			          try {
			            if (res.code === 1) {
			              // 更新评论的点赞状态，增加安全检查			              
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
			              // 更新评论的点赞状态，增加安全检查			              
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
			openExternalLink() {
        // 外部链接，使用微信官方提供的API打开公众号文章
					wx.openOfficialAccountArticle({
						url: this.articleContent.external_link,
						success: function(res) {
							console.log('打开外部链接成功', res)
						},
						fail: function(err) {
							console.error('打开外部链接失败', err)
							// 失败时可以给出提示
							uni.showToast({
								title: '打开链接失败',
								icon: 'none'
							})
						}
					})
			},
			// 打开评论弹窗
			openCommentPopup(commentId) {
				//判断是否登录
				this.$api.apiUserInfo().then(res => {
					if (res.code === 1) {
						this.userInfo = res.data
						this.commentId = commentId;
						this.popupshow = true;
						return
					}
					this.$func.showToast(res.msg)
				})
			},
			
			// 提交评论
			submitComment() {
				if(!this.commentContent) {
					this.$func.showToast('请输入评论内容')
					return
				}
				if(this.commentContent.length > 250) {
					this.$func.showToast('评论内容不能超过250字符')
					return
				}
				this.$api.apiCommonAddComment({
					qid: this.searchWhere.id,
					pid: this.commentId,
					type: this.searchWhere.type,
					content: this.commentContent
				}).then(res => {
					if (res.code === 1) {
						this.$func.showToast('评论成功')
						this.commentContent = ''
						this.popupshow = false
						this.getCommentList(true)
						return
					}
					this.$func.showToast(res.msg)
				}).catch(error => {
					console.error('提交评论失败:', error);
					this.$func.showToast('网络请求失败，请稍后重试');
				})
			}
		},
		
		// 分享配置
		onShareAppMessage() {
			return {
				title: this.articleContent.title || '精彩内容分享',
				desc: this.articleContent.abstract || '',
				path: '/subpages/news/content?id=' + this.searchWhere.id,
				imageUrl: this.articleContent.image || ''
			}
		},
		
		onShareTimeline() {
			return {
				title: this.articleContent.title || '精彩内容分享',
				desc: this.articleContent.abstract || '',
				path: '/subpages/news/content?id=' + this.searchWhere.id,
				imageUrl: this.articleContent.image || ''
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";

	.template-news {
		background-color: #fff;
	}

		// 底部操作按钮开始	
	.bottom-menu {
		width: 25%;

		&--icon {
			font-size: 34rpx;
		}

		&--text {
			font-size: 20rpx;
		}
	}

	// 底部操作按钮结束
	/* 标题 start */
	.nav_title {
		-webkit-background-clip: text;

		&--wrap {
			position: relative;
			display: flex;
			font-size: 30rpx;
			align-items: center;
			background-image: url('https://datiqiniu.allpp.cn/static/title00.png');
			background-size: cover;
		}
	}
	/* 标题 end */
	/* 评论项样式*/
	.comment-item {
		position: relative;
		padding-bottom: 20rpx;
		&:not(:last-child) {
			border-bottom: 1px solid #f0f0f0;
		}
	}

  /* 外部链接按钮容器样式 */
  .external-link-container {
    display: flex;
    justify-content: center;
    margin: 40rpx 0;
    padding: 20rpx;
  }

  /* 点赞按钮样式优化 */
  .comment-like-btn {
    cursor: pointer;
    transition: all 0.3s ease;
    
    &.hover-class {
      opacity: 0.7; // 添加点击效果
      transform: scale(0.95);
    }
  }
  
  /* 确保点赞按钮在最上层 */
  .justify-content-item {
    position: relative;
  }
  
  /* 点赞用户列表样式 */
  .like-users-container {
    background-color: #f8f8f8;
    padding: 15rpx 20rpx;
    border-radius: 8rpx;
    line-height: 1.6;
  }
</style>

