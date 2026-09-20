<template>
  <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <view
          slot="back"
          class="tn-custom-nav-bar__back"
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
        <view class="tn-flex tn-flex-col-center tn-flex-row-center">
          <text class="tn-text-bold tn-text-xl tn-color-white">
            信息资讯
          </text>
        </view>
      </tn-nav-bar>
    </view>
    <view >
      <!-- 顶部搜索 -->
      <view  class="search-box" :style="{paddingTop: vuex_custom_bar_height+ 10 + 'px', position: 'fixed', zIndex: 2, width: '100%'}">
        <text
          class="tn-icon-menu-classify fs-22"
          style="color: #8b9aae;"
          @click="showCategoryMenu = true"
        />
        <view class="acea-row row-middle relative search-item">
          <input
            v-model="queryParams.keyword"
            type="text"
            placeholder-class="plaClass"
            placeholder="搜索点什么呢"
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
      
      <!-- 资讯列表 -->
      <scroll-view
        class="article-list-container"
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
          <block v-for="(item, index) in newsList" :key="index">
            <view class="article-shadow tn-margin-sm" @click="handleArticleClick(item)">
              <view class="tn-flex">
                <view class="tn-margin-sm" style="width: 100%;">
                  <view class="tn-text-md tn-text-bold clamp-text-2 tn-text-justify" style="min-height: 80rpx;">
                    <view v-for="(label_item,label_index) in item.label" :key="label_index" style="transform: translate(0,-5rpx);"
                      class="justify-content-item tn-tag-content__item tn-margin-right-xs tn-round tn-text-sm tn-text-bold" :class="[`tn-bg-${item.color}--light tn-color-${item.color}`]">
                      <text class="tn-tag-content__item--prefix">#</text> {{ label_item }}
                    </view>
                    <text class="">{{ item.title }}</text>
                  </view>
                  <view class="tn-padding-top-xs">
                    <text class=" tn-text-sm tn-color-gray clamp-text-1">
                      {{ item.desc }}
                    </text>
                  </view>
                  <view class="tn-flex tn-flex-row-between tn-flex-col-between tn-text-sm">
                    <view class="justify-content-item tn-color-gray tn-text-center" style="padding-top: 15rpx;">
                      <text class="tn-icon-footprint tn-padding-right-xs"></text>
                      <text class="tn-padding-right">{{ item.author }}</text>
                      <text class="tn-icon-like-lack tn-padding-right-xs"></text>
                      <text class="">{{ item.click }}</text>
                    </view>
                  </view>
                </view>
                <view class="image-pic tn-margin-sm" :style="'background-image:url(' + item.image + ')'">
                  <view class="image-article">
                  </view>
                </view>
              </view>
            </view>
          </block>
        </view>

        <!-- 资源列表为空开始-->
        <view
          v-if="newsList.length === 0"
          :style="{paddingTop: vuex_custom_bar_height * 2 + 'px'}"
        >
          <tn-empty
            mode="list"
            text="暂无图文数据"
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
    </view>
    <!-- 选择器-->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        value-name="id"
        label-name="name"
        mode="multi-auto"
        :list="categoryList"
        @confirm="categoryConfirm"
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
					keyword: '',
					cid: '',
          exam_category_uid: '',
				},
				showCategoryMenu: false,
				newsList: [],
				categoryList: [],
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
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: true,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.queryParams.exam_category_uid = option.exam_category_uid || ''
		  this.fetchNewsList()
			this.fetchCategoryList()
    },
		created() {
			
		},
		methods: {
			categoryConfirm(e) {
				this.queryParams.cid = e[0].value
				this.queryParams.page_no = 1
				this.newsList = []
				this.fetchNewsList()
			},
			search() {
				this.queryParams.page_no = 1
				this.newsList = []
				this.noMoreData = false
				this.loadingError = false
				this.fetchNewsList()
			},
			// 滚动到底部加载更多
			onScrollToLower() {
				// 防抖处理，避免频繁触发
				if (this.debounceTimer) {
					clearTimeout(this.debounceTimer)
				}
				this.debounceTimer = setTimeout(() => {
					this.fetchNewsList()
				}, 300)
			},
			// 下拉刷新
			onRefresherRefresh() {
				// 设置刷新状态
				this.refresherTriggered = true
				
				// 重置分页参数和数据
				this.queryParams.page_no = 1
				this.newsList = []
				this.noMoreData = false
				this.loadingError = false
				
				// 重新加载数据
				this.fetchNewsList().finally(() => {
					// 关闭刷新状态
					this.refresherTriggered = false
				})
			},
			// 重试加载
			retryLoad() {
				this.fetchNewsList()
			},
			fetchNewsList() {
        // 防止重复请求
        if (this.loading || this.noMoreData) {
          return Promise.resolve()
        }
        
        // 设置加载状态
        this.loading = true
        this.loadingError = false
        
        return new Promise((resolve, reject) => {
          this.$api.apiArticleList(this.queryParams).then(res => {
            // 加载成功
            this.loading = false
            this.loadingError = false
            
            const newData = res.data.lists || []
            if (newData.length > 0) {
              this.newsList.push(...newData)
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
            console.error('获取资讯列表失败:', err)
            this.loading = false
            this.loadingError = true
            reject(err)
          })
        })
      },
			fetchCategoryList() {
				// 只有当exam_category_uid存在且不为空时才调用API，避免不必要的请求
				if (this.queryParams.exam_category_uid) {
					this.$api.apiArticleCateList({exam_category_uid: this.queryParams.exam_category_uid}).then(res => {
						// 递归处理API返回的数据，确保每个分类项都有children属性
						const processCategory = (items) => {
							return (items || []).map(item => ({
								...item
							}))
						}
						
						const processedData = processCategory(res.data)
						
						// 在分类列表开头添加全部分类选项
						this.categoryList = [
							{
								id: '',
								name: '全部'
							},
							...processedData
						]
					})
				} else {
					// 如果exam_category_uid为空，清空分类列表
					this.categoryList = []
				}
			},
			tl(url) {
				this.$func.navigatorTo(url)
			},
			handleArticleClick(item) {
				// 判断是否是外部链接
				if (item.is_external_link === 1 && item.external_link) {
					// 外部链接，使用微信官方提供的API打开公众号文章
					wx.openOfficialAccountArticle({
						url: item.external_link,
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
				} else {
					// 内部文章，跳转到详情页
					this.tl('/subpages/news/content?id=' + item.id)
				}
			}
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
/* 资讯主图 start*/
  .image-article {
    border-radius: 8rpx;
    border: 1rpx solid #F8F7F8;
    width: 250rpx;
    height: 200rpx;
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
  }

  /* 文字截取*/
  .clamp-text-1 {
    -webkit-line-clamp: 2;
    line-clamp: 2;
    
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

  /* 标签内容 start*/
  .tn-tag-content {
    &__item {
      display: inline-block;
      line-height: 35rpx;
      padding: 5rpx 25rpx;

      &--prefix {
        padding-right: 10rpx;
      }
    }
  }

  /* 标签内容 end*/
	.fs-16 {
		font-size: 30rpx;
	}

  /* 资讯列表容器 */
  .article-list-container {
    height: calc(100vh - var(--vuex-custom-bar-height, 0px));
    width: 100%;
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

