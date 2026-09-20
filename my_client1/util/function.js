import messageApi from '@/util/api/message.js'
import configApi from '@/util/api/config.js'

export default {
	showToast: function(title, time = 3000, mask = false) {
		uni.showToast({
			title: title,
			duration: time,
			icon: "none",
			mask: mask,
		});
	},

	/**
	 * 统一处理微信小程序订阅消息授权
	 * @param {Object} options - 配置选项
	 * @param {Array} options.tmplIds - 模板ID列表
	 * @param {Function} options.apiCall - 授权成功后调用的后端API方法
	 * @param {Object} options.apiParams - 后端API调用参数
	 * @param {Object} options.templateData - 模板数据（可选）
	 * @param {Boolean} options.isSubscribed - 当前是否已订阅（可选）
	 * @param {Function} options.successCallback - 成功回调（可选）
	 * @param {Function} options.failCallback - 失败回调（可选）
	 * @param {Function} options.completeCallback - 完成回调（可选）
	 */
handleSubscribeMessage(options) {
	const {
		tmplIds,
		apiCall,
		apiParams,
		templateData,
		isSubscribed = false,
		successCallback,
		failCallback,
		completeCallback
	} = options;
		
		// 只在微信小程序平台支持订阅消息
		if (this.currentPlatform() !== 'wechat_mini') {
			this.showToast("该功能仅支持微信小程序");
			// 调用完成回调
			if (completeCallback) {
				completeCallback();
			}
			return;
		}

		// 前置校验全局订阅开关
		this.checkSubscribeSetting().then(setting => {
			if (!setting.mainSwitch) {
				this.showToast('请先开启微信订阅消息全局开关\n路径：微信 > 我 > 设置 > 通知 > 订阅消息')
				// 调用完成回调
				if (completeCallback) {
					completeCallback();
				}
				return
			}

			// 检查是否有模板ID
			if (!tmplIds || tmplIds.length === 0) {
				this.showToast('暂无可用的订阅模板，请联系管理员配置');
				// 调用完成回调
				if (completeCallback) {
					completeCallback();
				}
				return;
			}
			
			// 订阅前的引导说明
			uni.showModal({
				title: '📢 订阅消息提醒',
				content: '🎉 订阅后，我们将为您提供贴心的提醒服务：\n\n✅ 及时获取最新动态\n✅ 不错过重要信息\n✅ 个性化服务推送\n\n📱 请在接下来的弹窗中点击"允许"，开启消息提醒功能。\n\n🙏 感谢您的支持！',
				confirmText: '继续订阅',
				cancelText: '暂不订阅',
				confirmColor: '#01BEFF',
				cancelColor: '#909399',
				success: (modalRes) => {
					if (modalRes.confirm) {
						// 直接调用订阅消息API，微信会自动处理权限逻辑
						uni.requestSubscribeMessage({
							tmplIds: tmplIds,
							success: (res) => {
								
								// 记录订阅结果
								const subscribeResults = {};
								let acceptedCount = 0;
								let rejectedCount = 0;
								let bannedCount = 0;
								
								// 处理每个模板的订阅结果
								for (let tmplId in res) {
									if (tmplId !== 'errMsg') {
										const status = res[tmplId];
										subscribeResults[tmplId] = status;
										
										// 统计不同状态的数量
										if (status === 'accept' || status === 'acceptWithAudio' || status === 'acceptWithAlert') {
											acceptedCount++;
										} else if (status === 'reject') {
											rejectedCount++;
										} else if (status === 'ban') {
											bannedCount++;
										}
									}
								}
								
								// 根据订阅结果提供不同的反馈
								if (acceptedCount > 0) {
									if (acceptedCount === tmplIds.length) {
										this.showToast('🎉 订阅成功！\n\n我们将及时为您推送提醒消息');
									} else {
										this.showToast(`🎊 成功订阅${acceptedCount}个模板\n\n部分模板订阅失败，您仍将收到已订阅模板的提醒`);
									}
									
									// 调用后端API保存订阅状态和订阅结果
									// 检查isSubscribed参数，避免重复插入导致唯一键冲突
									if (apiCall && !isSubscribed) {
										apiCall({
											...apiParams,
											template_data: templateData,
											subscribe_results: JSON.stringify(subscribeResults)
										});
									}
									
									// 调用成功回调
									if (successCallback) {
										successCallback({ acceptedCount, subscribeResults });
									}
								} else {
									let errorMsg = '💔 订阅失败\n\n请在微信通知权限中允许消息通知，\n然后重新尝试订阅';
									
									if (bannedCount > 0) {
										errorMsg = '💔 订阅失败\n\n您已禁止订阅消息，请前往微信设置开启权限';
									} else if (rejectedCount > 0) {
										errorMsg = '💔 订阅失败\n\n请在微信通知权限中允许消息通知，\n然后重新尝试订阅';
									} else if (res.errMsg && res.errMsg.includes('fail auth deny')) {
										errorMsg = '💔 订阅失败\n\n您已拒绝订阅授权，请在微信设置中开启';
									}
									
									this.showToast(errorMsg);
									
									// 调用失败回调
									if (failCallback) {
										failCallback({ subscribeResults });
									}
								}
								
								// 调用完成回调
								if (completeCallback) {
									completeCallback();
								}
							},
							fail: (err) => {
								console.error('请求订阅消息失败', err);
								
								// 细化错误提示，明确无弹窗的原因
								const errorMap = {
									20001: '模板ID无效，请联系管理员配置',
									20004: '订阅请求已发送，但未检测到授权弹窗（可能被微信拦截）',
									20005: '当前设备不支持订阅消息功能',
									20006: '订阅消息功能已被禁用，请前往微信设置开启',
									10001: '用户未登录微信，无法触发授权弹窗'
								};
								
								let errorMsg = errorMap[err.errCode] || (err.errMsg.includes('auth deny') 
									? '您已拒绝订阅授权，无弹窗（需手动开启微信订阅权限）' 
									: '订阅失败，请稍后重试');
								
								this.showToast(errorMsg);
								
								// 调用失败回调
								if (failCallback) {
									failCallback({ error: err });
								}
								
								// 调用完成回调
								if (completeCallback) {
									completeCallback();
								}
							}
						});
					} else {
						this.showToast('已取消订阅');
						
						// 调用完成回调
						if (completeCallback) {
							completeCallback();
						}
					}
				}
			});
		}).catch(err => {
			this.showToast('校验订阅权限失败：' + err)
			if (failCallback) failCallback({ error: err })
			// 调用完成回调
			if (completeCallback) {
				completeCallback();
			}
		})
	},
	
	/**
	 * 检查微信订阅消息设置状态
	 * @param {string} templateId - 可选，模板ID，用于检查特定模板的订阅状态
	 * @returns {Promise} - 返回Promise，resolve为设置状态，reject为错误信息
	 */
	checkSubscribeSetting(templateId = '') {
		return new Promise((resolve, reject) => {
			// 只在微信小程序平台支持
			if (this.currentPlatform() !== 'wechat_mini') {
				reject("该功能仅支持微信小程序");
				return;
			}
			
			// 使用withSubscriptions: true获取订阅消息设置
			uni.getSetting({
				withSubscriptions: true,
				success: (res) => {
					
					// 检查全局订阅开关
					const result = {
						mainSwitch: (res.subscriptionsSetting && res.subscriptionsSetting.mainSwitch) || false,
						authStatus: 'unknown',
						hasPermission: false
					};
					
					if (result.mainSwitch) {
						// 全局订阅开关已打开
						if (templateId && res.subscriptionsSetting && res.subscriptionsSetting.itemSettings) {
							// 检查特定模板的授权状态
							const authStatus = res.subscriptionsSetting.itemSettings[templateId];
							result.authStatus = authStatus || 'unknown';
							result.hasPermission = authStatus === 'accept';
						} else {
							// 没有模板ID或没有itemSettings，只检查全局开关
							result.hasPermission = true;
						}
					}
					
					resolve(result);
				},
				fail: (err) => {
					reject("获取订阅消息设置失败：" + err.errMsg);
				}
			});
		});
	},
	// 导航跳转，增加防重复点击机制
	navigatorTo: function(url) {
		// 防止重复点击，使用时间戳判断
		const now = Date.now();
		if (this.lastNavigateTime && now - this.lastNavigateTime < 500) {
			return; // 500ms内不重复跳转
		}
		this.lastNavigateTime = now;
		
		uni.navigateTo({
			url: url,
			fail: function(res) {
				uni.showToast({
					title: "地址不存在",
					icon: "none",
					mask: true,
				});
			}
		});
	},
	redirectTo: function(url) {
		uni.redirectTo({
			url: url,
			fail: function(res) {
				uni.showToast({
					title: "地址不存在",
					icon: "none",
					mask: true,
				});
			}
		});
	},
	navigatorMini: function(appid, path = '') {
		uni.navigateToMiniProgram({
			appId: appid,
			path: path
		});
	},
	setClipboardData: function(content, msg = '复制成功', showTotast = true) {
		uni.setClipboardData({
			data: content,
			showToast: showTotast,
			success: function() {
				uni.showToast({
					title: msg,
					icon: 'none'
				});
			},
			fail: function(res) {
				uni.showToast({
					title: res.errMsg,
					icon: 'none'
				});
			}
		});
	},
	previewImage: function(url) {
		wx.previewImage({
			urls: [url]
		});
	},
	showServiceImage: function() {
		// 从全局配置获取图片URL
		const serviceQrCode = getApp().globalData.serviceQrCode;
		wx.previewImage({
			urls: [serviceQrCode]
		});
	},
	saveImageToAlbum: function(image) {
		uni.downloadFile({
			url: image,
			success: function(res) {
				if (res.statusCode === 200) {
					uni.saveImageToPhotosAlbum({
						filePath: res.tempFilePath,
						success: function() {
							uni.showToast({
								title: '保存成功'
							});
						},
						fail: function(res) {
							uni.showToast({
								title: res.errMsg,
								icon: "none"
							});
						}
					});
				}
			},
			fail: function(res) {
				uni.showToast({
					title: res.errMsg,
					icon: "none"
				});
			}
		});
	},
	tnHome: function() {
		uni.reLaunch({
			url: '/pages/index/index'
		});
	},
	tnRelunch: function(url) {
		uni.reLaunch({
			url: url
		});
	},
	templateSubscribe: function(templateCode, templateData) {
		return new Promise((resolve, reject) => {
			// 平台检查
			if (this.currentPlatform() !== 'wechat_mini') {
				uni.showToast({
					title: '订阅功能仅支持微信小程序',
					icon: "none",
					duration: 3000
				});
				reject(new Error('不支持的平台'));
				return;
			}

			// 获取模板ID
			configApi.getSubscribeTemplates().then(resConfig => {
				let template = '';
				if (resConfig && resConfig.code === 100 && resConfig.data) {
					// 统一数据格式为数组
					let templateList = []
					templateList = Array.isArray(resConfig.data) ? resConfig.data : [resConfig.data]

					// 优先匹配传入的templateCode，无则匹配默认版本更新模板
					const targetTemplate = templateList.find(item => {
						return item.code === templateCode || item.code === 'app_version_update'
					})

					if (targetTemplate && targetTemplate.template_id) {
						template = targetTemplate.template_id;
						
					} else {
						uni.showToast({
							title: '暂无可用的订阅模板',
							icon: "none",
							duration: 3000
						});
						reject(new Error('暂无可用的订阅模板'));
						return;
					}
				} else {
					uni.showToast({
						title: '获取订阅模板失败',
						icon: "none",
						duration: 3000
					});
					reject(new Error('获取订阅模板失败'));
					return;
				}

				// 验证模板ID是否有效
				if (!template || typeof template !== 'string' || template.trim() === '') {
					uni.showToast({
						title: '订阅模板配置错误',
						icon: "none",
						duration: 3000
					});
					reject(new Error('无效的模板ID'));
					return;
				}

				// 请求用户授权订阅
				uni.requestSubscribeMessage({
					tmplIds: [template],
					success: (res) => {
						
						if (res.errMsg === 'requestSubscribeMessage:ok') {
							if (res[template] === 'accept') {
								// 用户同意订阅，提交订阅信息
								messageApi.apiTemplateSubscribe({code: templateCode, type: 'wechat_mini', data: templateData})
								.then(res1 => {
									resolve(res1);
									let msg = res1.code === 100 ? "订阅成功" : "订阅失败";
									uni.showToast({
										title: msg,
										icon: "none",
										duration: 3000
									});
								})
								.catch(error => {
									console.error('提交订阅信息失败:', error);
									reject(error);
								});
							} else if (res[template] === 'reject') {
								// 用户拒绝订阅
								uni.showModal({
									title: '订阅提示',
									content: '你已拒绝接收该消息，请打开右上角设置按钮，在订阅消息中开启接收，以便实时获取最新更新通知。',
									showCancel: false
								});
								reject(new Error('用户拒绝订阅'));
							} else if (res[template] === 'ban') {
								// 用户被禁止订阅
								uni.showToast({
									title: '您已被禁止订阅消息',
									icon: 'none',
									duration: 3000
								});
								reject(new Error('用户被禁止订阅'));
							} else {
								// 其他状态
								uni.showToast({
									title: '订阅状态异常',
									icon: 'none',
									duration: 3000
								});
								reject(new Error('订阅状态异常:' + res[template]));
							}
						} else {
							// 请求失败
							console.error('requestSubscribeMessage请求失败:', res);
							if (res.errCode === 20001) {
								uni.showToast({
									title: '模板ID无效，请检查配置',
									icon: "none",
									duration: 3000
								});
								reject(new Error('模板ID无效'));
							} else {
								uni.showToast({
									title: '订阅请求失败:' + res.errMsg,
									icon: "none",
									duration: 3000
								});
								reject(new Error('订阅请求失败:' + res.errMsg));
							}
						}
					},
					fail: (err) => {
						console.error('requestSubscribeMessage调用失败:', err);
						uni.showToast({
							title: '订阅请求失败:' + err.errMsg,
							icon: "none",
							duration: 3000
						});
						reject(new Error('订阅请求失败:' + err.errMsg));
					}
				});
			})
			.catch(error => {
				console.error('获取模板ID失败:', error);
				reject(error);
			});
		});
	},
	verifyPhone: function(phoneNumber) {
		const regex = /^(\+?86)?[1][3-9][0-9]{9}$/;
		return regex.test(phoneNumber);
	},
	currentPlatform: function() {
		// #ifdef MP-WEIXIN
		return 'wechat_mini';
		// #endif
		// #ifdef MP-TOUTIAO
		return 'dy_mini';
		// #endif
		// #ifdef H5
		return 'network_h5';
		// #endif
		// 新增默认值
		return 'unknown';
	},

	/**
	 * 返回上一页并刷新数据
	 * @param {boolean} forceRefresh - 是否强制刷新，默认为true
	 * @returns {void}
	 */
	navigateBackAndRefresh: function(forceRefresh = true) {
		try {
			
			// 获取页面栈
			const pages = getCurrentPages()
			
			if (pages && pages.length > 1) {
				// 获取上一个页面实例
				const prevPage = pages[pages.length - 2]
				
				if (prevPage) {
					// 尝试执行刷新操作
					try {
						
						// 优先调用自定义的onRefresh方法
						if (typeof prevPage.onRefresh === 'function') {
							prevPage.onRefresh()
						} 
						// 其次检查是否有$vm属性（Vue组件实例），并尝试调用其onRefresh方法
						else if (prevPage.$vm && typeof prevPage.$vm.onRefresh === 'function') {
							prevPage.$vm.onRefresh()
						}
						// 再次检查是否有$vm属性，并尝试设置其needRefresh标志
						else if (prevPage.$vm && prevPage.$vm.$data) {
							prevPage.$vm.$data.needRefresh = true
						} 
						// 其次尝试设置needRefresh标志
						else if (prevPage.$data) {
							prevPage.$data.needRefresh = true
						} 
						// 最后如果forceRefresh为true，尝试重新获取页面数据
						else if (forceRefresh && typeof prevPage.fetchData === 'function') {
							prevPage.fetchData()
						}
						// 最后如果forceRefresh为true，且有$vm属性，尝试调用其fetchData方法
						else if (forceRefresh && prevPage.$vm && typeof prevPage.$vm.fetchData === 'function') {
							prevPage.$vm.fetchData()
						}
						else {
							console.log('[navigateBackAndRefresh] 没有找到合适的刷新方法')
						}
					} catch (refreshError) {
						console.warn('[navigateBackAndRefresh] 刷新上一页数据失败:', refreshError)
						// 即使刷新失败，也继续返回上一页
					}
				}
				
				// 返回上一页
				uni.navigateBack({
					delta: 1,
					fail: (err) => {
						// 如果返回失败，尝试返回到首页
						uni.reLaunch({
							url: '/pages/index/index',
							fail: (reLaunchErr) => {
								console.error('[navigateBackAndRefresh] 返回到首页失败:', reLaunchErr)
							}
						})
					}
				})
			} else {
				// 如果没有上一页，返回到首页
				uni.reLaunch({
					url: '/pages/index/index',
					fail: (err) => {
						console.error('[navigateBackAndRefresh] 返回到首页失败:', err)
					}
				})
			}
		} catch (error) {
			// 发生错误时，尝试返回到首页
			uni.reLaunch({
				url: '/pages/index/index',
				fail: (reLaunchErr) => {
					console.error('[navigateBackAndRefresh] 返回到首页失败:', reLaunchErr)
				}
			})
		}
	},

	/**
	 * 获取当前登录用户的信息
	 */
	getUserInfo: function () {
	try {
		let userInfo = {}

		// 从本地存储获取用户ID
		const lifeData = uni.getStorageSync('lifeData')
		if (lifeData && lifeData.vuex_user) {
		userInfo['userId'] = lifeData.vuex_user.id
		userInfo['userAvatar'] = lifeData.vuex_user.avatar
		userInfo['userName'] = lifeData.vuex_user.name
		}

		// 如果 本地存储 中没有，尝试从 store 中获取用户信息
		try {
		const appInstance = getApp()
		if (appInstance && appInstance.$store && appInstance.$store.state && appInstance.$store.state.vuex_user) {
			const lifeData2 = appInstance.$store.state.vuex_user
			if (lifeData2 && lifeData2.id) {
			userInfo['userId'] = lifeData2.id
			userInfo['userAvatar'] = lifeData2.avatar
			userInfo['userName'] = lifeData2.name
			}
		}
		} catch (storeError) {
		console.warn('从store获取用户信息失败:', storeError)
		// 继续执行，不影响从本地存储获取的结果
		}
		
		// 都没有的话返回默认值或提示登录
		if (!userInfo['userId']) {
		console.warn('未找到用户ID，请先登录')
		return {}
		}
		return userInfo
	} catch (error) {
		console.error('获取用户信息失败:', error)
		return {}
	}
	}
}