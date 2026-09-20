import request from "@/util/request"

export default {
  /**
   * 积分兑换会员
   * @param {Object} params - 请求参数
   * @param {Number} params.vip_type - 会员类型
   * @param {Number} params.integral_required - 所需积分
   * @returns {Promise} - 请求结果
   */
   apiVipIntegralPay(params) {
    return request.post('api/exam.examCommon/vipIntegralPay', params).then(res => {
      return res
    })
  },
  
  /**
   * 激活码兑换会员
   * @param {Object} params - 请求参数
   * @param {String} params.activation_code - 激活码
   * @returns {Promise} - 请求结果
   */
   apiVipActivationCode(params) {
    return request.post('api/exam.examCommon/vipActivationCode', params).then(res => {
      return res
    })
  },
  

}