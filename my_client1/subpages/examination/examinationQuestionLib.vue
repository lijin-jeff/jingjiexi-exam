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
            在线考试
          </text>
        </view>
      </tn-nav-bar>
    </view>
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
    <view class="bg-contaniner tn-bg-blue"></view>
		<!-- 顶部搜索 -->
      <view
        class="search-box"
        :style="{top: vuex_custom_bar_height + 'px'}"
      >
        <text class="tn-icon-menu-classify fs-22" style="color: #8b9aae;" @click="showLibMenu = true"></text>
         <view class="acea-row row-middle relative search-item">
          <input
            v-model="queryParams.title"
            type="text"
            placeholder-class="plaClass"
            placeholder="搜索考试"
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
      <!-- <view class="exam-ul">
        <block
          v-for="(it, index) in questionList"
          :key="index"
        >
          <view
            class="tn-flex exam-li mt-10"
            @click="tl('/subpages/exam/examinationContent?uid=' + it.uid)"
          >
            <view class="txt tn-flex tn-flex-direction-column">
              <view class="fs-16 tn-text-ellipsis-2 news-title">
                {{ it.title }}
              </view>
              <view class="acea-row row-middle tn-color-gray">
                <view>
                  <text class="tn-icon-bookmark" />
                  <text class="ml-5">
                    {{ it.status.text }}
                  </text>
                </view>
                <view class="tn-margin-left-sm">
                  <text class="tn-icon-time" />
                  <text class="ml-5">
                    {{ it.exam_time }}
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
      </view> -->

	  <view class="" style="padding-top: 80rpx;">
      <block v-for="(it, index) in questionList" :key="index">
        <view class="article-shadow tn-margin-sm">
          <view class="tn-flex">
            <view class="image-pic tn-margin-sm" :style="'background-image:url(' + it.image + ')'">
              <view class="image-article">
              </view>
            </view>
            <view class="tn-margin-sm" style="width: 100%;">
              <view class="tn-text-lg tn-text-bold clamp-text-1">
                <text  v-if="it.privilege === 2" class="vip-tag">
                VIP
               </text>
              {{ it.title }}
              </view>
              <view class="tn-padding-top-xs tn-text-sm tn-color-gray">
                <view class="tn-padding-bottom-xs">
                  <text class="tn-icon-time tn-padding-right-xs"></text>
                  <text>考试时长：{{ it.exam_time }}</text>
                </view>
                <view class="">
                  <text class="tn-icon-data tn-padding-right-xs"></text>
                   <text>考试总分: {{ it.option_score || 0 }}分</text>
                </view>
                <!-- <view class="tn-padding-bottom-xs"> 
                  <text class="tn-icon-date tn-padding-right-xs"></text>
                  <text>开始时间：{{ it.start_time }}</text>
                </view>
                <view class="tn-padding-bottom-xs"> 
                  <text class="tn-icon-date tn-padding-right-xs"></text>
                  <text>结束时间：{{ it.end_time }}</text>
                </view> -->
              </view>
              <view class="tn-flex tn-flex-row-between tn-flex-col-center">
                  <view class="justify-content-item tn-text-center">
                    <!-- status=0，status=1，status=2，status=3 分别对应不存在 未开始，已结束 ，进行中-->
                    <view 
                      class="tn-inline-flex tn-items-center tn-px-2 tn-py-1 tn-radius-full tn-font-medium tn-text-sm" 
                      :class="[
                        it.status.status === 0 ? 'tn-color-gray' : '',
                        it.status.status === 1 ? 'tn-color-yellow' : '',
                        it.status.status === 2 ? 'tn-color-red' : '',
                        it.status.status === 3 ? 'tn-color-green' : ''
                      ]">
                      <text class="tn-icon-bookmark tn-mr-1"></text>
                      {{ it.status.text }}
                    </view>
                  </view>
                  <view class="tn-flex-shrink-0">
                    <tn-button 
                      size="sm" 
                      :plain="false" 
                      backgroundColor="tn-cool-bg-color-14"
                      font-color="#fff"
                      @click="tl('/subpages/examination/examinationContent?uid=' + it.uid)"
                    >
                      查看详情
                    </tn-button>
                  </view>
                </view>
            </view>
          </view>
        </view>
      </block>
    </view>
	
      <!-- 资源列表为空开始-->
      <view
        v-if="questionList.length === 0"
        :style="{paddingTop: vuex_custom_bar_height * 2 + 'px'}"
      >
        <tn-empty
          mode="list"
          text="暂无考试"
        />
      </view>
      <!-- 资源列表为空结束 -->
      <view class="tn-tabbar-height" />
    </view>
    <!-- 选择项-->
    <view class="">
      <tn-select
        v-model="showLibMenu"
        value-name="uid"
        label-name="title"
        mode="single"
        :searchShow="false"
        :list="libList"
        @confirm="libConfirm"
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
					library_category_uid: '',
          lib_uid: '',
				},
				showLibMenu: false,
				questionList: [],
				libList: [],
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

      this.queryParams.library_category_uid = option.exam_category_uid
			this.fetchtLibList()
			this.fetchQuestionLibList()
		},
		methods: {
			search() {
				this.queryParams.page_no = 1
				this.questionList = []
				this.fetchQuestionLibList()
			},
			libConfirm(e) {
				console.log(e)
				if (e.length) this.queryParams.lib_uid = e[e.length - 1].value
				this.search()
			},
			fetchtLibList() {
				this.$api.apiQuestionLib({category_uid:this.queryParams.library_category_uid}).then((res) => {
          if(res.code === 1){
            this.libList = res.data.lists
          }else{
            this.libList = []
          }
					
				})
			},
			fetchQuestionLibList() {
				uni.showLoading({
					title: '努力加载中'
				})
				this.$api.apiExaminationList(this.queryParams).then(res => {
          if(res.code === 1){
            this.questionList.push(...res.data.lists)
            this.queryParams.page_no += 1
            uni.hideLoading()
          }else{
            this.questionList = []
            uni.hideLoading()
          }
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

  /* 资讯主图 start*/
  .image-article {
    border-radius: 10rpx;
    border: 1rpx solid #F8F7F8;
    width: 250rpx;
    position: relative;
    height: 160rpx;
  }

  .image-pic {
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    border-radius: 10rpx;
    width: 250rpx;
    height: 160rpx;
    flex-shrink: 0;
  }

  .article-shadow {
    background-color: #fff;
    border-radius: 15rpx;
    box-shadow: var(--tn-shadow-md);
    transition: var(--tn-transition);
    overflow: hidden;
  }

  .article-shadow:hover {
    transform: translateY(-4rpx);
    box-shadow: var(--tn-shadow-lg);
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
	.vip-tag {
		background: linear-gradient(135deg, #FBBD12, #FF71D2);
		color: white;
		font-size: 20rpx;
		padding: 4rpx 12rpx;
		border-radius: 12rpx;
		display: inline-block;
		vertical-align: middle;
		margin: 4rpx;
		text-transform: uppercase;
		letter-spacing: 1rpx;
		box-shadow: var(--tn-shadow-sm);
	}
  
	.fs-16 {
		font-size: 30rpx;
	}
</style>

