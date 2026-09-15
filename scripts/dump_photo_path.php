<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$path = \App\Models\StatusLog::whereNotNull('photo_path')->latest()->value('photo_path');
echo $path ?: 'NULL';
