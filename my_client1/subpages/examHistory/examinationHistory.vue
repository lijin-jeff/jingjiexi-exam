<template>
  <view class="page tn-safe-area-inset-bottom">
    <view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<template #back>
			<view class="tn-custom-nav-bar__back">
			<text
				class="icon tn-icon-left tn-color-white"
				@click="goBack"
			/>
			<text
				class="icon tn-icon-home-capsule-fill tn-color-white tn-margin-left-sm"
				@click="goHome"
			/>
			</view>
		</template>
		<text class="tn-text-bold tn-text-xl tn-color-white">
			答题历史
		</text>
		</tn-nav-bar>
	</view>
    <view class="content-container" :style="{paddingTop: vuex_custom_bar_height + 'px'}">
      <!-- 固定在顶部的科目切换组件 -->
      <view class="subject-manager-fixed" :style="{top: vuex_custom_bar_height + 'px'}">
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
          @save="handleSubjectsSave"
          @add="handleSubjectsAdd"
          @remove="handleSubjectsRemove"
        />
      </view>
      <!-- 固定在顶部的选项卡容器 -->
      <view class="tabs-fixed-container" :style="{top: (vuex_custom_bar_height + 50) + 'px'}">
        <!-- TabsSwiper 选项卡-->
        <tn-tabs-swiper 
          :list="tabs" 
          :current="currentTabIndex"
          :active-color="mainColor"
          :inactive-color="'#666666'"
          count="count"
          :badge-offset="[24, 18]"
          @change="onTabChange"
        />
      </view>
			
      <!-- 内容区域 -->
      <swiper
        class="exam-swiper"
        :current="currentTabIndex"
        @change="onSwiperChange"
      >
        <swiper-item
          v-for="(tab, index) in tabs"
          :key="index"
        >
          <!-- 空状态-->
          <view
            v-if="getTabHistoryList(tab.value).length === 0 && !loading[tab.value]"
            class="empty-container"
          >
            <tn-empty
              mode="list"
              text="暂无该类型答题历史"
            />
          </view>
          <view v-else>
            <scroll-view 
              class="exam-ul" 
              scroll-y 
              :scroll-into-view="scrollIntoView[tab.value]"
              scroll-with-animation
              :enable-back-to-top="true"
              @scrolltolower="onScrollToLower(tab.value)"
            >
              <block
                v-for="(it, itemIndex) in getTabHistoryList(tab.value)"
                :key="itemIndex"
                :id="'examItem-' + tab.value + '-' + itemIndex"
              >
                <view class="tn-flex exam-li">
                  <view class="txt tn-flex tn-flex-direction-column">
                    <view class="fs-16 line2">
                      {{ it.title ? it.title : '无标题' }}
                    </view>
                    <view class="acea-row row-middle">
                      <view class="tn-color-gray">
                        <text class="tn-icon-time" />
                        <text class="ml-5">
                          {{ it.submit_time }}
                        </text>
                      </view>
                      <view class="tn-margin-left-sm">
                        <text
                          class="tn-icon-success-circle"
                          :style="{color: mainColor, paddingRight: '8rpx'}"
                        />
                        <text>{{ it.correct_count }}</text>
                        <text
                          class="tn-icon-close-circle tn-padding-left-sm tn-color-red"
                          :style="{paddingRight: '8rpx'}"
                        />
                        <text>{{ it.error_count }}</text>
                      </view>
                    </view>
                    <view class="acea-row row-middle">
                      <view class="tn-margin-left-sm">
						<text v-if="it.questions_type == 2 || it.questions_type == 7" style="display: inline-block;">
							<text class="tn-color-gray tn-icon-ticket" />
							<text class="ml-5">{{ it.paper_score || 0 }}</text>
							<text class="tn-icon-ticket" :style="{color: mainColor, paddingRight: '8rpx'}" />
							<text>{{ it.basic_score || 0 }}</text>
						</text>
						<text v-else style="display: inline-block;">
							<text class="tn-icon-ticket" :style="{color: mainColor, paddingRight: '8rpx'}" />
                        	<text>{{ it.user_integral || 0 }}</text>
						</text>
                        <text class="tn-icon-ticket tn-padding-left-sm tn-color-red" :style="{paddingRight: '8rpx'}" />
                        <text>{{ it.user_score || 0 }}</text>
                      </view>
                    </view>
                  </view>
                  <!-- 按钮容器 - 固定在右侧垂直居中 -->
                  <view class="btn-container tn-flex tn-flex-direction-column tn-flex-col-center">
                    <view
                      class="btn-item"
					  style="margin-bottom: 10rpx;"
                      @click="submitDetail(tab.value, itemIndex)"
                    >
                      <tn-button
                        :background-color="mainColor"
                        size="sm"
						padding="10rpx 20rpx"
                        shape="round"
                        font-color="tn-color-white"
                      >
                        答题结果
                      </tn-button>
                    </view>
                    <view
                      class="btn-item"
                      @click="showDesc(tab.value, itemIndex)"
                    >
                      <tn-button
                        background-color="tn-bg-blue"
                        size="sm"
						padding="10rpx 20rpx"
                        shape="round"
                        font-color="tn-color-white"
                      >
                        图表统计
                      </tn-button>
                    </view>
                  </view>
                </view>
              </block>
              <!-- 加载更多提示 -->
              <view class="load-more-container">
                <tn-load-more 
                  :status="loading[tab.value] ? 'loading' : (!hasMoreData[tab.value] ? 'nomore' : 'loadmore')"
                  loading-icon-color="#0E7DFF"
                  :load-text="{
                    loadmore: '上拉加载更多',
                    loading: '加载中...',
                    nomore: '没有更多了'
                  }"
                  font-color="#999999"
                />
              </view>
            </scroll-view>
          </view>
        </swiper-item>
      </swiper>
      <!-- 增加底部占位空间，确保加载更多组件能完整显示 -->

    </view>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import SubjectTabsManager from '@/components/subject-tabs-manager.vue'
	import ExamDataAdapter from '@/util/examDataAdapter.js'
export default {
	name: 'Exam',
	components: {SubjectTabsManager},
	mixins: [template_page_mixin],
	data() {
			return {
				mainColor: getApp().globalData.mainColor,
				currentTabIndex: 0,
				tabs: [],
				historyData: {}, // 按类型存储历史记录
				hasMoreData: {}, // 跟踪每个tab是否还有更多数据
				totalDataCount: {}, // 存储每个tab的总数据量
				queryParams: {
					page_no: 1,
					page_size: 10, // 分页大小
					keywords: '',
					questions_type: '', // 添加考试类型参数
					uid: '', // 题库ID
				},
				dictData: [], // 存储字典数据
				loading: {}, // 按类型存储加载状态
				pageNo: {}, // 按类型存储页码
				scrollIntoView: {}, // 用于控制滚动到指定元素
				// 科目管理
				showSubjectPopup: false, // 控制科目管理弹窗显示/隐藏
				loadingQuestionLib: false, // 加载状态
				questionLibList: [], // 全部科目列表
				myQuestionLibList: [], // 我的科目列表
				scrollList: [], // 科目切换标签列表
				currentSubjectIndex: 0, // 当前选中的科目索引
				selectedCategoryUid: uni.getStorageSync('selectedCategoryUid'), // 从本地存储中恢复分类ID
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
		// 获取题库ID
		this.queryParams.uid = option.uid || '';
		
		// 并行执行API调用，减少加载时间
		Promise.all([
			this.restoreUserSubjects(),
			this.fetchDictData()
		]).then(() => {
			console.log('页面初始化完成');
		}).catch(err => {
			console.error('页面初始化失败:', err);
		});
	},
	
	// 分享给朋友
	onShareAppMessage(res) {
		return {
			title: '我的答题历史',
			path: `/pages/index/index`
		}
	},
	
	// 分享到朋友圈
	onShareTimeline() {
		return {
			title: '我的答题历史',
			query: ``
		}
	},
		onShow() {
			// 每次显示页面时，从本地存储中恢复用户选择的科目
			this.restoreUserSubjects().then(() => {
				// 恢复科目后，重新加载当前标签页的数据
				if (this.tabs.length > 0) {
					const currentType = this.tabs[this.currentTabIndex].value;
					// 如果当前类型没有数据或数据为空，重新加载
					if (!this.historyData[currentType] || this.historyData[currentType].length === 0) {
						this.fetchData(currentType);
					}
				}
			});
		},
		onReachBottom() {
				// 此方法在 swiper 内部无效，已移除
			},
		methods: {
			// scroll-view 滚动到底部事件
			onScrollToLower(type) {
				// 只有在有更多数据且不在加载中的情况下才请求
				if (this.hasMoreData[type] && !this.loading[type]) {
					console.log('[Scroll] 加载下一页:', type);
					this.fetchData(type, true);
				}
			},
			// 监听swiper切换
			onSwiperChange(e) {
				const index = e.detail.current;
				this.currentTabIndex = index;
				const type = this.tabs[index].value;
				// 如果该类型还没有数据，则加载
				if (!this.historyData[type] || this.historyData[type].length === 0) {
					this.fetchData(type);
				}
			},
			// 获取字典数据
		fetchDictData() {
		// 返回Promise，以便在Promise.all中使用
		return new Promise((resolve, reject) => {
			this.$api.getDictData({ type: 'questions_type' }).then(res => {
				if (res.code === 1) {
					// 确保 res.data 是数组类型
					this.dictData = Array.isArray(res.data) ? res.data : [];
					// 设置选项卡，在最前面添加“全部”选项
					this.tabs = [
						{ name: '全部', value: 'all', count: 0 }
					].concat(this.dictData.map(item => ({
						name: item.name,
						value: item.value,
						count: 0
					})));
					// 初始化每个类型的数据容器和状态标记
							this.$set(this.historyData, 'all', []); // 初始化“全部”类型的数据容器
							this.$set(this.hasMoreData, 'all', true); // 初始化是否有更多数据的标记
							this.$set(this.totalDataCount, 'all', 0); // 初始化总数据量
							this.$set(this.loading, 'all', false); // 初始化加载状态
							this.$set(this.scrollIntoView, 'all', ''); // 初始化滚动位置
							this.dictData.forEach(item => {
								this.$set(this.historyData, item.value, []);
								this.$set(this.hasMoreData, item.value, true);
								this.$set(this.totalDataCount, item.value, 0);
								this.$set(this.loading, item.value, false); // 初始化加载状态
								this.$set(this.scrollIntoView, item.value, ''); // 初始化滚动位置
							});
					// 优化：懒加载策略 - 只加载当前激活的tab数据，避免并发请求
					if (this.tabs.length > 0) {
						// 只加载当前选中的tab（默认是第一个“全部”）
						const currentType = this.tabs[this.currentTabIndex].value;
						// 检查历史数据是否为空，避免重复加载
						if (!this.historyData[currentType] || this.historyData[currentType].length === 0) {
							this.fetchData(currentType);
						}
					}
					resolve();
				} else {
					// API调用失败，确保 tabs 仍然是数组
					if (!Array.isArray(this.tabs)) {
						this.tabs = [];
					}
					this.$func.showToast(res.msg || '获取题型列表失败');
					resolve();
				}
			}).catch(err => {
				// 网络错误，确保 tabs 仍然是数组
				if (!Array.isArray(this.tabs)) {
					this.tabs = [];
				}
				console.error('获取题型字典失败:', err);
				this.$func.showToast('网络错误，请重试');
				reject(err);
			})
		});
		},
			// 根据选项卡类型获取历史记录
		getTabHistoryList(type) {
			// 如果是"全部"类型，直接返回专门为"all"类型存储的数据
			// 不再合并其他标签的数据，以确保正确显示API返回的所有数据
			return this.historyData[type] || [];
		},
		// 选项卡切换事件
		onTabChange(index) {
			this.currentTabIndex = index;
			const type = this.tabs[index].value;
			// 如果该类型还没有数据，则加载
			if (!this.historyData[type] || this.historyData[type].length === 0) {
				this.fetchData(type);
			}
		},
		// 加载数据
			fetchData(type, isLoadMore = false) {
			// 如果已经在加载中，或者没有更多数据，则不重复加载
			if (this.loading[type] || !this.hasMoreData[type]) return Promise.resolve();
			
			// 防抖优化：如果不是加载更多，且已经有数据，则直接返回（避免重复请求）
			if (!isLoadMore && this.historyData[type] && this.historyData[type].length > 0) {
				return Promise.resolve();
			}
			
			// 创建并返回一个Promise，便于异步控制
			return new Promise((resolve, reject) => {
				// 快速失败，避免不必要的计算
				if (!this.tabs.length) {
					resolve();
					return;
				}
				
				this.$set(this.loading, type, true);
				
				// 为每个标签类型维护独立的页码
				if (!this.pageNo) {
					this.pageNo = {};
				}
				if (!this.pageNo[type]) {
					this.pageNo[type] = 1;
				}
				
				// 计算当前请求的页码
				// 对于加载更多，使用当前页码；对于重新加载，使用1
				const currentRequestPage = isLoadMore ? this.pageNo[type] : 1;
				
				// 创建独立的请求参数，避免并行请求时参数被覆盖
				const requestParams = {
					page_no: currentRequestPage,
					page_size: this.queryParams.page_size,
					keywords: this.queryParams.keywords,
					questions_type: type === 'all' ? '' : type,
					uid: this.queryParams.uid,
					//examination_uid: this.myQuestionLibList[this.currentSubjectIndex]?.id || 1, // 取当前选中科目的 id 或 uid 作为考试分类筛选条件
				};
				
				// 如果不是加载更多，则重置数据和页码
				if (!isLoadMore) {
					this.$set(this.historyData, type, []);
					this.$set(this.hasMoreData, type, true); // 重置是否有更多数据的标志
					this.pageNo[type] = 1; // 重置页码为1
				}
				
				// 只在第一次加载和用户主动触发时显示loading
				if (!isLoadMore && this.historyData[type].length === 0) {
					uni.showLoading({
						title: '加载中...',
						icon: 'none'
					})
				}
				
				this.$api.apiExaminationHistory(requestParams).then(res => {
					if (res.code === 1) {
						// 优化：利用extend字段一次性更新所有tab的count统计
						if (res.data.extend && Array.isArray(res.data.extend)) {
							// 计算全部类型的总数量
							let totalCount = 0;
							// 批量更新tabs和totalDataCount，减少Vue更新次数
							const tabsToUpdate = [...this.tabs];
							const totalDataCountToUpdate = {...this.totalDataCount};
							
							res.data.extend.forEach(item => {
								if (item.questions_type !== undefined && item.count !== undefined) {
									totalCount += item.count;
									// 更新对应题型的count
									const typeTabIndex = tabsToUpdate.findIndex(tab => tab.value == item.questions_type);
									if (typeTabIndex !== -1) {
										tabsToUpdate[typeTabIndex].count = item.count;
										totalDataCountToUpdate[item.questions_type] = item.count;
									}
								}
							});
							
							// 更新"全部"选项卡的count
							const allTabIndex = tabsToUpdate.findIndex(tab => tab.value === 'all');
							if (allTabIndex !== -1) {
								tabsToUpdate[allTabIndex].count = totalCount;
								totalDataCountToUpdate.all = totalCount;
							}
							
							// 批量更新，减少Vue更新次数
							this.tabs = tabsToUpdate;
							this.totalDataCount = totalDataCountToUpdate;
						}
						
						// 使用适配器处理历史数据（统一时间戳格式）
						const formattedLists = (res.data.lists || []).map(item => {
							return {
								...item,
								// 统一时间戳格式
								submit_time: this.formatSubmitTime(item.submit_time || item.create_time)
							}
						})
						
						// 添加到对应类型的数据列表中
						if (isLoadMore) {
							// 直接添加新数据，减少不必要的DOM操作
							this.historyData[type].push(...formattedLists);
						} else {
							this.$set(this.historyData, type, formattedLists);
						}
						
						// 判断是否还有更多数据
						const hasMore = res.data.lists && res.data.lists.length === this.queryParams.page_size;
						this.$set(this.hasMoreData, type, hasMore);
						
						// 因为页码是下一次请求时使用的
						this.pageNo[type] = currentRequestPage + 1;
					} else {
						this.$func.showToast(res.msg);
						// API调用失败，设置为没有更多数据
						this.$set(this.hasMoreData, type, false);
					}
					resolve(res);
				}).catch(error => {
					reject(error);
					// 请求失败，设置为没有更多数据
					this.$set(this.hasMoreData, type, false);
				}).finally(() => {
					this.$set(this.loading, type, false);
					uni.hideLoading();
				})
			});
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
					}
					// 优化：重置所有标签页的数据和状态
					this.tabs.forEach(tab => {
						this.$set(this.historyData, tab.value, []);
						this.$set(this.hasMoreData, tab.value, true);
						this.$set(this.totalDataCount, tab.value, 0);
						this.$set(this.loading, tab.value, false);
						this.pageNo[tab.value] = 1;
						// 重置选项卡的徽章数量
						const tabIndex = this.tabs.findIndex(t => t.value === tab.value);
						if (tabIndex !== -1) {
							this.$set(this.tabs[tabIndex], 'count', 0);
						}
					});
					// 优化：只加载当前激活的标签页数据，其他tab切换时再加载
					if (this.tabs.length > 0) {
						const currentType = this.tabs[this.currentTabIndex].value;
						this.fetchData(currentType);
					}
				},
				
				// 从本地存储中恢复用户选择的科目
		restoreUserSubjects() {
			// 返回Promise，以便在Promise.all中使用
			return new Promise((resolve, reject) => {
				try {
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
								// 优化：恢复科目后，重置数据状态但只加载当前tab
								if (this.tabs.length > 0) {
									// 重置所有标签页的数据
									this.tabs.forEach(tab => {
										this.$set(this.historyData, tab.value, []);
										this.$set(this.hasMoreData, tab.value, true);
										this.$set(this.totalDataCount, tab.value, 0);
										this.$set(this.loading, tab.value, false);
										this.pageNo[tab.value] = 1;
										// 重置选项卡的徽章数量
										const tabIndex = this.tabs.findIndex(t => t.value === tab.value);
										if (tabIndex !== -1) {
											this.$set(this.tabs[tabIndex], 'count', 0);
										}
									});
									// 优化：只重新加载当前激活的标签页数据
									// 但要确保fetchDictData不会重复调用
									// 因为fetchDictData已经在onLoad中被调用，会处理数据加载
									// this.fetchData(currentType);
								}
							}
							resolve();
						} else {
							// 如果本地没有保存的科目，加载默认科目列表
							this.loadQuestionLibList();
							resolve();
						}
					} else {
						// 如果没有选中的分类ID，加载默认科目列表
						this.loadQuestionLibList();
						resolve();
					}
				} catch (error) {
					console.error('恢复用户科目失败:', error);
					reject(error);
				}
			});
		},
				
				// 科目保存事件处理
				handleSubjectsSave(subjects) {
					this.saveSubjectsToStorage(subjects)
					this.myQuestionLibList = [...subjects]
					this.updateScrollList()
				},
				// 科目添加事件处理
				handleSubjectsAdd(subject, subjects) {
					this.saveSubjectsToStorage(subjects)
					this.myQuestionLibList = [...subjects]
					this.updateScrollList()
				},
				// 科目移除事件处理
				handleSubjectsRemove(index, subjects) {
					this.saveSubjectsToStorage(subjects)
					this.myQuestionLibList = [...subjects]
					this.updateScrollList()
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
						page_size: 10,
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
					
					// 确保currentSubjectIndex索引在有效范围内
					if (this.currentSubjectIndex >= this.scrollList.length) {
						this.currentSubjectIndex = Math.max(0, this.scrollList.length - 1);
						// 如果有科目，更新题库ID
						if (this.myQuestionLibList[this.currentSubjectIndex]) {
							this.queryParams.uid = this.myQuestionLibList[this.currentSubjectIndex].id || this.myQuestionLibList[this.currentSubjectIndex].uid;
						}
					}
				},
			// 跳转题库答题结果
			submitDetail(type, index) {
				const item = this.historyData[type][index];
				// 使用uid作为唯一标识符
				this.$func.navigatorTo('/subpages/examHistory/history/analysis?uid=' + item.uid + '&examination_uid=' + this.queryParams.uid);
			},
			// 答题分析
			showDesc(type, index) {
				const item = this.historyData[type][index];
				// 使用uid作为唯一标识符
				this.$func.navigatorTo('/subpages/examHistory/history/chart?uid=' + item.uid + '&examination_uid=' + this.queryParams.uid);
			},
			goBack() {
				uni.navigateBack()
			},
					
			// 格式化提交时间（使用数据适配器统一处理）
			formatSubmitTime(timestamp) {
				if (!timestamp) return '-'
						
				// 如果已经是格式化的日期字符串（包含 . 或 -），直接返回
				if (typeof timestamp === 'string' && (timestamp.includes('.') || timestamp.includes('-') || timestamp.includes('/'))) {
					return timestamp
				}
						
				try {
					// 使用适配器统一时间戳为 13 位
					const normalized = ExamDataAdapter.normalizeTimestamp(timestamp)
					const date = new Date(normalized)
							
					if (isNaN(date.getTime())) {
						console.warn('[答题历史] 无效的时间戳:', timestamp)
						return '-'
					}
							
					const year = date.getFullYear()
					const month = String(date.getMonth() + 1).padStart(2, '0')
					const day = String(date.getDate()).padStart(2, '0')
					const hour = String(date.getHours()).padStart(2, '0')
					const minute = String(date.getMinutes()).padStart(2, '0')
							
					return `${year}.${month}.${day} ${hour}:${minute}`
				} catch (error) {
					console.error('[答题历史] 时间格式化失败:', error)
					return '-'
				}
			},
		},
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";

	::v-deep .plaClass {
		color: #999;
		text-align: start;
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

.exam-swiper {
		/* 恢复固定高度，确保swiper能正确显示 */
		min-height: calc(100vh - 60rpx);
		/* 使用flex布局让内容自适应 */
		display: flex;
		overflow: hidden;
	}
	
	/* 确保swiper-item能正确显示内容 */
	.exam-swiper swiper-item {
		height: 100%;
		display: flex;
		flex-direction: column;
	}
	
	.subject-manager-fixed {
		/* 固定在顶部 */
		position: fixed;
		/* 确保宽度为100% */
		width: 100%;
		/* 背景色，避免内容透传 */
		background-color: #ffffff;
		/* 确保在内容之上 */
		z-index: 100;
	}

	.tabs-fixed-container {
		/* 固定在顶部 */
		position: fixed;
		/* 确保宽度为100% */
		width: 100%;
		/* 背景色，避免内容透传 */
		background-color: #ffffff;
		/* 确保在内容之上 */
		z-index: 90;
		/* 添加底部阴影，增强视觉效果 */
		box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);
	}
	
	/* 为内容区域添加padding-top，避免被固定选项卡遮挡 */
	.exam-swiper {
		/* 添加顶部padding，避免内容被固定选项卡和科目切换组件遮挡 */
		padding-top: 170rpx;
	}
	
	.exam-ul {
		/* 设置高度，预留足够空间显示加载更多 */
		height: calc(100vh - 260rpx);
		/* 移除不必要的overflow-y，scroll-view本身有滚动功能 */
		/* 避免滚动条影响布局 */
		padding: 10rpx;
		-webkit-overflow-scrolling: touch;
		
		.exam-li {
			background: #fff;
			border-radius: 10rpx;
			padding: 25rpx 15rpx;
			/* 减小最小高度 */
			min-height: 160rpx;
			position: relative;
			margin: 18rpx 0;
			/* 添加阴影效果 */
			box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

			.txt {
				/* 调整宽度，为右侧按钮留出空间 */
				width: calc(100% - 220rpx);
				justify-content: space-between;
			}

			/* 按钮容器样式 */
			.btn-container {
				/* 固定在右侧 */
				position: absolute;
				right: 0;
				/* 垂直居中 */
				top: 50%;
				transform: translateY(-50%);
				/* 固定宽度，确保按钮对齐 */
				width: 180rpx;
				/* 按钮间距 */
				gap: 10rpx;
			}

			/* 单个按钮样式 */
			.btn-item {
				/* 确保按钮居中显示 */
				text-align: center;
			}

		}
		
		/* 确保加载更多提示在底部显示 */
		.load-more-tip {
			margin-top: 10rpx;
			margin-bottom: 20rpx;
			text-align: center;
		}
		
		/* 加载更多容器样式 */
		.load-more-container {
			margin-top: 10rpx;
			margin-bottom: 20rpx;
			text-align: center;
			width: 100%;
			padding: 20rpx 0;
		}

	}

	.fs-16 {
		font-size: 30rpx;
	}

	/* 移除不再使用的样式 */
	.exam-list-btn {
		display: none;
	}
	
	/* 加载更多提示样式 */
	.load-more-tip {
		text-align: center;
		padding: 30rpx 0;
		font-size: 26rpx;
		color: #999;
	}
	
	/* 空状态容器 */
	.empty-container {
		min-height: 400rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}
</style>

