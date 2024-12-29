<?php

return [
    'site_name' => env('SITE_NAME', 'My Website'),
    'site_logo' => env('SITE_LOGO', 'logos/default_logo.webp'),
    'site_favicon' => env('SITE_FAVICON', 'assets/img/favicon/favicon.ico'),
    'site_description' => env('SITE_DESCRIPTION', 'This is the default site description.'),
    'admin_email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'site_language' => env('SITE_LANGUAGE', 'en'),
    'timezone' => env('TIMEZONE', 'UTC'),
    'two_factor_auth' => env('TWO_FACTOR_AUTH', false),
    'auto_lock_time' => env('AUTO_LOCK_TIME', 15),
    'mail_mailer' => env('MAIL_MAILER', 'smtp'),
    'mail_host' => env('MAIL_HOST', 'smtp.example.com'),
    'mail_port' => env('MAIL_PORT', 587),
    'mail_username' => env('MAIL_USERNAME', 'user@example.com'),
    'mail_password' => env('MAIL_PASSWORD', ''),
    'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
    'mail_from_name' => env('MAIL_FROM_NAME', 'Example'),

    'notification_email' => env('NOTIFICATION_EMAIL', true),
    'notification_sms' => env('NOTIFICATION_SMS', false),
    'notification_push' => env('NOTIFICATION_PUSH', false),

    // SEO settings
    'meta_title' => env('META_TITLE', 'Default Meta Title'),
    'meta_description' => env('META_DESCRIPTION', 'Default Meta Description'),
    'meta_keywords' => env('META_KEYWORDS', 'default, keywords'),
    'robots_txt' => env('ROBOTS_TXT', 'User-agent: *\nDisallow: /'),
    'sitemap_url' => env('SITEMAP_URL', 'sitemap.xml'),
    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
    'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID', ''),
    'canonical_url'=> env('CANONICAL_URL', ''),

    // Social Media settings
    'facebook' => env('FACEBOOK_URL', ''),
    'twitter' => env('TWITTER_URL', ''),
    'linkedin' => env('LINKEDIN_URL', ''),
    'whatsapp' => env('WHATSAPP_NUMBER', ''),
    'tiktok' => env('TIKTOK_URL', ''),

    // ADS settings
    'google_ads_desktop_classes' => env('GOOGLE_ADS_DESKTOP_CLASSES', ''),
    'google_ads_desktop_classes_2' => env('GOOGLE_ADS_DESKTOP_CLASSES_2', ''),
    'google_ads_desktop_subject' => env('GOOGLE_ADS_DESKTOP_SUBJECT', ''),
    'google_ads_desktop_subject_2' => env('GOOGLE_ADS_DESKTOP_SUBJECT_2', ''),
    'google_ads_desktop_article' => env('GOOGLE_ADS_DESKTOP_ARTICLE', ''),
    'google_ads_desktop_article_2' => env('GOOGLE_ADS_DESKTOP_ARTICLE_2', ''),
    'google_ads_desktop_news' => env('GOOGLE_ADS_DESKTOP_NEWS', ''),
    'google_ads_desktop_news_2' => env('GOOGLE_ADS_DESKTOP_NEWS_2', ''),
    'google_ads_desktop_home' => env('GOOGLE_ADS_DESKTOP_HOME', ''),
    'google_ads_desktop_home_2' => env('GOOGLE_ADS_DESKTOP_HOME_2', ''),
    'google_ads_desktop_download' => env('GOOGLE_ADS_DESKTOP_DOWNLOAD', ''),
    'google_ads_desktop_download_2' => env('GOOGLE_ADS_DESKTOP_DOWNLOAD_2', ''),
    'google_ads_mobile_classes' => env('GOOGLE_ADS_MOBILE_CLASSES', ''),
    'google_ads_mobile_classes_2' => env('GOOGLE_ADS_MOBILE_CLASSES_2', ''),
    'google_ads_mobile_subject' => env('GOOGLE_ADS_MOBILE_SUBJECT', ''),
    'google_ads_mobile_subject_2' => env('GOOGLE_ADS_MOBILE_SUBJECT_2', ''),
    'google_ads_mobile_article' => env('GOOGLE_ADS_MOBILE_ARTICLE', ''),
    'google_ads_mobile_article_2' => env('GOOGLE_ADS_MOBILE_ARTICLE_2', ''),
    'google_ads_mobile_news' => env('GOOGLE_ADS_MOBILE_NEWS', ''),
    'google_ads_mobile_news_2' => env('GOOGLE_ADS_MOBILE_NEWS_2', ''),
    'google_ads_mobile_download' => env('GOOGLE_ADS_MOBILE_DOWNLOAD', ''),
    'google_ads_mobile_download_2' => env('GOOGLE_ADS_MOBILE_DOWNLOAD_2', ''),
    'google_ads_mobile_home' => env('GOOGLE_ADS_MOBILE_HOME', ''),
    'google_ads_mobile_home_2' => env('GOOGLE_ADS_MOBILE_HOME_2', ''),

];
