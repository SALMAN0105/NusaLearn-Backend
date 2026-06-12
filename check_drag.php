<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DRAG & DROP QUIZZES ===\n";
$quizzes = App\Models\Soal::where('tipe_template', 'drag_and_drop')->get();
foreach ($quizzes as $s) {
    $d = $s->data_soal;
    echo "ID: {$s->id}\n";
    echo "Teks: " . ($d['teks_soal'] ?? $s->teks_soal) . "\n";
    echo "Items:\n";
    if (isset($d['items'])) {
        foreach ($d['items'] as $item) {
            echo "  - ID: {$item['id']} | Text: " . ($item['text'] ?? 'NULL') . " | Image: " . ($item['image_asset'] ?? 'NULL') . "\n";
        }
    } else {
        echo "  (No items array)\n";
    }
    echo "\n";
}
