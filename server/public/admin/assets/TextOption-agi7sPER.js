import{h as _,B as f}from"./element-plus-3HMVa920.js";import{f as x}from"./tenant_exam_question-R2rDMGpJ.js";import{d as y,_ as w,o as v,c as V,a as o,W as e,P as n,U as s,u as a}from"./@vue-Eh_fo2Q7.js";import{d as B}from"./index-5uqe_97X.js";import"./lodash-es-ConpBW6D.js";import"./async-validator-DKvM95Vc.js";import"./@element-plus-DB6xhwyO.js";import"./dayjs-CMfar2R3.js";import"./balanced-match-mNcR6oh4.js";import"./@popperjs-D9SI2xQl.js";import"./@ctrl-r5W6hzzQ.js";import"./normalize-wheel-es-B6fDCfyv.js";import"./nprogress-Cgf5DU8x.js";import"./vue-router-C6EjJBYh.js";import"./pinia-DVNJ-8SP.js";import"./axios-C-n2IhIP.js";import"./lodash-BtPWuHkK.js";import"./@vueuse-DNhprs8A.js";import"./css-color-function-rJvg8h-6.js";import"./color-B0v57BL7.js";import"./clone-CuIhj1wH.js";import"./color-convert-BGgJB5UM.js";import"./color-name-BQ5IbGbl.js";import"./color-string-BhgG7-8u.js";import"./ms-CzQ2E3wO.js";import"./vue-clipboard3-8CNoFg4V.js";import"./clipboard-Dch_ozqB.js";import"./echarts-BR_QmBtV.js";import"./tslib-BDyQ-Jie.js";import"./zrender-B-CeXLwU.js";import"./highlight.js-D5uP1KGk.js";import"./@highlightjs-BKKoSBVW.js";const b={class:"margin-bottom20",style:{display:"flex"}},h={style:{width:"50%"}},A={class:"tip-head-title"},g={style:{width:"50%","padding-left":"20px"}},k={class:"tip-head-title"},C={class:"display-flex-start-center"},d=`下面是文本录入的试题格式，目前只支持单选题、多选题和判断题。每一道试题之间，需要换行。

题目：下列属于行政行为的是（ ）
A. 某县民政局建办公楼的行为
B. 某县民政局起诉建筑公司违约的行为
C. 某县民政局越权处罚违约的建筑公司的行为
D. 某县民政局依建筑合同奖励建筑公司的行为
答案：A
题型：单选题
解释：行政行为是指行政主体行使行政职权，作出的能够产生行政法律效果的行为。。

题目：下列属于行政行为的是（ ）
A. 某县民政局建办公楼的行为
B. 某县民政局起诉建筑公司违约的行为
C. 某县民政局越权处罚违约的建筑公司的行为
D. 某县民政局依建筑合同奖励建筑公司的行为
答案：A、B
题型：多选题
解释：行政行为是指行政主体行使行政职权，作出的能够产生行政法律效果的行为。。

题目：夏天小孩适合在河塘中游泳（ ）
A. 正确
B. 错误
答案：A
题型：判断题
解释：河塘水深，小孩应原理河塘。
`,T=y({__name:"TextOption",props:{libraryUid:{}},setup(u){const i=w({chapter_uid:"",library_uid:u.libraryUid,content:""}),c=async()=>{const m={...i};await x(m)};return(m,t)=>{const r=_,l=f;return v(),V("div",null,[o("div",b,[o("div",h,[o("div",A,[e(r,{type:"primary",icon:"InfoFilled",link:"",disabled:""},{default:n(()=>t[2]||(t[2]=[s("编辑区")])),_:1})]),e(l,{type:"textarea",modelValue:a(i).content,"onUpdate:modelValue":t[0]||(t[0]=p=>a(i).content=p),autosize:{minRows:40,maxRows:40},placeholder:d,resize:"none","show-word-limit":!0,maxlength:"65535"},null,8,["modelValue"])]),o("div",g,[o("div",k,[e(r,{type:"primary",icon:"View",link:"",disabled:""},{default:n(()=>t[3]||(t[3]=[s("预览区")])),_:1})]),e(l,{type:"textarea",modelValue:a(i).content,"onUpdate:modelValue":t[1]||(t[1]=p=>a(i).content=p),autosize:{minRows:40,maxRows:40},placeholder:d,resize:"none","show-word-limit":!0,maxlength:"65535",disabled:!0},null,8,["modelValue"])])]),o("div",C,[e(r,{type:"primary",onClick:c},{default:n(()=>t[4]||(t[4]=[s("保存试题")])),_:1}),e(r,{type:"warning"},{default:n(()=>t[5]||(t[5]=[s("重置数据")])),_:1})])])}}}),pt=B(T,[["__scopeId","data-v-8ac09645"]]);export{pt as default};
