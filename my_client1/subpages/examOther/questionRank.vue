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
			刷题排行榜
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <!-- 顶部 Banner -->
    <view
      class="banner"
      :style="{paddingTop: vuex_custom_bar_height + 'px'}"
    >
      <!-- 波浪背景 -->
      <view class="tnwave waveAnimation">
        <view class="waveWrapperInner bgTop">
          <view
            class="wave waveTop"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-2.png')"
          />
        </view>
        <view class="waveWrapperInner bgMiddle">
          <view
            class="wave waveMiddle"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-2.png')"
          />
        </view>
        <view class="waveWrapperInner bgBottom">
          <view
            class="wave waveBottom"
            style="background-image: url('https://datiqiniu.allpp.cn/static/wave-1.png')"
          />
        </view>
      </view>
      
      <view class="banner-content">
        <view
          class="tag"
          style="width: 360rpx;"
        >
          #每天更新前一天答题排行榜#
        </view>
        <view
          class="tag gift-tag"
          style="width: 440rpx;"
        >
          <text class="gift-icon">
            
          </text> #排名前几名可获得对应积分奖励#
        </view>

      <view  class="tag" style="width: 300rpx;" @click="showRankSettingsDesc = true;">
          刷题排行榜说明
        </view>
      </view>
    </view>
    <!-- Tabs -->
    <view class="tn-flex tn-flex-row-center tn-margin">
      <view v-if="rankCurrentTab === 0" class="tabs">
        <view
          :class="currentTab === 0 ? ' tab active' : 'tab'"
          @click="scoreTab(0)"
        >
          昨日榜单
        </view>
      </view>
	  <view v-if="rankCurrentTab === 1" class="tabs">
        <view
          :class="currentTab === 0 ? ' tab active' : 'tab'"
          @click="scoreTab(0)"
        >
          本周榜单
        </view>
        <view
          :class="currentTab === 1 ? ' tab active  tn-margin-left' : 'tab tn-margin-left'"
          @click="scoreTab(1)"
        >
          上周榜单
        </view>
      </view>
	  <view v-if="rankCurrentTab === 2" class="tabs">
        <view
          :class="currentTab === 0 ? ' tab active' : 'tab'"
          @click="scoreTab(0)"
        >
          本月榜单
        </view>
        <view
          :class="currentTab === 1 ? ' tab active  tn-margin-left' : 'tab tn-margin-left'"
          @click="scoreTab(1)"
        >
          上月榜单
        </view>
      </view>
	  <view v-if="rankCurrentTab === 3" class="tabs">
        <view
          :class="currentTab === 0 ? ' tab active' : 'tab'"
          @click="scoreTab(0)"
        >
          总榜单
        </view>
      </view>
    </view>

    <!-- 我的排名 -->
    <view class="my-score-card">
      <text class="my-score-label">
        我的排名
      </text>
      <image
        class="avatar"
        :src="myInfo.avatar"
        mode="aspectFill"
      />
      <text class="name">
        {{ myInfo.name }}
      </text>
      <text class="rank-status">
        {{ myInfo.rankStatus }}
      </text>
      <text class="score">
        {{ myInfo.score }}
      </text>
    </view>

    <!-- 排行榜列表-->
    <view class="leaderboard">
      <!-- 空数据提示 -->
      <view v-if="leaderboardData.length === 0" class="empty-data">
		 <tn-empty mode="data" text="暂无排行榜数据"></tn-empty>
      </view>
      
      <!-- 有数据时显示列表 -->
      <template v-else>
        <!-- 列表头部 -->
        <view class="list-header">
          <text class="header-rank">
            排名
          </text>
          <text class="header-name">
            姓名
          </text>
          <text class="header-score">
            {{ getRankDimensionLabel() }}
          </text>
        </view>
        <!-- 列表项-->
        <view
          v-for="(item, index) in leaderboardData"
          :key="index"
          class="list-item"
        >
          <view class="rank-cell">
            <image
              v-if="item.ranking <= 3"
              :src="getMedalIcon(item.ranking - 1)"
              class="medal-icon"
              mode="widthFix"
            />
            <text
              v-else
              class="rank-number"
            >
              {{ item.ranking }}
            </text>
          </view>
          <view class="name-cell">
            <image
              class="avatar item-avatar"
              :src="getAvatar(item)"
              mode="aspectFill"
            />
            <text class="name">
              {{ getNickname(item) }}
            </text>
          </view>
          <view
            class="points-cell"
            :class="{ 'highlight-points': item.ranking <= 3 }"
          >
            {{ getRewardPoints(item.ranking) }}
          </view>
          <text class="score-cell">
            {{ getRankDimensionValue(item) }}
          </text>
        </view>
        <view class="tn-padding-bottom-xl" />
      </template>
    </view>
    <!-- 排行榜类型按钮 -->
    <tn-fab 
      v-if="showRankType" 
      :bgColor="mainColor"
      :bottom="120" 
      :right="20" 
      :show-mask="false" 
      :btn-list="RankTypeBtnList" 
      @click="handleFabBtnClick"
      ref="fabRef"
    >
      <!-- 自定义主按钮 -->
      <!-- <view 
        class="custom-fab-btn " 
        :style="{backgroundColor: mainColor , width: '37px', height: '37px', borderRadius: '50%', display: 'flex',
  alignItems: 'center',
  justifyContent: 'center;'}"
      >
        <text class="tn-icon-up"></text>
      </view> -->
    </tn-fab>

  <!-- 排行榜说明模态框 -->
  <tn-modal
    v-model="showRankSettingsDesc"
    :custom="true"
    :mask-closeable="true"
    :showCloseBtn="true"
    :width="'90%'"
    :radius="16"
    :background-color="'#ffffff'"
    @click="handleModalClick"
    @touchmove.stop.prevent
  >
    <!-- 标题区域 -->
    <view class="tn-text-center tn-padding-xs" :style="{ backgroundColor: mainColor, borderTopLeftRadius: '16rpx', borderTopRightRadius: '16rpx' }">
      <text class="tn-text-bold tn-text-md tn-color-white">排行榜说明</text>
    </view>
    
    <!-- 内容区域 -->
    <view class="tn-padding-sm tn-max-h-[500rpx] tn-overflow-y-auto">
      <!-- 结算时间规则 -->
      <view class="tn-margin-bottom">
        <view class="tn-flex tn-flex-col-center tn-margin-bottom-sm">
          <text class="tn-margin-right-xs tn-text-lg">⏰</text>
          <text class="tn-text-bold tn-text-lg" :style="{ color: mainColor }">结算时间规则</text>
        </view>
        <view class="tn-margin-left-lg">
          <view v-for="(item, index) in RankSettings.rankTime ? RankSettings.rankTime.split('\n') : []" :key="index">
            <text class="tn-text-md tn-color-gray ">{{ item }}</text>
          </view>
        </view>
      </view>
      
      <!-- 积分奖励规则 -->
      <view class="tn-margin-bottom">
        <view class="tn-flex tn-flex-col-center tn-margin-bottom-sm">
          <text class="tn-margin-right-xs tn-text-lg">🏆</text>
          <text class="tn-text-bold tn-text-lg" :style="{ color: mainColor }">积分奖励规则</text>
        </view>
        <view class="tn-margin-left-lg">
          <view  v-for="(item, index) in RankSettings.rankScore ? RankSettings.rankScore.split('\n') : []" :key="index">
            <text class="tn-text-md" :class="{
              'tn-text-bold tn-color-orange': item.includes('第1名'),
              'tn-text-bold tn-color-green': item.includes('第2名'),
              'tn-text-bold tn-color-blue': item.includes('第3名'),
              'tn-color-gray': !item.includes('第1名') && !item.includes('第2名') && !item.includes('第3名')
            }">{{ item }}</text>
          </view>
        </view>
      </view>
      
      <!-- 其他说明 -->
      <view v-if="RankSettings.desc" class="tn-margin-bottom">
        <view class="tn-flex tn-flex-col-center tn-margin-bottom-sm">
          <text class="tn-margin-right-xs tn-text-lg">ℹ️</text>
          <text class="tn-text-bold tn-text-lg" :style="{ color: mainColor }">其他说明</text>
        </view>
        <view class="tn-margin-left-lg">
          <text class="tn-text-md tn-color-gray ">{{ RankSettings.desc }}</text>
        </view>
      </view>
    </view>
    
    <!-- 底部按钮 -->
    <view class="tn-padding-sm tn-flex tn-flex-row-center">
      <tn-button 
        :background-color="mainColor" 
        :size="'large'" 
        :radius="'30rpx'" 
        :fontColor="'#ffffff'" 
        @click="showRankSettingsDesc = false"
      >
        我知道了
      </tn-button>
    </view>
  </tn-modal>
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	
	import { getUserInfo } from '@/util/userStore.js'
	export default {
		name: 'QuestionRank',
		mixins: [template_page_mixin],
		components: {
	},
		data() {
			return {
				mainColor: getApp().globalData.mainColor,
				currentTab: 0,
				rankCurrentTab: 0,
				showRankType: true,
				showRankSettingsDesc: false,
				RankSettings: {},
				rankingData: {
					day: [],
					week: [],
					month: [],
					total: []
				},
				myInfo: {
					name: '用户',
					avatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
					rankStatus: '暂未排名',
					score: 0
				},
				leaderboardData: [],
				defaultAvatar: 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png',
				// 使用 userStore 统一管理的用户信息
				cachedUserInfo: null
			};
			
		},
		computed: {
			// 动态生成排行榜类型按钮配置
			RankTypeBtnList() {
				// 使用mainColor作为按钮背景色
				return [
					{
						bgColor: this.mainColor,
						textColor: '#FFF',
						text: '日榜',
						textSize: 24
					},
					{
						bgColor: this.mainColor,
						textColor: '#FFF',
						text: '周榜',
						textSize: 24
					},
					{
						bgColor: this.mainColor,
						textColor: '#FFF',
						text: '月榜',
						textSize: 24
					},
					{
						bgColor: this.mainColor,
						textColor: '#FFF',
						text: '总榜',
						textSize: 24
					}
				];
			}
		},
		async onLoad() {
			// 优化：使用 userStore 统一获取用户信息
			try {
				this.cachedUserInfo = await getUserInfo();
				console.log('[用户信息加载]', this.cachedUserInfo);
			} catch (error) {
				console.error('获取用户信息失败:', error);
				this.cachedUserInfo = {};
			}
			
			this.getRankingData()
			this.getRankingSetting()
		},
		onReady() {
			// 组件挂载后调用open方法，让悬浮按钮处于展开状态
			this.$nextTick(() => {
				if (this.$refs.fabRef) {
					this.$refs.fabRef.open()
				}
			})
		},
		methods: {
		   // 处理悬浮按钮点击事件
			handleFabBtnClick(data) {
				// 切换排行榜类型
				if (data && data.index !== undefined) {
					switch (data.index) {
						case 0:
							this.RankSettings.rankType = 'day'
							break
						case 1:
							this.RankSettings.rankType = 'week'
							break
						case 2:
							this.RankSettings.rankType = 'month'
							break
						case 3:
							this.RankSettings.rankType = 'total'
							break
					}
					this.rankCurrentTab = data.index;
					// 切换榜单时，默认选中第一个选项卡
					this.currentTab = 0;
					// 更新榜单数据
					this.updateLeaderboardData();
					// 更新我的排名信息
					this.updateMyRankInfo();
				}
			},
			// 返回顶部
			backToTop() {
				uni.pageScrollTo({
					scrollTop: 0,
					duration: 300
				});
			},
			getMedalIcon(index) {
				const icons = [
					'https://datiqiniu.allpp.cn/static/gold_gift.png',
					'https://datiqiniu.allpp.cn/static/silver_gift.png',
					'https://datiqiniu.allpp.cn/static/copper_gift.png'
				];
				return icons[index];
			},
			//通过API获取排行榜设置信息
			getRankingSetting() {
				// TODO: 调用API获取排行榜设置信息
				this.$api.getRankSettings().then(res => {
					if (res.code === 1 && res.data) {
						this.RankSettings = res.data
						// 根据重置时间生成排行榜说明
						this.generateRankSettingsTime()
						// 根据积分规则生成排行榜说明
						this.generateRankSettingsScore()
					}
				})
			},
			// 生成排行榜说明文本
			generateRankSettingsTime() {
				const { reset_time_day, reset_time_week, reset_time_month, reset_time_total } = this.RankSettings
				let rankTime = []
				
				// 日榜说明
				if (reset_time_day) {
					const [hours, minutes] = reset_time_day.split(':')
					const resetHour = parseInt(hours)
					const resetMinute = parseInt(minutes)
					rankTime.push(`日榜每日${resetHour.toString().padStart(2, '0')}:${resetMinute.toString().padStart(2, '0')}结算`)
				}
				
				// 周榜说明
				if (reset_time_week) {
					const weekDays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
					const resetDay = parseInt(reset_time_week)
					rankTime.push(`周榜每周${weekDays[resetDay % 7]}结算`)
				}
				
				// 月榜说明
				if (reset_time_month) {
					const resetDate = parseInt(reset_time_month)
					rankTime.push(`月榜每月${resetDate}日结算`)
				}
				
				// 总榜说明
				if (reset_time_total) {
					rankTime.push(`总榜不重置`)
				}
				
				// 如果没有生成任何说明，设置默认值
				if (rankTime.length === 0) {
					rankTime = ['暂无排行榜说明']
				}
				
				this.RankSettings.rankTime = rankTime.join('\n')
			},
			// 根据积分规则生成排行榜说明
			generateRankSettingsScore() {
				const { integral_count_day, integral_count_week, integral_count_month, integral_count_total } = this.RankSettings
				let scoreRules = []

				// 解析积分规则字符串的辅助函数
				const parseIntegralRule = (rule, periodName) => {
					if (!rule) return null
					
					// 解析格式："1|5,2|3,3|2"
					const rules = rule.split(',').map(item => {
						const [rank, integral] = item.split('|').map(Number)
						return { rank, integral }
					})

					// 生成说明文本
					let scoreRulesText = `${periodName}积分奖励：`
					if (rules.length > 0) {
						scoreRulesText += rules.map(item => `第${item.rank}名${item.integral}积分`).join('、')
					} else {
						scoreRulesText += '暂无奖励'
					}
					
					return scoreRulesText
				}

				// 生成各排行榜的积分说明
				const dayScore = parseIntegralRule(integral_count_day, '日榜')
				const weekScore = parseIntegralRule(integral_count_week, '周榜')
				const monthScore = parseIntegralRule(integral_count_month, '月榜')
				const totalScore = parseIntegralRule(integral_count_total, '总榜')

				// 收集有效的积分说明
				if (dayScore) scoreRules.push(dayScore)
				if (weekScore) scoreRules.push(weekScore)
				if (monthScore) scoreRules.push(monthScore)
				if (totalScore) scoreRules.push(totalScore)

				// 如果没有生成任何说明，设置默认值
				if (scoreRules.length === 0) {
					scoreRules = ['暂无积分奖励说明']
				}

				this.RankSettings.rankScore = scoreRules.join('\n')
			},
			//通过API获取排行榜数据
			getRankingData() {
				// 调用API获取排行榜数据
				this.$api.apiRankingList().then(res => {
					if (res.code === 1 && res.data) {
						// 保存完整的排行榜数据
						this.rankingData = res.data;
						// 更新当前榜单数据
						this.updateLeaderboardData();
						
						// 更新我的排名信息
						this.updateMyRankInfo();
					}
				})
			},
			// 更新排行榜数据
			updateLeaderboardData() {
				const rankType = this.getRankType();
				const rankData = this.rankingData[rankType];
				
				if (!rankData) {
					this.leaderboardData = [];
					return;
				}
				
				// 过滤掉 'me' 键，只保留数字索引的数据
				const filteredData = Object.keys(rankData)
					.filter(key => key !== 'me' && !isNaN(key))
					.map(key => rankData[key]);
				
				// 根据currentTab过滤周榜和月榜的数据
				if (rankType === 'week') {
					const weekType = this.currentTab === 0 ? 'current' : 'last';
					this.leaderboardData = filteredData.filter(item => item.week_type === weekType);
				} else if (rankType === 'month') {
					const monthType = this.currentTab === 0 ? 'current' : 'last';
					this.leaderboardData = filteredData.filter(item => item.month_type === monthType);
				} else {
					this.leaderboardData = filteredData;
				}
			},
			// 更新我的排名信息
			updateMyRankInfo() {
				const rankType = this.getRankType();
				const myRank = this.rankingData[rankType]?.me;
				
				if (myRank) {
					// 从用户信息中获取昵称和头像
					const userInfo = myRank.user || {};
					// 优化：处理用户ID可能不存在的情况
					const userId = myRank.user_id || myRank.uid || this.cachedUserInfo?.id || '';
					const defaultName = userId ? `用户${userId}` : '未知用户';
					
					this.myInfo = {
						name: userInfo.nickname || this.cachedUserInfo?.nickname || defaultName,
						avatar: userInfo.avatar || this.cachedUserInfo?.avatar || this.defaultAvatar,
						rankStatus: myRank.ranking ? `第${myRank.ranking}名` : '暂未排名',
						score: this.getRankDimensionValue(myRank)
					};
				} else {
					// 没有排名数据时，优先使用 userStore 中的用户信息
					this.myInfo = {
						name: this.cachedUserInfo?.nickname || '用户',
						avatar: this.cachedUserInfo?.avatar || this.defaultAvatar,
						rankStatus: '暂未排名',
						score: 0
					};
				}
			},
			// 根据rankCurrentTab获取对应的排行榜类型
			getRankType() {
				const rankTypes = ['day', 'week', 'month', 'total'];
				return rankTypes[this.rankCurrentTab] || 'day';
			},
			scoreTab(index) {
				this.currentTab = index;
				// 切换选项卡时更新榜单数据
				this.updateLeaderboardData();
			},
			handleModalClick(event) {
				this.showRankSettingsDesc = false
			},
			// 获取用户头像
			getAvatar(item) {
				return item.user?.avatar || this.defaultAvatar;
			},
			// 获取用户昵称
			getNickname(item) {
				// 优化：处理用户ID可能不存在的情况
				const userId = item.user_id || item.uid || '';
				const defaultName = userId ? `用户${userId}` : '未知用户';
				return item.user?.nickname || defaultName;
			},
			// 获取排名维度标签
			getRankDimensionLabel() {
				const dimension = this.RankSettings.ranking_dimension || 'correct_count';
				const labels = {
					integral: '积分',
					correct_count: '答对题数',
					accuracy: '正确率'
				};
				return labels[dimension] || '答对题数';
			},
			// 获取排名维度值
			getRankDimensionValue(item) {
				const dimension = this.RankSettings.ranking_dimension || 'correct_count';
				if (dimension === 'correct_count') {
					return item.correct_count || 0;
				} else if (dimension === 'accuracy') {
					return (item.accuracy || '0') + '%';
				} else {
					// 即使是积分维度，也显示答对题数
					return item.correct_count || 0;
				}
			},
			// 获取奖励积分
			getRewardPoints(ranking) {
				const rankType = this.getRankType();
				const integralField = `integral_count_${rankType}`;
				const integralRule = this.RankSettings[integralField];
				
				if (!integralRule) {
					return '+0积分';
				}
				
				// 解析格式："1|5,2|3,3|2"
				const rules = integralRule.split(',').map(item => {
					const [rank, integral] = item.split('|').map(Number);
					return { rank, integral };
				});
				
				// 查找对应排名的积分
				const rule = rules.find(r => r.rank === ranking);
				if (rule) {
					return '+' + rule.integral + '积分';
				}
				
				return '+0积分';
			}
		}
	}
</script>

<style scoped lang="scss">
	@import "@/scss/custom_nav_bar.scss";
	.container {
		background-color: #f8f8f8;
		min-height: 100vh;
		padding-bottom: 20rpx;
		/* 底部留白 */
	}


	/* 模拟导航栏样式(与之前类似，背景透明) */
	.nav-bar {
		position: absolute;
		/* 悬浮banner样式 */
		top: 0;
		left: 0;
		right: 0;
		display: flex;
		align-items: center;
		justify-content: space-between;
		height: 88rpx;
		/* 状态栏高度 + 导航栏内容高度，需要适配 */
		padding: var(--status-bar-height) 30rpx 0;
		/* 适配状态栏 */
		background-color: transparent;
		/* 透明背景 */
		z-index: 10;
		color: #fff;
		/* 白色图标和文字*/
	}

	.back-icon,
	.action-icon {
		color: #fff;
		font-size: 40rpx;
	}

	.title {
		color: #fff;
		font-size: 34rpx;
		font-weight: bold;
	}

	.actions {
		display: flex;
		align-items: center;
	}

	.action-icon {
		margin-left: 30rpx;
	}


	/* 顶部 Banner */
	.banner {
		background: linear-gradient(180deg, #42B476 0%, #42B476 100%);
		/* 渐变背景 */
		padding: calc(var(--status-bar-height) + 88rpx) 30rpx 40rpx;
		/* 顶部留出导航栏空间，底部增加padding */
		text-align: center;
		color: #fff;
		position: relative;
		overflow: hidden;
		display: flex;
		flex-direction: column;
		align-items: center;
	}
	
	/* Banner内容，确保显示在波浪背景之上 */
	.banner-content {
		position: relative;
		z-index: 2;
	}
	
	/* 动态背景波浪 */
	@keyframes move_wave {
		0% {
			transform: translateX(0) scaleY(1);
		}
		50% {
			transform: translateX(-25%) scaleY(1);
		}
		100% {
			transform: translateX(-50%) scaleY(1);
		}
	}

	.tnwave {
		overflow: hidden;
		position: absolute;
		left: 0;
		right: 0;
		top: 0;
		height: 100%;
		z-index: 1;
	}

	.waveWrapperInner {
		position: absolute;
		width: 100%;
		overflow: hidden;
		height: 100%;
	}

	.wave {
		position: absolute;
		left: 0;
		width: 200%;
		height: 100%;
		background-repeat: repeat no-repeat;
		background-position: 0 bottom;
		transform-origin: center bottom;
	}
	
	.bgTop {
		opacity: 0.1;
	}

	.waveTop {
		background-size: 50% 45px;
	}

	.waveAnimation .waveTop {
		animation: move_wave 4s linear infinite;
	}

	.bgMiddle {
		opacity: 0.2;
	}

	.waveMiddle {
		background-size: 50% 40px;
	}

	.waveAnimation .waveMiddle {
		animation: move_wave 3.5s linear infinite;
	}

	.bgBottom {
		opacity: 0.3;
	}

	.waveBottom {
		background-size: 50% 35px;
	}

	.waveAnimation .waveBottom {
		animation: move_wave 2s linear infinite;
	}


	.tag {
		background-color: rgba(255, 255, 255, 0.8);
		color: #00796B;
		/* 深青色*/
		padding: 8rpx 20rpx;
		border-radius: 30rpx;
		font-size: 24rpx;
		margin: 10rpx 5rpx;
	}

	.gift-tag {
		background-color: rgba(255, 255, 255, 0.9);
	}

	.gift-icon {
		margin-right: 8rpx;
	}

	/* Tabs */
	.tabs {
		// text-align: center;
		// position: absolute;
		// bottom: -40rpx;
		/* 使其一半悬浮在banner下方 */
		left: 50%;
		// transform: translateX(-50%);
		display: flex;
		width: 440rpx;
		height: 60rpx;
		line-height: 60rpx;
		justify-content: center;
		flex-direction: row;
		background-color: #fff;
		border-radius: 40rpx;
		// box-shadow: 0 4rpx 10rpx rgba(0, 0, 0, 0.1);
		/* 给内部tab留出空间 */
		// z-index: 5;
	}

	.tab {
		width: 200rpx;
		// padding: 15rpx 40rpx;
		font-size: 28rpx;
		text-align: center;
		color: #666;
		border-radius: 35rpx;
		transition: all 0.3s ease;
	}

	.tab.active {
		background-color: $view-theme;
		/* 激活状态背景色 */
		color: #fff;
		font-weight: bold;
	}

	/* 我的排名 */
	.my-score-card {
		background-color: #fff;
		margin: 20rpx 30rpx 20rpx;
		/* 顶部留出tabs空间 */
		border-radius: 16rpx;
		padding: 30rpx;
		display: flex;
		align-items: center;
		position: relative;
		/* 用于定位label */
		// box-shadow: 0 4rpx 10rpx rgba(0, 0, 0, 0.05);
	}

	.my-score-label {
		position: absolute;
		top: -20rpx;
		left: 30rpx;
		background: linear-gradient(to right, #FFD700, #FFA500);
		/* 金黄色渐变*/
		color: #fff;
		padding: 5rpx 15rpx;
		border-radius: 8rpx 8rpx 8rpx 0;
		/* 左下角直角*/
		font-size: 24rpx;
		font-weight: bold;
	}

	.avatar {
		width: 80rpx;
		height: 80rpx;
		border-radius: 50%;
		margin-right: 20rpx;
		background-color: #eee;
		/* 占位背景 */
	}

	.name {
		font-size: 30rpx;
		color: #333;
		font-weight: bold;
		flex-grow: 1;
		/* 占据剩余空间 */
	}

	.rank-status {
		font-size: 26rpx;
		color: #999;
		margin-right: 20rpx;
	}

	.score {
		font-size: 30rpx;
		color: $view-theme;
		/* 橙色 */
		font-weight: bold;
	}

	/* 排行榜*/
	.leaderboard {
		background-color: #fff;
		margin: 0 30rpx;
		border-radius: 16rpx;
		padding: 0 30rpx 20rpx;
		/* 底部留白 */
		// box-shadow: 0 4rpx 10rpx rgba(0, 0, 0, 0.05);
	}

	.list-header {
		display: flex;
		align-items: center;
		padding: 25rpx 0;
		font-size: 24rpx;
		color: #999;
		border-bottom: 1rpx solid #f0f0f0;
	}

	.header-rank {
		width: 15%;
		text-align: center;
	}

	.header-name {
		width: 40%;
		text-align: left;
		padding-left: 10rpx;
	}

	.header-score {
		width: 45%;
		text-align: right;
		padding-right: 10rpx;
	}

	.list-item {
		display: flex;
		align-items: center;
		padding: 25rpx 0;
		border-bottom: 1rpx solid #f0f0f0;

		&:last-child {
			border-bottom: none;
		}
	}

	.rank-cell {
		width: 15%;
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.medal-icon {
		width: 50rpx;
		height: auto;
		/* 高度自适应 */
	}

	.rank-number {
		font-size: 30rpx;
		color: #666;
		font-weight: bold;
	}

	.name-cell {
		width: 40%;
		display: flex;
		align-items: center;
		padding-left: 10rpx;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.item-avatar {
		width: 70rpx;
		height: 70rpx;
		border-radius: 50%;
		margin-right: 15rpx;
		background-color: #eee;
		/* 占位背景 */
	}

	.name {
		font-size: 28rpx;
		color: #333;
		// 处理名字过长的情况		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.points-cell {
		width: 25%;
		/* 调整宽度给积分*/
		font-size: 24rpx;
		color: $view-theme;
		/* 默认橙色 */
		text-align: right;
		padding-right: 10rpx;
	}

	.highlight-points {
		font-weight: bold;
	}

	.score-cell {
		width: 20%;
		/* 调整宽度给答对题数*/
		font-size: 28rpx;
		color: #333;
		font-weight: bold;
		text-align: right;
		padding-right: 10rpx;
	}
	
	/* 空数据样式 */
	.empty-data {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 80rpx 0;
	}
	
	.empty-icon {
		width: 200rpx;
		height: auto;
		margin-bottom: 30rpx;
		opacity: 0.6;
	}
	
	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
</style>

