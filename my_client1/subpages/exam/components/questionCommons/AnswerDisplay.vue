<template>
  <!-- 作答显示组件（问答题、案例题不显示） -->
  <view
    v-if="showComponent"
    class="answer-display" 
  >
    <!-- 答案标题（动态显示） -->
    <view v-if="question.exam_type !== 5 && 
             question.exam_type !== 6 &&
             hasUserAnswer" class="answer-header">
      <text 
        class="header-title"
        :class="{ 'title-correct': verifiedIsCorrect, 'title-wrong': !verifiedIsCorrect }"
      >
        {{ verifiedIsCorrect ? '答案正确' : '答案错误' }}
      </text>
    </view>

    <!-- 答案对比区域 -->
    <view v-if="question.exam_type !== 5 && 
             question.exam_type !== 6 " class="answer-comparison">
      <!-- 正确答案 -->
      <view class="answer-item correct-answer">
        <view class="answer-label">
          正确答案
        </view>
        <view class="answer-value correct">
          {{ formattedCorrectAnswer }}
        </view>
      </view>

      <!-- 你的答案 -->
      <view class="answer-item user-answer">
        <view class="answer-label">
          你的答案
        </view>
        <view 
          class="answer-value"
          :class="{ 'correct': verifiedIsCorrect, 'wrong': !verifiedIsCorrect }"
        >
          {{ formattedUserAnswer }}
        </view>
      </view>
    </view>
    
    <!-- 数据统计区域 -->
    <view
      v-if="showStatistics"
      class="stats-section"
    >
      <view class="stats-grid">
        <!-- 全站作答 -->
        <view class="stat-item">
          <view class="stat-value primary">
            {{ formatNumber(question.answer_count) }}次
          </view>
          <view class="stat-label">
            全站作答
          </view>
        </view>

        <!-- 全站正确率 -->
        <view class="stat-item">
          <view class="stat-value success">
            {{ question.correct_rate || '0%' }}
          </view>
          <view class="stat-label">
            全站正确率
          </view>
        </view>

        <!-- 易错项 -->
        <view class="stat-item">
          <view class="stat-value error">
            {{ question.wrong_option || '无' }}
          </view>
          <view class="stat-label">
            易错项
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'AnswerDisplay',
  props: {
    // 题目对象
    question: {
      type: Object,
      default: () => ({})
    },
    // 是否显示统计信息
    showStatistics: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    // 判断是否应该显示此组件
    showComponent() {
      // 在已提交或背题模式下显示（无论正确还是错误）
      return this.question && 
             (this.question.is_submitted || 
              (this.question.exam_type !== 5 && this.question.exam_type !== 6));
    },
    
    // 判断用户是否已作答
    hasUserAnswer() {
      if (!this.question || !this.question.user_answer) return false;
      
      const userAnswer = this.question.user_answer;
      
      // 检查是否为空值
      if (userAnswer === '' || userAnswer === null || userAnswer === undefined) {
        return false;
      }
      
      // 如果是字符串，检查是否为空或仅包含空白
      if (typeof userAnswer === 'string') {
        const trimmed = userAnswer.trim();
        if (trimmed === '' || trimmed === '[]' || trimmed === '{}') {
          return false;
        }
      }
      
      // 如果是数组，检查是否为空数组
      if (Array.isArray(userAnswer) && userAnswer.length === 0) {
        return false;
      }
      
      return true;
    },
    
    // 二次验证答案正确性
    verifiedIsCorrect() {
      // 二次验证：比较格式化后的正确答案和用户答案
      const correct = this.formattedCorrectAnswer;
      const user = this.formattedUserAnswer;
      
      // 无论is_correct字段如何设置，都进行二次验证
      // 如果答案完全一致，直接返回true
      if (correct === user) {
        return true;
      }
      
      // 只有当答案不一致时，才使用is_correct字段的值
      return this.question.is_correct || false;
    },
    
    // 格式化正确答案显示
    formattedCorrectAnswer() {
      if (!this.question) return '-';
      
      // 统一处理逻辑：无论输入格式如何，最终都转换为英文逗号分隔，无空格
      let finalAnswer = '';
      
      // 获取原始答案数据
      let answerData = this.question.answer || this.question.answer_str || '-';
      
      // 1. 优先处理 answer_str（可能是已格式化的）
      if (typeof answerData === 'string' && this.question.answer_str) {
        const trimmed = answerData.trim();
        
        // 处理空值
        if (trimmed === '' || trimmed === '[]' || trimmed === '{}') {
          return '-';
        }
        
        // 检查是否为 JSON 格式
        if (trimmed.startsWith('[') || trimmed.startsWith('{')) {
          // 是 JSON 格式，交给后续的 try 块处理
        } else {
          // 不是 JSON 格式，直接处理
          // 处理已经是中文顿号分隔的字符串（如 "A、B、C"）
          if (trimmed.includes('、')) {
            // 转换为英文逗号，无空格
            return trimmed
              .split('、')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          }
          
          // 处理已经是英文逗号分隔的字符串（如 "A, B, C" 或 "A,B,C"）
          if (trimmed.includes(',')) {
            return trimmed
              .split(',')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          }
          
          // 处理已经是中文逗号分隔的字符串（如 "A，B，C"）
          if (trimmed.includes('，')) {
            // 转换为英文逗号，无空格
            return trimmed
              .split('，')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          }
          
          // 单个答案，直接返回
          return trimmed;
        }
      }
      
      // 2. 尝试解析 JSON 格式
      try {
        let processedData = answerData;
        
        // 如果是字符串，尝试解析为对象或数组
        if (typeof answerData === 'string') {
          const trimmed = answerData.trim();
          
          // 处理空值
          if (trimmed === '' || trimmed === '[]' || trimmed === '{}') {
            return '-';
          }
          
          // 检查是否为 JSON 字符串
          if (trimmed.startsWith('[') || trimmed.startsWith('{')) {
            try {
              processedData = JSON.parse(trimmed);
            } catch (jsonError) {
              // JSON 解析失败，将其作为普通字符串处理
              processedData = trimmed;
            }
          } else {
            // 不是 JSON 字符串，直接作为普通字符串处理
            processedData = trimmed;
          }
        }
        
        // 3. 处理解析后的数据
        if (Array.isArray(processedData)) {
          // 处理嵌套数组
          while (Array.isArray(processedData) && processedData.length === 1 && Array.isArray(processedData[0])) {
            processedData = processedData[0];
          }
          
          // 处理数组元素
          const finalArray = [];
          
          // 遍历数组，处理每个元素
          for (let item of processedData) {
            // 如果元素是对象，转换为字符串
            if (typeof item === 'object' && item !== null) {
              item = JSON.stringify(item);
            }
            
            // 如果元素是字符串，去除多余的引号和空格
            if (typeof item === 'string') {
              // 去除可能的引号（如 '"A"' 变为 'A'）
              item = item.replace(/^['"]|['"]$/g, '').trim();
            }
            
            // 只有非空元素才添加到最终数组
            if (item !== null && item !== undefined && item !== '') {
              finalArray.push(item);
            }
          }
          
          // 使用英文逗号连接，无空格
          finalAnswer = finalArray.join(',');
        } else if (typeof processedData === 'string') {
          // 处理普通字符串
          const trimmed = processedData.trim();
          
          // 处理已经是中文顿号分隔的情况
          if (trimmed.includes('、')) {
            // 转换为英文逗号，无空格
            finalAnswer = trimmed
              .split('、')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          } else if (trimmed.includes('，')) {
            // 中文逗号转换为英文逗号，无空格
            finalAnswer = trimmed
              .split('，')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          } else {
            // 按英文逗号分割，使用英文逗号连接，无空格
            finalAnswer = trimmed
              .split(',')
              .map(item => item.trim())
              .filter(item => item !== '')
              .join(',');
          }
        } else {
          // 其他类型，转换为字符串
          finalAnswer = String(processedData);
        }
        
        // 4. 最终检查
        if (!finalAnswer) {
          return '-';
        }
        
        return finalAnswer;
      } catch (e) {
        // 解析失败，进行兜底处理
        const trimmed = String(answerData).trim();
        
        // 处理空值
        if (trimmed === '' || trimmed === '[]' || trimmed === '{}') {
          return '-';
        }
        
        // 提取答案内容：去除JSON数组的括号和引号
        let extractedAnswer = trimmed;
        
        // 处理JSON数组格式，如 ["A", "B", "C"]
        if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
          // 去除括号
          extractedAnswer = trimmed.slice(1, -1);
          
          // 处理内部的引号，如 "A", "B", "C" -> A, B, C
          extractedAnswer = extractedAnswer.replace(/"/g, '');
        }
        
        // 最后，统一转换为英文逗号分隔，无空格
        if (extractedAnswer.includes(',')) {
          return extractedAnswer
            .split(',')
            .map(item => item.trim())
            .filter(item => item !== '')
            .join(',');
        } else if (extractedAnswer.includes('、')) {
          // 中文顿号转换为英文逗号，无空格
          return extractedAnswer
            .split('、')
            .map(item => item.trim())
            .filter(item => item !== '')
            .join(',');
        } else if (extractedAnswer.includes('，')) {
          // 中文逗号转换为英文逗号，无空格
          return extractedAnswer
            .split('，')
            .map(item => item.trim())
            .filter(item => item !== '')
            .join(',');
        } else {
          // 单个答案，直接返回
          return extractedAnswer;
        }
      }
    },
    
    // 格式化用户答案显示
    formattedUserAnswer() {
      if (!this.question || !this.question.user_answer) return '未作答';
      
      let userAnswer = this.question.user_answer;
      let finalAnswer = '';
      
      // 统一处理逻辑：无论输入格式如何，最终都转换为英文逗号分隔，无空格
      if (Array.isArray(userAnswer)) {
        // 处理数组格式
        const processedArray = [];
        
        for (let item of userAnswer) {
          if (typeof item === 'object' && item !== null) {
            item = JSON.stringify(item);
          }
          
          if (typeof item === 'string') {
            // 清理字符串，去除括号、引号和空格
            item = item.replace(/[\[\]"'\s]/g, '').trim();
            
            // 如果清理后包含逗号或顿号，再次分割
            if (item.includes(',') || item.includes('，') || item.includes('、')) {
              processedArray.push(...item.split(/[,，、]/).map(ans => ans.trim()));
            } else if (item) {
              processedArray.push(item);
            }
          } else if (item !== null && item !== undefined) {
            processedArray.push(String(item).trim());
          }
        }
        
        finalAnswer = processedArray.filter(Boolean).join(',');
      } else if (typeof userAnswer === 'string') {
        // 处理字符串格式
        let trimmed = userAnswer.trim();
        
        // 清理字符串，去除括号、引号和空格
        trimmed = trimmed.replace(/[\[\]"'\s]/g, '');
        
        // 处理不同分隔符
        if (trimmed.includes(',') || trimmed.includes('，') || trimmed.includes('、')) {
          finalAnswer = trimmed.split(/[,，、]/).map(ans => ans.trim()).filter(Boolean).join(',');
        } else {
          finalAnswer = trimmed;
        }
      } else {
        // 处理其他类型
        finalAnswer = String(userAnswer).trim();
      }
      
      return finalAnswer || '未作答';
    }
  },
  methods: {
    // 格式化数字（添加千位分隔符）
    formatNumber(num) {
      if (!num && num !== 0) return '0';
      return String(num).replace(/(\d)(?=(\d{3})+$)/g, '$1,');
    }
  }
}
</script>

<style scoped lang="scss">
.answer-display {
  background-color: #FFFFFF;
  margin: 20rpx 0;
  padding: 30rpx;
  border-radius: 16rpx;
  box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.06);
  
  // 答案标题
  .answer-header {
    margin-bottom: 10rpx;
    margin-left: 10rpx;

    .header-title {
      font-size: 36rpx;
      font-weight: bold;
      color: #333333;
      letter-spacing: 2rpx;
      
      // 正确答案标题颜色
      &.title-correct {
        color: #52C41A;
      }
      
      // 错误答案标题颜色
      &.title-wrong {
        color: #FF4D4F;
      }
    }
  }
  
  // 答案对比区域
  .answer-comparison {
    background: linear-gradient(135deg, #F7F8FA 0%, #FAFBFC 100%);
    border-radius: 12rpx;
    padding: 20rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20rpx;
    margin-bottom: 20rpx;
    
    .answer-item {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10rpx;
      
      .answer-label {
        font-size: 24rpx;
        color: #999999;
        letter-spacing: 1rpx;
      }
      
      .answer-value {
        font-size: 34rpx;
        letter-spacing: 4rpx;
        padding: 10rpx;
        
        &.correct {
          color: #52C41A;
        }
        
        &.wrong {
          color: #FF4D4F;
        }
      }
    }
  }
  
  // 数据统计区域
  .stats-section {
    .stats-grid {
      display: flex;
      justify-content: space-between;
      gap: 10rpx;
      
      .stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16rpx;
        padding: 20rpx 10rpx;
        background: linear-gradient(135deg, #F7F8FA 0%, #FAFBFC 100%);
        border-radius: 12rpx;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        
        /* 移除:active伪类，使用小程序内置的hover-*属性代替 */
        
        .stat-value {
          font-size: 30rpx;
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
          color: #999999;
          letter-spacing: 1rpx;
        }
      }
    }
  }
}
</style>

