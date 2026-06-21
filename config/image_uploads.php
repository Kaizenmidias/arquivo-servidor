<?php

return [
    'max_files_per_property' => (int) env('PROPERTY_IMAGES_MAX_FILES', 200),
    'max_file_size_bytes' => (int) env('PROPERTY_IMAGES_MAX_FILE_SIZE', 50 * 1024 * 1024),
    'request_max_body_hint' => (int) env('PROPERTY_IMAGES_REQUEST_MAX_BODY', 60 * 1024 * 1024),
    'parallel_uploads' => (int) env('PROPERTY_IMAGES_PARALLEL_UPLOADS', 8),
    'poll_interval_ms' => (int) env('PROPERTY_IMAGES_POLL_INTERVAL_MS', 4000),
    'processing' => [
        'hero_max_width' => 1920,
        'gallery_max_width' => 1600,
        'thumb_max_width' => 400,
        'webp_quality' => (int) env('PROPERTY_IMAGES_WEBP_QUALITY', 85),
    ],
    'staging_disk' => env('PROPERTY_IMAGES_STAGING_DISK', env('PROPERTY_IMAGES_FINAL_DISK', 'public')),
    'staging_directory' => trim((string) env('PROPERTY_IMAGES_STAGING_DIRECTORY', 'property-uploads/originals'), '/'),
    'final_disk' => env('PROPERTY_IMAGES_FINAL_DISK', 'public'),
    'final_directory' => 'properties',
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'],
    'allowed_mime_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/heic',
        'image/heif',
        'image/heif-sequence',
        'image/heic-sequence',
        'application/octet-stream',
    ],
];
