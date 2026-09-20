<template>
  <view class="page tn-safe-area-inset-bottom">
	<view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view class="tn-custom-nav-bar__back">
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
			资源列表
			</text>
		</view>
		</tn-nav-bar>
	</view>
    <!-- 顶部搜索 -->
    <view
      class="search-box"
      :style="{paddingTop: vuex_custom_bar_height+ 10 + 'px', position: 'fixed', zIndex: 2, width: '100%'}"
    >
      <text
        class="tn-icon-menu-classify fs-22"
        style="color: #8b9aae;"
        @click="showCategoryMenu = true"
      />
      <view class="acea-row row-middle relative search-item">
        <input
          v-model="queryParams.keywords"
          type="text"
          placeholder-class="plaClass"
          placeholder="搜索资源"
          class="input"
          confirm-type="search"
          @confirm="search"
        >
        <view
          class="search-btn absolute"
          @click="search"
        >
          搜索
        </view>
      </view>
    </view>
    <!-- 资源列表 -->
    <scroll-view
      class="resource-list-container"
	  :style="{paddingTop: vuex_custom_bar_height+ 40 + 'px'}"
      scroll-y
      @scrolltolower="onScrollToLower"
      @refresherrefresh="onRefresherRefresh"
      :refresher-enabled="true"
      :refresher-threshold="80"
      :refresher-triggered="refresherTriggered"
      refresher-default-style="black"
    >
      <view class="content-container" >
        <block v-for="(it, index) in resourceList" :key="index">
          <view class="article-shadow tn-margin-sm"  @click="tl('/subpages/resource/content?uid=' + it.uid)">
            <view class="tn-flex">
              <view class="image-pic tn-margin-sm" :style="'background-image:url(' + it.image + ')'">
                <view class="image-article">
                </view>
              </view>
              <view class="tn-margin-sm tn-padding-top-xs" style="width: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                <view>
                  <view class="tn-text-bold clamp-text-2">
                    [{{ it.category ? it.category.title : '未分类' }}]{{ it.title }}
                  </view>
                </view>
            <view class="justify-content-item tn-color-gray--dark">
              <text class="tn-icon-eye tn-padding-right-xs"></text>
              <text class="tn-padding-right">{{ it.view_count }}</text>
              <text class="tn-icon-download tn-padding-right-xs"></text>
              <text class="tn-padding-right">{{ it.download_count }}</text>
                <text class="tn-icon-calendar tn-padding-right-xs" />
              <text>{{ it.year + `年` }}</text>
            </view>
              </view>
            </view>
          </view>
        </block>
      </view>
      
      <!-- 资源列表为空开始-->
      <view
        v-if="resourceList.length === 0"
        class="empty-container"
      >
        <tn-empty
          mode="list"
          text="暂无资源"
        />
      </view>
      <!-- 资源列表为空结束 -->
      
      <!-- 加载状态显示 -->
      <view class="loading-status">
        <!-- 加载中 -->
        <view v-if="loading" class="loading-item">
          <tn-loading size="small" />
          <text class="loading-text">加载中...</text>
        </view>
        
        <!-- 加载失败 -->
        <view v-else-if="loadingError" class="loading-item error-item" @click="retryLoad">
          <tn-color-icon name="warning-circle" size="20" color="#ff6b6b" />
          <text class="error-text">加载失败，点击重试</text>
        </view>
        
        <!-- 无更多数据 -->
        <view v-else-if="noMoreData" class="loading-item no-more-item">
          <text class="no-more-text">——没有更多数据了——</text>
        </view>
      </view>
      
      <view class="tn-tabbar-height" />
    </scroll-view>
    <!-- 选择器-->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        value-name="value"
        mode="multi-auto"
        :list="resourceCategory"
        @confirm="confirm"
      />
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	export default {
		name: 'PageB',
		components: {},
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				queryParams: {
					page_no: 1,
					page_size: 20,
					keywords: '',
          category_parent_uid: '',
					category_uid: '',
          exam_category_uid: '',
				},
				showCategoryMenu: false,
				resourceList: [],
				resourceCategory: [],
				// 加载状态管理
				loading: false,
				loadingError: false,
				noMoreData: false,
				// 下拉刷新状态
				refresherTriggered: false,
				// 防抖计时器
				debounceTimer: null,
			}
		},
		onLoad(option) {
			// 设置exam_category_uid，兼容所有环境
      		this.queryParams.exam_category_uid = option.exam_category_uid || ''
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: true,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			// 在onLoad中调用数据获取方法，确保exam_category_uid已被设置
			this.fetchResourceList()
			this.fetchResourceCategory()
		},
		created() {
			// 数据获取移至onLoad，确保参数已初始化
		},
		methods: {
			fetchResourceCategory() {
				// 只有当exam_category_uid存在时才调用API
				if (this.queryParams.exam_category_uid) {
					this.$api.apiResourceCategory({exam_category_uid: this.queryParams.exam_category_uid}).then(res => {
						// 确保res.data是数组
						const categoryData = Array.isArray(res.data) ? res.data : []
						
						// 递归处理API返回的数据，确保每个分类项都有children属性
						const processCategory = (items) => {
							if (!Array.isArray(items)) {
								return []
							}
							return items.map(item => {
								// 确保item是对象
								if (!item || typeof item !== 'object') {
									return {
										label: '',
										value: '',
										children: [{
											label: '',
											value: '',
										}]
									}
								}
								// 处理子分类，确保每个一级分类都有"全部子分类"选项
								const processedChildren = processCategory(item.children)
								// 在每个一级分类的子分类列表开头添加"全部子分类"选项
								const childrenWithAllOption = [
									{
										label: '全部子分类',
										value: '',
									},
									...processedChildren
								]
								return {
									...item,
									children: childrenWithAllOption
								}
							})
						}
						
						const processedData = processCategory(categoryData)
						
						// 在分类列表开头添加全部分类选项
						this.resourceCategory = [
							{
								label: '全部分类',
								value: '',
								children: [{
										label: '全部子分类',
										value: '',
								}]
							},
							...processedData
						]
					})
				} else {
					// 如果exam_category_uid为空，清空分类列表
					this.resourceCategory = []
				}
			},
			fetchResourceList() {
        // 防止重复请求
        if (this.loading || this.noMoreData) {
          return Promise.resolve()
        }
        
        // 只有当exam_category_uid存在时才调用API
        if (this.queryParams.exam_category_uid) {
          // 设置加载状态
          this.loading = true
          this.loadingError = false
          
          return new Promise((resolve, reject) => {
            this.$api.apiResourceList(this.queryParams).then(res => {
              // 加载成功
              this.loading = false
              this.loadingError = false
              
              const newData = res.data.lists || []
              if (newData.length > 0) {
                this.resourceList.push(...newData)
                // 当返回的数据量小于 page_size 时，表示没有更多数据了
                if (newData.length < this.queryParams.page_size) {
                  this.noMoreData = true
                } else {
                  this.queryParams.page_no += 1
                }
              } else {
                // 没有更多数据
                this.noMoreData = true
              }
              resolve(res)
            }).catch(err => {
              // 加载失败
              console.error('获取资源列表失败:', err)
              this.loading = false
              this.loadingError = true
              reject(err)
            })
          })
        } else {
          // 如果exam_category_uid为空，清空资源列表
          this.resourceList = []
          this.queryParams.page_no = 1
          return Promise.resolve()
        }
      },
			// 分类确认事件
			confirm(e) {
				if (e.length) this.queryParams.category_uid = e[e.length - 1].value
        if (e.length > 1) this.queryParams.category_parent_uid = e[e.length - 2].value
				this.search()
			},
			
			search() {
				this.queryParams.page_no = 1
				this.resourceList = []
				this.noMoreData = false
				this.loadingError = false
				this.fetchResourceList()
			},
			// 滚动到底部加载更多
			onScrollToLower() {
				// 防抖处理，避免频繁触发
				if (this.debounceTimer) {
					clearTimeout(this.debounceTimer)
				}
				this.debounceTimer = setTimeout(() => {
					this.fetchResourceList()
				}, 300)
			},
			// 下拉刷新
			onRefresherRefresh() {
				// 设置刷新状态
				this.refresherTriggered = true
				
				// 重置分页参数和数据
				this.queryParams.page_no = 1
				this.resourceList = []
				this.noMoreData = false
				this.loadingError = false
				
				// 重新加载数据
				this.fetchResourceList().finally(() => {
					// 关闭刷新状态
					this.refresherTriggered = false
				})
			},
			// 重试加载
			retryLoad() {
				this.fetchResourceList()
			},
			// 点击资源
			tl(url) {
				this.$func.navigatorTo(url)
			}
		},
		onReachBottom() {
			this.fetchResourceList()
		}
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	::v-deep .plaClass {
		color: #999;
		text-align: start;
	}

	.news-title {
		letter-spacing: 1rpx;
		font-size: 30rpx;
	}

	.search-box {
		left: 0;
		background: #fff;
		padding: 10rpx;
		display: flex;
		align-items: center;

		.search-item {
			width: 100%;

			.input {
				width: 100% !important;
				padding-left: 30rpx;
				padding-right: 150rpx;
				margin-left: 10rpx;
				height: 60rpx;
				background: #f4f4f4;
				border-radius: 40rpx;
			}

			.search-btn {
				width: 100rpx;
				height: 50rpx;
				line-height: 50rpx;
				text-align: center;
				right: 10rpx;
				font-size: 12px;
				background: $view-theme;
				color: #fff;
				border-radius: 50rpx;
			}
		}

	}

	.exam-ul {
		padding-top: 70rpx;
		width: 98%;
		margin-left: 1%;

		.exam-li {
			background: #fff;
			border-radius: 10rpx;
			padding: 20rpx;
			position: relative;

			.txt {
				width: calc(100% - 200rpx);
				justify-content: space-between;
			}

			.pic {
				width: 200rpx;
				height: 150rpx;
				border-radius: 10rpx;
			}
		}

	}

  /*资讯列表 start*/
  /* 资讯主图 start*/
  .image-article {
    border-radius: 8rpx;
    border: 1rpx solid #F8F7F8;
    width: 150rpx;
    height: 150rpx;
    position: relative;
  }

  .image-pic {
    background-size: cover;
    background-repeat: no-repeat;
    background-position: top;
    border-radius: 10rpx;
  }

  .article-shadow {
	background-color: #fff;
    border-radius: 15rpx;
    box-shadow: 0rpx 0rpx 50rpx 0rpx rgba(0, 0, 0, 0.07);
  }

  /* 文字截取*/
  .clamp-text-1 {
    -webkit-line-clamp: 1;
    line-clamp: 1;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .clamp-text-2 {
    -webkit-line-clamp: 2;
    line-clamp: 2;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    text-overflow: ellipsis;
    overflow: hidden;
  }

	.fs-16 {
		font-size: 30rpx;
	}

  /* 页面容器 */
  .page {
    height: 100vh;
    width: 100vw;
    overflow: hidden;
    position: relative;
  }

  /* 资源列表容器 */
  .resource-list-container {
    height: 100vh;
    width: 100%;
    position: relative;
  }

  /* 空状态容器 */
  .empty-container {
    padding-top: 30vh;
    padding-bottom: 30vh;
  }

  /* 加载状态样式 */
  .loading-status {
    padding: 20rpx 0;
    width: 100%;
  }

  .loading-item {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20rpx 0;
    font-size: 24rpx;
    color: #999;
  }

  .loading-text {
    margin-left: 10rpx;
  }

  .error-item {
    color: #ff6b6b;
    cursor: pointer;
  }

  .error-icon {
    margin-right: 10rpx;
  }

  .no-more-item {
    color: #ccc;
  }

</style>

