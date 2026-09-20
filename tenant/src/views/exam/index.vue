<template>
    <div class="exam-container">
        <el-aside width="100px" class="exam-aside">
            <el-menu class="exam-menu">
                <div class="margin-bottom20 tl-text-center">
                    <el-text
                        :type="menuType === 'category' ? 'primary' : ''"
                        @click="menuChange('category')"
                        link
                        >题库分类</el-text
                    >
                </div>
                <div class="tl-text-center margin-bottom20">
                    <el-text
                        style="margin-left: 0"
                        :type="menuType === 'library' ? 'primary' : ''"
                        @click="menuChange('library')"
                        link
                        >题库管理</el-text
                    >
                </div>
            </el-menu>
        </el-aside>
        <el-main class="exam-main">
            <ExamCategory v-if="menuType === 'category'" />
            <ExamLibrary v-else-if="menuType === 'library'" />
        </el-main>
    </div>
</template>

<script setup lang="ts">
import ExamCategory from './exam_category/index.vue'
import ExamLibrary from './tenant_exam_library/index.vue'

const menuType = ref('category')

const menuChange = (type: string) => {
    menuType.value = type
}
</script>

<style scoped>
.exam-container {
    display: flex;
    /*height: 100vh;*/
}

.exam-aside {
    /*background-color: #f5f7fa;*/
}

.exam-menu {
    height: 100%;
    display: flex;
    padding: 10px 10px;
    flex-direction: column;
}

.exam-main {
    flex: 1;
    /*padding: 20px;*/
    /*background-color: #fff;*/
}

.el-main {
    padding: 0 !important;
}
.el-button--link:focus {
    outline: none !important;
}
</style>
