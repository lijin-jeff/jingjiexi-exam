<template>
  <view class="container">
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
		<view class="tn-flex tn-flex-col-center tn-flex-row-center">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			章节练习
			</text>
		</view>
		</tn-nav-bar>
	</view>

	<!-- 数据统计区域 -->
    <view  class="stats-section" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <view class="stats-grid">
        <!-- 我的作答题目 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value primary">
            {{ overallStats.total_answers }}/{{ overallStats.total_questions }}
          </view>
          <view class="stat-label">
            我的作答
          </view>
        </view>

        <!-- 我的错题 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value error">
          {{ overallStats.eliminated_errors }}/{{ overallStats.total_errors }}
          </view>
          <view class="stat-label">
            我的错题
          </view>
        </view>

        <!-- 我的正确率 -->
        <view class="stat-item" hover-class="stat-item-hover">
          <view class="stat-value success">
           {{ overallStats.overall_accuracy }}%
          </view>
          <view class="stat-label">
            我的正确率
          </view>
        </view>
      </view>
    </view>

    <!-- 列表 -->
    <scroll-view
      class="list-container"
      scroll-y
    >
      <!-- 加载状态 -->
      <div v-if="loading && isFirstLoad" class="loading-container">
        <tn-loading
          mode="spinner"
          text="正在加载章节数据..."
          color="#5B7FE8"
          size="large"
        />
      </div>
      
      <!-- 空状态 -->
      <div v-else-if="!loading && chapertList.length === 0" class="empty-container">
        <div class="empty-content">
          <tn-empty
            mode="list"
            text="暂无章节数据"
            icon-color="#5B7FE8"
            icon-size="80"
          />
          <text class="empty-tip">请检查题库设置或联系管理员</text>
        </div>
      </div>
      
      <!-- 章节列表 -->
      <view v-else>
        <view
                  v-for="(group, groupIndex) in chapertList"
                  :key="groupIndex"
                  class="group-item"
                  hover-class="group-item-hover"
                >
                  <view
                    class="group-header"
                    :class="{ 'expanded': group.expanded }"
                    hover-class="group-header-hover"
                  >
                    <view class="group-header-content">
                      <view
                        class="group-title-section"
                        @click="menuExpend(groupIndex)"
                      >
                        <text
                          :class="group.expanded ? 'tn-icon-reduce-circle-fill tn-text-xxl tn-color-blue' : 'tn-icon-add-fill tn-text-xxl tn-color-blue'"
                        />
                        <text class="group-title tn-margin-left-sm">
                          {{ group.title }}
                        </text>
                      </view>
                      <!-- 一级章节统计数据显示在标题下方 -->
                      <view class="group-stats-under-title">
                        <!-- 答题进度 -->
                        <view class="stats-item" hover-class="stats-item-hover">
                          <text class="stats-icon"></text>
                          <text class="stats-text">
                            {{ group.done_count || 0 }}/{{ group.total_count || group.exam_count || 0 }}
                          </text>
                        </view>
                        
                        <!-- 正确率 -->
                        <view class="stats-item" hover-class="stats-item-hover">
                          <text class="accuracy-text" :class="group.accuracyClass">
                            正确率 {{ group.formattedAccuracy }}%
                          </text>
                        </view>
                      </view>
                    </view>
                    <tn-button
                      :background-color="group.has_progress ? 'tn-bg-blue' : mainColor"
                      size="sm"
                      shape="round"
                      font-color="tn-color-white"
                      :class="group.has_progress ? 'continue-btn' : 'practice-btn'"
                      @click="questionWrite(group)"
                    >
                      {{ group.has_progress ? '继续' : '练习' }}
                    </tn-button>
                  </view>
          
          <!-- 子章节列表 -->
          <view
            v-if="group.expanded && group.children && group.children.length > 0"
            class="children-list"
          >
            <!-- 第二级章节 -->
            <view
              v-for="(item, itemIndex) in group.children"
              :key="itemIndex"
              class="list-item-vertical"
              hover-class="list-item-vertical-hover"
            >
              <div class="item-header">
                <view class="item-header-content">
                  <view
                    class="item-title-section"
                    :class="{ 'has-children': item.children && item.children.length > 0 }"
                    @click="toggleChildChapter(item)"
                  >
                    <text
                      v-if="item.children && item.children.length > 0"
                      :class="item.expanded ? 'tn-icon-reduce-circle tn-text-xl tn-color-blue' : 'tn-icon-add-circle tn-text-xl tn-color-blue'"
                    />
                    <text class="item-title tn-margin-left-sm">
                      {{ item.title }}
                    </text>
                  </view>
                  <view class="group-stats-under-title item-stats-under-title">
                    <!-- 答题进度 -->
                    <view class="stats-item" hover-class="stats-item-hover">
                      <text class="stats-icon"></text>
                      <text class="stats-text">
                      {{ Math.min(item.done_count || 0, item.total_count || item.exam_count || 0) }}/{{ item.total_count || item.exam_count || 0 }}
                    </text>
                    </view>
                    
                    <!-- 正确率 -->
                    <view class="stats-item" hover-class="stats-item-hover">
                      <text class="accuracy-text" :class="item.accuracyClass">
                        正确率 {{ item.formattedAccuracy }}%
                      </text>
                    </view>
                  </view>
                </view>
                <tn-button
                  :background-color="item.has_progress ? 'tn-bg-blue' : mainColor"
                  size="sm"
                  shape="round"
                  font-color="tn-color-white"
                  :class="item.has_progress ? 'continue-btn' : 'practice-btn'"
                  @click="questionWrite(item)"
                >
                  {{ item.has_progress ? '继续' : '练习' }}
                </tn-button>
              </div>
              
              <!-- 第三级章节（孙子章节） -->
              <view
                v-if="item.expanded && item.children && item.children.length > 0"
                class="grandchildren-container"
              >
                <view
                  v-for="(grandchild, grandchildIndex) in item.children"
                  :key="grandchildIndex"
                  class="grandchild-item-vertical"
                  hover-class="grandchild-item-vertical-hover"
                >
                  <div class="item-header">
                    <view class="item-header-content">
                      <view
                        class="item-title-section"
                        :class="{ 'has-children': grandchild.children && grandchild.children.length > 0 }"
                        @click="toggleChildChapter(grandchild)"
                      >
                        <text
                          v-if="grandchild.children && grandchild.children.length > 0"
                      	  :class="item.expanded ? 'tn-icon-reduce-circle tn-text-xl tn-color-blue' : 'tn-icon-add-circle tn-text-xl tn-color-blue'"
                        />
                        <text class="item-title grandchild-title">
                          {{ grandchild.title }}
                        </text>
                      </view>
                      <view class="group-stats-under-title item-stats-under-title">
                        <!-- 答题进度 -->
                        <view class="stats-item" hover-class="stats-item-hover">
                          <text class="stats-icon"></text>
                          <text class="stats-text">
                            {{ Math.min(grandchild.done_count || 0, grandchild.total_count || grandchild.exam_count || 0) }}/{{ grandchild.total_count || grandchild.exam_count || 0 }}
                          </text>
                        </view>
                        
                        <!-- 正确率 -->
                        <view class="stats-item" hover-class="stats-item-hover">
                          <text class="accuracy-text" :class="grandchild.accuracyClass">
                            正确率 {{ grandchild.formattedAccuracy }}%
                          </text>
                        </view>
                      </view>
                    </view>
                    <tn-button
                      :background-color="grandchild.has_progress ? 'tn-bg-blue' : mainColor"
                      size="sm"
                      shape="round"
                      font-color="tn-color-white"
                      :class="grandchild.has_progress ? 'continue-btn' : 'practice-btn'"
                      @click="questionWrite(grandchild)"
                    >
                      {{ grandchild.has_progress ? '继续' : '练习' }}
                    </tn-button>
                  </div>
                  
                  <!-- 第四级章节（曾孙子章节） -->
                  <view
                    v-if="grandchild.expanded && grandchild.children && grandchild.children.length > 0"
                    class="great-grandchildren-container"
                  >
                    <view
                      v-for="(greatGrandchild, greatGrandchildIndex) in grandchild.children"
                      :key="greatGrandchildIndex"
                      class="great-grandchild-item-vertical"
                      hover-class="great-grandchild-item-vertical-hover"
                    >
                      <div class="item-header">
                        <view class="item-header-content">
                          <view
                            class="item-title-section"
                            :class="{ 'has-children': greatGrandchild.children && greatGrandchild.children.length > 0 }"
                            @click="toggleChildChapter(greatGrandchild)"
                          >
                            <text
                              v-if="greatGrandchild.children && greatGrandchild.children.length > 0"
                              :class="item.expanded ? 'tn-icon-reduce-circle tn-text-xl tn-color-blue' : 'tn-icon-add-circle tn-text-xl tn-color-blue'"
                            />
                            <text class="item-title great-grandchild-title">
                              {{ greatGrandchild.title }}
                            </text>
                          </view>
                          <view class="group-stats-under-title item-stats-under-title">
                            <!-- 答题进度 -->
                            <view class="stats-item" hover-class="stats-item-hover">
                              <text class="stats-icon"></text>
                              <text class="stats-text">
                                {{ Math.min(greatGrandchild.done_count || 0, greatGrandchild.total_count || greatGrandchild.exam_count || 0) }}/{{ greatGrandchild.total_count || greatGrandchild.exam_count || 0 }}
                              </text>
                            </view>
                            
                            <!-- 正确率 -->
                            <view class="stats-item" hover-class="stats-item-hover">
                              <text class="accuracy-text" :class="greatGrandchild.accuracyClass">
                                正确率 {{ greatGrandchild.formattedAccuracy }}%
                              </text>
                            </view>
                          </view>
                        </view>
                        <tn-button
                          :background-color="greatGrandchild.has_progress ? 'tn-bg-blue' : mainColor"
                          size="sm"
                          shape="round"
                          font-color="tn-color-white"
                          :class="greatGrandchild.has_progress ? 'continue-btn' : 'practice-btn'"
                          @click="questionWrite(greatGrandchild)"
                        >
                          {{ greatGrandchild.has_progress ? '继续' : '练习' }}
                        </tn-button>
                      </div>
                    </view>
                  </view>
                </view>
              </view>
            </view>
          </view>
          
          <!-- 空状态 -->
          <view
            v-if="group.expanded && group.children && group.children.length === 0"
            class="empty-children"
          >
            <text class="empty-text">
              暂无子章节
            </text>
          </view>
        </view>
      </view>
    </scroll-view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import { getUserInfo } from '@/util/userStore.js'
	import { getChapterProgressMap, getExamProgress, clearExamProgress } from '@/util/examProgressManager.js'
	import ExamDataAdapter from '@/util/examDataAdapter.js'
	export default {
		name: 'QuestionChapter',
		mixins: [template_page_mixin],
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				uid: '', // 题库uid
				chapertList: [],
				loading: false, // 加载状态
				isFirstLoad: true, // 是否首次加载
				questions_type: 1, // 做题类型1-章节练习
				// 整体统计数据
				overallStats: {
					eliminated_errors: 0,
					total_questions: 0,
					total_errors: 0,
					overall_accuracy: 0,
					total_answers: 0,
				}
			};
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
			this.uid = option.uid || ''
			this.fetchQuestionChapter()
		},
		methods: {
			questionWrite(row) {
				// 修复：区分“练习”和“继续”
				if (row.has_progress) {
					// 有进度：直接跳转到做题页面，恢复进度
					this.continueChapterPractice(row)
				} else {
					// 没有进度：跳转到设置页面
					this.$func.navigatorTo('/subpages/exam/questionSetting?uid=' + this.uid + '&chapter_uid=' + row.uid + '&paper_uid=' + this.uid + '&question_count=' + row.total_count + '&questions_type=' + this.questions_type)
				}
			},
					
			// 继续章节练习：直接跳转到做题页面并恢复进度
			async continueChapterPractice(chapter) {
				try {
					// 获取用户信息
					const userInfo = await getUserInfo()
					const userId = userInfo?.id || userInfo?.uid || ''
							
					// 从缓存中读取进度
					const progress = getExamProgress(parseInt(this.questions_type), chapter.uid, userId)
							
					if (!progress || !progress.questions || progress.questions.length === 0) {
						uni.showToast({
							title: '进度数据异常，请重新开始',
							icon: 'none'
						})
						// 清除无效缓存
						clearExamProgress(parseInt(this.questions_type), chapter.uid, userId)
						// 跳转到设置页面
						this.$func.navigatorTo('/subpages/exam/questionSetting?uid=' + this.uid + '&chapter_uid=' + chapter.uid + '&paper_uid=' + this.uid + '&question_count=' + chapter.total_count + '&questions_type=' + this.questions_type)
						return
					}
														
					// 将进度数据保存到全局状态，供 commonQuestion.vue 使用
					getApp().globalData.savedProgress = progress
					getApp().globalData.shouldRestoreProgress = true
							
					// 设置 examSettings，确保 questionParams 正确
					getApp().globalData.currentExamSettings = progress.questionParams || {
						uid: this.uid,
						chapter_uid: chapter.uid,
						paper_uid: this.uid,
						question_count: chapter.total_count,
						questions_type: this.questions_type,
						mode: progress.currentMode || 'learnPractice'
					}
										
					// questionSetting.vue 会检测到 restore 参数，直接显示 commonQuestion 组件并恢复进度
					this.$func.navigatorTo(
						'/subpages/exam/questionSetting?uid=' + this.uid + 
						'&chapter_uid=' + chapter.uid + 
						'&paper_uid=' + this.uid + 
						'&question_count=' + chapter.total_count + 
						'&questions_type=' + this.questions_type +
						'&restore=true'
					)
				} catch (error) {
					console.error('[章节练习] 继续练习失败:', error)
					uni.showToast({
						title: '加载进度失败',
						icon: 'none'
					})
				}
			},
			// 展开/折叠子章节
			menuExpend(index) {
				// 确保expanded属性存在
				if (typeof this.chapertList[index].expanded === 'undefined') {
					this.$set(this.chapertList[index], 'expanded', true)
				} else {
					this.$set(this.chapertList[index], 'expanded', !this.chapertList[index].expanded)
				}
			},
			// 切换子章节展开/折叠状态
		toggleChildChapter(item) {
			if (item.children && item.children.length > 0) {
				// 确保expanded属性存在
				if (typeof item.expanded === 'undefined') {
					this.$set(item, 'expanded', true)
				} else {
					this.$set(item, 'expanded', !item.expanded)
				}
			}
		},
			// 下拉刷新开始
			onRefresh() {
				this.refreshing = true
				this.fetchQuestionChapter().finally(() => {
					this.refreshing = false
				})
			},
			// 下拉刷新恢复
			onRefresherRestore() {
				// 可以添加一些恢复状态的逻辑
			},
			// 下拉刷新被中断
			onRefresherAbort() {
				// 可以添加一些中断处理的逻辑
			},
			// 递归初始化所有层级的expanded属性
			initializeExpanded(data) {
				if (!data || !Array.isArray(data)) {
					console.error('[章节练习] initializeExpanded 参数错误:', data);
					return [];
				}
				
				return data.map(item => {
					const newItem = {
						...item,
						expanded: false
					}
					if (item.children && item.children.length > 0) {
						newItem.children = this.initializeExpanded(item.children)
					}
					return newItem
				});
			},
			async fetchQuestionChapter() {
				// 避免重复加载
				if (this.loading) {
					return;
				}
				this.loading = true
				try {
					console.log('[章节练习] 开始加载章节列表, uid:', this.uid);
								
					// 并行获取用户信息和章节列表，提高加载速度
					const [userInfo, res] = await Promise.all([
						getUserInfo(),
						this.$api.apiQuestionChapterList({uid: this.uid})
					]);
								
					if (res.code === 1) {
						if (!res.data || !Array.isArray(res.data)) {
							console.warn('[章节练习] 返回数据格式异常:', res.data);
							this.chapertList = [];
							this.$func.showToast('数据格式异常');
							this.loading = false;
							this.isFirstLoad = false;
							return res;
						}
									
						// 递归为每个分组添加expanded属性和样式类
						let chapertList = this.initializeExpanded(res.data);
													
						// 为每个章节添加格式化后的统计数据和样式类
						this.addFormattedStats(chapertList);
									
						// 获取用户ID用于进度检查
						const userId = userInfo?.id || userInfo?.uid || '';
									
						// 检查每个章节的进度状态
						const progressMap = getChapterProgressMap(this.questions_type, this.flattenChapters(chapertList), userId);
									
						// 递归添加has_progress属性
						chapertList = this.markChapterProgress(chapertList, progressMap);
													
						// 关键修复：先设置loading为false，再赋值数据
						this.loading = false;
						this.isFirstLoad = false;
													
						// 赋值数据
						this.chapertList = chapertList;
																
						// 异步加载整体统计数据，不阻塞主流程
						this.fetchLibraryStats();
					} else {
						console.error('[章节练习] API返回错误:', res.msg);
						this.$func.showToast(res.msg || '加载失败');
						// API错误时也要关闭loading
						this.loading = false;
						this.isFirstLoad = false;
					}
					return res;
				} catch (err) {
					console.error('[章节练习] 加载失败:', err);
					this.$func.showToast('加载失败，请重试');
					// 错误时也要关闭loading
					this.loading = false;
					this.isFirstLoad = false;
					throw err;
				} finally {
					// finally块中不再重复设置（已在成功分支中设置）
					console.log('[章节练习] loading状态:', this.loading, '章节数量:', this.chapertList.length);
				}
			},
						
			// 获取题库整体统计数据
			async fetchLibraryStats() {
				try {
					console.log('[章节练习] 开始加载整体统计数据, uid:', this.uid);
								
					const res = await this.$api.apiQuestionLibraryStats({uid: this.uid});
								
					if (res.code === 1 && res.data) {
						this.overallStats = {
							eliminated_errors: res.data.eliminated_errors || 0,
							total_answers: res.data.total_answers || 0,
							total_questions: res.data.total_questions || 0,
							total_errors: res.data.total_errors || 0,
							overall_accuracy: res.data.overall_accuracy || 0
						};
						console.log('[章节练习] 整体统计加载成功:', this.overallStats);
					} else {
						console.warn('[章节练习] 统计数据加载失败:', res.msg);
					}
				} catch (err) {
					console.error('[章节练习] 统计数据加载失败:', err);
					// 统计数据加载失败不影响主流程，不抛出错误
				}
			},
			
			// 将嵌套的章节数组展平为一维数组
			flattenChapters(chapters) {
				const result = [];
				
				const flatten = (items) => {
					items.forEach(item => {
						result.push(item);
						if (item.children && item.children.length > 0) {
							flatten(item.children);
						}
					});
				};
				
				flatten(chapters);
				return result;
			},
			
			// 递归标记章节进度
			markChapterProgress(chapters, progressMap) {
				if (!chapters || !Array.isArray(chapters)) {
					console.error('[章节练习] markChapterProgress 参数错误:', chapters);
					return [];
				}
				
				return chapters.map(chapter => {
					const chapterUid = chapter.uid || chapter.id;
					const newChapter = {
						...chapter,
						has_progress: progressMap[chapterUid] || false
					};
					
					if (chapter.children && chapter.children.length > 0) {
						newChapter.children = this.markChapterProgress(chapter.children, progressMap);
					}
					
					return newChapter;
				});
			},
			
			/**
			 * 递归为所有章节添加格式化后的统计数据和样式类
			 * 解决UniApp在:class中不能调用方法的问题
			 */
			addFormattedStats(chapters) {
				if (!chapters || !Array.isArray(chapters)) {
					return;
				}
				
				chapters.forEach(chapter => {
					// 格式化正确率
					chapter.formattedAccuracy = this.formatAccuracy(chapter.accuracy || 0);
					
					// 计算正确率样式类
					chapter.accuracyClass = this.getAccuracyClass(chapter.accuracy || 0);
					
					// 递归处理子章节
					if (chapter.children && chapter.children.length > 0) {
						this.addFormattedStats(chapter.children);
					}
				});
			},
			
			/**
			 * 格式化正确率显示（使用数据适配器统一处理）
			 * @param {Number|String} accuracy - 正确率值
			 * @returns {String} 格式化后的正确率
			 */
			formatAccuracy(accuracy) {
				try {
					if (accuracy === null || accuracy === undefined || accuracy === '') {
						return '0.0';
					}
							
					// 使用适配器的类型安全处理
					const stats = ExamDataAdapter.formatStatistics({
						accuracy_rate: accuracy
					})
							
					// 保留一位小数
					return stats.accuracy_rate.toFixed(1);
				} catch (e) {
					console.error('formatAccuracy error:', e, accuracy);
					return '0.0';
				}
			},
			
			/**
			 * 根据正确率获取样式类（使用适配器统一处理）
			 * @param {Number|String} accuracy - 正确率值
			 * @returns {String} CSS类名
			 */
			getAccuracyClass(accuracy) {
				try {
					// 使用适配器的类型安全处理
					const stats = ExamDataAdapter.formatStatistics({
						accuracy_rate: accuracy
					})
					const numAccuracy = stats.accuracy_rate
							
					if (numAccuracy === 0) {
						return 'accuracy-none'; // 未答题
					} else if (numAccuracy < 60) {
						return 'accuracy-low'; // 低于60%
					} else if (numAccuracy < 80) {
						return 'accuracy-medium'; // 60-80%
					} else {
						return 'accuracy-high'; // 80%以上
					}
				} catch (e) {
					console.error('getAccuracyClass error:', e, accuracy);
					return 'accuracy-none';
				}
			}
		}
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	.container {
		background-color: #f5f7fa;
		min-height: 100vh;
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
		font-size: 28rpx;
		color: #333333;
	}

	/* 列表容器 */
	.list-container {
		padding: 20rpx;
		box-sizing: border-box;
		min-height: calc(100vh - var(--custom-bar-height, 0px));
		-webkit-overflow-scrolling: touch;
	}

	/* 加载状态 */
	.loading-container {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 120rpx 0;
		background-color: #ffffff;
		margin: 20rpx;
		border-radius: 16rpx;
		box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.06);
	}

	/* 空状态 */
	.empty-container {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 100rpx 20rpx;
		background-color: #ffffff;
		margin: 20rpx;
		border-radius: 16rpx;
		box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.06);
	}

	.empty-content {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 16rpx;
	}

	.empty-tip {
		font-size: 24rpx;
		color: #999999;
		text-align: center;
		line-height: 36rpx;
	}

	/* 分组项*/
	.group-item {
		margin-bottom: 20rpx;
		border-radius: 16rpx;
		overflow: hidden;
		background: #ffffff;
		box-shadow: 0 4rpx 16rpx rgba(0, 0, 0, 0.08);
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

		&:hover {
			transform: translateY(-2rpx);
			box-shadow: 0 6rpx 20rpx rgba(0, 0, 0, 0.12);
		}

		&.group-item-hover {
			transform: translateY(0);
			box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.06);
		}
	}

	/* 分组头部 */
	.group-header {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
		gap: 20rpx;
		background-color: #ffffff;
		padding: 16rpx 20rpx;
		cursor: pointer;
		transition: all 0.3s ease;

		&.group-header-hover {
			background-color: #fafafa;
		}

		&.expanded {
			border-bottom-left-radius: 0;
			border-bottom-right-radius: 0;
			box-shadow: none;
		}
	}

	/* 分组头部内容区 - 包含标题和统计 */
	.group-header-content {
		display: flex;
		flex-direction: column;
		flex: 1;
		gap: 8rpx;
		padding: 2rpx 0;
		min-width: 0;
	}

	/* 分组标题区域 */
	.group-title-section {
		display: flex;
		align-items: center;
		flex: 1;
	}
	
	/* 所有层级的标题区域都垂直居中 */
	.item-title-section,
	.grandchild-title-section,
	.great-grandchild-title-section {
		display: flex;
		align-items: center;
		flex: 1;
	}
	
	/* 子章节头部容器（二级、三级、四级通用） */
	.item-header {
		display: flex;
		flex-direction: row;
		align-items: center;
		justify-content: space-between;
		gap: 20rpx;
		background-color: #ffffff;
		padding: 16rpx 20rpx;
	}
	
	/* 子章节内容区 - 包含标题和统计 */
	.item-header-content {
		display: flex;
		flex-direction: column;  /* 垂直布局，包含标题和统计 */
		flex: 1;  /* 占据剩余空间 */
		gap: 8rpx;
	}

	/* 箭头图标 */
	.arrow-icon {
		color: #909399;
		margin-right: 16rpx;
		width: 32rpx;
		height: 32rpx;
		text-align: center;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		font-size: 32rpx;
		transform-origin: center;

		&.tn-icon-up-triangle {
			transform: rotate(180deg);
		}
	}

	.group-header.expanded .arrow-icon {
		color: $view-theme;
		transform: rotate(0deg);
	}

	/* 分组标题 */
	.group-title {
		color: #303133;
		font-size: 32rpx;
		line-height: 44rpx;
		flex: 1;
	}

	/* 一级章节标题下方的统计数据 */
	.group-stats-under-title {
		display: flex;
		align-items: center;
		gap: 24rpx;
		width: 100%;
		flex-wrap: wrap;
	}
	
	/* 统计项 */
	.stats-item {
		display: flex;
		align-items: center;
		gap: 6rpx;
		padding: 8rpx 16rpx;
		background: linear-gradient(135deg, #f5f7fa 0%, #fafbfc 100%);
		border-radius: 20rpx;
		transition: all 0.3s ease;
		
		&.stats-item-hover {
			transform: scale(0.98);
			opacity: 0.9;
		}
	}
	
	/* 统计图标 */
	.stats-icon {
		font-size: 24rpx;
		line-height: 1;
	}

	/* 统计数据 */
	.group-stats {
		display: flex;
		align-items: center;
		gap: 20rpx;
	}

	/* 已做/总题数 */
	.stats-text {
		color: #606266;
		font-size: 24rpx;
		line-height: 1.4;
		font-weight: 500;
	}

	/* 正确率 */
	.accuracy-text {
		font-size: 24rpx;
		line-height: 1.4;
		font-weight: 600;
		transition: color 0.3s ease;
		
		/* 未答题 */
		&.accuracy-none {
			color: #909399;
		}
		
		/* 低于60% */
		&.accuracy-low {
			color: #F56C6C;
		}
		
		/* 60-80% */
		&.accuracy-medium {
			color: #E6A23C;
		}
		
		/* 80%以上 */
		&.accuracy-high {
			color: #67C23A;
		}
	}

	/* 继续按钮 */
	::v-deep.continue-btn {
		background: linear-gradient(135deg, #409eff 0%, #66b1ff 100%);
		color: #ffffff;
	}

	/* 子章节列表 */
	.children-list {
		background-color: #fafafa;
		border-top: 1rpx solid #f0f0f0;
		border-bottom-left-radius: 12rpx;
		border-bottom-right-radius: 12rpx;
		overflow: hidden;
	}

	/* 垂直布局列表项 - 二级章节 */
	.list-item-vertical {
		display: flex;
		flex-direction: column;
		background-color: #fafafa;
		transition: all 0.3s ease;
		margin: 4rpx 0;

		&.list-item-vertical-hover {
			background-color: #f0f0f0;
		}

		&:not(:last-child) {
			border-bottom: 1rpx solid #f0f0f0;
		}
	}

	/* 垂直布局列表项 - 三级章节 */
	.grandchild-item-vertical {
		display: flex;
		background-color:#ffffff;
		flex-direction: column;
		padding-left: 40rpx;

		&.grandchild-item-vertical-hover {
			background-color: #e8e8e8;
		}

		&:last-child {
			border-bottom: none;
		}
	}

	/* 垂直布局列表项 - 四级章节 */
	.great-grandchild-item-vertical {
		display: flex;
		background-color:#ffffff;
		flex-direction: column;
		padding-left: 60rpx;

		&.great-grandchild-item-vertical-hover {
			background-color: #e0e0e0;
		}

		&:last-child {
			border-bottom: none;
		}
	}



	/* 子项箭头 */
	.child-arrow {
		color: #c0c4cc;
		margin-right: 16rpx;
		width: 32rpx;
		height: 32rpx;
		text-align: center;
		font-size: 32rpx;
		transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
		transform-origin: center;
	}

	/* 子项标题 */
	.item-title {
		color: #606266;
		font-size: 30rpx;
		line-height: 42rpx;
		flex: 1;
	}

	/* 孙子章节标题 */
	.grandchild-title {
		font-size: 28rpx;
		color: #909399;
		font-weight: 400;
		line-height: 40rpx;
	}

	/* 曾孙子章节标题 */
	.great-grandchild-title {
		font-size: 26rpx;
		color: #909399;
		font-weight: 400;
		line-height: 38rpx;
	}

	/* 二级、三级和四级章节的统计数据 - 统一与一级章节样式 */
	.item-stats-under-title {
		display: flex;
		align-items: center;
		gap: 20rpx;
		width: 100%;
	}

	/* 空提示 */
	.empty-children {
		padding: 32rpx 30rpx;
		background-color: #fafafa;
		border-top: 1rpx solid #f0f0f0;
		border-bottom-left-radius: 16rpx;
		border-bottom-right-radius: 16rpx;
	}

	.empty-text {
		color: #909399;
		font-size: 28rpx;
		text-align: center;
		line-height: 40rpx;
	}

	/* 有子章节的样式 */
	.item-header.has-children {
		cursor: pointer;
	}

	/* 孙子章节容器 */
	.grandchildren-container {
		background-color: transparent;
		overflow: hidden;
	}

	/* 曾孙子章节容器 */
	.great-grandchildren-container {
		background-color: transparent;
		overflow: hidden;
	}
	/* 数据统计区域 start */
  .stats-section {
    .stats-grid {
      display: flex;
      justify-content: space-between;
      margin: 20rpx;
      padding: 0;
      gap: 20rpx;
      
      .stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16rpx;
        padding: 24rpx 20rpx;
        background: linear-gradient(135deg, #F5F7FA 0%, #E4ECF7 100%);
        border-radius: 16rpx;
        box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: 1rpx solid rgba(255, 255, 255, 0.8);
        
        &:hover,
        &.stat-item-hover {
          transform: translateY(-4rpx);
          box-shadow: 0 8rpx 24rpx rgba(91, 127, 232, 0.15);
          background: linear-gradient(135deg, #E4ECF7 0%, #D1E0FA 100%);
        }
        
        .stat-value {
          font-size: 32rpx;
          font-weight: bold;
          letter-spacing: 2rpx;
          
          &.primary {
            color: #5B7FE8;
          }
          
          &.success {
            color: #52C988;
          }
          
          &.error {
            color: #FF6B81;
          }
        }
        
        .stat-label {
          font-size: 24rpx;
          color: #666666;
          letter-spacing: 1rpx;
          font-weight: 500;
        }
      }
    }
}
	/* 数据统计区域 end */
</style>