<template>
  <view class="page tn-safe-area-inset-bottom">
    <view v-if="pageLoading" class="loading-container">
      <tn-loading mode="circle" size="80"></tn-loading>
      <text class="tn-margin-top">加载中...</text>
    </view>
    <template v-else>
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
          {{ isCopy ? '复制试题' : questionId ? '编辑试题' : '新增试题' }}
        </text>
      </tn-nav-bar>
    </view>

    <view :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 试题设置 -->
      <view class="settings-section tn-bg-white tn-padding tn-margin-bottom">
        <view class="tn-margin-bottom">
          <text class="tn-text-bold tn-text-lg">试题设置</text>
        </view>
        
        <!-- 题型选择 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">题型：</text>
          <text
            @click="showExamTypeSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ getExamTypeLabel(currentExamType) }}
          </text>
          <tn-select
            v-model="showExamTypeSelect"
            :list="examTypeList"
            :searchShow="false"
            :defaultValue="getExamTypeDefaultValue"
            @confirm="onExamTypeSelectConfirm"
            @cancel="showExamTypeSelect = false"
            title="选择题型"
          />
        </view>
        
        <!-- 章节选择 -->
        <view class="setting-item tn-margin-bottom">
          <text class="tn-text-bold tn-margin-bottom">章节：</text>
          <text
            @click="showChapterSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
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
            {{ getDifficultyLabel(questionForm.exam_level) }}
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
            v-model="questionForm.score"
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
            v-model="questionForm.integral"
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
              <view style="flex: 1; overflow-y: auto;">
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
              </view>
              <view class="tn-margin-top tn-flex tn-flex-row tn-justify-between tn-padding-bottom">
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
              <view style="flex: 1; overflow-y: auto;">
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
              </view>
              <view class="tn-margin-top tn-flex tn-flex-row tn-justify-between tn-padding-bottom">
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
            v-model="questionForm.is_show"
            :active-color="mainColor"
            size="40"
          />
          <text :class="questionForm.is_show ? 'success-text' : 'error-text'" class="tn-margin-left-sm">{{ questionForm.is_show ? '显示' : '隐藏' }}</text>
        </view>
      </view>

      <!-- 题干内容 -->
      <view class="form-section tn-bg-white tn-padding tn-margin-bottom">
        <text class="tn-text-bold tn-text-lg tn-margin-bottom">题干</text>
        <textarea
          v-model="questionForm.title"
          placeholder="请输入题干"
          :auto-height="true"
          :max-height="500"
          class="tn-margin-top-sm textarea-input"
        />
      </view>

      <!-- 题型表单字段 -->
      <view class="form-section tn-bg-white tn-padding tn-margin-bottom">
        <view v-if="currentExamType === 1">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">选项设置</text>
          <tn-radio-group v-model="questionForm.correct_answer">
            <view
              v-for="(option, index) in questionForm.options"
              :key="option._id"
              class="option-item"
            >
              <view class="tn-flex tn-flex-row tn-items-center">
                <tn-radio
                  :name="String.fromCharCode(65 + index)"
                  class="option-radio"
                />
                <text class="option-label">{{ String.fromCharCode(65 + index) }}</text>
                <view class="option-content">
                  <tn-input
                    v-model="option.content"
                    placeholder="请输入选项内容"
                    :border="false"
                  />
                </view>
                <view class="option-delete" @click="removeOption(index)">
                  <text class="icon tn-icon-delete tn-color-danger" />
                </view>
              </view>
            </view>
          </tn-radio-group>
          <tn-button
            type="default"
            size="sm"
            shape="round"
            @click="addOption"
            class="tn-margin-top"
          >
            <text class="icon tn-icon-add tn-margin-right-xs" />
            添加选项
          </tn-button>
        </view>

        <view v-else-if="currentExamType === 2">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">选项设置</text>
          <tn-checkbox-group v-model="questionForm.correct_answer">
            <view
              v-for="(option, index) in questionForm.options"
              :key="option._id"
              class="option-item"
            >
              <view class="tn-flex tn-flex-row tn-items-center">
                <tn-checkbox
                  :name="String.fromCharCode(65 + index)"
                  class="option-checkbox"
                />
                <text class="option-label">{{ String.fromCharCode(65 + index) }}</text>
                <view class="option-content">
                  <tn-input
                    v-model="option.content"
                    placeholder="请输入选项内容"
                    :border="false"
                  />
                </view>
                <view class="option-delete" @click="removeOption(index)">
                  <text class="icon tn-icon-delete tn-color-danger" />
                </view>
              </view>
            </view>
          </tn-checkbox-group>
          <tn-button
            type="default"
            size="sm"
            shape="round"
            @click="addOption"
            class="tn-margin-top"
          >
            <text class="icon tn-icon-add tn-margin-right-xs" />
            添加选项
          </tn-button>
        </view>

        <!-- 判断题 -->
        <view v-else-if="currentExamType === 3">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">正确答案</text>
          <tn-radio-group v-model="questionForm.correct_answer">
            <tn-radio name="A" class="tn-margin-right-md">正确</tn-radio>
            <tn-radio name="B">错误</tn-radio>
          </tn-radio-group>
        </view>

        <!-- 填空题 -->
        <view v-else-if="currentExamType === 5">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">填空答案</text>
          <view
            v-for="(answer, index) in questionForm.correct_answer"
            :key="index"
            class="blank-item tn-margin-bottom"
          >
            <view class="tn-flex tn-flex-row tn-items-center">
              <text class="blank-label tn-margin-right">填空{{ index + 1 }}: </text>
              <tn-input
                v-model="questionForm.correct_answer[index]"
                placeholder="请输入填空答案"
                class="tn-flex-1"
              />
              <text
                class="icon tn-icon-delete tn-color-danger tn-margin-left"
                @click="removeBlank(index)"
              />
            </view>
          </view>
          <tn-button
            type="default"
            size="sm"
            shape="round"
            @click="addBlank"
          >
            <text class="icon tn-icon-add tn-margin-right-xs" />
            添加填空
          </tn-button>
        </view>

        <!-- 问答题 -->
        <view v-else-if="currentExamType === 4">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">参考答案</text>
          <textarea
            v-model="questionForm.correct_answer"
            placeholder="请输入参考答案"
            :auto-height="true"
            :max-height="500"
            class="tn-margin-top-sm textarea-input"
          />
        </view>
        
        <!-- 案例题 -->
        <view v-else-if="currentExamType === 6">
          <text class="tn-text-bold tn-text-lg tn-margin-bottom">案例内容</text>
          <textarea
            v-model="questionForm.case_content"
            placeholder="请输入案例内容"
            :auto-height="true"
            :max-height="500"
            class="tn-margin-top-sm textarea-input"
          />
          
          <text class="tn-text-bold tn-text-lg tn-margin-bottom tn-margin-top">案例问题</text>
          <textarea
            v-model="questionForm.case_question"
            placeholder="请输入案例问题"
            :auto-height="true"
            :max-height="300"
            class="tn-margin-top-sm textarea-input"
          />
          
          <text class="tn-text-bold tn-text-lg tn-margin-bottom tn-margin-top">参考答案</text>
          <textarea
            v-model="questionForm.correct_answer"
            placeholder="请输入参考答案"
            :auto-height="true"
            :max-height="500"
            class="tn-margin-top-sm textarea-input"
          />
        </view>
      </view>

      <!-- 解析内容 -->
      <view class="form-section tn-bg-white tn-padding tn-margin-bottom">
        <text class="tn-text-bold tn-text-lg tn-margin-bottom">解析</text>
        <textarea
          v-model="questionForm.analysis"
          placeholder="请输入解析内容"
          :auto-height="true"
          :max-height="600"
          class="tn-margin-top-sm textarea-input"
        />
      </view>
      
      <!-- 名师点评 -->
      <view class="form-section tn-bg-white tn-padding tn-margin-bottom">
        <text class="tn-text-bold tn-text-lg tn-margin-bottom">名师点评</text>
        <textarea
          v-model="questionForm.commentaries"
          placeholder="请输入名师点评"
          :auto-height="true"
          :max-height="500"
          class="tn-margin-top-sm textarea-input"
        />
      </view>

      <!-- 保存按钮 -->
      <view class="save-section tn-padding tn-margin-bottom">
        <tn-button
          size="lg"
          class="save-button"
          backgroundColor="tn-cool-bg-color-7"
          width="100%"
          fontColor="#ffffff"
          @click="handleSave"
          :loading="saving"
          :disabled="saving"
        >
          {{ saving ? '保存中...' : '保存' }}
        </tn-button>
      </view>
    </view>
    
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
    </template>
  </view>
</template>

<script>
import template_page_mixin from '@/libs/mixin/template_page_mixin.js'

export default {
  name: 'QuestionEdit',
  mixins: [template_page_mixin],
  data() {
    return {
      mainColor: getApp().globalData.mainColor,
      questionId: '',
      isCopy: false,
      currentExamType: 1,
      saving: false,
      pageLoading: true,
      
      queryParams: { uid: '' },
      
      showExamTypeSelect: false,
      
      showChapterSelect: false,
      chapterFilterOptions: [],
      selectedChapter: null,
      
      showDifficultySelect: false,
      difficultyList: [],
      
      // 知识点和标签
      knowledgeList: [],
      labelList: [],
      selectedKnowledge: [],
      selectedLabels: [],
      showKnowledgePopup: false,
      showLabelPopup: false,
      
      // 重复试题确认弹窗
      showDuplicateConfirm: false,
      duplicateMessage: '',
      pendingQuestionData: null,
      
      // 题型列表
      examTypeList: [
        { name: '单选题', value: 1 },
        { name: '多选题', value: 2 },
        { name: '判断题', value: 3 },
        { name: '问答题', value: 4 },
        { name: '填空题', value: 5 },
        { name: '案例题', value: 6 }
      ],
      
      optionIdCounter: 0,
      questionForm: {
        title: '',
        exam_type: 1,
        exam_level: '',
        score: 1,
        integral: 0,
        is_show: 1,
        knowledge: [],
        label: [],
        options: [],
        correct_answer: '',
        case_content: '',
        case_question: '',
        analysis: '',
        commentaries: ''
      }
    }
  },
  computed: {
    getExamTypeDefaultValue() {
      const index = this.examTypeList.findIndex(item => item.value === this.currentExamType);
      return index >= 0 ? [index] : [0];
    },
    
    getChapterDefaultValue() {
      if (!this.selectedChapter || !this.chapterFilterOptions.length) {
        return [];
      }
      return this.findChapterPath(this.chapterFilterOptions, this.selectedChapter.value);
    },
    
    getDifficultyDefaultValue() {
      if (!this.difficultyList.length || !this.questionForm.exam_level) {
        return [];
      }
      const index = this.difficultyList.findIndex(item => String(item.value) === String(this.questionForm.exam_level));
      return index >= 0 ? [index] : [];
    }
  },
  watch: {
    currentExamType(newVal, oldVal) {
      if (newVal !== oldVal && oldVal !== undefined) {
        this.questionForm.exam_type = newVal;
        this.initForm();
      }
    }
  },
  onLoad(options) {
    this.queryParams.uid = options.uid || ''
    this.questionId = options.id || ''
    this.isCopy = options.is_copy === '1'
    this.currentExamType = Number(options.exam_type || 1)
    
    this.initPageData()
  },
  methods: {
    findChapterPath(list, value) {
      for (let i = 0; i < list.length; i++) {
        if (String(list[i].value) === String(value)) {
          return [i];
        }
        if (list[i].children && list[i].children.length) {
          const path = this.findChapterPath(list[i].children, value);
          if (path.length > 0) {
            return [i, ...path];
          }
        }
      }
      return [];
    },
    
    findChapterByUid(list, uid) {
      if (!Array.isArray(list)) return null;
      
      for (const item of list) {
        if (item && String(item.value) === String(uid)) {
          return item;
        }
        if (item && item.children && Array.isArray(item.children) && item.children.length) {
          const found = this.findChapterByUid(item.children, uid);
          if (found) return found;
        }
      }
      return null;
    },
    
    async initPageData() {
      try {
        console.log('开始初始化页面数据');
        // 先加载字典数据
        await this.loadDictData();
        console.log('字典数据加载完成');
        
        if (this.questionId) {
          console.log('开始获取试题详情:', this.questionId);
          await this.fetchQuestionDetail()
          console.log('试题详情获取完成');
          
          // 重新加载章节，使用正确的 library_uid
          console.log('重新加载章节');
          await this.loadChapterOptions();
          console.log('章节重新加载完成');
          
          // 再次匹配章节
          if (this.questionForm.chapter_uid) {
            console.log('再次匹配章节:', this.questionForm.chapter_uid);
            const chapterFromList = this.findChapterByUid(this.chapterFilterOptions, this.questionForm.chapter_uid)
            console.log('再次找到的章节:', chapterFromList);
            this.selectedChapter = { 
              value: this.questionForm.chapter_uid, 
              label: chapterFromList ? chapterFromList.label : (this.questionForm.chapter_name || this.questionForm.chapter_uid) 
            }
            console.log('再次设置的章节:', this.selectedChapter);
          }
          
          await this.loadKnowledgeAndLabels()
          console.log('知识点和标签加载完成');
        } else {
          // 新增试题时，直接加载章节
          await this.loadChapterOptions();
          this.initForm()
          console.log('表单初始化完成');
        }
      } finally {
        this.pageLoading = false
        console.log('页面加载完成');
      }
    },
    
    loadDictData() {
      return this.$api.getDictData({ type: 'exam_level' }).then(res => {
        if (res && res.code === 1) {
          if (res.data && Array.isArray(res.data)) {
            this.difficultyList = res.data.map(item => ({
              label: item.name,
              value: item.value
            }));
            if (this.difficultyList.length > 0 && !this.questionForm.exam_level) {
              const mediumOption = this.difficultyList.find(item => item.label === '中等' || item.value === '2');
              this.questionForm.exam_level = mediumOption ? mediumOption.value : this.difficultyList[0].value;
            }
          }
        }
      }).catch(error => {
        console.error('获取难度数据失败:', error);
      });
    },
    
    loadChapterOptions() {
      // 使用 uid 作为参数，与其他文件保持一致
      const uid = this.questionForm.library_uid || this.queryParams.library_uid || this.queryParams.uid;
      console.log('加载章节选项，uid:', uid);
      return this.$api.apiQuestionChapterList({ uid: uid }).then(res => {
        console.log('章节API返回:', res);
        if (res && res.code === 1) {
          console.log('章节API返回数据:', res.data);
          if (res.data && Array.isArray(res.data)) {
            function processChaptersForMultiAuto(chapters) {
              return chapters.map(chapter => {
                if (!chapter || typeof chapter !== 'object') {
                  return { label: '未知章节', value: '' };
                }
                
                let displayTitle = chapter.title || '未知章节';
                if (displayTitle.length > 20) {
                  displayTitle = displayTitle.substring(0, 20) + '...';
                }
                
                const processedChapter = {
                  label: displayTitle,
                  value: chapter.uid || chapter.chapter_uid || ''
                };
                
                if (chapter.children && Array.isArray(chapter.children) && chapter.children.length > 0) {
                  processedChapter.children = processChaptersForMultiAuto(chapter.children);
                }
                
                return processedChapter;
              });
            }
            
            this.chapterFilterOptions = processChaptersForMultiAuto(res.data);
            console.log('章节选项加载完成:', this.chapterFilterOptions);
          }
        }
      }).catch(error => {
        console.error('获取章节数据失败:', error);
      });
    },
    
    loadKnowledgeAndLabels() {
      const tasks = []
      
      if (this.selectedChapter) {
        tasks.push(
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
              }
            }
          }).catch(error => {
            console.error('获取知识点数据失败:', error);
          })
        )
      }
      
      tasks.push(
        this.$api.apiLabelList({ library_uid: this.queryParams.uid }).then(res => {
          if (res && res.code === 1) {
            if (res.data && Array.isArray(res.data)) {
              this.labelList = res.data.map(item => ({
                label: item.title,
                value: item.uid
              }));
            }
          }
        }).catch(error => {
          console.error('获取标签数据失败:', error);
        })
      )
      
      return Promise.all(tasks)
    },
    
    getExamTypeLabel(value) {
      const examType = this.examTypeList.find(item => item.value === value);
      return examType ? examType.name : '单选题';
    },
    
    onExamTypeSelectConfirm(values) {
      if (values && values.length > 0) {
        this.currentExamType = values[0].value;
      }
    },
    
    getDifficultyLabel(value) {
      const difficulty = this.difficultyList.find(item => item.value === value);
      return difficulty ? difficulty.label : '中等';
    },
    
    // 难度选择确认
    onDifficultySelectConfirm(values) {
      if (values && values.length > 0) {
        this.questionForm.exam_level = values[0].value;
      }
    },
    
    // 章节选择确认
    onChapterSelectConfirm(values) {
      if (values && values.length > 0) {
        this.selectedChapter = values[values.length - 1];
        this.loadKnowledgeAndLabels();
      }
    },
    
    // 知识点复选框组变化
    onKnowledgeGroupChange(value) {
      this.selectedKnowledge = value;
    },
    
    // 标签复选框组变化
    onLabelGroupChange(value) {
      this.selectedLabels = value;
    },
    
    // 确认知识点选择
    confirmKnowledgeSelection() {
      this.showKnowledgePopup = false;
    },
    
    // 确认标签选择
    confirmLabelSelection() {
      this.showLabelPopup = false;
    },
    
    // 返回上一页
    goBack() {
      uni.navigateBack()
    },
    
    createOption(content = '') {
      return { _id: ++this.optionIdCounter, content };
    },
    
    initForm() {
      this.questionForm.exam_type = this.currentExamType
      
      if (this.currentExamType === 1) {
        this.questionForm.correct_answer = 'A'
      } else if (this.currentExamType === 2) {
        this.questionForm.correct_answer = []
      } else if (this.currentExamType === 3) {
        this.questionForm.correct_answer = 'A'
      } else if (this.currentExamType === 4) {
        this.questionForm.correct_answer = ''
      } else if (this.currentExamType === 5) {
        this.questionForm.correct_answer = ['']
      } else if (this.currentExamType === 6) {
        this.questionForm.correct_answer = ''
        this.questionForm.case_content = ''
        this.questionForm.case_question = ''
      }
      
      if ([1, 2].includes(this.currentExamType)) {
        this.questionForm.options = [
          this.createOption(),
          this.createOption(),
          this.createOption(),
          this.createOption()
        ]
      } else {
        this.questionForm.options = []
      }
    },
    
    // 获取试题详情
    async fetchQuestionDetail() {
      try {
        const res = await this.$api.apiQuestionDetail({ uid: this.questionId })
        
        if (res.code === 1 && res.data) {
          const data = res.data
          this.questionForm.title = data.title || ''
          this.questionForm.exam_type = data.exam_type || 1
          this.questionForm.exam_level = data.exam_level || ''
          this.questionForm.score = data.score || 1
          this.questionForm.integral = data.integral || 0
          this.questionForm.is_show = data.is_show || 1
          this.questionForm.analysis = data.analysis || ''
          this.questionForm.commentaries = data.commentaries || ''
          this.questionForm.case_content = data.case_content || ''
          this.questionForm.case_question = data.case_question || ''
          this.questionForm.library_uid = data.library_uid || this.queryParams.library_uid || this.queryParams.uid
          this.questionForm.chapter_uid = data.chapter_uid || ''
          this.questionForm.chapter_name = data.chapter_name || ''
          
          this.currentExamType = data.exam_type
          
          if (data.option) {
            try {
              const options = typeof data.option === 'string' ? JSON.parse(data.option) : data.option
              this.questionForm.options = options.map(opt => 
                this.createOption(opt.title || opt.content || '')
              )
            } catch (e) {
              this.questionForm.options = []
            }
          }
          
          // 处理答案
          if (data.answer) {
            try {
              const answer = typeof data.answer === 'string' ? JSON.parse(data.answer) : data.answer
              this.questionForm.correct_answer = answer
            } catch (e) {
              this.questionForm.correct_answer = data.answer
            }
          }
          
          // 处理知识点
          if (data.knowledge_uid) {
            this.selectedKnowledge = data.knowledge_uid.split(',').filter(uid => uid)
          }
          
          // 处理标签
          if (data.label_uid) {
            this.selectedLabels = data.label_uid.split(',').filter(uid => uid)
          }
          
          if (data.chapter_uid) {
            console.log('章节UID:', data.chapter_uid);
            console.log('章节选项:', this.chapterFilterOptions);
            const chapterFromList = this.findChapterByUid(this.chapterFilterOptions, data.chapter_uid)
            console.log('找到的章节:', chapterFromList);
            this.selectedChapter = { 
              value: data.chapter_uid, 
              label: chapterFromList ? chapterFromList.label : (data.chapter_name || data.chapter_uid) 
            }
            console.log('设置的章节:', this.selectedChapter);
          }
        } else {
          uni.showToast({ title: res.msg || '获取试题详情失败', icon: 'none' })
        }
      } catch (error) {
        console.error('获取试题详情失败:', error)
        uni.showToast({ title: '获取试题详情失败', icon: 'none' })
      }
    },
    
    trimContent(content) {
      if (!content) return content;
      let trimmed = content.replace(/^[\s\n]+|[\s\n]+$/g, '');
      trimmed = trimmed.replace(/^(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>\s*)+/i, '');
      trimmed = trimmed.replace(/(\s*<p>\s*(<br\s*\/?>)?\s*<\/p>)+$/i, '');
      return trimmed;
    },
    
    validateForm() {
      if (!this.questionForm.title || !this.questionForm.title.trim()) {
        uni.showToast({ title: '请输入题干', icon: 'none' })
        return false
      }
      
      if (!this.selectedChapter) {
        uni.showToast({ title: '请选择章节', icon: 'none' })
        return false
      }
      
      if ([1, 2].includes(this.currentExamType)) {
        const hasEmptyOption = this.questionForm.options.some(option => !option.content || !option.content.trim())
        if (hasEmptyOption) {
          uni.showToast({ title: '请完善所有选项内容', icon: 'none' })
          return false
        }
        if (this.questionForm.options.length < 2) {
          uni.showToast({ title: '至少需要2个选项', icon: 'none' })
          return false
        }
      }
      
      if (this.currentExamType === 5) {
        const hasEmptyBlank = this.questionForm.correct_answer.some(ans => !ans || !ans.trim())
        if (hasEmptyBlank) {
          uni.showToast({ title: '请完善所有填空答案', icon: 'none' })
          return false
        }
      }
      
      if ([4, 6].includes(this.currentExamType)) {
        if (!this.questionForm.correct_answer || !this.questionForm.correct_answer.trim()) {
          uni.showToast({ title: '请输入参考答案', icon: 'none' })
          return false
        }
      }
      
      if (this.currentExamType === 6) {
        if (!this.questionForm.case_content || !this.questionForm.case_content.trim()) {
          uni.showToast({ title: '请输入案例内容', icon: 'none' })
          return false
        }
        if (!this.questionForm.case_question || !this.questionForm.case_question.trim()) {
          uni.showToast({ title: '请输入案例问题', icon: 'none' })
          return false
        }
      }
      
      return true
    },
    
    async handleSave() {
      if (!this.validateForm()) return
      
      this.saving = true
      
      try {
        const questionData = {
          title: this.trimContent(this.questionForm.title),
          exam_type: this.currentExamType,
          exam_level: this.questionForm.exam_level,
          score: this.questionForm.score,
          integral: this.questionForm.integral,
          is_show: this.questionForm.is_show ? 1 : 0,
          library_uid: this.queryParams.uid,
          chapter_uid: this.selectedChapter ? this.selectedChapter.value : '',
          knowledge_uid: this.selectedKnowledge.join(','),
          label_uid: this.selectedLabels.join(','),
          analysis: this.trimContent(this.questionForm.analysis || ''),
          commentaries: this.trimContent(this.questionForm.commentaries || '')
        }
        
        if (this.questionId && !this.isCopy) {
          questionData.uid = this.questionId
        }
        
        const examType = this.currentExamType
        const options = this.questionForm.options || []
        
        switch (examType) {
          case 1:
          case 2:
          case 3:
            if (options.length > 0) {
              questionData.option = options.map((opt, index) => ({
                check: String.fromCharCode(65 + index),
                title: this.trimContent(opt.content),
                is_check: this.questionForm.correct_answer && 
                  this.questionForm.correct_answer.includes(String.fromCharCode(65 + index)) ? "1" : ""
              }))
              questionData.answer = questionData.option
                .filter(item => item.is_check === "1")
                .map(item => item.check)
            }
            break
          case 5:
            if (this.questionForm.correct_answer) {
              questionData.answer = Array.isArray(this.questionForm.correct_answer) 
                ? this.questionForm.correct_answer 
                : [this.questionForm.correct_answer]
              questionData.option = questionData.answer.map(ans => ({ title: ans }))
            }
            break
          case 4:
          case 6:
            if (this.questionForm.correct_answer) {
              questionData.answer = this.questionForm.correct_answer
              questionData.option = [{ title: this.questionForm.correct_answer }]
            }
            if (this.questionForm.case_content) {
              questionData.case_content = this.questionForm.case_content
            }
            if (this.questionForm.case_question) {
              questionData.case_question = this.questionForm.case_question
            }
            break
        }
        
        const res = await (this.questionId && !this.isCopy 
          ? this.$api.apiExamQuestionEdit(questionData) 
          : this.$api.apiExamQuestionAdd(questionData))
        
        if (res.code === 1) {
          uni.showToast({ title: '保存成功', icon: 'success' })
          setTimeout(() => {
            uni.navigateBack()
          }, 500)
        } else {
          if (res.msg && res.msg.includes('相同标题的试题已存在')) {
            this.duplicateMessage = res.msg
            this.pendingQuestionData = questionData
            this.showDuplicateConfirm = true
          } else {
            uni.showToast({ title: res.msg || '保存失败', icon: 'none' })
          }
        }
      } catch (error) {
        console.error('保存试题失败:', error)
        uni.showToast({ title: '保存失败', icon: 'none' })
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
        const questionData = { ...this.pendingQuestionData, force: 1 }
        const res = await this.$api.apiExamQuestionAdd(questionData)
        
        if (res.code === 1) {
          uni.showToast({ title: '保存成功', icon: 'success' })
          setTimeout(() => {
            uni.navigateBack()
          }, 500)
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
    },
    
    addOption() {
      this.questionForm.options.push(this.createOption())
    },
    
    // 删除选项
    removeOption(index) {
      if (this.questionForm.options.length > 2) {
        this.questionForm.options.splice(index, 1)
      } else {
        uni.showToast({ title: '至少需要2个选项', icon: 'none' })
      }
    },
    
    // 添加填空
    addBlank() {
      this.questionForm.correct_answer.push('')
    },
    
    // 删除填空
    removeBlank(index) {
      if (this.questionForm.correct_answer.length > 1) {
        this.questionForm.correct_answer.splice(index, 1)
      } else {
        uni.showToast({ title: '至少需要1个填空', icon: 'none' })
      }
    }
  }
}
</script>

<style lang="scss" scoped>
@import "@/scss/custom_nav_bar.scss";

.page {
  background-color: #f5f5f5;
  min-height: 100vh;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #f5f5f5;
}

.settings-section {
  border-radius: 10rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.setting-item {
  padding: 15rpx;
  background-color: #f9f9f9;
  border-radius: 8rpx;
}

.filter-value-tag {
  padding: 10rpx 20rpx;
}

.form-section {
  border-radius: 10rpx;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.textarea-input {
  width: 100%;
  padding: 20rpx;
  background-color: #f9f9f9;
  border-radius: 8rpx;
  min-height: 150rpx;
}

.option-item {
  padding: 20rpx;
  background-color: #f9f9f9;
  border-radius: 12rpx;
  margin-bottom: 20rpx;
  box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.04);
  transition: all 0.2s ease;
  
  &:active {
    background-color: #f0f0f0;
    transform: scale(0.99);
  }
  
  .tn-flex {
    align-items: center;
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
  line-height: 1;
}

.option-content {
  flex: 1;
  min-width: 0;
  
  ::v-deep .tn-input {
    background-color: transparent;
    
    .tn-input__input {
      font-size: 28rpx;
      color: #333;
      height: 60rpx;
      line-height: 60rpx;
      
      &::placeholder {
        color: #999;
      }
    }
  }
}

.option-delete {
  width: 60rpx;
  height: 60rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
  flex-shrink: 0;
  margin-left: 10rpx;
  
  &:active {
    background-color: rgba(250, 53, 52, 0.1);
  }
  
  .icon {
    font-size: 36rpx;
  }
}

.option-checkbox,
.option-radio {
  margin-right: 16rpx;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  
  ::v-deep .tn-checkbox,
  ::v-deep .tn-radio {
    transform: scale(1.1);
  }
}

.blank-item {
  padding: 15rpx;
  background-color: #f9f9f9;
  border-radius: 8rpx;
}

.blank-label {
  font-weight: bold;
}

.save-section {
  padding: 20rpx;
}

.save-button {
  width: 100%;
}

.success-text {
  color: #19be6b;
  font-size: 26rpx;
}

.error-text {
  color: #fa3534;
  font-size: 26rpx;
}

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
</style>
