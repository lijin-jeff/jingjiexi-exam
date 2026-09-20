<template>
    <el-card class="!border-none margin-bottom20" shadow="never">
        <el-page-header content="试卷管理" @back="$router.back()" />
    </el-card>
    <div class="exam-container">
        <el-aside width="300px" class="exam-aside">
            <el-card
                style="width: 100%"
                class="margin-bottom10"
                :body-style="{ padding: 10 + 'px' }"
                v-for="(item, index) in visibleExamTypes"
                :key="index"
            >
                <template #header>
                    <div class="card-header">
                        <span class="font-semibold">{{ item.exam_type }}</span>
                    </div>
                </template>
                <div class="left-main">
                    <div class="left-main-left">
                        <div style="display: flex; justify-content: flex-start">
                            <div class="padding-right10">
                                <span>共</span>
                                <span class="font-setting">{{ item.total_number }}</span>
                                <span>道</span>
                            </div>
                            <div>
                                <span>共</span>
                                <span class="font-setting">{{ item.total_score }}</span>
                                <span>分</span>
                            </div>
                        </div>
                        <div class="left-main-left-score">
                            <div style="display: flex; align-items: center">
                                <div class="padding-right10">每题</div>
                                <div class="padding-right10">
                                    <el-input-number
                                        v-model="item.score"
                                        :min="0.01"
                                        :step="0.5"
                                        :precision="2"
                                        size="small"
                                        @change="handlePerScoreChange(item.dataIndex, $event)"
                                        style="width: 80px"
                                    />
                                </div>
                                <div>分</div>
                            </div>
                        </div>
                    </div>
                </div>
            </el-card>
            <el-card
                style="max-width: 300px"
                class="margin-top10"
                v-if="formData.exam_list.length > 0"
            >
                <div style="display: flex; justify-content: flex-start" class="margin-bottom20">
                    <div>总题数</div>
                    <div class="margin-left10 font-setting">{{ formData.total.exam_count }}</div>
                    <div class="margin-left10">道</div>
                </div>
                <div style="display: flex; justify-content: flex-start">
                    <div>总分数</div>
                    <div class="margin-left10 font-setting">{{ formData.total.exam_score }}</div>
                    <div class="margin-left10">分</div>
                </div>
            </el-card>
        </el-aside>
        <el-main class="exam-main">
            <!-- 空状态提示 -->
            <el-card
                v-if="!formData.exam_list || formData.exam_list.length === 0"
                style="width: 100%"
                shadow="never"
                :body-style="{ padding: '80px 20px', textAlign: 'center' }"
            >
                <el-empty description="请选择题库试题或具体题目来组卷">
                    <template #image>
                        <svg class="icon" viewBox="0 0 1024 1024" width="64" height="64">
                            <path
                                d="M832 64H192a128 128 0 0 0-128 128v640a128 128 0 0 0 128 128h640a128 128 0 0 0 128-128V192a128 128 0 0 0-128-128zM320 768H256v-64h64v64z m0-128H256v-64h64v64z m0-128H256v-64h64v64z m0-128H256v-64h64v64z m0-128H256v-64h64v64z m448 512H384v-64h384v64z m0-128H384v-64h384v64z m0-128H384v-64h384v64z m0-128H384v-64h384v64z m0-128H384v-64h384v64z"
                                fill="#909399"
                            />
                        </svg>
                    </template>
                    <el-button type="primary" @click="fetchLibraryLists">选择题库试题</el-button>
                    <el-button type="warning" @click="showQuestionSelector" class="margin-left10"
                        >选择具体题目</el-button
                    >
                </el-empty>
            </el-card>

            <el-card v-else style="width: 100%" shadow="never" :body-style="{ padding: 20 + 'px' }">
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px">
                    <div style="display: flex; justify-content: flex-start; align-items: center">
                        <el-button type="primary" @click="savePaper">保存试卷</el-button>
                        <el-button type="success" @click="fetchLibraryLists" class="margin-left10"
                            >选择题库试题</el-button
                        >
                        <el-button
                            type="warning"
                            @click="showQuestionSelector"
                            class="margin-left10"
                            >选择具体题目</el-button
                        >
                    </div>
                </div>
                <div>
                    <!--          <div v-for="(item, index) in formData.exam_list" :key="index">-->
                    <div v-for="(exam_item, exam_index) in formData.exam_list" :key="exam_index">
                        <div class="display-flex-between-center margin-bottom20">
                            <div class="display-flex-start-center exam-title">
                                <div>
                                    {{ exam_index + 1 }}、[{{ exam_item.exam_type_text }}]
                                    <el-tooltip content="删除" placement="top">
                                        <el-button
                                            type="danger"
                                            :icon="Delete"
                                            circle
                                            size="small"
                                            @click="deleteExam(exam_index)"
                                        />
                                    </el-tooltip>
                                    <el-tooltip content="上移" placement="top">
                                        <el-button
                                            type="primary"
                                            :icon="Top"
                                            circle
                                            size="small"
                                            :disabled="exam_index === 0"
                                            @click="moveUp(exam_index)"
                                            class="margin-left10"
                                        />
                                    </el-tooltip>
                                    <el-tooltip content="下移" placement="top">
                                        <el-button
                                            type="primary"
                                            :icon="Bottom"
                                            circle
                                            size="small"
                                            :disabled="exam_index === formData.exam_list.length - 1"
                                            @click="moveDown(exam_index)"
                                            class="margin-left10"
                                        />
                                    </el-tooltip>
                                </div>

                                <div v-html="exam_item.title"></div>
                            </div>
                            <div
                                class="margin-right10 display-flex-right-center"
                                style="margin: 5px 0"
                            >
                                <div class="padding-left20">
                                    分数:
                                    <el-input-number
                                        :step="0.5"
                                        :min="0.5"
                                        :max="100"
                                        style="width: 120px"
                                        v-model="formData.exam_list[exam_index].score"
                                        size="small"
                                        @change="handleScoreChange(exam_index, $event)"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- 题目元信息：章节、知识点、标签 -->
                        <div
                            class="margin-bottom10"
                            v-if="
                                exam_item.chapter ||
                                exam_item.knowledge ||
                                (exam_item.labels && exam_item.labels.length > 0)
                            "
                        >
                            <el-space :size="8" wrap>
                                <el-tag
                                    v-if="exam_item.chapter"
                                    type="info"
                                    size="small"
                                    effect="plain"
                                >
                                    <template #default>
                                        <el-icon><Folder /></el-icon>
                                        <span class="margin-left5"
                                            >章节: {{ exam_item.chapter.title }}</span
                                        >
                                    </template>
                                </el-tag>
                                <el-tag
                                    v-if="exam_item.knowledge"
                                    type="warning"
                                    size="small"
                                    effect="plain"
                                >
                                    <template #default>
                                        <el-icon><DocumentCopy /></el-icon>
                                        <span class="margin-left5"
                                            >知识点: {{ exam_item.knowledge.title }}</span
                                        >
                                    </template>
                                </el-tag>
                                <el-tag
                                    v-for="(label, labelIndex) in (exam_item.labels || []).slice(
                                        0,
                                        3
                                    )"
                                    :key="labelIndex"
                                    type="success"
                                    size="small"
                                    effect="plain"
                                >
                                    <template #default>
                                        <el-icon><PriceTag /></el-icon>
                                        <span class="margin-left5">{{ label.title }}</span>
                                    </template>
                                </el-tag>
                                <el-tag
                                    v-if="exam_item.labels && exam_item.labels.length > 3"
                                    size="small"
                                    type="info"
                                >
                                    +{{ exam_item.labels.length - 3 }}
                                </el-tag>
                            </el-space>
                        </div>
                        <!--                             试题选项开始-->
                        <!-- 普通选择题选项（单选、多选、判断） -->
                        <div
                            class="margin-bottom20"
                            v-if="
                                [1, 2, 3].includes(exam_item.exam_type) &&
                                exam_item.option &&
                                exam_item.option.length > 0
                            "
                        >
                            <div
                                v-for="(optionItem, optionIndex) in exam_item.option"
                                :key="optionIndex"
                            >
                                <div class="exam-option-item margin-top20">
                                    <el-col :span="0.5">
                                        <div
                                            :class="
                                                optionItem.is_check
                                                    ? 'exam-option-selected'
                                                    : 'exam-option'
                                            "
                                        >
                                            {{ optionItem.check }}
                                        </div>
                                    </el-col>
                                    <el-col :span="23">
                                        <div v-html="optionItem.title" class="margin-left10"></div>
                                    </el-col>
                                </div>
                            </div>
                        </div>

                        <!-- 填空题和问答题答案显示 -->
                        <div
                            class="margin-bottom20"
                            v-if="[4, 5].includes(exam_item.exam_type) && exam_item.answer"
                        >
                            <el-card shadow="never" class="answer-card">
                                <template #header>
                                    <div class="answer-card-header">
                                        <el-icon color="#67c23a"><Select /></el-icon>
                                        <span class="margin-left10">标准答案</span>
                                    </div>
                                </template>
                                <div v-html="exam_item.answer" class="answer-content"></div>
                            </el-card>
                        </div>

                        <!-- 案例题子题 -->
                        <div
                            class="margin-bottom20"
                            v-if="
                                exam_item.exam_type === 6 &&
                                exam_item.option &&
                                exam_item.option.length > 0
                            "
                        >
                            <el-alert
                                title="案例题包含多个子题，每个子题单独评分"
                                type="warning"
                                :closable="false"
                                class="margin-bottom20"
                            />
                            <div
                                v-for="(subQuestion, subIndex) in exam_item.option"
                                :key="subIndex"
                                class="case-sub-question"
                            >
                                <!-- 子题标题 -->
                                <div class="sub-question-header">
                                    <el-tag type="primary" size="small" effect="plain">
                                        子题 {{ subIndex + 1 }}
                                    </el-tag>
                                    <el-tag type="info" size="small" class="margin-left10">
                                        {{ getExamTypeName(Number(subQuestion.exam_type) || 1) }}
                                    </el-tag>
                                    <span class="margin-left10"
                                        >分数:
                                        {{
                                            subQuestion.childrenScore || subQuestion.score || 2
                                        }}</span
                                    >
                                </div>

                                <!-- 子题题干 -->
                                <div class="sub-question-title" v-html="subQuestion.title"></div>

                                <!-- 子题选项（如果是选择题） -->
                                <div
                                    v-if="[1, 2, 3].includes(Number(subQuestion.exam_type) || 1)"
                                    class="margin-top10"
                                >
                                    <!-- 优先使用 children 字段（后端实际返回的字段） -->
                                    <div
                                        v-if="
                                            subQuestion.children && subQuestion.children.length > 0
                                        "
                                    >
                                        <div
                                            v-for="(opt, optIndex) in subQuestion.children"
                                            :key="optIndex"
                                            class="exam-option-item margin-top10"
                                        >
                                            <el-col :span="0.5">
                                                <div
                                                    :class="
                                                        opt.is_check
                                                            ? 'exam-option-selected'
                                                            : 'exam-option'
                                                    "
                                                >
                                                    {{ opt.check }}
                                                </div>
                                            </el-col>
                                            <el-col :span="23">
                                                <div v-html="opt.title" class="margin-left10"></div>
                                            </el-col>
                                        </div>
                                    </div>
                                    <!-- 其次使用 options 字段 -->
                                    <div
                                        v-else-if="
                                            subQuestion.options && subQuestion.options.length > 0
                                        "
                                    >
                                        <div
                                            v-for="(opt, optIndex) in subQuestion.options"
                                            :key="optIndex"
                                            class="exam-option-item margin-top10"
                                        >
                                            <el-col :span="0.5">
                                                <div
                                                    :class="
                                                        opt.is_check
                                                            ? 'exam-option-selected'
                                                            : 'exam-option'
                                                    "
                                                >
                                                    {{ opt.check }}
                                                </div>
                                            </el-col>
                                            <el-col :span="23">
                                                <div v-html="opt.title" class="margin-left10"></div>
                                            </el-col>
                                        </div>
                                    </div>
                                    <!-- 最后使用 option 字段 -->
                                    <div
                                        v-else-if="
                                            subQuestion.option && subQuestion.option.length > 0
                                        "
                                    >
                                        <div
                                            v-for="(opt, optIndex) in subQuestion.option"
                                            :key="optIndex"
                                            class="exam-option-item margin-top10"
                                        >
                                            <el-col :span="0.5">
                                                <div
                                                    :class="
                                                        opt.is_check
                                                            ? 'exam-option-selected'
                                                            : 'exam-option'
                                                    "
                                                >
                                                    {{ opt.check }}
                                                </div>
                                            </el-col>
                                            <el-col :span="23">
                                                <div v-html="opt.title" class="margin-left10"></div>
                                            </el-col>
                                        </div>
                                    </div>
                                </div>

                                <!-- 子题答案（填空题和问答题） -->
                                <div
                                    class="sub-question-answer margin-top10"
                                    v-if="
                                        [4, 5].includes(Number(subQuestion.exam_type)) &&
                                        subQuestion.answerContent
                                    "
                                >
                                    <el-text type="success" size="small">
                                        <el-icon><Select /></el-icon>
                                        答案:
                                    </el-text>
                                    <div
                                        v-html="subQuestion.answerContent"
                                        class="margin-left10"
                                    ></div>
                                </div>

                                <!-- 子题选择题答案 -->
                                <div
                                    class="sub-question-answer margin-top10"
                                    v-if="[1, 2, 3].includes(Number(subQuestion.exam_type))"
                                >
                                    <el-text type="success" size="small">
                                        <el-icon><Select /></el-icon>
                                        答案:
                                    </el-text>
                                    <span v-if="subQuestion.selectedAnswer">
                                        {{ subQuestion.selectedAnswer }}
                                    </span>
                                    <span
                                        v-else-if="
                                            subQuestion.selectedAnswers &&
                                            subQuestion.selectedAnswers.length > 0
                                        "
                                    >
                                        {{ subQuestion.selectedAnswers.join(', ') }}
                                    </span>
                                    <span v-else-if="subQuestion.check">
                                        {{ subQuestion.check }}
                                    </span>
                                    <span v-else-if="subQuestion.judeAnswer">
                                        {{ subQuestion.judeAnswer }}
                                    </span>
                                    <span v-else> - </span>
                                </div>

                                <el-divider v-if="subIndex < exam_item.option.length - 1" />
                            </div>
                        </div>
                        <!--                          试题选项结束-->
                        <div class="exam-answer" v-if="exam_item.analysis">
                            <div style="white-space: nowrap">试题解析：</div>
                            <div class="analysis-content">
                                <div
                                    v-html="exam_item.analysis"
                                    :class="{ 'analysis-collapsed': !analysisExpanded[exam_index] }"
                                ></div>
                                <el-button
                                    v-if="isAnalysisLong(exam_item.analysis)"
                                    type="text"
                                    size="small"
                                    @click="toggleAnalysis(exam_index)"
                                    class="margin-top10"
                                >
                                    {{ analysisExpanded[exam_index] ? '收起' : '展开全部' }}
                                </el-button>
                            </div>
                        </div>
                        <el-divider />
                    </div>
                    <!--          </div>-->
                </div>
            </el-card>
        </el-main>
        <!--    题库选择开始-->
        <el-dialog title="题库选择" v-model="dialogLibraryVisible" width="80%">
            <el-alert
                title="提示：选择题库后将加载该题库的所有试题，支持多个题库选择"
                type="info"
                :closable="false"
                class="mb-4"
            />
            <div class="mt-4">
                <el-card
                    class="!border-none mb-4"
                    shadow="never"
                    :body-style="{ padding: 10 + 'px' }"
                >
                    <el-form class="" :model="queryLibraryParams" inline>
                        <el-form-item label="题库名称" prop="title">
                            <el-input
                                class="w-[120px]"
                                v-model="queryLibraryParams.title"
                                clearable
                                placeholder="请输入"
                            />
                        </el-form-item>
                        <el-form-item label="题库分类" prop="category_uid">
                            <el-tree-select
                                v-model="queryLibraryParams.category_uid"
                                :data="categoryList"
                                clearable
                                filterable
                                node-key="uid"
                                :props="{
                                    label: 'title',
                                    value: 'uid',
                                    children: 'children'
                                }"
                                :default-expand-all="false"
                                style="width: 100%"
                                @change="handleLibraryCategoryChange"
                            />
                        </el-form-item>
                        <el-form-item label="收费状态" prop="free_state">
                            <el-select
                                style="width: 100px"
                                v-model="queryLibraryParams.free_state"
                                clearable
                                placeholder="请选择"
                            >
                                <el-option label="全部" value=""></el-option>
                                <el-option
                                    v-for="(item, index) in dictData.price_typ"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="是否热门" prop="hot_state">
                            <el-select
                                style="width: 100px"
                                v-model="queryLibraryParams.hot_state"
                                clearable
                                placeholder="请选择"
                            >
                                <el-option label="全部" value=""></el-option>
                                <el-option
                                    v-for="(item, index) in dictData.hot_state"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="是否推荐" prop="recommend_state">
                            <el-select
                                style="width: 100px"
                                v-model="queryLibraryParams.recommend_state"
                                clearable
                                placeholder="请选择"
                            >
                                <el-option label="全部" value=""></el-option>
                                <el-option
                                    v-for="(item, index) in dictData.recommend_state"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item label="收费状态" prop="free_state">
                            <el-select
                                style="width: 100px"
                                v-model="queryLibraryParams.free_state"
                                clearable
                                placeholder="请选择"
                            >
                                <el-option label="全部" value=""></el-option>
                                <el-option
                                    v-for="(item, index) in dictData.price_typ"
                                    :key="index"
                                    :label="item.name"
                                    :value="item.value"
                                />
                            </el-select>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="resetPage">查询</el-button>
                            <el-button @click="resetParams">重置</el-button>
                            <el-button @click="confirmLibrary" type="warning">确认选择</el-button>
                        </el-form-item>
                    </el-form>
                </el-card>
                <el-table
                    :data="pager.lists"
                    @selection-change="handleSelectionChange"
                    stripe
                    border
                >
                    <el-table-column type="selection" width="55" align="center" />
                    <el-table-column
                        label="题库编号"
                        prop="uid"
                        min-width="120"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="题库分类"
                        prop="category.title"
                        min-width="120"
                        show-overflow-tooltip
                    />
                    <el-table-column
                        label="题库名称"
                        prop="title"
                        min-width="150"
                        show-overflow-tooltip
                    />
                    <el-table-column label="试题总数" prop="exam_count" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag type="primary" size="small">{{ row.exam_count }} 道</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="题库封面" prop="image" width="100" align="center">
                        <template #default="{ row }">
                            <el-image
                                v-if="row.image"
                                style="width: 50px; height: 50px"
                                :src="row.image"
                                :preview-src-list="[row.image]"
                                preview-teleported
                                fit="cover"
                            />
                            <span v-else class="text-gray-400">无图片</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="收费状态" prop="free_state" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag
                                :type="row.free_state == 1 ? 'success' : 'warning'"
                                size="small"
                            >
                                {{ row.free_state == 1 ? '免费' : '收费' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="显示状态" prop="is_show" width="100" align="center">
                        <template #default="{ row }">
                            <el-tag :type="row.is_show == 1 ? 'success' : 'info'" size="small">
                                {{ row.is_show == 1 ? '显示' : '隐藏' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column
                        label="创建时间"
                        prop="create_time"
                        min-width="160"
                        show-overflow-tooltip
                    />
                </el-table>
            </div>
            <div class="flex mt-4 justify-end">
                <pagination v-model="pager" @change="getLists" />
            </div>
        </el-dialog>
        <!--    题库选择结束-->

        <!--    具体题目选择开始-->
        <el-dialog
            title="题目选择"
            v-model="dialogQuestionVisible"
            width="90%"
            :close-on-click-modal="false"
        >
            <el-alert
                title="提示：可以通过章节、知识点、标签筛选题目，勾选题目后点击确认添加到试卷"
                type="info"
                :closable="false"
                class="mb-4"
            />
            <el-card class="!border-none mb-4" shadow="never" :body-style="{ padding: 10 + 'px' }">
                <el-form :model="queryQuestionParams" inline>
                    <el-form-item label="题库" prop="library_uid">
                        <el-select
                            v-model="queryQuestionParams.library_uid"
                            placeholder="请选择题库"
                            clearable
                            style="width: 200px"
                            @change="handleLibraryChange"
                        >
                            <el-option
                                v-for="lib in availableLibraries"
                                :key="lib.uid"
                                :label="lib.title"
                                :value="lib.uid"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="题型" prop="exam_type">
                        <el-select
                            v-model="queryQuestionParams.exam_type"
                            placeholder="全部"
                            clearable
                            style="width: 120px"
                        >
                            <el-option label="全部" value="" />
                            <el-option
                                v-for="item in dictData.exam_level"
                                :key="item.value"
                                :label="item.name"
                                :value="item.value"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="章节" prop="chapter_uid">
                        <el-tree-select
                            v-model="queryQuestionParams.chapter_uid"
                            :data="chapterList"
                            clearable
                            filterable
                            node-key="uid"
                            :props="{
                                label: 'title',
                                value: 'uid',
                                children: 'children'
                            }"
                            :default-expand-all="false"
                            placeholder="请选择章节"
                            check-strictly
                            style="width: 200px"
                            @change="handleChapterChange"
                        />
                    </el-form-item>
                    <el-form-item label="知识点" prop="knowledge_uid">
                        <el-select
                            v-model="queryQuestionParams.knowledge_uid"
                            clearable
                            filterable
                            placeholder="请选择知识点"
                            style="width: 200px"
                        >
                            <el-option
                                v-for="item in knowledgeList"
                                :key="item.uid"
                                :label="item.title"
                                :value="item.uid"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="标签" prop="label_uid">
                        <el-select
                            v-model="queryQuestionParams.label_uid"
                            placeholder="请选择标签"
                            clearable
                            style="width: 150px"
                        >
                            <el-option label="全部" value="" />
                            <el-option
                                v-for="item in labelList"
                                :key="item.uid"
                                :label="item.title"
                                :value="item.uid"
                            />
                        </el-select>
                    </el-form-item>
                    <el-form-item label="题干" prop="title">
                        <el-input
                            v-model="queryQuestionParams.title"
                            placeholder="搜索题干"
                            clearable
                            style="width: 150px"
                        />
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" @click="searchQuestions">查询</el-button>
                        <el-button @click="resetQuestionParams">重置</el-button>
                        <el-button type="success" @click="confirmSelectedQuestions"
                            >确认添加</el-button
                        >
                    </el-form-item>
                </el-form>
            </el-card>

            <el-table
                :data="questionPager.lists"
                @selection-change="handleQuestionSelectionChange"
                stripe
                border
                max-height="500"
                v-loading="questionPager.loading"
            >
                <el-table-column
                    type="selection"
                    width="55"
                    align="center"
                    :selectable="checkQuestionSelectable"
                />
                <el-table-column label="题目UID" prop="uid" width="180" align="center">
                    <template #default="{ row }">
                        <el-tag type="info" effect="plain" size="small">{{ row.uid }}</el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="题型" width="100" align="center">
                    <template #default="{ row }">
                        <el-tag :type="getExamTypeTagType(row.exam_type)" size="small">
                            {{ getExamTypeName(row.exam_type) }}
                        </el-tag>
                    </template>
                </el-table-column>
                <el-table-column label="题干" prop="title" min-width="300" show-overflow-tooltip>
                    <template #default="{ row }">
                        <div v-html="row.title"></div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="章节"
                    prop="chapter.title"
                    width="120"
                    show-overflow-tooltip
                />
                <el-table-column
                    label="知识点"
                    prop="knowledge.title"
                    width="120"
                    show-overflow-tooltip
                />
                <el-table-column label="标签" width="150" show-overflow-tooltip>
                    <template #default="{ row }">
                        <div v-if="row.labels?.length" class="flex flex-wrap gap-1">
                            <el-tag
                                v-for="(label, index) in row.labels.slice(0, 2)"
                                :key="index"
                                size="small"
                                effect="plain"
                            >
                                {{ label.title }}
                            </el-tag>
                            <el-tag v-if="row.labels.length > 2" size="small" type="info">
                                +{{ row.labels.length - 2 }}
                            </el-tag>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="分值" prop="score" width="80" align="center" />
            </el-table>

            <div class="flex mt-4 justify-end">
                <pagination v-model="questionPager" @change="getQuestionLists" />
            </div>
        </el-dialog>
        <!--    具体题目选择结束-->
    </div>
</template>

<script setup lang="ts">
import {
    Bottom,
    Delete,
    DocumentCopy,
    Folder,
    PriceTag,
    Select,
    Top
} from '@element-plus/icons-vue'
import { ElLoading } from 'element-plus'
import { useRouter } from 'vue-router'

import { apiExamCategoryTree } from '@/api/exam/exam_category'
import { apiTenantExamChapterTree } from '@/api/exam/tenant_exam_chapter'
import { apiTenantExamKnowledgeTree } from '@/api/exam/tenant_exam_knowledge'
import { apiTenantExamLabelLists } from '@/api/exam/tenant_exam_label'
import { apiTenantExamLibraryLists } from '@/api/exam/tenant_exam_library'
import { apiTenantExamSavePaper, apiTenantPaperDetail } from '@/api/exam/tenant_exam_paper'
import {
    apiTenantExamQuestionList,
    apiTenantExamQuestionLists
} from '@/api/exam/tenant_exam_question'
import { useDictData } from '@/hooks/useDictOptions'
import { usePaging } from '@/hooks/usePaging'
import feedback from '@/utils/feedback'

const dialogLibraryVisible = ref(false)
const dialogQuestionVisible = ref(false)
const route = useRoute()
const router = useRouter()

// 解析展开状态
const analysisExpanded = ref<Record<number, boolean>>({})

// 判断解析是否过长（超过200字符）
const isAnalysisLong = (analysis: string): boolean => {
    if (!analysis) return false
    const textContent = analysis.replace(/<[^>]*>/g, '')
    return textContent.length > 200
}

// 切换解析展开/收起
const toggleAnalysis = (index: number) => {
    analysisExpanded.value[index] = !analysisExpanded.value[index]
}

// 格式化子题答案
const formatSubAnswer = (answer: any): string => {
    if (!answer) return '-'
    if (Array.isArray(answer)) {
        return answer.join(', ')
    }
    if (typeof answer === 'string') {
        try {
            const parsed = JSON.parse(answer)
            return Array.isArray(parsed) ? parsed.join(', ') : String(parsed)
        } catch {
            return answer
        }
    }
    return String(answer)
}

// 解析option字段（可能是JSON字符串）
const parseOption = (option: any): any[] => {
    if (!option) return []
    if (Array.isArray(option)) return option
    if (typeof option === 'string') {
        try {
            const parsed = JSON.parse(option)
            return Array.isArray(parsed) ? parsed : []
        } catch {
            return []
        }
    }
    return []
}

// 定义类型
interface ExamTypeData {
    total_score: number
    total_number: number
    score: number
    value: number
    exam_type: string
}

interface ExamItem {
    title: string
    option: any[]
    score: number
    analysis: string
    exam_type: number
    exam_type_text: string
    uid: string
    [key: string]: any
}

interface TotalData {
    exam_count: number
    exam_score: number
}

// 题型映射配置
const EXAM_TYPE_CONFIG = {
    1: { index: 0, name: '单选题' },
    2: { index: 1, name: '多选题' },
    3: { index: 2, name: '判断题' },
    4: { index: 3, name: '填空题' },
    5: { index: 4, name: '问答题' },
    6: { index: 5, name: '案例题' }
}

const formData = reactive<{
    data: ExamTypeData[]
    exam_list: ExamItem[]
    total: TotalData
}>({
    data: [],
    exam_list: [],
    total: {
        exam_count: 0,
        exam_score: 0
    }
})

// 计算只显示有题目的题型
const visibleExamTypes = computed(() => {
    return formData.data
        .map((item, index) => ({
            ...item,
            dataIndex: index
        }))
        .filter((item) => item.total_number > 0)
})

const fetchPaperDetail = async () => {
    try {
        const res = await apiTenantPaperDetail({ id: route.query.id })
        // 清空现有数据
        formData.data = []
        formData.exam_list = []
        formData.total = { exam_count: 0, exam_score: 0 }

        // 赋值新数据
        if (res.data && res.data.length > 0) {
            formData.data = res.data
        }
        if (res.exam_list && res.exam_list.length > 0) {
            // 将exam_type转换为数字类型
            formData.exam_list = res.exam_list.map((item: any) => ({
                ...item,
                exam_type: Number(item.exam_type)
            }))
        }
        if (res.total) {
            formData.total = res.total
        }
    } catch (error) {
        console.error('获取试卷详情失败:', error)
        feedback.msgError('获取试卷详情失败')
    }
}
fetchPaperDetail()

/**
 * 组卷处理逻辑开始
 */
// 保存组卷数据
const savePaper = async () => {
    try {
        // 验证是否有试题
        if (!formData.exam_list || formData.exam_list.length === 0) {
            feedback.msgWarning('请至少添加一道试题')
            return
        }

        // 将exam_list中的exam_type由字符串类型转换为数字类型
        const formattedExamList = formData.exam_list.map((item) => {
            const convertedItem = {
                ...item,
                exam_type: Number(item.exam_type)
            }
            return convertedItem
        })

        const params = {
            id: route.query.id,
            data: formData.data,
            exam_list: formattedExamList,
            total: formData.total
        }

        await apiTenantExamSavePaper(params)
        feedback.msgSuccess('保存成功')
        router.back()
    } catch (error) {
        console.error('保存失败:', error)
        feedback.msgError('保存失败')
    }
}

// 删除试题
const deleteExam = async (exam_index: number) => {
    try {
        await feedback.confirm('确定要删除该试题吗？')
        formData.exam_list.splice(exam_index, 1)
        scoreCalculate()
        feedback.msgSuccess('删除成功')
    } catch (error) {
        // 用户取消删除
    }
}

// 试题积分变化
const handleScoreChange = (index: number, value: number | undefined) => {
    console.log(value, index)
    scoreCalculate()
}

// 批量设置分数
const handlePerScoreChange = (index: number, value: number | null | undefined) => {
    const scoreValue = parseFloat(String(value || 0)) || 0

    // 根据索引找到对应的题型
    const typeMap = Object.entries(EXAM_TYPE_CONFIG).find(([_, config]) => config.index === index)

    if (!typeMap) return

    const examTypeValue = parseInt(typeMap[0])

    formData.exam_list.forEach((item: any, key: number) => {
        if (parseInt(item.exam_type) === examTypeValue) {
            formData.exam_list[key].score = scoreValue
        }
    })

    scoreCalculate()
}

// 计算试题总数和试题总分
const scoreCalculate = () => {
    // 初始化各题型统计
    const typeStats: Record<number, { score: number; count: number }> = {}
    Object.keys(EXAM_TYPE_CONFIG).forEach((type) => {
        typeStats[parseInt(type)] = { score: 0, count: 0 }
    })

    let exam_score = 0 // 总分数

    // 统计各题型数据
    formData.exam_list.forEach((item: any) => {
        const score = parseFloat(item.score) || 0
        const examType = parseInt(item.exam_type)

        exam_score += score

        if (typeStats[examType]) {
            typeStats[examType].score += score
            typeStats[examType].count += 1
        }
    })

    // 更新各题型统计（确保data数组足够长）
    Object.entries(EXAM_TYPE_CONFIG).forEach(([type, config]) => {
        const examType = parseInt(type)
        const stats = typeStats[examType]

        // 如果data数组长度不够，扩展它
        while (formData.data.length <= config.index) {
            formData.data.push({
                total_score: 0,
                total_number: 0,
                score: 0,
                value: formData.data.length + 1,
                exam_type: ''
            })
        }

        if (formData.data[config.index]) {
            formData.data[config.index].total_score = stats.score
            formData.data[config.index].total_number = stats.count
            formData.data[config.index].exam_type = config.name
        }
    })

    // 更新总计
    formData.total.exam_score = exam_score
    formData.total.exam_count = Object.values(typeStats).reduce(
        (sum, stats) => sum + stats.count,
        0
    )
}

// 上移功能
const moveUp = (index: number) => {
    if (index <= 0) return
    const newItems = [...formData.exam_list]
    ;[newItems[index - 1], newItems[index]] = [newItems[index], newItems[index - 1]]
    formData.exam_list = newItems
}

// 下移功能
const moveDown = (index: number) => {
    if (index >= formData.exam_list.length - 1) return
    const newItems = [...formData.exam_list]
    ;[newItems[index], newItems[index + 1]] = [newItems[index + 1], newItems[index]]
    formData.exam_list = newItems
}
/**
 * 组卷处理逻辑结束
 */

/**
 * 题库处理逻辑开始
 */
const queryLibraryParams = reactive({
    title: '',
    is_show: '',
    category_uid: '',
    author: '',
    free_state: '',
    year: '',
    hot_state: '',
    recommend_state: ''
})
const selectData = ref<any[]>([])
const categoryList = reactive<any[]>([])
const handleSelectionChange = (val: any[]) => {
    selectData.value = val.map(({ id }) => id)
}
const handleLibraryCategoryChange = (value: any) => {
    queryLibraryParams.category_uid = value || ''
}

const confirmLibrary = async () => {
    try {
        // 验证是否选择了题库
        if (!selectData.value || selectData.value.length === 0) {
            feedback.msgWarning('请至少选择一个题库')
            return
        }

        const loading = ElLoading.service({
            lock: true,
            text: '试题努力加载中',
            background: 'rgba(0, 0, 0, 0.7)'
        })

        const res = await apiTenantExamQuestionList({ id: selectData.value })

        // 清空现有数据
        formData.data = []
        formData.exam_list = []
        formData.total = { exam_count: 0, exam_score: 0 }

        // 赋值新数据
        if (res.data && res.data.length > 0) {
            formData.data = res.data
        }
        if (res.exam_list && res.exam_list.length > 0) {
            // 将exam_type转换为数字类型
            formData.exam_list = res.exam_list.map((item: any) => ({
                ...item,
                exam_type: Number(item.exam_type)
            }))
        }
        if (res.total) {
            formData.total = res.total
        }

        dialogLibraryVisible.value = false
        loading.close()
        feedback.msgSuccess(`成功加载 ${res.exam_list?.length || 0} 道试题`)
    } catch (error) {
        console.error('加载试题失败:', error)
        feedback.msgError('加载试题失败')
    }
}
const { dictData } = useDictData(
    'show_status,price_typ,data_year,hot_state,recommend_state,exam_level'
)

const { pager, getLists, resetParams, resetPage } = usePaging({
    fetchFun: apiTenantExamLibraryLists,
    params: queryLibraryParams
})

const fetchExamCategoryList = async () => {
    try {
        const res = await apiExamCategoryTree()
        console.log('题库分类响应:', res)
        // 清空旧数据并添加新数据
        categoryList.length = 0
        categoryList.push(...(res.lists || []))
        console.log('题库分类数据:', categoryList)
    } catch (error) {
        console.error('获取题库分类失败:', error)
    }
}

const fetchLibraryLists = async () => {
    await getLists()
    await fetchExamCategoryList()
    dialogLibraryVisible.value = true
}
/**
 * 题库处理逻辑结束
 */

/**
 * 具体题目选择逻辑开始
 */
const queryQuestionParams = reactive({
    library_uid: '',
    title: '',
    exam_type: '',
    chapter_uid: '',
    knowledge_uid: '',
    label_uid: ''
})

const selectedQuestions = ref<any[]>([])
const chapterList = ref<any[]>([])
const knowledgeList = ref<any[]>([])
const labelList = ref<any[]>([])
const availableLibraries = ref<any[]>([])

// 题型配置
const getExamTypeName = (type: number): string => {
    const typeMap: Record<number, string> = {
        1: '单选题',
        2: '多选题',
        3: '判断题',
        4: '填空题',
        5: '问答题',
        6: '案例题'
    }
    return typeMap[type] || ''
}

const getExamTypeTagType = (
    type: number
): 'primary' | 'success' | 'warning' | 'danger' | 'info' => {
    const typeMap: Record<number, 'primary' | 'success' | 'warning' | 'danger' | 'info'> = {
        1: 'primary',
        2: 'success',
        3: 'warning',
        4: 'danger',
        5: 'info',
        6: 'primary'
    }
    return typeMap[type] || 'info'
}

// 题目列表分页
const questionPager = reactive({
    page: 1,
    size: 20,
    count: 0,
    lists: [] as any[],
    loading: false
})

// 获取题目列表
const getQuestionLists = async () => {
    try {
        questionPager.loading = true
        const params = {
            ...queryQuestionParams,
            library_uid: queryQuestionParams.library_uid,
            page_no: questionPager.page,
            page_size: questionPager.size
        }
        const res = await apiTenantExamQuestionLists(params)

        // 处理章节信息：将chapter_uid转为chapter对象
        const lists = (res.lists || []).map((item: any) => {
            const newItem = { ...item }

            // 如果没有chapter但有chapter_uid，查找chapter对象
            if (!newItem.chapter && newItem.chapter_uid) {
                newItem.chapter = getChapterByUid(newItem.chapter_uid)
            }

            // 如果没有knowledge但有knowledge_uid，查找knowledge对象
            if (!newItem.knowledge && newItem.knowledge_uid) {
                newItem.knowledge = getKnowledgeByUid(newItem.knowledge_uid)
            }

            return newItem
        })

        questionPager.lists = lists
        questionPager.count = res.count || 0
    } catch (error) {
        console.error('获取题目列表失败:', error)
        feedback.msgError('获取题目列表失败')
    } finally {
        questionPager.loading = false
    }
}

// 显示题目选择器
const showQuestionSelector = async () => {
    try {
        // 获取可用题库列表
        const res = await apiTenantExamLibraryLists({ page_size: 1000 })
        availableLibraries.value = res.lists || []

        // 重置查询参数
        Object.assign(queryQuestionParams, {
            library_uid: '',
            title: '',
            exam_type: '',
            chapter_uid: '',
            knowledge_uid: '',
            label_uid: ''
        })

        selectedQuestions.value = []
        dialogQuestionVisible.value = true
    } catch (error) {
        console.error('获取题库列表失败:', error)
        feedback.msgError('获取题库列表失败')
    }
}

// 题库变化时加载章节和标签
const handleLibraryChange = async (libraryUid: string) => {
    if (!libraryUid) {
        chapterList.value = []
        knowledgeList.value = []
        labelList.value = []
        queryQuestionParams.chapter_uid = ''
        queryQuestionParams.knowledge_uid = ''
        queryQuestionParams.label_uid = ''
        return
    }

    try {
        // 加载章节
        const chapterRes = await apiTenantExamChapterTree({ library_uid: libraryUid })
        console.log('章节数据响应:', chapterRes)
        // 兼容多种数据格式
        const chapterLists = chapterRes?.lists || chapterRes?.data?.lists || []
        chapterList.value = chapterLists
        console.log('章节列表数据:', chapterList.value)

        // 加载知识点（全部）
        const knowledgeRes = await apiTenantExamKnowledgeTree({ library_uid: libraryUid })
        console.log('知识点数据响应:', knowledgeRes)
        knowledgeList.value = knowledgeRes.lists || []
        console.log('知识点列表数据:', knowledgeList.value)

        // 加载标签
        const labelRes = await apiTenantExamLabelLists({ library_uid: libraryUid })
        console.log('标签数据响应:', labelRes)
        if (labelRes?.lists) {
            labelList.value = labelRes.lists
            console.log('标签列表数据:', labelList.value)
        } else if (labelRes?.data?.lists) {
            labelList.value = labelRes.data.lists
            console.log('标签列表数据:', labelList.value)
        }

        // 自动查询题目
        await getQuestionLists()
    } catch (error) {
        console.error('加载筛选数据失败:', error)
    }
}

// 获取章节信息（根据UID）
const getChapterByUid = (uid: string): any => {
    if (!uid || !chapterList.value.length) return null

    const findChapter = (list: any[]): any => {
        for (const item of list) {
            if (item.uid === uid) return item
            if (item.children?.length) {
                const found = findChapter(item.children)
                if (found) return found
            }
        }
        return null
    }

    return findChapter(chapterList.value)
}

// 获取知识点信息（根据UID）
const getKnowledgeByUid = (uid: string): any => {
    if (!uid || !knowledgeList.value.length) return null

    const findKnowledge = (list: any[]): any => {
        for (const item of list) {
            if (item.value === uid) return { uid: item.value, title: item.label }
            if (item.children?.length) {
                const found = findKnowledge(item.children)
                if (found) return found
            }
        }
        return null
    }

    return findKnowledge(knowledgeList.value)
}

// 章节变化时加载知识点
const handleChapterChange = async (chapterUid: any) => {
    const uid = String(chapterUid || '')
    if (!uid) {
        knowledgeList.value = []
        queryQuestionParams.knowledge_uid = ''
        return
    }

    try {
        const res = await apiTenantExamKnowledgeTree({ chapter_uid: uid })
        console.log('知识点数据响应:', res)
        knowledgeList.value = res || []
        console.log('知识点列表数据:', knowledgeList.value)
    } catch (error) {
        console.error('加载知识点失败:', error)
    }
}

// 查询题目
const searchQuestions = () => {
    questionPager.page = 1
    getQuestionLists()
}

// 重置查询参数
const resetQuestionParams = () => {
    Object.assign(queryQuestionParams, {
        title: '',
        exam_type: '',
        chapter_uid: '',
        knowledge_uid: '',
        label_uid: ''
    })
    searchQuestions()
}

// 题目选择变化
const handleQuestionSelectionChange = (val: any[]) => {
    selectedQuestions.value = val
}

// 检查题目是否可选（已在试卷中的不可选）
const checkQuestionSelectable = (row: any): boolean => {
    return !formData.exam_list.some((item) => item.uid === row.uid)
}

// 确认添加选中的题目
const confirmSelectedQuestions = () => {
    if (!selectedQuestions.value || selectedQuestions.value.length === 0) {
        feedback.msgWarning('请至少选择一道题目')
        return
    }

    // 转换题目格式并添加到试卷
    selectedQuestions.value.forEach((question: any) => {
        // 解析option字段
        const parsedOption = parseOption(question.option)

        // 处理章节信息
        let chapterInfo = question.chapter
        if (!chapterInfo && question.chapter_uid) {
            chapterInfo = getChapterByUid(question.chapter_uid)
        }

        // 处理知识点信息
        let knowledgeInfo = question.knowledge
        if (!knowledgeInfo && question.knowledge_uid) {
            knowledgeInfo = getKnowledgeByUid(question.knowledge_uid)
        }

        const examItem: ExamItem = {
            uid: question.uid,
            title: question.title,
            exam_type: parseInt(question.exam_type),
            exam_type_text: getExamTypeName(parseInt(question.exam_type)),
            score: parseFloat(question.score) || 2.0,
            option: parsedOption,
            analysis: question.analysis || '',
            answer: question.answer || '',
            chapter: chapterInfo,
            knowledge: knowledgeInfo,
            labels: question.labels || []
        }
        formData.exam_list.push(examItem)
    })

    // 重新计算分数
    scoreCalculate()

    // 关闭对话框
    dialogQuestionVisible.value = false
    feedback.msgSuccess(`成功添加 ${selectedQuestions.value.length} 道题目`)
}
/**
 * 具体题目选择逻辑结束
 */
</script>

<style scoped>
.exam-container {
    display: flex;
    height: 100vh;
}

.exam-main {
    padding: 0 0 0 20px;
}

.left-main {
    display: flex;
    justify-content: space-between;
    height: 90px;
}

.left-main-left {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.left-main-left-score {
    display: flex;
    justify-content: space-between;
    flex-direction: column;
}

.left-main-right {
    display: flex;
    justify-content: space-between;
    flex-direction: column;
    height: 100%;
}

.exam-title {
    font-weight: bolder;
}

.exam-option-item {
    display: flex;
    justify-content: flex-start;
}

.font-setting {
    font-weight: bolder;
    color: #4a5dff;
}

.exam-option-selected {
    width: 20px;
    height: 20px;
    text-align: center;
    line-height: 20px;
    border-radius: 50%;
    background-color: #4a5dff;
    color: #ffffff;
}

.exam-option {
    width: 20px;
    height: 20px;
    text-align: center;
    line-height: 20px;
    border-radius: 50%;
    background-color: #ffffff;
    border: 1px solid #c8c8d2;
}

.exam-answer {
    color: #adadad;
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
}

.analysis-content {
    flex: 1;
    margin-left: 8px;
}

.analysis-collapsed {
    max-height: 100px;
    overflow: hidden;
    position: relative;
}

.analysis-collapsed::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 30px;
    background: linear-gradient(to bottom, transparent, white);
}

.flex {
    display: flex;
}

.flex-wrap {
    flex-wrap: wrap;
}

.gap-1 {
    gap: 4px;
}

/* 案例题子题样式 */
.case-sub-question {
    padding: 16px;
    margin-bottom: 16px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #409eff;
}

.sub-question-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e4e7ed;
}

.sub-question-title {
    margin-top: 8px;
    margin-bottom: 8px;
    line-height: 1.6;
    color: #303133;
}

.sub-question-answer {
    padding: 8px 12px;
    background-color: #f0f9ff;
    border-radius: 4px;
    border-left: 3px solid #67c23a;
}

/* 问答题答案卡片 */
.answer-card {
    border: 1px solid #e4e7ed;
}

.answer-card-header {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: #67c23a;
}

.answer-content {
    line-height: 1.8;
    color: #606266;
}

/* 空状态样式 */
:deep(.el-empty__description) {
    margin-top: 20px;
    font-size: 14px;
    color: #909399;
}

:deep(.el-empty__bottom) {
    margin-top: 30px;
}
</style>
