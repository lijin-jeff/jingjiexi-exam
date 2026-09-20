<template>
  <view class="common-question-container">
    <!-- 自定义导航栏 -->
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
      <tn-nav-bar fixed alpha customBack>
      <view
        slot="back"
        class="tn-custom-nav-bar__back"
      >
        <text
          class="icon tn-icon-left"
          @click="handleGoBack"
        />
        <text
          class="icon tn-icon-home-capsule-fill"
          @click="handleGoHome"
        />
      </view>
      
      <view class="nav-title tn-flex tn-flex-col-center tn-flex-row-center">
        <text class="tn-text-bold tn-text-xl tn-color-white">
          {{ currentMode === 'normal' ? '答题模式' : currentMode === 'reviewOnly' ? '背题模式' : '学练结合' }}
        </text>
      </view>
      
      <view
        slot="right"
        class="nav-right"
      >
        <text
          class="tn-icon-setting tn-color-white"
          @click="handleModeSwitch"
        />
      </view>
    </tn-nav-bar>
    </view>

    <!-- 页面加载动画 -->
    <view v-if="isPageLoading" class="page-loading-overlay">
      <view class="loading-content">
        <tn-loading 
          mode="circle"
          :show="true"
          text="加载题目中..."
          :size="60"
        />
      </view>
    </view>

    <!-- 题目区域 -->
    <view
      class="question-area"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
      :class="{ 'content-loaded': isContentLoaded }"
      v-show="!isPageLoading"
    >
      <!-- 答题进度 -->
      <view 
        class="progress-container tn-padding-top tn-padding-left tn-padding-right tn-padding-bottom tn-bg-white"
        :key="`progress-${displayQuestionIndex}`"
      >
        <view class="tn-flex-row tn-justify-content-between tn-align-items-center tn-margin-bottom-xs">
          <text class="tn-text-sm tn-color-gray">答题进度</text>
          <text class="tn-text-sm">
            {{ displayQuestionIndex + 1 }}/{{ totalQuestionCount || swiperList.length || 0 }}
          </text>
        </view>
        <tn-line-progress 
          :percent="(totalQuestionCount || swiperList.length) > 0 ? (displayQuestionIndex + 1) / (totalQuestionCount || swiperList.length) * 100 : 0"
          stroke-width="6"
          :activeColor="mainColor"
          background-color="#e5e7eb"
          :key="`progress-bar-${displayQuestionIndex}`"
        />
      </view>
      <!-- 单题显示模式 - 带滑动动画 -->
      <view v-if="currentQuestion" class="question-animation-container">
        <!-- 当前题目 -->
        <view 
          v-show="true"
          class="question-item"
        >
          <scroll-view
            scroll-y
            class="question-scroll-view"
          >
            <view class="question-content" @touchstart="handleSimpleTouchStart" @touchmove="handleSimpleTouchMove" @touchend="handleSimpleTouchEnd">
              <!-- 题干区域（包含点赞、收藏、分享按钮） -->
              <view class="question-header tn-bg-white tn-padding-md tn-radius-lg tn-shadow-sm">
                <!-- 题目内容会在具体题型组件中显示 -->
                
                <!-- 点赞、收藏、分享\纠错按钮组 -->
                <view class="action-buttons tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center tn-justify-content-between" style="width: 100%;">
                  <!-- 左侧按钮：点赞、收藏 -->
                  <view class="tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center">
                    <!-- 点赞按钮 -->
                    <view class="action-btn tn-margin-left-sm tn-padding-sm tn-radius-md tn-transition-all"  @click="handleLike(currentQuestion)" :class="{ 'tn-bg-red-50': currentQuestion.is_like }">
                      <text :class="currentQuestion.is_like ? 'tn-icon-like-fill tn-color-red tn-font-lg' : 'tn-icon-like tn-color-gray tn-font-md'" />
                      <text  class="action-text tn-text-xs tn-margin-left-xxs" :class="{ 'tn-color-red': currentQuestion.is_like }">
                        {{ currentQuestion.like_count || 0 }}
                      </text>
                    </view>
                    
                    <!-- 收藏按钮 -->
                    <view  class="action-btn tn-margin-right-sm tn-padding-sm tn-radius-md tn-transition-all"  @click="handleCollect(currentQuestion)" :class="{ 'tn-bg-orangeyellow-50': currentQuestion.is_collection }">
                      <text :class="currentQuestion.is_collection ? 'tn-icon-star-fill tn-color-orangeyellow tn-font-lg' : 'tn-icon-star tn-color-gray tn-font-md'" />
                      <text class="action-text tn-text-xs tn-margin-left-xxs" :class="{ 'tn-color-orangeyellow': currentQuestion.is_collection }">
                        {{ currentQuestion.collect_count || 0 }}
                      </text>
                    </view>
                  </view>
                  <!-- 答题时间显示 ，isExamination=true 显示倒计时，否则显示答题时长-->
                   <view  class="tn-text-sm tn-color-gray tn-padding-sm tn-radius-md tn-bg-gray-50 tn-border tn-border-gray">
                    <!-- 倒计时模式 -->
                    <tn-count-down v-if="isExamination" mode="countdown" :timestamp="examTimeInSeconds" autoplay />
                     <!-- 计时模式 -->
                    <tn-count-down v-else mode="countup" autoplay />
                  </view>
                  <view class="tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center">
                  <!-- 分享按钮 -->
                  <view class="action-btn tn-color-gray tn-padding-sm tn-radius-md tn-transition-all" @click="handleShare(currentQuestion)">
                    <text class="tn-icon-share tn-font-md" />
                    <text class="action-text tn-text-xs tn-margin-left-xxs">
                      分享
                    </text>
                  </view>
                  <!-- 右侧按钮：纠错 -->
                  <view class="action-btn tn-color-gray tn-padding-sm tn-radius-md tn-transition-all tn-margin-right-sm" @click="handleCorrection(currentQuestion)">
                    <text class="tn-icon-topics tn-font-md" />
                    <text class="action-text tn-text-xs tn-margin-left-xxs">
                      纠错
                    </text>
                  </view>
                  </view>
                </view>
              </view>
              
              <!-- 通用题目标题显示区域 -->
              <view class="question-title-area tn-bg-white tn-padding-md tn-radius-lg tn-shadow-sm tn-margin-top-sm">
                <view style="display: flex; align-items: center;">
                  <text class="question-number tn-text-bold tn-text-lg">
                    {{ displayQuestionIndex + 1 }}.
                  </text>
                  <text class="question-type-tag" :style="{ color: mainColor }">
                    [{{ currentQuestion ? getQuestionTypeName(currentQuestion.exam_type) : '未知题型' }}]
                  </text>
                  <view
                    v-if="currentQuestion && currentQuestion.score"
                    class="question-score tn-text-sm tn-color-gray tn-margin-left-sm"
                  >
                    ({{ currentQuestion.score }}分)
                  </view>
                  <view v-if="currentQuestion && currentQuestion.exam_level" class="question-level" style="margin-left: auto;">
                    <tn-rate 
                      :value="currentQuestion.exam_level" 
                      count="5" 
                      allow-half="true"
                      :activeColor="mainColor"
                      :disabled="true"
                    ></tn-rate>
                  </view>
                </view>
                <view class="tn-margin-top-sm tn-text-justify">
                  <view style="display: inline-block;" class="tn-margin-bottom-xs">
                    <view 
                      v-for="(label_item, label_index) in (currentQuestion && currentQuestion.label_data) || []" 
                      :key="label_index" 
                      class="blogger__desc__label tn-margin-bottom-xs tn-round tn-text-sm tn-text-bold"
                      :class="[currentQuestion ? getLabelColorClass('color', currentQuestion.uid, label_index) : '', currentQuestion ? getLabelColorClass('bg', currentQuestion.uid, label_index) + '--light' : '']"
                      style="display: inline-block; margin-bottom: 0;"
                    >
                      <text class="blogger__desc__label--prefix">#</text> 
                      <text class="tn-text-md">{{ label_item.title }}</text>
                    </view>
                  </view>
                  <mp-html :content="currentQuestion ? (currentQuestion.title || '') : ''" />
                </view>
              </view>

              <!-- 题型组件渲染区 -->
              <view class="question-type-component">
                <!-- 单选题 -->
                <keep-alive max="5">
                  <radio-option
                    v-if="currentQuestion && currentQuestion.exam_type === 1"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    @option-selected="handleOptionSelected"
                    @answer-shown="handleAnswerShown"
                    @next-question="handleNextQuestion"
                  />
                </keep-alive>
                
                <!-- 多选题 -->
                <keep-alive max="5">
                  <check-box-option
                    v-if="currentQuestion && currentQuestion.exam_type === 2"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    @option-selected="handleOptionSelected"
                    @answer-shown="handleAnswerShown"
                    @submit="handleMultipleSubmit"
                    @next-question="handleNextQuestion"
                  />
                </keep-alive>
                
                <!-- 判断题 -->
                <keep-alive max="5">
                  <judge-option
                    v-if="currentQuestion && currentQuestion.exam_type === 3"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    @option-selected="handleOptionSelected"
                    @answer-shown="handleAnswerShown"
                    @next-question="handleNextQuestion"
                  />
                </keep-alive>
                
                <!-- 填空题 -->
                <keep-alive max="5">
                  <fill-option
                    v-if="currentQuestion && currentQuestion.exam_type === 4"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    @answer-change="handleAnswerChange"
                    @answer-shown="handleAnswerShown"
                    @submit="handleFillSubmit"
                  />
                </keep-alive>
                
                <!-- 问答题 -->
                <keep-alive max="5">
                  <write-option
                    v-if="currentQuestion && currentQuestion.exam_type === 5"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    @answer-change="handleAnswerChange"
                    @answer-input="handleAnswerInput"
                    @answer-shown="handleAnswerShown"
                    @submit="handleEssaySubmit"
                  />
                </keep-alive>
                
                <!-- 案例题 -->
                <keep-alive max="5">
                  <compound-option
                    v-if="currentQuestion && currentQuestion.exam_type === 6"
                    :key="currentQuestion.uid"
                    :question="currentQuestion"
                    :question-index="swiperCurrentIndex"
                    :current-mode="currentMode"
                    :show-title="false"
                    :is-last-question="swiperCurrentIndex === swiperList.length - 1"
                    :total-questions="swiperList.length"
                    @option-selected="handleOptionSelected"
                    @answer-input="handleAnswerInput"
                    @answer-shown="handleAnswerShown"
                    @submit="handleCompoundSubmit"
                    @next-question="handleNextQuestion"
                  />
                </keep-alive>
              </view>

              <!-- 三大核心组件区域 -->
              <view class="core-components" :key="`core-components-${currentQuestion?.uid}-${displayQuestionIndex}`">
                <!-- 作答显示组件（问答题和案例题不显示） -->
                <view 
                  v-if="shouldShowAnswerDisplay(currentQuestion) " 
                  class="component-wrapper tn-margin-bottom-sm"
                >
                  <keep-alive>
                    <answer-display 
                      :key="currentQuestion.uid"
                      :question="currentQuestion"
                      :question-id="currentQuestion.uid"
                      :show-result="currentQuestion.is_submitted || currentQuestion.forceShowAnswer"
                    />
                  </keep-alive>
                </view>

                <!-- 试题解析组件（答题模式全程隐藏，其他模式在提交后显示） -->
                <view 
                  v-if="shouldShowAnalysis(currentQuestion) " 
                  class="component-wrapper tn-margin-bottom-sm"
                >
                  <keep-alive>
                    <analysis-section 
                      :key="currentQuestion.uid"
                      :question-id="currentQuestion.uid"
                      :question-name="currentQuestion.title"
                      :chapter="currentQuestion.chapter"
                      :analysis="currentQuestion.analysis"
                      :commentaries="currentQuestion.commentaries"
                      :knowledge="currentQuestion.Knowledge"
                    />
                  </keep-alive>
                </view>

                <!-- 做题笔记组件（答题模式全程隐藏） -->
                <view 
                  v-if="shouldShowComment(currentQuestion) " 
                  class="component-wrapper tn-margin-bottom-sm"
                >
                  <keep-alive>
                    <comment-section 
                      :key="currentQuestion.uid"
                      :question-uid="currentQuestion.uid"
                      :library-uid="currentQuestion.library_uid"
                      :category-uid="selectedCategoryUid"
                      :current-mode="currentMode"
                      @note-added="handleNoteAdded"
                      @note-liked="handleNoteLiked"
                    />
                  </keep-alive>
                </view>
              </view>
            </view>
          </scroll-view>
        </view>
        
        <!-- 下一个题目（用于动画） -->
        <view 
          v-if="false"
          class="question-item"
        >
          <scroll-view
            scroll-y
            class="question-scroll-view"
          >
            <view class="question-content" @touchstart="handleSimpleTouchStart" @touchmove="handleSimpleTouchMove" @touchend="handleSimpleTouchEnd">
                <!-- 题干区域（包含点赞、收藏、分享按钮） -->
                <view class="question-header tn-bg-white tn-padding-md tn-radius-lg tn-shadow-sm">
                  <!-- 题目内容会在具体题型组件中显示 -->
                  
                  <!-- 点赞、收藏、分享\纠错按钮组 -->
                  <view class="action-buttons tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center tn-justify-content-between" style="width: 100%;">
                    <!-- 左侧按钮：点赞、收藏 -->
                    <view class="tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center">
                      <!-- 点赞按钮 -->
                      <view class="action-btn tn-margin-left-sm tn-padding-sm tn-radius-md tn-transition-all"  @click="handleLike(getNextQuestion())" :class="{ 'tn-bg-red-50': getNextQuestion().is_like }">
                        <text :class="getNextQuestion().is_like ? 'tn-icon-like-fill tn-color-red tn-font-lg' : 'tn-icon-like tn-color-gray tn-font-md'" />
                        <text  class="action-text tn-text-xs tn-margin-left-xxs" :class="{ 'tn-color-red': getNextQuestion().is_like }">
                          {{ getNextQuestion().like_count || 0 }}
                        </text>
                      </view>
                      
                      <!-- 收藏按钮 -->
                      <view  class="action-btn tn-margin-right-sm tn-padding-sm tn-radius-md tn-transition-all"  @click="handleCollect(getNextQuestion())" :class="{ 'tn-bg-orangeyellow-50': getNextQuestion().is_collection }">
                        <text :class="getNextQuestion().is_collection ? 'tn-icon-star-fill tn-color-orangeyellow tn-font-lg' : 'tn-icon-star tn-color-gray tn-font-md'" />
                        <text class="action-text tn-text-xs tn-margin-left-xxs" :class="{ 'tn-color-orangeyellow': getNextQuestion().is_collection }">
                          {{ getNextQuestion().collect_count || 0 }}
                        </text>
                      </view>
                    </view>
                    <!-- 答题时间显示 ，isExamination=true 显示倒计时，否则显示答题时长-->
                     <view  class="tn-text-sm tn-color-gray tn-padding-sm tn-radius-md tn-bg-gray-50 tn-border tn-border-gray">
                      <!-- 倒计时模式 -->
                      <tn-count-down v-if="isExamination" mode="countdown" :timestamp="examTimeInSeconds" autoplay />
                       <!-- 计时模式 -->
                      <tn-count-down v-else mode="countup" autoplay />
                    </view>
                    <view class="tn-flex tn-flex-row tn-flex-wrap-nowrap tn-align-items-center">
                    <!-- 分享按钮 -->
                    <view class="action-btn tn-color-gray tn-padding-sm tn-radius-md tn-transition-all" @click="handleShare(getNextQuestion())">
                      <text class="tn-icon-share tn-font-md" />
                      <text class="action-text tn-text-xs tn-margin-left-xxs">
                        分享
                      </text>
                    </view>
                    <!-- 右侧按钮：纠错 -->
                    <view class="action-btn tn-color-gray tn-padding-sm tn-radius-md tn-transition-all tn-margin-right-sm" @click="handleCorrection(getNextQuestion())">
                      <text class="tn-icon-topics tn-font-md" />
                      <text class="action-text tn-text-xs tn-margin-left-xxs">
                        纠错
                      </text>
                    </view>
                    </view>
                  </view>
                </view>
                
                <!-- 通用题目标题显示区域 -->
                <view class="question-title-area tn-bg-white tn-padding-md tn-radius-lg tn-shadow-sm tn-margin-top-sm">
                  <view style="display: flex; align-items: center;">
                    <text class="question-number tn-text-bold tn-text-lg">
                      {{ getNextQuestionIndex() + 1 }}.
                    </text>
                    <text class="question-type-tag" :style="{ color: mainColor }">
                      [{{ getNextQuestion() ? getQuestionTypeName(getNextQuestion().exam_type) : '未知题型' }}]
                    </text>
                    <view
                      v-if="getNextQuestion() && getNextQuestion().score"
                      class="question-score tn-text-sm tn-color-gray tn-margin-left-sm"
                    >
                      ({{ getNextQuestion().score }}分)
                    </view>
                    <view v-if="getNextQuestion() && getNextQuestion().exam_level" class="question-level" style="margin-left: auto;">
                      <tn-rate 
                        :value="getNextQuestion().exam_level" 
                        count="5" 
                        allow-half="true"
                        :activeColor="mainColor"
                        :disabled="true"
                      ></tn-rate>
                    </view>
                  </view>
                  <view class="tn-margin-top-sm tn-text-justify">
                    <view style="display: inline-block;" class="tn-margin-bottom-xs">
                      <view 
                        v-for="(label_item, label_index) in (getNextQuestion() && getNextQuestion().label_data) || []" 
                        :key="label_index" 
                        class="blogger__desc__label tn-margin-right-xs tn-margin-bottom-xs tn-round tn-text-sm tn-text-bold"
                        :class="[getNextQuestion() ? getLabelColorClass('color', getNextQuestion().uid, label_index) : '', getNextQuestion() ? getLabelColorClass('bg', getNextQuestion().uid, label_index) + '--light' : '']"
                        style="display: inline-block; margin-bottom: 0; margin-right: 12rpx;"
                      >
                        <text class="blogger__desc__label--prefix">#</text> 
                        <text class="tn-text-df">{{ label_item.title }}</text>
                      </view>
                    </view>
                    <text>{{ getNextQuestion() ? (getNextQuestion().title || '').replace(/<[^>]*>/g, '') : '' }}</text>
                  </view>
                </view>

                <!-- 题型组件渲染区 -->
                <view class="question-type-component">
                  <!-- 单选题 -->
                  <keep-alive max="5">
                    <radio-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 1"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      @option-selected="handleOptionSelected"
                      @answer-shown="handleAnswerShown"
                      @next-question="handleNextQuestion"
                    />
                  </keep-alive>
                  
                  <!-- 多选题 -->
                  <keep-alive max="5">
                    <check-box-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 2"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      @option-selected="handleOptionSelected"
                      @answer-shown="handleAnswerShown"
                      @submit="handleMultipleSubmit"
                      @next-question="handleNextQuestion"
                    />
                  </keep-alive>
                  
                  <!-- 判断题 -->
                  <keep-alive max="5">
                    <judge-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 3"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      @option-selected="handleOptionSelected"
                      @answer-shown="handleAnswerShown"
                      @next-question="handleNextQuestion"
                    />
                  </keep-alive>
                  
                  <!-- 填空题 -->
                  <keep-alive max="5">
                    <fill-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 4"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      @answer-change="handleAnswerChange"
                      @answer-shown="handleAnswerShown"
                      @submit="handleFillSubmit"
                    />
                  </keep-alive>
                  
                  <!-- 问答题 -->
                  <keep-alive max="5">
                    <write-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 5"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      @answer-change="handleAnswerChange"
                      @answer-input="handleAnswerInput"
                      @answer-shown="handleAnswerShown"
                      @submit="handleEssaySubmit"
                    />
                  </keep-alive>
                  
                  <!-- 案例题 -->
                  <keep-alive max="5">
                    <compound-option
                      v-if="getNextQuestion() && getNextQuestion().exam_type === 6"
                      :key="getNextQuestion().uid"
                      :question="getNextQuestion()"
                      :question-index="getNextQuestionIndex()"
                      :current-mode="currentMode"
                      :show-title="false"
                      :is-last-question="getNextQuestionIndex() === swiperList.length - 1"
                      :total-questions="swiperList.length"
                      @option-selected="handleOptionSelected"
                      @answer-input="handleAnswerInput"
                      @answer-shown="handleAnswerShown"
                      @submit="handleCompoundSubmit"
                      @next-question="handleNextQuestion"
                    />
                  </keep-alive>
                </view>

                <!-- 三大核心组件区域 -->
                <view class="core-components">
                  <!-- 作答显示组件（问答题和案例题不显示） -->
                  <view 
                    v-if="shouldShowAnswerDisplay(getNextQuestion()) " 
                    class="component-wrapper tn-margin-bottom-sm"
                  >
                    <keep-alive>
                      <answer-display 
                        :key="getNextQuestion().uid"
                        :question="getNextQuestion()"
                        :question-id="getNextQuestion().uid"
                        :show-result="getNextQuestion().is_submitted || getNextQuestion().forceShowAnswer"
                      />
                    </keep-alive>
                  </view>

                  <!-- 试题解析组件（答题模式全程隐藏，其他模式在提交后显示） -->
                  <view 
                    v-if="shouldShowAnalysis(getNextQuestion()) " 
                    class="component-wrapper tn-margin-bottom-sm"
                  >
                    <keep-alive>
                      <analysis-section 
                        :key="getNextQuestion().uid"
                        :question-id="getNextQuestion().uid"
                        :question-name="getNextQuestion().title"
                        :chapter="getNextQuestion().chapter"
                        :analysis="getNextQuestion().analysis"
                        :commentaries="getNextQuestion().commentaries"
                        :knowledge="getNextQuestion().Knowledge"
                      />
                    </keep-alive>
                  </view>

                  <!-- 做题笔记组件（答题模式全程隐藏） -->
                  <view 
                    v-if="shouldShowComment(getNextQuestion()) " 
                    class="component-wrapper tn-margin-bottom-sm"
                  >
                    <keep-alive>
                      <comment-section 
                        :key="getNextQuestion().uid"
                        :question-uid="getNextQuestion().uid"
                        :library-uid="getNextQuestion().library_uid"
                        :category-uid="selectedCategoryUid"
                        :current-mode="currentMode"
                        @note-added="handleNoteAdded"
                        @note-liked="handleNoteLiked"
                      />
                    </keep-alive>
                  </view>
                </view>
              </view>
            </scroll-view>
          </view>
        </view>
      </view>

    <!-- 底部操作栏 -->
    <view class="bottom-toolbar">
      <view class="tn-flex tn-flex-row-between tn-flex-col-center tn-padding-sm">
        <!-- 答题卡按钮 -->
        <view
          class="toolbar-btn"
          :class="{ 'disabled': questionParams.questions_type === 0 && currentMode === 'reviewOnly' }"
          @click="handleShowAnswerCard"
        >
          <text class="tn-icon-cardbag tn-text-lg" />
          <text class="toolbar-text">
            答题卡
          </text>
        </view>

        <!-- 导航按钮组 -->
        <view class="tn-flex tn-flex-row-center">
          <tn-button
            size="sm"
            padding="30rpx 40rpx"
            height="60rpx"
            fontColor="tn-color-white"
            :backgroundColor="swiperCurrentIndex === 0 ? 'tn-bg-gray' : 'tn-cool-bg-color-6'"
            :disabled="swiperCurrentIndex === 0"
            :aria-label="swiperCurrentIndex === 0 ? '已是第一题' : '上一题'"
            @click="handlePrevQuestion"
          >
            {{ swiperCurrentIndex === 0 ? '已是第一题' : '上一题' }}
          </tn-button>
          
        <view class="question-progress" :key="`bottom-progress-${displayQuestionIndex}`">
          <text class="tn-text-bold">
            {{ displayQuestionIndex + 1 }}
          </text>
          <text class="tn-text-sm tn-color-gray">
            /{{ totalQuestionCount || swiperList.length || 0 }}
          </text>
        </view>
          
          <tn-button
            v-if="swiperCurrentIndex === swiperList.length - 1"
            size="sm"
            padding="30rpx 40rpx"
            height="60rpx"
            fontColor="tn-color-white"
            backgroundColor="tn-bg-red"
            :disabled="questionParams.questions_type === 0 && currentMode === 'reviewOnly'"
            aria-label="答题提交"
            @click="handleSubmitExam"
          >
            答题提交
          </tn-button>
          <tn-button
            v-else
            size="sm"
            padding="30rpx 40rpx"
            height="60rpx"
            fontColor="tn-color-white"
            :backgroundColor="swiperCurrentIndex >= swiperList.length - 1 ? 'tn-bg-gray' : 'tn-cool-bg-color-9'"
            :disabled="swiperCurrentIndex >= swiperList.length - 1"
            :aria-label="swiperCurrentIndex >= swiperList.length - 1 ? '已是最后一题' : '下一题'"
            @click="handleNextQuestion"
          >
            {{ swiperCurrentIndex >= swiperList.length - 1 ? '已是最后一题' : '下一题' }}
          </tn-button>
        </view>

        <!-- 更多操作 -->
        <!-- 看答案按钮 -->
        <!-- 非学练结合模式下禁用按钮  -->
         <view 
         class="toolbar-btn"
         :class="{'disabled': currentMode !== 'learnPractice' || questionParams.questions_type === 0}"
          @click="handleShowAnswer()"
          >
          <text class="tn-icon-eye tn-text-lg" />
          <text class="toolbar-text">看答案</text> 
        </view>
      </view>
    </view>

    <!-- 答题卡弹窗 -->
    <answer-card 
      v-model="showAnswerCard"
      :question-list="simplifiedQuestionList || swiperList"
      :current-index="displayQuestionIndex"
      :answered-count="answeredCount"
      :total-count="(simplifiedQuestionList || swiperList) ? (simplifiedQuestionList || swiperList).length : 0"
      :show-result="currentMode !== 'normal'"
      @examChange="handleQuestionChange"
      @saveProgress="handleSaveProgress"
      @submit-exam="handleSubmitExam"
    />

    <!-- 模式切换弹窗 -->
    <tn-popup 
      v-model="showModeSwitch" 
      mode="bottom"
      height="50%"
      :border-radius="30"
      :safe-area-inset-bottom="true"
    >
      <view class="mode-switch-popup">
        <view class="popup-title tn-padding">
          <text class="tn-text-bold tn-text-xl">
            选择做题模式
          </text>
        </view>
        
        <view class="mode-list tn-padding">
          <!-- 答题模式 -->
          <view 
            class="mode-item"
            :class="{ 'active': currentMode === 'normal' }"
            @click="switchMode('normal')"
          >
            <view class="mode-icon">
              <text class="tn-icon-edit-pen tn-text-xl" />
            </view>
            <view class="mode-info">
              <text class="mode-name tn-text-bold">
                答题模式
              </text>
              <text class="mode-desc tn-text-sm tn-color-gray">
                模拟真实答题环境，答题后不可修改答案且全程隐藏解析和笔记
              </text>
            </view>
            <view
              v-if="currentMode === 'normal'"
              class="mode-check"
            >
              <text class="tn-icon-check-circle-fill tn-color-blue" />
            </view>
          </view>

          <!-- 学练结合模式 -->
          <view 
            class="mode-item tn-margin-top"
            :class="{ 'active': currentMode === 'learnPractice' }"
            @click="switchMode('learnPractice')"
          >
            <view class="mode-icon">
              <text class="tn-icon-book tn-text-xl" />
            </view>
            <view class="mode-info">
              <text class="mode-name tn-text-bold">
                学练结合
              </text>
              <text class="mode-desc tn-text-sm tn-color-gray">
                答题后显示解析和笔记，支持修改答案，适合学习巩固
              </text>
            </view>
            <view
              v-if="currentMode === 'learnPractice'"
              class="mode-check"
            >
              <text class="tn-icon-check-circle-fill tn-color-blue" />
            </view>
          </view>

          <!-- 背题模式 -->
          <view 
            class="mode-item tn-margin-top"
            :class="{ 'active': currentMode === 'reviewOnly' }"
            @click="switchMode('reviewOnly')"
          >
            <view class="mode-icon">
              <text class="tn-icon-eye tn-text-xl" />
            </view>
            <view class="mode-info">
              <text class="mode-name tn-text-bold">
                背题模式
              </text>
              <text class="mode-desc tn-text-sm tn-color-gray">
                进入即显示正确答案、解析和笔记，仅供复习查看，不可答题
              </text>
            </view>
            <view
              v-if="currentMode === 'reviewOnly'"
              class="mode-check"
            >
              <text class="tn-icon-check-circle-fill tn-color-blue" />
            </view>
          </view>
        </view>
      </view>
    </tn-popup>

    <!-- 插槽：特殊功能区域 -->
    <slot name="special-features" />
    <slot name="examFooter" />
    <slot name="footer" />

    <!-- 综合确认提交模态框 -->
    <tn-modal 
      v-model="showConfirmModal"
      title="确认提交"
      :content="confirmModalContent"
      :button="[
        {
          text: '取消',
          backgroundColor: 'tn-bg-gray--light',
          fontColor: '#333333'
        },
        {
          text: '确认',
          backgroundColor: mainColor || '#1E88E5',
          fontColor: '#FFFFFF'
        }
      ]"
      :radius="16"
      :mask-closeable="false"
      @click="handleModalClick"
    />
    
    <!-- 结果模态框 -->
    <tn-modal 
      v-model="showResultModal"
      title="答题结果"
      :custom="true"
      :showCloseBtn="false"
      :maskCloseable="false"
      @click="handleResultModalClick"
    >
      <!-- 自定义内容，支持换行 -->
      <view class="result-content">
        <mp-html :content="resultMessage" />
      </view>
      <!-- 自定义按钮 -->
      <view class="result-buttons">
        <tn-button 
          :backgroundColor="mainColor || '#1E88E5'"
          fontColor="#FFFFFF"
          @click="handleResultClose"
        >
          知道了
        </tn-button>
        <tn-button 
          :backgroundColor="mainColor || '#1E88E5'"
          fontColor="#FFFFFF"
          @click="handleResultAnalysis"
        >
          查看解析
        </tn-button>
      </view>
    </tn-modal>

    <!-- 提交加载动画 -->
    <view v-if="isSubmitting" class="submit-loading-overlay">
      <view class="submit-loading-content">
        <tn-loading 
          mode="circle"
          :show="true"
          text="正在提交..."
        />
      </view>
    </view>
  </view>
</template>

<script>
// 同步组件导入
import RadioOption from './questionTypes/RadioOption.vue'
import CheckBoxOption from './questionTypes/CheckBoxOption.vue'
import JudgeOption from './questionTypes/JudgeOption.vue'
import FillOption from './questionTypes/FillOption.vue'
import WriteOption from './questionTypes/WriteOption.vue'
import CompoundOption from './questionTypes/CompoundOption.vue'

// 核心组件 - 同步加载
import AnswerDisplay from './questionCommons/AnswerDisplay.vue'
import AnalysisSection from './questionCommons/AnalysisSection.vue'
import CommentSection from './questionCommons/note.vue'
import AnswerCard from './questionCommons/AnswerCard.vue'
import { getUserInfo } from '@/util/userStore.js'
import { autoSaveExamProgress, saveExamProgress, clearExamProgress, cleanExpiredCache } from '@/util/examProgressManager.js'
import examStore from '../stores/examStore.vue2.js'
//import performanceTester from '../utils/performanceTester.js'
import dataProcessor from '../utils/dataProcessor.js'
//import performanceMonitor from '../utils/performanceMonitor.js'
import SwipeHandler from '../utils/swipeHandler.js'
import { OptimizedMixin } from '../utils/optimizedMixin.js'

export default {
  mixins: [OptimizedMixin],
  name: 'CommonQuestionPage',
  components: {
    RadioOption,
    CheckBoxOption,
    JudgeOption,
    FillOption,
    WriteOption,
    CompoundOption,
    AnswerDisplay,
    AnalysisSection,
    CommentSection,
    AnswerCard
  },
  data() {
    // 从全局状态获取currentExamSettings和本地存储获取questionParams
    const savedQuestionParams = getApp().globalData.currentExamSettings || uni.getStorageSync('questionParams') || {}
    return {
      mainColor: getApp().globalData.mainColor,
      // 优先使用本地存储的数据，如果本地存储为空则使用全局状态
      questionParams: savedQuestionParams,
      questionType: savedQuestionParams.questionType,
      examTime: savedQuestionParams.exam_time || 0,
      // 当前分享的题目信息
      currentShareQuestion: null,
      // 新增：独立页面初始化标志
      shouldStartNewPractice: false,
      savedProgress: null,
      // 🔑 新增：本地题目列表，用于临时存储题目数据
      localQuestionList: [],
      // 🔑 新增：总题数
      totalQuestionCount: 0,
      // 🔑 新增：题型比例配置
      ratioParams: {
        1: 30, // 单选题 30%
        2: 20, // 多选题 20%
        3: 10, // 判断题 10%
        4: 10, // 填空题 10%
        5: 15, // 问答题 15%
        6: 15  // 案例题 15%
      },
      chunkIndex : 0,
      chunkSize : 20,
      // 🔑 新增：点赞、收藏、分享防重复点击和防抖状态
      isLiking: {}, // 点赞加载状态，以 question.uid 为 key
      likeDebounceTimer: null, // 点赞防抖定时器
      shareDebounceTimer: null, // 分享防抖定时器
      // 🔑 修复：改为响应式属性，确保Vue能监听到变化
      swiperDisplayIndex: 1,  // swiper 显示索引（响应式）
      needProgrammaticJump: false,  // 程序化跳转标志（响应式）
      // 🔑 新增：记录上一次 transition 的 swiper 索引，用于判断滑动方向
      _lastTransitionIndex: -1,
      // 🔑 核心修复：响应式的滑动状态标志（必须是响应式的）
      isUserSwiping: false,
      // 🔑 核心修复：响应式的滑动过程中的临时题号索引（必须是响应式的）
      transitionQuestionIndex: -1,
      // 🔑 新增：弱网/卡顿兼容性状态
      swipeLoadingState: {
        isLoading: false,
        loadingText: '',
        loadingStartTime: 0,
        loadingTimeout: null
      },
      // 🔑 新增：滑动防抖定时器
      swipeDebounceTimer: null,
      // 🔑 新增：swiper 动画完成标志
      isSwiperAnimating: false,
      // 🔑 新增：预加载相关状态
      preloadConfig: {
        enabled: true,
        chunkSize: 10,
        preloadDistance: 1, // 预加载距离（题目数量）- 修复：从2减少到1，避免重复请求
        minPreloadTime: 1000, // 最小预加载间隔（毫秒）
        maxPreloadChunks: 3 // 最大预加载块数
      },
      preloadState: {
        isPreloading: false,
        lastPreloadTime: 0,
        preloadQueue: [],
        userSpeed: 0, // 用户浏览速度（题目/秒）
        preloadedChunks: new Set() // 已预加载的块索引集合，避免重复预加载
      },
      // 🔑 新增：加载状态标志，防止重复加载
      isLoading: false,
      // 🔑 新增：精简题目列表，用于答题卡显示
      simplifiedQuestionList: null,
      // 🔑 新增：缓存的题目结构信息，用于避免重复调用 API
      cachedQuestionStructure: null,
      // 🔑 新增：已加载的题块索引集合，防止重复加载
      loadedChunks: new Set(),
      // 🔑 关键修复：保存上一次的activeQuestions长度，用于检测变化
      _lastActiveQuestionsLength: 3,
      // 🔑 新增：错误恢复计数器
      _errorRecoveryCount: 0,
      // 🔑 新增：最大错误恢复次数
      _maxErrorRecoveryCount: 3,
      // 🔑 新增：滑动异常状态
      _swipeAbnormalState: {
        lastErrorTime: 0,
        errorCount: 0,
        isRecovering: false
      },
      // 🔑 优化：手势识别状态由 swipeHandler 管理，这里只保留必要的状态
      tempDisableSwipe: false,
      isInputFocused: false,
      // 🔑 新增：当前滑动方向
      currentSwipeDirection: '',
      // 🔑 新增：组件内 swipeHandler 实例
      swipeHandler: null,
      // 🔑 新增：题块加载锁，避免滑动冲突
      isLoadingQuestions: false,
      
      // 触摸状态管理
      _touchStartX: null,
      _touchEndX: null,
      _touchStartTime: null,
      
      // 动画相关状态
      isAnimating: false,
      direction: '',
      nextQuestionIndex: -1,
      
      // 动画配置选项
        animationConfig: {
          duration: 800, // 动画持续时间（毫秒）
          easing: 'ease-in-out', // 缓动函数
          threshold: 80, // 滑动触发阈值（像素），从50px提高到80px以减少误触发
          enable: false // 是否启用动画
        },
      
      // 页面加载状态
      isPageLoading: true,
      isContentLoaded: false
    }
  },
  computed: {
    // 用于界面显示的题号索引
    displayQuestionIndex() {
      // 直接使用 swiperCurrentIndex，与其他方法保持一致
      // 避免 simplifiedQuestionList 顺序不一致导致的索引错位
      return this.swiperCurrentIndex;
    },
    
    // 当前题目
    currentQuestion() {
      return this.swiperList[this.swiperCurrentIndex];
    },
    
    // 优化的虚拟列表计算 - 简化边界处理
    virtualSwiperList() {
        // 🔑 增强缓存机制：添加滑动状态检测
        const cacheKey = `virtualSwiperList_${this.swiperCurrentIndex}_${this.swiperList.length}_${this.swiperList[this.swiperCurrentIndex]?.uid || ''}_${this.isUserSwiping ? 'swiping' : 'static'}`;
        
        return this.swiperList;
    },
    
    // 优化的swiper位置计算 - 根据题目位置动态计算
    swipeCurrent() {
      const currentIndex = this.swiperCurrentIndex;
      const listLength = this.swiperList.length;
      
      // 🔑 关键修复：使用swiperDisplayIndex来强制Swiper重新渲染
      // 当swiperDisplayIndex变化时，这个计算属性会重新计算
      const displayIndex = this.swiperDisplayIndex;
      
      // 第一题：当前题在 index=0（因为 activeQuestions 只有2个题目）
      if (currentIndex === 0) {
        return 0;
      }
      
      // 最后一题：当前题在 index=1（因为 activeQuestions 只有2个题目）
      if (currentIndex >= listLength - 1) {
        return 1;
      }
      
      // 中间题：当前题在 index=1（因为 activeQuestions 有3个题目）
      return 1;
    },
    
    // 代理 store 状态到组件 computed
    swiperList() {
      // 直接从 examStore 中获取 swiperList
      const list = examStore.state.swiperList || []
      
      // 如果 list 为空，但 localQuestionList 不为空，返回 localQuestionList
      if (list.length === 0 && this.localQuestionList.length > 0) {
        return this.localQuestionList
      }
      
      return list
    },
    
    // 活跃题目列表 - 根据位置返回2个或3个题目
    activeQuestions() {
      const currentIndex = this.swiperCurrentIndex;
      const listLength = this.swiperList.length;
      
      // 如果 swiperList 为空，返回空数组
      if (listLength === 0) {
        return [];
      }
      
      // 🔑 关键检查：如果当前题是占位符，触发加载
      const currentQuestion = this.swiperList[currentIndex];
      if (currentQuestion && currentQuestion.isPlaceholder) {
        // 异步加载当前题（不阻塞渲染）
        this.$nextTick(() => {
          this.loadCurrentQuestionIfNeeded(currentIndex);
        });
      }
      
      const questions = [];
      
      // 第一题：只渲染当前题和后一题 (2个)
      if (currentIndex === 0) {
        questions.push(this.swiperList[0]); // 当前题 (index=0)
        if (listLength > 1) {
          questions.push(this.swiperList[1]); // 后一题 (index=1)
        }
        return questions;
      }
      
      // 最后一题：只渲染前一题和当前题 (2个)
      if (currentIndex >= listLength - 1) {
        questions.push(this.swiperList[listLength - 2]); // 前一题 (index=0)
        questions.push(this.swiperList[listLength - 1]); // 当前题 (index=1)

        return questions;
      }
      

      
      // 中间题：渲染3个题目
      questions.push(this.swiperList[currentIndex - 1]); // 前一题 (index=0)
      questions.push(this.swiperList[currentIndex]);     // 当前题 (index=1)
      questions.push(this.swiperList[currentIndex + 1]); // 后一题 (index=2)
      
      return questions;
    },
    
    swiperCurrentIndex: {
      get() {
        const index = examStore.state.swiperCurrentIndex;
        return typeof index === 'number' ? index : 0;
      },
      set(val) {
        examStore.setCurrentIndex(val)
      }
    },
    
    currentMode() {
      return examStore.state.currentMode || 'learnPractice'
    },
    
    showAnswerCard: {
      get() {
        return examStore.state.showAnswerCard || false
      },
      set(val) {
        examStore.setState({ showAnswerCard: val })
      }
    },
    
    showModeSwitch: {
      get() {
        return examStore.state.showModeSwitch || false
      },
      set(val) {
        examStore.setState({ showModeSwitch: val })
      }
    },
    
    disableSwipe() {
      // 🔑 关键修复：弹窗存在时全局禁用滑动
      const isModalOpen = this.showAnswerCard || this.showModeSwitch || this.showConfirmModal || this.showResultModal;
      return examStore.state.disableSwipe || isModalOpen || false;
    },
    
    answeredCount() {
      const count = examStore.state.answeredCount;
      return typeof count === 'number' ? count : 0;
    },
    
    examTimeInSeconds() {
      return examStore.state.examTimeInSeconds
    },
    
    isExamination() {
      return examStore.state.isExamination
    },
    
    seconds: {
      get() {
        return examStore.state.seconds
      },
      set(val) {
        examStore.setState({ seconds: val })
      }
    },
    
    timerId() {
      return examStore.state.timerId
    },
    
    showConfirmModal: {
      get() {
        return examStore.state.showConfirmModal
      },
      set(val) {
        examStore.setState({ showConfirmModal: val })
      }
    },
    
    showResultModal: {
      get() {
        return examStore.state.showResultModal
      },
      set(val) {
        examStore.setState({ showResultModal: val })
      }
    },
    
    resultMessage() {
      return examStore.state.resultMessage
    },
    
    confirmModalContent: {
      get() {
        return examStore.state.confirmModalContent
      },
      set(val) {
        examStore.setState({ confirmModalContent: val })
      }
    },
    
    selectedCategoryUid() {
      return examStore.state.selectedCategoryUid
    },
    
    shouldRestoreOnMount: {
      get() {
        return examStore.state.shouldRestoreOnMount
      },
      set(val) {
        examStore.setState({ shouldRestoreOnMount: val })
      }
    },
    
    isSubmitting: {
      get() {
        return examStore.state.isSubmitting
      },
      set(val) {
        examStore.setState({ isSubmitting: val })
      }
    },
    
    // 修改 currentQuestion 计算属性
    currentQuestion() {
      // 首先尝试从 swiperList 获取，因为 swiperList 是完整的题目列表
      const currentIndex = this.swiperCurrentIndex;
      const safeIndex = Math.max(0, Math.min(currentIndex, this.swiperList.length - 1));
      let question = this.swiperList[safeIndex] || {};
      
      
      // 如果从 swiperList 获取失败，再尝试从本地题目列表获取
      if (!question.uid && this.localQuestionList.length > 0) {
        const localSafeIndex = Math.max(0, Math.min(currentIndex, this.localQuestionList.length - 1));
        question = this.localQuestionList[localSafeIndex] || {};
        
      }
      
      // 如果 currentQuestion 为空，返回第一个题目
      if (!question.uid && this.swiperList.length > 0) {
        for (let i = 0; i < this.swiperList.length; i++) {
          const item = this.swiperList[i];
          if (item && item.uid) {
            question = item;
            break;
          }
        }
      }
      
      return question;
    },
    
    progressPercent() {
      return examStore.getProgressPercent()
    },
    
    isLastQuestion() {
      return examStore.getIsLastQuestion()
    },
    
    isFirstQuestion() {
      return examStore.getIsFirstQuestion()
    }
  },
  onLoad(option) {
    // 从页面参数获取examSettings，支持JSON字符串和直接对象
    let examSettings = {}
    let parseSuccess = false
    
    // 首先检查是否存在examSettings参数
    if (option.examSettings) {
      try {
        // 获取原始examSettings值
        let examSettingsValue = option.examSettings
        
        // 确保examSettingsValue是字符串
        if (typeof examSettingsValue === 'string') {
          // 首先尝试URL解码，因为参数可能被encodeURIComponent编码过
          try {
            examSettingsValue = decodeURIComponent(examSettingsValue)
          } catch (decodeError) {
            console.error('URL解码examSettings失败:', decodeError)
            // 解码失败时，继续使用原始值
          }
          
          // 然后解析为对象
          examSettings = JSON.parse(examSettingsValue)
        } else {
          // 如果已经是对象，直接使用
          examSettings = examSettingsValue
        }
        
        // 确保examSettings是对象
        if (typeof examSettings === 'object' && examSettings !== null) {
          parseSuccess = true
        } else {
          // 如果解析结果不是对象，尝试使用option中的其他参数
          examSettings = { ...option }
          delete examSettings.examSettings // 移除原始的examSettings参数
        }
      } catch (e) {
        console.error('解析examSettings失败:', e)
        // 解析失败时，尝试从option中提取必要参数
        examSettings = { ...option }
        delete examSettings.examSettings // 移除原始的examSettings参数
      }
    } else {
      // 没有examSettings参数时，尝试使用整个option对象
      examSettings = option
    }
    // 修复：处理从 questionChapter.vue 直接跳转的场景
    if (option.restore === 'true') {
      // 从全局状态读取进度数据
      const savedProgress = getApp().globalData.savedProgress
      const shouldRestore = getApp().globalData.shouldRestoreProgress
      const selectedCategoryUid = uni.getStorageSync('selectedCategoryUid')
      examStore.setState({ selectedCategoryUid })
      if (shouldRestore && savedProgress) {
        
        // 设置标志，在 mounted 中执行恢复
        this.shouldRestoreOnMount = true
        this.savedProgress = savedProgress
        
        // 从保存的进度中获取总题数
        if (savedProgress.examSettings && savedProgress.examSettings.question_count) {
          this.totalQuestionCount = savedProgress.examSettings.question_count
        }
        
        // 清除全局状态
        getApp().globalData.shouldRestoreProgress = false
        getApp().globalData.savedProgress = null
      }
    } else {
      // 无论parseSuccess与否，都使用examSettings初始化，确保this.questionParams有值
      this.questionParams = { ...examSettings }
      // 从examSettings获取总题数
      if (examSettings.question_count) {
        this.totalQuestionCount = examSettings.question_count
      }
      // isExamination 现在通过 store 管理，不需要直接赋值
      // this.isExamination = this.questionParams.isExamination || false
      // 立即更新questionType和examTime，避免watch异步更新导致的问题
      this.questionType = this.questionParams.questionType
      this.examTime = this.questionParams.exam_time || 0
      this.shouldStartNewPractice = true
    }
    
    // 保存examSettings到本地存储，作为备选方案
    if (parseSuccess && Object.keys(examSettings).length > 0) {
      // 只保存必要的字段，避免循环引用
      const safeExamSettings = {
        questions_type: examSettings.questions_type || examSettings.questionsType || 0,
        chapter_uid: examSettings.chapter_uid || examSettings.chapterUid || '',
        exam_time: examSettings.exam_time || 0,
        questionType: examSettings.questionType,
        question_count: examSettings.question_count,
        isExamination: examSettings.isExamination || false
      };
      uni.setStorageSync('questionParams', safeExamSettings)
    }
    
    // 确保selectedCategoryUid有值
    const selectedCategoryUid = uni.getStorageSync('selectedCategoryUid')
    examStore.setState({ selectedCategoryUid })
  },
  async mounted() {
    // 清理过期缓存
    try {
      const userInfo = await getUserInfo();
      const userId = userInfo?.id || userInfo?.uid || '';
      cleanExpiredCache(userId);
    } catch (error) {
      console.error('清理过期缓存失败:', error);
    }
    
    // 🔑 初始化 swipeHandler 实例（组件内实例化，避免全局单例污染）
    this.swipeHandler = new SwipeHandler();

    // 🔑 设置滑动回调，包括垂直滚动回调
    this.swipeHandler.setCallbacks({
      onSwipeStart: (data) => {
        // 滑动开始
      },
      onSwipeMove: (data) => {
        // 滑动中
      },
      onSwipeEnd: (data) => {
        // 滑动结束
      },
      onVerticalScroll: (data) => {
        // 🔑 检测到垂直滚动时，临时禁用 swiper 滑动
        this.tempDisableSwipe = true;
        // 滚动结束后恢复
        if (data?.ended) {
          setTimeout(() => {
            this.tempDisableSwipe = false;
          }, 50);
        }
      },
      onBeforeSwipe: (data) => {
        // 滑动前
      }
    });

    // 从 questionParams 获取答题设置
    const examSettings = this.questionParams
    if (examSettings && examSettings.mode) {
      examStore.switchMode(examSettings.mode)
    }

    // 启动定期内存清理
    this.scheduleMemoryCleanup();
    
    // 启动空闲时间预加载
    // 延迟启动，避免与startNewPractice方法中的调用重复
    setTimeout(() => {
      this.scheduleIdlePreload();
    }, 1000);
    
    //exam_time: "10:27:57",转换为时间戳（秒）
    if (this.examTime) {
      // 转换为时间戳（秒）
      if (typeof this.examTime === 'string') {
        // 处理时间字符串格式，如 "10:27:57" 或 "10:27"
        const timeParts = this.examTime.split(':').map(Number)
        if (timeParts.length === 3) {
          // 时:分:秒格式
          const examTimeInSeconds = timeParts[0] * 3600 + timeParts[1] * 60 + timeParts[2]
          examStore.setState({
            examTimeInSeconds: examTimeInSeconds,
            isExamination: examTimeInSeconds > 0
          })
        } else if (timeParts.length === 2) {
          // 分:秒格式
          const examTimeInSeconds = timeParts[0] * 60 + timeParts[1]
          examStore.setState({
            examTimeInSeconds: examTimeInSeconds,
            isExamination: examTimeInSeconds > 0
          })
        } else {
          // 秒格式
          const examTimeInSeconds = Number(this.examTime)
          examStore.setState({
            examTimeInSeconds: examTimeInSeconds,
            isExamination: examTimeInSeconds > 0
          })
        }
      } else {
        // 处理数字格式（分钟）
        const examTimeInSeconds = Number(this.examTime) * 60
        examStore.setState({
          examTimeInSeconds: examTimeInSeconds,
          isExamination: examTimeInSeconds > 0
        })
      }
    }
    
    // 保存初始 questionParams 到本地存储
    if (Object.keys(this.questionParams).length > 0) {
      // 只保存必要的字段，避免循环引用
      const safeQuestionParams = {
        questions_type: this.questionParams.questions_type || this.questionParams.questionsType || 0,
        chapter_uid: this.questionParams.chapter_uid || this.questionParams.chapterUid || '',
        exam_time: this.questionParams.exam_time || 0,
        questionType: this.questionParams.questionType,
        isExamination: this.questionParams.isExamination || false
      };
      uni.setStorageSync('questionParams', safeQuestionParams);
    }
    
    // 修复：确保selectedCategoryUid始终有值
    const selectedCategoryUid = uni.getStorageSync('selectedCategoryUid')
    examStore.setState({ selectedCategoryUid })
    
    // 修复：处理直接跳转的场景
    if (this.shouldRestoreOnMount && this.savedProgress) {
      // 使用本地保存的进度数据
      this.restoreProgress(this.savedProgress)
    } else if (this.shouldStartNewPractice) {
      // 直接调用startNewPractice加载题目，不再依赖外部调用
      this.startNewPractice()
    } else {
      // 🔑 新增：默认分支，当shouldRestoreOnMount和shouldStartNewPractice都为false时，调用startNewPractice
      // 这样至少会尝试加载题目，而不是一直显示加载动画
      this.startNewPractice()
    }
    
    // 启动计时器
    this.startTimer()
    
    // 结束渲染监控
    this.$nextTick(() => {
      // 延迟强制更新组件状态，确保数据已经被正确设置
      setTimeout(() => {
        this.$forceUpdate()
      }, 500)
    })
  },
  watch: {
    // 🔑 关键修复：监听swiperCurrentIndex变化，强制更新activeQuestions
    swiperCurrentIndex: {
      handler(newVal, oldVal) {
        // 使用保存的上一次长度来检测变化
        const oldLength = this._lastActiveQuestionsLength;
        
        // 🔑 关键修复：延迟处理，等待swiper完成动画
        this.$nextTick(() => {
          const newLength = this.activeQuestions?.length;
          
          // 🔑 关键修复：边界状态切换处理
          this.handleBoundaryTransition(oldLength, newLength);
          
          // 保存当前长度供下一次使用
          this._lastActiveQuestionsLength = newLength;
        });
      }
    },
    // 监听questionParams变化，自动保存到本地存储并更新questionType
    questionParams: {
      handler(newVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          // 只保存必要的字段，避免循环引用
          const safeQuestionParams = {
            questions_type: newVal.questions_type || newVal.questionsType || 0,
            chapter_uid: newVal.chapter_uid || newVal.chapterUid || '',
            exam_time: newVal.exam_time || 0,
            questionType: newVal.questionType,
            isExamination: newVal.isExamination || false
          };
          uni.setStorageSync('questionParams', safeQuestionParams);
          // 及时更新questionType，确保它始终有有效值
          this.questionType = newVal.questionType;
          // 更新examTime，确保倒计时正确
          this.examTime = newVal.exam_time || 0;
        }
      },
      deep: true, // 深度监听，确保对象内部变化也能被捕获
      immediate: true // 立即执行，初始化时就保存
    },
    // 监听examTime变化，更新倒计时时间
    examTime: {
      handler(newVal) {
        if (newVal) {
          let seconds = 0
          if (typeof newVal === 'string') {
            // 处理时间字符串格式，如 "10:27:57" 或 "10:27"
            const timeParts = newVal.split(':').map(Number)
            if (timeParts.length === 3) {
              // 时:分:秒格式
              seconds = timeParts[0] * 3600 + timeParts[1] * 60 + timeParts[2]
            } else if (timeParts.length === 2) {
              // 分:秒格式
              seconds = timeParts[0] * 60 + timeParts[1]
            } else {
              // 秒格式
              seconds = Number(newVal)
            }
          }
          examStore.setState({
            examTimeInSeconds: seconds,
            isExamination: seconds > 0
          })
        } else {
          examStore.setState({
            examTimeInSeconds: 0,
            isExamination: false
          })
        }
      },
      immediate: true
    }
  },
  methods: {
    // 获取题目类型名称
    getQuestionTypeName(examType) {
      const typeMap = {
        1: '单选题',
        2: '多选题',
        3: '判断题',
        4: '填空题',
        5: '简答题',
        6: '案例题'
      }
      return typeMap[examType] || '题目'
    },
    
    // 🔑 关键方法：获取题目在 swiperList 中的真实索引
    // 用于将 activeQuestions 中的 item 映射回 swiperList 的真实索引
    getRealQuestionIndex(item, activeIndex) {
      if (!item || !item.uid) return -1;
      
      const currentIndex = this.swiperCurrentIndex;
      const listLength = this.swiperList.length;
      
      // 第一题情况
      if (currentIndex === 0) {
        if (activeIndex === 0) return 0; // 当前题
        if (activeIndex === 1) return Math.min(1, listLength - 1); // 后一题
      }
      // 最后一题情况
      else if (currentIndex >= listLength - 1) {
        if (activeIndex === 0) return Math.max(0, listLength - 2); // 前一题
        if (activeIndex === 1) return listLength - 1; // 当前题
      }
      // 中间题情况
      else {
        if (activeIndex === 0) return currentIndex - 1; // 前一题
        if (activeIndex === 1) return currentIndex; // 当前题，无论方向如何
        if (activeIndex === 2) return currentIndex + 1; // 后一题
      }
      
      return currentIndex;
    },
    
    // 🔑 关键方法：如果当前题是占位符，加载该题目
    async loadCurrentQuestionIfNeeded(index) {
      const question = this.swiperList[index];
      if (!question || !question.isPlaceholder) return;
      
      // 计算需要加载的块
      const chunkSize = this.preloadConfig?.chunkSize || 10;
      const chunkIndex = Math.floor(index / chunkSize);
      
      
      // 🔑 关键修复：检查该块是否已经加载过
      if (this.loadedChunks.has(chunkIndex)) {
        return;
      }
      
      // 🔑 关键修复：如果正在加载，直接跳过（不等待）
      if (this.isLoading) {
        return;
      }
            
      try {
        // 加载题目
        await this.fetchQuestionList(chunkIndex, chunkSize);
      } catch (error) {
        console.error('[loadCurrentQuestionIfNeeded] 失败:', error);
      }
    },
    
    // 构建API调用参数
    buildApiParams(chunkIndex, chunkSize) {
      // 转换参数格式
      const params = {
        ...this.questionParams,
        uid: this.questionParams.uid || this.questionParams.library_uid || this.questionParams.questionLibraryUid || '',
        // 将is_rand转换为random_type
        random_type: this.questionParams.is_rand || this.questionParams.random_type || 2,
        // 将exam_type从逗号分隔的字符串转换为数组
        exam_type: this.questionParams.exam_type,
        question_count: this.questionParams.question_count || 50,
        offset: chunkIndex * chunkSize,
        limit: chunkSize
      };
      
      // 移除可能存在的is_rand参数，避免混淆
      delete params.is_rand;
      
      return params;
    },
    
    // 转换ratio_params格式
    convertRatioParams(ratioParams) {
      if (!ratioParams) return {};
      if (typeof ratioParams === 'object') return ratioParams;
      
      try {
        // 尝试解码URL编码的字符串
        const decodedRatioParams = decodeURIComponent(ratioParams);
        // 尝试解析JSON字符串
        return JSON.parse(decodedRatioParams);
      } catch (e) {
        console.error('转换ratio_params失败:', e);
        return {};
      }
    },
    
    // 转换exam_type格式
    convertExamType(examType) {
      if (!examType) return [];
      if (Array.isArray(examType)) return examType;
      if (typeof examType === 'string') {
        return examType.split(',').filter(item => item.trim() !== '');
      }
      return [];
    },
    
    // 获取所有题目结构信息（用于构建题目索引和答题卡）
    getQuestionStructure(chunkIndex = 0, chunkSize = 10) {
      return new Promise((resolve, reject) => {
        // 检查是否有缓存的题目结构信息
        if (this.cachedQuestionStructure) {
          resolve(this.cachedQuestionStructure);
          return;
        }
        
        // 使用统一的参数构建方法
        const params = this.buildApiParams(chunkIndex, chunkSize);
        if(params.questions_type == 8 || params.questions_type == '8'){
          params.ratio_params = this.convertRatioParams(this.questionParams.ratio_params || this.ratioParams);
        }
        
        this.$api.getQuestionStructure(params).then(res => {
          if (res.code === 1) {
            // 缓存题目结构信息，避免重复调用 API
            this.cachedQuestionStructure = res.data;
            resolve(res.data);
          } else {
            reject(res.msg);
          }
        }).catch(err => {
          console.error('获取题目结构信息失败:', err);
          // 失败时使用 totalQuestionCount 构建占位符列表作为备用方案
          if (this.totalQuestionCount > 0) {
            // 构建占位符列表，确保索引范围正确
            const structure = Array.from({ length: this.totalQuestionCount }, (_, index) => ({
              uid: `placeholder_${index}`,
              exam_type: 'choice'
            }));
            resolve(structure);
          } else if (this.swiperList && this.swiperList.length > 0) {
            const structure = this.swiperList.map(item => ({
              uid: item.uid,
              exam_type: item.exam_type
            }));
            resolve(structure);
          } else {
            reject(err);
          }
        });
      });
    },
    
    // 检查题目是否已答
    checkIfAnswered(uid) {
      const question = this.swiperList.find(q => q.uid === uid);
      return question ? question.is_answered || (question.user_answer && question.user_answer.length > 0) : false;
    },
    
    // 获取用户答案
    getUserAnswer(uid) {
      const question = this.swiperList.find(q => q.uid === uid);
      return question ? question.user_answer : null;
    },
    
    // 获取题目正确性
    getQuestionCorrectness(uid) {
      const question = this.swiperList.find(q => q.uid === uid);
      return question ? question.is_correct : undefined;
    },
    
    // 显示答题卡
    handleShowAnswerCard() {
      this._clickedQuestionIndex = -1;
      // 移除阻止执行的条件判断，确保总是执行 getQuestionStructure 方法
      
      // 获取题目结构信息
      this.getQuestionStructure().then(structure => {
        
        // 构建精简的题目列表（包含uid和题型）
        const simplifiedQuestionList = structure.map((item, index) => ({
          uid: item.uid,
          exam_type: item.exam_type,
          // 保持与原有结构兼容的字段
          is_answered: this.checkIfAnswered(item.uid),
          user_answer: this.getUserAnswer(item.uid),
          is_correct: this.getQuestionCorrectness(item.uid)
        }))
        
        // 存储精简题目列表用于答题卡显示
        this.simplifiedQuestionList = simplifiedQuestionList
        // 显示答题卡
        this.showAnswerCard = true
      }).catch(err => {
        console.error('获取题目结构信息失败:', err)
        // 失败时使用原有方式，确保功能可用性
        this.simplifiedQuestionList = null
        
        // 确保已加载所有题目
        if (this.totalQuestionCount > 0 && this.swiperList.length < this.totalQuestionCount) {
          
          // 计算还需要加载的块数
          const remainingChunks = Math.ceil((this.totalQuestionCount - this.swiperList.length) / chunkSize)
          
          // 立即加载第一块剩余题目
          const currentChunkIndex = Math.floor(this.swiperList.length / chunkSize)
          this.preloadNextChunk(currentChunkIndex, chunkSize)
          
          // 延迟显示答题卡，确保题目加载完成
          setTimeout(() => {
            this.showAnswerCard = true
          }, 2000) // 延迟2秒，确保至少加载一块题目
        } else {
          // 题目已加载完成，直接显示答题卡
          this.showAnswerCard = true
        }
      })
    },
    
    // 去除HTML标签
    stripHtmlTags(html) {
      if (!html) return ''
      return html.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim()
    },
    
    // 获取标签颜色类（基于题目uid和标签索引，确保颜色稳定不变）
    getLabelColorClass(type, questionUid, labelIndex) {
      const colorTypes = ['red', 'orangeyellow', 'orangered', 'green', 'cyan', 'blue', 'purple', 'orangered']
      const colorCount = colorTypes.length
      // 使用题目uid和标签索引的组合生成稳定索引
      const uidNum = String(questionUid).split('').reduce((acc, char) => acc + char.charCodeAt(0), 0)
      const stableIndex = (uidNum + labelIndex) % colorCount
      return 'tn-' + type + '-' + colorTypes[stableIndex]
    },
    
    // 恢复保存的进度数据
    restoreProgress(progressData) {
      try {
        // 设置加载状态
        this.isPageLoading = true;
        this.isContentLoaded = false;
        
        // 修复：兼容两种数据结构 - questions(旧) 和 questionList(新)
        const questionList = progressData.questions || progressData.questionList || [];
        
        if (Array.isArray(questionList) && questionList.length > 0) {
          
          // 直接使用缓存的题目列表
          examStore.setSwiperList(questionList)
          
          // 🔑 关键修复：从缓存恢复后初始化 simplifiedQuestionList
          if (Array.isArray(questionList) && questionList.length > 0) {
            // 根据缓存的题目列表构建 simplifiedQuestionList
            const simplifiedQuestionList = questionList.map((item, index) => ({
              uid: item.uid,
              exam_type: item.exam_type,
              // 保持与原有结构兼容的字段
              is_answered: this.checkIfAnswered(item.uid),
              user_answer: this.getUserAnswer(item.uid),
              is_correct: this.getQuestionCorrectness(item.uid)
            }));
            
            // 存储精简题目列表用于答题卡显示
            this.simplifiedQuestionList = simplifiedQuestionList;
          }
          
          // 修复：恢复 questionParams（兼容两种字段名）
          let savedParams = progressData.questionParams || progressData.examSettings || {};
          
          
          if (savedParams && typeof savedParams === 'object' && Object.keys(savedParams).length > 0) {
            // 重要：直接替换，不要使用 Object.assign，避免嵌套
            this.questionParams = { ...savedParams }
            
            // 修复：强制覆盖 questions_type （从顶层字段获取，确保正确）
            if (progressData.questionsType !== undefined) {
              this.questionParams.questions_type = progressData.questionsType
            }
            
            // 修复：强制覆盖 chapter_uid
            if (progressData.chapterUid !== undefined) {
              this.questionParams.chapter_uid = progressData.chapterUid
            }
            
            // 修复：保存恢复的 questionParams 到本地存储，避免丢失
            // 只保存必要的字段，避免循环引用
            const safeQuestionParams = {
              questions_type: this.questionParams.questions_type || this.questionParams.questionsType || 0,
              chapter_uid: this.questionParams.chapter_uid || this.questionParams.chapterUid || '',
              exam_time: this.questionParams.exam_time || 0,
              questionType: this.questionParams.questionType,
              isExamination: this.questionParams.isExamination || false
            };
            uni.setStorageSync('questionParams', safeQuestionParams);
          }
          
          // 修复：恢复当前索引（兼容两种字段名）
          const currentIndex = progressData.currentIndex !== undefined 
            ? progressData.currentIndex 
            : progressData.currentQuestionIndex;
          
          if (currentIndex !== undefined) {
            this.swiperCurrentIndex = currentIndex
            // 🔑 程序化跳转：设置标志并更新显示索引
            this.swiperDisplayIndex = this.virtualCurrentIndex;
            this.needProgrammaticJump = true;
            // 🔑 needProgrammaticJump 会在 handleSwiperChange 中被重置
          }
          
          // 修复：恢复答题模式（兼容两种数据源）
          const mode = progressData.currentMode || progressData.examSettings?.mode;
          if (mode !== undefined) {
            examStore.switchMode(mode)
          }
          
          // 修复：恢复答题时间（兼容两种字段名）
          const totalTime = progressData.seconds !== undefined 
            ? progressData.seconds 
            : progressData.totalTime;
          
          if (totalTime !== undefined) {
            this.seconds = totalTime
          }
          
          // 修复：恢复倒计时状态和剩余时间
          if (progressData.examTime !== undefined) {
            this.examTime = progressData.examTime
          }
          
          if (progressData.examTimeInSeconds !== undefined) {
            examStore.setState({
              examTimeInSeconds: progressData.examTimeInSeconds,
              isExamination: progressData.examTimeInSeconds > 0
            })
          } else if (this.examTime) {
            // 如果没有保存剩余时间，但有总时间，重新计算
            // 假设已用时间为 this.seconds，剩余时间为总时间 - 已用时间
            let totalExamTimeSeconds = 0
            if (typeof this.examTime === 'string') {
              // 处理时间字符串格式，如 "10:27:57" 或 "10:27"
              const timeParts = this.examTime.split(':').map(Number)
              if (timeParts.length === 3) {
                // 时:分:秒格式
                totalExamTimeSeconds = timeParts[0] * 3600 + timeParts[1] * 60 + timeParts[2]
              } else if (timeParts.length === 2) {
                // 分:秒格式
                totalExamTimeSeconds = timeParts[0] * 60 + timeParts[1]
              } else {
                // 秒格式
                totalExamTimeSeconds = Number(this.examTime)
              }
            } else {
              // 处理数字格式（分钟）
              totalExamTimeSeconds = Number(this.examTime) * 60
            }
            const remainingTime = Math.max(0, totalExamTimeSeconds - this.seconds)
            examStore.setState({
              examTimeInSeconds: remainingTime,
              isExamination: remainingTime > 0
            })
          }
          
          // 重新计算已答题数
          this.calculateAnsweredCount()
          // 标记内容已加载
          this.isContentLoaded = true;
          // 延迟隐藏加载动画，确保内容平滑显示
          setTimeout(() => {
            this.isPageLoading = false;
          }, 300);
          
          // 显示恢复成功提示
          uni.showToast({
            title: '进度已恢复',
            icon: 'success',
            duration: 1500
          })
        } else {
          // 标记内容已加载
          this.isContentLoaded = true;
          // 隐藏加载动画
          setTimeout(() => {
            this.isPageLoading = false;
          }, 300);
          
          uni.showToast({
            title: '进度数据异常，开始新练习',
            icon: 'none',
            duration: 1500
          })
          
          // 开始新练习
          this.startNewPractice()
        }
      } catch (error) {
          console.error('恢复进度失败:', error);
          // 标记内容已加载
          this.isContentLoaded = true;
          // 隐藏加载动画
          setTimeout(() => {
            this.isPageLoading = false;
          }, 300);
          
          uni.showToast({
          title: '恢复进度失败，开始新练习',
          icon: 'none',
          duration: 2000
        })
        
        // 开始新练习
        this.startNewPractice()
      }
    },
    
    // 确认模态框确认按钮回调
    handleConfirmSubmit() {
      // 关闭模态框
      this.showConfirmModal = false;
      if (this.confirmModalResolve) {
        this.confirmModalResolve();
        // 重置引用
        this.confirmModalResolve = null;
        this.confirmModalReject = null;
      }
    },
    // 确认模态框取消按钮回调
    handleCancelSubmit() {
      // 关闭模态框
      this.showConfirmModal = false;
      if (this.confirmModalReject) {
        this.confirmModalReject(new Error('用户取消提交'));
        // 重置引用
        this.confirmModalResolve = null;
        this.confirmModalReject = null;
      }
    },
    
    // 开始新练习（从 API 加载题目）
    startNewPractice() {
      // 设置加载状态
      this.isPageLoading = true;
      this.isContentLoaded = false;
      
      // 首先获取题目结构信息，然后再加载第一块题目
      this.getQuestionStructure().then(structure => {

        // 构建精简的题目列表（包含uid和题型）
        const simplifiedQuestionList = structure.map((item, index) => ({
          uid: item.uid,
          exam_type: item.exam_type,
          // 保持与原有结构兼容的字段
          is_answered: this.checkIfAnswered(item.uid),
          user_answer: this.getUserAnswer(item.uid),
          is_correct: this.getQuestionCorrectness(item.uid)
        }));
        // 存储精简题目列表用于答题卡显示
        this.simplifiedQuestionList = simplifiedQuestionList;
        
        // 🔑 关键修复：初始化 swiperList 为包含所有占位符的列表
        // 这样 swiperList.length 就等于总题数，索引才能正确对应
        const initialPlaceholderList = simplifiedQuestionList.map(item => ({
          uid: item.uid,
          isPlaceholder: true,
          exam_type: item.exam_type
        }));
        examStore.setSwiperList(initialPlaceholderList);
        
        // 按chunkSize分批加载题目，而不是一次性加载所有题目
        const chunkSize = 10;
        
        // 🔑 关键修复：先标记块为已加载，防止重复加载
        this.loadedChunks.add(0);
        
        // 加载第一块题目
        this.fetchQuestionList(0, chunkSize).then(() => {
          // 强制更新组件状态，确保内容正确渲染
          this.$nextTick(() => {
            // 加载完成后，标记内容已加载
            this.isContentLoaded = true;
            // 延迟隐藏加载动画，确保内容平滑显示
            setTimeout(() => {
              this.isPageLoading = false;
            }, 300);
          });
        }).catch(err => {
          console.error('[startNewPractice] 加载第一块题目失败:', err);
          // 加载失败时也隐藏加载动画，避免长时间显示
          setTimeout(() => {
            this.isPageLoading = false;
            this.isContentLoaded = true;
          }, 2000);
        });
        
        // 延迟加载剩余的题目
        // for (let i = 1; i < totalChunks; i++) {
        //   setTimeout(() => {
        //     this.fetchQuestionList(i, chunkSize);
        //   }, i * 500); // 每块延迟500ms
        // }
      }).catch(async err => {
        console.error('[startNewPractice] 降级加载:', err);
        // 降级方案：先加载第一块题目获取总题数，然后初始化占位符列表
        const chunkSize = 10;
        await this.fetchQuestionList(0, chunkSize);
        
        // 如果获取到了总题数，初始化占位符列表
        if (this.totalQuestionCount > 0 && this.swiperList.length < this.totalQuestionCount) {
          const initialPlaceholderList = Array.from({ length: this.totalQuestionCount }, (_, index) => {
            // 如果该位置已有题目，使用已有题目
            if (index < this.swiperList.length && this.swiperList[index] && !this.swiperList[index].isPlaceholder) {
              return this.swiperList[index];
            }
            // 否则使用占位符
            return {
              uid: `placeholder_${index}`,
              isPlaceholder: true,
              exam_type: 'choice'
            };
          });
          examStore.setSwiperList(initialPlaceholderList);
        }
        
        // 加载失败时也隐藏加载动画，避免长时间显示
        setTimeout(() => {
          this.isPageLoading = false;
          this.isContentLoaded = true;
        }, 2000);
      });
    },
    
    // 模态框按钮点击事件处理
    handleModalClick(event) {
      // 根据按钮索引判断是确认还是取消操作
      if (event.index === 1) {
        // 确认按钮（索引1）
        this.handleConfirmSubmit();
      } else if (event.index === 0) {
        // 取消按钮（索引0）
        this.handleCancelSubmit();
      }
    },
    // 结果模态框确认按钮回调
    handleResultConfirm() {
      // 关闭模态框
      this.showResultModal = false;
      
      // 默认跳转到答题结果分析页面
      this.navigateToAnalysis();
    },
    
    // 结果模态框点击事件处理
    handleResultModalClick(event) {
      // 关闭模态框
      this.showResultModal = false;
      
      // 根据按钮索引执行不同的跳转逻辑
      if (event.index === 0) {
        // "知道了"按钮 - 跳转至questionContent.vue
        this.navigateToQuestionContent();
      } else if (event.index === 1) {
        // "查看解析"按钮 - 跳转至analysis.vue
        this.navigateToAnalysis();
      }
    },
    
    // 结果模态框"知道了"按钮点击事件
    handleResultClose() {
      // 关闭模态框
      this.showResultModal = false;
      
      // 跳转至questionContent.vue
      this.navigateToQuestionContent();
    },
    
    // 结果模态框"查看解析"按钮点击事件
    handleResultAnalysis() {
      // 关闭模态框
      this.showResultModal = false;
      
      // 跳转至analysis.vue
      this.navigateToAnalysis();
    },
    
    // 跳转到答题结果分析页面
    navigateToAnalysis() {
      // 跳转到analysis.vue页面
      this.$func.redirectTo('/subpages/examHistory/history/analysis?id=' + this.history_id);
    },
    
    // 跳转到questionContent.vue页面
    navigateToQuestionContent() {
      // 从questionParams中获取必要信息，或者使用默认值
      const examParams = this.questionParams || {};
      
      // 跳转到questionContent.vue页面，传递必要参数
      this.$func.redirectTo('/subpages/examHistory/examinationHistory?' + 
        'uid=' + (examParams.uid || '') + 
        '&questions_type=' + (examParams.questions_type || 0));
    },
    // 启动计时器
    startTimer() {
      // 清除之前的计时器（如果存在）
      if (this.timerId) {
        clearInterval(this.timerId)
        examStore.setState({ timerId: null })
      }
      
      const timerId = setInterval(() => {
        // 使用 store 更新 seconds
        examStore.setState({ seconds: examStore.state.seconds + 1 })
        
        // 倒计时模式下，检查时间是否结束
        if (this.isExamination && this.examTimeInSeconds > 0) {
          examStore.setState({ examTimeInSeconds: this.examTimeInSeconds - 1 })
          
          // 时间结束时处理
          if (this.examTimeInSeconds <= 0) {
            timerId && clearInterval(timerId)
            examStore.setState({ timerId: null })
            
            // 自动提交答案
            this.handleSubmitExam()
          }
        }
      }, 1000);
      
      examStore.setState({ timerId })
    },
    // 生命周期钩子：组件销毁时清除计时器
    beforeDestroy() {
      // 清除计时器
      if (this.timerId) {
        clearInterval(this.timerId)
        examStore.setState({ timerId: null })
      }

      // 取消 store 订阅
      if (this.unsubscribe) {
        this.unsubscribe();
      }

      // 清理预加载状态
      this.clearPreloadState();

      // 清理防抖定时器
      this.clearDebounceTimers();

      // 清理缓存
      this.clearComponentCache();

      // 清理题目数据
      this.clearQuestionData();

      // 清理内存
      this.garbageCollect();

      // 🔑 销毁 swipeHandler 实例（清理定时器和状态）
      if (this.swipeHandler) {
        this.swipeHandler.destroy();
        this.swipeHandler = null;
      }
    },
    
    // 清理预加载状态
    clearPreloadState() {
      this.preloadState.isPreloading = false;
      this.preloadState.preloadQueue = [];
      this.preloadState.lastPreloadTime = 0;
    },
    
    // 清理防抖定时器
    clearDebounceTimers() {
      if (this.likeDebounceTimer) {
        clearTimeout(this.likeDebounceTimer);
        this.likeDebounceTimer = null;
      }
      if (this.shareDebounceTimer) {
        clearTimeout(this.shareDebounceTimer);
        this.shareDebounceTimer = null;
      }
    },
    
    // 清理组件缓存
    clearComponentCache() {
      // 清除本地缓存的题目数据
      try {
        const keys = uni.getStorageInfoSync().keys;
        keys.forEach(key => {
          if (key.startsWith('data_processor_')) {
            uni.removeStorageSync(key);
          }
        });
      } catch (error) {
        console.error('清除本地缓存失败:', error);
      }
      
      // 清除 dataProcessor 的缓存
      if (typeof dataProcessor !== 'undefined' && typeof dataProcessor.clearAllCache === 'function') {
        dataProcessor.clearAllCache();
      }
    },
    
    // 清理题目数据
    clearQuestionData() {
      // 清除 store 中的题目列表
      examStore.setSwiperList([]);
      
      // 重置状态
      examStore.setState({
        swiperCurrentIndex: 0,
        answeredCount: 0,
        _cachedCurrentQuestion: null,
        _cachedQuestionIndex: -1
      });
    },
    
    // 内存垃圾回收
    garbageCollect() {
      // 强制垃圾回收（如果支持）
      if (typeof gc === 'function') {
        try {
          gc();
        } catch (error) {
          console.error('垃圾回收失败:', error);
        }
      }
      
      // 清理不需要的引用
      this.currentShareQuestion = null;
      this.savedProgress = null;
      this.isLiking = {};
    },
    
    // 定期清理内存
    scheduleMemoryCleanup() {
      // 每5分钟清理一次内存
      this.memoryCleanupTimer = setInterval(() => {
        this.clearComponentCache();
        this.garbageCollect();
      }, 5 * 60 * 1000);
    },    // 获取题目列表
    async fetchQuestionList(chunkIndex = 0, chunkSize = 10) {
      // 防止重复加载
      if (this.isLoading) {
        console.warn('[fetchQuestionList] 跳过，正在加载中');
        return Promise.resolve();
      }
      
      
      try {
        // 设置加载状态
        this.isLoading = true
        if (chunkIndex === 0) {
          uni.showLoading({ title: '加载中...' })
        }
        
        
        let apiMethod = ''
        // 使用统一的参数构建方法
        let params = {
          // ✅ 修改：如果存在 simplifiedQuestionList，按指定索引范围获取题目UID
          ...(this.simplifiedQuestionList && this.simplifiedQuestionList.length > 0 && 
            chunkIndex !== undefined && chunkSize !== undefined) 
            ? {
                // 计算要获取的题目索引范围
                question_start_index: chunkIndex * chunkSize,
                question_end_index: Math.min((chunkIndex + 1) * chunkSize, this.simplifiedQuestionList.length),
                // 获取对应范围内的题目UID列表
                question_uids: this.simplifiedQuestionList
                  .slice(chunkIndex * chunkSize, Math.min((chunkIndex + 1) * chunkSize, this.simplifiedQuestionList.length))
                  .map(item => item.uid)
              }
            : this.buildApiParams(chunkIndex, chunkSize)
        }
        
        // ✅ 根据是否有 question_uids 参数决定使用哪个 API 方法
        if (params.question_uids && params.question_uids.length > 0) {
          apiMethod = 'apiQuestionOrderListByUids';
        } else {
          // 根据题目类型选择不同的API
          const questionType = this.questionType || this.questionParams.questionType
          switch (questionType) {
            // 模考试题
            case 'mock':
              apiMethod = 'apiMockExaminationList'
              break
            // 正式考试（在线考试）
            case 'examination':
              apiMethod = 'apiExaminationQuestionList'
              break
            default:
              // 其他的（收藏、错题、顺序、章节练习等）全部默认按顺序获取题目
              apiMethod = 'apiQuestionOrderList'
          }
        }

        const res = await this.$api[apiMethod](params)
        
        // ✅ 增加调试日志：查看 API 返回的题目
        if (res && res.code === 1 && res.data) {
          const actualQuestionList = res.data.list || res.data.data || res.data;
          const actualCount = Array.isArray(actualQuestionList) ? actualQuestionList.length : 0;
        }

        
        // 从 API 返回结果获取总题数，确保总是使用最新的总题数
        if (res && res.data && res.data.total) {
          if (this.totalQuestionCount !== res.data.total) {
            this.totalQuestionCount = res.data.total;
          } 
        }
        
        
        if (res && res.code === 1 && res.data) {
          // 确保传递给processQuestionList的是有效的数组
          let rawQuestionList = []
          if (Array.isArray(res.data.list)) {
            rawQuestionList = res.data.list
          } else if (Array.isArray(res.data)) {
            rawQuestionList = res.data
          } else {
            console.warn('API返回的数据不是数组:', res.data)
            rawQuestionList = []
          }
          
          // 使用initQuestionList方法处理原始题目数据
          const questionList = this.initQuestionList(rawQuestionList)

          // 只有当题目列表非空时才更新
          if (questionList.length > 0) {
            // 🔑 关键修复：标记该块为已加载
            this.loadedChunks.add(chunkIndex);
            
            // 🔑 关键修复：所有加载都使用相同的合并逻辑，保持索引一致
            const currentList = examStore.state.swiperList || []
            
            // 🔑 关键修复：直接使用所有新题目，在合并时替换占位符
            // 不再限制题目数量，因为我们要替换占位符而不是添加新题目
            const finalQuestionList = questionList
            
            if (finalQuestionList.length > 0) {
              
              // 🔑 关键修复：使用所有新题目（不去重），在合并时替换占位符
              if (this.simplifiedQuestionList && this.simplifiedQuestionList.length > 0) {
                // 步骤1：为每个新题目添加其在 simplifiedQuestionList 中的目标索引
                const questionsWithIndex = finalQuestionList.map(newQuestion => {
                  const targetIndex = this.simplifiedQuestionList.findIndex(item => item.uid === newQuestion.uid);
                  return {
                    question: newQuestion,
                    targetIndex: targetIndex
                  };
                });
                
                // 步骤2：按照 targetIndex 升序排序，确保顺序插入
                questionsWithIndex.sort((a, b) => a.targetIndex - b.targetIndex);
                
                // 步骤3：创建已有题目的 Map，便于快速查找
                const existingQuestionsMap = new Map();
                for (const question of currentList) {
                  if (question && question.uid) {
                    existingQuestionsMap.set(question.uid, question);
                  }
                }
                
                // 步骤4：按 simplifiedQuestionList 顺序重建列表
                const mergedList = [];
                let newQuestionsIndex = 0;
                
                for (let i = 0; i < this.simplifiedQuestionList.length; i++) {
                  const simplifiedQuestion = this.simplifiedQuestionList[i];
                  let added = false;
                  
                  // 检查该位置是否有新题目需要插入
                  while (newQuestionsIndex < questionsWithIndex.length && 
                         questionsWithIndex[newQuestionsIndex].targetIndex === i) {
                    mergedList.push(questionsWithIndex[newQuestionsIndex].question);
                    newQuestionsIndex++;
                    added = true;
                  }
                  
                  // 检查该位置是否已有题目（使用 Map 查找）
                  if (!added && existingQuestionsMap.has(simplifiedQuestion.uid)) {
                    mergedList.push(existingQuestionsMap.get(simplifiedQuestion.uid));
                    added = true;
                  }
                  
                  // 如果该位置没有题目，添加占位符（保持索引一致）
                  if (!added) {
                    mergedList.push({
                      uid: simplifiedQuestion.uid,
                      isPlaceholder: true,
                      question: null
                    });
                  }
                }
                
                // 步骤5：处理在 simplifiedQuestionList 中找不到的题目（追加到末尾）
                while (newQuestionsIndex < questionsWithIndex.length) {
                  if (questionsWithIndex[newQuestionsIndex].targetIndex === -1) {
                    mergedList.push(questionsWithIndex[newQuestionsIndex].question);
                  }
                  newQuestionsIndex++;
                }
                
                // 🔑 关键修复：在替换 swiperList 前，记录当前题目的 uid
                const currentQuestionUid = this.swiperList[this.swiperCurrentIndex]?.uid;
                
                
                examStore.setSwiperList(mergedList);
                
                // 🔑 关键修复：在替换 swiperList 后，重新定位当前题目的索引
                if (currentQuestionUid) {
                  const newIndex = mergedList.findIndex(item => item.uid === currentQuestionUid);
                  if (newIndex !== -1 && newIndex !== this.swiperCurrentIndex) {
                    this.swiperCurrentIndex = newIndex;
                  }
                }
              } else {
                // 如果没有 simplifiedQuestionList，回退到原来的追加逻辑
                examStore.setSwiperList([...currentList, ...finalQuestionList]);
              }
            }
          }

          // 只有当题目列表非空时才计算已答题数量
          if (questionList.length > 0) {
            examStore.calculateAnsweredCount()
          }
          
          // 确保数据更新后触发重新渲染
          this.$nextTick(() => {

            if (chunkIndex === 0) {
              // 🔑 初始化显示索引
              this.swiperDisplayIndex = 0;
              
              // 确保当前索引正确设置
              examStore.setCurrentIndex(0);
              // 清除缓存并强制重新计算 currentQuestion
              examStore.clearComputedCache();
              // 强制更新组件状态
              this.$forceUpdate();
              // 调用调试方法
              this.debugAndFixData();
            }
            
            // 预加载下一块题目：当返回的数据长度大于0时就预加载
            // 避免API返回空数据时的无限循环，但要确保加载完所有题目
            // 当chunkIndex为0时，不要预加载下一块题目，因为smartPreload方法会在1秒后执行，会根据当前进度进行预加载
            if (questionList.length > 0 && chunkIndex !== 0) {
              // 检查是否还有未加载的题目
              const currentListLength = examStore.state.swiperList.length
              const hasMoreQuestions = !this.totalQuestionCount || currentListLength < this.totalQuestionCount
              
              if (hasMoreQuestions) {
                this.preloadNextChunk(chunkIndex + 1, chunkSize)
              }
            }
            
            // 移除自动加载剩余题块的逻辑，避免连锁预加载
            // 预加载应该由smartPreload方法根据用户行为触发，而不是自动批量加载
          })
        } else {
          console.warn('API返回失败:', {
            code: res?.code,
            msg: res?.msg
          });
          this.$func.showToast(res.msg || '获取题目失败')
        }
      } catch (error) {
        console.error('题目加载异常:', error);
        this.$func.showToast('加载失败，请重试')
      } finally {
        this.isLoading = false
        if (chunkIndex === 0) {
          uni.hideLoading()
        }
      }
    },
    
    // 预加载下一块题目
    preloadNextChunk(chunkIndex, chunkSize) {
      if (!this.preloadConfig.enabled) return
      
      // 检查预加载间隔
      const now = Date.now()
      if (now - this.preloadState.lastPreloadTime < this.preloadConfig.minPreloadTime) {
        return
      }
      
      // 检查是否正在预加载
      if (this.preloadState.isPreloading) {
        // 添加到预加载队列，避免重复添加
        if (!this.preloadState.preloadQueue.includes(chunkIndex)) {
          this.preloadState.preloadQueue.push(chunkIndex)
        }
        return
      }
      
      // 检查是否已经在预加载队列中
      if (this.preloadState.preloadQueue.includes(chunkIndex)) {
        return
      }
      
      // 检查目标块的所有题目是否都已经在 swiperList 中（且不是占位符）
      const chunkStartNum = chunkIndex * chunkSize;
      const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
      let loadedCount = 0;
      for (let i = chunkStartNum; i < chunkEndNum; i++) {
        const uid = this.simplifiedQuestionList[i]?.uid;
        // 🔑 关键修复：只统计非占位符的题目
        const item = this.swiperList.find(item => item.uid === uid);
        if (uid && item && !item.isPlaceholder) {
          loadedCount++;
        }
      }
      if (loadedCount === chunkEndNum - chunkStartNum) {
        return;
      }
      
      // 检查是否超过总题数
      const expectedStartIndex = chunkIndex * chunkSize;
      if (this.totalQuestionCount > 0 && expectedStartIndex >= this.totalQuestionCount) {
        return
      }
      
      // 执行预加载
      this.executePreload(chunkIndex, chunkSize)
    },
    
    // 预加载上一个题块
    preloadPreviousChunk(chunkIndex, chunkSize) {
      if (!this.preloadConfig.enabled) return
      
      // 检查预加载间隔
      const now = Date.now()
      if (now - this.preloadState.lastPreloadTime < this.preloadConfig.minPreloadTime) {
        return
      }
      
      // 检查是否正在预加载
      if (this.preloadState.isPreloading) {
        // 添加到预加载队列，避免重复添加
        if (!this.preloadState.preloadQueue.includes(chunkIndex)) {
          this.preloadState.preloadQueue.push(chunkIndex)
        }
        return
      }
      
      // 检查是否已经在预加载队列中
      if (this.preloadState.preloadQueue.includes(chunkIndex)) {
        return
      }
      
      // 检查是否是第一块，第一块不需要加载上一个块
      if (chunkIndex < 0) {
        return
      }
      
      // 🔑 关键修复：检查目标块的所有题目是否都已经在 swiperList 中且不是占位符
      const chunkStartNum = chunkIndex * chunkSize;
      const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
      let loadedCount = 0;
      for (let i = chunkStartNum; i < chunkEndNum; i++) {
        const uid = this.simplifiedQuestionList[i]?.uid;
        // 🔑 关键修复：只统计非占位符的题目
        const item = this.swiperList.find(item => item.uid === uid);
        if (uid && item && !item.isPlaceholder) {
          loadedCount++;
        }
      }
      if (loadedCount === chunkEndNum - chunkStartNum) {
        return;
      }
      
      // 检查是否超过总题数
      const expectedStartIndex = chunkIndex * chunkSize;
      if (this.totalQuestionCount > 0 && expectedStartIndex >= this.totalQuestionCount) {
        return
      }
      
      // 执行预加载
      this.executePreload(chunkIndex, chunkSize)
    },
    
    // 执行预加载
    async executePreload(chunkIndex, chunkSize) {
      try {
        // 检查是否已经加载过该块
        const currentListLength = this.swiperList.length
        
        // 检查是否超过总题数
        if (this.totalQuestionCount > 0 && chunkIndex * chunkSize >= this.totalQuestionCount) {
          // 处理队列中的下一个任务
          this.processPreloadQueue(chunkSize)
          return
        }
        
        // 当currentListLength为0时，只有当chunkIndex为0时才预加载（第一次加载）
        // 避免刚进入页面时重复加载
        if (currentListLength === 0) {
          if (chunkIndex !== 0) {
            // 处理队列中的下一个任务
            this.processPreloadQueue(chunkSize)
            return
          }
        } else if (chunkIndex * chunkSize < currentListLength) {
          // 检查该块是否都是非占位符
          const chunkStart = chunkIndex * chunkSize;
          const chunkEnd = Math.min(chunkStart + chunkSize, this.swiperList.length);
          let hasPlaceholder = false;
          for (let i = chunkStart; i < chunkEnd; i++) {
            if (this.swiperList[i]?.isPlaceholder) {
              hasPlaceholder = true;
              break;
            }
          }
          if (!hasPlaceholder) {
            // 该块已加载且没有占位符，跳过
            this.processPreloadQueue(chunkSize)
            return
          }
          // 有占位符，继续加载
        }
        
        this.preloadState.isPreloading = true
        this.preloadState.lastPreloadTime = Date.now()
        
        await this.fetchQuestionList(chunkIndex, chunkSize)
        
        // 预加载完成后，处理队列中的下一个任务
        this.processPreloadQueue(chunkSize)
      } catch (error) {
        console.error('预加载题目失败:', error)
        // 即使失败也处理队列中的下一个任务
        this.processPreloadQueue(chunkSize)
      } finally {
        this.preloadState.isPreloading = false
      }
    },
    
    // 处理预加载队列
    processPreloadQueue(chunkSize) {
      if (this.preloadState.preloadQueue.length > 0) {
        const nextChunkIndex = this.preloadState.preloadQueue.shift()
        this.executePreload(nextChunkIndex, chunkSize)
      }
    },
    
    // 智能预加载 - 基于用户浏览速度
    smartPreload(currentIndex) {
      if (!this.preloadConfig.enabled) return
      
      // 计算需要预加载的块索引
      const chunkSize = this.preloadConfig.chunkSize
      const currentChunkIndex = Math.floor(currentIndex / chunkSize)
      const preloadDistance = this.calculatePreloadDistance()
      
      // 只有当currentIndex接近当前块末尾时，才预加载下一块
      // 避免在块开始位置就预加载，导致重复请求
      // 优化触发条件：只有当距离块末尾不足1题时才预加载，减少重复请求
      const distanceToChunkEnd = (currentChunkIndex + 1) * chunkSize - currentIndex
      
      // 检查是否已经为当前块预加载过
      if (!this.preloadState.preloadedChunks) {
        this.preloadState.preloadedChunks = new Set()
      }
      
      // 增强边界检查：确保不会预加载不存在的块
      if (!this.swiperList || this.swiperList.length === 0) {
        return;
      }
      
      // 🔑 修复：根据总题数计算总块数，而不是根据当前已加载的题目数
      const totalChunks = Math.ceil(this.totalQuestionCount  / chunkSize);
      
      // 当距离块末尾不足2题时触发预加载（第9题时开始加载），提升用户体验
      if (distanceToChunkEnd <= 2 && !this.preloadState.preloadedChunks.has(currentChunkIndex) && currentChunkIndex < totalChunks - 1) {
        // 标记当前块为已预加载
        this.preloadState.preloadedChunks.add(currentChunkIndex)
        
        // 预加载当前块后面的几个块
        // 只预加载下一块，避免过度预加载
        const chunkIndex = currentChunkIndex + 1
        if (chunkIndex < totalChunks) {
          this.preloadNextChunk(chunkIndex, chunkSize)
        }
      }
      
      // 计算距离块开始位置的距离，用于预加载上一个题块
      const distanceToChunkStart = currentIndex - currentChunkIndex * chunkSize
      
      
      // 🔑 关键修复：当距离块开始位置不足2题时触发预加载（第1题时开始加载），提升用户体验
      // 并且确保当前块不是第一块，且未预加载过
      if (distanceToChunkStart <= 2 && !this.preloadState.preloadedChunks.has(currentChunkIndex) && currentChunkIndex > 0) {
        // 标记当前块为已预加载（上一块）
        this.preloadState.preloadedChunks.add(currentChunkIndex)
        
        // 预加载上一个块
        const chunkIndex = currentChunkIndex - 1
        if (chunkIndex >= 0) {
          this.preloadPreviousChunk(chunkIndex, chunkSize)
        }
      }
    },
    
    // 计算预加载距离 - 基于用户浏览速度
    calculatePreloadDistance() {
      // 根据用户浏览速度调整预加载距离
      const baseDistance = this.preloadConfig.preloadDistance
      // 修复：限制速度因子，避免过度预加载
      const speedFactor = Math.min(this.preloadState.userSpeed * 1, 1) // 速度因子最大为1
      return Math.max(baseDistance, Math.round(baseDistance + speedFactor))
    },
    
    // 更新用户浏览速度
    updateUserSpeed(startIndex, endIndex, timeSpent) {
      if (timeSpent === 0) return
      
      const distance = Math.abs(endIndex - startIndex)
      const speed = distance / (timeSpent / 1000) // 题目/秒
      
      // 平滑更新速度
      this.preloadState.userSpeed = (this.preloadState.userSpeed * 0.7) + (speed * 0.3)
    },
    
    // 空闲时间预加载
    scheduleIdlePreload() {
      if (!this.preloadConfig.enabled) return
      
      // 使用 requestIdleCallback 或 setTimeout 模拟
      const idleCallback = typeof requestIdleCallback === 'function' ? requestIdleCallback : setTimeout
      
      idleCallback(() => {
        // 🔑 关键修复：使用 displayQuestionIndex 而不是 swiperCurrentIndex 来触发预加载
        this.smartPreload(this.displayQuestionIndex)
      }, 0)
    },
    
    // 初始化题目列表
    initQuestionList(list) {
      
      return list.map((item, index) => {
        // 确保题目有uid字段
        if (!item.uid) {
          item.uid = `question_${index}_${Date.now()}`
        }
        
        // 解析option字段（如果是JSON字符串）
        if (typeof item.option === 'string') {
          try {
            item.option = JSON.parse(item.option);
          } catch (e) {
            console.warn('[题目初始化] 解析option字段失败:', item.uid, item.option);
            item.option = [];
          }
        }
        
        // 确保option是数组
        if (!Array.isArray(item.option)) {
          item.option = [];
        }
        
        // 确保每个选项都有必要的字段
        item.option = item.option.map((opt, idx) => ({
          check: opt.check || String.fromCharCode(65 + idx),
          title: opt.title || '',
          is_check: opt.is_check || '',
          is_selected: false,
          status: 'default',
          ...opt
        }));
        
        // 初始化题目状态
        item.is_selected = false
        item.is_submitted = false // 所有模式下都初始化为未提交，背题模式下也需要用户手动提交
        item.is_correct = undefined
        item.is_like = item.is_like || false
        item.is_collection = item.is_collection || false
        item.like_count = item.like_count || 0
        item.collect_count = item.collect_count || 0
        
        // 处理用户答案：从questionParams中获取userAnswers并应用到题目
        let userAnswerArray = [];
        if (this.questionParams && this.questionParams.userAnswers) {
          // 将item.uid转换为字符串格式，确保与userAnswers中的key匹配
          const questionUidStr = String(item.uid);
          if (this.questionParams.userAnswers[questionUidStr]) {
            let userAnswer = this.questionParams.userAnswers[questionUidStr];
            // 确保userAnswer是数组格式
            const rawUserAnswerArray = Array.isArray(userAnswer) ? userAnswer : [userAnswer];
            
            // 初始化最终用户答案数组
            userAnswerArray = [];
            
            // 处理每个用户答案，支持字符串化的JSON格式
            rawUserAnswerArray.forEach(rawAnswer => {
              if (typeof rawAnswer === 'string') {
                // 尝试解析JSON格式的用户答案，支持双字符串化的情况
                let parsedAnswer = rawAnswer;
                let parseAttempts = 0;
                const maxParseAttempts = 2;
                
                // 尝试解析最多2次，处理双字符串化的情况
                while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
                  try {
                    parsedAnswer = JSON.parse(parsedAnswer);
                    parseAttempts++;
                  } catch (e) {
                    break;
                  }
                }
                
                // 处理解析结果
                if (Array.isArray(parsedAnswer)) {
                  // 解析后是数组，合并到最终数组
                  userAnswerArray = userAnswerArray.concat(parsedAnswer);
                } else {
                  // 其他类型，直接添加到最终数组
                  userAnswerArray.push(String(parsedAnswer));
                }
              } else {
                // 非字符串类型，直接添加到最终数组
                userAnswerArray.push(String(rawAnswer));
              }
            });
            
            // 处理字符串格式的答案列表：如 "C, D, E" -> ["C", "D", "E"]
            if (userAnswerArray.length === 1 && typeof userAnswerArray[0] === 'string') {
              const answerStr = userAnswerArray[0];
              
              // 优先处理JSON数组格式，如 "["D"]" -> ["D"]
              if (answerStr.startsWith('[') && answerStr.endsWith(']')) {
                try {
                  const parsed = JSON.parse(answerStr);
                  if (Array.isArray(parsed)) {
                    userAnswerArray = parsed;
                  }
                } catch (e) {
                  // 解析失败，继续处理其他情况
                }
              } 
              // 处理逗号分隔的答案列表
              else if (answerStr.includes(',') || answerStr.includes('，') || answerStr.includes('、')) {
                // 支持多种分隔符
                userAnswerArray = answerStr.split(/[,，、]/)
                  .map(ans => ans.trim())
                  .filter(ans => ans !== '');
              }
            }
          }
        }
        
        // 解析正确答案并设置选项的is_correct字段
        if (item.answer && item.option && Array.isArray(item.option)) {
          let correctAnswers = [];
          
          // 处理不同格式的正确答案
          if (Array.isArray(item.answer)) {
            // 直接使用数组格式答案
            correctAnswers = item.answer;
          } else if (typeof item.answer === 'string') {
            const answerStr = item.answer.trim();
            
            if (answerStr.startsWith('[') && answerStr.endsWith(']')) {
              // 处理JSON数组格式，如 '["D","E","C"]' -> ["D","E","C"]
              try {
                correctAnswers = JSON.parse(answerStr);
              } catch (e) {
                // 解析失败，尝试按分隔符分割
                correctAnswers = answerStr
                  .slice(1, -1)
                  .replace(/["']/g, '')
                  .split(/[,，、]/)
                  .map(ans => ans.trim())
                  .filter(ans => ans !== '');
              }
            } else {
              // 处理字符串格式，如 'D,E,C' 或 'D、E、C'
              correctAnswers = answerStr
                .split(/[,，、]/)
                .map(ans => ans.trim())
                .filter(ans => ans !== '');
            }
          }
          
          // 确保correctAnswers是数组
          correctAnswers = Array.isArray(correctAnswers) ? correctAnswers : [correctAnswers];
          
          // 标准化正确答案：去除空格，转换为大写
          const normalizedCorrectAnswers = correctAnswers
            .map(ans => String(ans).trim().toUpperCase())
            .filter(ans => ans !== '');
          
          // 设置选项的is_correct字段
          item.option.forEach(opt => {
            if (opt && opt.check) {
              const normalizedCheck = String(opt.check).trim().toUpperCase();
              opt.is_correct = normalizedCorrectAnswers.includes(normalizedCheck);
            }
          });
        }
        
        // 根据题型设置正确的user_answer格式
        // 单选题(1)和判断题(3)：字符串格式
        // 多选题(2)：数组格式
        // 问答题(4,5)和案例题(6)：字符串格式
        if (item.exam_type === 1 || item.exam_type === 3) {
          // 单选题和判断题，使用字符串格式
          // 如果userAnswerArray是数组且有多个元素，取第一个；否则转换为字符串
          const finalAnswer = userAnswerArray.length > 0 ? String(userAnswerArray[0]) : '';
          // 清理字符串，去除可能的括号和引号
          const cleanedAnswer = finalAnswer.replace(/[\[\]"]/g, '').trim();
          item.user_answer = cleanedAnswer || undefined;
        } else if (item.exam_type === 2) {
          // 多选题，使用数组格式
          // 清理数组元素，去除可能的括号和引号
          const cleanedArray = userAnswerArray.map(ans => String(ans).replace(/[\[\]"]/g, '').trim()).filter(item => item !== '');
          item.user_answer = cleanedArray.length > 0 ? cleanedArray : undefined;
        } else {
          // 其他题型（问答题、案例题等），使用字符串格式
          // 清理字符串，去除可能的括号和引号
          const finalAnswer = userAnswerArray.length > 0 ? userAnswerArray.join(',').replace(/[\[\]"]/g, '') : '';
          const cleanedAnswer = finalAnswer.replace(/\s*/g, '').replace(/[，、]/g, ',').trim();
          item.user_answer = cleanedAnswer || undefined;
        }
        
        // 检查是否是案例题（exam_type=6），如果是，为子试题应用用户答案
        if (item.exam_type === 6 && item.option && Array.isArray(item.option)) {
          // 遍历案例题的子试题
          item.option.forEach(subQuestion => {
            if (subQuestion.uid) {
              const subUidStr = String(subQuestion.uid);
              if (this.questionParams.userAnswers[subUidStr]) {
                let subUserAnswer = this.questionParams.userAnswers[subUidStr];
                // 确保子试题的user_answer始终是数组格式
                const rawSubAnswerArray = Array.isArray(subUserAnswer) ? subUserAnswer : [subUserAnswer];
                
                // 处理每个子答案，支持字符串化的JSON格式和逗号分隔格式
                let finalSubAnswerArray = [];
                rawSubAnswerArray.forEach(rawSubAnswer => {
                  if (typeof rawSubAnswer === 'string') {
                    // 处理字符串格式的答案列表：如 "C, D, E" -> ["C", "D", "E"]
                    if (rawSubAnswer.includes(',') || rawSubAnswer.includes('，') || rawSubAnswer.includes('、')) {
                      // 支持多种分隔符
                      const splitAnswers = rawSubAnswer.split(/[,，、]/)
                        .map(ans => ans.trim())
                        .filter(ans => ans !== '');
                      finalSubAnswerArray = finalSubAnswerArray.concat(splitAnswers);
                    } else {
                      finalSubAnswerArray.push(rawSubAnswer);
                    }
                  } else {
                    finalSubAnswerArray.push(String(rawSubAnswer));
                  }
                });
                
                subQuestion.user_answer = finalSubAnswerArray;
              }
              
              // 解析案例题子试题的正确答案，支持字符串化的JSON数组格式
              let subCorrectAnswerArray = [];
              if (Array.isArray(subQuestion.answer)) {
                subCorrectAnswerArray = subQuestion.answer;
              } else if (typeof subQuestion.answer === 'string') {
                // 尝试解析JSON格式的答案，支持双字符串化的JSON（如："["D"]" 或 ""["D"]""）
                let parsedAnswer = subQuestion.answer;
                let parseAttempts = 0;
                const maxParseAttempts = 2;
                
                // 尝试解析最多2次，处理双字符串化的情况
                while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
                  try {
                    parsedAnswer = JSON.parse(parsedAnswer);
                    parseAttempts++;
                  } catch (e) {
                    break;
                  }
                }
                
                // 处理解析结果
                if (Array.isArray(parsedAnswer)) {
                  subCorrectAnswerArray = parsedAnswer;
                } else if (typeof parsedAnswer === 'string') {
                  // 解析后是单个字符串，如 "D"，转换为数组
                  subCorrectAnswerArray = [parsedAnswer];
                } else {
                  // 其他类型，直接转换为数组
                  subCorrectAnswerArray = [String(parsedAnswer)];
                }
                
                // 最后的处理：如果解析结果还是字符串且包含逗号或顿号，尝试分割
                if (typeof subCorrectAnswerArray[0] === 'string' && 
                    (subCorrectAnswerArray[0].includes(',') || subCorrectAnswerArray[0].includes('、'))) {
                  // 支持多种分隔符：逗号、顿号、空格
                  subCorrectAnswerArray = subCorrectAnswerArray[0].split(/[,，、]/).map(ans => ans.trim());
                }
                } else {
                  // 其他类型，直接转换为数组
                  subCorrectAnswerArray = [String(subQuestion.answer)];
                }
                






                  
              }
            });
          }
        // 初始化选项状态
        if (item.option && Array.isArray(item.option)) {
          // 解析正确答案 - 支持字符串化的JSON数组格式
          let correctAnswerArray = [];
          if (Array.isArray(item.answer)) {
            correctAnswerArray = item.answer;
          } else if (typeof item.answer === 'string') {
            // 尝试解析JSON格式的答案，支持双字符串化的JSON（如："["D"]" 或 ""["D"]""）
            let parsedAnswer = item.answer;
            let parseAttempts = 0;
            const maxParseAttempts = 2;
            
            // 尝试解析最多2次，处理双字符串化的情况
            while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
              try {
                parsedAnswer = JSON.parse(parsedAnswer);
                parseAttempts++;
              } catch (e) {
                break;
              }
            }
            
            // 处理解析结果
            if (Array.isArray(parsedAnswer)) {
              correctAnswerArray = parsedAnswer;
            } else if (typeof parsedAnswer === 'string') {
              // 解析后是单个字符串，如 "D"，转换为数组
              correctAnswerArray = [parsedAnswer];
            } else {
              // 其他类型，直接转换为数组
              correctAnswerArray = [String(parsedAnswer)];
            }
            
            // 最后的处理：如果解析结果还是字符串且包含逗号，尝试分割
            if (typeof correctAnswerArray[0] === 'string' && correctAnswerArray[0].includes(',')) {
              correctAnswerArray = correctAnswerArray[0].split(',').map(ans => ans.trim());
            } else if (typeof correctAnswerArray[0] === 'string' && correctAnswerArray[0].includes('、')) {
              // 处理中文顿号分隔的情况
              correctAnswerArray = correctAnswerArray[0].split('、').map(ans => ans.trim());
            }
          } else {
            // 其他类型，直接转换为数组
            correctAnswerArray = [String(item.answer)];
          }
          





            
          
          item.option.forEach(opt => {
            // 初始化状态
            opt.is_user_correct = false
            opt.is_user_wrong = false
            // 保留之前设置的is_correct值，不覆盖
            opt.is_selected = false
            
            // 标记正确答案，支持模糊比较
            for (const correctAnswer of correctAnswerArray) {
              // 标准化正确答案和选项值：去除首尾空格，转换为小写
              const normalizedCorrectAnswer = String(correctAnswer).trim().toLowerCase();
              const normalizedOptCheck = String(opt.check).trim().toLowerCase();
              
              // 模糊比较：包含即正确
              if (normalizedCorrectAnswer.includes(normalizedOptCheck) || normalizedOptCheck.includes(normalizedCorrectAnswer)) {
                opt.is_correct = true;
                break; // 找到匹配的正确答案，跳出循环
              }
            }
            
            // 应用用户答案到选项状态
            if (userAnswerArray.length > 0) {
              // 遍历用户答案数组
              userAnswerArray.forEach(userAnswer => {
                // 处理用户答案可能是字符串的情况
                const userAnswers = typeof userAnswer === 'string' ? userAnswer.split(/[,，、]/) : [userAnswer];
                
                // 遍历分割后的用户答案
                userAnswers.forEach(ans => {
                  // 标准化用户答案和选项值：去除首尾空格，转换为小写
                  const normalizedAns = String(ans).trim().toLowerCase();
                  const normalizedOptCheck = String(opt.check).trim().toLowerCase();
                  
                  // 模糊比较：包含即正确
                  if (normalizedAns.includes(normalizedOptCheck) || normalizedOptCheck.includes(normalizedAns)) {
                    opt.is_selected = true
                    // 判断用户选择是否正确
                    if (opt.is_correct) {
                      opt.is_user_correct = true
                    } else {
                      opt.is_user_wrong = true
                    }
                  }
                });
              });
            }
          })
          
          // 添加题目整体正确性判断逻辑
          if (item.exam_type === 2) {
            // 多选题：使用排序后比较的方式判断正确性
            const userSelected = item.option
              .filter(opt => opt.is_selected)
              .map(opt => String(opt.check).trim().toUpperCase())
              .filter(Boolean)
              .sort();
            
            const correctAnswers = item.option
              .filter(opt => opt.is_correct)
              .map(opt => String(opt.check).trim().toUpperCase())
              .filter(Boolean)
              .sort();
            
            // 比较排序后的数组是否完全一致
            item.is_correct = JSON.stringify(userSelected) === JSON.stringify(correctAnswers);
          } else if (item.exam_type === 1 || item.exam_type === 3) {
            // 单选题和判断题：只有一个正确选项，只要用户选择了正确选项就正确
            const userSelectedCorrect = item.option.some(opt => opt.is_selected && opt.is_correct);
            const userSelectedWrong = item.option.some(opt => opt.is_selected && !opt.is_correct);
            
            item.is_correct = userSelectedCorrect && !userSelectedWrong;
          }
        }
        
        // 背题模式：设置默认值确保组件完整显示
        if (this.currentMode === 'reviewOnly') {
          this.markCorrectAnswers(item)
          // 背题模式下，强制设置is_correct为true，确保答案显示正确
          item.is_correct = true
          // 注意：背题模式下不要设置is_submitted = true，否则提交按钮会被隐藏
          // item.is_submitted = true // 移除这行代码，确保提交按钮能正常显示和响应
        }
        
        return item;
      });
    },
    
    // 标记正确答案（背题模式）
    markCorrectAnswers(question) {
      // 添加对question参数的检查，避免TypeError
      if (!question || !question.answer) return;
      
      // 优先保留用户答案，只有在没有用户答案时才使用正确答案
      if (!question.user_answer || question.user_answer.length === 0) {
        // 将正确答案赋值给user_answer
        if (question.answer) {
          // 确保user_answer始终是数组格式，与后端期望一致
          question.user_answer = Array.isArray(question.answer) 
            ? question.answer 
            : [question.answer]
        }
      }
      
      // 标记正确选项（仅适用于有选项的题型）
      if (question.option && Array.isArray(question.option)) {
        // 解析正确答案，支持双字符串化的JSON数组格式
        let correctAnswerArray = [];
        if (Array.isArray(question.answer)) {
          correctAnswerArray = question.answer;
        } else if (typeof question.answer === 'string') {
          // 尝试解析JSON格式的答案，支持双字符串化的JSON（如："["D"]" 或 ""["D"]""）
          let parsedAnswer = question.answer;
          let parseAttempts = 0;
          const maxParseAttempts = 2;
          
          // 尝试解析最多2次，处理双字符串化的情况
          while (typeof parsedAnswer === 'string' && parseAttempts < maxParseAttempts) {
            try {
              parsedAnswer = JSON.parse(parsedAnswer);
              parseAttempts++;
            } catch (e) {
              break;
            }
          }
          
          // 处理解析结果
          if (Array.isArray(parsedAnswer)) {
            correctAnswerArray = parsedAnswer;
          } else if (typeof parsedAnswer === 'string') {
            // 解析后是单个字符串，如 "D"，转换为数组
            correctAnswerArray = [parsedAnswer];
          } else {
            // 其他类型，直接转换为数组
            correctAnswerArray = [String(parsedAnswer)];
          }
          
          // 最后的处理：如果解析结果还是字符串且包含逗号或顿号，尝试分割
          if (typeof correctAnswerArray[0] === 'string') {
            const answerStr = correctAnswerArray[0];
            if (answerStr.includes(',') || answerStr.includes('，') || answerStr.includes('、')) {
              correctAnswerArray = answerStr.split(/[,，、]/).map(ans => ans.trim());
            }
          }
        } else {
          // 其他类型，直接转换为数组
          correctAnswerArray = [String(question.answer)];
        }
        
        question.option.forEach(opt => {
          // 先重置选项状态
          opt.is_correct = false;
          opt.is_selected = false;
          
          // 遍历用户答案，标记用户选择的选项
          if (question.user_answer) {
            const userAnswers = Array.isArray(question.user_answer) ? question.user_answer : [question.user_answer];
            for (const userAnswer of userAnswers) {
              // 标准化用户答案和选项值：去除首尾空格，转换为小写
              const normalizedUserAnswer = String(userAnswer).trim().toLowerCase();
              const normalizedOptCheck = String(opt.check).trim().toLowerCase();
              
              // 模糊比较：包含即选中
              if (normalizedUserAnswer.includes(normalizedOptCheck) || normalizedOptCheck.includes(normalizedUserAnswer)) {
                opt.is_selected = true;
                break;
              }
            }
          }
          
          // 遍历正确答案数组，标记正确选项
          for (const correctAnswer of correctAnswerArray) {
            // 标准化正确答案和选项值：去除首尾空格，转换为小写
            const normalizedCorrectAnswer = String(correctAnswer).trim().toLowerCase();
            const normalizedOptCheck = String(opt.check).trim().toLowerCase();
            
            // 模糊比较：包含即正确
            if (normalizedCorrectAnswer.includes(normalizedOptCheck) || normalizedOptCheck.includes(normalizedCorrectAnswer)) {
              opt.is_correct = true;
              break; // 找到匹配的正确答案，跳出循环
            }
          }
        });
        
        // 计算题目整体的is_correct值
        if (question.exam_type === 2) {
          // 多选题：使用排序后比较的方式判断正确性
          const userSelected = question.option
            .filter(opt => opt.is_selected)
            .map(opt => String(opt.check).trim().toUpperCase())
            .filter(Boolean)
            .sort();
          
          const correctAnswers = question.option
            .filter(opt => opt.is_correct)
            .map(opt => String(opt.check).trim().toUpperCase())
            .filter(Boolean)
            .sort();
          
          // 比较排序后的数组是否完全一致
          question.is_correct = JSON.stringify(userSelected) === JSON.stringify(correctAnswers);
        } else if (question.exam_type === 1 || question.exam_type === 3) {
          // 单选题和判断题：只有一个正确选项，只要用户选择了正确选项就正确
          const userSelectedCorrect = question.option.some(opt => opt.is_selected && opt.is_correct);
          const userSelectedWrong = question.option.some(opt => opt.is_selected && !opt.is_correct);
          
          question.is_correct = userSelectedCorrect && !userSelectedWrong;
        }
      }
    },
    
    // 计算已答题数量
    calculateAnsweredCount() {
      examStore.calculateAnsweredCount()
    },

    // 🔑 优化：手势识别 - touchstart，委托给 swipeHandler
    handleTouchStart(e) {
      if (!this.swipeHandler) return;
      const result = this.swipeHandler.handleTouchStart(e);

      // 如果检测到交互元素，临时禁用滑动
      if (result.isInteractive) {
        this.tempDisableSwipe = true;
      }
    },

    // 🔑 优化：手势识别 - touchmove，委托给 swipeHandler
    handleTouchMove(e) {
      if (!this.swipeHandler) return;
      const result = this.swipeHandler.handleTouchMove(e);

      // 根据 swipeHandler 的结果控制滑动禁用状态
      if (result.shouldDisableSwipe) {
        this.tempDisableSwipe = true;
      }
    },

    // 🔑 优化：手势识别 - touchend，委托给 swipeHandler
    handleTouchEnd(e) {
      if (!this.swipeHandler) return;
      const result = this.swipeHandler.handleTouchEnd(e);

      // 延迟恢复滑动（防止点击后立即触发滑动）
      if (this.tempDisableSwipe) {
        setTimeout(() => {
          this.tempDisableSwipe = false;
        }, 100);
      }

      // 如果是有效的水平滑动，自动保存答案
      if (result.isValidSwipe) {
        this.triggerAutoSave();
      }
    },

    // 🔑 优化：边界状态切换处理 - 委托给 swipeHandler
    handleBoundaryTransition(oldLength, newLength) {
      if (!this.swipeHandler) return;
      const result = this.swipeHandler.handleBoundaryTransition(oldLength, newLength, {
        currentIndex: this.swiperCurrentIndex,
        listLength: this.swiperList.length
      });

      if (!result.handled) return;

      // 根据 swipeHandler 结果更新状态
      if (result.needProgrammaticJump) {
        this.needProgrammaticJump = true;
      }
      if (result.swiperDisplayIndex !== undefined) {
        this.swiperDisplayIndex = result.swiperDisplayIndex;
      }
    },
    
    // 🔑 优化：swiper 滑动过程中 - 完全委托给 swipeHandler
    handleSwiperTransition(e) {
      if (!this.swipeHandler) return;
      const result = this.swipeHandler.handleSwiperTransition(e, {
        currentIndex: this.displayQuestionIndex,
        totalQuestions: this.simplifiedQuestionList?.length || 0
      });

      if (!result.handled) return;

      // 根据 swipeHandler 结果更新状态
      if (result.isComplete) {
        this.isUserSwiping = false;
      } else if (result.predictedIndex !== undefined) {
        this.isUserSwiping = true;
        this.transitionQuestionIndex = result.predictedIndex;
      }
    },
    
    // 🔑 优化：swiper切换事件 - 完全委托给 swipeHandler
    async handleSwiperChange(e) {
      // 🔑 新增：检查题块加载锁，避免滑动冲突
      if (this.isLoadingQuestions) {
        return;
      }

      // 🔑 新增：防护措施，防止重复索引更新
      if (this.isSwiperAnimating) {
        return;
      }

      // 清除之前的防抖定时器
      if (this.swipeDebounceTimer) {
        clearTimeout(this.swipeDebounceTimer);
        this.swipeDebounceTimer = null;
      }

      this.isSwiperAnimating = true;

      try {
        if (this.isLoading) {
          this.showSwipeLoading('题目加载中...');
        }

        if (this.needProgrammaticJump) {
          this.needProgrammaticJump = false;
          this.hideSwipeLoading();
          return;
        }

        // 等待加载完成
        let waitCount = 0;
        while (this.isLoading && waitCount < 10) {
          await new Promise(resolve => setTimeout(resolve, 100));
          waitCount++;
        }

        if (waitCount >= 10) {
          this.hideSwipeLoading();
          uni.showToast({ title: '加载超时，请重试', icon: 'none' });
          return;
        }

        // 🔑 新增：设置题块加载锁
        this.isLoadingQuestions = true;

        // 使用 swipeHandler 处理滑动
        if (!this.swipeHandler) {
          this.isLoadingQuestions = false;
          return;
        }
        const result = await this.swipeHandler.handleSwiperChange(e, {
          currentIndex: this.swiperCurrentIndex,
          swiperList: this.swiperList,
          activeQuestionsLength: this.activeQuestions.length,
          needProgrammaticJump: this.needProgrammaticJump,
          isLoading: this.isLoading
        });

        // 🔑 新增：释放题块加载锁（使用微任务确保渲染完成）
        this.$nextTick(() => {
          this.isLoadingQuestions = false;
        });



        if (!result.handled) {
          this.hideSwipeLoading();
          this.isSwiperAnimating = false;
          return;
        }

        // 处理程序化跳转重置
        if (result.shouldResetFlag) {
          this.needProgrammaticJump = false;
          this.hideSwipeLoading();
          return;
        }

        // 处理边界情况
        if (result.isBoundary) {

          this.hideSwipeLoading();
          this.isSwiperAnimating = false;
          return;
        }

        // 更新索引
        if (result.shouldUpdate && result.newIndex !== undefined) {
          
          // 更新滑动方向
          this.currentSwipeDirection = result.direction;
          
          // 🔑 关键修复：直接更新 examStore.state.swiperCurrentIndex，绕过异步的 setState
          // 这样可以立即更新状态，减少异步操作的延迟
          examStore.state.swiperCurrentIndex = result.newIndex;
          
          // 清除缓存的索引，确保下次获取时能获取到最新的索引
          this._lastProcessedIndex = -1;
          this._cachedQuestionIndex = -1;
          this._cachedCurrentQuestion = null;

          // 🔑 增强状态同步：立即强制更新组件状态，确保计算属性和依赖数据同步更新
          // 这样可以确保 displayQuestionIndex 等计算属性立即更新
          this.$forceUpdate();
          
          // 立即更新 swiper 的 current 属性，确保与 swiperCurrentIndex 同步
          if (this.$refs.swiper) {
            this.$refs.swiper.setCurrent(result.newIndex, false);
          }
          
          // 确保在DOM渲染完成后再执行其他操作
          this.$nextTick(() => {
            // 执行其他操作
            this.triggerAutoSave();
            this.smartPreload(this.displayQuestionIndex);
            this.hideSwipeLoading();
            this.isSwiperAnimating = false;
          });
        } else {
          this.hideSwipeLoading();
          this.isSwiperAnimating = false;
        }

        this._lastSwiperChangeTime = Date.now();

        this.$nextTick(() => {
          this.isUserSwiping = false;
          this.transitionQuestionIndex = -1;
        });

        this.checkLoadMoreQuestions();

      } catch (error) {
  
        this.hideSwipeLoading();
        this.isSwiperAnimating = false;
        this.swipeHandler?.forceRelease();
        this.checkSwipeAbnormality();

        if (this._errorRecoveryCount < this._maxErrorRecoveryCount) {
          this._errorRecoveryCount++;
          await this.recoverFromSwipeError();
        } else {
          uni.showToast({ title: '操作异常，请重试', icon: 'none', duration: 2000 });
          this._errorRecoveryCount = 0;
        }
      }
    },
    
    // 🔑 新增：显示滑动loading状态
    showSwipeLoading(text = '加载中...') {
      this.swipeLoadingState.isLoading = true;
      this.swipeLoadingState.loadingText = text;
      this.swipeLoadingState.loadingStartTime = Date.now();
      
      // 设置超时保护
      if (this.swipeLoadingState.loadingTimeout) {
        clearTimeout(this.swipeLoadingState.loadingTimeout);
      }
      this.swipeLoadingState.loadingTimeout = setTimeout(() => {

        this.hideSwipeLoading();
      }, 5000); // 5秒超时
    },
    
    // 🔑 新增：隐藏滑动loading状态
    hideSwipeLoading() {
      this.swipeLoadingState.isLoading = false;
      this.swipeLoadingState.loadingText = '';
      if (this.swipeLoadingState.loadingTimeout) {
        clearTimeout(this.swipeLoadingState.loadingTimeout);
        this.swipeLoadingState.loadingTimeout = null;
      }
    },
    
    // 🔑 新增：滑动异常恢复机制
    async recoverFromSwipeError() {      
      if (this._swipeAbnormalState.isRecovering) {
        return;
      }
      
      this._swipeAbnormalState.isRecovering = true;
      
      try {
        // 1. 强制释放滑动锁
        this.swipeHandler?.forceRelease();

        // 2. 重置所有滑动相关状态
        this.isUserSwiping = false;
        this.isSwiperAnimating = false;
        this.transitionQuestionIndex = -1;
        this.needProgrammaticJump = false;
        
        // 3. 清除所有定时器
        if (this.swipeDebounceTimer) {
          clearTimeout(this.swipeDebounceTimer);
          this.swipeDebounceTimer = null;
        }
        this.hideSwipeLoading();
        
        // 4. 验证当前索引的有效性
        const currentIndex = this.swiperCurrentIndex;
        const listLength = this.swiperList.length;
        
        if (currentIndex < 0 || currentIndex >= listLength) {
          console.warn('[recoverFromSwipeError] 索引越界，重置到安全位置');
          this.swiperCurrentIndex = Math.max(0, Math.min(currentIndex, listLength - 1));
        }
        
        // 5. 强制更新swiper显示位置
        const safeIndex = this.swiperCurrentIndex;
        if (safeIndex === 0) {
          this.swiperDisplayIndex = 0;
        } else if (safeIndex >= listLength - 1) {
          this.swiperDisplayIndex = 1;
        } else {
          this.swiperDisplayIndex = 1;
        }
        
        // 6. 强制刷新组件
        this.$forceUpdate();
        
        
        // 7. 显示恢复提示
        uni.showToast({
          title: '已恢复',
          icon: 'none',
          duration: 1000
        });
        
      } catch (error) {
        console.error('[recoverFromSwipeError] 恢复失败:', error);
      } finally {
        this._swipeAbnormalState.isRecovering = false;
        this._swipeAbnormalState.lastErrorTime = Date.now();
      }
    },
    
    // 🔑 新增：检测滑动异常
    checkSwipeAbnormality() {
      const now = Date.now();
      const timeSinceLastError = now - this._swipeAbnormalState.lastErrorTime;
      
      // 如果在5秒内发生多次错误，触发恢复
      if (timeSinceLastError < 5000) {
        this._swipeAbnormalState.errorCount++;
        
        if (this._swipeAbnormalState.errorCount >= 3) {
          console.warn('[checkSwipeAbnormality] 检测到滑动异常，触发恢复');
          this.recoverFromSwipeError();
          this._swipeAbnormalState.errorCount = 0;
        }
      } else {
        // 超过5秒，重置计数
        this._swipeAbnormalState.errorCount = 1;
      }
      
      this._swipeAbnormalState.lastErrorTime = now;
    },
    
    // 🔑 监听 Swiper 的实际显示位置
    handleSwiperRealChange(e) {
      const realCurrent = e.detail.current;
      
      // 🔑 关键修复：确保 swiper 显示正确的位置
      // 当实际显示位置与预期位置不符时，强制修正
      if (realCurrent !== this.swipeCurrent) {
        // 强制修正 swiper 位置
        if (this.$refs.swiper) {
          this.$refs.swiper.setCurrent(this.swipeCurrent, false);
          
          // 🔑 增强修复：再次检查位置，确保修正成功
          setTimeout(() => {
            const updatedRealCurrent = this.$refs.swiper.getCurrent();
            if (updatedRealCurrent !== this.swipeCurrent) {
              this.$refs.swiper.setCurrent(this.swipeCurrent, false);
            }
          }, 50);
        }
      }
    },
    
    // 选项选择事件
    handleOptionSelected(data) {
      // 🔑 关键修复：点击选项时临时禁用滑动，防止误触发
      this.tempDisableSwipe = true;
      
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
      
      // 延迟恢复滑动
      setTimeout(() => {
        this.tempDisableSwipe = false;
      }, 150);
    },
    
    // 答案显示事件统一处理
    handleAnswerShown(data) {
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
    },
    
    // 多选题提交
    handleMultipleSubmit(data) {
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
    },
    
    // 填空题提交
    handleFillSubmit(data) {
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
    },
    
    // 问答题提交
    handleEssaySubmit(data) {
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
    },
    
    // 案例题提交
    handleCompoundSubmit(data) {
      const { questionIndex, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, question);
        // 清除虚拟列表缓存，确保virtualSwiperList重新计算
        this._virtualListCache = null;
        // 强制更新组件状态，确保参考答案、解析、笔记等内容正确显示
        this.$forceUpdate();
      }
    },
    
    // 笔记添加成功回调
    handleNoteAdded(data) {
      uni.showToast({
        title: '笔记发布成功',
        icon: 'success',
        duration: 1500
      })
    },
    
    // 笔记点赞成功回调
    handleNoteLiked(data) {
      // 可以在这里添加点赞后的逻辑，例如刷新笔记列表
      // 暂时只是接收通知，不显示 Toast提示，因为 comment-section 组件已经显示过了
    },
    
    // 临时调试方法：直接检查和修复数据状态
    debugAndFixData() {

      // 如果组件中的数据与store中的数据不同步，强制同步
      if (this.swiperList.length !== examStore.state.swiperList.length) {
        this.$forceUpdate();
      }

      // 检查 store 中的题目列表是否为空，但组件认为有数据
      if (examStore.state.swiperList.length === 0 && this.swiperList.length > 0) {
        this.$forceUpdate();
      }

      // 检查组件中的题目列表是否为空，但 store 认为有数据
      if (this.swiperList.length === 0 && examStore.state.swiperList.length > 0) {
        this.$forceUpdate();
      }

      // 检查当前题目是否存在
      const currentQuestion = this.currentQuestion;
      if (!currentQuestion || !currentQuestion.uid) {
      }
    },
    
    // 答案变化事件
    handleAnswerChange(data) {
      const { questionIndex, answer, question } = data
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, {
          ...question,
          user_answer: answer
        });
      }
    },
    
    // 处理答案输入事件（用于问答题和案例题）
    handleAnswerInput(data) {
      const { questionIndex, answer, question, isFocused } = data
      
      // 🔑 关键修复：根据输入框聚焦状态控制滑动
      if (isFocused !== undefined) {
        this.isInputFocused = isFocused;
      }
      
      if (question && question.uid) {
        examStore.updateQuestion(question.uid, {
          ...question,
          user_answer: answer
        });
      }
    },
    
    // 上一题
    // 🔑 新增：按钮点击防抖和连续点击计数
    _checkButtonClickThrottle() {
      const now = Date.now();

      // 初始化连续点击计数
      if (!this._buttonClickCount) {
        this._buttonClickCount = 0;
      }

      // 检查是否超过3次连续点击
      if (this._buttonClickCount >= 3) {
        // 禁用1秒
        if (now - this._lastButtonClickTime < 1000) {
          uni.showToast({
            title: '点击过于频繁，请稍后再试',
            icon: 'none',
            duration: 1000
          });
          return false;
        }
        // 重置计数
        this._buttonClickCount = 0;
      }

      // 300ms 防抖
      if (this._lastButtonClickTime && now - this._lastButtonClickTime < 300) {
        this._buttonClickCount++;
        return false;
      }

      // 通过检查，重置计数并更新时间
      this._buttonClickCount = 0;
      this._lastButtonClickTime = now;
      return true;
    },

    handlePrevQuestion(withAnimation = false) {
      try {
        // 🔑 增强防抖处理
        if (!this._checkButtonClickThrottle()) {
          return;
        }

        // 🔑 关键修复：基于 displayQuestionIndex 计算上一题，确保和题号显示一致
        const currentQuestionNum = this.displayQuestionIndex;
        const prevQuestionNum = currentQuestionNum - 1;

        // 🔑 增强边界检查：已经是第一题，显示提示
        if (currentQuestionNum <= 0) {
          uni.showToast({
            title: '已是第一题',
            icon: 'none',
            duration: 1000
          });
          return;
        }

        // 🔑 增强边界检查：确保上一题索引在有效范围内
        if (prevQuestionNum >= 0 && this.simplifiedQuestionList && this.simplifiedQuestionList[prevQuestionNum]) {
          const prevUid = this.simplifiedQuestionList[prevQuestionNum].uid;
          const prevIndex = this.swiperList.findIndex(item => item.uid === prevUid);

          if (prevIndex !== -1) {
            // 自动保存当前题的进度
            this.triggerAutoSave();
            
            // 直接设置索引，移除动画
            this.swiperCurrentIndex = prevIndex;
          }
        }
      } catch (error) {
        console.error('上一题切换错误:', error);
      }
    },

    // 下一题
    handleNextQuestion(withAnimation = false) {
      try {
        // 🔑 增强防抖处理
        if (!this._checkButtonClickThrottle()) {
          return;
        }

        // 🔑 增强边界检查：确保 simplifiedQuestionList 存在且是一个数组
        if (!this.simplifiedQuestionList || !Array.isArray(this.simplifiedQuestionList) || !('length' in this.simplifiedQuestionList)) {
          console.error('simplifiedQuestionList 未初始化或格式不正确:', this.simplifiedQuestionList);
          return;
        }

        // 🔑 关键修复：基于 displayQuestionIndex 计算下一题，确保和题号显示一致
        const currentQuestionNum = this.displayQuestionIndex;
        const nextQuestionNum = currentQuestionNum + 1;

        // 🔑 增强边界检查：已经是最后一题，显示提示并引导提交
        if (currentQuestionNum >= this.simplifiedQuestionList.length - 1) {
          uni.showToast({
            title: '已是最后一题',
            icon: 'none',
            duration: 1500
          });
          return;
        }

        // 🔑 增强边界检查：确保下一题索引在有效范围内
        if (nextQuestionNum < this.simplifiedQuestionList.length && this.simplifiedQuestionList[nextQuestionNum]) {
          const nextUid = this.simplifiedQuestionList[nextQuestionNum].uid;
          const nextIndex = this.swiperList.findIndex(item => item.uid === nextUid);

          if (nextIndex !== -1) {
            // 自动保存当前题的进度
            this.triggerAutoSave();
            
            // 直接设置索引，移除动画
            this.swiperCurrentIndex = nextIndex;
          }
        }
      } catch (error) {
        console.error('下一题切换错误:', error);
      }
    },
    
    // 🔑 新增：统一切换题目入口方法
    // 所有题目切换（滑动/点击/答题卡）均通过此方法
    async updateQuestionIndex(targetIndex, options = {}) {
      const {
        source = 'unknown', // 切换来源：'slide', 'button', 'answerCard'
        needSave = true,    // 是否需要保存当前进度
        showLoading = false // 是否显示加载状态
      } = options;

      try {
        // 边界检查
        if (targetIndex < 0 || targetIndex >= this.swiperList.length) {
          console.error('[updateQuestionIndex] 索引超出范围:', targetIndex);
          return false;
        }

        // 检查是否是边界题
        const isFirst = targetIndex === 0;
        const isLast = targetIndex === this.swiperList.length - 1;

        // 边界提示
        if (isFirst && source === 'button') {
          uni.showToast({ title: '已是第一题', icon: 'none', duration: 1000 });
          return false;
        }
        if (isLast && source === 'button') {
          uni.showToast({ title: '已是最后一题，请提交', icon: 'none', duration: 1500 });
          return false;
        }

        // 保存当前进度
        if (needSave) {
          this.triggerAutoSave();
        }

        // 检查目标题目是否已加载
        const targetQuestion = this.swiperList[targetIndex];
        if (targetQuestion && !targetQuestion.isPlaceholder) {
          // 题目已加载，直接跳转
          this.swiperCurrentIndex = targetIndex;
          this.showAnswerCard = false;
          return true;
        } else {
          // 题目未加载，需要异步加载
          if (showLoading) {
            uni.showLoading({ title: '加载题目中...' });
          }

          const targetQuestionUid = this.simplifiedQuestionList[targetIndex]?.uid;

          const success = await this.loadQuestionAndJump(targetIndex, targetQuestionUid);
          return success;
        }
      } catch (error) {
        console.error('[updateQuestionIndex] 切换题目错误:', error);
        return false;
      }
    },

    // 答题卡切换题目（调用统一切换方法）
    handleQuestionChange(index) {
      // 使用统一切换方法
      this.updateQuestionIndex(index, {
        source: 'answerCard',
        needSave: true,
        showLoading: true
      });
    },
    
    // 加载题目并跳转
    async loadQuestionAndJump(index, targetQuestionUid) {
      try {
        uni.showLoading({ title: '加载题目中...' });
        
        // 计算需要加载的块
        const chunkSize = this.preloadConfig.chunkSize || 10;
        const chunkIndex = Math.floor(index / chunkSize);
        
        // 加载题目
        await this.fetchQuestionList(chunkIndex, chunkSize);
        
        uni.hideLoading();
        
        // 加载完成后，直接使用传入的 index 作为目标索引
        // 因为 fetchQuestionList 会按照 simplifiedQuestionList 的顺序重建列表
        // 所以 index 就是目标题目在 swiperList 中的正确位置
        const targetQuestion = this.swiperList[index];
        
        
        if (targetQuestion && !targetQuestion.isPlaceholder) {
          this.swiperCurrentIndex = index;
          this.showAnswerCard = false;
        } else {
          console.error('[loadQuestionAndJump] 加载后仍未找到题目或仍是占位符:', targetQuestionUid);
          uni.showToast({ title: '题目加载失败', icon: 'none' });
        }
      } catch (err) {
        console.error('[loadQuestionAndJump] 加载题目失败:', err);
        uni.hideLoading();
        uni.showToast({ title: '题目加载失败', icon: 'none' });
      }
    },
    
    // 🔑 新增：滑动到左边界时加载上一块题目
    async loadPreviousChunkIfNeeded(targetQuestionNum = null) {
      try {
        // 🔑 防止重复加载
        if (this.isLoading) {
          return;
        }
        
        // 使用传入的目标题号，如果没有则使用当前显示的题号
        const currentIndex = targetQuestionNum !== null ? targetQuestionNum : this.displayQuestionIndex;
        const chunkSize = this.preloadConfig.chunkSize || 10;
        const currentChunkIndex = Math.floor(currentIndex / chunkSize);
        
        // 如果当前是第一块，不需要加载
        if (currentChunkIndex <= 0) {
          return;
        }
        
        // 计算上一块的索引
        const previousChunkIndex = currentChunkIndex - 1;
        
        // 检查上一块的所有题目是否都已经在 swiperList 中
        const chunkStartNum = previousChunkIndex * chunkSize;
        const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
        let loadedCount = 0;
        for (let i = chunkStartNum; i < chunkEndNum; i++) {
          const uid = this.simplifiedQuestionList[i]?.uid;
          if (uid && this.swiperList.some(item => item.uid === uid)) {
            loadedCount++;
          }
        }
        if (loadedCount === chunkEndNum - chunkStartNum) {
          return;
        }
                
        // 显示加载提示
        uni.showLoading({ title: '加载题目中...' });
        
        // 加载上一块题目
        await this.fetchQuestionList(previousChunkIndex, chunkSize);
        
        uni.hideLoading();
        
        
        // 🔑 修复：不再强制跳转到上一块的最后一个题
        // 让 handleSwiperChange 根据滑动方向正确计算目标题号
        
      } catch (error) {
        console.error('[loadPreviousChunkIfNeeded] 加载失败:', error);
        uni.hideLoading();
        uni.showToast({ title: '加载失败', icon: 'none' });
      }
    },
    
    // 🔑 新增：滑动到右边界时加载下一块题目
    async loadNextChunkIfNeeded(targetQuestionNum = null) {
      try {
        // 🔑 防止重复加载
        if (this.isLoading) {
          return;
        }
        
        // 使用传入的目标题号，如果没有则使用当前显示的题号
        const currentIndex = targetQuestionNum !== null ? targetQuestionNum : this.displayQuestionIndex;
        const chunkSize = this.preloadConfig.chunkSize || 10;
        const currentChunkIndex = Math.floor(currentIndex / chunkSize);
        
        // 计算总块数
        const totalChunks = Math.ceil(this.totalQuestionCount / chunkSize);
        
        // 如果当前是最后一块，不需要加载
        if (currentChunkIndex >= totalChunks - 1) {
          return;
        }
        
        // 计算下一块的索引
        const nextChunkIndex = currentChunkIndex + 1;
        
        // 检查下一块的所有题目是否都已经在 swiperList 中
        const chunkStartNum = nextChunkIndex * chunkSize;
        const chunkEndNum = Math.min(chunkStartNum + chunkSize, this.simplifiedQuestionList.length);
        let loadedCount = 0;
        for (let i = chunkStartNum; i < chunkEndNum; i++) {
          const uid = this.simplifiedQuestionList[i]?.uid;
          if (uid && this.swiperList.some(item => item.uid === uid)) {
            loadedCount++;
          }
        }
        if (loadedCount === chunkEndNum - chunkStartNum) {
          return;
        }
                
        // 显示加载提示
        uni.showLoading({ title: '加载题目中...' });
        
        // 加载下一块题目
        await this.fetchQuestionList(nextChunkIndex, chunkSize);
        
        uni.hideLoading();
        
        
      } catch (error) {
        console.error('[loadNextChunkIfNeeded] 加载失败:', error);
        uni.hideLoading();
        uni.showToast({ title: '加载失败', icon: 'none' });
      }
    },
    
    // 保存进度
    async handleSaveProgress() {
      try {
        const userInfo = await getUserInfo();
        const userId = userInfo?.id || userInfo?.uid || '';
        const questionsType = this.questionParams.questions_type || this.questionParams.questionsType;
        
        // 构造进度数据
        const progressData = {
          questionsType: questionsType,
          chapterUid: this.questionParams.chapter_uid || this.questionParams.chapterUid || '',
          userId: userId,
          questionList: this.swiperList,
          currentQuestionIndex: this.swiperCurrentIndex,
          totalTime: this.seconds,
          startTime: Date.now() - (this.seconds * 1000),
          examSettings: {
            mode: this.currentMode,
            ...this.questionParams
          }
        }
            
        // 同步保存（手动触发时应立即保存）
        const result = saveExamProgress(progressData, true);
            
        if (result) {
          uni.showToast({
            title: '进度已保存',
            icon: 'success',
            duration: 1500
          })
        } else {
          throw new Error('Save failed');
        }
      } catch (error) {
          console.error('点赞操作失败:', error);
          uni.showToast({
          title: '保存失败，请重试',
          icon: 'none',
          duration: 2000
        })
      }
    },
    
    // 自动保存进度（静默保存，不显示提示）
    async autoSaveProgress() {
      // 统一调用 triggerAutoSave 即可，内部已实现防抖和性能优化
      await this.triggerAutoSave();
    },
    
    // 清除答题进度缓存
    async clearExamProgress() {
      try {
        // 修复：使用用户ID而不是题目UID
        const userInfo = await getUserInfo();
        const userId = userInfo?.id || userInfo?.uid || '';
        
        // 使用统一的缓存管理函数
        clearExamProgress(
          this.questionParams.questions_type || this.questionParams.questionsType || 0,
          this.questionParams.chapter_uid || this.questionParams.chapterUid || '',
          userId
        );
      } catch (error) {
        console.error('清除答题进度缓存失败:', error);
      }
    },
    
    // 触发自动保存（带防抖）
    async triggerAutoSave() {
      try {
        // 验证必要数据
        
        if (!this.questionParams || Object.keys(this.questionParams).length === 0) {

          return;
        }
        
        if (!this.questionParams.questions_type && !this.questionParams.questionsType) {
          return;
        }
        
        // 修复：兼容两种字段名
        const questionsType = this.questionParams.questions_type || this.questionParams.questionsType;
        
        if (!this.swiperList || this.swiperList.length === 0) {

          return;
        }
        
        // 修复：使用用户ID而不是题目UID
        const userInfo = await getUserInfo();
        const userId = userInfo?.id || userInfo?.uid || '';
        
        // 验证索引有效性
        const listLength = this.swiperList.length;
        const currentIndex = this.swiperCurrentIndex;
        if (currentIndex < 0 || currentIndex >= listLength) {
          console.warn('[进度保存] 索引越界', { currentIndex, listLength });
          return;
        }
        
        // 构建进度数据
        // 性能优化：不再这里进行深度克隆，而是传递引用，由 manager 在防抖结束后异步处理
        const progressData = {
          questionsType: questionsType,
          chapterUid: this.questionParams.chapter_uid || this.questionParams.chapterUid || '',
          userId: userId,
          questionList: this.swiperList, // 传递引用，提升 UI 响应速度
          currentQuestionIndex: this.swiperCurrentIndex,
          answerHistory: [], 
          startTime: Date.now() - (this.seconds * 1000),
          totalTime: this.seconds,
          examSettings: {
            mode: this.currentMode,
            ...this.questionParams
          },
          submitInfo: {}
        };
        
        // 调用examProgressManager的自动保存方法（带防抖）
        await autoSaveExamProgress(progressData);
      } catch (error) {
        console.error('自动保存进度失败:', error);
      }
    },
    
    // 点赞功能
    async handleLike(question) {
      // 🔑 参数校验：防止 question 为 undefined 或缺少 uid
      if (!question || !question.uid) {
        console.error('[handleLike] 参数错误:', {
          question,
          swiperCurrentIndex: this.swiperCurrentIndex,
          virtualSwiperList: this.virtualSwiperList,
          swiperList: this.swiperList
        });
        uni.showToast({
          title: '题目信息加载失败，请刷新',
          icon: 'none',
          duration: 2000
        });
        return;
      }
      
      // 防止重复点击
      if (this.isLiking[question.uid]) {
        return
      }
      
      // 防抖处理
      if (this.likeDebounceTimer) {
        clearTimeout(this.likeDebounceTimer)
      }
      
      this.likeDebounceTimer = setTimeout(async () => {
        try {
          // 设置加载状态
          this.$set(this.isLiking, question.uid, true)
          
          // 调用API点赞接口
          const res = await this.$api.apiAddQuestionLike({
            library_uid: question.library_uid,
            question_uid: question.uid,
            action: question.is_like ? 2 : 1 // 1点赞，2取消点赞
          })
          
          if (res && res.code === 1) {
            // 更新本地状态（红色/灰色切换）
            const newLikeStatus = !question.is_like
            this.$set(question, 'is_like', newLikeStatus)
            this.$set(question, 'like_count', newLikeStatus ? (question.like_count + 1) : Math.max(0, question.like_count - 1))
            
            // 显示成功提示
            uni.showToast({
              title: newLikeStatus ? '点赞成功' : '已取消点赞',
              icon: 'success',
              duration: 1500
            })
          } else {
            throw new Error(res.msg || '操作失败')
          }
        } catch (error) {
          console.error('点赞操作失败:', error);
          uni.showToast({
            title: error.message || '点赞失败，请重试',
            icon: 'none',
            duration: 2000
          })
        } finally {
          // 清除加载状态
          this.$set(this.isLiking, question.uid, false)
        }
      }, 300) // 300ms防抖
    },
    
    // 收藏功能（实心星+黄色/空心星+灰色切换）
    async handleCollect(question) {
      // 🔑 参数校验：防止 question 为 undefined 或缺少必要属性
      if (!question || !question.uid) {
        console.error('[handleCollect] 参数错误:', {
          question,
          swiperCurrentIndex: this.swiperCurrentIndex,
          virtualSwiperList: this.virtualSwiperList,
          swiperList: this.swiperList
        });
        uni.showToast({
          title: '题目信息加载失败，请刷新',
          icon: 'none',
          duration: 2000
        });
        return;
      }
      
      try {
        // 调用API收藏接口
        const res = await this.$api.apiQuestionCollection({
            library_uid: question.library_uid,
            question_uid: question.uid,
            exam_type: question.exam_type || '',
            chapter_uid: question.chapter.uid ||'',
            exam_type_name: question.exam_type_name || '',
            title: question.title || '',
            exam_level: question.exam_level || '',
            score: question.score || '',
            correct_answer: question.answer || '',
            action: question.is_collection ? 2 : 1 // 1收藏，2取消收藏
        })
        
        if (res && res.code === 1) {
          // 更新本地状态（实心星黄色/空心星灰色切换）
          this.$set(question, 'is_collection', !question.is_collection)
          this.$set(question, 'collect_count', question.is_collection ? (question.collect_count + 1) : Math.max(0, question.collect_count - 1))
          
          // 本地缓存收藏状态
          const cachedCollections = uni.getStorageSync('questionCollections') || {}
          if (question.is_collection) {
            cachedCollections[question.uid] = true
          } else {
            delete cachedCollections[question.uid]
          }
          uni.setStorageSync('questionCollections', cachedCollections)
          
          // 显示成功提示
          uni.showToast({
            title: question.is_collection ? '收藏成功' : '已取消收藏',
            icon: 'success',
            duration: 1500
          })
        } else {
          throw new Error(res.msg || '操作失败')
        }
      } catch (error) {
        console.error('收藏操作失败:', error);
        uni.showToast({
          title: error.message || '收藏失败，请重试',
          icon: 'none',
          duration: 2000
        })
      }
    },
    
    // 分享功能（兼容微信小程序分享能力）
    handleShare(question) {
      // 🔑 参数校验
      if (!question) {
        console.error('[handleShare] 参数错误:', question);
        uni.showToast({
          title: '题目信息加载失败',
          icon: 'none',
          duration: 2000
        });
        return;
      }
      
      try {
        // 防抖处理，避免重复点击
        if (this.shareDebounceTimer) {
          clearTimeout(this.shareDebounceTimer)
        }
        
        this.shareDebounceTimer = setTimeout(() => {
          // 保存当前题目信息，供onShareAppMessage使用
          this.currentShareQuestion = question;
          
          // #ifdef MP-WEIXIN
          // 微信小程序环境：显示分享菜单
          uni.showShareMenu({
            withShareTicket: true,
            menus: ['shareAppMessage', 'shareTimeline'],
            success: () => {
              // 显示分享引导提示
              uni.showToast({
                title: '请点击右上角分享',
                icon: 'none',
                duration: 2000
              })
            },
            fail: (err) => {

              uni.showToast({
                title: '分享功能启动失败',
                icon: 'none',
                duration: 2000
              })
            }
          })
          // #endif
          
          // #ifndef MP-WEIXIN
          // 非微信环境：提示用户在微信中打开
          uni.showModal({
            title: '提示',
            content: '请在微信中打开小程序后使用分享功能',
            showCancel: false,
            confirmText: '我知道了'
          })
          // #endif
        }, 300) // 300ms防抖
      } catch (error) {
        console.error('提交答题失败:', error);
        uni.showToast({
          title: '分享失败，请重试',
          icon: 'none',
          duration: 2000
        })
      }
    },
    
    // 看答案（强制显示）
    handleShowAnswer() {
      if (this.currentMode === 'normal') {
        uni.showToast({
          title: '答题模式下无法查看答案',
          icon: 'none',
          duration: 2000
        })
        return
      }
      
      // 获取当前题目索引，直接从swiperList获取题目对象
      const questionIndex = this.swiperCurrentIndex
      const currentQuestion = this.swiperList[questionIndex]
      
      if (!currentQuestion) {
        uni.showToast({
          title: '题目信息加载失败',
          icon: 'none',
          duration: 2000
        })
        return
      }
      
      // 设置为已提交状态（使用$set确保响应式）
      this.$set(currentQuestion, 'forceShowAnswer', true)
      this.$set(currentQuestion, 'is_submitted', true)
      this.$set(currentQuestion, 'is_correct', true)
      
      // 标记正确答案
      this.markCorrectAnswers(currentQuestion)
      
      // 根据题型设置用户答案为正确答案
      if (currentQuestion.exam_type === 1 || currentQuestion.exam_type === 2 || currentQuestion.exam_type === 3) {
        // 单选、多选、判断题：设置选项状态
        if (currentQuestion.option && Array.isArray(currentQuestion.option)) {
          currentQuestion.option.forEach((opt, optIndex) => {
            if (currentQuestion.answer.includes(opt.check)) {
              // 正确答案：使用$set标记为已选中且正确
              this.$set(currentQuestion.option[optIndex], 'is_selected', true)
              this.$set(currentQuestion.option[optIndex], 'is_user_correct', true)
              // 移除status字段，只使用is_selected和is_user_correct
              // this.$set(currentQuestion.option[optIndex], 'status', 'correct')
            }
          })
        }
        // user_answer已经在markCorrectAnswers中正确设置，不需要重复设置
      } else if (currentQuestion.exam_type === 4 || currentQuestion.exam_type === 5) {
        // 填空题、问答题：确保user_answer是正确格式
        // user_answer已经在markCorrectAnswers中设置，这里只需要确保答案是数组格式
        if (!Array.isArray(currentQuestion.user_answer)) {
          this.$set(currentQuestion, 'user_answer', [currentQuestion.user_answer])
        }
      } else if (currentQuestion.exam_type === 6) {
        // 案例题：标记所有子题的正确答案
        if (currentQuestion.option && Array.isArray(currentQuestion.option)) {
          currentQuestion.option.forEach((subQuestion, subIndex) => {
            if (subQuestion.option && Array.isArray(subQuestion.option)) {
              subQuestion.option.forEach((opt, optIndex) => {
                if (subQuestion.answer && subQuestion.answer.includes(opt.check)) {
                  this.$set(currentQuestion.option[subIndex].option[optIndex], 'is_selected', true)
                  this.$set(currentQuestion.option[subIndex].option[optIndex], 'is_user_correct', true)
                  // 移除status字段，只使用is_selected和is_user_correct
                  // this.$set(currentQuestion.option[subIndex].option[optIndex], 'status', 'correct')
                }
              })
            }
            // 设置子题用户答案
            const subAnswer = Array.isArray(subQuestion.answer) ? subQuestion.answer[0] : subQuestion.answer
            this.$set(currentQuestion.option[subIndex], 'selectedAnswer', subAnswer)
            this.$set(currentQuestion.option[subIndex], 'selectedAnswers', Array.isArray(subQuestion.answer) ? subQuestion.answer : [subQuestion.answer])
          })
        }
      }
      
      // 使用响应式更新替代强制更新
      // reactiveUpdater.smartUpdate(this, 'question', this.currentQuestion);
    },
    
    // 纠错
    handleCorrection(question) {
      // 🔑 参数校验
      if (!question || !question.uid) {
        console.error('[handleCorrection] 参数错误:', {
          question,
          swiperCurrentIndex: this.swiperCurrentIndex,
          virtualSwiperList: this.virtualSwiperList
        });
        uni.showToast({
          title: '题目信息加载失败，请刷新',
          icon: 'none',
          duration: 2000
        });
        return;
      }
      
      this.$func.navigatorTo(`/subpages/examOther/correctionForm?question_uid=${question.uid}`);
    },
    
    // 模式切换
    handleModeSwitch() {
      this.showModeSwitch = true
    },
    
    // 切换模式
    switchMode(mode) {
      if (mode === this.currentMode) {
        this.showModeSwitch = false
        return
      }
      
      uni.showModal({
        title: '提示',
        content: '切换模式将重新加载题目，是否继续？',
        success: (res) => {
          if (res.confirm) {
            examStore.switchMode(mode)
            examStore.setState({ showModeSwitch: false })
            this.fetchQuestionList()
          }
        }
      })
    },
    
    // 返回上一页
    handleGoBack() {
      uni.navigateBack()
    },
    
    // 简单的手势处理 - touchstart
    handleSimpleTouchStart(e) {
      this._touchStartX = e.touches[0].clientX;
      this._touchStartY = e.touches[0].clientY;
      this._touchStartTime = Date.now();
    },
    
    // 简单的手势处理 - touchmove
    handleSimpleTouchMove(e) {
      this._touchEndX = e.touches[0].clientX;
      this._touchEndY = e.touches[0].clientY;
    },
    
    // 简单的手势处理 - touchend
    async handleSimpleTouchEnd(e) {
      if (!this._touchStartX || !this._touchEndX || !this._touchStartY || !this._touchEndY) return;
      
      const diffX = this._touchEndX - this._touchStartX;
      const diffY = this._touchEndY - this._touchStartY;
      const diffTime = Date.now() - this._touchStartTime;
      
      // 重置触摸状态
      this._touchStartX = null;
      this._touchEndX = null;
      this._touchStartY = null;
      this._touchEndY = null;
      this._touchStartTime = null;
      
      // 方向校验：确保是水平滑动（水平滑动距离大于垂直滑动距离的2倍）
      if (Math.abs(diffX) < Math.abs(diffY) * 2) return;
      
      // 检测是否是有效的水平滑动
      if (Math.abs(diffX) > this.animationConfig.threshold && diffTime < 300) {
        // 滑动热区限制：只允许在屏幕宽度的中间80%区域触发滑动
        const screenWidth = uni.getSystemInfoSync().screenWidth;
        const touchStartX = e.changedTouches[0].clientX;
        const hotZoneLeft = screenWidth * 0.1;
        const hotZoneRight = screenWidth * 0.9;
        
        if (touchStartX >= hotZoneLeft && touchStartX <= hotZoneRight) {
          if (diffX > 0) {
            // 向右滑动，上一题
            await this.handlePrevQuestion(true);
          } else {
            // 向左滑动，下一题
            await this.handleNextQuestion(true);
          }
        }
      }
    },
    
    // 提交答题
    async handleSubmitExam() {
      try {
        // 计算已答题数量和未答题数量
        const totalQuestions = this.swiperList.length;
        const answeredQuestions = this.swiperList.filter(question => {
          // 检查案例题（exam_type === 6）的作答状态
          if (question.exam_type === 6) {
            // 案例题：检查是否有子题且所有子题都已作答
            if (question.option && Array.isArray(question.option)) {
              // 检查所有子题是否都已作答
              const allSubQuestionsAnswered = question.option.every(subItem => {
                if (!subItem) return false;
                
                // 检查子题是否有选中的选项或用户答案
                const hasSelectedOption = (subItem.selectedAnswer || (subItem.selectedAnswers && subItem.selectedAnswers.length > 0));
                const hasUserAnswer = subItem.user_answer && subItem.user_answer.trim().length > 0;
                return hasSelectedOption || hasUserAnswer;
              });
              return allSubQuestionsAnswered;
            }
            return false;
          }
          
          // 检查其他题型的作答状态
          const hasSelectedOption = question.option && Array.isArray(question.option) && 
                                   question.option.some(opt => opt.is_selected);
          const hasUserAnswer = question.user_answer && question.user_answer.length > 0;
          return hasSelectedOption || hasUserAnswer;
        }).length;
        const unansweredQuestions = totalQuestions - answeredQuestions;
        
        // 1. 不管是否有未答题，都允许用户提交
        // 弹出确认提交对话框
        this.confirmModalContent = unansweredQuestions > 0 
          ? `还有${unansweredQuestions}题未作答，确定要提交答题吗？提交后将无法修改答案`
          : '确定要提交答题吗？提交后将无法修改答案';
        this.showConfirmModal = true;
        
        try {
          // 等待用户确认
          await new Promise((resolve, reject) => {
            this.confirmModalResolve = resolve;
            this.confirmModalReject = reject;
          });
        } catch (error) {
          this.showConfirmModal = false;
          return;
        }
        
        // 用户确认后，执行提交逻辑
        await this.performSubmit();
      } catch (error) {
        console.error('提交答题失败:', error);
        uni.showToast({
          title: error.message || '提交过程中出现错误，请检查网络后重试',
          icon: 'none',
          duration: 3000
        });
      }
    },
    
    // 执行实际的提交逻辑
    async performSubmit() {
      try {
        // 处理所有题目，包括案例题和其他题型
        const questionListWithUserAnswer = this.swiperList
          // 为每个题目添加user_answer字段
          .map(question => {
            let user_answer = [];
            let is_correct = false; // 重新计算is_correct
            
            // 特殊处理案例题（exam_type === 6）
            if (question.exam_type === 6) {
              // 案例题：收集所有子题的答案
              if (question.option && Array.isArray(question.option)) {
                user_answer = question.option.map(subItem => {
                  let answer = [];
                  
                  if (subItem.selectedAnswer) {
                    // 单选子题
                    answer = [subItem.selectedAnswer];
                  } else if (subItem.selectedAnswers && subItem.selectedAnswers.length > 0) {
                    // 多选子题
                    answer = subItem.selectedAnswers;
                  } else if (subItem.user_answer && subItem.user_answer.trim().length > 0) {
                    // 填空或问答子题
                    answer = [subItem.user_answer];
                  }
                  
                  // 确保子题的answer不为空
                  if (Array.isArray(answer) && answer.length === 0) {
                    answer = [''];
                  }
                  
                  return { sub_question_uid: subItem.uid, answer: answer };
                });
              }
              
              // 确保案例题的user_answer不为空
              if (!Array.isArray(user_answer) || user_answer.length === 0) {
                user_answer = [{ sub_question_uid: '', answer: [''] }];
              }
              // 案例题is_correct由后端判断，前端不处理
              is_correct = false;
            } 
            // 处理问答题（exam_type === 5）
            else if (question.exam_type === 5) {
              // 确保user_answer为数组格式，且不为空
              if (question.user_answer && question.user_answer.trim().length > 0) {
                user_answer = [question.user_answer.trim()];
              } else {
                user_answer = [''];
              }
              // 问答题is_correct由后端判断，前端不处理
              is_correct = false;
            }
            // 处理填空题（exam_type === 4）
            else if (question.exam_type === 4) {
              // 确保user_answer为数组格式，且不为空
              if (question.user_answer && question.user_answer.trim().length > 0) {
                user_answer = [question.user_answer.trim()];
              } else {
                user_answer = [''];
              }
              // 填空题is_correct由后端判断，前端不处理
              is_correct = false;
            }
            // 处理其他题型（单选、多选、判断题）
            else {
              // 优先使用已有的user_answer字段（背题模式和看答案按钮会设置）
              if (question.user_answer) {
                user_answer = question.user_answer;
              }
              // 否则从选项中收集用户选择的答案，同时兼容option和options字段
              else {
                // 兼容不同的数据结构：option和options字段
                const questionOptions = question.option || [];
                if (Array.isArray(questionOptions)) {
                  // 使用filter和map更高效地收集选中的答案
                  // 同时检查is_selected属性，确保正确收集选中的选项
                  user_answer = questionOptions
                    .filter(opt => opt && (opt.is_selected === true || opt.is_correct === true)) // 背题模式下的正确选项is_correct为true
                    .map(opt => opt.check); // 提取选中选项的check值
                } else {
                  user_answer = [];
                }
              }
              
              // 确保单选、多选、判断题的user_answer不为空且格式正确
              // 对于单选、多选、判断题，使用前端计算的is_correct值
              // 不强制设置为false，保留前端的判断结果，让用户能立即看到是否正确
              // 后端仍然会根据user_answer重新判断，前端的判断只是为了提供即时反馈
              is_correct = question.is_correct;
            }
            
            // 确保所有题型的user_answer格式正确，放在所有条件分支之后执行
            // 1. 处理嵌套数组问题：如果user_answer是数组且第一个元素也是数组，直接使用第一个元素
            if (Array.isArray(user_answer) && user_answer.length === 1 && Array.isArray(user_answer[0])) {
              user_answer = user_answer[0];
            }
            // 2. 确保是数组格式
            else if (!Array.isArray(user_answer)) {
              user_answer = [user_answer];
            }
            // 3. 处理字符串格式的JSON数组：如 "["D"]" -> ["D"]
            else if (user_answer.length === 1 && typeof user_answer[0] === 'string' && 
                     (user_answer[0].startsWith('[') || user_answer[0].startsWith('{'))) {
              try {
                const parsed = JSON.parse(user_answer[0]);
                if (Array.isArray(parsed)) {
                  user_answer = parsed;
                }
              } catch (e) {
                // 解析失败，保留原格式
              }
            }
            // 4. 处理字符串格式的答案列表：如 "C, D, E" -> ["C", "D", "E"]
            else if (user_answer.length === 1 && typeof user_answer[0] === 'string' && 
                     (user_answer[0].includes(',') || user_answer[0].includes('，') || user_answer[0].includes('、'))) {
              // 支持多种分隔符
              user_answer = user_answer[0].split(/[,，、]/)
                .map(ans => ans.trim())
                .filter(ans => ans !== '');
            }
            // 5. 移除数组中的undefined和null值
            user_answer = user_answer.filter(item => item !== undefined && item !== null);
            // 6. 如果是空数组，设置为包含空字符串的数组，确保不为空
            if (user_answer.length === 0) {
              user_answer = [''];
            }
            
            // 返回带有user_answer和is_correct的题目对象
            return {
              ...question,
              user_answer: user_answer,
              is_correct: is_correct // 重新计算的is_correct，简单题型设为false让后端判断
            };
          });
        
        // 准备提交数据
        // 确保所有user_answer都是JSON字符串格式，不是数组对象
        // 同时只保留必要字段，不提交is_correct，让后端根据user_answer判断
        
        const finalOptionList = questionListWithUserAnswer.map((question, index) => {
          try {
            // 修复：清理 option 字段，只保留后端需要的基本信息
            // 从缓存恢复的题目可能包含完整的原始数据，需要过滤
            let cleanedOption = [];
            // 兼容不同的数据结构：option和options字段
            const questionOptions = question.option || [];
            if (Array.isArray(questionOptions)) {
              cleanedOption = questionOptions.map(opt => {
                if (!opt) return {};
                // 只保留is_selected字段，移除status字段
                const isSelected = opt.status === 'selected' || opt.is_selected === true || opt.is_correct === true;
                return {
                  check: opt.check,
                  is_selected: isSelected,
                  // 案例题子题特殊字段
                  selectedAnswer: opt.selectedAnswer,
                  selectedAnswers: opt.selectedAnswers,
                  user_answer: opt.user_answer,
                  uid: opt.uid  // 案例题子题UID
                  // 移除status字段，只保留is_selected
                };
              });
            }
            
            // 只保留后端需要的字段
            const result = {
              uid: question.uid,
              exam_type: question.exam_type,
              user_answer: question.user_answer,
              options: cleanedOption  // 使用清理后的选项数据
            };
            
            // 数据验证
            if (!result.uid) {
              console.warn(`第${index + 1}题缺少uid，使用默认值`);
              result.uid = `default_${index}`;
            }
            if (!Array.isArray(result.user_answer)) {
              console.warn(`第${index + 1}题user_answer格式错误，转换为数组`);
              result.user_answer = [result.user_answer];
            }
            if (!Array.isArray(result.options)) {
              console.warn(`第${index + 1}题options格式错误，转换为数组`);
              result.options = [];
            }
            
            return result;
          } catch (e) {

            throw e;
          }
        });
        
        const optionJson = JSON.stringify(finalOptionList);        
        const submitData = {
          uid: this.questionParams.uid || '', // 使用questionParams.uid作为uid
          questions_type: this.questionParams.questions_type || 0, // 答题类型
          options: optionJson, // 答题选项
          submit_time: this.seconds, // 答题时间，使用计时器记录的秒数
          exam_time: this.examTime // 考试总时间，单位为分钟
          // 移除冗余字段：options_type（与questions_type重复）和questionParams（包含大量不必要信息）
        };
        
        // 参数验证
        if (!questionListWithUserAnswer || questionListWithUserAnswer.length === 0) {
          throw new Error('没有可提交的题目');
        }
        
        // 显示加载动画
        this.isSubmitting = true;
        
        // 添加调试日志        
        const result = await this.$api.apiSubmitExamination(submitData);

        // 响应数据验证
        if (!result || typeof result !== 'object') {
          // 打印完整响应内容用于调试
          this.isSubmitting = false;
          throw new Error('服务器响应格式错误，请检查网络或稍后重试');
        }
        
        if (result.code === 1) {
          // 隐藏加载状态
          this.isSubmitting = false;
          
          // 短暂延迟后更新状态并显示结果模态框
          setTimeout(async () => {
            this.swiperList.forEach(question => {
              question.is_submitted = true;
            });
            
            // 保存服务器返回的记录ID，用于后续跳转到分析页面
            if (result.data && (result.data.uid || result.data.record_id)) {
              // 保存到questionParams中，供handleResultConfirm方法使用
              this.questionParams.record_id = result.data.uid || result.data.record_id;
            }
            
            // 清除答题进度缓存
            await this.clearExamProgress();
            
            // 保存结果消息用于模态框显示
            // 将换行符替换为HTML换行标签，确保在mp-html组件中正确显示
            examStore.setState({ resultMessage: (result.data.msg || '提交成功').replace(/\n/g, '<br/>') });
            this.history_id = result.data.history_id || 0;
            // 使用v-model控制tn-modal组件显示
            this.showResultModal = true;
          
          // 触发父组件的提交成功事件，便于外部组件处理
          this.$emit('submit-success', result.data);
          }, 1000);
        } else {
          // 错误码处理
          this.isSubmitting = false;
          throw new Error(result.msg || '提交失败，请稍后重试');
        }
      } catch (error) {
        // 确保隐藏加载状态
        this.isSubmitting = false;
        throw error;
      } finally {
        // 确保加载状态被隐藏
        this.isSubmitting = false;
      }
    },
    
    // 返回首页
    handleGoHome() {
      uni.switchTab({ url: '/pages/index/index' })
    },
    
    // Toast提示
    showToast(message) {
      this.$func.showToast(message)
    },
    
    // 获取下一个题目（用于动画）
    getNextQuestion() {
      if (this.nextQuestionIndex < 0 || this.nextQuestionIndex >= this.swiperList.length) {
        return null
      }
      return this.swiperList[this.nextQuestionIndex]
    },
    
    // 获取下一个题目索引（用于动画）
    getNextQuestionIndex() {
      return this.nextQuestionIndex
    },
    
    // 触发滑动动画
    async triggerSlideAnimation(targetIndex, slideDirection) {
      // 检查是否正在动画中
      if (this.isAnimating) return;
      
      // 检查目标题目是否需要加载
      const targetQuestion = this.swiperList[targetIndex];
      if (targetQuestion && targetQuestion.isPlaceholder) {
        // 显示加载提示
        uni.showLoading({ title: '加载题目中...' });
        
        try {
          // 计算需要加载的块
          const chunkSize = this.preloadConfig.chunkSize || 10;
          const chunkIndex = Math.floor(targetIndex / chunkSize);
          
          // 加载题目
          await this.fetchQuestionList(chunkIndex, chunkSize);
        } catch (error) {
          console.error('加载题目失败:', error);
          uni.showToast({ title: '题目加载失败', icon: 'none' });
          return;
        } finally {
          uni.hideLoading();
        }
      }
      
      // 设置动画状态
      this.isAnimating = true;
      this.direction = slideDirection;
      this.nextQuestionIndex = targetIndex;
      
      // 直接设置索引，移除动画
      this.swiperCurrentIndex = targetIndex;
      
      // 重置动画状态
      this.isAnimating = false;
      this.direction = '';
      this.nextQuestionIndex = -1;
    },
    
    // 判断是否显示作答显示组件
    shouldShowAnswerDisplay(question) {
      // 如果 question 为 undefined，返回 false
      if (!question) {
        return false
      }
      
      // 问答题和案例题不显示
      if (question.exam_type === 5 || question.exam_type === 6) {
        return false
      }
      
      // 答题模式全程隐藏（点击“看答案”除外）
      if (this.currentMode === 'normal' && !question.forceShowAnswer) {
        return false
      }
      
      // 学练模式：提交后或点击“看答案”后显示
      if (this.currentMode === 'learnPractice') {
        return question.is_submitted || question.forceShowAnswer
      }
      
      // 背题模式：直接显示
      if (this.currentMode === 'reviewOnly') {
        return true
      }
      
      return false
    },
    
    // 判断是否显示试题解析
    shouldShowAnalysis(question) {
      // 如果 question 为 undefined，返回 false
      if (!question) {
        return false
      }
      
      // 答题模式全程隐藏（点击“看答案”除外）
      if (this.currentMode === 'normal' && !question.forceShowAnswer) {
        return false
      }
      
      // 学练模式：提交后或点击“看答案”后显示
      if (this.currentMode === 'learnPractice') {
        return question.is_submitted || question.forceShowAnswer
      }
      
      // 背题模式：直接显示
      if (this.currentMode === 'reviewOnly') {
        return true
      }
      
      return false
    },
    // 判断是否显示做题笔记
    shouldShowComment(question) {
      // 如果 question 为 undefined，返回 false
      if (!question) {
        return false
      }
      
      // 答题模式全程隐藏
      if (this.currentMode === 'normal' && !question.forceShowAnswer) {
        return false
      }
          
      // 学练模式：提交后或点击"看答案"后显示
      if (this.currentMode === 'learnPractice') {
        return question.is_submitted || question.forceShowAnswer
      }
          
      // 背题模式：直接显示
      if (this.currentMode === 'reviewOnly') {
        return true
      }
          
      return false
    },
    
    // 微信小程序分享功能 - 好友分享
    onShareAppMessage(res) {
      const question = this.currentShareQuestion || this.currentQuestion
      const shareInfo = {
        title: '我正在做这道题，快来试试！',
        path: '/pages/index/index',
        imageUrl: ''
      }
        
      // 如果有题目信息，添加到分享标题和封面
      if (question) {
        // 优化分享标题：显示题目类型和题库名称
        const typeName = this.getQuestionTypeName(question.exam_type)
        const questionTitle = this.stripHtmlTags(question.title).substring(0, 30)
        shareInfo.title = `${typeName}：${questionTitle}${questionTitle.length >= 30 ? '...' : ''}`
          
        // 设置分享封面图
        if (question.cover_image) {
          shareInfo.imageUrl = question.cover_image
        }
          
        // 构造分享路径：跳转到首页，带上题库UID和题目UID参数
        if (this.questionParams && this.questionParams.uid) {
          const params = [
            `library_uid=${this.questionParams.uid}`,
            `question_uid=${question.uid}`,
            `questions_type=${this.questionParams.questions_type || 0}`
          ]
          shareInfo.path = `/pages/index/index?${params.join('&')}`
        }
      }
        
      // 记录分享事件（可选）

        
      return shareInfo
    },
  
  // 微信小程序分享功能 - 朋友圈分享
    onShareTimeline(res) {
      const question = this.currentShareQuestion || this.currentQuestion
      const shareInfo = {
        title: '我正在做这道题，快来试试！',
        query: '',
        imageUrl: ''
      }
        
      // 如果有题目信息，添加到分享标题和封面
      if (question) {
        // 优化分享标题
        const typeName = this.getQuestionTypeName(question.exam_type)
        const questionTitle = this.stripHtmlTags(question.title).substring(0, 30)
        shareInfo.title = `${typeName}：${questionTitle}${questionTitle.length >= 30 ? '...' : ''}`
          
        // 设置分享封面图
        if (question.cover_image) {
          shareInfo.imageUrl = question.cover_image
        }
          
        // 构造分享查询参数
        if (this.questionParams && this.questionParams.uid) {
          const params = [
            `library_uid=${this.questionParams.uid}`,
            `question_uid=${question.uid}`,
            `questions_type=${this.questionParams.questions_type || 0}`
          ]
          shareInfo.query = params.join('&')
        }
      }
        
      // 记录分享事件（可选）

        
      return shareInfo
    },
  
  // 加载更多题目（分块加载）
    loadMoreQuestions() {
      const remainingQuestions = examStore.state.remainingQuestions || [];
      if (remainingQuestions.length > 0) {
        const loadChunkSize = 5; // 每次加载5个题目
        const newQuestions = remainingQuestions.slice(0, loadChunkSize);
        const newRemainingQuestions = remainingQuestions.slice(loadChunkSize);
        
        // 合并到现有题目列表
        const currentQuestions = examStore.state.swiperList || [];
        const updatedQuestions = currentQuestions.concat(newQuestions);
        
        examStore.setSwiperList(updatedQuestions);
        examStore.setState({ remainingQuestions: newRemainingQuestions });
                
        // 强制触发响应式更新
        this.$forceUpdate();
      }
    },
  
  // 检查是否需要加载更多题目
    checkLoadMoreQuestions() {
      const currentIndex = this.swiperCurrentIndex;
      const currentListLength = this.swiperList.length;
      const remainingQuestions = examStore.state.remainingQuestions || [];
      
      // 当接近当前列表末尾时，加载更多题目
      if (currentIndex >= currentListLength - 2 && remainingQuestions.length > 0) {
        this.loadMoreQuestions();
      }
    },
  }
}
</script>

<style lang="scss" scoped>
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

.nav-right {
  padding: 0 20rpx;
  font-size: 40rpx;
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

.question-area {
  height: calc(100vh - 180rpx);
}

.question-swiper {
  height: 100%;
}

.question-animation-container {
  position: relative;
  height: 100%;
  overflow: hidden;
}

.question-item {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.question-scroll-view {
  height: 100%;
  -webkit-overflow-scrolling: touch;
}

.question-content {
  padding-bottom: 120rpx;
}

/* 移除动画样式 */

/* 页面加载动画样式 */
.page-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ffffff;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99999;
  transition: opacity 0.4s ease-in-out;
}

.loading-content {
  text-align: center;
}

/* 内容加载完成动画 */
.question-area {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.4s ease-in-out, transform 0.6s ease-in-out;
}

.question-area.content-loaded {
  opacity: 1;
  transform: translateY(0);
}

.question-header {
  border-radius: 16rpx;
}

.action-buttons {
  padding-top: 20rpx;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  flex-wrap: nowrap;
  min-width: 0;
}

.action-buttons > view:nth-child(1) {
  display: flex;
  align-items: center;
  flex-wrap: nowrap;
  flex-shrink: 1;
  min-width: 0;
  margin-right: 10rpx;
}

.action-buttons > view:nth-child(2) {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.action-btn {
  display: flex;
  align-items: center;
  padding: 8rpx 16rpx;
  border-radius: 8rpx;
  transition: all 0.3s;
  flex-shrink: 1;
  min-width: 0;
  white-space: nowrap;
  
  // 修复：将标签选择器 text 改为通用选择器
  .tn-icon-like,
  .tn-icon-like-fill,
  .tn-icon-star,
  .tn-icon-star-fill,
  .tn-icon-share,
  .tn-icon-topics {
    font-size: 30rpx;
  }
  
  .action-text {
    font-size: 26rpx;
    margin-left: 6rpx;
    color: #666666;
  }
  
  /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
}

.action-buttons .tn-margin-left {
  margin-left: 8rpx;
}

.action-buttons .tn-margin-right {
  margin-right: 8rpx;
}

.component-wrapper {
  background-color: #FFFFFF;
  margin: 0 20rpx;
}

.bottom-toolbar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: #FFFFFF;
  box-shadow: 0 -4rpx 20rpx rgba(0, 0, 0, 0.05);
  z-index: 100;
}

.toolbar-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10rpx 20rpx;
  
  .toolbar-text {
    font-size: 24rpx;
    color: #666666;
    margin-top: 4rpx;
  }
}

/* 禁用状态样式 */
.toolbar-btn.disabled {
  opacity: 0.4;
  pointer-events: none;
}

.question-progress {
  margin: 18rpx 10rpx;
  font-size: 28rpx;
}

.mode-switch-popup {
  padding: 20rpx 0;
}

.popup-title {
  text-align: center;
  border-bottom: 1rpx solid #F0F0F0;
  padding-bottom: 20rpx;
}

.mode-list {
  padding-top: 20rpx;
}

.mode-item {
  display: flex;
  align-items: center;
  padding: 30rpx 20rpx;
  background-color: #F8F9FA;
  border-radius: 16rpx;
  transition: all 0.3s;
  
  &.active {
    background-color: #E6F7FF;
    border: 2rpx solid #0E7DFF;
  }
  
  .mode-icon {
    width: 80rpx;
    height: 80rpx;
    background-color: #FFFFFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20rpx;
  }
  
  .mode-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    
    .mode-name {
      font-size: 32rpx;
      margin-bottom: 8rpx;
    }
    
    .mode-desc {
      line-height: 1.5;
    }
  }
  
  .mode-check {
    font-size: 48rpx;
  }
}

// 点赞和收藏用户列表样式
.users-info-container {
  width: 100%;
  background-color: #f8f8f8;
  padding: 15rpx 20rpx;
  border-radius: 8rpx;
  
  .users-row {
    line-height: 1.6;
  }
}

// 结果模态框自定义内容样式
.result-content {
  padding: 40rpx 0;
  font-size: 28rpx;
  line-height: 1.5;
  text-align: left;
  white-space: pre-line;
}

// 结果模态框按钮样式
.result-buttons {
  display: flex;
  justify-content: space-around;
  padding: 20rpx;
  margin-top: 20rpx;
}

.result-buttons .tn-button {
  flex: 0 0 45%;
}

// 响应式适配
@media screen and (max-width: 375px) {
  .action-buttons {
    // 保持水平排列，不使用column
    
    .action-btn {
      padding: 6rpx 12rpx; // 减小按钮内边距
      
      // 修复：将标签选择器 text 改为通用选择器
      .tn-icon-like,
      .tn-icon-like-fill,
      .tn-icon-star,
      .tn-icon-star-fill,
      .tn-icon-share,
      .tn-icon-topics {
        font-size: 28rpx; // 减小图标大小
      }
      
      .action-text {
        font-size: 24rpx; // 减小文字大小
        margin-left: 4rpx; // 减小文字与图标间距
      }
    }
  }
}

/* 提交加载动画样式 */
.submit-loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.submit-loading-content {
  background-color: white;
  padding: 40rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

/* 题目标题区域样式 */
.question-title-area {
    padding: 20rpx;
  .question-header {
    
    .question-number {
      font-weight: bold;
      font-size: 32rpx;
      margin-right: 10rpx;
    }
    
    .question-type-tag {
      font-weight: bold;
      font-size: 28rpx;
      margin-right: 10rpx;
    }
    
    .question-score {
      color: #FF6B6B;
      font-size: 26rpx;
    }
  }
  
  .question-text {
    line-height: 1.6;
    font-size: 28rpx;
  }
}

.blogger {
    &__desc {
      line-height: 45rpx;
      
      &__label {
        padding: 0 20rpx;
        &--prefix {
          padding-right: 10rpx;
        }
      }
    }
  }
</style>
