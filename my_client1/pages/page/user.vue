<template>
  <view class="page-e tn-safe-area-inset-bottom">
    <!-- 顶部自定义导航 -->
	<view class="tn-navbg" :style="{height: vuex_custom_bar_height + 'px'}">
		<tn-nav-bar fixed alpha customBack>
		<view class="tn-flex tn-flex-col-center tn-flex-row-center ">
			<text class="tn-text-bold tn-text-xl tn-color-white">
			{{ projectName }}
			</text>
		</view>
		</tn-nav-bar>
	</view>

    <!-- 顶部背景 -->
    <view class="top-backgroup tn-bg-gradient-to-b"></view>

    <view
      class="tn-margin-left tn-margin-right"
      :style="{paddingTop: vuex_custom_bar_height + 10 + 'px'}"
    >
      <!-- 用户信息卡片 -->
      <view
        class="tn-flex tn-flex-row-between tn-flex-col-center tn-margin-bottom"
        style="margin-top: -380rpx;"
      >
        <!-- 左侧用户信息 -->
        <view class="tn-flex tn-flex-col-center tn-flex-row-left">
          <!-- 头像 -->
          <view
            class="logo-pic tn-shadow"
            @click="handleAvatarClick"
          >
            <view class="logo-image">
              <view
			class="tn-shadow-blur tn-border-4 tn-border-white"
			:style="'background-image:url('+ (currentUserInfo.avatar || 'https://datiqiniu.allpp.cn/static/userinfo_avatar.png') +');width: 120rpx;height: 120rpx;background-size: cover;overflow: hidden;border-radius: 50%;'"
		/>
            </view>
          </view>
          
          <!-- 用户信息 -->
          <view class="tn-padding-left-sm">
            <!-- 昵称和会员状态 -->
            <view class="tn-flex tn-flex-row tn-flex-wrap tn-align-items-center" style="gap: 12rpx;">
              <text class="tn-text-bold tn-text-lg tn-color-white" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ isUserLoggedIn() ? (currentUserInfo.nickname || currentUserInfo.account || currentUserInfo.mobile || currentUserInfo.sn || '登录用户') : '游客' }}
              </text>
              <!-- 会员状态显示 -->
              <text v-if="isUserLoggedIn() && currentUserInfo.vip_state === 3" 
                class="tn-round tn-text-xs tn-bg-yellow tn-color-black"
                style="padding: 6rpx 16rpx;"
              >
                vip会员 （有效期至: {{ currentUserInfo.vip_endTime ? unixtimeToDate(currentUserInfo.vip_endTime) : '未知' }}）
              </text>
            
              <text v-else-if="isUserLoggedIn() && currentUserInfo.vip_state === 2" 
                class="tn-round tn-text-xs tn-bg-red tn-color-white"
                style="padding: 6rpx 16rpx;"
              >
                会员过期
              </text>
                <text v-else
                class="tn-round tn-text-xs tn-bg-gray tn-color-white"
                style="padding: 6rpx 16rpx;"
              >
                {{ isUserLoggedIn() ? '普通用户' : '未登录' }}
              </text>
            </view>
            
            <!-- 编号和积分 -->
            <view
              class="tn-flex tn-flex-row tn-flex-wrap tn-align-items-center tn-margin-top-sm"
              @click="handleNumberClick"
              style="gap: 20rpx;"
            >
              <view v-if="currentUserInfo.sn && isUserLoggedIn()" class="tn-flex tn-flex-row tn-align-items-center">
                <text class="tn-color-white tn-text-xs">
                  编号: {{ currentUserInfo.sn }}
                </text>
                <text class="tn-padding-left-xs tn-text-sm tn-icon-copy tn-color-white" />
              </view>
              <view class="tn-flex tn-flex-row tn-align-items-center">
                <text class="tn-color-white tn-text-xs">
                  积分：{{ isUserLoggedIn() ? (currentUserInfo.integral || 0) : 0 }}
                </text>
              </view>
            </view>
          </view>
        </view>
        
        <!-- 右侧功能按钮 -->
        <view class="tn-flex tn-flex-row tn-align-items-center">
          <!-- 消息按钮 - 未登录时不显示 -->
          <view
        v-if="currentUserInfo && (currentUserInfo.id || currentUserInfo.user_id || currentUserInfo.sn) && (isUserLoggedIn())"
        @click="tn('/subpages/user/message')"
        class="tn-relative tn-flex tn-flex-row-center tn-flex-col-center"
        style="width: 50rpx; height: 50rpx; position: relative;"
      >
        <view
          class="tn-icon-notice tn-color-white"
          style="font-size: 50rpx;"
        ></view>
        <!-- 未读消息徽标 -->
        <tn-badge
        v-if="unreadMessageCount > 0"
          :absolute="true"
          :translateCenter="true"
          backgroundColor="#FF3B30"
          fontColor="#FFFFFF"
          :radius="25"
          :fontSize="15"
        >
          {{ unreadMessageCount > 99 ? '99+' : unreadMessageCount }}
        </tn-badge>
      </view>

      <!-- 设置按钮 - 未登录时不显示 -->
      <view
        v-if="currentUserInfo && (currentUserInfo.id || currentUserInfo.user_id || currentUserInfo.sn) && (isUserLoggedIn())"
        @click="tn('/subpages/user/set')"
        class="tn-flex tn-flex-row-center tn-flex-col-center"
        style="width: 50rpx; height: 50rpx; margin-left: 20rpx;"
      >
        <view
          class="tn-icon-install tn-color-white"
          style="font-size: 50rpx;"
        />
      </view>
        </view>
      </view>

      <!-- 会员状态卡片 -->
      <view class="tn-margin-bottom">
        <view
          class="button-number tn-flex tn-flex-row-between tn-flex-col-center tn-shadow-lg tn-round-lg"
          :style="{background: `linear-gradient(-135deg, ${mainColor} 0%, ${mainColor} 50%)`}"
        >
          <view class="tn-margin-left tn-flex-1">
            <view
              class="tn-flex tn-flex-col-center tn-align-items-start"
              style="color: #F1C68E;"
            >
              <text class="tn-text-bold tn-text-xl">
                <!-- 会员状态：0-普通用户，1-未激活，2-过期，3-已激活 -->
                <text v-if="isUserLoggedIn()">
                  {{ currentUserInfo.vip_state === 0 ? '普通用户' : (currentUserInfo.vip_state === 1 ? '会员激活' : (currentUserInfo.vip_state === 2 ? '会员已过期' : '会员已激活')) }}
                </text>
                <text v-else>会员服务</text>
              </text>
              <text class="tn-icon-vip-text tn-text-center" style="font-size: 60rpx;"></text> 
            </view>
            <view class="tn-color-white tn-text-sm tn-margin-top-xs">
              可使用激活码、积分等激活会员
            </view>
          </view>
          <view
            class="tn-margin-right"
            @click="handleMemberClick"
          >
            <tn-button
              shape="round"
              background-color="#F1C68E"
              padding=""
              width="180rpx"
              :style="{fontWeight: 'bold'}"
            >
              <text v-if="isUserLoggedIn()">
                {{ currentUserInfo.vip_state === 0 ? '激活会员' : (currentUserInfo.vip_state === 1 ? '马上激活' : (currentUserInfo.vip_state === 2 ? '重新激活' : '查看权益')) }}
              </text>
              <text v-else>登录查看</text>
            </tn-button>
          </view>

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
        </view>
      </view>

      <!-- 更多信息-->
      <!-- 方式12 start-->
    <view class="tn-flex tn-flex-wrap tn-margin-top-xs tn-padding-sm job-shadow"  style="background-color: #ffffff;">
      <block
        v-for="(item, index) in userTopList"
        :key="index"
      >
        <view
          class=" "
          style="width: 25%;"   @click.stop="handleMenuClick(item)"
        >
          <view class="tn-flex tn-flex-direction-column tn-flex-row-center tn-flex-col-center tn-padding-sm">
            <view class="icon16__item--icon tn-flex tn-flex-row-center tn-flex-col-center">
              <view
                class="tn-cool-color-icon16"
                :class="[$tn.color.getRandomCoolBgClass(index) + ' tn-icon-' + item.icon]"
              />
            </view>  
            <view class="tn-color-black tn-text-md tn-text-center">
              <text class="tn-text-ellipsis">
                {{ item.title }}
              </text>
            </view>
          </view>
        </view>
      </block>
    </view>

      <!-- 更多信息-->
      <view class="tn-margin-top">
		<tn-list-cell v-if="currentUserInfo.is_admin == 1"
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="tn('/subpages/examEdit/index?uid=' + questionLibrary)"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-edit-write-fill" />
            </view>
            <view class="tn-margin-left-sm tn-flex-1">
              试题管理
            </view>
            <view class="tn-color-gray tn-icon-right" />
          </view>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="tn('/subpages/common/serviceQrCode')"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-plane-fill" />
            </view>
            <view class="tn-margin-left-sm tn-flex-1">
              商务合作
            </view>
            <view class="tn-color-gray tn-icon-right" />
          </view>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="tn('/subpages/common/helpList')"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-ticket-fill" />
            </view>
            <view class="tn-margin-left-sm tn-flex-1">
              在线帮助
            </view>
            <view class="tn-color-gray tn-icon-right" />
          </view>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="tn('/subpages/common/update')"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-message-fill" />
            </view>
            <view
              class="tn-flex tn-flex-row-between"
              style="width: 100%;"
            >
              <view class="tn-margin-left-sm">
                更新订阅
              </view>
              <view class="tn-color-gray tn-icon-right" />
            </view>
          </view>
        </tn-list-cell>
      </view>

      <view class="tn-margin-top">
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
        >
          <button
            class="tn-flex tn-flex-col-center tn-button--clear-style"
            open-type="contact"
          >
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-service-fill" />
            </view>
            <view
              class="tn-flex tn-flex-row-between"
              style="width: 100%;"
            >
              <view class="tn-margin-left-sm">
                在线客服
              </view>
              <view class="tn-color-gray tn-icon-right" />
            </view>
          </button>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
        >
          <button
            class="tn-flex tn-flex-col-center tn-button--clear-style"
            open-type="feedback"
          >
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-tip-fill" />
            </view>
            <view
              class="tn-flex tn-flex-row-between"
              style="width: 100%;"
            >
              <view class="tn-margin-left-sm">
                问题反馈
              </view>
              <view class="tn-color-gray tn-icon-right" />
            </view>
          </button>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="copyWechat()"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-tel-circle-fill" />
            </view>
            <view class="tn-margin-left-sm tn-flex-1">
              技术热线
            </view>
            <view
              class="tn-margin-left-sm tn-color-wallpaper tn-text-sm tn-padding-left-xs tn-padding-right-xs tn-bg-gray--light tn-round"
            >
              {{ customerWechat }}
            </view>
          </view>
        </tn-list-cell>
        <tn-list-cell
          :hover="true"
          :unlined="true"
          :radius="true"
          :font-size="30"
          @click="handleLoginOutClick"
        >
          <view class="tn-flex tn-flex-col-center">
            <view
              class="icon1__item--icon tn-flex tn-flex-row-center tn-flex-col-center"
              :style="{color: mainColor}"
            >
              <view class="tn-icon-my-simple-fill" />
            </view>
            <view class="tn-margin-left-sm tn-flex-1">
              {{ isUserLoggedIn() ? '退出登录' : '马上登录' }}
            </view>
            <view
              class="tn-margin-left-sm tn-color-wallpaper tn-text-sm tn-padding-left-xs tn-padding-right-xs tn-bg-gray--light tn-round"
            />
          </view>
        </tn-list-cell>
      </view>
    </view>

    <view class="tn-text-center tn-margin-top-xl tn-padding-bottom">
      <view>
        <text class="tn-icon-copyright" />
        <text >
          {{ copyright }}
        </text>
      </view>
    </view>
    <view class="tn-tabbar-height" />
  </view>
</template>

<script>
	import template_page_mixin from '@/libs/mixin/template_page_mixin.js'
	import messageApi from '@/util/api/message.js'
	import { clearUserInfo, isUserLoggedIn, getUserInfo } from '@/util/userStore.js'
	export default {
		name: 'PageE',
		mixins: [template_page_mixin],
		props: {
			user: {
				required: false,
				type: Object,
				default: () => ({})
			},
		},
		data() {
			return {
				loginStatus: 2, // 1登录2未登录
				websiteConfig: null, // 网站配置信息
				projectName: '会员中心', // 项目名称
				unreadMessageCount: 0, // 未读消息总数
				userInfo: {}, // 用户信息，用于存储登录后的用户数据
				needRefresh: false, // 是否需要刷新数据
				questionLibrary: '', // 当前题库
				userTopList: [], // 菜单列表
			}
		},
		computed: {
			// 使用计算属性实时获取globalData中的配置数据
			mainColor() {
				const color = getApp().globalData.mainColor;
				// 确保返回的是字符串类型，避免null或undefined
				return typeof color === 'string' && color.trim() !== '' ? color : '#007AFF';
			},
			customerWechat() {
				return getApp().globalData.customerWechat || getApp().globalData.otherSettings?.customer_wechat || 'xxhw1314'
			},
			copyright() {
				return getApp().globalData.copyRight || getApp().globalData.otherSettings?.copyright || 'Copyright © 2025 精解析答题'
			},
			// 实时获取最新的用户信息，优先使用组件内的userInfo数据
			currentUserInfo() {
				// 如果组件内userInfo不为空，直接返回
				if (this.userInfo && typeof this.userInfo === 'object' && Object.keys(this.userInfo).length > 0) {
					return this.userInfo;
				}
				
				// 如果组件内userInfo为空，尝试从本地存储获取
				const localUserInfo = uni.getStorageSync('userInfo');
				if (localUserInfo && typeof localUserInfo === 'object' && Object.keys(localUserInfo).length > 0) {
					return localUserInfo;
				}
				
				// 如果本地存储也没有，尝试从globalData获取
				try {
					const app = getApp();
					if (app && app.globalData && app.globalData.userInfo && typeof app.globalData.userInfo === 'object' && Object.keys(app.globalData.userInfo).length > 0) {
						return app.globalData.userInfo;
					}
				} catch (error) {
					console.error('[currentUserInfo] 从globalData获取userInfo失败:', error);
				}
				
				// 如果都没有，返回空对象
				console.log('[currentUserInfo] 所有来源都没有userInfo，返回空对象');
				return {};
			}
		},
		watch: {
			// 监听props中的user属性变化，及时更新组件内的userInfo
			user: {
				handler(newUser) {
					if (newUser && Object.keys(newUser).length > 0) {
						this.userInfo = { ...newUser };
						this.loginStatus = 1;
					}
				},
				deep: true,
				immediate: true
			}
		},
		// 组件生命周期 - 挂载完成
		mounted() {

							
			// 2. 获取当前题库
			this.questionLibrary = uni.getStorageSync('selectedCategoryUid') || '';
			
			// 3. 初始化用户信息：优先使用 props 传递的 user 属性
			if (this.user && Object.keys(this.user).length > 0) {
				this.userInfo = { ...this.user };
				this.loginStatus = 1;
			}

			// 1. 检查是否需要刷新数据（从其他页面返回时）
			if (this.needRefresh) {
				this.needRefresh = false
				this.handleRefresh()
			} else {
				// 2. 直接调用 updateUserInfo 获取最新的用户信息
				this.updateUserInfo()
			}
			// 调用 fetchUserMenu 获取菜单数据
			this.fetchUserMenu();
			// 获取未读消息数量
			this.fetchUnreadMessageCount();
		},
		// 页面显示时刷新用户信息（处理从登录页返回的情况）
		onShow() {
			// 延迟 100ms 执行，确保登录已完成
			setTimeout(() => {
				this.updateUserInfo()
				// 获取未读消息数量
				this.fetchUnreadMessageCount()
			}, 100)
		},
		methods: {
			/**
			 * 获取网站配置信息
			 */
			async fetchWebsiteConfig() {
				try {
					// 从App.vue的globalData中获取配置信息
					const otherSettings = getApp().globalData.otherSettings
				} catch (error) {
					console.error('[fetchWebsiteConfig] 获取网站配置失败:', error)
				}
			},
			/**
			 * 页面跳转
			 * @param {string} url - 跳转地址
			 */
			tn(url) {
				if (url == '') {
					this.$func.showToast('暂未开放')
					return
				}
				this.$func.navigatorTo(url)
			},
			/**
			 * 处理菜单点击事件
			 * @param {Object} item - 菜单项数据
			 */
			handleMenuClick(item) {
				// 检查questionLibrary类型，确保正确获取uid
				let uid = '';
				if (typeof this.questionLibrary === 'object' && this.questionLibrary) {
					uid = this.questionLibrary.uid || '';
				} else if (typeof this.questionLibrary === 'string') {
					uid = this.questionLibrary;
				}
				
				// 构建完整URL
				let url = item.url;
				if (uid) {
					url += (url.includes('?') ? '&' : '?') + 'uid=' + uid;
				}
				
				// 调用跳转方法
				this.tn(url);
			},
			async fetchUserMenu() {
				try {
					// 获取当前平台
					let client = 'wechat_mini'; // 默认值
					if (this.$func && typeof this.$func.currentPlatform === 'function') {
						client = this.$func.currentPlatform();
					}
					
					// 构建请求参数
					const requestParams = {
						type: 'image_menu',
						position: 'user_top',
						client: client
					};
					
					// 执行API调用
					if (this.$api && typeof this.$api.apiImageConfig === 'function') {
						const res = await this.$api.apiImageConfig(requestParams);
						
						// 处理响应数据
						if (res.code === 1 && res.data) {
							this.userTopList = Array.isArray(res.data) ? res.data : [];
						} else {
							this.userTopList = [];
						}
						
						// 强制更新视图
						this.$forceUpdate();
						
						return res;
					} else {
						this.userTopList = [];
						return Promise.reject(new Error('$api.apiImageConfig 方法不存在'));
					}
				} catch (error) {
					// 确保菜单数据为空数组，避免页面出错
					this.userTopList = [];
					this.$forceUpdate();
					
					return Promise.reject(error);
				}
			},
			/**
			 * 复制微信号
			 */
			copyWechat() {
				this.$func.setClipboardData(this.customerWechat)
			},
			/**
			 * 复制用户编号
			 * @param {string} sn - 用户编号
			 */
			copyUid(sn) {
				this.$func.setClipboardData(String(sn))
			},
			/**
			 * 处理头像点击事件
			 */
			handleAvatarClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/user/set');
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理编号点击事件
			 */
			handleNumberClick() {
				if (this.isUserLoggedIn() && this.currentUserInfo.sn) {
					this.copyUid(this.currentUserInfo.sn);
				}
			},
			/**
			 * 处理会员卡片点击事件
			 */
			handleMemberClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/user/member');
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理答题历史点击事件
			 */
			handleExamHistoryClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/examHistory/examinationHistory?uid=' + this.questionLibrary['uid']);
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理我的收藏点击事件
			 */
			handleCollectionClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/examCollection/questionCollection?uid=' + this.questionLibrary['uid']);
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理我的错题点击事件
			 */
			handleErrorClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/examError/questionError?uid=' + this.questionLibrary['uid']);
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理积分明细点击事件
			 */
			handleIntegralClick() {
				if (this.isUserLoggedIn()) {
					this.tn('/subpages/integral/integralHistory');
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 处理页面刷新，用于onRefresh和needRefresh调用
			 */
			handleRefresh() {
				// 强制刷新用户信息
				this.updateUserInfo()
					.then(() => {
						// 刷新未读消息数量
						this.fetchUnreadMessageCount()
						// 强制更新视图
						this.$forceUpdate()
					})
				.catch((error) => {
						console.error('刷新页面数据失败:', error)
					})
			},
			/**
			 * 更新用户信息
			 */
			async updateUserInfo() {
				try {
					// 1. 获取 token
					const loginData = uni.getStorageSync('login');
					const app = getApp();
					
					// 兼容新旧版本的 token 存储格式
					let token = '';
					if (typeof loginData === 'string') {
						// 旧版本：直接存储 token 字符串
						token = loginData;
					} else if (loginData && typeof loginData === 'object' && loginData.token) {
						// 新版本：存储的是包含 token 的对象
						token = loginData.token;
					}
					
					// 记录更新开始
					
					// 2. 只要 token 存在，就尝试获取用户信息
					if (token && typeof token === 'string' && token.trim() !== '') {
						// 优先从服务器获取最新的用户信息（强制刷新）
						let userInfoFromServer = {};
						try {
							userInfoFromServer = await getUserInfo(true);
						} catch (serverError) {
							// 服务器获取失败时，从本地存储获取用户信息
							userInfoFromServer = uni.getStorageSync('userInfo') || {};
						}
						if (userInfoFromServer && typeof userInfoFromServer === 'object' && Object.keys(userInfoFromServer).length > 0) {
							// 确保 integral 和 vip_state 字段存在，避免显示为 0 或 undefined
						const userInfoWithDefaults = {
							...userInfoFromServer,
							integral: userInfoFromServer.integral || 0,
							vip_state: userInfoFromServer.vip_state || 0,
							vip_endTime: userInfoFromServer.vip_endTime || null
						};
							
							// 使用展开运算符创建新对象，确保触发 Vue 响应式更新
							this.userInfo = { ...userInfoWithDefaults };
							this.loginStatus = 1;
						}
						else {
							// 如果服务器返回的用户信息为空，尝试从本地存储获取
							const localUserInfo = uni.getStorageSync('userInfo');
							if (localUserInfo && typeof localUserInfo === 'object' && Object.keys(localUserInfo).length > 0) {
								const userInfoWithDefaults = {
									...localUserInfo,
									integral: localUserInfo.integral || 0,
									vip_state: localUserInfo.vip_state || 0,
									vip_endTime: localUserInfo.vip_endTime || null
								};
								this.userInfo = { ...userInfoWithDefaults };
								this.loginStatus = 1;
							} else {
								// 本地也没有用户信息，但 token 存在，可能是 API 调用失败
								// 从 globalData 获取用户信息作为最后的降级方案
								const globalUserInfo = app.globalData?.userInfo || {};
								if (globalUserInfo && Object.keys(globalUserInfo).length > 0) {
									const userInfoWithDefaults = {
										...globalUserInfo,
										integral: globalUserInfo.integral || 0,
										vip_state: globalUserInfo.vip_state || 0,
										vip_endTime: globalUserInfo.vip_endTime || null
									};
									this.userInfo = { ...userInfoWithDefaults };
									this.loginStatus = 1;
								} else {
									// 所有途径都失败，保持登录状态但用户信息为空，等待后续刷新
									console.log('[updateUserInfo] token 存在但用户信息获取失败，保持登录状态');
									this.loginStatus = 1;
									this.userInfo = {};
								}
							}
						}
						
						// 确保globalData也同步更新
						if (app && app.globalData) {
							app.globalData.userInfo = this.userInfo;
							app.globalData.loginToken = token;
						}
					} else {
						// 未登录状态
						console.log('[updateUserInfo] 未登录，重置用户信息');
						this.userInfo = {};
						this.loginStatus = 2;
						
						// 确保globalData也同步更新
						if (app && app.globalData) {
							app.globalData.userInfo = {};
							app.globalData.loginToken = '';
						}
					}
					
					// 3. 强制更新视图，确保所有绑定的数据都能及时刷新
					this.$nextTick(() => {
						this.$forceUpdate();
					});
					
					return this.userInfo;
				} catch (error) {
					console.error('更新用户信息失败:', error);
					// 发生错误时，尝试从本地存储获取用户信息，避免显示"登录用户"
					const localUserInfo = uni.getStorageSync('userInfo');
					if (localUserInfo && typeof localUserInfo === 'object' && Object.keys(localUserInfo).length > 0) {
						// 确保 integral、vip_state 和 vip_endTime 字段存在，避免显示异常
								const userInfoWithDefaults = {
									...localUserInfo,
									integral: localUserInfo.integral || 0,
									vip_state: localUserInfo.vip_state || 0,
									vip_endTime: localUserInfo.vip_endTime || null
								};
						this.userInfo = { ...userInfoWithDefaults };
						this.loginStatus = 1;
						
						// 确保 globalData 也同步更新
						const app = getApp();
						if (app && app.globalData) {
							app.globalData.userInfo = this.userInfo;
							// 兼容新旧版本的 token 存储格式
							const loginData = uni.getStorageSync('login');
							if (typeof loginData === 'string') {
								app.globalData.loginToken = loginData;
							} else if (loginData && typeof loginData === 'object' && loginData.token) {
								app.globalData.loginToken = loginData.token;
							}
						}
					}
					
					// 强制更新视图
					this.$nextTick(() => {
						this.$forceUpdate();
					});
					console.log('[updateUserInfo] userInfo.is_admin:', this.userInfo.is_admin);

					return this.userInfo;
				}
			},
			/**
		 * 检查用户是否已登录
		 * @returns {boolean} 是否已登录
		 */
		isUserLoggedIn() {
			try {
				// 1. 检查 token 是否存在，这是最核心的登录标识
				const loginData = uni.getStorageSync('login');
				
				// 兼容新旧版本的 token 存储格式
				let token = '';
				if (typeof loginData === 'string') {
					// 旧版本：直接存储 token 字符串
					token = loginData;
				} else if (loginData && typeof loginData === 'object' && loginData.token) {
					// 新版本：存储的是包含 token 的对象
					token = loginData.token;
				}
				
				if (!(token && typeof token === 'string' && token.trim() !== '')) {
					return false;
				}
				
				// 2. 只要 token 存在，就认为用户已登录，简化登录状态检测
				return true;
			} catch (error) {
				console.error('isUserLoggedIn: 检查登录状态失败:', error);
				return false;
			}
		},
			/**
			 * 处理登录/退出登录按钮点击事件
			 */
			handleLoginOutClick() {
				if (this.isUserLoggedIn()) {
					this.loginOut();
				} else {
					this.tn('/subpages/user/login');
				}
			},
			/**
			 * 退出登录
			 */
			loginOut() {
			// 使用统一的清除用户信息函数
			clearUserInfo()
			// 强制更新用户信息
			this.updateUserInfo()
			this.$func.showToast("退出成功")
			this.loginStatus = 2
			// 强制更新视图
			this.$forceUpdate()
			// 跳转回首页
			this.$func.navigatorTo('/pages/index/index')
		},
			/**
			 * 获取未读消息数量
			 */
			async fetchUnreadMessageCount() {
				try {
					const res = await messageApi.apiMessageUnreadCount()
					
					if (res && res.code === 1 && res.data) {
						// 直接使用API返回的total字段
						this.unreadMessageCount = res.data.total || 0
					} else {
						console.error('[fetchUnreadMessageCount] 响应数据异常:', res)
						this.unreadMessageCount = 0
					}
				} catch (error) {
					console.error('[fetchUnreadMessageCount] 获取失败:', error)
					this.unreadMessageCount = 0
				}
			},
			unixtimeToDate(unixtime) {
				const date = new Date(unixtime * 1000)
				const year = date.getFullYear()
				const month = date.getMonth() + 1
				const day = date.getDate()
				return `${year}-${month}-${day}`
			}
		}
	}
</script>

<style lang="scss" scoped>
	@import "@/scss/custom_nav_bar.scss";

	.page-e {
		max-height: 100vh;
	}

	.tn-color-wallpaper {
		color: #1D2541;
	}

	/* 顶部背景 start */
	.top-backgroup {
		height: 420rpx;
		z-index: -1;
		position: relative;
	}

	/* 顶部背景 end */

	/* 用户头像 start */
	.logo-image {
		width: 120rpx;
		height: 120rpx;
		position: relative;
		overflow: hidden;
		border-radius: 50%;
	}

	.logo-pic {
		background-size: cover;
		background-repeat: no-repeat;
		background-position: top;
		border: 4rpx solid #FFFFFF;
		box-shadow: 0rpx 4rpx 20rpx 0rpx rgba(0, 0, 0, 0.2);
		border-radius: 50%;
		transition: transform 0.2s ease;
	}

	.logo-pic.hover-class {
		transform: scale(0.95);
	}

	/* 图标容器 start */
	.icon12__item--icon {
		width: 70rpx;
		height: 70rpx;
		font-size: 40rpx;
		margin-bottom: 12rpx;
	}

	/* 背景波浪高度 */
	.button-number {
		width: 100%;
		height: 140rpx;
		border-radius: 20rpx;
		position: relative;
		z-index: 1;
		overflow: hidden;
		transition: transform 0.2s ease, box-shadow 0.2s ease;
	}

	.button-number.hover-class {
		transform: scale(0.98);
		box-shadow: 0rpx 2rpx 10rpx 0rpx rgba(0, 0, 0, 0.1) !important;
	}

	/* 动态背景波浪 - 简化版本 */
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
		bottom: 0;
		top: 0;
		margin: auto;
		z-index: -1;
		border-radius: 15rpx;
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

	/* 底部悬浮按钮 start*/
    .tn-tabbar-height {
    	min-height: 120rpx;
    	height: calc(140rpx + env(safe-area-inset-bottom));
    }

    /* 响应式设计 */
    @media (max-width: 375rpx) {
    	.logo-image {
    		width: 100rpx;
    		height: 100rpx;
    	}
    	
    	.button-number {
    		height: 160rpx;
    		flex-direction: column;
    		justify-content: center;
    		align-items: center;
    		gap: 16rpx;
    		padding: 20rpx 0;
    	}
    	
    	.top-backgroup {
    		height: 400rpx;
    	}
    }

    /* 卡片阴影效果 */
    .tn-shadow-lg {
    	box-shadow: 0rpx 8rpx 32rpx 0rpx rgba(0, 0, 0, 0.12);
    }

    /* 圆角样式 */
    .tn-round-lg {
    	border-radius: 20rpx;
    }

  /* 图标容器16 start */
 .job-shadow{
      box-shadow: 0rpx 0rpx 80rpx 0rpx rgba(0, 0, 0, 0.07);
      border-radius: 20rpx;
  }
  /* 信息展示 end */
  

  .tn-cool-color-icon16{
    // background-image: -webkit-linear-gradient(135deg, #ED1C24, #FECE12);   16
    // background-image: linear-gradient(135deg, #ED1C24, #FECE12);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    -webkit-text-fill-color: transparent;
  }
    .icon16 {
      &__item {
        // width: 30%;
        background-color: #FFFFFF;
        border-radius: 10rpx;
        padding: 0rpx;
        margin: 0rpx;
        transform: scale(1);
        transition: transform 0.3s linear;
        transform-origin: center center;
        
        &--icon {
          width: 70rpx;
          height: 70rpx;
          font-size: 45rpx;
          border-radius: 50%;
          position: relative;
          z-index: 1;
        }
      }
    }
  /* 图标容器16 end */
</style>