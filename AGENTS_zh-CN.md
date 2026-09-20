# 智能代理

<skills_system priority="1">

## 可用技能

<!-- SKILLS_TABLE_START -->
<usage>
当用户要求你执行任务时，检查下面的可用技能是否有能帮助更有效地完成任务的。技能提供专业能力和领域知识。

如何使用技能：
- 调用：Bash("openskills read <skill-name>")
- 技能内容将加载，包含完成任务的详细说明
- 输出中提供的基础目录用于解析捆绑资源（references/、scripts/、assets/）

使用说明：
- 仅使用下面 <available_skills> 中列出的技能
- 不要调用已经在你的上下文中加载的技能
- 每个技能调用都是无状态的
</usage>

<available_skills>

<skill>
<name>algorithmic-art</name>
<description>使用 p5.js 创建算法艺术，支持种子随机和交互式参数探索。当用户要求使用代码创建艺术、生成艺术、算法艺术、流场或粒子系统时使用此技能。创建原创算法艺术，不要复制现有艺术家的作品以避免版权侵权。</description>
<location>project</location>
</skill>

<skill>
<name>brand-guidelines</name>
<description>将 Anthropic 的官方品牌颜色和排版应用于任何可能受益于 Anthropic 外观的工件。当涉及品牌颜色或风格指南、视觉格式或公司设计标准时使用此技能。</description>
<location>project</location>
</skill>

<skill>
<name>canvas-design</name>
<description>使用设计理念在 .png 和 .pdf 文档中创建精美的视觉艺术。当用户要求创建海报、艺术品、设计或其他静态作品时使用此技能。创建原创视觉设计，不要复制现有艺术家的作品以避免版权侵权。</description>
<location>project</location>
</skill>

<skill>
<name>doc-coauthoring</name>
<description>指导用户完成协作编写文档的结构化工作流程。当用户想要编写文档、提案、技术规范、决策文档或类似结构化内容时使用。此工作流程帮助用户高效传递上下文，通过迭代完善内容，并验证文档对读者有效。当用户提到编写文档、创建提案、起草规范或类似文档任务时触发。</description>
<location>project</location>
</skill>

<skill>
<name>docx</name>
<description>全面的文档创建、编辑和分析，支持跟踪更改、注释、格式保留和文本提取。当 Claude 需要处理专业文档（.docx 文件）时使用：(1) 创建新文档，(2) 修改或编辑内容，(3) 处理跟踪更改，(4) 添加注释，或任何其他文档任务。</description>
<location>project</location>
</skill>

<skill>
<name>frontend-design</name>
<description>创建具有高设计质量的独特生产级前端界面。当用户要求构建 Web 组件、页面、工件、海报或应用程序（例如网站、着陆页、仪表板、React 组件、HTML/CSS 布局，或美化任何 Web UI 时）使用此技能。生成创意、精美的代码和 UI 设计，避免通用 AI 美学。</description>
<location>project</location>
</skill>

<skill>
<name>internal-comms</name>
<description>一套资源，帮助我使用公司喜欢的格式编写各种内部通信。当被要求编写某种内部通信（状态报告、领导更新、3P 更新、公司通讯、常见问题解答、事件报告、项目更新等）时，Claude 应使用此技能。</description>
<location>project</location>
</skill>

<skill>
<name>mcp-builder</name>
<description>创建高质量 MCP（模型上下文协议）服务器的指南，使 LLM 能够通过精心设计的工具与外部服务交互。在构建 MCP 服务器以集成外部 API 或服务时使用，无论是在 Python（FastMCP）还是 Node/TypeScript（MCP SDK）中。</description>
<location>project</location>
</skill>

<skill>
<name>pdf</name>
<description>全面的 PDF 处理工具包，用于提取文本和表格、创建新 PDF、合并/拆分文档以及处理表单。当 Claude 需要填写 PDF 表单或大规模程序化处理、生成或分析 PDF 文档时使用。</description>
<location>project</location>
</skill>

<skill>
<name>pptx</name>
<description>演示文稿创建、编辑和分析。当 Claude 需要处理演示文稿（.pptx 文件）时使用：(1) 创建新演示文稿，(2) 修改或编辑内容，(3) 处理布局，(4) 添加注释或演讲者备注，或任何其他演示文稿任务。</description>
<location>project</location>
</skill>

<skill>
<name>skill-creator</name>
<description>创建有效技能的指南。当用户想要创建新技能（或更新现有技能）以通过专业知识、工作流程或工具集成扩展 Claude 的能力时，应使用此技能。</description>
<location>project</location>
</skill>

<skill>
<name>slack-gif-creator</name>
<description>创建针对 Slack 优化的动画 GIF 的知识和工具。提供约束、验证工具和动画概念。当用户要求为 Slack 创建动画 GIF 时使用，例如"为 Slack 制作一个 X 做 Y 的 GIF"。</description>
<location>project</location>
</skill>

<skill>
<name>theme-factory</name>
<description>使用主题为工件设置样式的工具包。这些工件可以是幻灯片、文档、报告、HTML 着陆页等。有 10 个预设主题，包含颜色/字体，您可以将其应用于已创建的任何工件，或实时生成新主题。</description>
<location>project</location>
</skill>

<skill>
<name>web-artifacts-builder</name>
<description>使用现代前端 Web 技术（React、Tailwind CSS、shadcn/ui）创建复杂、多组件 claude.ai HTML 工件的工具套件。用于需要状态管理、路由或 shadcn/ui 组件的复杂工件 - 不适用于简单的单文件 HTML/JSX 工件。</description>
<location>project</location>
</skill>

<skill>
<name>webapp-testing</name>
<description>使用 Playwright 与本地 Web 应用程序交互和测试的工具包。支持验证前端功能、调试 UI 行为、捕获浏览器截图和查看浏览器日志。</description>
<location>project</location>
</skill>

<skill>
<name>xlsx</name>
<description>全面的电子表格创建、编辑和分析，支持公式、格式设置、数据分析和可视化。当 Claude 需要处理电子表格（.xlsx、.xlsm、.csv、.tsv 等）时使用：(1) 创建带有公式和格式的新电子表格，(2) 读取或分析数据，(3) 修改现有电子表格同时保留公式，(4) 电子表格中的数据分析和可视化，或 (5) 重新计算公式。</description>
<location>project</location>
</skill>

</available_skills>
<!-- SKILLS_TABLE_END -->

</skills_system>