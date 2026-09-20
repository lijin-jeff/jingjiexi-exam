<template>
  <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<view class="tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			资源列表
			</text>
		</view>
		</tn-nav-bar>
	</view>
	<view>
	<view class="top-backgroup"></view>
      <!-- 顶部搜索 -->
      <view  class="search-box" :style="{paddingTop: vuex_custom_bar_height+ 10 + 'px', position: 'fixed', zIndex: 2, width: '100%'}">
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
        <view class="content-container">
          <block v-for="(it, index) in resourceList" :key="index">
            <navigator :url="'/subpages/resource/content?uid=' + it.uid" class="article-shadow tn-margin-sm">
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
            </navigator>
          </block>
        </view>
        
        <!-- 资源列表为空提示 -->
        <view
          v-if="resourceList.length === 0"
          :style="{paddingTop: vuex_custom_bar_height * 2 + 'px'}"
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
        
      </scroll-view>
    </view>
    <!-- 选择分类 -->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        value-name="value"
        label-name="label"
        mode="multi-auto"
        :list="resourceCategory"
        @confirm="confirm"
      />
    </view>
  </view>
</template>

<script>
	export default {
		name: 'PageB',
		components: {},
		props: {
			selectedCategoryUid: {
				type: String,
				default: ''
			}
		},
		data() {
			return {
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
		computed: {
			// 使用计算属性实时获取globalData中的配置数据
			mainColor() {
				return getApp().globalData.mainColor || '#007AFF'
			}
		},
		created() {
			this.fetchResourceCategory(this.selectedCategoryUid)
		},
		methods: {
			fetchResourceCategory(exam_category_uid) {
				// 只有当exam_category_uid存在且不为空时才调用API，避免不必要的请求
				if (exam_category_uid) {
					this.$api.apiResourceCategory({exam_category_uid: exam_category_uid}).then(res => {
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
						
					}).catch(err => {
						// 处理API请求失败的情况
						console.error('获取分类失败:', err)
						this.resourceCategory = []
					})
				} else {
					// 如果exam_category_uid为空，清空分类列表
					this.resourceCategory = []
				}
			},
			fetchResourceList(exam_category_uid) {
				// 防止重复请求
				if (this.loading || this.noMoreData) {
				return Promise.resolve()
				}
				
				if (exam_category_uid) {
				// 当传入新的分类ID时，重置分页参数并清空现有列表
				if (this.queryParams.exam_category_uid !== exam_category_uid) {
					this.queryParams.exam_category_uid = exam_category_uid
					this.queryParams.page_no = 1
					this.resourceList = []
					this.noMoreData = false
					this.loadingError = false
				}
				} else {
				// 如果没有传入分类ID，确保page_no至少为1
				if (this.queryParams.page_no < 1) {
					this.queryParams.page_no = 1
				}
				}
				
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
			},
			// 分类确认事件
			confirm(e) {
				if (e.length) this.queryParams.category_uid = e[e.length - 1].value
				this.queryParams.category_parent_uid = e[e.length - 2].value
				this.search()
			},
			getCategory() {
				this.$api.apiResourceCategoryList().then((res) => {
					this.resourceCategory = res.data
				})
			},
			search() {
				this.queryParams.page_no = 1
				this.resourceList = []
				this.noMoreData = false
				this.loadingError = false
				this.fetchResourceList(this.selectedCategoryUid)
			},
			// 点击资源
				tl(url) {
					this.$func.navigatorTo(url)
				},
				// 重试加载
				retryLoad() {
					this.fetchResourceList(this.selectedCategoryUid)
				},
				// 滚动到底部加载更多
				onScrollToLower() {
					// 防抖处理，避免频繁触发
					if (this.debounceTimer) {
						clearTimeout(this.debounceTimer)
					}
					this.debounceTimer = setTimeout(() => {
						this.fetchResourceList(this.selectedCategoryUid)
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
					this.fetchResourceList(this.selectedCategoryUid).finally(() => {
						// 关闭刷新状态
						this.refresherTriggered = false
					})
				}
			},
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
		// top: 0;
		left: 0;
		position: fixed;
		z-index: 2;
		width: 100vw;
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
			// height: 240rpx;
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

	.fs-16 {
		font-size: 30rpx;
	}
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
    // background-attachment:fixed;
    background-position: top;
    border-radius: 10rpx;
  }

  .article-shadow {
	background-color: #fff;
    border-radius: 15rpx;
    box-shadow: 0rpx 0rpx 50rpx 0rpx rgba(0, 0, 0, 0.07);
    display: block;
    text-decoration: none;
    color: inherit;
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

  /* 资源列表容器 */
  .resource-list-container {
    height: calc(100vh - var(--vuex-custom-bar-height, 0px) - 10px);
    width: 100%;
  }

</style>

