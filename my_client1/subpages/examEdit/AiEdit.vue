<template>
  <view class="page tn-safe-area-inset-bottom">
    <!-- 导航栏 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
        <template #back>
          <view class="tn-custom-nav-bar__back">
            <text class="icon tn-icon-left" @click="goBack" />
            <text class="icon tn-icon-home-capsule-fill" @click="goHome"/>
        </view>
        </template>
        <text class="tn-text-bold tn-text-xl tn-color-white">
          AI试题识别
        </text>
      </tn-nav-bar>
    </view>

    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
       <!-- 试题设置 -->
      <view class="settings-section tn-bg-white tn-padding tn-margin-bottom">
        <view class="tn-margin-bottom">
        <text class="tn-text-bold tn-text-lg">试题设置</text>
        </view>
        <!-- 章节选择 -->
        <view class="setting-item tn-margin-bottom">
         <text class="tn-text-bold tn-margin-bottom">章节：</text>
        <text
            @click="showChapterSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            width="80%"
            :originLeft='true'
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ selectedChapter ? selectedChapter.label : '选择章节' }}
        </text>
        <tn-select
          v-model="showChapterSelect"
          mode="multi-auto"
          :list="chapterFilterOptions"
          :defaultValue="getChapterDefaultValue"
          @confirm="onChapterSelectConfirm"
          @cancel="showChapterSelect = false"
          title="选择章节"
        /> 
      </view>
        <!-- 难度设置 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">难度：</text>
          <text
            @click="showDifficultySelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ getDifficultyLabel(selectedDifficulty) }}
          </text>
          <tn-select
            v-model="showDifficultySelect"
            :list="difficultyList"
            :searchShow="false"
            :defaultValue="getDifficultyDefaultValue"
            @confirm="onDifficultySelectConfirm"
            @cancel="showDifficultySelect = false"
            title="选择难度"
          />
        </view>
        
        <!-- 分值设置 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">分值：</text>
          <tn-number-box
            v-model="score"
            :min="0"
            :max="10"
            :step="0.5"
            :positive-integer="false"
          />
        </view>
        
        <!-- 积分设置 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">积分：</text>
          <tn-number-box
            v-model="integral"
            :min="0"
            :max="100"
            :step="1"
          />
        </view>
        
        <!-- 知识点设置 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">知识点：</text>
          <text
            @click="showKnowledgePopup = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ selectedKnowledge.length > 0 ? selectedKnowledge.length + '个知识点' : '选择知识点' }}
          </text>
          <!-- 知识点多选弹窗 -->
          <tn-popup
            v-model="showKnowledgePopup"
            mode="bottom"
            width="100%"
            height="50%"
            closeBtn="true"
            title="选择知识点"
            @close="showKnowledgePopup = false"
          >
            <view class="tn-padding" style="height: 100%; display: flex; flex-direction: column;">
              <view class="tn-text-center tn-margin-bottom">
                <text class="tn-text-bold tn-text-lg">选择知识点</text>
              </view>
              <scroll-view scroll-y="true" style="flex: 1;">
                <tn-checkbox-group v-model="selectedKnowledge" @change="onKnowledgeGroupChange">
                  <tn-checkbox 
                    v-for="item in knowledgeList" 
                    :key="item.value"
                    :name="item.value"
                    shape="circle"
                    size="40"
                    class="tn-margin-bottom"
                  >
                    {{ item.label }}
                  </tn-checkbox>
                </tn-checkbox-group>
              </scroll-view>
              <view class="tn-margin-top tn-flex tn-flex-row tn-justify-between tn-padding-bottom" style="margin-top: auto;">
                <tn-button 
                  size="lg" 
                  @click="setDefaultKnowledge"
                  class="tn-flex-1 tn-margin-right"
                  width="100%"
                  backgroundColor="#F5F7FA"
                >
                  设为默认
                </tn-button>
                <tn-button 
                  size="lg" 
                  @click="showKnowledgePopup = false"
                  class="tn-flex-1 tn-margin-right"
                  width="100%"
                  backgroundColor="#F5F7FA"
                >
                  取消
                </tn-button>
                <tn-button 
                  size="lg" 
                  @click="confirmKnowledgeSelection"
                  class="tn-flex-1"
                  width="100%"
                  backgroundColor="tn-cool-bg-color-7"
                  fontColor="#ffffff"
                >
                  确认选择
                </tn-button>
              </view>
            </view>
          </tn-popup>
        </view>
        
        <!-- 标签设置 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">标签：</text>
          <text
            @click="showLabelPopup = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ selectedLabels.length > 0 ? selectedLabels.length + '个标签' : '选择标签' }}
          </text>
          <!-- 标签多选弹窗 -->
          <tn-popup
            v-model="showLabelPopup"
            mode="bottom"
            width="100%"
            height="50%"
            title="选择标签"
            closeBtn="true"
            @close="showLabelPopup = false"
          >
            <view class="tn-padding" style="height: 100%; display: flex; flex-direction: column;">
              <view class="tn-text-center tn-margin-bottom">
                <text class="tn-text-bold tn-text-lg">选择标签</text>
              </view>
              <scroll-view scroll-y="true" style="flex: 1;">
                <tn-checkbox-group v-model="selectedLabels" @change="onLabelGroupChange">
                  <tn-checkbox 
                    v-for="item in labelList" 
                    :key="item.value"
                    :name="item.value"
                    shape="circle"
                    size="40"
                    class="tn-margin-bottom"
                  >
                    {{ item.label }}
                  </tn-checkbox>
                </tn-checkbox-group>
              </scroll-view>
              <view class="tn-margin-top tn-flex tn-flex-row tn-justify-between tn-padding-bottom" style="margin-top: auto;">
                <tn-button 
                  size="lg" 
                  width="100%"
                  @click="setDefaultLabels"
                  class="tn-flex-1 tn-margin-right"
                  backgroundColor="#F5F7FA"
                >
                  设为默认
                </tn-button>
                <tn-button 
                  size="lg" 
                  width="100%"
                  @click="showLabelPopup = false"
                  class="tn-flex-1 tn-margin-right"
                  backgroundColor="#F5F7FA"
                >
                  取消
                </tn-button>
                <tn-button 
                  size="lg"
                  width="100%"
                  @click="confirmLabelSelection"
                  class="tn-flex-1"
                  backgroundColor="#01BEFF"
                  fontColor="#ffffff"
                >
                  确认选择
                </tn-button>
              </view>
            </view>
          </tn-popup>
        </view>
        
        <!-- 显示状态 -->
        <view class="setting-item tn-margin-bottom tn-flex tn-flex-row tn-align-center">
          <text class="tn-text-bold tn-margin-right">显示状态：</text>
          <tn-switch
            v-model="isShow"
            :active-color="mainColor"
            @change="onIsShowChange"
            size="40"
          />
          <text :class="isShow ? 'success-text' : 'error-text'" class="tn-margin-left-sm">{{ isShow ? '显示' : '隐藏' }}</text>
        </view>
      </view>

      <!-- 图片上传 -->
      <view class="upload-section tn-bg-white tn-padding tn-margin-bottom">
        <view class="tn-flex tn-flex-row tn-justify-between tn-flex-center tn-margin-bottom">
          <text class="tn-text-bold tn-text-lg">上传试题图片</text>
          <tn-button 
            size="sm" 
            backgroundColor="tn-bg-gray--light" 
            fontColor="#666666"
            @click="openPromptModal"
          >
            <text class="tn-icon-edit tn-margin-right-xs"></text>
            编辑提示词
          </tn-button>
        </view>
        <view class="upload-area tn-margin-bottom" @click="chooseImage">
          <view v-if="!imageUrl" class="upload-placeholder tn-flex tn-flex-col-center">
            <text class="tn-icon-camera tn-text-3xl tn-color-gray tn-margin-bottom" />
            <text class="tn-color-gray">点击上传试题图片</text>
          </view>
          <view v-else class="image-container" @click.stop>
            <image :src="imageUrl" class="uploaded-image" mode="aspectFit" />
            <view class="delete-button" @click.stop="deleteImage">
              <text class="tn-icon-delete-fill tn-color-red tn-text-lg"></text>
            </view>
          </view>
        </view>
        <view class="tn-flex tn-flex-row tn-justify-between">
        <tn-button
          size="lg"
          class="tn-flex-1 save-button"
          :backgroundColor="imageUrl ? 'tn-cool-bg-color-9' : '#E6E6E6'"
          width="100%"
          :fontColor="imageUrl ? '#ffffff' : '#999999'"
          @click="recognizeQuestion"
          :loading="recognizing"
          :disabled="!imageUrl || !selectedChapter || recognizing"
        >
          {{ recognizing ? '识别中...' : '开始识别' }}
        </tn-button>
        <tn-button
            size="lg"
            class="tn-flex-1 save-button"
            width="100%"
            :backgroundColor="recognitionResult ? 'tn-cool-bg-color-7' : '#E6E6E6'"
            :fontColor="recognitionResult ? '#ffffff' : '#999999'"
            @click="handleSave"
            :loading="saving"
            :disabled="!recognitionResult || !selectedChapter"
          >
            {{ saving ? '保存中...' : '保存' }}
        </tn-button>
        </view>
        <text class="tn-flex tn-margin-top tn-text-sm tn-color-gray">
          支持识别单选题、多选题，会自动提取题干、选项、正确答案、解析和笔记
        </text>
      </view>
      
      <!-- 删除确认模态框 -->
      <tn-modal
        v-model="showDeleteConfirm"
        :showCloseBtn="true"
        :title="deleteTitle" 
        :content="deleteContent" 
        :button="deleteButton"
        @cancel="cancelDelete"
        @click="confirmDeleteImage"
      >
      </tn-modal>

      <!-- 重复试题确认模态框 -->
      <tn-modal
        v-model="showDuplicateConfirm"
        :showCloseBtn="true"
        title="提示"
        :custom="true"
        width="80%"
      >
        <view class="duplicate-confirm-modal">
          <view class="modal-icon">
            <text class="tn-icon-warning-fill tn-text-3xl" style="color: #ff9900;"></text>
          </view>
          <view class="modal-message">
            <text>{{ duplicateMessage }}</text>
          </view>
          <view class="modal-hint">
            <text class="tn-text-sm tn-color-gray">是否仍要添加此试题？</text>
          </view>
          <view class="modal-buttons">
            <tn-button
              size="lg"
              width="100%"
              backgroundColor="#E6E6E6"
              fontColor="#666666"
              @click="cancelDuplicateConfirm"
            >
              取消
            </tn-button>
            <tn-button
              size="lg"
              width="100%"
              :backgroundColor="mainColor"
              fontColor="#ffffff"
              @click="confirmDuplicateAdd"
            >
              继续添加
            </tn-button>
          </view>
        </view>
      </tn-modal>

      <!-- 识别结果模态框 -->
      <tn-modal
        v-model="showRecognitionResultModal"
        :showCloseBtn="true"
        title="识别成功"
        :custom="true"
        width="90%"
      >
        <view class="recognition-result-modal">
          <!-- 识别成功标题 -->
          <view class="modal-section modal-center">
            <text class="section-title">识别结果</text>
          </view>
           <view class="modal-section">
            <text class="section-title">题目：</text>
            <text class="section-content">{{ recognitionResultDetails.title }}</text>
          </view>
          <!-- 匹配知识点数量 -->
          <view class="modal-section">
            <text class="section-title">匹配知识点数量：</text>
            <text class="section-content">{{ recognitionResultDetails.matchedKnowledgeCount }} 个</text>
          </view>
          <!-- 题型 -->
          <view class="modal-section">
            <text class="section-title">题型：</text>
            <text class="section-content">{{ recognitionResultDetails.examTypeName || '未识别' }}</text>
          </view>
          <!-- 选项识别状态 -->
          <view class="modal-section">
            <text class="section-title">选项：</text>
            <text :class="recognitionResultDetails.hasOptions ? 'success-text' : 'error-text'">
              {{ recognitionResultDetails.hasOptions ? '识别成功' : '未识别到选项' }}
            </text>
          </view>
          <!-- 解析识别状态 -->
          <view class="modal-section">
            <text class="section-title">解析：</text>
            <text :class="recognitionResultDetails.hasAnalysis ? 'success-text' : 'error-text'">
              {{ recognitionResultDetails.hasAnalysis ? '识别成功' : '未识别到解析' }}
            </text>
          </view>
          <!-- 消耗token信息 -->
          <view class="modal-section">
            <text class="section-title">Token使用统计：</text>
            <view class="token-details">
              <text class="token-item">输入: {{ recognitionResultDetails.tokenUsage.input_tokens }}</text>
              <text class="token-item">输出: {{ recognitionResultDetails.tokenUsage.output_tokens }}</text>
              <text class="token-item">总计: {{ recognitionResultDetails.tokenUsage.total_tokens }}</text>
            </view>
          </view>
          
          <!-- 模型信息 -->
          <view class="modal-section">
            <text class="section-title">使用模型：</text>
            <text class="section-content">{{ recognitionResultDetails.model }}</text>
          </view>
          
          <!-- 操作按钮 -->
          <view class="modal-buttons">
            <tn-button
              size="lg"
              width="100%"
              backgroundColor="tn-cool-bg-color-7"
              fontColor="#ffffff"
              @click="showRecognitionResultModal = false"
            >
              确定
            </tn-button>
          </view>
        </view>
      </tn-modal>

      <!-- 提示词编辑弹窗 -->
      <tn-modal
        v-model="showPromptModal"
        :custom="true"
        :show-close-btn="true"
      >
        <view class="custom-modal-content">
          <view class="">
            <view
              class="tn-text-lg tn-text-bold tn-text-center"
              :style="{color: mainColor}"
            >
              编辑识别提示词
            </view>
            <view
              class="tn-bg-gray--light"
              style="border-radius: 10rpx;padding: 20rpx 30rpx;margin: 30rpx 0;"
            >
              <textarea
                placeholder="请输入提示词"
                name="input"
                v-model="editingPrompt"
                placeholder-style="color:#AAAAAA"
                :maxlength="500"
                style="width: 100%;height: 300rpx;"
              />
            </view>
          </view>
          <view class="tn-flex tn-flex-row tn-justify-between">
            <tn-button
              backgroundColor="tn-bg-gray--light"
              padding="30rpx"
              width="100%"
              @click="resetPrompt"
            >
              <text class="tn-color-gray">
                重置默认
              </text>
            </tn-button>
            <tn-button
              backgroundColor="tn-bg-gray--light"
              padding="30rpx"
              width="100%"
              font-bold
              @click="showPromptModal = false"
            >
              <text class="tn-color-gray">
                取 消
              </text>
            </tn-button>
            <tn-button
              :backgroundColor="mainColor"
              padding="30rpx"
              width="100%"
              font-bold
              @click="savePrompt"
            >
              <text class="tn-color-white">
                保 存
              </text>
            </tn-button>
          </view>
        </view>
      </tn-modal>

      <!-- 识别结果 -->
      <view v-if="recognitionResult" class="result-section tn-bg-white tn-padding tn-margin-bottom">
        <text class="tn-text-bold tn-text-lg tn-margin-bottom">识别结果</text>
        
        <!-- 题干 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">题干：</text>
          <textarea
            v-model="recognitionResult.title"
            placeholder="请输入题干"
            :auto-height="true"
            class="tn-margin-top-sm"
            style="width: 100%; min-height: 150rpx;"
          />
        </view>
        
        <!-- 选项 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">选项：</text>
          <view 
            v-for="(option, index) in recognitionResult.options" 
            :key="index" 
            class="option-item"
          >
            <view class="tn-flex tn-flex-row">
              <text class="option-label">{{ String.fromCharCode(65 + index) }}</text>
              <view class="option-content">
                <textarea
                  v-model="option.content"
                  placeholder="请输入选项内容"
                  :auto-height="true"
                  class="option-textarea"
                  style="width: 100%; min-height: 60rpx;"
                />
              </view>
            </view>
          </view>
        </view>
        
        <!-- 正确答案 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">正确答案：</text>
          <tn-input
            v-model="recognitionResult.correct_answer"
            placeholder="请输入正确答案"
            class="tn-margin-top-sm"
          />
        </view>
        
        <!-- 解析 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">解析：</text>
          <piaoyiEditor 
            :values="recognitionResult.analysis" 
            :maxlength="5000" 
            @changes="onAnalysisChange"
            :api="uploadConfig.api"
            :photoUrl="uploadConfig.photoUrl"
            class="piaoyi-editor-wrapper"
          />
        </view>
        
        <!-- 名师点评 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">名师点评：</text>
          <piaoyiEditor 
            :values="recognitionResult.commentaries" 
            :maxlength="5000" 
            @changes="onCommentariesChange"
            :api="uploadConfig.api"
            :photoUrl="uploadConfig.photoUrl"
            class="piaoyi-editor-wrapper"
          />
        </view>
        
        <!-- 题型 -->
        <view class="result-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">题型：</text>
          <tn-input
            v-model="recognitionResult.exam_type_name"
            placeholder="请输入题型"
            class="tn-margin-top-sm"
          />
        </view>
      </view>
    </view>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
import piaoyiEditor from '@/uni_modules/piaoyi-editor/components/piaoyi-editor/piaoyi-editor.vue'

const STOP_WORDS = new Set([
  '的', '了', '是', '在', '有', '和', '就', '不', '人', '都', '一', '一个', '上', '也', '很', '到', '说', '要', '去', '你', '会', '着', '没有', '看', '好', '自己', '这', '正确', '错误', '对', '错', '选项', '答案', '解析', '题干', '问题', '下列', '哪些', '哪个', '什么', '如何', '为什么', '怎样', '是否', '可能', '应该', '可以', '必须', '不能', '不是', '属于', '不属于', '包括', '不包括', '关于', '对于', '根据', '依据', '按照', '遵循', '符合', '不符合', '或者', '与', '及', '等', '其中', '主要', '重要', '关键', '因此', '所以', '因为', '由于', '虽然', '但是', '然而', '如果', '那么', '只要', '只有', '无论', '不管', '尽管', '即使', '既然', '那么', '而且', '并且', '或者', '还是', '要么', '与其', '不如', '宁可', '也不', '一边', '一边', '一方面', '另一方面', '首先', '其次', '再次', '最后', '总之', '综上所述', '由此可见', '因此可见'
])

export default {
  name: 'AiEdit',
  mixins: [template_page_mixin],
  components: {
    piaoyiEditor
  },
  data() {
    return {
      mainColor: getApp().globalData.mainColor,      
      // 章节选择
      showChapterSelect: false,
      chapterFilterOptions: [],
      selectedChapter: null,
      
      // 图片上传
      imageUrl: '',
      showDeleteConfirm: false,
      deleting: false,
      
      // 识别状态
      recognizing: false,
      saving: false,
      
      // 识别结果
      recognitionResult: null,
      
      // API 配置 - 从全局配置获取
      apiKey: '',
      modelName: 'doubao-seed-1-8-251228',
      apiUrl: 'https://ark.cn-beijing.volces.com/api/v3/responses',
      
      // 查询参数
      queryParams:{uid:''},
      
      // 知识点和标签
      knowledgeList: [],
      labelList: [],
      selectedKnowledge: [],
      selectedLabels: [],
      
      // 难度和分值
      difficultyList: [],
      selectedDifficulty: '', // 默认中等
      score: 1, // 默认1分
      integral: 0, // 默认0积分
      
      // 显示状态
      isShow: true, // 默认显示
      
      // 选择器显示状态
      showDifficultySelect: false,
      
      // 多选弹窗状态
      showKnowledgePopup: false,
      showLabelPopup: false,
        deleteTitle: '提示信息',
        deleteContent: '确定要删除这张图片吗？删除后将无法恢复',
        deleteButton: [{
            text: '取消',
            backgroundColor: '#E6E6E6',
          },
          {
            text: '确定',
            backgroundColor: 'tn-cool-bg-color-9',
            fontColor: '#FFFFFF'
          }
        ],
      
      // 识别结果模态框
      showRecognitionResultModal: false,
      recognitionResultDetails: {
        title: '',
        examTypeName: '',
        hasOptions: false,
        hasAnalysis: false,
        matchedKnowledgeCount: 0,
        tokenUsage: {
          input_tokens: 0,
          output_tokens: 0,
          total_tokens: 0
        },
        model: ''
      },
      
      // 提示词编辑
      showPromptModal: false,
      defaultPrompt: '请识别这张图片中的试题，提取出题型（单选题、多选题、判断题、填空题、问答题、案例题）、题干、选项（如果有）、正确答案和解析内容，以及笔记第一条的内容部分（没有笔记的话就不识别，不含用户名）。用JSON格式输出。题型字段为exam_type_name。',
      customPrompt: '',
      editingPrompt: '', // 正在编辑的提示词
      
      // 重复试题确认弹窗
      showDuplicateConfirm: false,
      duplicateMessage: '',
      pendingQuestionData: null, // 待保存的试题数据
      
      // 选项ID计数器
      optionIdCounter: 0,
      
      // 附件上传配置
      uploadConfig: {
        api: 'api/upload/image',
        photoUrl: 'https://cx4vmw1d.allpp.cn/'
      },
    }
  },
  onLoad(option) {
	this.queryParams.uid = option.uid || ''
    this.apiKey = getApp().globalData.doubaoApiKey || ''
    this.loadSavedSettings()
    this.loadChapterOptions()
    this.loadDictData()
    // 加载已保存的提示词
    const savedPrompt = uni.getStorageSync('aiEditCustomPrompt')
    if (savedPrompt) {
      this.customPrompt = savedPrompt
    }
    // 加载附件上传配置
    this.loadUploadConfig()
  },
  computed: {
    // 章节选择默认值
    getChapterDefaultValue() {
      if (!this.selectedChapter || !this.chapterFilterOptions || !this.chapterFilterOptions.length) {
        return [];
      }
      
      // 多列联动模式需要处理父子章节
      const currentValue = this.selectedChapter.value;
      
      // 递归查找选中的章节路径
      function findChapterPath(list, value) {
        for (let i = 0; i < list.length; i++) {
          if (String(list[i].value) === String(value)) {
            return [i];
          }
          if (list[i].children && list[i].children.length) {
            const path = findChapterPath(list[i].children, value);
            if (path.length > 0) {
              return [i, ...path];
            }
          }
        }
        return [];
      }
      
      const path = findChapterPath(this.chapterFilterOptions, currentValue);
      return path;
    },
    
    // 难度选择默认值
    getDifficultyDefaultValue() {
      if (!this.difficultyList || !this.difficultyList.length || !this.selectedDifficulty) {
        return [];
      }
      const index = this.difficultyList.findIndex(item => String(item.value) === String(this.selectedDifficulty));
      return index >= 0 ? [index] : [];
    }
  },
  methods: {
    // 加载附件上传配置
    loadUploadConfig() {
      // 清除全局配置中的错误配置
      if (getApp().globalData.uploadConfig) {
        getApp().globalData.uploadConfig = this.uploadConfig
      }
      console.log('上传配置已设置:', this.uploadConfig)
    },
    // 加载数据字典
    loadDictData() {
      // 获取难度数据
      this.$api.getDictData({ type: 'exam_level' }).then(res => {
        if (res && res.code === 1) {
          if (res.data && Array.isArray(res.data)) {
            const levelOptions = res.data.map(item => ({
              label: item.name,
              value: item.value
            }));
            this.difficultyList = levelOptions;
            // 设置默认难度为中等
            if (levelOptions.length > 0) {
              const mediumOption = levelOptions.find(item => item.label === '中等' || item.value === '2');
              if (mediumOption) {
                this.selectedDifficulty = mediumOption.value;
              } else {
                this.selectedDifficulty = levelOptions[0].value;
              }
            }
          }
        }
      }).catch(error => {
        console.error('获取难度数据失败:', error);
      });
    },
    // 返回上一页
    goBack() {
      uni.navigateBack()
    },
    
    // 打开提示词编辑弹窗
    openPromptModal() {
      this.editingPrompt = this.customPrompt || this.defaultPrompt
      this.showPromptModal = true
    },
    
    // 保存提示词
    savePrompt() {
      if (!this.editingPrompt.trim()) {
        uni.showToast({
          title: '提示词不能为空',
          icon: 'none'
        })
        return
      }
      
      this.customPrompt = this.editingPrompt
      // 保存到本地存储
      uni.setStorageSync('aiEditCustomPrompt', this.customPrompt)
      
      this.showPromptModal = false
      uni.showToast({
        title: '保存成功',
        icon: 'success'
      })
    },
    
    // 重置为默认提示词
    resetPrompt() {
      this.editingPrompt = this.defaultPrompt
      uni.showToast({
        title: '已重置为默认提示词',
        icon: 'none'
      })
    },
    
    // piaoyiEditor 解析内容变化
    onAnalysisChange(e) {
      if (this.recognitionResult) {
        this.recognitionResult.analysis = e.html || ''
      }
    },
    
    // piaoyiEditor 名师点评内容变化
    onCommentariesChange(e) {
      if (this.recognitionResult) {
        this.recognitionResult.commentaries = e.html || ''
      }
    },
    
    // 获取难度标签
    getDifficultyLabel(value) {
      const difficulty = this.difficultyList.find(item => item.value === value);
      return difficulty ? difficulty.label : '中等';
    },
    
    // 难度选择确认
    onDifficultySelectConfirm(values) {
      if (values && values.length > 0) {
        this.selectedDifficulty = values[0].value;
        // 保存到本地存储
        uni.setStorageSync('aiEditSelectedDifficulty', this.selectedDifficulty);
      }
    },
    
    // 切换知识点选中状态
    toggleKnowledge(value) {
      const index = this.selectedKnowledge.indexOf(value);
      if (index > -1) {
        // 取消选中
        this.selectedKnowledge.splice(index, 1);
      } else {
        // 选中
        this.selectedKnowledge.push(value);
      }
    },
    
    // 确认知识点选择
    confirmKnowledgeSelection() {
      // 保存到本地存储
      uni.setStorageSync('aiEditSelectedKnowledge', this.selectedKnowledge);
      // 关闭弹窗
      this.showKnowledgePopup = false;
    },
    
    // 设为默认知识点
    setDefaultKnowledge() {
      if (this.selectedKnowledge.length === 0) {
        uni.showToast({
          title: '请先选择知识点',
          icon: 'none'
        })
        return
      }
      // 保存到本地存储
      uni.setStorageSync('aiEditDefaultKnowledge', this.selectedKnowledge)
      uni.showToast({
        title: '已设为默认知识点',
        icon: 'success'
      })
    },
    
    // 切换标签选中状态
    toggleLabel(value) {
      const index = this.selectedLabels.indexOf(value);
      if (index > -1) {
        // 取消选中
        this.selectedLabels.splice(index, 1);
      } else {
        // 选中
        this.selectedLabels.push(value);
      }
    },
    
    // 确认标签选择
    confirmLabelSelection() {
      // 保存到本地存储
      uni.setStorageSync('aiEditSelectedLabels', this.selectedLabels);
      // 关闭弹窗
      this.showLabelPopup = false;
    },
    
    // 设为默认标签
    setDefaultLabels() {
      if (this.selectedLabels.length === 0) {
        uni.showToast({
          title: '请先选择标签',
          icon: 'none'
        })
        return
      }
      // 保存到本地存储
      uni.setStorageSync('aiEditDefaultLabels', this.selectedLabels)
      uni.showToast({
        title: '已设为默认标签',
        icon: 'success'
      })
    },
    
    // 显示状态变化
    onIsShowChange(value) {
      this.isShow = value;
    },
    
    // 知识点复选框组变化
    onKnowledgeGroupChange(value) {
      this.selectedKnowledge = value;
    },
    
    // 标签复选框组变化
    onLabelGroupChange(value) {
      this.selectedLabels = value;
    },
    
    // 去除字符串首尾的空行和空格，包括HTML空段落
    trimContent(content) {
      if (!content) return content;
      // 去除首尾空格和空行
      let trimmed = content.replace(/^[\s\n]+|[\s\n]+$/g, '');
      // 去除首尾的空段落（<p><br></p> 或 <p></p>）
      trimmed = trimmed.replace(/^(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>\s*)+/i, '');
      trimmed = trimmed.replace(/(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>)+$/i, '');
      return trimmed;
    },
    
    // 清理文本内容，确保中文格式正确
    cleanTextContent(text) {
      if (!text) return text
      let cleaned = text
      cleaned = cleaned.replace(/[\u0000-\u001F\u007F-\u009F]/g, '')
      cleaned = cleaned.replace(/\\u[0-9a-fA-F]{4}/g, match => {
        try {
          return String.fromCharCode(parseInt(match.slice(2), 16))
        } catch (e) {
          return ''
        }
      })
      cleaned = cleaned.replace(/\s+/g, ' ').trim()
      return cleaned
    },
    
    // 加载章节选项
    loadChapterOptions() {
        // 从服务器获取章节数据
        this.$api.apiQuestionChapterList({ uid: this.queryParams.uid }).then(res => {
            if (res && res.code === 1) {
                // 更新章节筛选选项
                if (res.data && Array.isArray(res.data)) {
                    // 处理章节数据，转换为多列联动模式所需的格式
                    function processChaptersForMultiAuto(chapters) {
                        return chapters.map(chapter => {
                            // 确保chapter是有效的对象
                            if (!chapter || typeof chapter !== 'object') {
                                return {
                                    label: '未知章节',
                                    value: ''
                                };
                            }
                            
                            // 处理章节标题过长的问题，限制长度
                            let displayTitle = chapter.title || '未知章节';
                            if (displayTitle.length > 20) {
                                displayTitle = displayTitle.substring(0, 20) + '...';
                            }
                            
                            const processedChapter = {
                                label: displayTitle,
                                value: chapter.uid || ''
                            };
                            
                            // 递归处理子章节
                            if (chapter.children && Array.isArray(chapter.children) && chapter.children.length > 0) {
                                processedChapter.children = processChaptersForMultiAuto(chapter.children);
                            }
                            
                            return processedChapter;
                        });
                    }
                    
                    // 开始处理章节数据
                    this.chapterFilterOptions = processChaptersForMultiAuto(res.data);
                    // 章节数据加载完成后，加载知识点和标签
                    this.loadKnowledgeAndLabels();
                } else {
                    // 确保chapterFilterOptions始终是数组
                    this.chapterFilterOptions = [];
                }
            } else {
                // 确保chapterFilterOptions始终是数组
                this.chapterFilterOptions = [];
            }
        }).catch(error => {
            console.error('获取章节数据失败:', error);
            // 发生错误时，确保chapterFilterOptions是数组
            this.chapterFilterOptions = [];
        });
    },
    
    // 加载保存的设置
    loadSavedSettings() {
      // 加载保存的章节
      const savedChapter = uni.getStorageSync('aiEditSelectedChapter')
      if (savedChapter) {
        this.selectedChapter = savedChapter
      }
      
      // 加载保存的难度
      const savedDifficulty = uni.getStorageSync('aiEditSelectedDifficulty')
      if (savedDifficulty) {
        this.selectedDifficulty = savedDifficulty
      }
      
      // 加载保存的知识点
      // 优先加载默认知识点
      const defaultKnowledge = uni.getStorageSync('aiEditDefaultKnowledge')
      if (defaultKnowledge && Array.isArray(defaultKnowledge) && defaultKnowledge.length > 0) {
        this.selectedKnowledge = defaultKnowledge
      } else {
        // 如果没有默认知识点，加载上次选择的知识点
        const savedKnowledge = uni.getStorageSync('aiEditSelectedKnowledge')
        if (savedKnowledge && Array.isArray(savedKnowledge)) {
          this.selectedKnowledge = savedKnowledge
        }
      }
      
      // 加载保存的标签
      // 优先加载默认标签
      const defaultLabels = uni.getStorageSync('aiEditDefaultLabels')
      if (defaultLabels && Array.isArray(defaultLabels) && defaultLabels.length > 0) {
        this.selectedLabels = defaultLabels
      } else {
        // 如果没有默认标签，加载上次选择的标签
        const savedLabels = uni.getStorageSync('aiEditSelectedLabels')
        if (savedLabels && Array.isArray(savedLabels)) {
          this.selectedLabels = savedLabels
        }
      }
    },
    
    // 加载知识点和标签
    loadKnowledgeAndLabels() {
      // 保存当前选中的知识点和标签
      const savedKnowledge = [...this.selectedKnowledge]
      const savedLabels = [...this.selectedLabels]
      
      // 加载知识点数据
      if (this.selectedChapter) {
        this.$api.apiKnowledgeList({ 
          library_uid: this.queryParams.uid, 
          chapter_uid: this.selectedChapter.value 
        }).then(res => {
          if (res && res.code === 1) {
            if (res.data && Array.isArray(res.data)) {
              this.knowledgeList = res.data.map(item => ({
                label: item.title,
                value: item.uid
              }));
              // 重新应用保存的知识点选中状态，只保留存在于新列表中的
              this.reapplyKnowledgeSelection(savedKnowledge)
            } else {
              this.knowledgeList = [];
              this.selectedKnowledge = [];
            }
          } else {
            this.knowledgeList = [];
            this.selectedKnowledge = [];
          }
        }).catch(error => {
          console.error('获取知识点数据失败:', error);
          this.knowledgeList = [];
          this.selectedKnowledge = [];
        });
      } else {
        this.knowledgeList = [];
        this.selectedKnowledge = [];
      }
      
      // 加载标签数据
      this.$api.apiLabelList({ library_uid: this.queryParams.uid }).then(res => {
        if (res && res.code === 1) {
          if (res.data && Array.isArray(res.data)) {
            this.labelList = res.data.map(item => ({
              label: item.title,
              value: item.uid
            }));
            // 重新应用保存的标签选中状态，只保留存在于新列表中的
            this.reapplyLabelSelection(savedLabels)
          } else {
            this.labelList = [];
            this.selectedLabels = [];
          }
        } else {
          this.labelList = [];
          this.selectedLabels = [];
        }
      }).catch(error => {
        console.error('获取标签数据失败:', error);
        this.labelList = [];
        this.selectedLabels = [];
      });
    },
    
    // 重新应用知识点选中状态
    reapplyKnowledgeSelection(savedSelection) {
      if (!savedSelection || savedSelection.length === 0) {
        // 如果没有保存的选择，尝试加载默认设置
        this.loadKnowledgeDefaults()
        return
      }
      
      // 过滤掉不在新列表中的知识点
      const validKnowledgeValues = this.knowledgeList.map(item => item.value)
      this.selectedKnowledge = savedSelection.filter(value => validKnowledgeValues.includes(value))
      
      // 如果过滤后没有选中的，尝试加载默认设置
      if (this.selectedKnowledge.length === 0) {
        this.loadKnowledgeDefaults()
      }
    },
    
    // 重新应用标签选中状态
    reapplyLabelSelection(savedSelection) {
      if (!savedSelection || savedSelection.length === 0) {
        // 如果没有保存的选择，尝试加载默认设置
        this.loadLabelDefaults()
        return
      }
      
      // 过滤掉不在新列表中的标签
      const validLabelValues = this.labelList.map(item => item.value)
      this.selectedLabels = savedSelection.filter(value => validLabelValues.includes(value))
      
      // 如果过滤后没有选中的，尝试加载默认设置
      if (this.selectedLabels.length === 0) {
        this.loadLabelDefaults()
      }
    },
    
    // 加载知识点默认设置
    loadKnowledgeDefaults() {
      const defaultKnowledge = uni.getStorageSync('aiEditDefaultKnowledge')
      if (defaultKnowledge && Array.isArray(defaultKnowledge) && defaultKnowledge.length > 0) {
        const validKnowledgeValues = this.knowledgeList.map(item => item.value)
        this.selectedKnowledge = defaultKnowledge.filter(value => validKnowledgeValues.includes(value))
      } else {
        const savedKnowledge = uni.getStorageSync('aiEditSelectedKnowledge')
        if (savedKnowledge && Array.isArray(savedKnowledge)) {
          const validKnowledgeValues = this.knowledgeList.map(item => item.value)
          this.selectedKnowledge = savedKnowledge.filter(value => validKnowledgeValues.includes(value))
        }
      }
    },
    
    // 加载标签默认设置
    loadLabelDefaults() {
      const defaultLabels = uni.getStorageSync('aiEditDefaultLabels')
      if (defaultLabels && Array.isArray(defaultLabels) && defaultLabels.length > 0) {
        const validLabelValues = this.labelList.map(item => item.value)
        this.selectedLabels = defaultLabels.filter(value => validLabelValues.includes(value))
      } else {
        const savedLabels = uni.getStorageSync('aiEditSelectedLabels')
        if (savedLabels && Array.isArray(savedLabels)) {
          const validLabelValues = this.labelList.map(item => item.value)
          this.selectedLabels = savedLabels.filter(value => validLabelValues.includes(value))
        }
      }
    },
    
    // 章节选择确认
    onChapterSelectConfirm(values) {
      if (values && values.length > 0) {
        this.selectedChapter = values[values.length - 1]
        // 保存到本地存储
        uni.setStorageSync('aiEditSelectedChapter', this.selectedChapter)
        // 重新加载知识点数据
        this.loadKnowledgeAndLabels()
      }
    },
    
    // 选择图片
    chooseImage() {
      uni.chooseImage({
        count: 1,
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success: (res) => {
          this.imageUrl = res.tempFilePaths[0]
          this.recognitionResult = null
        },
        fail: (error) => {
          // 检查是否是用户取消操作导致的错误
          if (error.errMsg && error.errMsg.includes('cancel')) {
            // 用户取消操作，不显示错误信息
          } else {
            // 其他错误，显示错误信息
            uni.showToast({ title: '选择图片失败，请重试', icon: 'none' })
          }
        }
      })
    },
    
    // 删除图片
    deleteImage() {
      this.showDeleteConfirm = true
    },
    
    // 取消删除
    cancelDelete() {
      this.showDeleteConfirm = false
    },
    
    // 确认删除图片
    confirmDeleteImage() {
      this.deleting = true
      
      // 模拟删除操作
      setTimeout(() => {
        this.imageUrl = ''
        this.recognitionResult = null
        this.showDeleteConfirm = false
        this.deleting = false
        
        uni.showToast({
          title: '图片已删除',
          icon: 'success'
        })
      }, 1000)
    },
    
    // 识别试题
    async recognizeQuestion() {
      if (!this.imageUrl) {
        uni.showToast({ title: '请先上传图片', icon: 'none' })
        return
      }
      
      if (!this.selectedChapter) {
        uni.showToast({ title: '请选择章节', icon: 'none' })
        return
      }
      
      this.recognizing = true
      
      try {
        // 先上传图片获取file_id
        const fileId = await this.uploadImage(this.imageUrl)
        
        // 调用豆包大模型API
        const apiResult = await this.callDoubaoAPI(fileId)
        
        // 解析结果
        this.parseRecognitionResult(apiResult.result)
        
        // 保存识别结果详情
        this.recognitionResultDetails = {
          title: this.recognitionResult?.title || '未识别到标题',
          examTypeName: this.recognitionResult?.exam_type_name || '未识别',
          hasOptions: this.recognitionResult?.options && this.recognitionResult.options.length > 0,
          hasAnalysis: !!(this.recognitionResult?.analysis && this.recognitionResult.analysis.trim()),
          matchedKnowledgeCount: this.selectedKnowledge.length,
          tokenUsage: apiResult.usage || {
            input_tokens: 0,
            output_tokens: 0,
            total_tokens: 0
          },
          model: apiResult.model || this.modelName
        }
        
        // 显示识别结果模态框
        this.showRecognitionResultModal = true
      } catch (error) {
        console.error('识别失败:', error)
        uni.showToast({ title: '识别失败，请重试', icon: 'none' })
      } finally {
        this.recognizing = false
      }
    },
    
    // 上传图片
    uploadImage(imageUrl) {
      return new Promise((resolve, reject) => {
        try {
          
          // 调用火山引擎的Files API上传图片获取file_id
          // 🔑 关键修复：修正API URL，使用正确的火山引擎Files API地址
          const uploadUrl = 'https://ark.cn-beijing.volces.com/api/v3/files'
                    
          uni.uploadFile({
            url: uploadUrl,
            filePath: imageUrl,
            name: 'file',
            header: {
              'Authorization': 'Bearer ' + this.apiKey
              // 🔑 关键修复：移除手动设置的 Content-Type 头
              // uni.uploadFile 会自动设置正确的 Content-Type 和 boundary
            },
            formData: {
              'type': 'image',
              'purpose': 'user_data' // 🔑 关键修复：添加 purpose 参数，值必须是 user_data
            },
            success: (uploadRes) => {
              try {

                // 尝试解析响应数据
                const response = JSON.parse(uploadRes.data)
                // 检查不同的响应格式
                if (response.code === 0 && response.data && response.data.file_id) {
                  resolve(response.data.file_id)
                } else if (response.code === 0 && response.id) {
                  // 🔑 关键修复：如果响应格式是 {code: 0, id: 'xxx'}，也视为成功
                  resolve(response.id)
                } else if (response.file_id) {
                  // 🔑 关键修复：如果响应格式直接是 {file_id: 'xxx'}，也视为成功
                  resolve(response.file_id)
                } else if (response.id) {
                  // 🔑 关键修复：如果响应格式直接是 {id: 'xxx'}，也视为成功
                  resolve(response.id)
                } else if (uploadRes.statusCode === 200) {
                  // 🔑 关键修复：如果状态码是 200，但响应格式不符合预期，尝试从响应数据中提取可能的文件ID
                  console.warn('状态码200，但响应格式不符合预期，尝试提取文件ID')
                  // 尝试从响应数据中提取可能的文件ID
                  const fileIdMatch = uploadRes.data.match(/"file_id":"([^"]+)"/)
                  if (fileIdMatch && fileIdMatch[1]) {
                    resolve(fileIdMatch[1])
                  } else {
                    const idMatch = uploadRes.data.match(/"id":"([^"]+)"/)
                    if (idMatch && idMatch[1]) {
                      resolve(idMatch[1])
                    } else {
                      reject(new Error('上传图片失败: 无法从响应中提取文件ID，响应内容: ' + uploadRes.data))
                    }
                  }
                } else {
                  reject(new Error('上传图片失败: ' + (response.message || '未知错误') + '，响应内容: ' + uploadRes.data))
                }
              } catch (error) {
                console.error('解析上传响应失败:', error)
                reject(new Error('解析上传响应失败: ' + error.message + '，响应内容: ' + uploadRes.data))
              }
            },
            fail: (error) => {
              console.error('上传图片失败:', error)
              reject(new Error('上传图片失败: ' + error.errMsg))
            }
          })
        } catch (error) {
          console.error('上传图片过程中发生错误:', error)
          reject(new Error('上传图片过程中发生错误: ' + error.message))
        }
      })
    },
    
    // 调用豆包大模型API
    callDoubaoAPI(fileId) {
      return new Promise((resolve, reject) => {
        const prompt = this.customPrompt || this.defaultPrompt
        
        // 构建请求参数
        // 🔑 关键修复：修正 API 格式，符合火山引擎Ark API要求
        // 参考用户提供的 curl 命令示例
        const requestData = {
          model: this.modelName,
          input: [
            {
              role: "user",
              content: [
                {
                  type: "input_image",
                  file_id: fileId
                },
                {
                  type: "input_text",
                  text: prompt
                }
              ]
            }
          ]
        }
        
        // 实际API调用代码
        uni.request({
          url: this.apiUrl,
          method: 'POST',
          header: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + this.apiKey
          },
          data: requestData,
          success: (res) => {
            
            if (res.statusCode === 200 && res.data) {
              
              // 🔑 关键修复：正确解析响应，提取识别结果
              // 从响应中提取 output 数组
              const output = res.data.output || []
              
              // 找到 type 为 "message" 的对象
              const messageObj = output.find(item => item.type === "message")
              
              if (messageObj && messageObj.content) {
                
                // 找到 content 数组中 type 为 "output_text" 的对象
                const outputTextObj = messageObj.content.find(item => item.type === "output_text")
                
                if (outputTextObj && outputTextObj.text) {                  
                  try {
                    // 解析 text 字段，得到识别结果
                    const result = JSON.parse(outputTextObj.text)
                    
                    // 返回包含结果、usage 和 model 的完整对象
                    resolve({
                      result: result,
                      usage: res.data.usage || {
                        input_tokens: 0,
                        output_tokens: 0,
                        total_tokens: 0
                      },
                      model: res.data.model || this.modelName
                    })
                  } catch (error) {
                    reject(new Error('解析识别结果失败: ' + error.message))
                  }
                } else {
                  // 🔑 关键修复：如果没有找到 output_text 对象，尝试直接使用 message 对象的 content
                  
                  // 尝试直接使用 message 对象的 content
                  if (typeof messageObj.content === 'string') {
                    try {
                      const result = JSON.parse(messageObj.content)
                      
                      // 返回包含结果、usage 和 model 的完整对象
                      resolve({
                        result: result,
                        usage: res.data.usage || {
                          input_tokens: 0,
                          output_tokens: 0,
                          total_tokens: 0
                        },
                        model: res.data.model || this.modelName
                      })
                    } catch (error) {
                      reject(new Error('解析识别结果失败: ' + error.message))
                    }
                  } else {
                    reject(new Error('API调用失败: 响应中没有找到有效的 output_text 或 content'))
                  }
                }
              } else {
                // 🔑 关键修复：如果没有找到 message 对象，尝试直接使用 output 数组的第一个元素
                
                if (output.length > 0) {
                  const firstOutput = output[0]
                  
                  if (firstOutput.content) {                    
                    if (typeof firstOutput.content === 'string') {
                      try {
                        const result = JSON.parse(firstOutput.content)
                        
                        // 返回包含结果、usage 和 model 的完整对象
                        resolve({
                          result: result,
                          usage: res.data.usage || {
                            input_tokens: 0,
                            output_tokens: 0,
                            total_tokens: 0
                          },
                          model: res.data.model || this.modelName
                        })
                      } catch (error) {
                        reject(new Error('解析识别结果失败: ' + error.message))
                      }
                    } else {
                      reject(new Error('API调用失败: 响应中没有找到有效的 content 字符串'))
                    }
                  } else {
                    reject(new Error('API调用失败: 响应中没有找到有效的 content'))
                  }
                } else {
                  reject(new Error('API调用失败: 响应中没有找到 message 或有效的 output'))
                }
              }
            } else {
              reject(new Error('API调用失败: 状态码 ' + (res.statusCode || '未知')))
            }
          },
          fail: (error) => {
            reject(error)
          }
        })
      })
    },
    
    // 解析识别结果
    parseRecognitionResult(result) {
      
      // 获取题干，使用 question_stem 字段，支持更多字段名
      const questionStem = result.question_stem || result.title || result.stem || result.question || ''
      
      // 获取选项，将对象转换为数组，支持更多格式
      let optionsArray = []
      if (result.options) {
        if (typeof result.options === 'object' && result.options !== null && !Array.isArray(result.options)) {
          // 如果 options 是一个对象，将其转换为数组
          optionsArray = Object.entries(result.options).map(([key, value]) => ({
            letter: key,
            content: value
          }))
        } else if (Array.isArray(result.options)) {
          // 如果 options 已经是一个数组，直接使用
          optionsArray = result.options.map((opt, index) => {
            if (typeof opt === 'object' && opt !== null) {
              return {
                letter: opt.letter || opt.key || String.fromCharCode(65 + index),
                content: opt.content || opt.value || opt.text || ''
              }
            }
            return {
              letter: String.fromCharCode(65 + index),
              content: String(opt)
            }
          })
        }
      } else if (result.option) {
        // 兼容 option 字段
        if (Array.isArray(result.option)) {
          optionsArray = result.option.map((opt, index) => ({
            letter: opt.check || String.fromCharCode(65 + index),
            content: opt.title || opt.content || ''
          }))
        }
      }
      
      // 获取正确答案，支持更多字段名
      const correctAnswer = (result.correct_answer || result.answer || result.right_answer || result.correct || '').toUpperCase()
      
      // 提取正确答案中的字母（A-E）
      const cleanedAnswer = correctAnswer.replace(/[^A-E]/g, '')
      const correctAnswerLetters = cleanedAnswer.split('').filter(char => /[A-E]/.test(char))
      const uniqueCorrectAnswers = [...new Set(correctAnswerLetters)]
      
      // 确定题型 - 优先根据正确答案数量判断
      let examType = 1
      let examTypeName = '单选题'
      
      if (optionsArray.length > 0) {
        if (optionsArray.length === 2) {
          // 判断题：只有2个选项
          examType = 3
          examTypeName = '判断题'
        } else if (uniqueCorrectAnswers.length > 1) {
          // 多选题：有多个正确答案
          examType = 2
          examTypeName = '多选题'
        } else if (result.exam_type_name) {
          // 如果AI识别出了题型，且正确答案数量为1，则使用AI识别的题型
          const aiExamTypeName = result.exam_type_name
          if (['单选题', '多选题', '判断题', '填空题', '问答题', '案例题', '完形填空'].includes(aiExamTypeName)) {
            examTypeName = aiExamTypeName
            switch (aiExamTypeName) {
              case '单选题': examType = 1; break
              case '多选题': examType = 2; break
              case '判断题': examType = 3; break
              case '填空题': examType = 4; break
              case '问答题': examType = 5; break
              case '案例题': examType = 6; break
              case '完形填空': examType = 7; break
            }
          }
        } else {
          // 单选题：只有一个正确答案
          examType = 1
          examTypeName = '单选题'
        }
      } else {
        // 没有选项的情况，根据题型名称或默认为问答题
        if (result.exam_type_name) {
          const aiExamTypeName = result.exam_type_name
          if (['填空题', '问答题', '案例题'].includes(aiExamTypeName)) {
            examTypeName = aiExamTypeName
            switch (aiExamTypeName) {
              case '填空题': examType = 4; break
              case '问答题': examType = 5; break
              case '案例题': examType = 6; break
            }
          } else {
            examType = 5
            examTypeName = '问答题'
          }
        } else {
          examType = 5
          examTypeName = '问答题'
        }
      }
      
      // 处理选项数据，根据题型调整
      let processedOptions = optionsArray
      if (examType === 3) {
        // 判断题，确保有正确/错误两个选项
        if (processedOptions.length !== 2) {
          processedOptions = [
            { letter: 'A', content: '正确' },
            { letter: 'B', content: '错误' }
          ]
        }
      }
      
      // 为选项添加唯一ID
      processedOptions = processedOptions.map(opt => ({
        ...opt,
        _id: ++this.optionIdCounter
      }))
      
      // 获取名师点评，支持更多字段名
      let commentaries = result.first_note_content || result.first_note || result.commentaries || result.note || result.notes || result.teacher_comment || ''
      
      // 获取解析，支持更多字段名
      const analysis = result.analysis || result.explanation || result.parse || result.solution || ''
      
      // 如果名师点评为空，自动生成总结性内容
      if (!commentaries || !commentaries.trim()) {
        commentaries = this.generateCommentaries(questionStem, examTypeName, analysis)
      }
      
      // 构建识别结果
      this.recognitionResult = {
        ...result,
        exam_type: examType,
        exam_type_name: examTypeName,
        question_stem: questionStem,
        options: processedOptions,
        title: questionStem,
        analysis: analysis,
        commentaries: commentaries
      }
      
      // 根据题型设置默认分值和积分
      switch (examType) {
        case 1: // 单选题
        case 3: // 判断题
          this.score = 1
          this.integral = 1
          break
        case 2: // 多选题
          this.score = 2
          this.integral = 2
          break
        case 4: // 填空题
        case 5: // 问答题
          this.score = 2
          this.integral = 2
          break
        case 6: // 案例题
        case 7: // 完形填空
          this.score = 5
          this.integral = 5
          break
        default:
          this.score = 1
          this.integral = 1
      }
      
      // 自动匹配知识点
      this.autoMatchKnowledge(this.recognitionResult)
    },
    
    // 生成名师点评（当点评为空时）
    generateCommentaries(questionStem, examTypeName, analysis) {
      const templates = [
        '本题考查基础知识掌握',
        '重点考查核心概念理解',
        '考查综合分析能力',
        '本题需掌握关键要点',
        '考查知识运用能力',
        '本题考查逻辑推理',
        '重点考查判断能力',
        '考查知识记忆理解'
      ]
      
      const examTypeComment = {
        '单选题': '单选题需准确理解',
        '多选题': '多选题需全面分析',
        '判断题': '判断题需仔细辨析',
        '填空题': '填空题需准确记忆',
        '问答题': '问答题需综合阐述',
        '案例题': '案例题需深入分析',
        '完形填空': '完形填空需语境理解'
      }
      
      const typeComment = examTypeComment[examTypeName] || '本题考查综合能力'
      const baseComment = templates[Math.floor(Math.random() * templates.length)]
      
      return `${typeComment}，${baseComment}`.substring(0, 20)
    },
    
    // 自动匹配知识点
    autoMatchKnowledge(result) {
      if (!result || !this.knowledgeList || this.knowledgeList.length === 0) {
        return
      }
      
      // 提取题干、解析和选项中的关键词
      const content = (result.title || '') + ' ' + (result.analysis || '') + ' ' + (result.commentaries || '') + ' ' + this.extractOptionsText(result.options || [])
      const keywords = this.extractKeywords(content)
      
      if (keywords.length === 0) {
        return
      }
      
      // 计算每个知识点的匹配度
      const matchedKnowledge = this.knowledgeList.map(item => {
        const matchScore = this.calculateMatchScore(item.label, keywords)
        return {
          ...item,
          score: matchScore
        }
      })
      
      // 筛选匹配度大于阈值的知识点，按匹配度排序
      const threshold = 0.15 // 降低阈值以提高匹配率
      const sortedKnowledge = matchedKnowledge
        .filter(item => item.score >= threshold)
        .sort((a, b) => b.score - a.score)
      
      // 限制自动匹配的知识点数量
      const maxMatches = 5 // 最多匹配5个知识点
      const selectedItems = sortedKnowledge.slice(0, maxMatches)
      
      // 更新选中的知识点
      if (selectedItems.length > 0) {
        this.selectedKnowledge = selectedItems.map(item => item.value)
        // 保存到本地存储
        uni.setStorageSync('aiEditSelectedKnowledge', this.selectedKnowledge)
      }
    },
    
    // 提取选项文本
    extractOptionsText(options) {
      if (!Array.isArray(options)) return ''
      return options.map(opt => opt.content || '').join(' ')
    },
    
    // 提取关键词
    extractKeywords(text) {
      if (!text) return []
      
      // 去除标点符号和空白字符
      let cleanedText = text.replace(/[\s\p{P}\p{S}]/gu, ' ')
      // 转小写
      cleanedText = cleanedText.toLowerCase()
      // 分词（简单的按空格分词）
      const words = cleanedText.split(' ')
      // 过滤短词和停用词
      const keywords = words
        .filter(word => word.length > 1)
        .filter(word => !STOP_WORDS.has(word))
      
      // 去重并保留出现频率高的词
      const wordCount = {}
      keywords.forEach(word => {
        wordCount[word] = (wordCount[word] || 0) + 1
      })
      
      return Object.keys(wordCount)
        .sort((a, b) => wordCount[b] - wordCount[a])
        .slice(0, 100) // 最多提取100个关键词
    },
    
    // 计算匹配度
    calculateMatchScore(knowledgeLabel, keywords) {
      if (!knowledgeLabel || keywords.length === 0) return 0
      
      const labelLower = knowledgeLabel.toLowerCase()
      let score = 0
      
      // 计算关键词匹配数
      const matchedKeywords = keywords.filter(keyword => labelLower.includes(keyword))
      
      if (matchedKeywords.length > 0) {
        // 基础匹配度：匹配的关键词数占总关键词数的比例
        score = matchedKeywords.length / Math.max(keywords.length, 1) * 0.4
        
        // 增加完全匹配的权重
        const exactMatches = keywords.filter(keyword => labelLower === keyword)
        if (exactMatches.length > 0) {
          score += exactMatches.length * 0.3
        }
        
        // 增加前缀匹配的权重
        const prefixMatches = keywords.filter(keyword => labelLower.startsWith(keyword))
        if (prefixMatches.length > 0) {
          score += prefixMatches.length * 0.2
        }
        
        // 增加包含匹配的权重
        const containsMatches = matchedKeywords.filter(keyword => !exactMatches.includes(keyword) && !prefixMatches.includes(keyword))
        if (containsMatches.length > 0) {
          score += containsMatches.length * 0.1
        }
        
        // 限制匹配度最大值为1
        score = Math.min(score, 1)
      }
      
      return score
    },
    
    // 表单验证
    validateForm() {
      if (!this.recognitionResult) {
        uni.showToast({ title: '请先完成识别', icon: 'none' })
        return false
      }
      
      if (!this.selectedChapter) {
        uni.showToast({ title: '请选择章节', icon: 'none' })
        return false
      }
      
      if (!this.recognitionResult.title || !this.recognitionResult.title.trim()) {
        uni.showToast({ title: '题干不能为空', icon: 'none' })
        return false
      }
      
      if (!this.recognitionResult.analysis || !this.recognitionResult.analysis.trim()) {
        uni.showToast({ title: '解析不能为空', icon: 'none' })
        return false
      }
      
      const examType = this.recognitionResult.exam_type
      const options = this.recognitionResult.options || []
      
      if ([1, 2, 3].includes(examType)) {
        if (options.length === 0) {
          uni.showToast({ title: '请添加选项', icon: 'none' })
          return false
        }
        const emptyOption = options.find(opt => !opt.content || !opt.content.trim())
        if (emptyOption) {
          uni.showToast({ title: '选项内容不能为空', icon: 'none' })
          return false
        }
      }
      
      if (!this.recognitionResult.correct_answer || !this.recognitionResult.correct_answer.trim()) {
        uni.showToast({ title: '请填写正确答案', icon: 'none' })
        return false
      }
      
      return true
    },
    
    // 保存试题
    async handleSave() {
      if (!this.validateForm()) {
        return
      }
      
      this.saving = true
      
      try {
        // 构建试题数据
        const questionData = {
          title: this.cleanTextContent(this.trimContent(this.recognitionResult.title)),
          exam_type: this.recognitionResult.exam_type,
          exam_level: this.selectedDifficulty,
          score: this.score,
          integral: this.integral,
          is_show: this.isShow ? 1 : 0,
          library_uid: this.queryParams.uid,
          chapter_uid: this.selectedChapter.value,
          knowledge_uid: this.selectedKnowledge,
          label_uid: this.selectedLabels,
          analysis: this.cleanTextContent(this.trimContent(this.recognitionResult.analysis || '')),
          commentaries: this.cleanTextContent(this.trimContent(this.recognitionResult.commentaries || ''))
        }
        
        // 处理知识点和标签数据
        if (Array.isArray(questionData.knowledge_uid)) {
          questionData.knowledge_uid = questionData.knowledge_uid.join(',')
        }
        if (Array.isArray(questionData.label_uid)) {
          questionData.label_uid = questionData.label_uid.join(',')
        }
        
        // 根据题型处理选项和答案
        const examType = this.recognitionResult.exam_type
        const options = this.recognitionResult.options || []
        
        switch (examType) {
          case 1: // 单选题
          case 2: // 多选题
          case 3: // 判断题
            // 处理选项数据
            if (options.length > 0) {
              questionData.option = options.map((opt, index) => ({
                check: String.fromCharCode(65 + index),
                title: this.cleanTextContent(this.trimContent(opt.content)),
                is_check: this.recognitionResult.correct_answer && 
                  this.recognitionResult.correct_answer.includes(String.fromCharCode(65 + index)) ? "1" : ""
              }))
              // 构建答案数组
              questionData.answer = questionData.option
                .filter(item => item.is_check === "1")
                .map(item => item.check)
            }
            break
          case 4: // 填空题
            // 填空题的答案直接使用正确答案
            if (this.recognitionResult.correct_answer) {
              questionData.answer = [this.cleanTextContent(this.recognitionResult.correct_answer)]
              questionData.option = [{ title: this.cleanTextContent(this.recognitionResult.correct_answer) }]
            }
            break
          case 5: // 问答题
            // 问答题的答案直接使用正确答案
            if (this.recognitionResult.correct_answer) {
              questionData.answer = this.cleanTextContent(this.recognitionResult.correct_answer)
              questionData.option = [{ title: this.cleanTextContent(this.recognitionResult.correct_answer) }]
            }
            break
          case 6: // 案例题
          case 7: // 完形填空
            // 对于案例题和完形填空，使用原始选项数据
            if (options.length > 0) {
              questionData.option = options.map(opt => ({
                title: this.cleanTextContent(this.trimContent(opt.content))
              }))
              if (this.recognitionResult.correct_answer) {
                questionData.answer = this.cleanTextContent(this.recognitionResult.correct_answer)
              }
            }
            break
        }
        
        // 调用API保存试题
        const res = await this.$api.apiExamQuestionAdd(questionData)
        
        if (res.code === 1) {
          uni.showToast({ title: '保存成功', icon: 'success' })
          // 重置表单
          this.imageUrl = ''
          this.recognitionResult = null
          this.selectedKnowledge = []
          this.selectedLabels = []
        } else {
          // 检查是否是重复试题错误
          if (res.msg && res.msg.includes('相同标题的试题已存在')) {
            this.duplicateMessage = res.msg
            this.pendingQuestionData = questionData
            this.showDuplicateConfirm = true
          } else {
            uni.showToast({ title: res.msg || '保存失败', icon: 'none' })
          }
        }
      } catch (error) {
        uni.showToast({ title: '保存失败，请重试', icon: 'none' })
      } finally {
        this.saving = false
      }
    },
    
    // 取消重复试题确认
    cancelDuplicateConfirm() {
      this.showDuplicateConfirm = false
      this.duplicateMessage = ''
      this.pendingQuestionData = null
    },
    
    // 确认继续添加重复试题
    async confirmDuplicateAdd() {
      this.showDuplicateConfirm = false
      this.saving = true
      
      try {
        // 强制添加重复试题（添加force参数）
        const questionData = { ...this.pendingQuestionData, force: 1 }
        const res = await this.$api.apiExamQuestionAdd(questionData)
        
        if (res.code === 1) {
          uni.showToast({ title: '保存成功', icon: 'success' })
          // 重置表单
          this.imageUrl = ''
          this.recognitionResult = null
          this.selectedKnowledge = []
          this.selectedLabels = []
        } else {
          uni.showToast({ title: res.msg || '保存失败', icon: 'none' })
        }
      } catch (error) {
        uni.showToast({ title: '保存失败，请重试', icon: 'none' })
      } finally {
        this.saving = false
        this.duplicateMessage = ''
        this.pendingQuestionData = null
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";
  
  /* 基础样式 */
  .page {
    background-color: #f5f5f5;
    min-height: 100vh;
  }
  
  /* 章节选择 */
  .chapter-section {
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  }
  
  .selected-chapter {
    padding: 15rpx;
    background-color: #f9f9f9;
    border-radius: 8rpx;
  }
  
  /* 图片上传 */
  .upload-section {
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  }
  
  .upload-area {
    width: 100%;
    height: 400rpx;
    border: 2rpx dashed #ddd;
    border-radius: 10rpx;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .upload-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  
  .uploaded-image {
    width: 100%;
    height: 100%;
  }
  
  /* 识别结果 */
  .result-section {
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
    overflow: visible !important;
    height: auto !important;
    max-height: none !important;
  }
  
  /* 题干和解析文本框样式 */
  .result-item > textarea {
    width: 100% !important;
    min-height: 150rpx !important;
    line-height: 1.6;
    font-size: 28rpx;
    color: #333;
    white-space: pre-wrap;
    word-wrap: break-word;
    word-break: break-all;
    padding: 10rpx !important;
    box-sizing: border-box !important;
  }
  
  /* 解析显示样式 */
  .analysis-display,
  .commentaries-display {
    width: 100%;
    min-height: 200rpx;
    padding: 20rpx;
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
    border-radius: 8rpx;
    margin-top: 10rpx;
    box-sizing: border-box;
  }
  
  .analysis-text,
  .commentaries-text {
    font-size: 28rpx;
    line-height: 1.8;
    color: #333;
    white-space: pre-wrap;
    word-wrap: break-word;
    word-break: break-all;
  }
  
  /* editor富文本编辑器样式 */
  .editor-content {
    width: 100% !important;
    min-height: 300rpx !important;
    height: auto !important;
    padding: 20rpx !important;
    background-color: #fff !important;
    border: 1rpx solid #01BEFF !important;
    border-radius: 8rpx !important;
    margin-top: 10rpx !important;
    box-sizing: border-box !important;
    font-size: 28rpx !important;
    line-height: 1.8 !important;
  }
  
  .analysis-textarea,
  .commentaries-textarea {
    display: block;
    width: 100% !important;
    line-height: 1.8;
    font-size: 28rpx;
    color: #333;
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
    border-radius: 8rpx;
    padding: 20rpx !important;
    box-sizing: border-box !important;
    resize: none;
  }
  
  .analysis-textarea {
    height: 500rpx !important;
    min-height: 500rpx !important;
    max-height: 500rpx !important;
  }
  
  .commentaries-textarea {
    height: 300rpx !important;
    min-height: 300rpx !important;
    max-height: 300rpx !important;
  }
  
  /* 编辑模式下的 textarea 样式 */
  .analysis-textarea-edit {
    width: 100% !important;
    min-height: 200rpx !important;
    line-height: 1.8;
    font-size: 28rpx;
    color: #333;
    background-color: #f9f9f9;
    border: none;
    border-radius: 8rpx;
    padding: 20rpx !important;
    box-sizing: border-box !important;
  }
  
  .analysis-textarea-edit:focus {
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
  }
  
  .commentaries-textarea-edit {
    width: 100% !important;
    min-height: 150rpx !important;
    line-height: 1.8;
    font-size: 28rpx;
    color: #333;
    background-color: #f9f9f9;
    border: none;
    border-radius: 8rpx;
    padding: 20rpx !important;
    box-sizing: border-box !important;
  }
  
  .commentaries-textarea-edit:focus {
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
  }
  
  /* 大高度 textarea 样式（编辑模式使用） */
  .analysis-textarea-large {
    width: 100% !important;
    height: 1000rpx !important;
    line-height: 1.8;
    font-size: 28rpx;
    color: #333;
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
    border-radius: 8rpx;
    padding: 20rpx !important;
    box-sizing: border-box !important;
  }
  
  .commentaries-textarea-large {
    width: 100% !important;
    height: 500rpx !important;
    line-height: 1.8;
    font-size: 28rpx;
    color: #333;
    background-color: #fff;
    border: 1rpx solid #e5e5e5;
    border-radius: 8rpx;
    padding: 20rpx !important;
    box-sizing: border-box !important;
  }
  
  .option-item {
    padding: 20rpx;
    background-color: #f9f9f9;
    border-radius: 12rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
    overflow: visible !important;
    height: auto !important;
    
    &:active {
      background-color: #f0f0f0;
      transform: scale(0.99);
    }
    
    .tn-flex {
      align-items: flex-start;
      overflow: visible !important;
    }
  }
  
  .option-label {
    font-weight: bold;
    margin-right: 16rpx;
    font-size: 32rpx;
    color: #333;
    width: 50rpx;
    text-align: center;
    flex-shrink: 0;
    line-height: 1.5;
    padding-top: 10rpx;
  }
  
  .option-content {
    flex: 1;
    min-width: 0;
    overflow: visible !important;
    
    .option-textarea {
      width: 100% !important;
      min-height: 80rpx !important;
      height: auto !important;
      max-height: none !important;
      line-height: 1.5;
      font-size: 28rpx;
      color: #333;
      white-space: pre-wrap;
      word-wrap: break-word;
      word-break: break-all;
      overflow: visible !important;
      padding: 5rpx 0 !important;
      box-sizing: border-box !important;
    }
  }
  
  /* 保存按钮样式 */
  .save-button {
    padding: 0 30rpx;
  }
  
  /* 筛选标签样式 */
  .filter-item {
    margin-bottom: 20rpx;
  }
  
  .filter-value-tag {
    padding: 10rpx 20rpx;
  }
  
  /* 试题设置样式 */
  .settings-section {
    border-radius: 10rpx;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  }
  
  .setting-item {
    padding: 15rpx;
    background-color: #f9f9f9;
    border-radius: 8rpx;
  }
  
  /* 图片容器样式 */
  .image-container {
    position: relative;
    width: 100%;
    height: 100%;
  }
  
  /* 删除按钮样式 */
  .delete-button {
    position: absolute;
    top: 10rpx;
    right: 10rpx;
    width: 60rpx;
    height: 60rpx;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }
  
  /* 图片预览弹窗样式 */
  .image-preview-container {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
  }
  
  .preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20rpx;
    border-bottom: 1rpx solid #f0f0f0;
  }
  
  .close-button {
    padding: 10rpx;
  }
  
  .preview-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20rpx;
  }
  
  .preview-image {
    max-width: 100%;
    max-height: 100%;
  }
  
  /* 删除确认弹窗样式 */
  .delete-confirm-container {
    padding: 40rpx;
    display: flex;
    flex-direction: column;
  }
  
  .confirm-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 30rpx;
  }
  
  .confirm-content {
    margin-bottom: 40rpx;
  }
  
  .confirm-buttons {
    width: 100%;
  }
  
  /* 识别结果模态框样式 */
  .recognition-result-modal {
    padding: 30rpx;
  }
  
  .modal-section {
    margin-bottom: 30rpx;
  }
  
  .section-title {
    font-size: 28rpx;
    font-weight: bold;
    color: #333;
    margin-bottom: 15rpx;
  }
  
  .section-content {
    font-size: 26rpx;
    color: #666;
    line-height: 36rpx;
  }
  
  .token-details {
    display: flex;
    flex-direction: column;
    gap: 10rpx;
  }
  
  .token-item {
    font-size: 24rpx;
    color: #666;
  }
  
  .modal-buttons {
    margin-top: 40rpx;
  }
  
  .modal-center {
    text-align: center;
    justify-content: center;
  }
  
  .success-text {
    color: #19be6b;
    font-size: 26rpx;
  }
  
  .error-text {
    color: #fa3534;
    font-size: 26rpx;
  }
  
  /* 重复试题确认弹窗样式 */
  .duplicate-confirm-modal {
    padding: 30rpx;
    text-align: center;
  }
  
  .duplicate-confirm-modal .modal-icon {
    margin-bottom: 20rpx;
  }
  
  .duplicate-confirm-modal .modal-message {
    font-size: 28rpx;
    color: #333;
    margin-bottom: 15rpx;
    line-height: 1.5;
  }
  
  .duplicate-confirm-modal .modal-hint {
    margin-bottom: 30rpx;
  }
  
  .duplicate-confirm-modal .modal-buttons {
    display: flex;
    flex-direction: row;
    gap: 20rpx;
    margin-top: 20rpx;
  }
  
  .duplicate-confirm-modal .modal-buttons .tn-button {
    flex: 1;
  }
  
  /* piaoyiEditor 富文本编辑器样式 */
  .piaoyi-editor-wrapper {
    width: 100%;
    margin-top: 10rpx;
  }
</style>