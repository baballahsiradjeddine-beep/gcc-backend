<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Badge;
use App\Models\LeaderBoard;

echo "Total Users: " . User::count() . "\n";
echo "Total LeaderBoard entries: " . LeaderBoard::count() . "\n";
$lbs = LeaderBoard::all();
foreach ($lbs as $lb) {
    echo "LB User ID: " . $lb->user_id . " | Points: " . $lb->points . "\n";
}
