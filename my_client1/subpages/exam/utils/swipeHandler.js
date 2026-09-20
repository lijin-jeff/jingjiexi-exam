/**
 * 滑动处理优化工具
 * 解决 commonQuestion.vue 中复杂的滑动处理逻辑
 * 支持动态长度的 activeQuestions（2个或3个题目）
 * 适配微信小程序环境
 */

class SwipeHandler {
  constructor() {
    this.lastSwipeTime = 0
    this.lastProcessedIndex = -1
    // 滑动状态锁，防止并发滑动
    this.isSwiping = false
    // 滑动锁获取时间（用于监控）
    this.swipeLockTime = 0
    // 滑动锁监控定时器
    this.lockMonitorTimer = null
    // 滑动完成回调队列
    this.pendingCallbacks = []
    // 滑动超时定时器
    this.swipeTimeout = null
    // 连续滑动计数器（用于检测快速滑动）
    this.rapidSwipeCount = 0
    this.rapidSwipeResetTimer = null
    // smartDebounce 定时器
    this.debounceTimeoutId = null
    // 程序化跳转标记
    this.isProgrammaticJump = false

    // 滑动路径跟踪
    this._lastSwiperIndex = -1
    this._currentSwiperIndex = -1

    // 手势识别状态
    this.gestureState = {
      startX: 0,
      startY: 0,
      startTime: 0,
      isVerticalScrolling: false,
      isTouching: false,
      touchTarget: null,
      isValidSwipe: false,
      isInHotZone: false,
      directionLocked: false
    }

    // 滑动配置 - 所有常量收敛到这里
    this.config = {
      // 滑动阈值（px）
      swipeThreshold: 50,
      // 垂直滚动判定阈值（px）
      verticalThreshold: 10,
      // 点击判定阈值（px）
      clickThreshold: 30,
      // 最大滑动持续时间（ms）
      maxSwipeDuration: 500,
      // 防抖时间（ms）
      debounceTime: 150,
      // 连续快速滑动阈值（从2增加到3，减少合法滑动被拦截）
      rapidSwipeThreshold: 3,
      // 滑动超时时间（ms）
      swipeTimeout: 500,
      // 快速滑动重置时间（ms，从300增加到500，降低误拦截概率）
      rapidSwipeResetTime: 500,
      // transition完成判定阈值（px）
      transitionCompleteThreshold: 350,
      // touchend后恢复滑动的延迟（ms）
      touchEndDelay: 100,
      // 滑动异常恢复延迟（ms）
      swipeRecoveryDelay: 300,
      // 滑动热区比例（1.0表示全屏可滑动，0.33表示仅右侧1/3）
      swipeHotZoneRatio: 1.0,
      // 方向锁定后禁用水平滑动
      directionLockEnabled: true,
      // 垂直滚动判定阈值（px，降低到5使垂直滚动更容易被检测）
      verticalThreshold: 5
    }

    // 回调函数（由外部设置）
    this.callbacks = {
      onSwipeStart: null,
      onSwipeMove: null,
      onSwipeEnd: null,
      onVerticalScroll: null,
      onBeforeSwipe: null
    }

    // 日志控制 - 小程序环境判断
    this.isDev = this._checkDevEnvironment()
  }

  /**
   * 检查是否为开发环境（适配小程序）
   */
  _checkDevEnvironment() {
    try {
      // 微信小程序环境
      if (typeof wx !== 'undefined' && wx.getAccountInfoSync) {
        const accountInfo = wx.getAccountInfoSync()
        return accountInfo.miniProgram?.envVersion === 'develop'
      }
      // Web环境
      if (typeof process !== 'undefined' && process.env) {
        return process.env.NODE_ENV === 'development'
      }
      return false
    } catch (e) {
      return false
    }
  }

  /**
   * 获取触摸位置（兼容 clientX/Y 和 pageX/Y）
   */
  _getTouchPos(touch) {
    if (!touch) return { x: 0, y: 0 }
    return {
      x: touch.pageX ?? touch.clientX ?? 0,
      y: touch.pageY ?? touch.clientY ?? 0
    }
  }

  /**
   * 检查元素是否包含指定类名（替代 closest，适配小程序）
   */
  _hasClass(target, className) {
    if (!target) return false
    // 小程序中 target 可能是 dataset 对象
    if (typeof target === 'object' && target.dataset) {
      // 通过 dataset 判断
      return target.dataset.class?.includes(className)
    }
    // 如果是 DOM 元素（Web环境）
    if (target.classList) {
      return target.classList.contains(className)
    }
    return false
  }

  /**
   * 检查是否是交互元素（适配小程序）
   */
  _isInteractiveElement(target) {
    if (!target) return false

    // 小程序中通过 dataset 判断
    if (target.dataset) {
      const tag = target.dataset.tag || ''
      const className = target.dataset.class || ''
      return ['input', 'textarea', 'button', 'label'].includes(tag) ||
             className.includes('option-item') ||
             className.includes('action-btn')
    }

    // Web环境使用传统方式
    const tagName = target.tagName?.toLowerCase()
    return ['input', 'textarea', 'button', 'label', 'a'].includes(tagName)
  }

  // 日志方法
  log(...args) {
    if (this.isDev) {
      console.log(...args)
    }
  }

  error(...args) {
    console.error(...args)
  }

  /**
   * 优化的滑动处理 - 支持动态长度的activeQuestions
   * 支持两种调用方式：
   * 1. handleSwipe(sliderEvent, currentState) - 传统方式
   * 2. handleSwipe(options) - 简化方式，适用于 commonQuestion.vue
   */
  async handleSwipe(optionsOrSliderEvent, currentState) {
    // 检测调用方式
    const isSimplifiedCall = arguments.length === 1;
    const options = isSimplifiedCall ? (optionsOrSliderEvent || {}) : {};
    const sliderEvent = isSimplifiedCall ? options.event : optionsOrSliderEvent;
    const state = isSimplifiedCall
      ? {
          swiperCurrentIndex: options.currentIndex ?? 0,
          swiperList: options.items ?? [],
          virtualCurrentIndex: options.swiperDisplayIndex ?? 0,
          activeQuestionsLength: options.activeQuestionsLength ?? 3
        }
      : (currentState || {});

    // 入参校验
    let {
      swiperCurrentIndex,
      swiperList,
      virtualCurrentIndex,
      activeQuestionsLength
    } = state;

    // 类型校验和兜底
    swiperCurrentIndex = typeof swiperCurrentIndex === 'number' ? swiperCurrentIndex : 0;
    swiperList = Array.isArray(swiperList) ? swiperList : [];
    virtualCurrentIndex = typeof virtualCurrentIndex === 'number' ? virtualCurrentIndex : 0;
    activeQuestionsLength = typeof activeQuestionsLength === 'number' ? activeQuestionsLength : 3;

    // 安全获取swiperIndex
    const swiperIndex = sliderEvent?.detail?.current ?? 0;
    const now = Date.now();
    const listLength = swiperList.length;

    // 空列表校验
    if (listLength <= 1) {
      return isSimplifiedCall
        ? { needProgrammaticJump: false, reason: 'empty_or_single_item' }
        : { handled: false, reason: 'empty_or_single_item' };
    }

    // 参数合法性校验
    if (swiperCurrentIndex < 0 || swiperCurrentIndex >= listLength) {
      this.error('[SwipeHandler] 当前索引越界:', { swiperCurrentIndex, listLength });
      return isSimplifiedCall
        ? { needProgrammaticJump: false, reason: 'invalid_current_index' }
        : { handled: false, reason: 'invalid_current_index' };
    }

    if (activeQuestionsLength < 2 || activeQuestionsLength > 3) {
      this.error('[SwipeHandler] activeQuestionsLength 不合法:', activeQuestionsLength);
      return isSimplifiedCall
        ? { needProgrammaticJump: false, reason: 'invalid_active_length' }
        : { handled: false, reason: 'invalid_active_length' };
    }

    this.log('[SwipeHandler] 开始处理:', {
      swiperIndex,
      swiperCurrentIndex,
      listLength,
      activeQuestionsLength,
      isSwiping: this.isSwiping
    });

    // 滑动状态锁，防止并发滑动
    if (this.isSwiping) {
      this.log('[SwipeHandler] 滑动进行中，阻止并发');
      return isSimplifiedCall
        ? { needProgrammaticJump: false, reason: 'swiping_in_progress' }
        : { handled: false, reason: 'swiping_in_progress' };
    }

    // 增强防抖处理：检测连续快速滑动
    if (this.lastSwipeTime > 0 && now - this.lastSwipeTime < this.config.debounceTime) {
      this.rapidSwipeCount++;
      this.log('[SwipeHandler] 快速滑动检测:', {
        elapsed: now - this.lastSwipeTime,
        rapidSwipeCount: this.rapidSwipeCount
      });

      // 设置快速滑动重置定时器
      if (this.rapidSwipeResetTimer) {
        clearTimeout(this.rapidSwipeResetTimer);
      }
      this.rapidSwipeResetTimer = setTimeout(() => {
        this.rapidSwipeCount = 0;
        this.log('[SwipeHandler] 快速滑动计数重置');
      }, this.config.rapidSwipeResetTime);

      // 如果连续快速滑动超过阈值，加强防抖
      if (this.rapidSwipeCount > this.config.rapidSwipeThreshold) {
        this.log('[SwipeHandler] 连续快速滑动，加强防抖');
        return isSimplifiedCall
          ? { needProgrammaticJump: false, reason: 'rapid_swipe' }
          : { handled: false, reason: 'rapid_swipe' };
      }
    } else {
      // 重置快速滑动计数
      this.rapidSwipeCount = 0;
    }

    // 设置滑动状态锁
    this.isSwiping = true;

    // 启动锁状态监控
    this._startLockMonitor();

    // 设置滑动超时保护（防止死锁）
    if (this.swipeTimeout) {
      clearTimeout(this.swipeTimeout);
    }
    this.swipeTimeout = setTimeout(() => {
      this.log('[SwipeHandler] 滑动超时，自动释放锁');
      this.isSwiping = false;
      this.swipeLockTime = 0;
    }, this.config.swipeTimeout);

    // 边界验证：swiperIndex 必须在有效范围内
    if (swiperIndex < 0 || swiperIndex >= activeQuestionsLength) {
      this.log('[SwipeHandler] 边界验证失败:', { swiperIndex, activeQuestionsLength });
      this.isSwiping = false;
      return isSimplifiedCall
        ? { needProgrammaticJump: false }
        : { handled: false, reason: 'invalid_range' };
    }

    try {
      return this._processSwipe({
        swiperIndex,
        swiperCurrentIndex,
        listLength,
        activeQuestionsLength,
        isSimplifiedCall,
        now
      });
    } finally {
      // 第一层：立即释放
      this.isSwiping = false;
      this.swipeLockTime = 0;
      if (this.swipeTimeout) {
        clearTimeout(this.swipeTimeout);
        this.swipeTimeout = null;
      }
      // 第二层：延迟兜底释放（防止try内逻辑异常导致锁未释放）
      setTimeout(() => {
        if (this.isSwiping) {
          this.error('[SwipeHandler] 延迟检测到锁未释放，强制释放');
          this.isSwiping = false;
          this.swipeLockTime = 0;
        }
      }, this.config.swipeRecoveryDelay || 300);
    }
  }

  /**
   * 私有方法：处理滑动核心逻辑
   */
  _processSwipe({ swiperIndex, swiperCurrentIndex, listLength, activeQuestionsLength, isSimplifiedCall, now }) {
    // 方向计算 - 考虑动态长度和边界情况
    const direction = this._calculateDirection(swiperIndex, swiperCurrentIndex, listLength, activeQuestionsLength);

    if (!direction) {
      return isSimplifiedCall
        ? { needProgrammaticJump: false }
        : { handled: false, reason: 'no_direction' };
    }

    if (direction.reason) {
      // 边界情况
      this.log('[SwipeHandler]', direction.reason);
      return isSimplifiedCall
        ? { needProgrammaticJump: false }
        : { handled: true, reason: direction.reason, isBoundary: true };
    }

    // 计算目标索引
    const targetIndex = this._calculateTargetIndex(direction.value, swiperCurrentIndex, listLength);

    this.log('[SwipeHandler] 计算目标索引:', { direction: direction.value, swiperCurrentIndex, targetIndex });

    // 更新状态
    this.lastSwipeTime = now;
    this.lastProcessedIndex = targetIndex;

    this.log('[SwipeHandler] 返回结果:', { newIndex: targetIndex, swiperDisplayIndex: targetIndex });

    // 返回结果
    return isSimplifiedCall
      ? { needProgrammaticJump: false, swiperDisplayIndex: targetIndex, newIndex: targetIndex }
      : { handled: true, targetIndex, direction: direction.value, swiperIndex };
  }

  /**
   * 私有方法：计算滑动方向
   * @returns {Object} { value: 'next'|'prev', reason?: string }
   */
  _calculateDirection(swiperIndex, currentIndex, listLength, activeQuestionsLength) {
    if (currentIndex === 0) {
      // 第一题的特殊处理（activeQuestions只有2个：当前题和后一题）
      if (swiperIndex === 0) {
        return { reason: 'first_question' };
      } else if (swiperIndex === 1) {
        return { value: 'next' };
      }
    } else if (currentIndex === listLength - 1) {
      // 最后一题的特殊处理（activeQuestions只有2个：前一题和当前题）
      // 👉 使用 === 精确匹配最后一题
      if (swiperIndex === 0) {
        // 最后一题滑prev，强制校验targetIndex
        const targetIndex = currentIndex - 1;
        if (targetIndex < 0) return { reason: 'first_question' };
        return { value: 'prev' };
      } else if (swiperIndex === 1) {
        return { reason: 'last_question' };
      }
    } else {
      // 中间题的标准处理（activeQuestions有3个：前一题、当前题、后一题）
      if (swiperIndex === 0) {
        return { value: 'prev' };
      } else if (swiperIndex === 2) {
        return { value: 'next' };
      } else if (swiperIndex === 1) {
        return { reason: 'center_position' };
      }
    }
    return null;
  }

  /**
   * 私有方法：计算目标索引（增加边界校验）
   */
  _calculateTargetIndex(direction, currentIndex, listLength) {
    let targetIndex = currentIndex;

    if (direction === 'next') {
      targetIndex = Math.min(currentIndex + 1, listLength - 1);
    } else if (direction === 'prev') {
      // 最后一题滑prev时，仅减1，且不低于0
      targetIndex = Math.max(currentIndex - 1, 0);
    }

    // 新增：校验targetIndex是否在合法范围
    if (targetIndex < 0 || targetIndex >= listLength) {
      this.error('[SwipeHandler] 目标索引越界', { targetIndex, listLength, direction, currentIndex });
      return currentIndex; // 越界时返回原索引，避免跳题
    }

    return targetIndex;
  }

  /**
   * 设置回调函数
   */
  setCallbacks(callbacks) {
    Object.entries(callbacks).forEach(([key, fn]) => {
      if (fn && typeof fn === 'function') {
        this.callbacks[key] = fn;
      }
    });
  }
  
  /**
   * 更新配置（带合法性校验）
   * @param {Object} config - 配置对象
   */
  setConfig(config) {
    if (!config || typeof config !== 'object') {
      this.error('[SwipeHandler.setConfig] 配置必须是对象');
      return;
    }

    const validKeys = [
      'swipeThreshold', 'verticalThreshold', 'clickThreshold',
      'maxSwipeDuration', 'debounceTime', 'rapidSwipeThreshold',
      'swipeTimeout', 'rapidSwipeResetTime', 'transitionCompleteThreshold',
      'touchEndDelay', 'swipeRecoveryDelay'
    ];

    const numericConfigs = [
      'swipeThreshold', 'verticalThreshold', 'clickThreshold',
      'maxSwipeDuration', 'debounceTime', 'rapidSwipeThreshold',
      'swipeTimeout', 'rapidSwipeResetTime', 'transitionCompleteThreshold',
      'touchEndDelay', 'swipeRecoveryDelay'
    ];

    Object.entries(config).forEach(([key, value]) => {
      // 校验键名合法性
      if (!validKeys.includes(key)) {
        this.error(`[SwipeHandler.setConfig] 非法配置项: ${key}`);
        return;
      }

      // 校验数值类型和范围
      if (numericConfigs.includes(key)) {
        if (typeof value !== 'number' || isNaN(value)) {
          this.error(`[SwipeHandler.setConfig] ${key} 必须是有效数字`);
          return;
        }
        if (value < 0) {
          this.error(`[SwipeHandler.setConfig] ${key} 不能为负数`);
          return;
        }
        // 时间相关配置不能超过10秒
        if (key.includes('Time') && value > 10000) {
          this.error(`[SwipeHandler.setConfig] ${key} 不能超过10秒`);
          return;
        }
      }

      this.config[key] = value;
      this.log(`[SwipeHandler.setConfig] ${key} 设置为 ${value}`);
    });
  }
  
  /**
   * 手势识别 - touchstart
   * 应在 swiper 的 @touchstart 事件中调用
   * @param {Object} e - 触摸事件对象
   * @param {Object} options - 可选参数
   * @param {number} options.screenWidth - 屏幕宽度（用于热区判断）
   */
  handleTouchStart(e, options = {}) {
    const touch = e.touches?.[0];
    if (!touch) {
      return { handled: false, reason: 'no_touch' };
    }

    const pos = this._getTouchPos(touch);
    const { screenWidth = 375 } = options;

    // 热区判断：仅右侧指定比例区域可滑动
    const hotZoneStart = screenWidth * (1 - this.config.swipeHotZoneRatio);
    const isInHotZone = pos.x >= hotZoneStart;

    this.gestureState.startX = pos.x;
    this.gestureState.startY = pos.y;
    this.gestureState.startTime = Date.now();
    this.gestureState.isTouching = true;
    this.gestureState.isVerticalScrolling = false;
    this.gestureState.isValidSwipe = false;
    this.gestureState.touchTarget = e.target;
    this.gestureState.isInHotZone = isInHotZone;
    this.gestureState.directionLocked = false; // 方向锁定标志


    // 触发回调
    if (this.callbacks.onSwipeStart) {
      this.callbacks.onSwipeStart({
        x: pos.x,
        y: pos.y,
        target: e.target,
        isInHotZone
      });
    }

    // 检查是否是交互元素，如果是则建议禁用滚动
    const isInteractive = this._isInteractiveElement(e.target);

    return {
      handled: true,
      state: this.gestureState,
      isInteractive,
      isInHotZone,
      shouldDisableScroll: isInteractive || !isInHotZone // 不在热区也禁用滑动
    };
  }

  /**
   * 手势识别 - touchstop（兼容小程序）
   * 用于重置手势状态
   */
  handleTouchStop(e) {
    this.log('[SwipeHandler.handleTouchStop] 手势停止');

    // 触发 touchend 回调（如果有）
    if (this.callbacks.onSwipeEnd) {
      this.callbacks.onSwipeEnd({
        state: this.gestureState,
        reason: 'touchstop'
      });
    }

    // 全量重置手势状态
    this.resetGestureState();

    return {
      handled: true,
      state: this.gestureState
    };
  }
  
  /**
   * 手势识别 - touchmove
   * 应在 swiper 的 @touchmove 事件中调用
   */
  handleTouchMove(e) {
    if (!this.gestureState.isTouching) {
      return { handled: false, reason: 'not_touching' };
    }

    // 不在热区，直接返回
    if (!this.gestureState.isInHotZone) {
      return { handled: true, reason: 'not_in_hot_zone', shouldDisableSwipe: true };
    }

    const touch = e.touches?.[0];
    if (!touch) {
      return { handled: false, reason: 'no_touch' };
    }

    const pos = this._getTouchPos(touch);
    const deltaX = pos.x - this.gestureState.startX;
    const deltaY = pos.y - this.gestureState.startY;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);

    // 方向锁定：一旦判定方向，全程锁定
    if (this.config.directionLockEnabled && !this.gestureState.directionLocked) {
      if (absY > absX && absY > this.config.verticalThreshold) {
        // 判定为垂直滚动，锁定方向
        this.gestureState.directionLocked = true;
        this.gestureState.isVerticalScrolling = true;
        this.log('[SwipeHandler.handleTouchMove] 方向锁定：垂直滚动');
      } else if (absX > absY && absX > this.config.swipeThreshold) {
        // 判定为水平滑动，锁定方向
        this.gestureState.directionLocked = true;
        this.gestureState.isValidSwipe = true;
        this.log('[SwipeHandler.handleTouchMove] 方向锁定：水平滑动');
      }
    }

    // 如果已锁定为垂直滚动，全程禁用水平滑动
    if (this.gestureState.isVerticalScrolling) {
      // 触发垂直滚动回调
      if (this.callbacks.onVerticalScroll) {
        this.callbacks.onVerticalScroll({ deltaX, deltaY });
      }

      return {
        handled: true,
        isVerticalScrolling: true,
        shouldDisableSwipe: true,
        directionLocked: true
      };
    }

    // 检测点击目标是否是交互元素（按钮、输入框等）
    const target = this.gestureState.touchTarget;
    if (target && this._isInteractiveElement(target)) {
      if (absX < this.config.clickThreshold) {
        // 如果点击的是交互元素且移动距离小于阈值，认为是点击而非滑动
        this.log('[SwipeHandler.handleTouchMove] 检测到交互元素点击');
        return {
          handled: true,
          isClick: true,
          shouldDisableSwipe: true
        };
      }
    }

    // 滑动阈值判断（非垂直滚动时）
    if (absX > this.config.swipeThreshold && !this.gestureState.isVerticalScrolling) {
      this.log('[SwipeHandler.handleTouchMove] 水平滑动检测:', { deltaX, deltaY });

      // 触发滑动移动回调
      if (this.callbacks.onSwipeMove) {
        this.callbacks.onSwipeMove({ deltaX, deltaY, absX, absY });
      }

      return {
        handled: true,
        isHorizontalSwipe: true,
        deltaX,
        deltaY,
        directionLocked: this.gestureState.directionLocked
      };
    }

    return { handled: true, isPending: true };
  }
  
  /**
   * 🔑 新增：手势识别 - touchend
   * 应在 swiper 的 @touchend 事件中调用
   */
  handleTouchEnd(e) {
    if (!this.gestureState.isTouching) {
      return { handled: false, reason: 'not_touching' };
    }
    
    const touch = e.changedTouches[0];
    const deltaX = touch.clientX - this.gestureState.startX;
    const deltaY = touch.clientY - this.gestureState.startY;
    const duration = Date.now() - this.gestureState.startTime;
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);
    

    // 重置触摸状态
    this.gestureState.isTouching = false;
    
    // 如果是垂直滚动，直接返回
    if (this.gestureState.isVerticalScrolling) {
      // 触发垂直滚动结束回调
      if (this.callbacks.onVerticalScroll) {
        this.callbacks.onVerticalScroll({ deltaX, deltaY, ended: true });
      }

      this.gestureState.isVerticalScrolling = false;
      this.gestureState.directionLocked = false; // 重置方向锁定
      return {
        handled: true,
        isVerticalScroll: true,
        shouldDisableSwipe: false
      };
    }

    // 重置方向锁定
    this.gestureState.directionLocked = false;
    
    // 检测是否是有效的水平滑动
    // 条件：水平距离 > 阈值，水平距离 > 垂直距离，持续时间 < 最大持续时间
    const isValidSwipe = absX > this.config.swipeThreshold && 
                         absX > absY && 
                         duration < this.config.maxSwipeDuration;
    
    if (isValidSwipe) {
      this.gestureState.isValidSwipe = true;
      
      // 触发滑动前回调（用于保存答案等）
      if (this.callbacks.onBeforeSwipe) {
        this.callbacks.onBeforeSwipe({ direction: deltaX > 0 ? 'prev' : 'next' });
      }
    }
    
    // 触发滑动结束回调
    if (this.callbacks.onSwipeEnd) {
      this.callbacks.onSwipeEnd({
        deltaX,
        deltaY,
        duration,
        isValidSwipe,
        direction: deltaX > 0 ? 'prev' : 'next'
      });
    }
    
    return {
      handled: true,
      isValidSwipe,
      isClick: !isValidSwipe && absX < this.config.clickThreshold,
      direction: deltaX > 0 ? 'prev' : 'next',
      shouldDisableSwipe: false
    };
  }
  
  /**
   * 🔑 新增：检查是否应该禁用滑动
   */
  shouldDisableSwipe() {
    if (!this.gestureState.isTouching) {
      return false;
    }
    
    // 如果正在垂直滚动，禁用水平滑动
    if (this.gestureState.isVerticalScrolling) {
      return true;
    }
    
    return false;
  }
  
  /**
   * 🔑 新增：获取手势状态
   */
  getGestureState() {
    return { ...this.gestureState };
  }
  
  /**
   * 🔑 新增：重置手势状态
   */
  resetGestureState() {
    this.gestureState = {
      startX: 0,
      startY: 0,
      startTime: 0,
      isVerticalScrolling: false,
      isTouching: false,
      touchTarget: null,
      isValidSwipe: false
    };
  }

  /**
   * 🔑 新增：处理 swiper transition 事件（滑动过程中）
   * @param {Object} e - transition 事件对象
   * @param {Object} context - 上下文信息
   * @returns {Object} 处理结果
   */
  handleSwiperTransition(e, context = {}) {
    const dx = e.detail?.dx || 0;
    const { currentIndex = 0, totalQuestions = 0 } = context;
    
    // 防护：如果 dx 接近屏幕宽度（375），说明滑动已完成
    if (Math.abs(dx) > 350) {
      return {
        handled: true,
        isComplete: true,
        shouldResetSwiping: true
      };
    }

    // 设置滑动标志
    this.isUserSwiping = true;

    // 边界检查
    if (totalQuestions <= 0) {
      return { handled: false, reason: 'no_questions' };
    }

    // 基于当前题号计算预测索引
    let predictedQuestionNum = currentIndex;

    // dx > 0 表示向左滑（上一题），dx < 0 表示向右滑（下一题）
    if (Math.abs(dx) > this.config.swipeThreshold) {
      if (dx > 0) {
        // 向左滑，上一题
        if (currentIndex === 0) {
          return { handled: true, isBoundary: true, direction: 'prev_blocked' };
        }
        predictedQuestionNum = Math.max(currentIndex - 1, 0);
      } else {
        // 向右滑，下一题
        if (currentIndex >= totalQuestions - 1) {
          return { handled: true, isBoundary: true, direction: 'next_blocked' };
        }
        predictedQuestionNum = Math.min(currentIndex + 1, totalQuestions - 1);
      }
    }

    return {
      handled: true,
      isComplete: false,
      predictedIndex: predictedQuestionNum,
      direction: dx > 0 ? 'prev' : 'next',
      dx
    };
  }

  /**
   * 处理 swiper change 事件（滑动完成）
   * @param {Object} e - change 事件对象
   * @param {Object} context - 上下文信息
   * @returns {Promise<Object>} 处理结果
   */
  async handleSwiperChange(e, context = {}) {
    // 入参校验
    let {
      currentIndex = 0,
      swiperList = [],
      activeQuestionsLength = 3,
      needProgrammaticJump = false,
      isLoading = false
    } = context;

    // 类型校验
    currentIndex = typeof currentIndex === 'number' ? currentIndex : 0;
    swiperList = Array.isArray(swiperList) ? swiperList : [];
    activeQuestionsLength = typeof activeQuestionsLength === 'number' ? activeQuestionsLength : 3;

    try {
      // 检测是否是程序化跳转触发的事件
      if (this.isProgrammaticJump) {
        this.log('[SwipeHandler.handleSwiperChange] 跳过程序化跳转事件');
        this.isProgrammaticJump = false; // 重置标记
        return { handled: true, reason: 'programmatic_jump', shouldResetFlag: true };
      }

      // 如果设置了程序化跳转标志，跳过处理
      if (needProgrammaticJump) {
        return { handled: true, reason: 'programmatic_jump', shouldResetFlag: true };
      }

      // 如果正在加载，返回等待状态
      if (isLoading) {
        return { handled: true, reason: 'loading', shouldWait: true };
      }

      const swiperIndex = e.detail?.current ?? 0;
      const listLength = swiperList.length;
      const now = Date.now();

      // 保存上一次的 swiperIndex
      this._lastSwiperIndex = this._currentSwiperIndex;
      // 更新当前的 swiperIndex
      this._currentSwiperIndex = swiperIndex;

      // 滑动状态锁检查
      if (this.isSwiping) {
        return { handled: false, reason: 'swiping_in_progress' };
      }

      // 防抖检查
      if (this.lastSwipeTime > 0 && now - this.lastSwipeTime < this.config.debounceTime) {
        this.rapidSwipeCount++;
        if (this.rapidSwipeCount > this.config.rapidSwipeThreshold) {
          return { handled: false, reason: 'rapid_swipe' };
        }
      } else {
        this.rapidSwipeCount = 0;
      }

      // 设置滑动锁
      this.isSwiping = true;
      this.lastSwipeTime = now;

      // 边界验证
      if (swiperIndex < 0 || swiperIndex >= activeQuestionsLength) {
        this.isSwiping = false;
        return { handled: false, reason: 'invalid_range' };
      }

      // 方向计算
      let direction = null;
      let isBoundary = false;

      if (currentIndex === 0) {
        // 第一题
        if (swiperIndex === 0) {
          isBoundary = true;
          this.isSwiping = false;
          return { handled: true, reason: 'first_question', isBoundary: true };
        } else if (swiperIndex === 1) {
          direction = 'next';
        }
      } else if (currentIndex >= listLength - 1) {
        // 最后一题
        if (swiperIndex === 0) {
          direction = 'prev';
        } else if (swiperIndex === 1) {
          isBoundary = true;
          this.isSwiping = false;
          return { handled: true, reason: 'last_question', isBoundary: true };
        }
      } else {
        // 中间题
        if (swiperIndex === 0) {
          direction = 'prev';
        } else if (swiperIndex === 2) {
          direction = 'next';
        } else if (swiperIndex === 1) {
          // 检查是否是从其他位置滑到中间位置
          if (this._lastSwiperIndex !== 1 && this._lastSwiperIndex !== -1) {
            // 从其他位置滑到中间位置，视为滑到当前题
            this.isSwiping = false;
            return { 
              handled: true, 
              shouldUpdate: true, 
              newIndex: currentIndex,
              direction: this._lastSwiperIndex < 1 ? 'next' : 'prev'
            };
          } else {
            // 一直停在中间位置，不做处理
            this.isSwiping = false;
            return { handled: true, reason: 'center_position' };
          }
        }
      }

      if (!direction) {
        this.isSwiping = false;
        return { handled: false, reason: 'no_direction' };
      }

      // 计算目标索引
      const targetIndex = direction === 'next'
        ? Math.min(currentIndex + 1, listLength - 1)
        : Math.max(currentIndex - 1, 0);

      // 验证目标索引
      if (targetIndex < 0 || targetIndex >= listLength) {
        this.isSwiping = false;
        return { handled: false, reason: 'invalid_target' };
      }

      // 释放滑动锁
      this.isSwiping = false;

      // 重置滑动路径状态
      this._lastSwiperIndex = -1;
      this._currentSwiperIndex = -1;

      return {
        handled: true,
        newIndex: targetIndex,
        direction,
        shouldUpdate: targetIndex !== currentIndex
      };

    } catch (error) {
      this.isSwiping = false;
      // 重置滑动路径状态
      this._lastSwiperIndex = -1;
      this._currentSwiperIndex = -1;
      return { handled: false, reason: 'error', error: error.message };
    }
  }

  /**
   * 🔑 新增：处理边界状态切换
   * @param {number} oldLength - 之前的 activeQuestions 长度
   * @param {number} newLength - 新的 activeQuestions 长度
   * @param {Object} context - 上下文信息
   * @returns {Object} 处理结果
   */
  handleBoundaryTransition(oldLength, newLength, context = {}) {
    const { currentIndex = 0, listLength = 0 } = context;

    // 从3个题目变为2个题目（中间题→边界题）
    if (oldLength === 3 && newLength === 2) {
      if (currentIndex === 0) {
        // 滑动到第一题
        return {
          handled: true,
          isBoundary: true,
          boundary: 'first',
          needProgrammaticJump: true,
          swiperDisplayIndex: 0
        };
      } else if (currentIndex >= listLength - 1) {
        // 滑动到最后一题
        return {
          handled: true,
          isBoundary: true,
          boundary: 'last',
          needProgrammaticJump: true,
          swiperDisplayIndex: 1
        };
      }
    }
    // 从2个题目变为3个题目（边界题→中间题）
    else if (oldLength === 2 && newLength === 3) {
      return {
        handled: true,
        isBoundary: false,
        swiperDisplayIndex: 1
      };
    }

    return { handled: false };
  }

  /**
   * 智能防抖处理（定时器挂载到实例，便于清理）
   */
  smartDebounce(handler, delay = 150) {
    const self = this;
    let lastCallTime = 0;

    return function(...args) {
      const now = Date.now();

      // 立即执行条件：首次调用或间隔足够长
      if (now - lastCallTime > delay * 2) {
        lastCallTime = now;
        return handler.apply(this, args);
      }

      // 清除之前的定时器并设置新的（使用实例上的定时器）
      if (self.debounceTimeoutId) {
        clearTimeout(self.debounceTimeoutId);
      }
      self.debounceTimeoutId = setTimeout(() => {
        lastCallTime = Date.now();
        handler.apply(this, args);
      }, delay);
    };
  }

  /**
   * 滑动惯性处理
   */
  handleSwipeInertia(currentIndex, targetIndex) {
    const distance = Math.abs(targetIndex - currentIndex);
    
    // 短距离滑动直接处理
    if (distance <= 1) {
      return { immediate: true, targetIndex };
    }
    
    // 长距离滑动需要动画过渡
    const direction = targetIndex > currentIndex ? 1 : -1;
    const steps = [];
    let current = currentIndex;
    
    while (current !== targetIndex) {
      current += direction;
      steps.push(current);
    }
    
    return { immediate: false, steps, targetIndex };
  }

  /**
   * 让出控制权
   */
  yieldControl() {
    return new Promise(resolve => {
      // 微信小程序兼容：使用 setTimeout 替代 requestAnimationFrame
      const nextFrame = typeof requestAnimationFrame === 'function' ? requestAnimationFrame : (cb) => setTimeout(cb, 0);
      nextFrame(resolve);
    });
  }

  /**
   * 重置处理器
   */
  reset() {
    this.lastSwipeTime = 0;
    this.lastProcessedIndex = -1;
    this.isSwiping = false;
    this.swipeLockTime = 0;
    this.rapidSwipeCount = 0;
    if (this.swipeTimeout) {
      clearTimeout(this.swipeTimeout);
      this.swipeTimeout = null;
    }
    if (this.rapidSwipeResetTimer) {
      clearTimeout(this.rapidSwipeResetTimer);
      this.rapidSwipeResetTimer = null;
    }
    if (this.lockMonitorTimer) {
      clearTimeout(this.lockMonitorTimer);
      this.lockMonitorTimer = null;
    }
  }

  /**
   * 启动锁状态监控
   */
  _startLockMonitor() {
    // 清理旧的监控定时器
    if (this.lockMonitorTimer) {
      clearTimeout(this.lockMonitorTimer);
    }

    // 记录锁获取时间
    this.swipeLockTime = Date.now();

    // 启动监控：每500ms检查一次锁状态
    this.lockMonitorTimer = setInterval(() => {
      if (!this.isSwiping) {
        // 锁已释放，停止监控
        clearInterval(this.lockMonitorTimer);
        this.lockMonitorTimer = null;
        return;
      }

      const lockDuration = Date.now() - this.swipeLockTime;
      this.log('[SwipeHandler._startLockMonitor] 锁持有时间:', lockDuration);

      // 如果锁持有超过3秒，认为是异常，自动释放
      if (lockDuration > 3000) {
        this.error('[SwipeHandler._startLockMonitor] 锁持有超时，自动释放');
        this.forceRelease();
      }
    }, 500);
  }

  /**
   * 强制释放滑动锁（用于异常恢复）
   */
  forceRelease() {
    this.log('[SwipeHandler] 强制释放滑动锁');

    // 释放滑动锁
    this.isSwiping = false;
    this.swipeLockTime = 0;

    // 清理滑动超时定时器
    if (this.swipeTimeout) {
      clearTimeout(this.swipeTimeout);
      this.swipeTimeout = null;
    }

    // 清理锁监控定时器
    if (this.lockMonitorTimer) {
      clearInterval(this.lockMonitorTimer);
      this.lockMonitorTimer = null;
    }

    // 重置快速滑动计数
    this.rapidSwipeCount = 0;
    if (this.rapidSwipeResetTimer) {
      clearTimeout(this.rapidSwipeResetTimer);
      this.rapidSwipeResetTimer = null;
    }

    // 重置手势状态
    this.resetGestureState();

    // 重置程序化跳转标记
    this.isProgrammaticJump = false;

    this.log('[SwipeHandler] 滑动锁释放完成');
  }

  /**
   * 获取当前滑动状态
   */
  getSwipeState() {
    return {
      isSwiping: this.isSwiping,
      swipeLockTime: this.swipeLockTime,
      lockDuration: this.isSwiping ? Date.now() - this.swipeLockTime : 0,
      rapidSwipeCount: this.rapidSwipeCount,
      lastSwipeTime: this.lastSwipeTime,
      lastProcessedIndex: this.lastProcessedIndex
    };
  }

  /**
   * 销毁处理器 - 清理所有定时器和状态
   * 应在组件卸载时调用
   */
  destroy() {
    this.log('[SwipeHandler] 销毁处理器');

    // 重置所有状态
    this.lastSwipeTime = 0;
    this.lastProcessedIndex = -1;
    this.isSwiping = false;
    this.rapidSwipeCount = 0;

    // 清理所有定时器
    if (this.swipeTimeout) {
      clearTimeout(this.swipeTimeout);
      this.swipeTimeout = null;
    }

    if (this.rapidSwipeResetTimer) {
      clearTimeout(this.rapidSwipeResetTimer);
      this.rapidSwipeResetTimer = null;
    }

    // 清理 smartDebounce 定时器
    if (this.debounceTimeoutId) {
      clearTimeout(this.debounceTimeoutId);
      this.debounceTimeoutId = null;
    }

    // 重置手势状态
    this.resetGestureState();

    // 清空回调
    this.callbacks = {
      onSwipeStart: null,
      onSwipeMove: null,
      onSwipeEnd: null,
      onVerticalScroll: null,
      onBeforeSwipe: null
    };

    // 重置程序化跳转标记
    this.isProgrammaticJump = false;

    this.log('[SwipeHandler] 销毁完成');
  }
}

// 导出类，由组件内实例化（避免全局单例污染）
export default SwipeHandler
