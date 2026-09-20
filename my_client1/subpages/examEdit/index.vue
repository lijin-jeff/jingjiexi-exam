<template>
  <view class="common-question-container">
    <!-- 自定义导航栏 -->
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
		
		<view class="nav-title tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			试题列表
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <!-- 搜索区域 -->
    <view
      class="search-area"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
	    <!-- 科目切换组件 -->
        <subject-tabs-manager
          :scroll-list="scrollList"
          :current="currentSubjectIndex"
          :visible="showSubjectPopup"
          :my-subjects="myQuestionLibList"
          :all-subjects="questionLibList"
          :loading="loadingQuestionLib"
          :title="'编辑科目'"
          :main-color="mainColor"
          :storage-key="'myQuestionLibList'+selectedCategoryUid"
          :auto-save="true"
          :show-toast="true"
          @tab-change="onSubjectTabChange"
          @manager-click="showSubjectManager"
          @close="showSubjectPopup = false"
          @save="(subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
          @add="(subject, subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
          @remove="(index, subjects) => { saveSubjectsToStorage(subjects); myQuestionLibList = [...subjects]; updateScrollList() }"
        />
		
      <view class="search-box tn-padding-xs tn-bg-white">
        <view class="tn-flex tn-flex-row-center tn-flex-col-center">
          <view class="search-input-wrapper tn-flex tn-flex-row-center tn-flex-col-center">
            <text class="tn-icon-search tn-color-gray tn-margin-right-sm" />
            <input
              v-model="queryParams.keywords"
              type="text"
              placeholder="搜索点什么呢"
              class="search-input"
              confirm-type="search"
              @confirm="search"
            >
			<tn-button 
            size="sm" 
            shape="round"
			padding="30rpx 40rpx"
            backgroundColor="tn-cool-bg-color-9"
            font-color="tn-color-white"
            class="tn-margin-left"
            @click="search"
          >
            搜索
          </tn-button>
          </view>
          
        </view>
      </view>

	  <!-- 筛选和排序容器 -->
      <view class="filter-sort-container tn-flex tn-flex-row-center tn-flex-col-center tn-bg-white tn-padding-xs">
        
        <!-- 题型筛选 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="typeFilter ? 'primary' : 'default'"
            @click="showTypeSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentTypeLabel }}
          </tn-tag>
        </view>
		<!-- 难度筛选 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="levelFilter ? 'primary' : 'default'"
            @click="showLevelSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentLevelLabel }}
          </tn-tag>
        </view>
		<!-- 章节筛选 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="chapterFilter ? 'primary' : 'default'"
            @click="showChapterSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentChapterLabel }}
          </tn-tag>
        </view>
		<!-- 知识点筛选 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="knowledgeFilter ? 'primary' : 'default'"
            @click="showKnowledgeSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentKnowledgeLabel }}
          </tn-tag>
        </view>
		<!-- 排序方式 -->
        <view class="filter-item tn-flex tn-flex-row tn-align-center">
          <tn-tag
            :type="sortBy !== 'default' ? 'primary' : 'default'"
            @click="showSortSelect = true"
            backgroundColor="#F5F7FA"
            class="filter-value-tag"
            fontSize="24"
            shape="round"
            size="sm"
          >
            {{ currentSortLabel }}
          </tn-tag>
        </view>
      </view>
      
      <!-- 筛选和排序选择器 -->
      <tn-select
        v-model="showTypeSelect"
        :searchShow="false"
        :list="typeFilterOptions"
        @confirm="onTypeSelectConfirm"
        @cancel="showTypeSelect = false"
        title="选择题型"
      />
      
      <tn-select
        v-model="showLevelSelect"
        :searchShow="false"
        :list="levelFilterOptions"
        @confirm="onLevelSelectConfirm"
        @cancel="showLevelSelect = false"
        title="选择难度"
      />
      
      <tn-select
        v-model="showChapterSelect"
        mode="multi-auto"
        :list="chapterFilterOptions"
        @confirm="onChapterSelectConfirm"
        @cancel="showChapterSelect = false"
        title="选择章节"
      />
      
      <tn-select
        v-model="showKnowledgeSelect"
        :list="knowledgeFilterOptions"
        @confirm="onKnowledgeSelectConfirm"
        @cancel="showKnowledgeSelect = false"
        title="选择知识点"
      />
      
      <tn-select
        v-model="showSortSelect"
        :searchShow="false"
        :list="sortOptions"
        @confirm="onSortSelectConfirm"
        @cancel="showSortSelect = false"
        title="选择排序方式"
      />
    </view>

	<!-- 添加按钮开始 -->
	<!-- 压屏窗-->
    <tn-landscape :show="show2" @close="closeLandscape" closePosition="bottom" :closeSize="60">
      <view class="tn-flex" style="margin-bottom: 100rpx;padding-top: 46vh;">
        <view class="tn-flex-1 tn-margin-sm tn-radius" @click="navEdit">
          <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
            <view class="icon9__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-red tn-color-white">
              <view class="tn-icon-camera-fill"></view>
            </view>  
            <view class="tn-color-white tn-text-sm tn-text-center">
              <text class="tn-text-ellipsis">AI识别</text>
            </view>
          </view>
        </view>
        <view class="tn-flex-1 tn-margin-sm tn-radius" @click="navCreate">
          <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
            <view class="icon9__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-cyan tn-color-white">
              <view class="tn-icon-add-fill"></view>
            </view>  
            <view class="tn-color-white tn-text-sm tn-text-center">
              <text class="tn-text-ellipsis">创建试题</text>
            </view>
          </view>
        </view>
        <view class="tn-flex-1 tn-margin-sm tn-radius" @click="navBuild">
          <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center">
            <view class="icon9__item--icon tn-flex tn-flex-row-center tn-flex-col-center tn-shadow-blur tn-bg-blue tn-color-white">
              <view class="tn-icon-folder-add-fill"></view>
            </view>  
            <view class="tn-color-white tn-text-sm tn-text-center">
              <text class="tn-text-ellipsis">文本导入</text>
            </view>
          </view>
        </view>
      </view>
    </tn-landscape>
	<view class="edit tnxuanfu" @tap="showLandscape">
      <view class="bg0 pa">
        <view class="bg1">
          <image src="https://datiqiniu.allpp.cn/static/my6.png" class="button-shop shadow"></image>
        </view>
      </view>
      <view class="hx-box pa">
        <view class="pr">
          <view class="hx-k1 pa0">
            <view class="span"></view>
          </view>
          <view class="hx-k2 pa0">
            <view class="span"></view>
          </view>
          <view class="hx-k3 pa0">
            <view class="span"></view>
          </view>
          <view class="hx-k4 pa0">
            <view class="span"></view>
          </view>
          <view class="hx-k5 pa0">
            <view class="span"></view>
          </view>
          <view class="hx-k6 pa0">
            <view class="span"></view>
          </view>
        </view>
      </view>
    </view>
	<!-- 添加按钮结束 -->

    <!-- 题目列表区域 -->
    <view class="question-list-container">
      <view class="tn-padding">
        <block
          v-for="(item, index) in questionList"
          :key="item.uid"
        >
          <view class="question-item tn-bg-white tn-padding tn-margin-bottom tn-border-radius tn-shadow-sm">
            <!-- 题目内容 -->
            <view class="question-content tn-margin-bottom">
              <view class="tn-margin-bottom-sm">
                <view
                  class="title-text"
                  v-html="item.title.replace(/<[^>]*>/g, '')"
                />
              </view>
              <view class="tn-flex tn-align-items-center tn-margin-bottom">
                <view class="=tn-text-sm tn-color-gray">
                  题型:{{ item.exam_type_name }}
                </view>
                <view class="tn-text-sm tn-color-gray tn-margin-left-sm">
                  难度:{{ ['', '简单', '中等', '困难'][item.exam_level] || '未设置' }}
                </view>
                <view class="tn-text-sm tn-color-gray tn-margin-left">
                  分值:{{ item.score }}分
                </view>
                <view :class="['tn-text-sm tn-margin-left-sm', item.is_show === 1 ? 'tn-color-green' : 'tn-color-red']">
                  状态:{{ item.is_show === 1 ? '显示' : '隐藏' }}
                </view>
              </view>
            </view>
            
            <!-- 选项区域 -->
            <view class="question-options tn-margin-bottom">
              <!-- 普通试题选项 -->
              <template v-if="item.exam_type !== 6">
                <view
                  v-for="(v, k) in item.option"
                  :key="k"
                  class="option-item tn-margin-bottom-xs tn-padding-xs tn-border-radius"
                  :class="{'option-correct': v.is_check === '1', 'option-selected': v.is_selected}"
                >
                  <text class="option-check">
                    {{ v.check + `、` }}
                  </text>
                  <view
                    class="option-text"
                    v-html="v.title.replace(/<[^>]*>/g, '')"
                  />
                </view>
              </template>
              
              <!-- 案例题特殊处理 -->
              <template v-else>
                <view
                  v-for="(subQuestion, index) in item.option"
                  :key="index"
                  class="case-sub-question tn-margin-bottom tn-padding tn-bg-white tn-border-radius"
                >
                  <view class="sub-question-title tn-margin-bottom-sm">
                    <text class="tn-text-bold">
                      第{{ index + 1 }}题：
                    </text>
                    <view v-html="subQuestion.title.replace(/<[^>]*>/g, '')" />
                  </view>
                  
                  <!-- 子试题的选项 -->
                  <view class="sub-question-options tn-margin-left">
                    <!-- 普通选择题/判断题 -->
                    <template v-if="subQuestion.exam_type == '1' || subQuestion.exam_type == 1 || subQuestion.exam_type == '2' || subQuestion.exam_type == 2 || subQuestion.exam_type == '3' || subQuestion.exam_type == 3">
                      <view
                        v-for="(childOption, childIndex) in subQuestion.children"
                        :key="childIndex"
                        class="option-item tn-margin-bottom-xs tn-padding-xs tn-border-radius"
                        :class="{'option-correct': childOption.is_check === '1', 'option-selected': childOption.is_selected}"
                      >
                        <text class="option-check">
                          {{ childOption.check + `、` }}
                        </text>
                        <view
                          class="option-text"
                          v-html="childOption.title.replace(/<[^>]*>/g, '')"
                        />
                      </view>
                    </template>
                    
                    <!-- 问答题 - 不显示选项序号 -->
                    <template v-else>
                      <view
                        v-for="(childOption, childIndex) in subQuestion.children"
                        :key="childIndex"
                        class="option-item tn-margin-bottom-xs tn-padding-xs tn-border-radius"
                        :class="{'option-correct': childOption.is_check === '1', 'option-selected': childOption.is_selected}"
                      >
                        <view
                          class="option-text"
                          v-html="childOption.title.replace(/<[^>]*>/g, '')"
                        />
                      </view>
                    </template>
                  </view>
                  
                  <!-- 子试题的答案内容 -->
                  <view class="sub-question-answer tn-margin-top-sm tn-color-gray tn-text-sm">
                    <text class="tn-text-bold">
                      参考答案：
                    </text>
                    <view
                      v-if="subQuestion.answerContent"
                      v-html="subQuestion.answerContent"
                    />
                    <view v-else>
                      {{ subQuestion.answer || '' }}
                    </view>
                  </view>
                </view>
              </template>
            </view>
            
            <!-- 操作按钮组 -->
            <view class="action-buttons tn-flex tn-flex-row-between tn-padding-top-xs tn-border-solids-top">
              <view class="tn-flex tn-flex-row-left">
                <!-- 点赞按钮 -->
                <view
                  class="action-btn"
                  @click="handleLike(item, index)"
                >
                  <text :class="item.is_like ? 'tn-icon-like-fill tn-color-red' : 'tn-icon-like tn-color-gray'" />
                  <text
                    class="action-text"
                    :class="{ 'tn-color-red': item.is_like }"
                  >
                    {{ item.like_count || 0 }}
                  </text>
                </view>
                
                <!-- 收藏按钮 -->
                <view
                  class="action-btn tn-margin-left"
                  @click="questionCollection(item, item.is_collection ? 2 : 1, index)"
                >
                  <text :class="item.is_collection ? 'tn-icon-star-fill tn-color-orangeyellow' : 'tn-icon-star tn-color-gray'" />
                  <text
                    class="action-text"
                    :class="{ 'tn-color-orangeyellow': item.is_collection }"
                  >
                    {{ item.collect_count || 0 }}
                  </text>
                </view>
              </view>
              
              <view class="tn-flex tn-flex-row-right">
                <!-- 解析按钮 -->
                <view
                  class="action-btn"
                  @click="openAnalysisPop(item)"
                >
                  <text class="tn-icon-info tn-color-blue" />
                  <text class="action-text tn-color-blue">
                    查看解析
                  </text>
                </view>
                
                <!-- 操作按钮 -->
                <view
                  class="action-btn"
                  @click="openActionSheet(item)"
                >
                  <text class="tn-icon-edit-form tn-color-grey" />
                  <text class="action-text tn-color-grey">
                    操作
                  </text>
                </view>
              </view>
            </view>
          </view>
        </block>
      </view>
      
      <!-- 空状态 -->
      <view
        v-if="questionList.length === 0"
        class="empty-state tn-padding tn-text-center tn-color-gray"
      >
        <text class="tn-icon-information-circle tn-text-3xl tn-margin-bottom" />
        <text class="tn-block tn-margin-bottom-sm">
          暂无搜索结果
        </text>
        <text class="tn-text-sm">
          请尝试其他关键词
        </text>
      </view>
    </view>

    <!-- 试题解析弹窗 -->
    <tn-popup
      v-model="showExamAnsy"
      mode="bottom"
      width="100%"
      height="70%"
      close-icon-size="60"
      border-radius="20 20 0 0"
      @close="closeAnalysisPop"
    >
      <view class="tn-padding">
        <view class="tn-flex tn-flex-row-between tn-align-items-center tn-margin-bottom">
          <view class="tn-text-lg tn-text-bold">
            {{ showAnalysisTitle }}
          </view>
          <text
            class="tn-icon-close tn-color-gray"
            @click="closeAnalysisPop"
          />
        </view>
        
        <!-- 整合AnalysisSection组件的完整功能 -->
        <view class="analysis-section">
          <!-- 章节信息 -->
          <view class="section-block">
            <view class="section-header">
              <text class="tn-icon-folder tn-color-indigo tn-margin-right-xs" />
              <text class="section-title tn-text-bold tn-text-lg">
                章节信息
              </text>
            </view>
            <view class="content-wrapper tn-padding">
              <view
                v-if="currentQuestion && currentQuestion.chapter"
                class="chapter-info"
              >
                <text class="tn-icon-folder tn-color-indigo tn-margin-right-xs" />
                <text>
                  <!-- 显示完整的父子章节路径 -->
                  <template v-if="currentQuestion.chapter.parent && currentQuestion.chapter.parent.title">
                    {{ currentQuestion.chapter.parent.title }} / 
                  </template>
                  {{ currentQuestion.chapter.title }}
                </text>
              </view>
              <view
                v-else
                class="no-content"
              >
                <text class="tn-color-grey">
                  暂无章节信息
                </text>
              </view>
            </view>
          </view>
          
          <!-- 试题解析 -->
          <view class="section-block">
            <view class="section-header">
              <text class="tn-icon-notebook tn-color-blue tn-margin-right-xs" />
              <text class="section-title tn-text-bold tn-text-lg">
                试题解析
              </text>
            </view>
            <view class="content-wrapper tn-padding">
              <mp-html
                v-if="currentQuestion && currentQuestion.analysis"
                :content="currentQuestion.analysis"
                :lazy-load="true"
              />
              <view
                v-else
                class="no-content"
              >
                <text class="tn-color-grey">
                  暂无解析内容
                </text>
              </view>
            </view>
          </view>
          
          <!-- 名师点评 -->
          <view class="section-block">
            <view class="section-header">
              <text class="tn-icon-teacher tn-color-green tn-margin-right-xs" />
              <text class="section-title tn-text-bold tn-text-lg">
                名师点评
              </text>
            </view>
            <view class="content-wrapper tn-padding">
              <mp-html
                v-if="currentQuestion && currentQuestion.commentaries"
                :content="currentQuestion.commentaries"
                :lazy-load="true"
              />
              <view
                v-else
                class="no-content"
              >
                <text class="tn-color-grey">
                  暂无名师点评
                </text>
              </view>
            </view>
          </view>
          
          <!-- 知识点 -->
          <view class="section-block">
            <view class="section-header">
              <text class="tn-icon-tags tn-color-purple tn-margin-right-xs" />
              <text class="section-title tn-text-bold tn-text-lg">
                知识点
              </text>
            </view>
            <view class="content-wrapper tn-padding">
              <view
				v-if="currentQuestion.Knowledge && currentQuestion.Knowledge.length > 0" 
				class="knowledge-tags"
				>
				<view class="tn-tag-content tn-text-justify">
					<view v-for="(item, index) in currentQuestion.Knowledge" 
					:key="index" 
					class="tn-tag-content__item tn-margin-right tn-round tn-text-sm tn-text-bold" 
					:class="[$tn.color.getRandomColorClass('color', index),$tn.color.getRandomColorClass('bg', index)+'--light']"
					>
					<text class="tn-tag-content__item--prefix">#</text> {{ item.title }}
					</view>
				</view>
				</view>
              <view
                v-else
                class="no-content"
              >
                <text class="tn-color-grey">
                  暂无知识点关联
                </text>
              </view>
            </view>
          </view>
        </view>
      </view>
    </tn-popup>

    <!-- 试题反馈弹窗 -->
    <tn-popup
      v-model="showExamFeek"
      mode="center"
      width="90%"
      close-icon-size="60"
      :showCloseBtn="true"
      border-radius="12"
      @close="closeExamFeek"
    >
      <view class="tn-padding">
        <!-- 表单标题 -->
        <view class="form-header tn-margin-bottom">
          <text class="tn-text-bold tn-text-lg">
            试题纠错
          </text>
          <text class="tn-text-sm tn-color-gray">
            请填写详细纠错信息，以便我们更快验证并解决问题
          </text>
        </view>
        
        <!-- 表单内容 -->
        <tn-form
          ref="errorForm"
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
        </tn-form>
        
        <!-- 提交按钮 -->
        <view class="submit-btn-container tn-margin">
          <tn-button 
            backgroundColor="tn-cool-bg-color-6" 
            font-color="#FFFFFF"
            width="100%"
            :loading="submitting"
            :round="true"
            size="lg"
            @click="submitFeedback"
          >
            {{ submitting ? '提交中...' : '提交' }}
          </tn-button>
        </view>
      </view>
    </tn-popup>
    
    <!-- 操作菜单 -->
    <tn-action-sheet
      v-model="showActionSheet"
      :list="actionSheetList"
      @click="handleActionSheetClick"
      :tips="{ text: '请选择操作' }"
    />
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import ExamDataAdapter from '@/util/examDataAdapter.js'
	import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
	export default {
		name: 'QuestionSearch',
		components: {
			SubjectTabsManager
		},
		mixins: [template_page_mixin],
		data() {
			return {
			showExamFeek: false,
			showExamAnsy: false,
			examAnsy: '', // 试题解析
			showAnalysisTitle: '试题解析', // 弹窗标题
			currentQuestion: {}, // 当前查看解析的题目信息
			currentFeedbackQuestion: {}, // 当前反馈的题目信息
			mainColor: getApp().globalData.mainColor || '#1E88E5', // 默认蓝色背景
			// 科目管理
			showSubjectPopup: false, // 控制科目管理弹窗显示/隐藏
			loadingQuestionLib: false, // 加载状态
			questionLibList: [], // 全部科目列表
			myQuestionLibList: [], // 我的科目列表
			scrollList: [], // 科目切换标签列表
			currentSubjectIndex: 0, // 当前选中的科目索引
			selectedCategoryUid: uni.getStorageSync('selectedCategoryUid'), // 从本地存储中恢复分类ID
			queryParams: {
				page_no: 1,
				page_size: 20,
				keywords: '',
				uid: 0
			},
			questionList: [],
			totalCount: 0, // 总数据量
			isLoading: false, // 是否正在加载
			// 筛选和排序
			typeFilter: '', // 题型筛选
			levelFilter: '', // 难度筛选
			chapterFilter: '', // 章节筛选
			knowledgeFilter: '', // 知识点筛选
			sortBy: 'default', // 排序方式
			// 筛选选项
			typeFilterOptions: [
				{ label: '全部题型', value: '' }
			],
			levelFilterOptions: [
				{ label: '全部难度', value: '' }
			],
			chapterFilterOptions: [
				{ label: '全部章节', value: '' }
			],
			knowledgeFilterOptions: [
				{ label: '全部知识点', value: '' }
			],
			// 排序选项
			sortOptions: [
				{ label: '默认排序', value: 'default' },
				{ label: '收藏数', value: 'collect_count' },
				{ label: '点赞数', value: 'like_count' },
				{ label: '总做题人次', value: 'do_count' },
				{ label: '答对次数', value: 'correct_count' },
				{ label: '正确率', value: 'correct_rate' }
			],
			// 选择器显示状态
			showStatusSelect: false,
			showTypeSelect: false,
			showLevelSelect: false,
			showChapterSelect: false,
			showKnowledgeSelect: false,
			showSortSelect: false,
			// 操作菜单
			showActionSheet: false,
			actionSheetList: [
				{ text: '隐藏/显示', color: '#606266' },
				{ text: '编辑', color: '#409EFF' },
				{ text: '删除', color: '#F56C6C' }
			],
			currentActionItem: null,
			/* 压屏窗*/
			show2: false,
			maskCloseable: true,
			// 反馈表单数据
			model: {
				correction_type: [],
				desc: ''
			},
			// 表单配置
			errorType: 'message',
			labelPosition: 'left',
			labelAlign: 'left',
			checkboxWidth: '100%',
			checkboxWrap: true,
			// 提交状态
			submitting: false,
			// 纠错类型选项
			checkboxList: [
				{ name: '题干错误', disabled: false },
				{ name: '选项错误', disabled: false },
				{ name: '答案错误', disabled: false },
				{ name: '解析错误', disabled: false },
				{ name: '其他错误', disabled: false }
			]
		}
		},
		computed: {
			// 当前选中的题型标签
			currentTypeLabel() {
				const option = this.typeFilterOptions.find(item => item.value === this.typeFilter);
				return option ? option.label : '题型';
			},
			// 当前选中的难度标签
			currentLevelLabel() {
				const option = this.levelFilterOptions.find(item => item.value === this.levelFilter);
				return option ? option.label : '难度';
			},
			// 当前选中的章节标签
			currentChapterLabel() {
				const option = this.chapterFilterOptions.find(item => item.value === this.chapterFilter);
				return option ? option.label : '试题章节';
			},
			// 当前选中的知识点标签
			currentKnowledgeLabel() {
				const option = this.knowledgeFilterOptions.find(item => item.value === this.knowledgeFilter);
				return option ? option.label : '知识点';
			},
			// 当前选中的排序标签
			currentSortLabel() {
				const option = this.sortOptions.find(item => item.value === this.sortBy);
				return option ? option.label : '排序方式';
			}
		},
		onLoad(option) {
			this.queryParams.uid = option.uid || ''
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			// 加载字典数据（题型和难度）
			this.loadDictData();
			// 从本地存储中恢复用户选择的科目
			this.restoreUserSubjects();
		},
		methods: {
			// 返回上一页
			goBack() {
				uni.navigateBack()
			},
			
			// 搜索功能
			search() {
				this.queryParams.page_no = 1
				this.questionList = []
				this.totalCount = 0
				this.fetchExamList()
			},
			// 获取题目列表
			fetchExamList() {
				// 检查是否正在加载或已加载完所有数据
				if (this.isLoading || (this.totalCount > 0 && this.questionList.length >= this.totalCount)) {
					return
				}
				
				this.isLoading = true
				uni.showLoading({
					title: '努力加载中...',
					mask: true
				})
				
				// 构建带有筛选和排序参数的请求参数
				const requestParams = {
					...this.queryParams,
					exam_type: this.typeFilter,
					exam_level: this.levelFilter,
					chapter_uid: this.chapterFilter,
					knowledge_uid: this.knowledgeFilter,
					sort_by: this.sortBy,
					is_edit: true
				}
				
				// 使用正确的API方法名，参考commonQuestion.vue中的API调用方式
				this.$api.apiQuestionSearchList(requestParams).then(res => {
					uni.hideLoading()
					this.isLoading = false
					if (res && res.code === 1) {
						// 确保res.data存在且结构正确
						if (res.data) {
							// 更新总数据量
							this.totalCount = res.data.count || 0
							
							// 确保res.data.lists存在且是数组
							if (res.data.lists && Array.isArray(res.data.lists)) {
								// 处理返回的数据，添加answer_str字段
								const processedList = res.data.lists.map(item => {
									// 使用数据适配器统一处理答案解析
									const answerArray = ExamDataAdapter.normalizeAnswer(item.answer)
																
									// 生成answer_str，去除HTML标签
									item.answer_str = answerArray.map(answer => {
										// 去除HTML标签
										return answer.replace(/<[^>]+>/g, '')
									}).join(',')
																
									// 案例题特殊处理：将答案数组与子试题关联
									if (item.exam_type === 6 && Array.isArray(item.option)) {
										item.option.forEach((subQuestion, index) => {
											// 为每个子试题添加对应的答案
											if (answerArray[index] !== undefined) {
												let subAnswer = answerArray[index]
												// 去除HTML标签
												subAnswer = subAnswer.replace(/<[^>]+>/g, '')
												subQuestion.answer = subAnswer
											} else {
												subQuestion.answer = ''
											}
										})
									}
									
									// 确保is_collection、is_like、like_count、collect_count有默认值
									item.is_collection = item.is_collection || false
									item.is_like = item.is_like || false
									// 如果已经收藏，collect_count至少为1
									item.collect_count = item.collect_count || (item.is_collection ? 1 : 0)
									// 如果已经点赞，like_count至少为1
									item.like_count = item.like_count || (item.is_like ? 1 : 0)
									
									return item
								})
								
								this.questionList.push(...processedList)
								// 只有当还有更多数据时，才增加页码
								if (this.questionList.length < this.totalCount) {
									this.queryParams.page_no += 1
								}
							}
						} else {
							// 处理数据格式错误
							this.$func.showToast('数据格式错误')
						}
					} else {
						this.$func.showToast(res.msg || '请求失败，请稍后重试')
					}
				}).catch(error => {
					uni.hideLoading()
					this.isLoading = false
					console.error('请求错误:', error)
					this.$func.showToast('网络错误，请检查网络连接')
				})
			},
			// 收藏/取消收藏
			async questionCollection(row, action, index) {
				try {
					console.log('收藏操作参数:', { row, action, index });
					// 检查参数是否完整
					if (!row || !row.uid) {
						console.error('收藏操作参数不完整:', { row });
						this.$func.showToast('操作失败，请稍后重试');
						return;
					}
					
					const params = {
						uid: this.queryParams.uid,
						question_uid: row.uid,
						action: action,
						exam_type: row.exam_type || 0,
						exam_type_name: row.exam_type_name || '',
						chapter_uid: row.chapter_uid || '',
						title: row.title || '',
						exam_level: row.exam_level || 0,
						score: row.score || 0,
						correct_answer: row.answer_str || ''
					};
					
					const res = await this.$api.apiQuestionCollection(params);
					
					// 检查res是否存在
					if (res && res.code === 1) {
						this.$func.showToast(res.msg);
						if (this.questionList && this.questionList[index] !== undefined) {
							if (action === 1) {
								this.questionList[index].is_collection = true;
								this.questionList[index].collect_count = (this.questionList[index].collect_count || 0) + 1;
							} else {
								this.questionList[index].is_collection = false;
								this.questionList[index].collect_count = Math.max(0, (this.questionList[index].collect_count || 0) - 1);
							}
						}
					} else {
						this.$func.showToast(res?.msg || '操作失败，请稍后重试');
					}
				} catch (error) {
					console.error('收藏操作失败:', error);
					this.$func.showToast('操作失败，请稍后重试');
				}
			},
			// 显示解析
			openAnalysisPop(row) {
				this.showExamAnsy = true
				this.examAnsy = row.analysis || ''
				this.currentQuestion = {
					...row,
					// 确保knowledge是数组格式
					knowledge: row.knowledge ? (Array.isArray(row.knowledge) ? row.knowledge : [row.knowledge]) : []
				}
			},
			// 关闭解析
			closeAnalysisPop() {
				this.showExamAnsy = false
			},
			// 点赞功能
			async handleLike(row, index) {
				try {
					const res = await this.$api.apiAddQuestionLike({
						library_uid: this.queryParams.uid,
						question_uid: row.uid,
						action: row.is_like ? 2 : 1 // 1点赞，2取消点赞
					})
					if (res.code === 1) {
						if (row.is_like) {
							this.questionList[index].is_like = false
							this.questionList[index].like_count = Math.max(0, (this.questionList[index].like_count || 0) - 1)
						} else {
							this.questionList[index].is_like = true
							this.questionList[index].like_count = (this.questionList[index].like_count || 0) + 1
						}
						this.$func.showToast(res.msg)
					}
				} catch (error) {
					console.error('点赞操作失败:', error)
					this.$func.showToast('操作失败，请稍后重试')
				}
			},
			
			// 科目管理相关方法
			// 显示科目管理弹窗
			showSubjectManager() {
				// 加载科目列表数据
				this.loadQuestionLibList();
				// 显示弹窗
				this.showSubjectPopup = true;
			},
			
			// 科目切换事件处理
			onSubjectTabChange(index) {
				this.currentSubjectIndex = index;
				// 切换科目时，根据新选中的科目更新题库ID
				if (this.myQuestionLibList.length > 0 && index < this.myQuestionLibList.length) {
					this.queryParams.uid = this.myQuestionLibList[index].id || this.myQuestionLibList[index].uid;
					// 加载筛选选项数据
					this.loadFilterOptions();
				}
				// 重置搜索数据状态，确保切换科目后能重新加载数据
				this.queryParams.page_no = 1;
				this.questionList = [];
				this.totalCount = 0;
				// 初始化加载搜索数据
				this.fetchExamList();
			},
			
			// 从本地存储中恢复用户选择的科目
			restoreUserSubjects() {
				// 获取当前选中的分类ID
				const selectedCategoryUid = uni.getStorageSync('selectedCategoryUid');
				if (selectedCategoryUid) {
					// 从本地存储中获取用户选择的科目
					const savedQuestionLibList = uni.getStorageSync('myQuestionLibList' + selectedCategoryUid);
					if (savedQuestionLibList && Array.isArray(savedQuestionLibList) && savedQuestionLibList.length > 0) {
						// 更新我的科目列表
						this.myQuestionLibList = savedQuestionLibList;
						// 更新科目切换标签列表
						this.updateScrollList();
						// 如果有科目，更新题库ID
						if (this.myQuestionLibList[this.currentSubjectIndex]) {
								this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
								// 加载筛选选项数据
								this.loadFilterOptions();
								// 恢复科目后，重新获取搜索数据
								this.fetchExamList();
							}
					} else {
						// 如果本地没有保存的科目，加载默认科目列表
						this.loadQuestionLibList();
					}
				} else {
					// 如果没有选中的分类ID，加载默认科目列表
					this.loadQuestionLibList();
				}
			},
			
			// 保存用户选择的科目到本地存储
			saveSubjectsToStorage(subjects) {
				// 获取当前选中的分类ID
				let selectedCategoryUid = uni.getStorageSync('selectedCategoryUid');
				// 如果没有分类ID，使用默认值或从其他地方获取
				if (!selectedCategoryUid) {
					// 尝试从当前选中的科目ID获取
					selectedCategoryUid = this.myQuestionLibList[0]?.id || this.myQuestionLibList[0]?.uid || 'default';
					// 保存分类ID到本地存储
					uni.setStorageSync('selectedCategoryUid', selectedCategoryUid);
				}
				// 保存用户选择的科目到本地存储
				uni.setStorageSync('myQuestionLibList' + selectedCategoryUid, subjects);
			},
			
			// 加载科目列表
			loadQuestionLibList() {
				// 避免重复请求
				if (this.loadingQuestionLib) {
					return;
				}
				// 加载数据
				this.loadingQuestionLib = true;
				
				// 调用API获取科目列表
				this.$api.apiQuestionLib({
					category_uid: this.selectedCategoryUid,
					is_show: 1,
					page_no: 1,
					page_size: 20
				}).then(res => {
					if (res.code === 1) {
						this.questionLibList = res.data.lists || [];
						// 只有当myQuestionLibList为空时才设置默认科目，否则保留用户选择的科目
						if (this.myQuestionLibList.length === 0) {
							// 设置默认科目为前2个
							this.myQuestionLibList = res.data.lists.slice(0, 2) || [];
							// 更新科目切换标签列表
							this.updateScrollList();
						}
					} else {
						uni.showToast({
							title: res.msg || '获取科目列表失败',
							icon: 'none'
						});
					}
				}).catch(err => {
					console.error('获取科目列表失败:', err);
					uni.showToast({
						title: '网络错误，请重试',
						icon: 'none'
					});
				}).finally(() => {
					this.loadingQuestionLib = false;
				});
			},
			
			// 更新科目切换标签列表
			updateScrollList() {
				// 根据myQuestionLibList生成scrollList
				this.scrollList = this.myQuestionLibList.map(subject => ({
					name: subject.name
				}));
				
				// 如果scrollList为空，添加一个默认项
				if (this.scrollList.length === 0) {
					this.scrollList = [{ name: '无考试科目' }];
				}
				
				// 确保currentSubjectIndex索引在有效范围内
				if (this.currentSubjectIndex >= this.scrollList.length) {
					this.currentSubjectIndex = Math.max(0, this.scrollList.length - 1);
					// 如果有科目，更新题库ID
					if (this.myQuestionLibList[this.currentSubjectIndex]) {
						this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
						// 更新科目后，重新获取搜索数据
						this.fetchExamList();
					}
				}
			},
			
			// 筛选和排序相关方法
			
			// 题型选择确认
			onTypeSelectConfirm(values) {
				if (values && values.length > 0) {
					this.typeFilter = values[0].value;
					this.refreshExamList();
				}
			},
			
			// 难度选择确认
			onLevelSelectConfirm(values) {
				if (values && values.length > 0) {
					this.levelFilter = values[0].value;
					this.refreshExamList();
				}
			},
			
			// 章节选择确认
			onChapterSelectConfirm(values) {
				if (values && values.length > 0) {
					// 对于多列联动模式，取最后一列的值，因为最后一列才是用户最终选择的子章节
					const lastValue = values[values.length - 1];
					this.chapterFilter = lastValue.value;
					
					// 根据选择的章节加载对应的知识点数据
					this.$api.apiKnowledgeList({ 
						library_uid: this.queryParams.uid, 
						chapter_uid: lastValue.value 
					}).then(res => {
						if (res && res.code === 1) {
							// 更新知识点筛选选项
							if (res.data && Array.isArray(res.data)) {
								const knowledgeOptions = res.data.map(item => ({
									label: item.title,
									value: item.uid
								}));
								this.knowledgeFilterOptions = [{ label: '全部知识点', value: '' }, ...knowledgeOptions];
							} else {
								// 如果没有知识点数据，重置为默认选项
								this.knowledgeFilterOptions = [{ label: '全部知识点', value: '' }];
							}
						}
					}).catch(error => {
						console.error('获取知识点数据失败:', error);
						// 发生错误时，重置为默认选项
						this.knowledgeFilterOptions = [{ label: '全部知识点', value: '' }];
					});
					
					this.refreshExamList();
				}
			},
			
			// 知识点选择确认
			onKnowledgeSelectConfirm(values) {
				if (values && values.length > 0) {
					this.knowledgeFilter = values[0].value;
					this.refreshExamList();
				}
			},
			
			// 排序选择确认
			onSortSelectConfirm(values) {
				if (values && values.length > 0) {
					this.sortBy = values[0].value;
					this.refreshExamList();
				}
			},
			
			// 刷新试题列表
			refreshExamList() {
				this.queryParams.page_no = 1;
				this.questionList = [];
				this.totalCount = 0;
				this.fetchExamList();
			},
			
			// 加载筛选选项数据
			loadFilterOptions() {
				// 确保有题库ID
				if (!this.queryParams.uid) {
					return;
				}
				
				// 从服务器获取章节数据
				this.$api.apiQuestionChapterList({ uid: this.queryParams.uid }).then(res => {
					if (res && res.code === 1) {
						// 更新章节筛选选项
						if (res.data && Array.isArray(res.data)) {
							// 处理章节数据，转换为多列联动模式所需的格式
							function processChaptersForMultiAuto(chapters) {
								return chapters.map(chapter => {
									// 处理章节标题过长的问题，限制长度
									let displayTitle = chapter.title;
									if (displayTitle.length > 20) {
										displayTitle = displayTitle.substring(0, 20) + '...';
									}
									
									const processedChapter = {
										label: displayTitle,
										value: chapter.uid
									};
									
									// 递归处理子章节
									if (chapter.children && chapter.children.length > 0) {
										processedChapter.children = processChaptersForMultiAuto(chapter.children);
									}
									
									return processedChapter;
								});
							}
							
							// 开始处理章节数据
							this.chapterFilterOptions = processChaptersForMultiAuto(res.data);
						}
					}
				}).catch(error => {
					console.error('获取章节数据失败:', error);
				});
				
				// 知识点数据将在选择章节后加载
				// 初始状态下不加载知识点数据，保持默认选项
				this.knowledgeFilterOptions = [{ label: '全部知识点', value: '' }];
			},
			
			// 显示操作菜单
			openActionSheet(item) {
				this.currentActionItem = item;
				this.showActionSheet = true;
			},
			
			// 处理操作菜单点击
		handleActionSheetClick(index) {
			const action = this.actionSheetList[index].text;
			const item = this.currentActionItem;
			
			switch (action) {
				case '隐藏/显示':
					this.handleHideQuestion(item);
					break;
				case '编辑':
					this.handleEditQuestion(item);
					break;
				case '添加':
					this.handleAddQuestion();
					break;
				case '删除':
					this.handleDeleteQuestion(item);
					break;
			}
			
			this.showActionSheet = false;
			this.currentActionItem = null;
		},
		
		// 处理编辑试题
		handleEditQuestion(item) {
			if (!item || !item.uid) {
				this.$func.showToast('参数错误');
				return;
			}
			uni.navigateTo({
				url: '/subpages/examEdit/edit?uid=' + this.queryParams.uid + '&id=' + item.uid + '&exam_type=' + (item.exam_type || 1)
			});
		},
		
		// 处理添加试题
		handleAddQuestion() {
			uni.navigateTo({
				url: '/subpages/examEdit/edit?uid=' + this.queryParams.uid
			});
		},
		// 处理隐藏/显示试题
		handleHideQuestion(item) {
			console.log('item：', item);
			if (!item || !item.uid) {
				this.$func.showToast('参数错误');
				return;
			}
			
			// 确定目标状态：当前显示则隐藏，当前隐藏则显示
			const targetShowStatus = item.is_show === 1 ? 0 : 1;
			const actionText = targetShowStatus === 0 ? '隐藏' : '显示';
			
			uni.showLoading({ title: '处理中...' });
			
			this.$api.apiExamQuestionHide({
				uid: item.uid,
				is_show: targetShowStatus
			}).then(res => {
				uni.hideLoading();
				if (res && res.code === 1) {
					this.$func.showToast(actionText + '成功');
					// 更新本地数据列表
					const index = this.questionList.findIndex(q => q.uid === item.uid);
					if (index !== -1) {
						// 更新状态而不是移除
						this.questionList[index].is_show = targetShowStatus;
					}
				} else {
					this.$func.showToast(actionText + '失败');
				}
			}).catch(error => {
				uni.hideLoading();
				console.error(actionText + '试题失败:', error);
				this.$func.showToast('网络错误，请稍后重试');
			});
		},
		// 处理删除试题
		handleDeleteQuestion(item) {
			if (!item || !item.uid) {
				this.$func.showToast('参数错误');
				return;
			}
			
			// 显示确认弹窗
			uni.showModal({
				title: '确认删除',
				content: '确定要删除这道试题吗？删除后不可恢复。',
				confirmText: '确定',
				cancelText: '取消',
				confirmColor: '#F56C6C',
				success: (res) => {
					if (res.confirm) {
						uni.showLoading({ title: '处理中...' });
						
						this.$api.apiExamQuestionDelete({
							uid: item.uid
						}).then(res => {
							uni.hideLoading();
							if (res && res.code === 1) {
								this.$func.showToast('删除成功');
								// 更新本地数据列表
								const index = this.questionList.findIndex(q => q.uid === item.uid);
								if (index !== -1) {
									this.questionList.splice(index, 1);
								}
							} else {
								this.$func.showToast(res.msg || '删除失败');
							}
						}).catch(error => {
							uni.hideLoading();
							console.error('删除试题失败:', error);
							this.$func.showToast('网络错误，请稍后重试');
						});
					}
				}
			});
		},
			// 弹出压屏窗
			showLandscape() {
				this.openLandscape()
			},
			// 打开压屏窗
			openLandscape() {
				// wx.vibrateLong();
				this.show2 = true
			},
			// 关闭压屏窗
			closeLandscape() {
				this.show2 = false
			},
			
			// 加载字典数据（题型和难度）
			loadDictData() {
				// 获取题型数据
				this.$api.getDictData({ type: 'exam_type' }).then(res => {
					if (res && res.code === 1) {
						if (res.data && Array.isArray(res.data)) {
							const typeOptions = res.data.map(item => ({
								label: item.name,
								value: item.value
							}));
							this.typeFilterOptions = [{ label: '全部题型', value: '' }, ...typeOptions];
						}
					}
				}).catch(error => {
					console.error('获取题型数据失败:', error);
				});
				
				// 获取难度数据
				this.$api.getDictData({ type: 'exam_level' }).then(res => {
					if (res && res.code === 1) {
						if (res.data && Array.isArray(res.data)) {
							const levelOptions = res.data.map(item => ({
								label: item.name,
								value: item.value
							}));
							this.levelFilterOptions = [{ label: '全部难度', value: '' }, ...levelOptions];
						}
					}
				}).catch(error => {
					console.error('获取难度数据失败:', error);
				});
			},
			
			// 导航到AI识别页面
			navEdit() {
				uni.navigateTo({
					url: '/subpages/examEdit/AiEdit?uid=' + this.queryParams.uid
				});
			},
			
			// 导航到创建试题页面
			navCreate() {
				uni.navigateTo({
					url: '/subpages/examEdit/edit?uid=' + this.queryParams.uid
				});
			},
			
			// 导航到文本导入页面
			navBuild() {
				// 这里可以导航到文本导入页面
				uni.showToast({ title: '文本导入功能待实现', icon: 'none' });
			},
			
			// 复选框组变化
			checkboxGroupChange(e) {
				this.model.correction_type = e.detail.value;
			},
			
			// 提交反馈
			submitFeedback() {
				if (!this.model.correction_type || this.model.correction_type.length === 0) {
					uni.showToast({ title: '请选择纠错类型', icon: 'none' });
					return;
				}
				
				if (!this.model.desc.trim()) {
					uni.showToast({ title: '请输入纠错内容', icon: 'none' });
					return;
				}
				
				this.submitting = true;
				
				try {
					// 这里可以添加提交反馈的API调用
					// 模拟API调用
					setTimeout(() => {
						this.submitting = false;
						uni.showToast({ title: '提交成功', icon: 'success' });
						this.showExamFeek = false;
						this.model = {
							correction_type: [],
							desc: ''
						};
					}, 1000);
				} catch (error) {
					console.error('提交反馈失败:', error);
					this.submitting = false;
					uni.showToast({ title: '提交失败，请重试', icon: 'none' });
				}
			}
		},
		onReachBottom() {
			this.fetchExamList()
		},
		
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.common-question-container {
		width: 100%;
		height: 100vh;
		background-color: #F8F9FA;
	}

	.nav-title {
		flex: 1;
		text-align: center;
	}

	/* 搜索区域样式 */
	.search-area {
		width: 100%;
		background-color: #ededed;
	}

	.search-box {
		box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
	}

	.search-input-wrapper {
		flex: 1;
		background-color: #ededed;
		border-radius: 50rpx;
		padding-left: 30rpx;
		height: 65rpx;
		box-sizing: border-box;
	}

	.search-input {
		flex: 1;
		font-size: 28rpx;
		background-color: transparent;
		border: none;
		outline: none;
		color: #333;
		height: 50rpx;
		line-height: 50rpx;
	}

	.search-input::placeholder {
		color: #999;
	}

	/* 题目列表样式 */
	.question-list-container {
		width: 100%;
		background-color: #F8F9FA;
		min-height: calc(100vh - 200rpx);
	}

	.question-item {
		transition: all 0.3s ease;
	}

	/* 移除:active伪类，使用小程序内置的hover-*属性代替 */

	/* 选项样式 */
	.option-item {
		transition: all 0.2s ease;
		background-color: #F8F9FA;
		display: flex;
		align-items: flex-start;
		flex-direction: row;
	}

	.option-correct {
		background-color: #E6F7EF;
		border: 1px solid #4CAF50;
	}

	.option-selected {
		background-color: #E3F2FD;
		border: 1px solid #2196F3;
	}

	.option-check {
		font-weight: bold;
		margin-right: 10rpx;
		flex-shrink: 0;
		width: 40rpx;
	}

	.option-text {
		flex: 1;
		word-break: break-word;
	}

	/* 案例题样式 */
	.case-sub-question {
		border: 1px solid #E6F7EF;
		background-color: #F0F9F4;
	}

	.sub-question-title {
		font-weight: bold;
		color: #2E7D32;
	}

	.sub-question-options {
		margin-left: 20rpx;
	}

	.sub-question-answer {
		padding: 10rpx;
		background-color: #E6F7EF;
		border-radius: 4rpx;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		display: flex;
		align-items: center;
	}

	/* 确保参考答案在一行显示 */
	.sub-question-answer view {
		display: inline;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		flex: 1;
	}

	/* 确保参考答案文本在一行显示 */
	.sub-question-answer text {
		display: inline;
		white-space: nowrap;
	}

	/* 针对第一个子试题的特殊处理 */
	.case-sub-question:first-child .sub-question-answer {
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	/* 操作按钮样式 */
	.action-buttons {
		margin-top: 10rpx;
	}

	.action-btn {
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 10rpx 20rpx;
		border-radius: 50rpx;
		transition: all 0.2s ease;
	}

	/* 移除:active伪类，使用小程序内置的hover-*属性代替 */

	.action-text {
		font-size: 24rpx;
		margin-left: 5rpx;
	}

	/* 空状态样式 */
	.empty-state {
		padding: 100rpx 0;
	}

	/* 底部安全边距 */
	.tn-tabbar-height {
		min-height: 30rpx;
		height: calc(40rpx + env(safe-area-inset-bottom) / 2);
		height: calc(40rpx + constant(safe-area-inset-bottom));
	}

	/* 确保导航栏显示 */
	::v-deep .tn-nav-bar {
		height: 88rpx;
		line-height: 88rpx;
	}

	::v-deep .tn-nav-bar__content {
		height: 88rpx;
		line-height: 88rpx;
	}
	
	/* 解析弹窗样式 */
	.analysis-section {
		background-color: #F8F9FA;
		overflow: hidden;
		max-height: calc(100% - 100rpx);
		overflow-y: auto;
		-webkit-overflow-scrolling: touch;
	}
	
	.section-block {
		background-color: #FFFFFF;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.03);
		overflow: hidden;
		transition: all 0.3s ease;
	}
	
	.section-header {
		background-color: #F8F9FA;
		padding: 20rpx 30rpx;
		border-bottom: 1rpx solid #E9ECEF;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	
	.section-title {
		color: #333333;
		font-size: 34rpx;
		flex: 1;
	}
	
	.content-wrapper {
		padding: 20rpx;
		background-color: #FFFFFF;
	}
	
	/* 富文本样式优化 */
	.mp-html img {
		max-width: 100% !important;
		height: auto !important;
		border-radius: 8rpx;
		margin: 16rpx 0;
	}
	
	.mp-html p {
		margin-bottom: 16rpx;
		line-height: 1.6;
		color: #333333;
	}
	
	.mp-html pre {
		background-color: #f5f5f5;
		padding: 16rpx;
		border-radius: 8rpx;
		overflow-x: auto;
		-webkit-overflow-scrolling: touch;
		font-size: 28rpx;
		margin: 16rpx 0;
	}
	
	/* 空内容样式 */
	.no-content {
		padding: 40rpx 0;
		text-align: center;
		color: #999;
		font-style: italic;
	}
	
	/* 章节信息样式 */
	.chapter-info {
		font-size: 32rpx;
		color: #333;
		padding: 16rpx 0;
		display: flex;
		align-items: center;
		line-height: 1.5;
	}
	
  /* 标签内容 start*/
  .tn-tag-content {
    &__item {
      display: inline-block;
      line-height: 45rpx;
      padding: 5rpx 20rpx;
	  margin: 10rpx;
      
      &--prefix {
        padding-right: 10rpx;
      }  
    }
  }
  /* 标签内容 end*/
  
  /* 反馈表单样式 */
  .form-header {
    padding: 10rpx 0;
    
    > text:first-child {
      display: block;
      margin-bottom: 10rpx;
    }
  }
  
  .submit-btn-container {
    padding-top: 0rpx;
  }
  
  /* 优化textarea样式 */
  .tn-input--textarea {
    min-height: 150rpx;
    max-height: 250rpx;
    border-radius: 12rpx;
  }
  
  .comment-textarea {
    width: 100%;
    height: 150rpx;
    resize: none;
    overflow: hidden;
    font-size: 28rpx;
    line-height: 1.5;
  }
  
  /* 优化表单间距 */
  .tn-form-item {
    margin: 0 0 20rpx 0;
    
    &__label {
      font-weight: bold;
    }
  }

    /* 筛选和排序容器 */
  .filter-sort-container {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
  }
  
  /* 筛选项样式 */
  .filter-item {
    display: flex;
    flex-direction: row;
    align-items: center;
  }
  
  /* 筛选标签样式 */
  .filter-label {
    font-size: 28rpx;
    color: #606266;
    white-space: nowrap;
  }
  
  /* 筛选值标签样式 */
  .filter-value-tag {
    margin: 0;
    padding: 12rpx 24rpx;
    font-size: 28rpx;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 120rpx;
  }
  
  .filter-value-tag.hover-class {
    opacity: 0.8;
  }


    /* 图标容器9 start */
  .icon9 {
    &__item {
      width: 30%;
      background-color: #FFFFFF;
      border-radius: 10rpx;
      padding: 30rpx;
      margin: 20rpx 10rpx;
      transform: scale(1);
      transition: transform 0.3s linear;
      transform-origin: center center;
      
      &--icon {
        width: 110rpx;
        height: 110rpx;
        font-size: 65rpx;
        border-radius: 50%;
        margin: 20rpx 40rpx;
        position: relative;
        z-index: 1;
        
        &::after {
          content: " ";
          position: absolute;
          z-index: -1;
          width: 100%;
          height: 100%;
          left: 0;
          bottom: 0;
          border-radius: inherit;
          opacity: 1;
          transform: scale(1, 1);
          background-size: 100% 100%;
          background-image: url(https://datiqiniu.allpp.cn/static/icon_bg5.png);
        }
      }
    }
  }
  

  /* 按钮 */
  .edit {
    bottom: 300rpx;
    right: 75rpx;
    position: fixed;
    z-index: 9999;
  }
  
  
  .pa,
  .pa0 {
    position: absolute
  }
  
  .pa0 {
    left: 0;
    top: 0
  }
  
  
  .bg0 {
    width: 100rpx;
    height: 100rpx;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  }
  
  .bg1 {
    width: 100%;
    height: 100%;
  }

  .hx-box {
    top: 50%;
    left: 50%;
    width: 100rpx;
    height: 100rpx;
    transform-style: preserve-3d;
    transform: translate(-50%, -50%) rotateY(75deg) rotateZ(10deg);
  }
  
  .hx-box .pr {
    width: 100rpx;
    height: 100rpx;
    transform-style: preserve-3d;
    animation: hxz 20s linear infinite;
  }
  
  @keyframes hxz {
    0% {
      transform: rotateX(0deg);
    }
  
    100% {
      transform: rotateX(-360deg);
    }
  }

  .hx-box .pr .pa0 {
    width: 100rpx;
    height: 100rpx;
    /* border: 4px solid #5ec0ff; */
    border-radius: 1000px;
  }
  
  .hx-box .pr .pa0 .span {
    display: block;
    width: 100%;
    height: 100%;
    background: url(https://datiqiniu.allpp.cn/static/arc4.png) no-repeat center center;
    background-size: 100% 100%;
    animation: hx 4s linear infinite;
  }
  
  @keyframes hx {
    to {
      transform: rotate(360deg);
    }
  }
  
  .hx-k1 {
    transform: rotateX(-60deg) rotateZ(-60deg)
  }
  
  .hx-k2 {
    transform: rotateX(-30deg) rotateZ(-30deg)
  }
  
  .hx-k3 {
    transform: rotateX(0deg) rotateZ(0deg)
  }
  
  .hx-k4 {
    transform: rotateX(30deg) rotateZ(30deg)
  }
  
  .hx-k5 {
    transform: rotateX(60deg) rotateZ(60deg)
  }
  
  .hx-k6 {
    transform: rotateX(90deg) rotateZ(90deg)
  }

  /* 悬浮 */
  .tnxuanfu{
    animation: suspension 3s ease-in-out infinite;
  }
  
  @keyframes suspension {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-0.8rem);
    }
  }
  /* 悬浮按钮 */
  .button-shop {
    width: 90rpx;
    height: 90rpx;
    display: flex;
    flex-direction: row;
    position: fixed;
    left: 5rpx;
    top: 5rpx;
    z-index: 1001;
    border-radius: 100px;
    opacity: 0.9;
  }
  
  .pa {
    position: absolute
  }

</style>

