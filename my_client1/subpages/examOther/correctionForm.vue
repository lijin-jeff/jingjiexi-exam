<template>
  <!-- 试题纠错表单 -->
  <view class="container">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <view slot="back" class='tn-custom-nav-bar__back' @click="goBack">
          <text class='icon tn-icon-left'></text>
          <text class='icon tn-icon-home-capsule-fill'></text>
        </view>
        <view class="tn-flex tn-flex-col-center tn-flex-row-center ">
          <text class="tn-text-bold tn-text-xl tn-color-white">试题纠错</text>
        </view>
      </tn-nav-bar>
    </view>
    
    <!-- 页面内容 -->
    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 表单容器，添加卡片样式 -->
      <view class="form-card tn-bg-white tn-margin-sm">
        <!-- 表单标题 -->
        <view class="form-header">
          <text class="tn-text-bold tn-text-lg">
            请填写纠错信息
          </text>
          <text class="tn-text-sm tn-color-gray">
            {{ websiteShop_name }}用心提供助你更高效率拿证的精编题目，但可能仍存在问题。你的反馈与建议对我们很重要~
          </text>
        </view>
        
        <!-- 表单内容 -->
        <tn-form
          ref="form" 
          :model="model" 
          :error-type="errorType" 
          :label-position="labelPosition"
        >
        <tn-form-item label="类型" prop="correction_type" :labelPosition="labelPosition" :labelAlign="labelAlign">
            <tn-checkbox-group v-model="model.correction_type" :width="checkboxWidth" :wrap="checkboxWrap" @change="checkboxGroupChange">
              <tn-checkbox v-for="(item, index) in checkboxList" :key="index" :activeColor="mainColor" :name="item.name" :disabled="item.disabled">{{ item.name }}</tn-checkbox>
            </tn-checkbox-group>
        </tn-form-item>
          <!-- 纠错原因 -->
        <tn-form-item label="内容" prop="desc" :labelPosition="labelPosition" :labelAlign="labelAlign">
          <tn-input v-model="model.desc" type="textarea" class="comment-textarea" placeholder="请输入详细纠错内容，以便我们更快验证并解决问题"></tn-input>
        </tn-form-item>
          
          <!-- 图片上传 -->
          <!-- <tn-form-item label="图片" prop="photo" :labelPosition="labelPosition" :labelAlign="labelAlign">
            <tn-image-upload 
              :action="action"
              @on-list-change="imageUploadChange"
              @on-before-upload="beforeImageUpload"
              :max-count="1"
              :multiple="false"
              :custom-upload="true"
            ></tn-image-upload>
          </tn-form-item> -->
        </tn-form>
        <!-- 提交按钮 -->
        <view class="submit-btn-container tn-margin">
          <tn-button 
            :background-color="mainColor" 
            font-color="#FFFFFF" 
            width="100%"
            :loading="submitting"
            :round="true"
            size="lg"
            @click="submit"
          >
            {{ submitting ? '提交中...' : '提交' }}
          </tn-button>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
export default {
  name: 'CorrectionForm',
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor, // 提供默认值
      websiteShop_name: '平台',
      errorType: ['message'],
      labelPosition: 'top',
      labelAlign: 'right',
      checkboxWidth: 'auto',
      checkboxWrap: false,
      action: '',
      uploadHeader: {}, // 上传请求头
      submitting: false, // 提交按钮加载状态
      model: {
        question_uid: '',
        desc: '',
        photo: [],
        correction_type: [],
        user_id: '',
      },
      // 表单验证规则
      rules: {
        correction_type: [
          {
            required: true,
            type: 'array',
            min: 1,
            message: '请选择纠错类型',
            trigger: ['change']
          }
        ],
        desc: [
          {
            required: true,
            message: '请输入纠错原因',
            trigger: ['blur', 'change']
          },
          {
            min: 6,
            message: '纠错原因不能少于6个字',
            trigger: ['blur', 'change']
          },
          {
            max: 200,
            message: '纠错原因不能超过200个字',
            trigger: ['blur', 'change']
          }
        ]
      },checkboxList:[
          {
            name: '错别字',
            disabled: false
          },
          {
            name: '答案有误',
            disabled: false
          },
          {
            name: '排版错误',
            disabled: false
          },
          {
            name: '图片模糊',
            disabled: false
          },
          {
            name: '解析有误',
            disabled: false
          },
          {
            name: '其他错误',
            disabled: false
          }
        ],
    }
  },
  onLoad(options) {
    // 获取全局应用实例
    const app = getApp()
    
    // 初始化数据
    if (app && app.globalData) {
      // 更新主色调
      this.mainColor = app.globalData.mainColor || this.mainColor
      
      // 更新店铺名称
      this.websiteShop_name = app.globalData.shopName || app.globalData.sysConfig?.website?.shop_name || this.websiteShop_name
      
      // 注意：TuniaoUI的image-upload组件不需要手动设置action和header
      // 组件会自动处理上传，我们在success回调中处理结果
      this.action = 'api/upload/image' // 设置上传接口地址，使用自定义上传
      
      // 获取token用于自定义上传
      const token = uni.getStorageSync('login')
      this.uploadHeader = {
        Authorization: `Bearer ${token}`,
        Platform: 'wechat_mini',
        'User-Code': 'Mg==',
        tenantId: 1
      }
    }

    // 从页面参数获取问题ID
    if (options && options.question_uid) {
      this.model.question_uid = options.question_uid
    }
  },
  onReady() {
    // 确保表单引用存在再设置规则
    this.$nextTick(() => {
      if (this.$refs.form) {
        this.$refs.form.setRules(this.rules)
      }
    })
  },
  methods: {
    // 返回上一页
    goBack() {
      uni.navigateBack()
    },
    // 多选项值改变事件
    checkboxGroupChange(event) {
      this.model.correction_type = event
    },
    
    // 图片列表变化事件
    imageUploadChange(fileList) {
      console.log('图片列表变化:', fileList)
      this.model.photo = fileList
    },
    
    // 图片上传前的钩子，使用自定义上传
    beforeImageUpload(file, fileList) {
      console.log('开始上传图片:', file)
      
      return new Promise((resolve, reject) => {
        uni.showLoading({ title: '上传中...', mask: true })
        
        // 使用自定义上传接口
        this.$api.apiUploadImage(file.path || file.url).then(res => {
          uni.hideLoading()
          
          if (res.code === 1) {
            // 上传成功，更新文件信息
            file.uri = res.data.uri
            file.url = res.data.url || res.data.uri
            file.status = 'success'
            
            this.$func.showToast('上传成功')
            resolve(file)
          } else {
            this.$func.showToast(res.msg || '图片上传失败')
            reject(new Error(res.msg || '上传失败'))
          }
        }).catch(err => {
          uni.hideLoading()
          console.error('图片上传失败:', err)
          this.$func.showToast('上传失败，请重试')
          reject(err)
        })
      })
    },
    // 输入框获得焦点
    onInputFocus() {
      // 可以在这里添加额外的交互逻辑
      console.log('输入框获得焦点')
    },
    
    // 输入框失去焦点
    onInputBlur() {
      // 可以在这里添加额外的交互逻辑
      console.log('输入框失去焦点')
    },

    // 表单提交
    submit() {
      if (!this.$refs.form) {
        this.$func.showToast('表单初始化失败', 'error')
        return
      }

      this.$refs.form.validate(valid => {
        if (valid) {
          // 显示提交中状态
          this.submitting = true
          
          // 执行表单提交逻辑
          this.submitFormData()
        } else {
          // 表单验证失败，使用表单内置的错误提示，这里不再重复提示
          // 只在需要时显示额外提示
          console.log('表单验证失败')
        }
      })
    },
    
    // 提交表单数据到服务器
    submitFormData() {
      try {
        // 准备提交数据
        const submitData = {
          question_uid: this.model.question_uid,
          // correction_type是数组，数据库字段是JSON类型，转换为JSON字符串
          correction_type: JSON.stringify(this.model.correction_type),
          correction_reason: this.model.desc,
          // 获取上传成功的图片uri
          correction_image: this.model.photo.length > 0 ? (this.model.photo[0].uri || this.model.photo[0].url || '') : '',
        }
        // 调用API提交数据
        this.$api.apiErroCorrect(submitData).then(res => {
          if (res && res.code === 1) {
            // 提交成功
            this.$func.showToast('提交成功', 'success')
            // 提交成功后返回上一页
            setTimeout(() => {
              this.goBack()
            }, 1500)
          } else {
            // 提交失败，显示错误信息
            this.$func.showToast(res.msg || '提交失败', 'error')
          }
        }).catch(err => {
          // 网络错误或其他异常
          this.$func.showToast(err.msg || '网络异常，请稍后重试', 'error')
        }).finally(() => {
          // 无论成功失败，都关闭加载状态
          this.submitting = false
        })
      } catch (error) {
        // 捕获代码执行错误
        console.error('提交过程中发生错误:', error)
        this.submitting = false
        this.$func.showToast('提交过程中发生错误，请稍后重试', 'error')
      }
    },
  
  }
}
</script>

<style lang="scss" scoped>
  @import "@/scss/custom_nav_bar.scss";
// 页面容器
.container {
  min-height: 100vh;
  background-color: #ffffff;
}
// 表单卡片
.form-card {
  margin-bottom: 30rpx;
  overflow: hidden;
} 

// 表单头部
.form-header {
  padding: 30rpx;
  
  > text:first-child {
    display: block;
    margin-bottom: 20rpx;
  }
}

// 提交按钮容器
.submit-btn-container {
  padding-top: 0rpx;
}

// 优化textarea样式
.tn-input--textarea {
  min-height: 150rpx;
  max-height: 250rpx;
  border-radius: 12rpx;
}

// 优化图片上传样式
.tn-image-upload {
  margin-top: 10rpx;
  
  ::v-deep .tn-image-upload__item-wrapper {
    border-radius: 12rpx;
  }
}

// 优化表单间距
.tn-form-item {
  margin: 0 20rpx 20rpx 20rpx;
  
  &__label {
    font-weight: bold;
  }
}

.comment-textarea {
    width: 100%;
    height: 150rpx;
    resize: none;
    overflow: hidden;
    font-size: 28rpx;
    line-height: 1.5;
  }

// 响应式设计
@media (max-width: 375px) {
  
  .form-card {
    margin-bottom: 20rpx;
  }
  
  .form-header {
    padding: 20rpx;
  }
  
}

</style>
