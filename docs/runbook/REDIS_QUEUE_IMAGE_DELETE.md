# Redis Queue Runbook（图片删除异步链路）

## 1. 适用范围
本 runbook 用于 `IMAGE_DELETE_ASYNC=true` 时的图片物理文件异步删除链路运维。

## 2. 前置条件
1. Redis 可用，应用可连接 Redis。
2. `.env` 已配置：
```dotenv
QUEUE_CONNECTION=redis
IMAGE_DELETE_ASYNC=true
IMAGE_DELETE_QUEUE_CONNECTION=redis
IMAGE_DELETE_QUEUE=image-delete
IMAGE_DELETE_QUEUE_TRIES=3
IMAGE_DELETE_QUEUE_TIMEOUT=120
IMAGE_DELETE_QUEUE_BACKOFF=10,30,60
```
3. 失败任务表已存在（项目已包含 migration）：
```bash
php artisan migrate
```

## 3. 启动 Worker
仅消费图片删除队列：
```bash
php artisan queue:work redis --queue=image-delete --tries=3 --timeout=120 --backoff=10,30,60
```

生产建议（Supervisor/systemd）：
1. 常驻运行至少 1 个专用 worker。
2. 与其他业务队列隔离（单独 `--queue=image-delete`）。
3. 部署后执行 `php artisan queue:restart` 平滑重启 worker。

## 4. 失败重试
查看失败任务：
```bash
php artisan queue:failed
```

重试单条失败任务：
```bash
php artisan queue:retry <failed-job-uuid>
```

重试全部失败任务：
```bash
php artisan queue:retry all
```

清理失败任务（确认无须保留时）：
```bash
php artisan queue:flush
```

## 5. 常见排查
1. 任务持续堆积：
- 检查 worker 是否运行。
- 检查命令是否监听 `image-delete` 队列。
- 检查 Redis 连接与认证配置。

2. 任务进入 failed_jobs：
- 查看 `php artisan queue:failed` 中异常信息。
- 检查策略配置是否存在（策略被删后无法定位适配器会失败）。
- 检查对象存储凭据、网络、权限是否有效。

3. 删除记录成功但物理文件未删：
- 确认 `IMAGE_DELETE_ASYNC=true` 且 worker 正常消费。
- 若存在同策略相同 `md5+sha1` 的其他图片记录，任务会跳过物理删除（避免误删共享文件）。

## 6. 回退方案
1. 将 `.env` 改为 `IMAGE_DELETE_ASYNC=false`。
2. 执行：
```bash
php artisan optimize:clear
php artisan queue:restart
```
3. 新删除请求将回退为同步物理删除链路。
