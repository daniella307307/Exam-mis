<?php
/**
 * Bible API Endpoint
 * Provides JSON responses for AJAX requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'config.php';
require_once 'functions.php';

$db = getDBConnection();

// Get action from request
$action = $_GET['action'] ?? '';

$response = ['success' => false, 'data' => null, 'message' => ''];

try {
    switch ($action) {
        case 'books':
            // Get all books
            $response['success'] = true;
            $response['data'] = getAllBooks($db);
            break;
            
        case 'book':
            // Get specific book
            $bookId = $_GET['id'] ?? null;
            if ($bookId) {
                $book = getBookById($db, $bookId);
                if ($book) {
                    $response['success'] = true;
                    $response['data'] = $book;
                } else {
                    $response['message'] = 'Book not found';
                }
            } else {
                $response['message'] = 'Book ID required';
            }
            break;
            
        case 'verses':
            // Get verses for a chapter
            $bookId = $_GET['book'] ?? null;
            $chapter = $_GET['chapter'] ?? null;
            
            if ($bookId && $chapter) {
                $verses = getVerses($db, $bookId, $chapter);
                $response['success'] = true;
                $response['data'] = $verses;
            } else {
                $response['message'] = 'Book ID and chapter required';
            }
            break;
            
        case 'verse':
            // Get a specific verse
            $bookId = $_GET['book'] ?? null;
            $chapter = $_GET['chapter'] ?? null;
            $verse = $_GET['verse'] ?? null;
            
            if ($bookId && $chapter && $verse) {
                $verseData = getVerse($db, $bookId, $chapter, $verse);
                if ($verseData) {
                    $response['success'] = true;
                    $response['data'] = $verseData;
                } else {
                    $response['message'] = 'Verse not found';
                }
            } else {
                $response['message'] = 'Book ID, chapter, and verse required';
            }
            break;
            
        case 'search':
            // Search Bible
            $query = $_GET['q'] ?? '';
            
            if (!empty($query)) {
                $results = searchBible($db, $query);
                $response['success'] = true;
                $response['data'] = $results;
                $response['message'] = count($results) . ' results found';
            } else {
                $response['message'] = 'Search query required';
            }
            break;
            
        case 'random':
            // Get random verse
            $randomVerse = getRandomVerse($db);
            if ($randomVerse) {
                $response['success'] = true;
                $response['data'] = $randomVerse;
            } else {
                $response['message'] = 'No verses available';
            }
            break;
            
        case 'bookmarks':
            // Get all bookmarks
            $bookmarks = getBookmarks($db);
            $response['success'] = true;
            $response['data'] = $bookmarks;
            break;
            
        case 'add_bookmark':
            // Add bookmark
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $bookId = $input['book_id'] ?? null;
                $chapter = $input['chapter'] ?? null;
                $verse = $input['verse'] ?? null;
                $note = $input['note'] ?? '';
                
                if ($bookId && $chapter && $verse) {
                    if (addBookmark($db, $bookId, $chapter, $verse, $note)) {
                        $response['success'] = true;
                        $response['message'] = 'Bookmark added successfully';
                    } else {
                        $response['message'] = 'Failed to add bookmark';
                    }
                } else {
                    $response['message'] = 'Book ID, chapter, and verse required';
                }
            } else {
                $response['message'] = 'POST method required';
            }
            break;
            
        case 'delete_bookmark':
            // Delete bookmark
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = json_decode(file_get_contents('php://input'), true);
                $bookmarkId = $input['id'] ?? null;
                
                if ($bookmarkId) {
                    if (deleteBookmark($db, $bookmarkId)) {
                        $response['success'] = true;
                        $response['message'] = 'Bookmark deleted successfully';
                    } else {
                        $response['message'] = 'Failed to delete bookmark';
                    }
                } else {
                    $response['message'] = 'Bookmark ID required';
                }
            } else {
                $response['message'] = 'POST method required';
            }
            break;
            
        default:
            $response['message'] = 'Invalid action';
            break;
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

$db->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>