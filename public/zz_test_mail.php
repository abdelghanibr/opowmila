<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

header('Content-Type: text/plain; charset=utf-8');

$to = $_GET['to'] ?? 'abdelghanibourihane@gmail.com';

try {
    Mail::raw(
        "Test d'envoi d'email depuis l'environnement DEV (opowmila43).\n\nDate: " . date('Y-m-d H:i:s'),
        function ($message) use ($to) {
            $message->to($to)
                    ->subject('Test alerte DEV - ' . date('Y-m-d H:i:s'));
        }
    );
    echo "EMAIL_SENT to $to at " . date('Y-m-d H:i:s') . "\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
