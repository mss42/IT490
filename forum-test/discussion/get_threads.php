<?php

header('Content-Type: application/json');

$threads = [
    [
        "category" => 16498,
        "id" => 1,
        "title" => "Was the ending justified?",
        "author" => "AnimeFan",
        "created" => "2026-10-22 12:00:00",
        "catname" => "Attack on Titan",
        "count" => 5
    ],
    [
        "category" => 16498,
        "id" => 2,
        "title" => "Who had the best character development?",
        "author" => "Levi123",
        "created" => "2026-10-22 12:30:00",
        "catname" => "Attack on Titan",
        "count" => 3
    ]
];

echo json_encode($threads);
