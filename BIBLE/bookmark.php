<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDBConnection();
    
    $bookId = $_POST['book_id'] ?? null;
    $chapter = $_POST['chapter'] ?? null;
    $verse = $_POST['verse'] ?? null;
    $note = $_POST['note'] ?? '';
    
    if ($bookId && $chapter && $verse) {
        if (addBookmark($db, $bookId, $chapter, $verse, $note)) {
            echo json_encode(['success' => true, 'message' => 'Bookmark added successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add bookmark.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    }
    
    $db->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>