<template>
  <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view
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
		</template>
		<view class="tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			历年真题
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
            v-model="queryParams.keywords"
            type="text"
            placeholder-class="tn-icon-search plaClass"
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
      <view class="exam-ul">
        <block
          v-for="(it, index) in newsList"
          :key="index"
        >
          <view
            class="tn-flex exam-li mt-10"
            @click="tl('/subpages/exam/questionOrder?uid=' + it.uid)"
          >
            <view class="txt tn-flex tn-flex-direction-column">
              <view class="fs-16 tn-text-ellipsis-2 news-title">
                {{ it.title }}
              </view>
              <view class="acea-row row-middle tn-color-gray">
                <view>
                  <text class="tn-icon-bookmark" />
                  <text class="ml-5">
                    {{ it.category.title }}
                  </text>
                </view>
                <view class="tn-margin-left-sm">
                  <text class="tn-icon-time" />
                  <text class="ml-5">
                    {{ it.publish_date }}
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
                  :src="it.cover"
                  class="pic"
                />
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
          text="暂无资源"
        />
      </view>
      <!-- 资源列表为空结束 -->
      <view class="tn-tabbar-height" />
    </view>
    <!-- 选择项-->
    <view class="">
      <tn-select
        v-model="showCategoryMenu"
        :confirm-color="mainColor"
        value-name="uid"
        label-name="title"
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
					page: 1,
					size: 20,
					keywords: '',
					category_uid: '',
				},
				showCategoryMenu: false,
				newsList: [{
					uid: '',
					title: '2021湖南高考语文真题涵盖试题解，助力你备考',
					cover: 'http://cdn.image.tutudati.com/uploads/images/20241004/2024100409415606b318141.jpg',
					category: {
						uid: '',
						title: '科技',
					},
					publish_date: '2025-04-03'
				}],
				resourceCategory: [],
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
			this.queryParams.category_uid = option.category_uid || ''
			// this.getCategory()
			// this.fetchData()
		},
		created() {
			this.demoData()
		},
		methods: {
			demoData() {
				for (let index = 0; index < 10; index++) {
					this.newsList.push({
						uid: '',
						title: '最强推理AI模型Deepseek问世，杭州深度求索科技股票大涨，杭州深度求索科技股票大涨',
						cover: 'http://cdn.image.tutudati.com/uploads/images/20241004/2024100409415606b318141.jpg',
						category: {
							uid: '',
							title: '科技',
						},
						publish_date: '2025-04-03'
					})
				}
			},
			// 回调参数为包含多个元素的数组，每个元素分别反应每一列的选择情况
			confirm(e) {
				if (e.length) this.queryParams.category_uid = e[e.length - 1].value
				this.search()
			},
			onSelected(res) {
				this.queryParams.free_state = res[0][0].value
				this.queryParams.source_type = res[1][0].value
				this.queryParams.year = res[2][0].value
				this.search()
			},
			// 重置查询条件
			resetQuery() {
				this.queryParams = {
					page: 1,
					size: 20,
					keywords: '',
					category_uid: ''
				}
				this.search()
			},
			dateChange(d) {
				uni.showToast({
					icon: 'none',
					title: d
				})
			},
			// 获取资源分类
			getCategory() {
				this.$api.apiResourceCategoryList().then((res) => {
					this.resourceCategory = res.data
				})
			},
			// 获取资源数据
			fetchData() {
				uni.showLoading({
					title: '努力加载中...',
					icon: 'none'
				})
				this.$api.apiResourceList(this.queryParams).then((res) => {
					uni.hideLoading()
					if (res.code != 100) {
						this.$func.showToast(res.msg)
						return
					}
					if (res.data.items.length === 0) {
						this.$func.showToast('暂无更多数据')
						return
					}
					this.resourceList.push(...res.data.items)
					this.queryParams.page += 1
				})
			},
			// 搜索资源
			search() {
				this.queryParams.page = 1
				this.resourceList = []
				this.fetchData()
			},
			tl(url) {
				this.$func.navigatorTo(url)
			}
		},
		onReachBottom() {
			this.fetchData()
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
</style>

