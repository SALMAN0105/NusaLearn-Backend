<?php
require __DIR__.'/vendor/autoload.php';
$g = new \GuzzleHttp\Client();
try {
    $r = $g->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=AQ.Ab8RN6JVC_EPR7vQRXatrJYgFhq0UbNb7VfS_NRbtv7gPaMS6Q', [
        'json' => ['contents' => [['parts' => [['text' => 'hi']]]]]
    ]);
    echo "Status: " . $r->getStatusCode() . "\n";
    echo $r->getBody()->getContents() . "\n";
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
