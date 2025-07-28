<?php
require_once __DIR__ . '/../Model/Database.php';

$data = json_decode(file_get_contents("php://input"), true);
$deck_id = $data['deck_id'] ?? '';
$record = $data['record'] ?? 0;

if (!$deck_id || !is_numeric($record)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$db = new Database();
$stmt = $db->update("UPDATE Decks SET record = ? WHERE id = ?");


if ($stmt) {
    echo json_encode(['success' => true, 'message' => 'Record updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update record']);
}