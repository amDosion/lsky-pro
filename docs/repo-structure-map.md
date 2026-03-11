# Repository Structure Map

Generated at: 2026-03-08T23:40:24.657Z

## Directory Tree (Top-Level)

```text
.
├── app/
├── artisan
├── bootstrap/
├── composer-setup.php
├── composer.json
├── composer.lock
├── config/
├── database/
├── deploy/
├── docs/
├── installed.lock
├── lang/
├── LICENSE
├── package-lock.json
├── package.json
├── phpunit.xml
├── phpunit.xml.bak
├── PLANS.md
├── png
├── prompts/
├── public/
├── queue/
├── README.md
├── resources/
├── routes/
├── scripts/
├── storage/
├── tailwind.config.js
├── tests/
├── vendor/
└── webpack.mix.js
```

> Note: output truncated at 400 files. Increase --max-files if needed.

## File Responsibility Matrix

| Path | Layer | File Responsibility |
| --- | --- | --- |
| .env.example | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Commands/Bootstrap.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Commands/CleanupExpiredImages.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Commands/Install.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Commands/MakeThumbnails.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Commands/Upgrade.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Console/Kernel.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Contracts/HookPluginInterface.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/ConfigKey.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/GroupConfigKey.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/ImagePermission.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/ImageReviewStatus.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Mail/SmtpOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/PastedAction.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Scan/AliyunOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Scan/NsfwJsOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Scan/TencentOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/CosOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/FtpOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/KodoOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/LocalOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/MinioOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/OssOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/S3Option.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/SftpOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/UssOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Strategy/WebDavOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/StrategyKey.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/UserConfigKey.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/UserStatus.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Watermark/FontOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Watermark/ImageOption.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Enums/Watermark/Mode.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Exceptions/Handler.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Exceptions/UploadException.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Admin/GroupController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Admin/ImageController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Admin/SettingController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Admin/StrategyController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Admin/UserController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/Admin/ReviewController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/AiConfigController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/AiController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/AiPromptTaskController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/AlbumController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/AnalyticsController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/ImageController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/ProcessingController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/ProcessTemplateController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/SpaceController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/StrategyController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/TokenController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/UploadTaskController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/UserController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Api/V1/WebhookController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/AuthenticatedSessionController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/ConfirmablePasswordController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/EmailVerificationNotificationController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/EmailVerificationPromptController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/NewPasswordController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/PasswordResetLinkController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/RegisteredUserController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/SocialAuthController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Auth/VerifyEmailController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Common/ApiController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Common/GalleryController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Concerns/AuditsOperations.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/Controller.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/User/AlbumController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/User/ImageController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Controllers/User/UserController.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Kernel.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/Authenticate.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/AuthenticateWithAdmin.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/CheckIsEnableApi.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/CheckIsEnableGallery.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/CheckIsEnableGuestUpload.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/CheckIsEnableRegistration.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/CheckIsInstalled.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/EncryptCookies.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/EnforceTokenRestrictions.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/PreventRequestsDuringMaintenance.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/RedirectIfAuthenticated.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/RequestContext.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/ResolveTeamSpaceContext.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/TrimStrings.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/TrustHosts.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/TrustProxies.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Middleware/VerifyCsrfToken.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/Admin/GroupRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/Admin/StrategyRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/Admin/UserRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/AlbumRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/Auth/LoginRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/FormRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/ImageRenameRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Requests/UserSettingRequest.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Http/Result.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/DeleteImagePhysicalFileJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/DeliverWebhookEventJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/GenerateAiPromptTaskJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/ProcessImageOcrPlaceholderJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/ProcessUploadTaskJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Jobs/RunImageProcessTemplateJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Mail/Test.php | other | Automated test coverage and expected behavior checks. |
| app/Models/AiPromptTask.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Album.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Config.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Group.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Image.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/ImageBatchOperation.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/ImageProcessJob.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/ImageProcessTemplate.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Model.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/PersonalAccessToken.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Strategy.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/Tag.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/TeamMembership.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/TeamSpace.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/UploadTask.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/User.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Models/WebhookSubscription.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Providers/AppServiceProvider.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Providers/AuthServiceProvider.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Providers/BroadcastServiceProvider.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Providers/EventServiceProvider.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Providers/RouteServiceProvider.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/AiPromptService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/AiProviderConfigService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/HookManager.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageBatchOperationService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImagePlaceholderService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageProcessing/Contracts/ImageProcessorDriver.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageProcessing/Drivers/ImagickImageProcessorDriver.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageProcessing/Drivers/LibvipsImageProcessorDriver.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageProcessing/ImageProcessExecutor.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageProcessing/ImageProcessingManager.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/ImageService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/InstallStateService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/SignedUrlService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/TeamSpaceService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/UpgradeService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/UploadTaskService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/UserService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Services/WebhookEventService.php | other | Project asset or source file; refine with concrete responsibility. |
| app/Utils.php | other | Project asset or source file; refine with concrete responsibility. |
| app/View/Components/AppLayout.php | other | Project asset or source file; refine with concrete responsibility. |
| app/View/Components/GuestLayout.php | other | Project asset or source file; refine with concrete responsibility. |
| artisan | other | Project asset or source file; refine with concrete responsibility. |
| bootstrap/app.php | other | Project asset or source file; refine with concrete responsibility. |
| bootstrap/cache/packages.php | other | Project asset or source file; refine with concrete responsibility. |
| bootstrap/cache/services.php | other | Project asset or source file; refine with concrete responsibility. |
| composer-setup.php | other | Multi-service local runtime orchestration configuration. |
| composer.json | other | Multi-service local runtime orchestration configuration. |
| composer.lock | other | Multi-service local runtime orchestration configuration. |
| config/app.php | other | Project asset or source file; refine with concrete responsibility. |
| config/auth.php | other | Project asset or source file; refine with concrete responsibility. |
| config/broadcasting.php | other | Project asset or source file; refine with concrete responsibility. |
| config/cache.php | other | Project asset or source file; refine with concrete responsibility. |
| config/convention.php | other | Project asset or source file; refine with concrete responsibility. |
| config/cors.php | other | Project asset or source file; refine with concrete responsibility. |
| config/database.php | other | Project asset or source file; refine with concrete responsibility. |
| config/debugbar.php | other | Project asset or source file; refine with concrete responsibility. |
| config/download.php | other | Project asset or source file; refine with concrete responsibility. |
| config/filesystems.php | other | Project asset or source file; refine with concrete responsibility. |
| config/flare.php | other | Project asset or source file; refine with concrete responsibility. |
| config/hashing.php | other | Project asset or source file; refine with concrete responsibility. |
| config/ignition.php | other | Project asset or source file; refine with concrete responsibility. |
| config/image_processing.php | other | Project asset or source file; refine with concrete responsibility. |
| config/image.php | other | Project asset or source file; refine with concrete responsibility. |
| config/lifecycle.php | other | Project asset or source file; refine with concrete responsibility. |
| config/logging.php | other | Project asset or source file; refine with concrete responsibility. |
| config/mail.php | other | Project asset or source file; refine with concrete responsibility. |
| config/octane.php | other | Project asset or source file; refine with concrete responsibility. |
| config/plugins.php | other | Project asset or source file; refine with concrete responsibility. |
| config/queue.php | other | Project asset or source file; refine with concrete responsibility. |
| config/sanctum.php | other | Project asset or source file; refine with concrete responsibility. |
| config/services.php | other | Project asset or source file; refine with concrete responsibility. |
| config/session.php | other | Project asset or source file; refine with concrete responsibility. |
| config/view.php | other | Project asset or source file; refine with concrete responsibility. |
| database/factories/UserFactory.php | other | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2014_10_10_000000_create_groups_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2014_10_12_000000_create_users_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2014_10_12_100000_create_password_resets_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2019_08_19_000000_create_failed_jobs_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2021_12_11_184521_create_strategies_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2021_12_11_185759_create_albums_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2021_12_11_191158_create_images_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2021_12_11_200033_create_configs_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2022_01_20_201231_create_group_strategy_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_04_162500_add_hot_indexes_to_images_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_120000_add_social_columns_to_users_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_120000_extend_group_accepted_suffixes_for_docs.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_121000_extend_group_accepted_suffixes_for_archives.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_123000_extend_group_accepted_suffixes_for_office_more.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_124000_expand_images_mimetype_length.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_140000_add_restrictions_to_personal_access_tokens_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_150000_add_lifecycle_columns_to_images_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_151000_add_upload_pipeline_async_enabled_config.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_152000_create_upload_tasks_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_153000_create_image_batch_operations_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_160000_add_observability_cost_configs.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_170000_create_tags_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_170000_create_webhook_subscriptions_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_170100_create_image_tag_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_170200_add_ocr_text_to_images_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_180000_create_team_spaces_and_memberships_tables.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_190000_add_review_columns_to_images_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_190000_create_image_process_templates_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_201000_add_permissions_to_team_memberships_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_210000_create_image_process_jobs_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_05_235000_create_ai_prompt_tasks_table.php | data | Project asset or source file; refine with concrete responsibility. |
| database/migrations/2026_03_08_120000_add_ai_provider_configs.php | data | Project asset or source file; refine with concrete responsibility. |
| database/seeders/DatabaseSeeder.php | other | Project asset or source file; refine with concrete responsibility. |
| database/seeders/InstallSeeder.php | other | Project asset or source file; refine with concrete responsibility. |
| database/smoke-acceptance.sqlite | other | Project asset or source file; refine with concrete responsibility. |
| database/smoke-ci.sqlite | other | Project asset or source file; refine with concrete responsibility. |
| database/smoke-idempotency.sqlite | other | Project asset or source file; refine with concrete responsibility. |
| database/testing-idempotency.sqlite | other | Automated test coverage and expected behavior checks. |
| deploy/1panel/docker-compose.php83.yml | other | Multi-service local runtime orchestration configuration. |
| deploy/1panel/README.md | other | Project overview and developer onboarding documentation. |
| deploy/e2e/docker-compose.bootstrap.yml | other | Multi-service local runtime orchestration configuration. |
| deploy/e2e/docker-compose.install.yml | other | Multi-service local runtime orchestration configuration. |
| deploy/php83/apache-vhost.conf | other | Project asset or source file; refine with concrete responsibility. |
| deploy/php83/Dockerfile | other | Container build definition for runtime or development environments. |
| deploy/php83/entrypoint.sh | other | Project asset or source file; refine with concrete responsibility. |
| deploy/php83/php.ini | other | Project asset or source file; refine with concrete responsibility. |
| docs/advanced/catalog/ADVANCED_FEATURE_CATALOG.md | docs | Documentation content and project guidance. |
| docs/advanced/catalog/COMBINED_ADVANCED_SCENARIOS.md | docs | Documentation content and project guidance. |
| docs/advanced/design/ai-config.md | docs | Documentation content and project guidance. |
| docs/advanced/design/ai-prompt.md | docs | Documentation content and project guidance. |
| docs/advanced/design/ai-search.md | docs | Documentation content and project guidance. |
| docs/advanced/design/drivers.md | docs | Documentation content and project guidance. |
| docs/advanced/design/image-process.md | docs | Documentation content and project guidance. |
| docs/advanced/design/INDEX.md | docs | Documentation content and project guidance. |
| docs/advanced/design/jobs.md | docs | Documentation content and project guidance. |
| docs/advanced/design/reviews.md | docs | Documentation content and project guidance. |
| docs/advanced/design/team-permissions.md | docs | Documentation content and project guidance. |
| docs/advanced/design/templates.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/ai-config.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/ai-prompt.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/ai-search.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/drivers.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/image-process.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/INDEX.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/jobs.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/reviews.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/team-permissions.md | docs | Documentation content and project guidance. |
| docs/advanced/requirements/templates.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/ai-config-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/ai-prompt-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/ai-search-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/drivers-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/image-process-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/INDEX.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/jobs-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/reviews-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/team-permissions-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced/tasks/templates-tasks.md | docs | Documentation content and project guidance. |
| docs/advanced-features.md | docs | Documentation content and project guidance. |
| docs/context-compact.md | docs | Documentation content and project guidance. |
| docs/design/ADVANCED_DESIGN.md | docs | Documentation content and project guidance. |
| docs/design/DESIGN.md | docs | Documentation content and project guidance. |
| docs/design.md | docs | Documentation content and project guidance. |
| docs/documentation.md | docs | Documentation content and project guidance. |
| docs/execplan-template.md | docs | Documentation content and project guidance. |
| docs/implement.md | docs | Documentation content and project guidance. |
| docs/iteration-log.md | docs | Documentation content and project guidance. |
| docs/plans.md | docs | Documentation content and project guidance. |
| docs/project-overview.md | docs | Documentation content and project guidance. |
| docs/prompt.md | docs | Documentation content and project guidance. |
| docs/README.md | docs | Project overview and developer onboarding documentation. |
| docs/repo-structure-map.md | docs | Documentation content and project guidance. |
| docs/requirements/ADVANCED_REQUIREMENTS.md | docs | Documentation content and project guidance. |
| docs/requirements/REQUIREMENTS.md | docs | Documentation content and project guidance. |
| docs/requirements.md | docs | Documentation content and project guidance. |
| docs/runbook/CODEX_NONINTERACTIVE_WORKFLOW.md | docs | Documentation content and project guidance. |
| docs/runbook/INSTALL_E2E.md | docs | Documentation content and project guidance. |
| docs/runbook/LOCAL_RUNTIME_AND_PREVIEW_DEPS.md | docs | Documentation content and project guidance. |
| docs/runbook/LONG_RUNNING_AUTOPILOT.md | docs | Documentation content and project guidance. |
| docs/runbook/LONG_RUNNING_PLAN.md | docs | Documentation content and project guidance. |
| docs/runbook/OPERATIONS_RUNBOOK.md | docs | Documentation content and project guidance. |
| docs/runbook/PHP83_1PANEL_CUSTOM_RUNTIME.md | docs | Documentation content and project guidance. |
| docs/runbook/PROD_MIGRATION_WINDOW_AND_ROLLBACK.md | docs | Documentation content and project guidance. |
| docs/runbook/QUEUE_WORKER_RUNTIME_EXAMPLES.md | docs | Documentation content and project guidance. |
| docs/runbook/REDIS_QUEUE_IMAGE_DELETE.md | docs | Documentation content and project guidance. |
| docs/runbook/SECURITY_BASELINE.md | docs | Documentation content and project guidance. |
| docs/runbook/STATUS.md | docs | Documentation content and project guidance. |
| docs/SECURITY_FIX_PLAN.md | docs | Documentation content and project guidance. |
| docs/self-research.md | docs | Documentation content and project guidance. |
| docs/tasks/ADVANCED_TASKS.md | docs | Documentation content and project guidance. |
| docs/tasks/TASKS.md | docs | Documentation content and project guidance. |
| docs/tasks.md | docs | Documentation content and project guidance. |
| docs/UPGRADE_PLAN_V3.md | docs | Documentation content and project guidance. |
| installed.lock | other | Project asset or source file; refine with concrete responsibility. |
| lang/en/auth.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/en/pagination.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/en/passwords.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/en/validation.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/en.json | other | Machine-readable configuration or metadata. |
| lang/zh_CN/auth.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/pagination.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/passwords.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/validation-attributes.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/validation-inline.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/validation.php | other | Project asset or source file; refine with concrete responsibility. |
| lang/zh_CN/zh_CN.json | other | Machine-readable configuration or metadata. |
| lang/zh_CN.json | other | Machine-readable configuration or metadata. |
| LICENSE | other | Project asset or source file; refine with concrete responsibility. |
| package-lock.json | other | Machine-readable configuration or metadata. |
| package.json | other | Project package manifest, scripts, and dependency definitions. |
| phpunit.xml | other | Project asset or source file; refine with concrete responsibility. |
| phpunit.xml.bak | other | Project asset or source file; refine with concrete responsibility. |
| PLANS.md | other | Documentation content and project guidance. |
| png | other | Project asset or source file; refine with concrete responsibility. |
| prompts/codex/advanced/generated/-.md | other | Documentation content and project guidance. |
| prompts/codex/advanced/generated/AR-001.md | other | Documentation content and project guidance. |
| prompts/codex/advanced/generated/AR-002.md | other | Documentation content and project guidance. |
| prompts/codex/advanced/SMOKE.md | other | Documentation content and project guidance. |
| prompts/codex/advanced/TEMPLATE.md | other | Documentation content and project guidance. |
| public/css/app.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/common.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/context-js/context-js.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/fontawesome.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/gallery.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/justified-gallery/justifiedGallery.min.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/markdown-css/github-markdown-light.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/markdown-css/github-markdown.css | other | Project asset or source file; refine with concrete responsibility. |
| public/css/viewer-js/viewer.min.css | other | Project asset or source file; refine with concrete responsibility. |
| public/favicon.ico | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.eot | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.svg | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.ttf | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.woff | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-brands-400.woff2 | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.eot | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.svg | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.ttf | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.woff | other | Project asset or source file; refine with concrete responsibility. |
| public/fonts/vendor/@fortawesome/fontawesome-free/webfa-solid-900.woff2 | other | Project asset or source file; refine with concrete responsibility. |
| public/index.php | other | Project asset or source file; refine with concrete responsibility. |
| public/jpg | other | Project asset or source file; refine with concrete responsibility. |
| public/js/app.js | other | Application source code or build/test tooling. |
| public/js/app.js.LICENSE.txt | other | Project asset or source file; refine with concrete responsibility. |
| public/js/blueimp-file-upload/jquery.fileupload.js | other | Application source code or build/test tooling. |
| public/js/blueimp-file-upload/jquery.iframe-transport.js | other | Application source code or build/test tooling. |
| public/js/blueimp-file-upload/jquery.ui.widget.js | other | Application source code or build/test tooling. |
| public/js/blueimp-load-image/load-image.all.min.js | other | Application source code or build/test tooling. |
| public/js/clipboard/clipboard.min.js | other | Application source code or build/test tooling. |
| public/js/clipboard/index.browser.js | other | Application source code or build/test tooling. |
| public/js/context-js/context-js.js | other | Application source code or build/test tooling. |
| public/js/dragselect/ds.min.js | other | Application source code or build/test tooling. |
| public/js/echarts/echarts.min.js | other | Application source code or build/test tooling. |
| public/js/imagesloaded/imagesloaded.pkgd.min.js | other | Application source code or build/test tooling. |
| public/js/justified-gallery/jquery.justifiedGallery.min.js | other | Application source code or build/test tooling. |
| public/js/masonry/masonry.pkgd.min.js | other | Application source code or build/test tooling. |
| public/js/viewer-js/viewer.min.js | other | Application source code or build/test tooling. |
| public/mix-manifest.json | other | Machine-readable configuration or metadata. |
| public/png | other | Project asset or source file; refine with concrete responsibility. |
| public/robots.txt | other | Project asset or source file; refine with concrete responsibility. |
| public/static/app/images/file-icons/archive.png | other | Project asset or source file; refine with concrete responsibility. |
| public/static/js/media-carousel-shared.js | other | Application source code or build/test tooling. |
| public/thumbnails/00245f0d9ba92685ddc89709c4af584d.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/01dd8a5712b913d928f594ddcee43565.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/01fd21a7aa49486577bc783d5181efd6.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/03333401bcecbe0d3ac64e22ce5d4f0d.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/034ad20e90b3028216ddb75f4ce52ce8.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0422d44bc7e37b2e3e885b0f41f0bbd4.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/051f58999d80dcfeb2c716ae3d501b06.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/054aa5b01d672b9ba36ae2bcfed5a629.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/063dc86848f0746f6a10b96718f1a860.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0685eceac6f0c33d1fbaefc383cf22ad.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/075631ee796da50f011f437b43b44aa8.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/075c0eaa6bd013c78c21caaeb09b4de2.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/084b2fd4083029df8ace35def88243ca.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/085f7bca30b0fb4cb65d9d5e5720db77.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0899af448598ddcf05b077289c884ba7.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/09496d5ca7260f6bd92a46a41d2d5e65.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/09ebb629a3c15e5795ae8638740f5348.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0abedc1d2ae8564269978812f777093a.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0ad68b5ad9844b7526b1f3cd2ca49b4f.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0adc779002043df758d0795d3b056955.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0b51e5cbbc2f99e263e92d4d17ebc02f.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0b53bcd8713033caf8a0ab12c3f3fdca.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0be0e2586f49897a43416210edaa51f1.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0bf605b50f69957f3c7cf30881f98e08.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0ca39fcd51dfe405ea02259bc14b7fc9.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0cc8514330607c6cba54ea15cd1fc946.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0d9de6dbf1932b580aff83fc7538e74d.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0db82565733d2fd59698be90468ab5fe.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0df0513d63664ba17858fed6530995ae.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0e04bfff0588786bec433cbcbcea2af8.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0f503dc67e83e79e2d9be4dcb7926012.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0f5835c37473a6573d365b17d5464eff.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/0f8353f69c9e2487f148578f84c75c62.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/111f42ef30715ce7dc5e9e15ceb2827a.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/112822976473f85887037ddd3e85948b.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/11724d20a3ab9928e0d71e2335d5cf27.png | other | Project asset or source file; refine with concrete responsibility. |
| public/thumbnails/119a1b4f571015ff340b9b981dfb8ac7.png | other | Project asset or source file; refine with concrete responsibility. |

## Follow-Up Required

- Replace inferred responsibilities with precise behavior, contracts, and ownership where needed.
- Keep this file updated when files are added, moved, or repurposed.
