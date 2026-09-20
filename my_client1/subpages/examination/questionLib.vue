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
			题库列表
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
            v-model="queryParams.title"
            type="text"
            placeholder-class="plaClass"
            placeholder="搜索题库"
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
      <view class="exam-ul">
        <block
          v-for="(it, index) in questionList"
          :key="index"
        >
          <view
            class="tn-flex exam-li mt-10"
            @click="tl('/subpages/exam/questionContent?uid=' + it.uid)"
          >
            <view class="txt tn-flex tn-flex-direction-column">
              <view class="fs-16 tn-text-ellipsis-2 news-title">
                {{ it.title }}
              </view>
              <view class="acea-row row-middle tn-color-gray">
                <view>
                  <text class="tn-icon-bookmark" />
                  <text class="ml-5">
                    {{ it.cate_name }}
                  </text>
                </view>
                <view class="tn-margin-left-sm">
                  <text class="tn-icon-time" />
                  <text class="ml-5">
                    {{ it.create_time }}
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
                  :src="it.image"
                  class="pic"
                />
              </view>
            </view>
          </view>
        </block>
      </view>
      <!-- 资源列表为空开始-->
      <view
        v-if="questionList.length === 0 && !loading"
        :style="{paddingTop: vuex_custom_bar_height * 2 + 'px'}"
      >
        <tn-empty
          mode="list"
          text="暂无题库"
        />
      </view>
      <!-- 资源列表为空结束 -->
      
      <!-- 加载更多提示 -->
      <view
        v-if="questionList.length > 0"
        class="load-more"
      >
        <text
          v-if="loading"
          class="tn-color-gray"
        >
          加载中...
        </text>
        <text
          v-else-if="!hasMore"
          class="tn-color-gray"
        >
          没有更多了
        </text>
        <text
          v-else
          class="tn-color-gray"
        >
          上拉加载更多
        </text>
      </view>
      
      <view class="tn-tabbar-height" />
    </view>
    <!-- 选择项-->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        value-name="value"
        label-name="label"
        mode="multi-auto"
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
				mainColor: getApp().globalData.mainColor,
				queryParams: {
					page_no: 1,
					page_size: 20,
					title: '',
					cate_uid: '',
				},
				showCategoryMenu: false,
				questionList: [],
				category: [],
				// 分页状态
				loading: false, // 加载中
				hasMore: true, // 是否还有更多数据
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
			this.fetchtCategory()
			this.fetchQuestionLibList()
		},
		methods: {
			search() {
				this.queryParams.page_no = 1
				this.questionList = []
				this.hasMore = true
				this.fetchQuestionLibList()
			},
			categoryConfirm(e) {
				if (e.length) this.queryParams.cate_uid = e[e.length - 1].value
				this.search()
			},
			fetchtCategory() {
				this.$api.apiQuestionCategoryTree().then((res) => {
					this.category = res.data
				})
			},
			fetchQuestionLibList() {
				// 防止重复加载
				if (this.loading || !this.hasMore) {
					return
				}
				
				this.loading = true
				uni.showLoading({
					title: '加载中...',
					mask: true
				})
				
				this.$api.apiQuestionLib(this.queryParams).then(res => {
					const newData = res.data.lists || []
					this.questionList.push(...newData)
					
					// 判断是否还有更多数据
					if (newData.length < this.queryParams.page_size) {
						this.hasMore = false
					} else {
						this.queryParams.page_no += 1
					}
				}).catch(err => {
					console.error('加载题库失败:', err)
					uni.showToast({
						title: '加载失败',
						icon: 'none'
					})
				}).finally(() => {
					this.loading = false
					uni.hideLoading()
				})
			},
			tl(url) {
				this.$func.navigatorTo(url)
			}
		},
		onReachBottom() {
			this.fetchQuestionLibList()
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
	
	.load-more {
		text-align: center;
		padding: 30rpx 0;
		font-size: 26rpx;
	}</style>

