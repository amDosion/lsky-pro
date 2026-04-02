<?php

namespace App\Enums;

final class ConfigKey
{
    /** @var string 是否启用注册 */
    const IsEnableRegistration = 'is_enable_registration';

    /** @var string 是否启用画廊 */
    const IsEnableGallery = 'is_enable_gallery';

    /** @var string 是否启用接口 */
    const IsEnableApi = 'is_enable_api';

    /** @var string 程序名称 */
    const AppName = 'app_name';

    /** @var string 程序版本 */
    const AppVersion = 'app_version';

    /** @var string 站点关键字 */
    const SiteKeywords = 'site_keywords';

    /** @var string 站点描述 */
    const SiteDescription = 'site_description';

    /** @var string 站点公告 */
    const SiteNotice = 'site_notice';

    /** @var string icp备案号 */
    const IcpNo = 'icp_no';

    /** @var string 是否允许游客上传 */
    const IsAllowGuestUpload = 'is_allow_guest_upload';

    /** @var string 用户初始容量(kb) */
    const UserInitialCapacity = 'user_initial_capacity';

    /** @var string 账户是否需要验证 */
    const IsUserNeedVerify = 'is_user_need_verify';

    /** @var string 是否启用异步上传流水线 */
    const UploadPipelineAsyncEnabled = 'upload_pipeline_async_enabled';

    /** @var string 存储成本单价（每 GB / 月） */
    const StorageCostPerGbMonth = 'storage_cost_per_gb_month';

    /** @var string 存储成本币种（ISO 货币代码） */
    const StorageCostCurrency = 'storage_cost_currency';

    /** @var string 当前启用的 AI 提供商 */
    const AiProvider = 'ai_provider';

    /** @var string AI 提供商配置 */
    const AiProviderSettings = 'ai_provider_settings';

    /** @var string 图片识别引擎 */
    const ImageIntelligenceEngine = 'image_intelligence_engine';

    /** @var string 图片识别提供商 */
    const ImageIntelligenceProvider = 'image_intelligence_provider';

    /** @var string 图片识别模型 */
    const ImageIntelligenceModel = 'image_intelligence_model';

    /** @var string 图片识别是否生成标签 */
    const ImageIntelligenceEnableLabels = 'image_intelligence_enable_labels';

    /** @var string 图片识别是否生成摘要 */
    const ImageIntelligenceEnableSummary = 'image_intelligence_enable_summary';

    /** @var string 图片识别是否提取 OCR 文本 */
    const ImageIntelligenceEnableOcrText = 'image_intelligence_enable_ocr_text';

    /** @var string 上传后自动识别 */
    const ImageIntelligenceAutoOnUpload = 'image_intelligence_auto_on_upload';

    /** @var string 定时识别是否开启 */
    const ImageIntelligenceScheduleEnabled = 'image_intelligence_schedule_enabled';

    /** @var string 定时识别 cron 表达式 */
    const ImageIntelligenceScheduleCron = 'image_intelligence_schedule_cron';

    /** @var string 失败任务是否允许重试 */
    const ImageIntelligenceRetryFailed = 'image_intelligence_retry_failed';

    /** @var string 邮件配置 */
    const Mail = 'mail';

    /** @var string 角色组默认配置 */
    const Group = 'group';
}
