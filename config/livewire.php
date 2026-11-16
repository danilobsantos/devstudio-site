<?php

return [
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => 'file|max:5120',
        'directory' => 'livewire-tmp',
        'middleware' => 'throttle:60,1',
        'preview_mimes' => [],
        'max_upload_time' => 5,
        'cleanup' => true,
    ],
];
