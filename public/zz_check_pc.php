<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

header('Content-Type: text/plain; charset=utf-8');

echo "=== POOL CLOSURE 16 ===\n";
$pc = DB::table('pool_closures')->where('id', 16)->first();
if (!$pc) {
    echo "PoolClosure 16 ABSENT\n";
} else {
    echo "complex_activity_id=" . $pc->complex_activity_id . "\n";
    echo "start_date=" . $pc->start_date . "\n";
    echo "end_date=" . $pc->end_date . "\n";
}

echo "\n=== USER par email ===\n";
$u = DB::table('users')->where('email', 'abdelghanibourihane@gmail.com')->first();
if ($u) {
    echo "USER FOUND id=" . $u->id . " name=" . $u->name . " type=" . (property_exists($u,'user_type')?$u->user_type:'?') . "\n";
} else {
    echo "USER ABSENT\n";
}

echo "\n=== SCHEDULES pour complex_activity " . ($pc->complex_activity_id ?? '?') . " ===\n";
if ($pc) {
    $scheds = DB::table('schedules')->where('complex_activity_id', $pc->complex_activity_id)->limit(5)->get();
    foreach ($scheds as $s) {
        echo "schedule_id={$s->id} | type=" . (property_exists($s,'type')?$s->type:'?') . " | price=" . (property_exists($s,'price')?$s->price:'?') . " | groupe=" . (property_exists($s,'groupe')?$s->groupe:'?') . "\n";
    }
}

echo "\n=== SEASONS ===\n";
$seasons = DB::table('seasons')->select('id','name','start_date','end_date')->orderByDesc('id')->limit(5)->get();
foreach ($seasons as $s) {
    echo "season_id={$s->id} | name={$s->name} | {$s->start_date} -> {$s->end_date}\n";
}

echo "\n=== RESERVATION columns ===\n";
echo implode(", ", array_values(DB::getSchemaBuilder()->getColumnListing('reservations'))) . "\n";
