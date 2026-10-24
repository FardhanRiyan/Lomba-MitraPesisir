<?php

return [
    'alert' => [
        'wind_speed_kmh' => (int) env('WEATHER_ALERT_WIND_SPEED_KMH', 30),
        'wave_height_m' => (float) env('WEATHER_ALERT_WAVE_HEIGHT_M', 2.0),
        'dedupe_hours' => (int) env('WEATHER_ALERT_DEDUPE_HOURS', 3),
    ],
];
