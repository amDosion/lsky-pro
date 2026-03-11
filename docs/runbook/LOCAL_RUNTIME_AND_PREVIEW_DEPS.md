# 本地运行与预览依赖补全

## 1. 是否可以直接运行项目目录（不走 Docker）
可以。项目是标准 Laravel，满足 PHP/扩展/数据库依赖后，可以直接在
`/root/code-server/config/workspace/lsky-pro/lsky` 运行。

## 2. 预览链路新增依赖
为了支持以下预览：
- `doc/docx/xls/xlsx`（转 PDF 后预览）
- `pdf`（首屏预览）
- `zip/rar`（内容清单预览）

需要安装系统命令：
- `soffice`（LibreOffice）
- `gs`（Ghostscript）
- `7z`（p7zip）
- `unzip`

## 3. 一键安装预览依赖
### 3.1 安装到宿主机
```bash
bash scripts/setup/install-preview-deps.sh --host
```

### 3.2 安装到运行容器
```bash
bash scripts/setup/install-preview-deps.sh --container lsky-pro
```

## 4. 本地目录直跑
先确认 `.env` 里的 DB/Redis 配置可用，然后执行：
```bash
bash scripts/setup/run-local.sh
```

脚本会执行：
1. `composer install`
2. `php artisan key:generate --force`
3. `php artisan migrate --force`
4. 若存在 npm：`npm ci && npm run dev`
5. 启动 `php artisan serve`

## 5. 验证点
1. 上传 `pdf/docx/xlsx/raw/psd/zip/rar` 能成功。
2. 我的图片页中点击文件主体可打开预览层。
3. `zip/rar` 预览显示压缩包内容清单文本图。

