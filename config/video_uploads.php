<?php

return [
    'max_file_size_kb' => (int) env('PROPERTY_VIDEOS_MAX_FILE_SIZE_KB', 204800),
    'max_per_property' => (int) env('PROPERTY_VIDEOS_MAX_PER_PROPERTY', 5),
    'max_width' => (int) env('PROPERTY_VIDEOS_MAX_WIDTH', 1280),
    'crf' => (int) env('PROPERTY_VIDEOS_VP9_CRF', 36),
    'ffmpeg' => env('FFMPEG_BINARY', 'ffmpeg'),
    'ffprobe' => env('FFPROBE_BINARY', 'ffprobe'),
];
