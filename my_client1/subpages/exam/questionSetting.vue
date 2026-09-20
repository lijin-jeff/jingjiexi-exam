<template>
  <view class="container">
    <!-- 做题设置页面 -->
    <view class="setting-container">
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
            做题设置
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
            <tn-list-cell
              class="header-cell"
              padding="26rpx 0;"
              unlined="true"
            >
              <view class="list__left">
                题型
              </view>
            </tn-list-cell>
          </view>
          <tn-checkbox-group
            v-model="selectedQuestionTypes"
            @change="checkboxGroupChange"
          >
            <tn-checkbox
              v-for="(item, index) in questionTypes"
              :key="index"
              :name="item.name"
              :activeColor="mainColor"
            >
              {{ item.name }}
            </tn-checkbox>
          </tn-checkbox-group>

          <view class="header-row">
            <tn-list-cell
              class="header-cell"
              padding="26rpx 0;"
              unlined="true"
            >
              <view class="list__left">
                类型
              </view>
            </tn-list-cell>
          </view>
          <tn-radio-group
            v-model="questionTypeValue"
            @change="onQuestionTypeChange"
          >
            <tn-radio
              v-for="(item, index) in questionTypeList"
              :key="index"
              :name="item.name"
              :activeColor="mainColor"
              borderColor="#AAAAAA"
              :disabled="item.disabled"
            >
              {{ item.name }}
            </tn-radio>
          </tn-radio-group>

          <view class="header-row">
            <tn-list-cell
              class="header-cell"
              padding="26rpx 0;"
              unlined="true"
            >
              <view class="list__left">
                模式
              </view>
            </tn-list-cell>
          </view>
          <tn-radio-group
            v-model="practiceModeValue"
            @change="onPracticeModeChange"
          >
            <tn-radio
              v-for="(item, index) in practiceModeList"
              :key="index"
              :name="item.name"
              :disabled="item.disabled"
              :activeColor="mainColor"
              borderColor="#AAAAAA"
            >
              {{ item.name }}
            </tn-radio>
          </tn-radio-group>

          <view class="header-row">
            <tn-list-cell
              class="header-cell"
              padding="26rpx 0;"
              unlined="true"
            >
              <view class="list__left">
                难易程度
              </view>
            </tn-list-cell>
          </view>
          <tn-radio-group
            v-model="examLevelValue"
            @change="onExamLevelChange"
          >
            <tn-radio
              v-for="(item, index) in examLevelList"
              :key="index"
              :name="item.name"
              :disabled="item.disabled"
              :activeColor="mainColor"
              borderColor="#AAAAAA"
            >
              {{ item.name }}
            </tn-radio>
          </tn-radio-group>


          <view class="header-row">
            <tn-list-cell
              padding="26rpx 0;"
              unlined="true"
            >
              <view class="list-icon-text">
                <view class="list__left">
                  <view class="list__left__text">
                    数量
                  </view>
                </view>
                <view class="list__right">
                  <view class="tn-text-sm tn-color-gray">
                    {{ SelectCount }} / {{ questionCount }}
                  </view>
                </view>
              </view>
            </tn-list-cell>
          </view>
          <tn-radio-group
            v-model="questionCountValue"
            @change="onQuestionCountChange"
          >
            <tn-radio
              v-for="(item, index) in questionCountList"
              :key="index"
              :name="item.name"
              :disabled="item.disabled"
              :activeColor="mainColor"
              borderColor="#AAAAAA"
            >
              {{ item.name }}
            </tn-radio>
          </tn-radio-group>
        </view>

        <view class="section settings-section">
          <view class="setting-item">
            <view class="label-group">
              <text class="label">
                随机选题
              </text>
              <text class="vip-tag">
                VIP
              </text>
              <text class="description">
                从题库中随机选题
              </text>
            </view>
            <view @click.stop="handleSwitchClick">
              <switch
                :checked="randomSelect"
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
          <view>规则说明</view>
          <view>1. 题数说明：相同类型的试题，题数不能超过该类型下的总题数</view>
          <view>2. 随机选题：开启后，返回的试题，顺序将被随机打乱</view>
        </view>

        <view class="tn-padding-xl" />
      </scroll-view>

      <!-- 底部操作栏-->
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
          开始做题
        </button>
      </view>
      
      <!-- 继续练习弹窗 -->
      <tn-modal
        v-model="showContinueModal"
		:showCloseBtn="true"
        :width="'75%'"
        title="继续练习"
        content="你有保存的练习，是否继续上次？"
        :button="[
          { text: '取消', backgroundColor: '#F5F5F5', fontColor: '#333333' },
          { text: '继续', backgroundColor: mainColor, fontColor: '#FFFFFF' }
        ]"
        :mask-closeable="false"
        @click="handleContinueModalClick"
        @cancel="handleCancelContinue"
      />
      
      <!-- VIP 功能提示弹窗 -->
      <tn-modal
        v-model="showVipModal"
        :width="'75%'"
        title="VIP功能"
        content="此功能为VIP专享功能，开通VIP会员即可使用"
        :button="[
          { text: '暂不开通', backgroundColor: '#F5F5F5', fontColor: '#333333' },
          { text: '立即开通', backgroundColor: mainColor, fontColor: '#FFFFFF' }
        ]"
        :maskCloseable="false"
        @click="handleVipModalClick"
        @cancel="handleVipModalCancel"
      />
    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	
	import { getQuestionTypeValue } from '@/util/questionTypeManager.js'
	import { checkVipStatusSync, getUserInfo } from '@/util/userStore.js'
	import { hasExamProgress, getExamProgress, clearExamProgress } from '@/util/examProgressManager.js'
	export default {
		name: 'QuestionMnSetting',
		
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				questionTypes: [],
				selectedQuestionTypes: [], // 存储选中的题型
				selectedQuestionTypesValue: [1,2,3,4,5,6], // 存储选中的题型值
				totalCount: 0, // 总题数
				randomSelect: false,
				shuffleOptions: true,
				priorityUnattempted: false,
				questionLibUid: '',
				questionsType: '',
				SelectCount: 0, // 已选择的题数
				questionCount: 0, // 题库总题数
				chapterUid: 0, // 章节ID
				
				// 继续练习相关
			showContinueModal: false, // 是否显示继续练习弹窗
			savedProgress: null, // 保存的进度数据
			
			// switch 异步控制相关
			switchLoading: false, // 开关加载状态
			controlStatus: false, // 防止watch无限循环的中间变量
			
			// VIP 提示相关
			showVipModal: false, // 是否显示 VIP 提示弹窗
			vipModalConfirm: false, // 记录 VIP 提示弹窗的确认结果
				
				// 分别为不同的单选组定义不同的value变量和列表数据
				questionTypeValue: '未做',
				questionTypeList: [
					{ name: '全部', disabled: false },
					{ name: '未做', disabled: false },
					{ name: '已做', disabled: false },
					{ name: '错题', disabled: false },
				],
				practiceModeValue: '学练结合',
				practiceModeList: [
					{ name: '学练结合', disabled: false },
					{ name: '答题模式', disabled: false },
					{ name: '背题模式', disabled: false }
				],
				examLevelValue: '全部',
				examLevelList: [
					{ name: '全部', disabled: false },
					{ name: '简单', disabled: false },
					{ name: '中等', disabled: false },
					{ name: '困难', disabled: false },
				],
				questionCountValue: '全部',
				questionCountList: [
					{ name: '全部', disabled: false },
					{ name: '5题', disabled: false },
					{ name: '10题', disabled: false },
					{ name: '20题', disabled: false },
					{ name: '50题', disabled: false },
				],
				selecte_type: 1,
				exam_type: 0,
				question_count: 0,
				practice_mode: 1,
				exam_level: 0, // 0 表示全部难度
				is_rand: 2, // 2 表示默认关闭随机抽题
			}
		},
		onShow() {
			// 检查是否有待加载的答题设置（来自 analysis/questionError/questionCollection 页面）
		const examSettings = getApp().globalData.currentExamSettings
		// 检测是否有错题、收藏等特殊参数
		if (examSettings && (examSettings.analysis_quid)) {
					
			// 跳转到答题页面
			uni.navigateTo({
				url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
			});
			return
		}
					
			if (this.needRefresh) {
				// 执行数据刷新操作
				this.loadData()
				// 重置标记
				this.needRefresh = false
			}
		},
		onRefresh() {
		// 执行数据刷新操作
		this.loadData()
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
			this.questionLibUid = option.uid
			this.questionsType = option.questions_type
			this.questionCount = option.question_count
			this.chapterUid = option.chapter_uid
			
			if (option.showQuestion === 'true') {
				this.showQuestion = true
			}
			// 修复：检测 restore 参数，如果为 true 则直接显示做题页面
			if (option.restore === 'true') {
				// 直接显示 commonQuestion 组件
				this.showQuestion = true
				// 设置标志，表示需要恢复进度
				this.shouldRestoreProgress = true
				
				// 等待 commonQuestion 组件渲染完成后恢复进度
				this.$nextTick(() => {
					const savedProgress = getApp().globalData.savedProgress
					if (savedProgress) {
						const commonQuestionComponent = this.$children.find(child => child.$options.name === 'CommonQuestion')
						if (commonQuestionComponent && typeof commonQuestionComponent.restoreProgress === 'function') {
							commonQuestionComponent.restoreProgress(savedProgress)
							// 清除全局状态
							getApp().globalData.savedProgress = null
							getApp().globalData.shouldRestoreProgress = false
						} else {
							console.error('[做题设置] 未找到 commonQuestion 组件或 restoreProgress 方法')
						}
					}
				})
				return
			}
			
			if (this.questionLibUid) {
				this.fetchQuestionType()
				this.updateSelectedQuestionCount()
			}

			// 如果是 questions_type=8，检查是否有保存的进度
			if (this.questionsType == '8') {
				this.checkSavedProgress()
			}
		},
	methods: {
			// 检查是否有保存的练习进度
			async checkSavedProgress() {
				try {
					// 获取用户ID
					const userInfo = await getUserInfo();
					const userId = userInfo?.id || userInfo?.uid || '';
					
					// 使用统一的进度管理工具检查进度
					// 修复：章节练习需要传入 chapterUid
					const chapterUid = this.chapterUid || '';
					const hasProgress = hasExamProgress(parseInt(this.questionsType), chapterUid, userId);
									
					if (hasProgress) {
						// 获取保存的进度
						const progress = getExamProgress(parseInt(this.questionsType), chapterUid, userId);
										
						// 修复：兼容 questions 和 questionList 两种结构
						const questionList = progress?.questions || progress?.questionList || [];
										
						if (progress && questionList.length > 0) {
							this.savedProgress = progress;
							// 显示继续练习弹窗
							this.showContinueModal = true;
						} else {
							clearExamProgress(parseInt(this.questionsType), chapterUid, userId);
						}
					}
				} catch (error) {
					console.error('[做题设置] 检查保存进度失败:', error);
				}
			},
			
			// 处理继续练习弹窗的点击事件
			handleContinueModalClick(event) {				
				if (event.index === 0) {
					// 点击“取消”或关闭弹窗
					this.handleCancelContinue()
				} else if (event.index === 1) {
					// 点击“继续”
					this.handleContinuePractice()
				}
			},
			
			// 处理 VIP 提示弹窗的点击事件
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
					this.randomSelect = false;
					this.is_rand = 2;
				}
				// 关闭弹窗
				this.showVipModal = false;
				// 调用 resolve 函数返回结果
				if (this.vipModalResolve) {
					this.vipModalResolve(this.vipModalConfirm);
					// 清空 resolve 函数引用
					this.vipModalResolve = null;
				}
			},
			
			// 处理 VIP 提示弹窗的取消事件
			handleVipModalCancel() {
				// 点击遮罩层关闭弹窗，视为取消，确保开关状态为关闭
				this.vipModalConfirm = false;
				// 直接设置开关状态为关闭
				this.randomSelect = false;
				this.is_rand = 2;
				this.showVipModal = false;
				// 调用 resolve 函数返回结果
				if (this.vipModalResolve) {
					this.vipModalResolve(this.vipModalConfirm);
					// 清空 resolve 函数引用
					this.vipModalResolve = null;
				}
			},
			
			// 显示 VIP 提示弹窗
			showVipRequiredDialog() {
				return new Promise((resolve) => {
					// 保存 resolve 函数，以便在弹窗关闭时调用
					this.vipModalResolve = resolve;
					// 显示弹窗
					this.showVipModal = true;
				});
			},
			
			// 已取消继续练习，开始新的答题
			async handleCancelContinue() {
				this.showContinueModal = false;
				uni.showToast({
					title: '已取消继续练习，开始新的答题',
					icon: 'none',
					duration: 1500
				});
				// 清除当前用户当前练习类型的进度缓存
				try {
					const userInfo = await getUserInfo();
					const userId = userInfo?.id || userInfo?.uid || '';
					const chapterUid = this.chapterUid || ''; // 假设组件中存储了章节UID
					
					clearExamProgress(parseInt(this.questionsType), chapterUid, userId);
					this.savedProgress = null;
				} catch (error) {
					console.error('[做题设置] 清除进度缓存失败:', error);
				}
				
				// 返回主页
				setTimeout(() => {
					uni.switchTab({ url: '/pages/index/index' });
				}, 100);
			},
			
			// 继续上次的练习
			handleContinuePractice() {
				this.showContinueModal = false
				
				if (this.savedProgress) {
					// 保存到全局状态，供答题页面恢复使用
					getApp().globalData.savedProgress = this.savedProgress
					getApp().globalData.shouldRestoreProgress = true
					
					// 跳转到独立的答题页面，带上restore参数
			uni.navigateTo({
				url: '/subpages/exam/components/commonQuestion?restore=true'
			});
				}
			},
			
			// 题型选择改变事件
			checkboxGroupChange(e) {
				// 使用统一的题型管理工具转换题型名称为值
				if (e) {
					this.selectedQuestionTypesValue = []
				}
				e.forEach((typeName) => {
					const typeValue = getQuestionTypeValue(typeName, this.questionTypes)
					if (typeValue) {
						this.selectedQuestionTypesValue.push(typeValue)
					}
				})
				// 同步更新每个选项的checked状态
				this.questionTypes.forEach((item) => {
					item.checked = this.selectedQuestionTypes.includes(item.name)
					
				})
				// 更新已选择的题型
				this.updateSelectedQuestionCount()
			},
			
			// 获取选项对应的数值（用于API调用）
			getOptionValue(textValue, valueMap) {
				return valueMap[textValue] || textValue
			},
			
			// 不同单选组的change事件处理
			onQuestionTypeChange(e) {
				this.questionTypeValue = e
				this.updateSelectedQuestionCount()
			},
			
			onPracticeModeChange(e) {
				this.practiceModeValue = e
				this.updateSelectedQuestionCount()
			},
			
			onExamLevelChange(e) {
				this.examLevelValue = e
				this.updateSelectedQuestionCount()
			},
			
			onQuestionCountChange(e) {
				this.questionCountValue = e,
				this.updateSelectedQuestionCount()
			},
			
			// 统一更新已选择的题型
			updateSelectedQuestionCount() {
				const valueMap = {
					'全部': 1,
					'未做': 2,
					'已做': 3,
					'错题': 4
				}
				this.selecte_type = valueMap[this.questionTypeValue]

				const ModevalueMap = {
					'学练结合': 1,
					'答题模式': 2,
					'背题模式': 3
				}
				this.practice_mode = ModevalueMap[this.practiceModeValue]
				
				const LevelvalueMap = {
					'全部': 0,
					'简单': 1,
					'中等': 2,
					'困难': 3
				}
				this.exam_level = LevelvalueMap[this.examLevelValue]

				const countMap = {
					'全部': 99999,
					'5题': 5,
					'10题': 10,
					'20题': 20,
					'50题': 50,
				}
				this.question_count = countMap[this.questionCountValue]
				this.exam_type = this.selectedQuestionTypesValue.join(',')
				// 同步更新已选择的题型
				this.$api.apiSelectedQuestionCount({
					uid: this.questionLibUid,
					exam_type: this.exam_type, 
					selecte_type: this.selecte_type,
					exam_level: this.exam_level,
					question_count: this.question_count,
					chapter_uid: this.chapterUid
				}).then(res => {
					if (res && res.code === 1) {
						// 如果用户选择的是"全部"，则使用API返回的题数
						// 否则使用用户选择的题数
						if (this.questionCountValue === '全部') {
							this.SelectCount = res.data.count
						} else {
							// 用户选择了具体数量，SelectCount 直接使用用户选择的值
							this.SelectCount = this.question_count
						}
						// questionCount（题库总题数）保持不变
						if (!this.questionCount || this.questionCount === 0) {
							this.questionCount = res.data.count
						}
						
						// 动态更新题目数量选择器的可选范围
						const availableCount = res.data.count;
						this.questionCountList.forEach(item => {
							const itemCount = countMap[item.name];
							// "全部"选项始终可用
							if (item.name === '全部') {
								item.disabled = false;
							} else {
								// 其他选项只有当可用题数大于等于该选项值时才可用
								item.disabled = availableCount < itemCount;
							}
						});
						
						// 自动调整默认值：如果当前选择的数量超出可用范围，选择最接近的可用选项
						if (this.questionCountValue !== '全部') {
							const currentCount = countMap[this.questionCountValue];
							if (currentCount > availableCount) {
								// 找到小于等于可用题数的最大选项
								const availableOptions = this.questionCountList.filter(item => 
									item.name !== '全部' && countMap[item.name] <= availableCount
								);
								if (availableOptions.length > 0) {
									// 按数量降序排序，选择最大的可用选项
									availableOptions.sort((a, b) => countMap[b.name] - countMap[a.name]);
									this.questionCountValue = availableOptions[0].name;
									this.question_count = countMap[this.questionCountValue];
									this.SelectCount = this.question_count;
								} else {
									// 如果没有可用的具体数量选项，切换到"全部"
									this.questionCountValue = '全部';
									this.question_count = countMap[this.questionCountValue];
									this.SelectCount = availableCount;
								}
							}
						}
						
						if (this.SelectCount === 0) {
							uni.showToast({
								title: '当前选择暂无题目',
								icon: 'none',
								duration: 2000
							})
						}
					}
				}).catch(err => {
					console.error('获取已选题数失败', err)
				})
			},
			submitConfig() { // 开始做题
				// 关闭继续练习弹窗（如果正在显示）
				if (this.showContinueModal) {
					this.showContinueModal = false
				}
				
				// 准备传递给commonQuestion.vue的参数
			const examSettings = {
				uid: this.questionLibUid,
				mode: this.practice_mode === 1 ? 'learnPractice' : this.practice_mode === 2 ? 'normal' : 'reviewOnly',
				question_count: this.SelectCount,
				exam_type: this.exam_type,
				selecte_type: this.selecte_type,
				exam_level: this.exam_level,
				practice_mode: this.practice_mode,
				is_rand: this.is_rand,
				questions_type: this.questionsType,
				chapter_uid: this.chapterUid
			}
				
				// 修复：清除当前用户当前练习类型的旧进度缓存，使用统一的进度管理工具
				try {
					getUserInfo().then(userInfo => {
						const userId = userInfo?.id || userInfo?.uid || '';
						const chapterUid = this.chapterUid || '';
						
						// 使用统一的进度管理工具清除进度
						const hasProgress = hasExamProgress(parseInt(this.questionsType), chapterUid, userId);
						if (hasProgress) {
							clearExamProgress(parseInt(this.questionsType), chapterUid, userId);
							this.savedProgress = null; // 清空已保存的进度引用
							
							// 显示清除提示
							uni.showToast({
								title: '已清除旧进度，开始新练习',
								icon: 'success',
								duration: 1500
							});
						}
					}).catch(error => {
						console.error('获取用户信息失败:', error);
					});
				} catch (error) {
					console.error('清除旧进度缓存失败:', error);
				}
				
				// 跳转到独立的答题页面
		uni.navigateTo({
			url: '/subpages/exam/components/commonQuestion?examSettings=' + encodeURIComponent(JSON.stringify(examSettings))
		});
			},
			
			switchHistory() {
				this.$func.navigatorTo('/subpages/examHistory/examinationHistory?uid=' + this.questionLibUid)
			},
			onSwitchChange(key, event) {
				this[key] = event.detail.value;
			},
			// 处理开关点击事件
			handleSwitchClick() {
				// 计算新的开关状态
				const newState = !this.randomSelect;
				
				// 如果是要开启开关，检查VIP权限
				if (newState) {
					if (!this.checkVipStatus()) {
						// 非VIP用户，显示VIP提示
						this.showVipModal = true;
						return;
					}
				}
				
				// VIP用户或关闭开关，直接更新状态
				this.randomSelect = newState;
				this.is_rand = newState ? 1 : 2;
			},
			fetchQuestionType() {
				this.$api.apiQuestionTypeList({
					uid: this.questionLibUid
				}).then(res => {
					this.questionTypes = res.data.exam_type_list || []
					this.questionTypes.forEach((value, index) => {
						value.score = 1
						value.count = 1
						value.checked = true // 初始化选中状态
					})
       				 this.selectedQuestionTypes = this.questionTypes.map(item => item.name)
				}).catch(err => {
					console.error('获取题型失败:', err)
					this.$func.showToast('获取题型失败，请重试')
				})
			},
			onSwitchChangeSelect(key, event) {
				const newValue = event.detail.value;
				
				// 如果是随机选题开关，检查 VIP 权限
				if (key === 'randomSelect' && newValue === true) {
					if (!this.checkVipStatus()) {
						// 非 VIP 用户，显示 VIP 需求对话框
						this.showVipRequiredDialog().then((confirmed) => {
							if (!confirmed) {
								// 用户取消，恢复开关未开启状态
								this.$nextTick(() => {
									this[key] = false;
								});
							}
						});
						return; // 不立即更新状态
					}
				}
				// 更新状态
				this[key] = newValue;
				// 更新is_rand
				this.is_rand = this.randomSelect ? 1 : 2;
			},

			/**
			 * 检查用户VIP状态
			 * 使用统一的用户管理工具，带有自动降级策略
			 * @returns {boolean} - true表示有VIP权限，false表示无VIP权限
			 */
			checkVipStatus() {
				return checkVipStatusSync()
			},
			// 返回首页
			handleGoHome() {
			uni.switchTab({ url: '/pages/index/index' })
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
		font-size: 30rpx;
	}

	.main-content {
		flex-grow: 1;
		padding-bottom: 140rpx;
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

	.header-row {
		align-items: center;
		padding: 15rpx 0;
		color: #999;
		font-size: 26rpx;
		padding-bottom: 10rpx;
	}

	.header-cell {
		text-align: center;
	}

	/* 考试设置 */
	.settings-section {
		padding: 0;
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

		.label {
			margin-bottom: 5rpx;
		}

		.description {
			font-size: 24rpx;
			color: #999;
		}
	}

	.vip-tag {
		background-color: #FFF0E6;
		color: #FF9900;
		font-size: 20rpx;
		padding: 2rpx 8rpx;
		border-radius: 4rpx;
		margin-left: 10rpx;
		display: inline-block;
		vertical-align: middle;
		margin-bottom: 5rpx;
	}

	.label-group .label+.vip-tag {
		margin-left: 10rpx;
	}

	/* 规则说明 */
	.rule-description {
		padding: 20rpx 30rpx;
		font-size: 26rpx;
		color: #999;
		line-height: 1.6;
		background-color: #f8f8f8;
	}

	/* 底部操作栏*/
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

.list {
    &__left {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      
      &__icon, &__image {
        margin-right: 18rpx;
      }
    }
    
    &__right {
      display: flex;
      align-items: center;
      justify-content: flex-end;
    }
  }
    .list-icon-text {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
</style>

