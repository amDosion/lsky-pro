# ai-config 页面需求文档

## 目标
建立 AI 配置页面，统一管理当前启用 provider、API Key、Base URL、模型列表与默认模型，为 AI 提示词及后续 AI 能力提供稳定的配置基线。

## 用户故事
- 作为管理员，我希望在一个页面中切换当前启用 provider，并维护模型与密钥。
- 作为开发/运维，我希望看到每个 provider 的 ready 状态，快速定位为什么 AI 功能不可用。
- 作为使用者，我希望 AI 提示词等功能默认复用系统已配置的 provider/model，而不是各处重复填配置。

## 功能范围
### In Scope
- provider 切换：`gpt/deepseek/qwen/gemini`。
- 配置项维护：`api_key/base_url/default_model/models[]`。
- 配置读取与保存：`GET/PUT /advanced-api/ai/config`。
- ready 状态展示：基于 `api_key + default_model` 判定。
- 管理员可保存，非管理员只能看到失败提示。

### Out of Scope
- provider 账单、配额与调用统计。
- 在线探活或真实连通性压测。
- 细粒度多空间 AI 配置隔离。

## 输入输出
### 输入
- `active_provider`
- `providers.<name>.api_key`
- `providers.<name>.base_url`
- `providers.<name>.default_model`
- `providers.<name>.models[]`

### 输出
- 当前启用 provider
- 各 provider 的标准化配置与 ready 状态
- `provider_options[]` 供前端渲染选择器与概览卡片

## 权限
- `Admin/Super Admin`：查看与保存 AI 配置。
- 非管理员：访问保存接口返回失败，不得修改全局 AI 配置。

## 异常处理
- provider 不在支持列表：阻止保存。
- model 列表为空：回退到系统默认模型列表。
- default model 不在 models 中：回退到该 provider 第一个模型。
- 非管理员保存：返回“仅管理员可修改 AI 配置”。

## 验收标准
- 页面可展示 4 个 provider 的当前配置与 ready 状态。
- 保存后再次读取可拿到标准化后的配置。
- 修改 `active_provider` 后，AI 提示词任务能够读取到对应 provider/model 基线。
- 文档与菜单命名统一使用 `ai-config`，不再将 `templates` 描述为主菜单页。

## 非功能要求
- 安全：API Key 仅在受控页面与接口中读写，不写入审计明文。
- 一致性：配置保存后立即清理缓存，后续读取获取最新值。
- 可维护性：新增 provider 时仅需补充元数据与验证规则。
