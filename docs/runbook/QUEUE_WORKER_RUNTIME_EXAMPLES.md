# 队列 Worker 运行示例（systemd / docker-compose）

## 1. 前提
- 已配置队列驱动（推荐 Redis）：`.env` 中 `QUEUE_CONNECTION=redis`。
- 已完成基础配置缓存刷新：`php artisan config:clear && php artisan config:cache`。
- 生产环境应使用常驻 Worker + 进程守护。

## 2. Worker 基础命令
```bash
php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=120
```

可选参数建议：
- `--max-jobs=1000`：处理固定任务数后重启，降低内存碎片风险。
- `--max-time=3600`：运行上限 1 小时后重启。
- `--memory=256`：内存上限（MB）。

## 3. systemd 运行示例

### 3.1 服务文件
文件：`/etc/systemd/system/lsky-queue-worker.service`

```ini
[Unit]
Description=LSky Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/lsky
ExecStart=/usr/bin/php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=120 --max-time=3600
Restart=always
RestartSec=5
KillSignal=SIGTERM
TimeoutStopSec=60

[Install]
WantedBy=multi-user.target
```

### 3.2 启动与验证
```bash
sudo systemctl daemon-reload
sudo systemctl enable lsky-queue-worker
sudo systemctl start lsky-queue-worker
sudo systemctl status lsky-queue-worker --no-pager
journalctl -u lsky-queue-worker -n 200 --no-pager
```

## 4. docker-compose 运行示例

### 4.1 服务片段
将以下片段加入 `docker-compose.yml`（按实际服务名调整）：

```yaml
services:
  lsky-worker:
    image: lsky-pro:latest
    container_name: lsky-worker
    restart: always
    working_dir: /var/www/html
    command: php artisan queue:work redis --queue=default --sleep=3 --tries=3 --timeout=120 --max-time=3600
    env_file:
      - .env
    depends_on:
      - redis
      - lsky-app
```

### 4.2 启动与验证
```bash
docker compose up -d lsky-worker
docker compose ps
docker logs --tail=200 lsky-worker
```

## 5. 运维建议
- 部署时执行平滑重启：`php artisan queue:restart`。
- 队列积压监控：关注任务等待时间、失败任务数量、重试次数。
- 失败任务处理：
```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
```

## 6. 验收清单
- Worker 进程在重启后可自动拉起。
- 队列任务可被消费，失败任务可重试。
- 部署后执行 `queue:restart` 不影响长期稳定运行。
- 日志可定位到任务失败原因（异常堆栈可追踪）。

## 7. 完成定义（Done Criteria）
- 至少一种托管方式（systemd 或 docker-compose）在生产/预生产验证通过。
- 运行手册包含：启动、停止、重启、日志查看、失败重试全链路命令。
- 值班同学可按文档独立完成一次 Worker 故障恢复演练。
