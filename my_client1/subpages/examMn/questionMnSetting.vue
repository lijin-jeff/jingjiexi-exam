<template>
  <view class="container">
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
			模考设置
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <!-- 主体内容 -->
    <scroll-view
      scroll-y
      class="main-content"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <!-- 题型设置 -->
      <view class="section question-type-section">
        <view class="header-row">
          <text class="header-cell type-header">
            题型 (题数)
          </text>
          <text class="header-cell count-header">
            题数
          </text>
          <text class="header-cell score-header">
            分
          </text>
        </view>
        <view
          v-for="(item, index) in questionTypes"
          :key="index"
          class="data-row"
        >
          <text class="data-cell type-cell">
            {{ item.name }} ({{ item.exam_count }})
          </text>
          <view class="data-cell count-cell">
            <view class="input-stepper">
              <view 
                class="stepper-btn minus-btn" hover-class="stepper-btn-hover"
                :class="{ 'disabled': item.count <= 0 }"
                @click="decreaseCount(item)"
              >
                <text class="tn-icon-reduce" />
              </view>
              <input 
                v-model="item.count" 
                type="number" 
                class="input-field" 
                :min="0"
                :max="item.exam_count || 50"
                @input="calculateScore"
              >
              <view 
                class="stepper-btn plus-btn" hover-class="stepper-btn-hover"
                :class="{ 'disabled': item.count >= (item.exam_count || 50) || (item.exam_count === 0) }"
                @click="increaseCount(item)"
              >
                <text class="tn-icon-add" />
              </view>
            </view>
          </view>
          <view class="data-cell score-cell">
            <view class="input-stepper">
              <view 
                class="stepper-btn minus-btn"
                :class="{ 'disabled': item.score <= 1 }"
                @click="decreaseScore(item)"
              >
                <text class="tn-icon-reduce" />
              </view>
              <input 
                v-model="item.score" 
                type="number" 
                class="input-field" 
                :min="1"
                :max="100"
                :step="1"
                @input="calculateScore"
              >
              <view 
                class="stepper-btn plus-btn"
                :class="{ 'disabled': item.score >= 100 }"
                @click="increaseScore(item)"
              >
                <text class="tn-icon-add" />
              </view>
            </view>
          </view>
        </view>
        <view class="summary-row">
          <text>
            总题数 <text class="highlight">
              {{ totalCount }}
            </text> 题
          </text>
          <text>
            总分 <text class="highlight">
              {{ totalScore.toFixed(2) }}
            </text> 分
          </text>
        </view>
      </view>

      <!-- 考试设置 -->
      <view class="section settings-section">
        <view class="setting-item arrow">
          <text class="label">
            考试时长
          </text>
          <view
            class="value-section"
            @click="showDuration"
          >
            <text class="value">
              {{ examDuration }} 分钟
            </text>
            <text class="arrow-icon tn-icon-right" />
          </view>
        </view>
        <view class="setting-item">
          <text class="label">
            及格分
          </text>
          <view class="value-section">
            <input 
              v-model="passScore" 
              type="number" 
              class="input-field score-input"
              :min="0"
              :max="totalScore"
              :step="0.1"
            >
            <text class="unit">
              分
            </text>
          </view>
        </view>
        <view class="setting-item">
          <view class="label-group">
            <text class="label">
              答题后显示答案
            </text>
            <text class="description">
              学练结合模式
            </text> 
          </view>
          <switch
            :checked="showAnswerAfter"
            :color="mainColor"
            style="transform:scale(0.8)"
            @change="onSwitchChange('showAnswerAfter', $event)"
          />
        </view>
        <view class="setting-item arrow">
          <text class="label">
            多选题得分模式
          </text>
          <view
            class="value-section"
            @click="showMoreCount"
          >
            <text class="value">
              {{ moreSwitchRange[examMoreSwitch] }}
            </text>
            <text class="arrow-icon tn-icon-right" />
          </view>
        </view>
        <view class="setting-item">
          <view class="label-group">
            <text class="label">
              选项乱序
            </text>
            <text class="vip-tag">
              VIP
            </text>
            <text class="description">
              选项随机排列，避免背答案
            </text> 
          </view>
          <view @click.stop="handleSwitchClick('shuffleOptions')">
            <switch
              :checked="shuffleOptions"
              :loading="switchLoading"
              :color="mainColor"
              style="transform:scale(0.8)"
              disabled
            />
          </view>
        </view>
        <view class="setting-item">
          <view class="label-group">
            <text class="label">
              错题优先
            </text>
            <text class="vip-tag">
              VIP
            </text>
            <text class="description">
              优先出错误过的题目
            </text> 
          </view>
          <view @click.stop="handleSwitchClick('priorityWrong')">
            <switch
              :checked="priorityWrong"
              :loading="switchLoading"
              :color="mainColor"
              style="transform:scale(0.8)"
              disabled
            />
          </view>
        </view>
        <view class="setting-item">
          <view class="label-group">
            <text class="label">
              未做题优先
            </text>
            <text class="vip-tag">
              VIP
            </text>
            <text class="description">
              优先出未作答的题目
            </text> 
          </view>
          <view @click.stop="handleSwitchClick('priorityUnattempted')">
            <switch
              :checked="priorityUnattempted"
              :loading="switchLoading"
              :color="mainColor"
              style="transform:scale(0.8)"
              disabled
            />
          </view>
        </view> 
      </view>

      <!-- 规则说明 -->
      <view class="rule-description">
        <view>出题规则说明</view>
        <view>1. 试题题数说明：相同类型的试题，题数不能超过该类型下的总题数</view>
        <view>2. 开启选项乱序：开启选项乱序，返回的试题，顺序将被随机打乱</view>
        <view>3. 错题优先：开启后，会优先出现曾经答错的题目</view>
        <view>4. 未做题优先：开启后，会优先出现还未作答的题目</view>
      </view>

      <view class="tn-padding-xl" />
    </scroll-view>

    <!-- 底部操作栏 -->
    <view class="bottom-bar">
      <view
        class="history-link"
        @click="switchHistory"
      >
        <text class="history-icon tn-icon-time" />
        <text>做题记录</text>
      </view>
      <button
        class="start-button"
        type="primary"
        @click="submitConfig"
      >
        开始考试
      </button>
    </view>
	
    <!-- 考试时长设置 -->
    <tn-picker
      v-model="showDurationPicker"
      mode="selector"
      :default-selector="[0]"
      :range="numberRange"
      confirm-text="确认时长"
      @confirm="randCountConfirm"
    />
    <!-- 考试时长结束 -->
	
	
    <!-- 多选题积分模式设置 -->
    <tn-picker
      v-model="showMoreSwitchPicker"
      mode="selector"
      :default-selector="[0]"
      :range="moreSwitchRange"
      confirm-text="确认模式"
      @confirm="moreCountConfirm"
    />
    <!-- 多选题积分模式设置 -->
    
    <!-- 继续练习弹窗 -->
    <tn-modal
      v-model="showContinueModal"
      title="提示"
      content="你有保存的练习，是否继续上次？"
      :mask-closeable="false"
      :button="showContinueButton"
      @cancel="handleCancelContinue"
      @click="handleContinueModalClick"
    >
    </tn-modal>
    
    <!-- VIP 功能提示弹窗 -->
    <tn-modal
      v-model="showVipModal"
	  :showCloseBtn="true"
      :width="'75%'"
      title="VIP功能"
      content="此功能为VIP专享功能，开通VIP会员即可使用"
      :button="[
        { text: '暂不开通', backgroundColor: '#F5F5F5', fontColor: '#333333' },
        { text: '立即开通', backgroundColor: mainColor, fontColor: '#FFFFFF' }
      ]"
      :mask-closeable="false"
      @click="handleVipModalClick"
      @cancel="handleVipModalCancel"
    />
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import { checkVipStatusSync, getUserInfo } from '@/util/userStore.js'
	import { hasExamProgress, getExamProgress, clearExamProgress } from '@/util/examProgressManager.js'
	
	export default {
		name: 'QuestionMnSetting',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				questionTypes: [],
				totalCount: 0, // 总题数
				totalScore: 0, // 总分数
				numberRange: [30, 60, 75, 90, 100, 120, 150, 180],// 考试时长选择
				examDuration: 30,// 考试时长
				showDurationPicker: false,// 考试时长选择开关
				moreSwitchRange: ['全部答对，才算得分', '答对一个，也算得分'],// 多选题积分模式选择
				examMoreSwitch: 0,// 多选题积分模式
				showMoreSwitchPicker: false,// 多选题积分模式开关
				passScore: 0, // 及格分，初始为0，加载题型后自动计算为总分60%
				showAnswerAfter: false, // 答题后显示答案开关，默认false（答题模式）
				shuffleOptions: false,
				priorityWrong: false,
				priorityUnattempted: false,
				questionLibUid: '',
				questionsType: 7,
			switchLoading: false, // 开关加载状态
				// VIP 提示相关
				showVipModal: false, // 是否显示 VIP 提示弹窗
				vipModalConfirm: false, // 记录 VIP 提示弹窗的确认结果
				// 进度相关
				showContinueModal: false, // 是否显示继续练习弹窗
				savedProgress: null, // 保存的进度数据
				showContinueButton: [{
						text: '取消',
						backgroundColor: '#E6E6E6',
						fontColor: '#FFFFFF'
					},
					{
						text: '确定',
						backgroundColor: 'tn-bg-indigo',
						fontColor: '#FFFFFF'
					}],
			}
		},
		onLoad(option) {
			// #ifdef MP-WEIXIN
			this.$tn.mpShare = {
				share: false,
			}
			if (!this.$tn.mpShare.share) {
				uni.hideShareMenu()
			}
			// #endif
			this.questionLibUid = option.uid || ''
			this.questionsType = option.questionsType || 7
			this.fetchQuestionType()
			
			// 检查是否有保存的进度
			this.checkSavedProgress()
		},
		methods: {
			// 检查是否有保存的练习进度
			async checkSavedProgress() {
				try {
					// 获取用户ID
					const userInfo = await getUserInfo();
					const userId = userInfo?.id || userInfo?.uid || '';
					
					// 使用统一的进度管理工具检查进度
					// 模拟考试 questions_type=7，不需要 chapterUid
					const hasProgress = hasExamProgress(7, '', userId);
					
					if (hasProgress) {
						// 获取保存的进度
						const progress = getExamProgress(7, '', userId);
						
						// 修复：兼容 questions 和 questionList 两种结构
						const questionList = progress?.questions || progress?.questionList || [];
						
						if (progress && questionList.length > 0) {
							console.log('[模拟考试] 发现保存的进度:', progress);
							this.savedProgress = progress;
							// 显示继续练习弹窗
							this.showContinueModal = true;
						} else {
							console.log('[模拟考试] 保存的进度无效，清除缓存');
							clearExamProgress(7, '', userId);
						}
					} else {
						console.log('[模拟考试] 没有保存的进度');
					}
				} catch (error) {
					console.error('[模拟考试] 检查保存进度失败:', error);
				}
			},
			
			// 继续上次练习
			async handleContinuePractice() {
				if (!this.savedProgress) {
					uni.showToast({
						title: '进度数据异常',
						icon: 'none'
					});
					return;
				}
				
				console.log('[模拟考试] 继续练习，跳转到做题页面');
				
				// 将进度数据保存到全局状态
				getApp().globalData.savedProgress = this.savedProgress;
				getApp().globalData.shouldRestoreProgress = true;
				
				// 设置 examSettings
				getApp().globalData.currentExamSettings = this.savedProgress.questionParams || {
					uid: this.questionLibUid,
					questions_type: 7,
					mode: this.savedProgress.currentMode || 'normal'
				};
				
				// 关闭弹窗
				this.showContinueModal = false;
				
				// 跳转到做题页面，传递 restore=true 参数
				this.$func.navigatorTo('/subpages/examMn/questionMock?restore=true');
			},
			
			// 开始新的练习（清除旧进度）
			async handleStartNewPractice() {
				try {
					const userInfo = await getUserInfo();
					const userId = userInfo?.id || userInfo?.uid || '';
					
					// 清除旧进度
					clearExamProgress(7, '', userId);
					this.savedProgress = null;
					
					console.log('[模拟考试] 已清除旧进度，开始新练习');
				} catch (error) {
					console.error('[模拟考试] 清除进度失败:', error);
				}
				
				// 关闭弹窗
				this.showContinueModal = false;
			},
			handleContinueModalClick(e) {
				console.log('弹窗按钮点击:', e)
				if(e.index === 0) {
					this.handleStartNewPractice()
				} else if(e.index === 1) {
					this.handleContinuePractice()
				}
			},

			// 验证并解析数字
			validateAndParseNumber(value, defaultValue = 0, min = 0, max = Number.MAX_SAFE_INTEGER) {
				const num = parseFloat(value);
				if (isNaN(num) || num < min || num > max) {
					return defaultValue;
				}
				return num;
			},
			
			showDuration() {// 考试时长选择
				this.showDurationPicker = true
			},
			randCountConfirm(e) {// 考试时长确认
				this.examDuration = this.numberRange[e[0]]
				this.showDurationPicker = false
			},
			showMoreCount() {// 多选题模式选择
				this.showMoreSwitchPicker = true
			},
			moreCountConfirm(e) {// 多选题模式确认
				this.examMoreSwitch = e[0]
				this.showMoreSwitchPicker = false
			},
			calculateScore() {// 根据题型动态计算总分
				let scoreSum = 0
				let questionCount = 0
				if (Array.isArray(this.questionTypes)) {
					this.questionTypes.forEach((value, index) => {
						if (value && typeof value.count === 'number' && typeof value.score === 'number') {
							questionCount += this.validateAndParseNumber(value.count, 0, 0)
							scoreSum += this.validateAndParseNumber(value.score, 0, 0) * this.validateAndParseNumber(value.count, 0, 0)
						}
					})
				}
				this.totalScore = scoreSum
				this.totalCount = questionCount
						
				// 自动计算及格分：总分60%向上取整
				this.passScore = Math.ceil(scoreSum * 0.6)
			},
			
			/**
			 * 减少题数
			 * @param {Object} item - 题型对象
			 */
			decreaseCount(item) {
				const currentCount = this.validateAndParseNumber(item.count, 0, 0, item.exam_count || 50)
				if (currentCount <= 0) {
					// 已经是0，不能再减少
					return
				}
				const newCount = Math.max(0, currentCount - 1)
				if (newCount !== currentCount) {
					item.count = newCount
					this.calculateScore()
				}
			},
			
			/**
			 * 增加题数
			 * @param {Object} item - 题型对象
			 */
			increaseCount(item) {
				const currentCount = this.validateAndParseNumber(item.count, 0, 0, item.exam_count || 50)
				const maxCount = item.exam_count || 50
				if (item.exam_count === 0) {
					uni.showToast({
						title: '该题型无试题，无法添加',
						icon: 'none',
						duration: 1500
					})
					return
				}
				if (currentCount >= maxCount) {
					uni.showToast({
						title: `题数不能超过${maxCount}题`,
						icon: 'none',
						duration: 1500
					})
					return
				}
				const newCount = Math.min(maxCount, currentCount + 1)
				if (newCount !== currentCount) {
					item.count = newCount
					this.calculateScore()
				}
			},
			
			// 减少分值
			decreaseScore(item) {
				const currentScore = this.validateAndParseNumber(item.score, 1, 1, 100)
				if (currentScore <= 1) {
					uni.showToast({
						title: '分值不能少于1分',
						icon: 'none',
						duration: 1500
					})
					return
				}
				const newScore = Math.max(1, currentScore - 1)
				if (newScore !== currentScore) {
					item.score = newScore
					this.calculateScore()
				}
			},
			
			// 增加分值
			increaseScore(item) {
				const currentScore = this.validateAndParseNumber(item.score, 1, 1, 100)
				if (currentScore >= 100) {
					uni.showToast({
						title: '分值不能超过100分',
						icon: 'none',
						duration: 1500
					})
					return
				}
				const newScore = Math.min(100, currentScore + 1)
				if (newScore !== currentScore) {
					item.score = newScore
					this.calculateScore()
				}
			},
			
			submitConfig() {// 开始考试
				// 参数验证
				if (!this.validateExamConfig()) {
					return
				}
				
				// 清除旧进度（开始新练习时自动覆盖）
				this.handleStartNewPractice()
				
				// 显示加载状态
				uni.showLoading({
					title: '正在创建考试...',
					mask: true
				})
				
				this.$api.apiMockExaminationConfig({
					paper_score: this.totalScore,
					score: this.passScore,
					option_type_config: JSON.stringify(this.questionTypes),
					exam_time: this.examDuration,
					checkbox_type: this.examMoreSwitch === 1 ? 2 : 1,
					option_type: this.shuffleOptions ? 1 : 2,
					library_uid: this.questionLibUid,
					// 传递错题优先和未做题优先参数
					priority_wrong: this.priorityWrong ? 1 : 0,
					priority_unattempted: this.priorityUnattempted ? 1 : 0
				}).then(res => {
					uni.hideLoading()
					if (res.code === 1) {
						// 根据答题后显示答案开关确定模式
						// showAnswerAfter为true时使用学练结合模式，为false时使用答题模式
						const examMode = this.showAnswerAfter ? 'learnPractice' : 'normal'
						
						// 修复：从响应中获取 history_uid
						const historyUid = res.data?.history_uid || res.data?.uid || ''
						const examDuration = res.data?.exam_time || 0
						if (!historyUid) {
							console.error('[模拟考试] 未获取到 history_uid')
							this.$func.showToast('创建考试失败，请重试')
							return
						}
						
						const examSettings = {
							uid: historyUid, // 使用 history_uid 作为 uid
							questions_type: this.questionsType,
							mode: examMode,
							questionType: 'mock', // 考试类型，对应commonQuestion.vue中的case 'mock'
							showAnswerAfter: this.showAnswerAfter, // 传递开关状态
							// 传递选项乱序、错题优先、未做题优先状态
							shuffleOptions: this.shuffleOptions,
							priorityWrong: this.priorityWrong,
							exam_time: examDuration,
							priorityUnattempted: this.priorityUnattempted,
							isExamination: true	
						}
						
						// 直接跳转到答题页面，不需要经过设置步骤
						this.$func.navigatorTo('/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings)))
						return
					}
					this.$func.showToast(res.msg || '创建考试失败，请重试')
				}).catch(error => {
					uni.hideLoading()
					console.error('创建考试失败:', error)
					this.$func.showToast('网络错误，请检查网络连接后重试')
				})
			},
			
			/**
			 * 辅助方法：验证考试配置
			 * @returns {boolean} - 配置是否有效
			 */
			validateExamConfig() {
				// 检查题库UID
				if (!this.questionLibUid) {
					this.$func.showToast('题库信息错误');
					return false;
				}
				
				if (!Array.isArray(this.questionTypes) || this.questionTypes.length === 0) {
					this.$func.showToast('请至少选择一种题型');
					return false;
				}
				
				// 检查每种题型的分值（题数可以为0）
				for (const item of this.questionTypes) {
					// 题数可以为0，但如果有题数，分值必须大于0
					if (item.count > 0 && item.score <= 0) {
						this.$func.showToast(`${item.name}分值必须大于0`);
						return false;
					}
				}
				
				// 检查总题数（至少要有1道题）
				if (this.totalCount <= 0) {
					this.$func.showToast('总题数必须大于0，请至少选择1道题目');
					return false;
				}
				
				// 检查及格分
				if (this.passScore < 0 || this.passScore > this.totalScore) {
					this.$func.showToast(`及格分必须在0-${this.totalScore}之间`);
					return false;
				}
				
				// 检查考试时长
				if (this.examDuration <= 0) {
					this.$func.showToast('考试时长必须大于0');
					return false;
				}
				
				return true;
			},
			switchHistory() {
				this.$func.navigatorTo('/subpages/examHistory/examinationHistory?uid=' + this.questionLibUid)
			},
			/**
			 * 检查用户VIP状态
			 * 使用统一的用户管理工具，带有自动降级策略
			 * @returns {boolean} - true表示有VIP权限，false表示无VIP权限
			 */
			checkVipStatus() {
				return checkVipStatusSync()
			},
			
			/**
			 * 处理 VIP 提示弹窗的点击事件
			 */
			handleVipModalClick(event) {
				if (event.index === 1) {
					// 用户点击“立即开通”
					this.vipModalConfirm = true;
					// 跳转到 VIP 开通页面
					uni.navigateTo({
						url: '/subpages/user/member'
					});
				} else {
					// 用户点击“暂不开通”，确保开关状态为关闭
					this.vipModalConfirm = false;
					// 直接设置开关状态为关闭
					this.shuffleOptions = false;
					this.priorityWrong = false;
					this.priorityUnattempted = false;
				}
				// 关闭弹窗
				this.showVipModal = false;
			},
			
			/**
			 * 处理 VIP 提示弹窗的取消事件
			 */
			handleVipModalCancel() {
				// 点击遮罩层关闭弹窗，视为取消，确保开关状态为关闭
				this.vipModalConfirm = false;
				// 直接设置开关状态为关闭
				this.shuffleOptions = false;
				this.priorityWrong = false;
				this.priorityUnattempted = false;
				this.showVipModal = false;
			},
			
			/**
			 * 显示VIP需要对话框
			 * 提示用户开通VIP会员
			 */
			showVipRequiredDialog() {
				// 用户未开通,确保VIP功能开关为false
				this.shuffleOptions = false;
				this.priorityWrong = false;
				this.priorityUnattempted = false;
				// 显示tn-modal弹窗
				this.showVipModal = true;
			},
			
			/**
			 * 处理开关点击事件
			 * @param {string} key - 要修改的状态字段名
			 */
			handleSwitchClick(key) {
				// 计算新的开关状态
				const newState = !this[key];
				
				// 定义需要VIP权限的功能列表
				const vipFeatures = ['shuffleOptions', 'priorityWrong', 'priorityUnattempted'];
				
				// 如果是VIP功能且用户试图开启它
				if (vipFeatures.includes(key) && newState === true) {
					// 检查VIP状态
					if (!this.checkVipStatus()) {
						// 非VIP用户，显示VIP提示
						this.showVipRequiredDialog();
						// 保持关闭状态
						return;
					}
				}
				
				// VIP用户或关闭开关时，直接更新状态
				this[key] = newState;
			},
			
			/**
			 * 开关切换事件处理（兼容旧代码）
			 * @param {string} key - 要修改的状态字段名
			 * @param {Object} event - 开关事件对象
			 */
			onSwitchChange(key, event) {
				// 参数验证
				if (!key || typeof event !== 'object' || event.detail === undefined) {
					console.warn('onSwitchChange: 无效参数', { key, event });
					return;
				}
				
				// 定义需要VIP权限的功能列表
				const vipFeatures = ['shuffleOptions', 'priorityWrong', 'priorityUnattempted'];
				
				// 如果是VIP功能且用户试图开启它
				if (vipFeatures.includes(key) && event.detail.value === true) {
					// 检查VIP状态
					if (!this.checkVipStatus()) {
						// 非VIP用户，保持关闭状态
						this[key] = false;
						// 显示VIP开通提示
						this.showVipRequiredDialog();
						return;
					}
				}
				
				// 更新对应的状态值
				this[key] = event.detail.value;
			},
			fetchQuestionType() {
				// 参数验证
				if (!this.questionLibUid) {
					this.$func.showToast('题库信息错误');
					return;
				}
				
				// 显示加载状态
				uni.showLoading({
					title: '加载中..'
				});
				
				this.$api.apiQuestionTypeList({
					uid: this.questionLibUid
				}).then(res => {
					uni.hideLoading();
					if (res.code === 1 && res.data && Array.isArray(res.data.exam_type_list)) {
						this.questionTypes = res.data.exam_type_list;
					// 初始化每个题型的分值和题数
					this.questionTypes.forEach((value, index) => {
						value.score = 1; // 默认分值为1
						value.count = value.exam_count === 0 ? 0 : 1; // 当试题数量为0时，题数显示为0，否则为1
					});
						this.calculateScore();
					} else {
						console.warn('获取题型列表失败:', res.msg);
						this.$func.showToast(res.msg || '获取题型失败');
					}
				}).catch(error => {
					uni.hideLoading();
					console.error('获取题型列表失败:', error);
					this.$func.showToast('网络错误，请重试');
				});
			},
		}
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.container {
		display: flex;
		flex-direction: column;
		height: 100vh;
		background-color: #f8f8f8;
		font-size: 28rpx;
	}

	.main-content {
		flex-grow: 1;
		/* 占据剩余空间 */
		padding-bottom: 140rpx;
		/* 为底部按钮留出空间 */
		box-sizing: border-box;
		-webkit-overflow-scrolling: touch;
	}

	.section {
		background-color: #fff;
		margin: 20rpx;
		border-radius: 16rpx;
		padding: 0 30rpx;
	}

	/* 题型设置 */
	.question-type-section {
		padding-top: 20rpx;
		padding-bottom: 10rpx;
	}

	.header-row,
	.data-row {
		display: flex;
		align-items: center;
		padding: 15rpx 0;
		color: #666;
	}

	.header-row {
		color: #999;
		font-size: 26rpx;
		padding-bottom: 10rpx;
	}

	.data-row {
		border-bottom: 1rpx solid #f0f0f0;

		&:last-child {
			border-bottom: none;
		}
	}

	.header-cell,
	.data-cell {
		text-align: center;
	}

	.type-header,
	.type-cell {
		width: 30%;
		text-align: left;
	}

	.count-header,
	.count-cell {
		width: 40%;
	}

	.score-header,
	.score-cell {
		width: 40%;
	}

	.type-cell {
		color: #333;
		font-size: 30rpx;
	}

	.input-field {
		background-color: #f7f7f7;
		border-radius: 8rpx;
		// padding: 8rpx 15rpx;
		font-size: 28rpx;
		text-align: center;
		width: 80rpx;
		/* 根据需要调整宽度 */
		box-sizing: border-box;
		display: inline-block;
		/* 使其可以居中 */
		height: 60rpx;
		line-height: 60rpx;
		padding: 0 5rpx;
	}
	
	/* 输入步进器容器 */
	.input-stepper {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 8rpx;
	}
	
	/* 步进器按钮 */
	.stepper-btn {
		width: 50rpx;
		height: 50rpx;
		border-radius: 8rpx;
		background-color: #f0f0f0;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 28rpx;
		color: #666;
		transition: all 0.3s;
		cursor: pointer;
		
		&.stepper-btn-hover:not(.disabled) {
			background-color: #e0e0e0;
			transform: scale(0.95);
		}
		
		&.disabled {
			opacity: 0.4;
			background-color: #f5f5f5;
			color: #ccc;
			cursor: not-allowed;
			
			&.stepper-btn-hover {
				background-color: #f5f5f5;
				transform: scale(1);
			}
		}
		
		.tn-icon-reduce,
		.tn-icon-add {
			font-size: 24rpx;
			font-weight: bold;
		}
	}
	
	.minus-btn {
		&.stepper-btn-hover:not(.disabled) {
			background-color: #ffecec;
			color: #ff6b6b;
		}
	}
	
	.plus-btn {
		&.stepper-btn-hover:not(.disabled) {
			background-color: #e6f7ff;
			color: $view-theme;
		}
	}

	.score-input {
		width: 120rpx;
	}

	.summary-row {
		display: flex;
		justify-content: space-between;
		padding: 25rpx 0;
		margin-top: 10rpx;
		font-size: 28rpx;
		color: #333;
		border-top: 1rpx solid #f0f0f0;
	}

	.highlight {
		color: $view-theme;
		font-weight: bold;
		margin: 0 5rpx;
	}

	/* 考试设置 */
	.settings-section {
		padding: 0;
		/* section自带左右padding */
	}

	.setting-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 30rpx;
		border-bottom: 1rpx solid #f0f0f0;

		&:last-child {
			border-bottom: none;
		}
	}

	.label {
		color: #333;
		font-size: 30rpx;
	}

	.label-group {
		display: flex;
		flex-direction: row;
		align-items: flex-start;

		/* 左对齐 */
		.label {
			margin-bottom: 5rpx;
		}

		.description {
			margin: 0 10rpx;
			font-size: 24rpx;
			color: #999;
		}
	}

	.vip-tag {
		background: linear-gradient(135deg, #FF9500, #FF6B00);
		/* 渐变橙色背景 */
		color: white;
		/* 白色文字 */
		font-size: 20rpx;
		font-weight: bold;
		padding: 3rpx 10rpx;
		border-radius: 6rpx;
		margin-left: 10rpx;
		display: inline-block;
		/* 使其可以和文字同行 */
		vertical-align: middle;
		/* 垂直居中 */
		margin-bottom: 5rpx;
		/* 与下方描述文字的间距 */
		box-shadow: 0 2rpx 4rpx rgba(255, 149, 0, 0.3);
		/* 添加阴影效果 */
	}

	.label-group .label+.vip-tag {
		margin-left: 10rpx;
		/* VIP 标签与主标签的间距 */
	}


	.value-section {
		display: flex;
		align-items: center;
		color: #666;
	}

	.value {
		margin-right: 10rpx;
	}

	.arrow-icon {
		font-size: 30rpx;
		color: #ccc;
	}

	.unit {
		margin-left: 10rpx;
		color: #666;
	}

	/* 规则说明 */
	.rule-description {
		padding: 20rpx 30rpx;
		font-size: 24rpx;
		color: #999;
		line-height: 1.6;
		background-color: #f8f8f8;
		/* 与页面背景色一致 */
	}

	/* 底部操作栏 */
	.bottom-bar {
		position: fixed;
		bottom: 0;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 20rpx 30rpx;
		padding-bottom: calc(20rpx + constant(safe-area-inset-bottom));
		/* 适配 iPhone X 等底部安全区 */
		padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
		background-color: #fff;
		border-top: 1rpx solid #eee;
		z-index: 10;
	}

	.history-link {
		display: flex;
		flex-direction: column;
		align-items: center;
		font-size: 22rpx;
		color: #666;
	}

	.history-icon {
		font-size: 40rpx;
		margin-bottom: 5rpx;
	}

	.start-button {
		flex-grow: 1;
		margin-left: 30rpx;
		height: 80rpx;
		line-height: 80rpx;
		font-size: 32rpx;
		background-color: $view-theme;
		border-radius: 40rpx;
	}

	/* 继续练习弹窗按钮 */
	.modal-buttons {
		display: flex;
		gap: 20rpx;
		padding: 20rpx;
		justify-content: center;
	}

	.modal-btn {
		flex: 1;
		height: 80rpx;
		line-height: 80rpx;
		border-radius: 40rpx;
		font-size: 28rpx;
		border: none;
		color: #fff;
	}

	.cancel-btn {
		background-color: #999;
	}

	.start-new-btn {
		background-color: #ff6b6b;
	}

	.continue-btn {
		background-color: $view-theme;
	}

</style>