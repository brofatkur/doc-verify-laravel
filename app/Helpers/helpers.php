<?php

if (!function_exists('setting')) {
    function setting($key = null, $default = null) {
        if ($key === null) {
            return \App\Models\Setting::getAll();
        }
        return \App\Models\Setting::get($key, $default);
    }
}
