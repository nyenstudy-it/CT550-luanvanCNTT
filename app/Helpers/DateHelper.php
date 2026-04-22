<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Dịch khoảng thời gian sang tiếng Việt
     */
    public static function diffForHumansVi(Carbon $date)
    {
        $now = Carbon::now();
        $diff = $date->diffInSeconds($now);

        // Dưới 1 phút
        if ($diff < 60) {
            return 'vừa mới';
        }

        // Dưới 1 giờ
        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' phút trước';
        }

        // Dưới 1 ngày
        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' giờ trước';
        }

        // Dưới 7 ngày
        if ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' ngày trước';
        }

        // Dưới 30 ngày
        if ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return $weeks . ' tuần trước';
        }

        // Dưới 1 năm
        if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $months . ' tháng trước';
        }

        // Trên 1 năm
        $years = floor($diff / 31536000);
        return $years . ' năm trước';
    }
}
