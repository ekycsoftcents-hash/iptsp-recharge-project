<?php

return [
    'demo_mode' => (bool) env('RECHARGE_DEMO_MODE', true),
    'timeout' => (int) env('RECHARGE_TIMEOUT', 30),
];
