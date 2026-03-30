<?php
/**
 * KJV Bible JSON Import Script
 * For importing data from https://github.com/aruljohn/Bible-kjv
 */

require_once 'config.php';

set_time_limit(0); // This might take a while
ini_set('memory_limit', '512M');

$db = getDBConnection();

// Bible books mapping (KJV order)
$booksMapping = [
    'Genesis' => ['id' => 1, 'testament' => 'Old', 'chapters' => 50],
    'Exodus' => ['id' => 2, 'testament' => 'Old', 'chapters' => 40],
    'Leviticus' => ['id' => 3, 'testament' => 'Old', 'chapters' => 27],
    'Numbers' => ['id' => 4, 'testament' => 'Old', 'chapters' => 36],
    'Deuteronomy' => ['id' => 5, 'testament' => 'Old', 'chapters' => 34],
    'Joshua' => ['id' => 6, 'testament' => 'Old', 'chapters' => 24],
    'Judges' => ['id' => 7, 'testament' => 'Old', 'chapters' => 21],
    'Ruth' => ['id' => 8, 'testament' => 'Old', 'chapters' => 4],
    '1 Samuel' => ['id' => 9, 'testament' => 'Old', 'chapters' => 31],
    '2 Samuel' => ['id' => 10, 'testament' => 'Old', 'chapters' => 24],
    '1 Kings' => ['id' => 11, 'testament' => 'Old', 'chapters' => 22],
    '2 Kings' => ['id' => 12, 'testament' => 'Old', 'chapters' => 25],
    '1 Chronicles' => ['id' => 13, 'testament' => 'Old', 'chapters' => 29],
    '2 Chronicles' => ['id' => 14, 'testament' => 'Old', 'chapters' => 36],
    'Ezra' => ['id' => 15, 'testament' => 'Old', 'chapters' => 10],
    'Nehemiah' => ['id' => 16, 'testament' => 'Old', 'chapters' => 13],
    'Esther' => ['id' => 17, 'testament' => 'Old', 'chapters' => 10],
    'Job' => ['id' => 18, 'testament' => 'Old', 'chapters' => 42],
    'Psalms' => ['id' => 19, 'testament' => 'Old', 'chapters' => 150],
    'Proverbs' => ['id' => 20, 'testament' => 'Old', 'chapters' => 31],
    'Ecclesiastes' => ['id' => 21, 'testament' => 'Old', 'chapters' => 12],
    'Song of Solomon' => ['id' => 22, 'testament' => 'Old', 'chapters' => 8],
    'Isaiah' => ['id' => 23, 'testament' => 'Old', 'chapters' => 66],
    'Jeremiah' => ['id' => 24, 'testament' => 'Old', 'chapters' => 52],
    'Lamentations' => ['id' => 25, 'testament' => 'Old', 'chapters' => 5],
    'Ezekiel' => ['id' => 26, 'testament' => 'Old', 'chapters' => 48],
    'Daniel' => ['id' => 27, 'testament' => 'Old', 'chapters' => 12],
    'Hosea' => ['id' => 28, 'testament' => 'Old', 'chapters' => 14],
    'Joel' => ['id' => 29, 'testament' => 'Old', 'chapters' => 3],
    'Amos' => ['id' => 30, 'testament' => 'Old', 'chapters' => 9],
    'Obadiah' => ['id' => 31, 'testament' => 'Old', 'chapters' => 1],
    'Jonah' => ['id' => 32, 'testament' => 'Old', 'chapters' => 4],
    'Micah' => ['id' => 33, 'testament' => 'Old', 'chapters' => 7],
    'Nahum' => ['id' => 34, 'testament' => 'Old', 'chapters' => 3],
    'Habakkuk' => ['id' => 35, 'testament' => 'Old', 'chapters' => 3],
    'Zephaniah' => ['id' => 36, 'testament' => 'Old', 'chapters' => 3],
    'Haggai' => ['id' => 37, 'testament' => 'Old', 'chapters' => 2],
    'Zechariah' => ['id' => 38, 'testament' => 'Old', 'chapters' => 14],
    'Malachi' => ['id' => 39, 'testament' => 'Old', 'chapters' => 4],
    'Matthew' => ['id' => 40, 'testament' => 'New', 'chapters' => 28],
    'Mark' => ['id' => 41, 'testament' => 'New', 'chapters' => 16],
    'Luke' => ['id' => 42, 'testament' => 'New', 'chapters' => 24],
    'John' => ['id' => 43, 'testament' => 'New', 'chapters' => 21],
    'Acts' => ['id' => 44, 'testament' => 'New', 'chapters' => 28],
    'Romans' => ['id' => 45, 'testament' => 'New', 'chapters' => 16],
    '1 Corinthians' => ['id' => 46, 'testament' => 'New', 'chapters' => 16],
    '2 Corinthians' => ['id' => 47, 'testament' => 'New', 'chapters' => 13],
    'Galatians' => ['id' => 48, 'testament' => 'New', 'chapters' => 6],
    'Ephesians' => ['id' => 49, 'testament' => 'New', 'chapters' => 6],
    'Philippians' => ['id' => 50, 'testament' => 'New', 'chapters' => 4],
    'Colossians' => ['id' => 51, 'testament' => 'New', 'chapters' => 4],
    '1 Thessalonians' => ['id' => 52, 'testament' => 'New', 'chapters' => 5],
    '2 Thessalonians' => ['id' => 53, 'testament' => 'New', 'chapters' => 3],
    '1 Timothy' => ['id' => 54, 'testament' => 'New', 'chapters' => 6],
    '2 Timothy' => ['id' => 55, 'testament' => 'New', 'chapters' => 4],
    'Titus' => ['id' => 56, 'testament' => 'New', 'chapters' => 3],
    'Philemon' => ['id' => 57, 'testament' => 'New', 'chapters' => 1],
    'Hebrews' => ['id' => 58, 'testament' => 'New', 'chapters' => 13],
    'James' => ['id' => 59, 'testament' => 'New', 'chapters' => 5],
    '1 Peter' => ['id' => 60, 'testament' => 'New', 'chapters' => 5],
    '2 Peter' => ['id' => 61, 'testament' => 'New', 'chapters' => 3],
    '1 John' => ['id' => 62, 'testament' => 'New', 'chapters' => 5],
    '2 John' => ['id' => 63, 'testament' => 'New', 'chapters' => 1],
    '3 John' => ['id' => 64, 'testament' => 'New', 'chapters' => 1],
    'Jude' => ['id' => 65, 'testament' => 'New', 'chapters' => 1],
    'Revelation' => ['id' => 66, 'testament' => 'New', 'chapters' => 22]
];

// Step 1: Insert all books
function insertBooks($db, $booksMapping) {
    echo "Step 1: Inserting books...\n";
    echo "==========================\n";
    
    $stmt = $db->prepare("INSERT INTO books (id, book_number, book_name, testament, chapters) VALUES (?, ?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE book_name = VALUES(book_name)");
    
    $count = 0;
    foreach ($booksMapping as $bookName => $info) {
        $id = $info['id'];
        $testament = $info['testament'];
        $chapters = $info['chapters'];
        
        $stmt->bind_param("iissi", $id, $id, $bookName, $testament, $chapters);
        
        if ($stmt->execute()) {
            $count++;
            echo "✓ {$id}. {$bookName} ({$testament} Testament, {$chapters} chapters)\n";
        } else {
            echo "✗ Failed: {$bookName} - " . $stmt->error . "\n";
        }
    }
    
    $stmt->close();
    echo "\n✓ Inserted/Updated {$count} books.\n\n";
}

// Step 2: Import verses from KJV JSON
function importKJVJson($db, $filename, $booksMapping) {
    echo "Step 2: Importing verses from KJV JSON...\n";
    echo "=========================================\n";
    
    if (!file_exists($filename)) {
        die("✗ Error: File not found: {$filename}\n");
    }
    
    $jsonContent = file_get_contents($filename);
    $bibleData = json_decode($jsonContent, true);
    
    if (!$bibleData) {
        die("✗ Error: Invalid JSON format\n");
    }
    
    $stmt = $db->prepare("INSERT INTO verses (book_id, chapter, verse, text) VALUES (?, ?, ?, ?)");
    
    $totalVerses = 0;
    $errors = 0;
    
    // The KJV JSON format has books as keys
    foreach ($bibleData as $bookName => $bookData) {
        
        // Find the book ID from our mapping
        $bookId = null;
        foreach ($booksMapping as $mappedName => $info) {
            // Try exact match first
            if ($bookName === $mappedName) {
                $bookId = $info['id'];
                break;
            }
            // Try case-insensitive match
            if (strcasecmp($bookName, $mappedName) === 0) {
                $bookId = $info['id'];
                break;
            }
        }
        
        if (!$bookId) {
            echo "✗ Unknown book: {$bookName}\n";
            continue;
        }
        
        echo "Processing {$bookName} (ID: {$bookId})...\n";
        
        $bookVerseCount = 0;
        
        // Iterate through chapters
        foreach ($bookData as $chapterNum => $verses) {
            // Iterate through verses
            foreach ($verses as $verseNum => $verseText) {
                $stmt->bind_param("iiis", $bookId, $chapterNum, $verseNum, $verseText);
                
                if ($stmt->execute()) {
                    $bookVerseCount++;
                    $totalVerses++;
                } else {
                    $errors++;
                    echo "  ✗ Error at {$bookName} {$chapterNum}:{$verseNum} - " . $stmt->error . "\n";
                }
            }
        }
        
        echo "  ✓ Imported {$bookVerseCount} verses\n";
    }
    
    $stmt->close();
    
    echo "\n";
    echo "=========================================\n";
    echo "Import Summary:\n";
    echo "=========================================\n";
    echo "✓ Total verses imported: {$totalVerses}\n";
    if ($errors > 0) {
        echo "✗ Errors encountered: {$errors}\n";
    }
    echo "=========================================\n";
}

// Main execution
if (php_sapi_name() === 'cli') {
    echo "\n";
    echo "=========================================\n";
    echo "  KJV Bible Import Script\n";
    echo "  Source: github.com/aruljohn/Bible-kjv\n";
    echo "=========================================\n\n";
    
    // Check if JSON file is provided as argument
    $jsonFile = $argv[1] ?? 'Bible-kjv.json';
    
    if (!file_exists($jsonFile)) {
        echo "Usage: php import_kjv_bible.php [path/to/Bible-kjv.json]\n\n";
        echo "Please provide the path to the Bible-kjv.json file.\n";
        echo "Download it from: https://raw.githubusercontent.com/aruljohn/Bible-kjv/master/Bible-kjv.json\n\n";
        exit(1);
    }
    
    $startTime = microtime(true);
    
    // Step 1: Insert books
    insertBooks($db, $booksMapping);
    
    // Step 2: Import verses
    importKJVJson($db, $jsonFile, $booksMapping);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    echo "\n✓ Import completed in {$duration} seconds!\n";
    echo "\nYou can now access your Bible app at: http://localhost/bible/\n\n";
    
} else {
    echo "<pre>";
    echo "This script must be run from the command line.\n\n";
    echo "Steps to run:\n";
    echo "1. Download the Bible JSON:\n";
    echo "   wget https://raw.githubusercontent.com/aruljohn/Bible-kjv/master/Bible-kjv.json\n\n";
    echo "2. Run the import script:\n";
    echo "   php import_kjv_bible.php Bible-kjv.json\n";
    echo "</pre>";
}

$db->close();
?>