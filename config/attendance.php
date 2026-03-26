<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Attendance Validation
    |--------------------------------------------------------------------------
    |
    | Chấm công hợp lệ khi thỏa ít nhất 1 điều kiện:
    | 1) IP thuộc danh sách mạng hợp lệ.
    | 2) Thiết bị nằm trong bán kính cho phép so với vị trí gốc.
    |
    */
    'allowed_networks' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ATTENDANCE_ALLOWED_NETWORKS', '127.0.0.1/32,::1/128'))
    ))),

    'origin_latitude' => env('ATTENDANCE_ORIGIN_LATITUDE'),
    'origin_longitude' => env('ATTENDANCE_ORIGIN_LONGITUDE'),

    'max_distance_meters' => (float) env('ATTENDANCE_MAX_DISTANCE_METERS', 50),
];
