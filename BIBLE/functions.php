<?php
/**
 * Get all books from the database
 */
function getAllBooks($db) {
    $sql = "SELECT * FROM books ORDER BY book_number ASC";
    $result = $db->query($sql);
    
    $books = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
    return $books;
}

/**
 * Get a specific book by ID
 */
function getBookById($db, $bookId) {
    $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get verses for a specific book and chapter
 */
function getVerses($db, $bookId, $chapter) {
    $stmt = $db->prepare("SELECT * FROM verses WHERE book_id = ? AND chapter = ? ORDER BY verse ASC");
    $stmt->bind_param("ii", $bookId, $chapter);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $verses = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $verses[] = $row;
        }
    }
    return $verses;
}

/**
 * Search Bible verses
 */
function searchBible($db, $query) {
    $searchTerm = "%" . $db->real_escape_string($query) . "%";
    
    $sql = "SELECT v.*, b.book_name 
            FROM verses v 
            JOIN books b ON v.book_id = b.id 
            WHERE v.text LIKE ? 
            ORDER BY b.book_number, v.chapter, v.verse 
            LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    return $results;
}

/**
 * Advanced fulltext search (if MySQL supports it)
 */
function searchBibleFulltext($db, $query) {
    $searchTerm = $db->real_escape_string($query);
    
    $sql = "SELECT v.*, b.book_name, 
            MATCH(v.text) AGAINST(? IN NATURAL LANGUAGE MODE) AS relevance
            FROM verses v 
            JOIN books b ON v.book_id = b.id 
            WHERE MATCH(v.text) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC, b.book_number, v.chapter, v.verse 
            LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    return $results;
}

/**
 * Get a specific verse
 */
function getVerse($db, $bookId, $chapter, $verse) {
    $stmt = $db->prepare("SELECT v.*, b.book_name FROM verses v 
                          JOIN books b ON v.book_id = b.id 
                          WHERE v.book_id = ? AND v.chapter = ? AND v.verse = ?");
    $stmt->bind_param("iii", $bookId, $chapter, $verse);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Highlight search terms in text
 */
function highlightSearchTerm($text, $search) {
    if (empty($search)) {
        return $text;
    }
    
    $pattern = '/(' . preg_quote($search, '/') . ')/i';
    return preg_replace($pattern, '<mark>$1</mark>', $text);
}

/**
 * Get random verse (Verse of the Day feature)
 */
function getRandomVerse($db) {
    $sql = "SELECT v.*, b.book_name 
            FROM verses v 
            JOIN books b ON v.book_id = b.id 
            ORDER BY RAND() 
            LIMIT 1";
    
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

/**
 * Get bookmarks (if user system implemented)
 */
function getBookmarks($db) {
    $sql = "SELECT bm.*, b.book_name, v.text 
            FROM bookmarks bm
            JOIN books b ON bm.book_id = b.id
            LEFT JOIN verses v ON bm.book_id = v.book_id 
                AND bm.chapter = v.chapter 
                AND bm.verse = v.verse
            ORDER BY bm.created_at DESC";
    
    $result = $db->query($sql);
    
    $bookmarks = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookmarks[] = $row;
        }
    }
    return $bookmarks;
}

/**
 * Add bookmark
 */
function addBookmark($db, $bookId, $chapter, $verse, $note = '') {
    $stmt = $db->prepare("INSERT INTO bookmarks (book_id, chapter, verse, note) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $bookId, $chapter, $verse, $note);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

/**
 * Delete bookmark
 */
function deleteBookmark($db, $bookmarkId) {
    $stmt = $db->prepare("DELETE FROM bookmarks WHERE id = ?");
    $stmt->bind_param("i", $bookmarkId);
    
    if ($stmt->execute()) {
        return true;
    }
    return false;
}

/**
 * Get chapter count for a book
 */
function getChapterCount($db, $bookId) {
    $stmt = $db->prepare("SELECT chapters FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['chapters'];
    }
    return 0;
}
?>