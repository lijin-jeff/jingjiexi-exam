<template>
  <view class="page tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航开始-->
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
        <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
          <text class="tn-text-bold tn-text-xl tn-color-white">
            资源预览
          </text>
        </view>
      </tn-nav-bar>
    </view>

    <!-- 内容区域开始-->
    <view class="tn-margin-top-xs"  :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 加载中 -->
      <view v-if="loading" class="tn-flex tn-flex-col-center tn-flex-row-center tn-height-lg">
        <tn-loading type="circular-ring" color="#42B476"></tn-loading>
        <text class="tn-margin-left-sm tn-color-grey">加载中...</text>
      </view>
      
      <!-- 图片预览 -->
      <view v-else-if="isImage" class="preview-image-container">
        <image 
          :src="previewUrl" 
          mode="aspectFit" 
          class="preview-image"
          @load="onImageLoad"
          @error="onImageError"
        />
      </view>
      
      <!-- 链接预览 -->
      <web-view v-else-if="previewUrl" :src="previewUrl" @error="onWebViewError"></web-view>
      
      <!-- 不支持的预览类型 -->
      <view v-else class="tn-flex tn-flex-col-center tn-flex-row-center tn-height-lg">
        <text class="tn-icon-file-text tn-text-grey tn-text-2xl"></text>
        <text class="tn-margin-top-sm tn-color-grey">暂不支持此类型资源的预览</text>
        <tn-button type="primary" plain size="small" class="tn-margin-top" @click="goBack">返回</tn-button>
      </view>
    </view>
    <!-- 内容区域结束 -->
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'

export default {
  components: {
  },
  mixins: [template_page_mixin],
  data() {
    return {
      previewUrl: '',
      title: '',
      loading: true,
      isImage: false
    }
  },
  onLoad(params) {
    // 获取预览链接和标题
    this.previewUrl = decodeURIComponent(params.url || '')
    this.title = decodeURIComponent(params.title || '资源预览')
    
    // 检测是否为图片类型
    this.isImage = this.isImageUrl(this.previewUrl)
    
    // 延迟设置loading为false，避免页面闪烁
    setTimeout(() => {
      this.loading = false
    }, 300)
  },
  methods: {
    // 检测是否为图片链接
    isImageUrl(url) {
      if (!url) return false
      const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp']
      const lowerUrl = url.toLowerCase()
      return imageExtensions.some(ext => lowerUrl.endsWith(ext))
    },
    
    // 图片加载成功
    onImageLoad() {
      console.log('图片加载成功')
      this.loading = false
    },
    
    // 图片加载失败
    onImageError() {
      console.error('图片加载失败')
      this.loading = false
      this.isImage = false
    },
    
    // WebView加载失败
    onWebViewError() {
      console.error('WebView加载失败')
      this.loading = false
    }
  }
}
</script>

<style lang="scss" scoped>
  @import "@/scss/custom_nav_bar.scss";
  
  .preview-image-container {
    width: 100%;
    min-height: calc(100vh - var(--custom-bar-height, 0px));
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f5f5f5;
  }
  
  .preview-image {
    width: 100%;
    max-height: calc(100vh - var(--custom-bar-height, 0px) - 20px);
  }
  
  web-view {
    width: 100%;
    min-height: calc(100vh - var(--custom-bar-height, 0px));
  }
</style>
