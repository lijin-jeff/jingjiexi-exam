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
			在线搜索
			</text>
		</view>
		</tn-nav-bar>
	</view>	
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 顶部搜索 -->
      <view
        class="search-box"
        :style="{top: vuex_custom_bar_height + 'px'}"
      >
        <text
          class="tn-icon-menu fs-22"
          style="color: #8b9aae;"
          @click="showCategoryMenu = true"
        />
        <view class="acea-row row-middle relative search-item">
          <input
            v-model="inputValue"
            type="text"
            placeholder-class="plaClass"
            placeholder="请输你需要的内容"
            class="input"
            confirm-type="search"
            @confirm="search"
            @input="handleInput"
            @focus="showSuggestions = !!inputValue.trim()"
          >
          <view
            class="search-btn absolute"
            @click="search"
          >
            搜索
          </view>
          
          <!-- 搜索建议列表 -->
          <view 
            v-if="showSuggestions" 
            class="search-suggestions"
            @click.stop
          >
            <view v-if="isLoadingSuggestions" class="suggestion-loading">
              <text class="tn-icon-loading"></text>
              <text>加载中...</text>
            </view>
            <view 
              v-for="(suggestion, index) in suggestions" 
              :key="index"
              class="suggestion-item"
              @click="selectSuggestion(suggestion)"
            >
              <text class="tn-icon-search"></text>
              <text class="suggestion-text">{{ suggestion.label }}</text>
            </view>
          </view>
        </view>
      </view>
      <view class="exam-ul">
        <block
          v-for="(it, index) in searchDataList"
          :key="index"
        >
          <view
            class="tn-flex exam-li mt-10"
            @click="tl(it)"
          >
            <view class="txt tn-flex tn-flex-direction-column">
              <view class="fs-16 tn-text-ellipsis-2 news-title">
                {{ it.title }}
              </view>
              <view class="acea-row row-middle tn-color-gray">
                <view>
                  <text class="tn-icon-bookmark" />
                  <text class="ml-5">
                    {{ it.data_type_title }}
                  </text>
                </view>
                <view class="tn-margin-left-sm">
                  <text class="tn-icon-time" />
                  <text class="ml-5">
                    {{ it.publish_time }}
                  </text>
                </view>
              </view>
            </view>
            <view
              class=""
              style="height: 100%;"
            >
              <view style="height: 100%;">
                <image
                  :src="getImageUrl(it.image)"
                  class="pic"
                />
              </view>
            </view>
          </view>
        </block>
      </view>
      <!-- 资源列表为空提示 -->
      <view
        v-if="searchDataList.length === 0"
        :style="{paddingTop: vuex_custom_bar_height * 2 + 'px'}"
      >
        <tn-empty
          mode="list"
          text="暂无数据"
        />
      </view>
      <!-- 资源列表为空结束 -->
      <view class="tn-tabbar-height" />
    </view>
    <!-- 选择分类 -->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        value-name="value"
        label-name="label"
        :list="category"
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
				queryParams: {
					page_no: 1,
					page_size: 20,
					title: '',
						data_type: ''
					},
					category: [{
						label: '资料文件',
						value: 'resource'
					},{  
						label: '资讯文章',
						value: 'article'
					},{  
						label: '在线题库',
						value: 'exam_library'
					}],
					showCategoryMenu: false,
					searchDataList: [],
					// 搜索建议相关数据
					suggestions: [],
					showSuggestions: false,
					isLoadingSuggestions: false,
					inputValue: '',
					suggestionTimer: null,
				}
			},
			created() {
				// 初始化防抖函数
				this.debouncedGetSuggestions = this.debounce(this.getSearchSuggestions, 300)
			},
		computed: {
			// 使用计算属性实时获取globalData中的配置数据
			mainColor() {
				return getApp().globalData.mainColor || '#007AFF'
			}
		},
		async onLoad(option) {
			// 确保配置已经加载完成，如果没有则手动触发并等待完成
			if (!getApp().globalData.otherSettings || Object.keys(getApp().globalData.otherSettings).length === 0) {
				console.log('search.vue中检测到配置未加载，手动触发并等待完成')
				if (getApp().globalData.fetchOtherSettings) {
					await getApp().globalData.fetchOtherSettings()
					console.log('手动触发后globalData状态:', getApp().globalData)
					
					// 强制更新视图，确保计算属性重新计算
					this.$forceUpdate()
				} else {
					console.error('fetchOtherSettings方法未找到')
				}
			}
			
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.fetchDataSearch()
		},
			onMounted() {
				// 添加点击页面其他区域关闭搜索建议的事件监听
				document.addEventListener('click', this.handlePageClick)
			},
			onUnmounted() {
				// 移除事件监听
				document.removeEventListener('click', this.handlePageClick)
			},
		methods: {
			categoryConfirm(event) {
				if (event.length > 0) {
					this.queryParams.data_type = event[0].value
				}
				this.fetchDataSearch()
				// 关闭建议列表
				this.showSuggestions = false
			},
			search() {
				this.queryParams.page_no = 1
				this.searchDataList = []
				this.fetchDataSearch()
				// 关闭建议列表
				this.showSuggestions = false
			},
			fetchDataSearch() {
				uni.showLoading({
					title: '努力加载中'
				})
				this.$api.apiDataSearch(this.queryParams).then(res => {
					if (res.data && Array.isArray(res.data.lists)) {
					      this.searchDataList.push(...res.data.lists)
					    }
					this.queryParams.page_no += 1
					uni.hideLoading()
				})
			},
			tl(row) {
				if (row.data_type === 'article') {
					this.$func.navigatorTo('/subpages/news/content?id=' + row.uid)
				} else if (row.data_type === 'resource') {
					this.$func.navigatorTo('/subpages/resource/content?uid=' + row.uid)
				} else if (row.data_type === 'exam_library') {
					this.$func.navigatorTo('/subpages/exam/questionContent?uid=' + row.uid)
				}
			},
			// 防抖函数
			debounce(func, delay) {
				let timer = null
				return function() {
					const context = this
					const args = arguments
					clearTimeout(timer)
					timer = setTimeout(() => {
						func.apply(context, args)
					}, delay)
				}
			},
			// 获取搜索建议
			getSearchSuggestions(title) {
				if (!title || title.trim().length < 1) {
					this.suggestions = []
					this.showSuggestions = false
					return
				}
				
				this.isLoadingSuggestions = true
				// 这里应该调用真实的搜索建议API，暂时使用模拟数据
				// this.$api.apiSearchSuggestions({title: title}).then(res => {
				//     this.suggestions = res.data || []
				//     this.isLoadingSuggestions = false
				//     this.showSuggestions = this.suggestions.length > 0
				// })
				
				// 模拟搜索建议数据
				setTimeout(() => {
					// 模拟不同类型的搜索建议
					const mockSuggestions = [
						{ label: `'${title}' 相关资料`, value: title, type: 'resource' },
						{ label: `'${title}' 相关文章`, value: title, type: 'article' },
						{ label: `'${title}' 相关题库`, value: title, type: 'exam_library' },
					]
					
					this.suggestions = mockSuggestions
					this.isLoadingSuggestions = false
					this.showSuggestions = this.suggestions.length > 0
				}, 300)
			},
			// 输入事件处理
			handleInput(e) {
				const value = e.detail.value
				this.queryParams.title = value
				
				// 调用防抖处理的获取搜索建议方法
				this.debouncedGetSuggestions(value)
				
				// 当搜索框清空时，清空搜索结果
				if (!value.trim()) {
					this.searchDataList = []
					this.queryParams.data_type = ''
					this.queryParams.page_no = 1
					this.fetchDataSearch()
				}
			},
			// 选择搜索建议
			selectSuggestion(suggestion) {
				// 填充搜索框
				this.inputValue = suggestion.value
				this.queryParams.title = suggestion.value
				// 如果有类型，设置类型
				if (suggestion.type) {
					this.queryParams.data_type = suggestion.type
				}
				// 执行搜索
				this.search()
			},
			// 关闭搜索建议
			closeSuggestions() {
				this.showSuggestions = false
			},
			// 点击页面其他区域关闭搜索建议
			handlePageClick() {
				this.showSuggestions = false
			},
			// 处理图片URL，确保正确的域名
			getImageUrl(image) {
				// 如果图片URL已经有完整的http/https协议，直接返回
				if (image && (image.startsWith('http://') || image.startsWith('https://'))) {
					return image
				}
				// 如果图片URL为空，返回默认图片
				if (!image) {
					return 'https://datiqiniu.allpp.cn/static/jingjiexi.png'
				}
				// 从全局配置获取域名
				const domain = getApp().globalData.domain || ''
				// 确保域名格式正确，没有尾部斜杠
				const cleanDomain = domain.replace(/\/$/, '')
				// 确保图片路径有头部斜杠
				const cleanImage = image.startsWith('/') ? image : '/' + image
				// 拼接完整URL
				return `${cleanDomain}${cleanImage}`
			}
		},
		onReachBottom() {
			this.fetchDataSearch()
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

	.fs-16 {
		font-size: 30rpx;
	}
	
	/* 搜索建议样式 */
		.search-item {
			/* 确保搜索项容器是相对定位的，以便建议列表绝对定位 */
			position: relative;
			width: 100%;
		}
		
		.search-suggestions {
			position: absolute;
			top: calc(100% + 2rpx);
			left: 10rpx;
			right: 10rpx;
			z-index: 1000;
			background-color: #fff;
			border-radius: 0 0 10rpx 10rpx;
			box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.15);
			max-height: 400rpx;
			overflow-y: auto;
			-webkit-overflow-scrolling: touch;
			animation: slideDown 0.2s ease-out;
			box-sizing: border-box;
		}
	
	/* 下滑动画 */
	@keyframes slideDown {
		from {
			opacity: 0;
			transform: translateY(-10rpx);
		}
		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
	
	.suggestion-item {
		display: flex;
		align-items: center;
		padding: 20rpx;
		cursor: pointer;
		transition: background-color 0.2s;
	}
	
	.suggestion-item.hover-class {
		background-color: #e6ebf5;
	}
	
	.suggestion-item .tn-icon-search {
		margin-right: 12rpx;
		color: #909399;
		font-size: 26rpx;
	}
	
	.suggestion-text {
		font-size: 30rpx;
		color: #303133;
	}
	
	.suggestion-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 20rpx;
		color: #909399;
		font-size: 28rpx;
	}
	
	.suggestion-loading .tn-icon-loading {
		margin-right: 12rpx;
		font-size: 28rpx;
		animation: spin 1s linear infinite;
	}
	
	/* 旋转动画 */
	@keyframes spin {
		from {
			transform: rotate(0deg);
		}
		to {
			transform: rotate(360deg);
		}
	}
</style>

