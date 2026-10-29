<?php
// Temporary endpoint. It mimics the JSON output of Peldan's
// script/get_topic_contents.php without using MySQL or RabbitMQ.
header('Content-Type: application/json; charset=utf-8');

$animeid = filter_input(INPUT_GET, 'animeid', FILTER_VALIDATE_INT);
$threadid = filter_input(INPUT_GET, 'threadid', FILTER_VALIDATE_INT);

if ($animeid !== 16498 || !in_array($threadid, [1, 2], true)) {
    echo json_encode([]);
    exit;
}

$threads = [
    1 => [
        'title' => 'Was the ending justified?',
        'author' => 'AnimeFan',
        'created' => '2026-10-22 12:00:00',
        'text' => 'What did everyone think of the ending?',
        'replies' => [
            ['author' => 'User123', 'created' => '2026-10-22 12:15:00', 'content' => 'I liked how the story concluded.'],
            ['author' => 'Levi123', 'created' => '2026-10-22 12:30:00', 'content' => 'I would have preferred a different ending.']
        ]
    ],
    2 => [
        'title' => 'Who had the best character development?',
        'author' => 'Levi123',
        'created' => '2026-10-22 12:30:00',
        'text' => 'Which character changed the most over the series?',
        'replies' => [
            ['author' => 'AnimeFan', 'created' => '2026-10-22 13:00:00', 'content' => 'My vote goes to Reiner.']
        ]
    ]
];

echo json_encode([$threads[$threadid]]);
