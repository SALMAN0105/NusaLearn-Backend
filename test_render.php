<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$start = microtime(true);
view('administrator.languages', ['languages' => collect()])->render();
echo 'Render time: ' . (microtime(true) - $start) . ' seconds' . PHP_EOL;
