# 图片编辑页设计（/advanced/image-process）

## 页面信息架构
- 页面目标：针对单张图片执行结构化处理（缩放、滤镜、水印），即时返回处理结果。
- 信息分区：
  - 输入区：`图片 Key`、`尺寸`、`滤镜`、`水印文本`。
  - 操作区：`执行处理`。
  - 结果区：返回 JSON（核心为 `content_base64` + 输出尺寸 + driver）。
- 导航位置：高阶工具页签之一，依赖统一壳层 `advanced-shell`。

## 交互流程
1. 用户输入 `key` 与可选处理参数。
2. 前端本地组装 payload（`resize/filters/watermark`），空字段不提交。
3. 调用 `POST /advanced-api/images/{key}/process`。
4. 成功：渲染处理结果，支持后续下载/预览（前端可扩展）。
5. 失败：展示 `message`，保留原输入以便修正并重试。

## 状态机
| 状态 | 触发条件 | 页面行为 | 可迁移状态 |
|---|---|---|---|
| `idle` | 初始进入 | 展示空表单 | `validating` |
| `validating` | 点击执行 | 校验 `key` 与基础格式 | `submitting` / `error` |
| `submitting` | 发起 API | 按钮 loading，防重复提交 | `success` / `error` |
| `success` | `status=true` | 展示处理结果数据 | `validating` |
| `error` | `status=false` 或网络异常 | 显示错误提示 | `validating` |

## API映射（基于 advanced-api 路由）
| 场景 | 路由名 | 方法与路径 | 请求参数 | 关键响应 |
|---|---|---|---|---|
| 执行图片处理 | `advanced.api.images.process` | `POST /advanced-api/images/{key}/process` | `resize`、`filters`、`watermark` | `data.key`、`data.driver`、`data.operations`、`data.content_base64` |
| 选图辅助（可选） | `advanced.api.images.search` | `GET /advanced-api/images/search` | `q/album_id/review_status/order...` | 分页图片列表，用于选择 `key` |

## 数据模型
- `ImageProcessRequest`
  - `resize.width?: int(1~10000)`
  - `resize.height?: int(1~10000)`
  - `resize.fit?: contain|cover|fill|inside|outside`
  - `filters.grayscale?: bool`
  - `filters.blur?: number(0~50)`
  - `filters.sharpen?: number(0~10)`
  - `filters.contrast?: number(-100~100)`
  - `watermark.text?: string<=500`
  - `watermark.position?: 9 宫格位置`
  - `watermark.size?: int(8~200)`
  - `watermark.color?: #RRGGBB[AA]`
- `ImageProcessResponse.data`
  - `key: string`
  - `driver: imagick|libvips`
  - `operations: object`
  - `mimetype: string`
  - `width: int`
  - `height: int`
  - `content_base64: string`

## 错误与空态
- 前端校验错误：缺少 `key`。
- 服务端业务错误：
  - `图片不存在`
  - `仅支持处理图片类型资源`
  - `读取图片失败`
  - 参数校验失败（如 `watermark.color` 非法）。
- 空态：
  - 初始空态：提示“输入图片 Key 后执行处理”。
  - 结果空态：请求失败或被取消时显示占位信息，不清空上一次成功结果（建议）。

## 可观测性点
- 服务端已接入审计：`action=api.image.process`，记录 `target(key)`、`driver`、`user_id`、`trace_id`、`ip`。
- 建议前端埋点：
  - `advanced_image_process_submit`
  - `advanced_image_process_success`
  - `advanced_image_process_error`
  - 指标：成功率、P95 延迟、平均处理尺寸。

## 安全设计
- 鉴权：依赖 `auth` 中间件，未登录不可访问。
- 数据隔离：按当前 `space_id` 查询图片，仅允许处理当前用户在当前空间的图片。
- 输入约束：处理参数由 `ImageProcessExecutor::RULES` 严格校验。
- 资源保护：非图片 MIME 类型拒绝处理，防止二进制误处理。
- 传输安全：通过同源会话 + CSRF 保护提交；结果中 `content_base64` 仅在前端内存使用，避免落日志。
