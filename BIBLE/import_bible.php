<?php
/**
 * Web-Based KJV Bible Import Script - UPDATED FOR CURRENT GITHUB STRUCTURE
 * Run this from your browser: http://yourdomain.com/import_web.php
 * FOR SHARED HOSTING - NO COMMAND LINE NEEDED
 */

set_time_limit(600); // 10 minutes
ini_set('memory_limit', '256M');

require_once 'config.php';

define('IMPORT_PASSWORD', 'changeme123');

$authenticated = false;
if (isset($_POST['password']) && $_POST['password'] === IMPORT_PASSWORD) {
    $authenticated = true;
}

// Bible books with correct filenames from GitHub
$books = [
    ['id' => 1, 'name' => 'Genesis', 'file' => 'Genesis', 'testament' => 'Old', 'chapters' => 50],
    ['id' => 2, 'name' => 'Exodus', 'file' => 'Exodus', 'testament' => 'Old', 'chapters' => 40],
    ['id' => 3, 'name' => 'Leviticus', 'file' => 'Leviticus', 'testament' => 'Old', 'chapters' => 27],
    ['id' => 4, 'name' => 'Numbers', 'file' => 'Numbers', 'testament' => 'Old', 'chapters' => 36],
    ['id' => 5, 'name' => 'Deuteronomy', 'file' => 'Deuteronomy', 'testament' => 'Old', 'chapters' => 34],
    ['id' => 6, 'name' => 'Joshua', 'file' => 'Joshua', 'testament' => 'Old', 'chapters' => 24],
    ['id' => 7, 'name' => 'Judges', 'file' => 'Judges', 'testament' => 'Old', 'chapters' => 21],
    ['id' => 8, 'name' => 'Ruth', 'file' => 'Ruth', 'testament' => 'Old', 'chapters' => 4],
    ['id' => 9, 'name' => '1 Samuel', 'file' => '1Samuel', 'testament' => 'Old', 'chapters' => 31],
    ['id' => 10, 'name' => '2 Samuel', 'file' => '2Samuel', 'testament' => 'Old', 'chapters' => 24],
    ['id' => 11, 'name' => '1 Kings', 'file' => '1Kings', 'testament' => 'Old', 'chapters' => 22],
    ['id' => 12, 'name' => '2 Kings', 'file' => '2Kings', 'testament' => 'Old', 'chapters' => 25],
    ['id' => 13, 'name' => '1 Chronicles', 'file' => '1Chronicles', 'testament' => 'Old', 'chapters' => 29],
    ['id' => 14, 'name' => '2 Chronicles', 'file' => '2Chronicles', 'testament' => 'Old', 'chapters' => 36],
    ['id' => 15, 'name' => 'Ezra', 'file' => 'Ezra', 'testament' => 'Old', 'chapters' => 10],
    ['id' => 16, 'name' => 'Nehemiah', 'file' => 'Nehemiah', 'testament' => 'Old', 'chapters' => 13],
    ['id' => 17, 'name' => 'Esther', 'file' => 'Esther', 'testament' => 'Old', 'chapters' => 10],
    ['id' => 18, 'name' => 'Job', 'file' => 'Job', 'testament' => 'Old', 'chapters' => 42],
    ['id' => 19, 'name' => 'Psalms', 'file' => 'Psalms', 'testament' => 'Old', 'chapters' => 150],
    ['id' => 20, 'name' => 'Proverbs', 'file' => 'Proverbs', 'testament' => 'Old', 'chapters' => 31],
    ['id' => 21, 'name' => 'Ecclesiastes', 'file' => 'Ecclesiastes', 'testament' => 'Old', 'chapters' => 12],
    ['id' => 22, 'name' => 'Song of Solomon', 'file' => 'SongofSolomon', 'testament' => 'Old', 'chapters' => 8],
    ['id' => 23, 'name' => 'Isaiah', 'file' => 'Isaiah', 'testament' => 'Old', 'chapters' => 66],
    ['id' => 24, 'name' => 'Jeremiah', 'file' => 'Jeremiah', 'testament' => 'Old', 'chapters' => 52],
    ['id' => 25, 'name' => 'Lamentations', 'file' => 'Lamentations', 'testament' => 'Old', 'chapters' => 5],
    ['id' => 26, 'name' => 'Ezekiel', 'file' => 'Ezekiel', 'testament' => 'Old', 'chapters' => 48],
    ['id' => 27, 'name' => 'Daniel', 'file' => 'Daniel', 'testament' => 'Old', 'chapters' => 12],
    ['id' => 28, 'name' => 'Hosea', 'file' => 'Hosea', 'testament' => 'Old', 'chapters' => 14],
    ['id' => 29, 'name' => 'Joel', 'file' => 'Joel', 'testament' => 'Old', 'chapters' => 3],
    ['id' => 30, 'name' => 'Amos', 'file' => 'Amos', 'testament' => 'Old', 'chapters' => 9],
    ['id' => 31, 'name' => 'Obadiah', 'file' => 'Obadiah', 'testament' => 'Old', 'chapters' => 1],
    ['id' => 32, 'name' => 'Jonah', 'file' => 'Jonah', 'testament' => 'Old', 'chapters' => 4],
    ['id' => 33, 'name' => 'Micah', 'file' => 'Micah', 'testament' => 'Old', 'chapters' => 7],
    ['id' => 34, 'name' => 'Nahum', 'file' => 'Nahum', 'testament' => 'Old', 'chapters' => 3],
    ['id' => 35, 'name' => 'Habakkuk', 'file' => 'Habakkuk', 'testament' => 'Old', 'chapters' => 3],
    ['id' => 36, 'name' => 'Zephaniah', 'file' => 'Zephaniah', 'testament' => 'Old', 'chapters' => 3],
    ['id' => 37, 'name' => 'Haggai', 'file' => 'Haggai', 'testament' => 'Old', 'chapters' => 2],
    ['id' => 38, 'name' => 'Zechariah', 'file' => 'Zechariah', 'testament' => 'Old', 'chapters' => 14],
    ['id' => 39, 'name' => 'Malachi', 'file' => 'Malachi', 'testament' => 'Old', 'chapters' => 4],
    ['id' => 40, 'name' => 'Matthew', 'file' => 'Matthew', 'testament' => 'New', 'chapters' => 28],
    ['id' => 41, 'name' => 'Mark', 'file' => 'Mark', 'testament' => 'New', 'chapters' => 16],
    ['id' => 42, 'name' => 'Luke', 'file' => 'Luke', 'testament' => 'New', 'chapters' => 24],
    ['id' => 43, 'name' => 'John', 'file' => 'John', 'testament' => 'New', 'chapters' => 21],
    ['id' => 44, 'name' => 'Acts', 'file' => 'Acts', 'testament' => 'New', 'chapters' => 28],
    ['id' => 45, 'name' => 'Romans', 'file' => 'Romans', 'testament' => 'New', 'chapters' => 16],
    ['id' => 46, 'name' => '1 Corinthians', 'file' => '1Corinthians', 'testament' => 'New', 'chapters' => 16],
    ['id' => 47, 'name' => '2 Corinthians', 'file' => '2Corinthians', 'testament' => 'New', 'chapters' => 13],
    ['id' => 48, 'name' => 'Galatians', 'file' => 'Galatians', 'testament' => 'New', 'chapters' => 6],
    ['id' => 49, 'name' => 'Ephesians', 'file' => 'Ephesians', 'testament' => 'New', 'chapters' => 6],
    ['id' => 50, 'name' => 'Philippians', 'file' => 'Philippians', 'testament' => 'New', 'chapters' => 4],
    ['id' => 51, 'name' => 'Colossians', 'file' => 'Colossians', 'testament' => 'New', 'chapters' => 4],
    ['id' => 52, 'name' => '1 Thessalonians', 'file' => '1Thessalonians', 'testament' => 'New', 'chapters' => 5],
    ['id' => 53, 'name' => '2 Thessalonians', 'file' => '2Thessalonians', 'testament' => 'New', 'chapters' => 3],
    ['id' => 54, 'name' => '1 Timothy', 'file' => '1Timothy', 'testament' => 'New', 'chapters' => 6],
    ['id' => 55, 'name' => '2 Timothy', 'file' => '2Timothy', 'testament' => 'New', 'chapters' => 4],
    ['id' => 56, 'name' => 'Titus', 'file' => 'Titus', 'testament' => 'New', 'chapters' => 3],
    ['id' => 57, 'name' => 'Philemon', 'file' => 'Philemon', 'testament' => 'New', 'chapters' => 1],
    ['id' => 58, 'name' => 'Hebrews', 'file' => 'Hebrews', 'testament' => 'New', 'chapters' => 13],
    ['id' => 59, 'name' => 'James', 'file' => 'James', 'testament' => 'New', 'chapters' => 5],
    ['id' => 60, 'name' => '1 Peter', 'file' => '1Peter', 'testament' => 'New', 'chapters' => 5],
    ['id' => 61, 'name' => '2 Peter', 'file' => '2Peter', 'testament' => 'New', 'chapters' => 3],
    ['id' => 62, 'name' => '1 John', 'file' => '1John', 'testament' => 'New', 'chapters' => 5],
    ['id' => 63, 'name' => '2 John', 'file' => '2John', 'testament' => 'New', 'chapters' => 1],
    ['id' => 64, 'name' => '3 John', 'file' => '3John', 'testament' => 'New', 'chapters' => 1],
    ['id' => 65, 'name' => 'Jude', 'file' => 'Jude', 'testament' => 'New', 'chapters' => 1],
    ['id' => 66, 'name' => 'Revelation', 'file' => 'Revelation', 'testament' => 'New', 'chapters' => 22]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bible Import - Web Interface</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 10px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
        h1 { color: #333; margin-bottom: 20px; text-align: center; }
        .step { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #667eea; }
        .step h3 { color: #667eea; margin-bottom: 10px; }
        .btn { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px 5px; transition: background 0.3s; }
        .btn:hover { background: #5568d3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        input[type="password"] { padding: 10px; border: 2px solid #ddd; border-radius: 5px; width: 100%; margin: 10px 0; }
        .log { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; max-height: 500px; overflow-y: auto; margin: 15px 0; font-size: 13px; line-height: 1.5; }
        .progress { width: 100%; height: 30px; background: #e0e0e0; border-radius: 15px; overflow: hidden; margin: 15px 0; }
        .progress-bar { height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-card h4 { font-size: 14px; margin-bottom: 10px; opacity: 0.9; }
        .stat-card .number { font-size: 32px; font-weight: bold; }
        .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📖 Bible Database Import Tool</h1>
        
        <?php if (!$authenticated): ?>
            <div class="step">
                <h3>🔒 Authentication Required</h3>
                <p>Enter the import password to continue:</p>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter password" required>
                    <button type="submit" class="btn">Authenticate</button>
                </form>
                <p style="margin-top: 15px; color: #666;">
                    <small>Default password: <code>changeme123</code></small>
                </p>
            </div>
        
        <?php else: ?>
            
            <div class="alert alert-info">
                <strong>ℹ️ How it works:</strong> This tool will download all 66 books of the KJV Bible from GitHub (one book at a time) and import them into your MySQL database. The process takes 2-5 minutes.
            </div>
            
            <div class="step">
                <h3>🚀 Start Import</h3>
                <p>Click the button below to begin importing the complete KJV Bible.</p>
                <form method="POST" action="">
                    <input type="hidden" name="password" value="<?php echo IMPORT_PASSWORD; ?>">
                    <input type="hidden" name="action" value="start_import">
                    <button type="submit" class="btn btn-success">Start Bible Import</button>
                </form>
            </div>
            
            <?php
            if (isset($_POST['action']) && $_POST['action'] === 'start_import') {
                $db = getDBConnection();
                
                echo '<div class="log" id="importLog">';
                
                // Step 1: Insert books
                echo "========================================\n";
                echo "STEP 1: Creating Books Table\n";
                echo "========================================\n";
                flush(); ob_flush();
                
                $stmt = $db->prepare("INSERT INTO books (id, book_number, book_name, testament, chapters) 
                                      VALUES (?, ?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE book_name = VALUES(book_name)");
                
                $bookCount = 0;
                foreach ($books as $book) {
                    $stmt->bind_param("iissi", $book['id'], $book['id'], $book['name'], $book['testament'], $book['chapters']);
                    if ($stmt->execute()) {
                        $bookCount++;
                        echo "✓ {$book['id']}. {$book['name']}\n";
                        flush(); ob_flush();
                    }
                }
                $stmt->close();
                
                echo "\n✅ Inserted {$bookCount} books successfully!\n\n";
                flush(); ob_flush();
                
                // Step 2: Import verses
                echo "========================================\n";
                echo "STEP 2: Importing Verses (66 books)\n";
                echo "========================================\n";
                echo "This will take a few minutes...\n\n";
                flush(); ob_flush();
                
                $verseStmt = $db->prepare("INSERT INTO verses (book_id, chapter, verse, text) VALUES (?, ?, ?, ?)");
                
                $totalVerses = 0;
                $successBooks = 0;
                $failedBooks = [];
                
                foreach ($books as $index => $book) {
                    $progress = round((($index + 1) / count($books)) * 100);
                    
                    // GitHub raw URL for each book
                    $url = "https://raw.githubusercontent.com/aruljohn/Bible-kjv/master/{$book['file']}.json";
                    
                    echo "[{$progress}%] ➤ Downloading {$book['name']}...\n";
                    flush(); ob_flush();
                    
                    // Download JSON
                    $context = stream_context_create(['http' => ['timeout' => 30]]);
                    $jsonContent = @file_get_contents($url, false, $context);
                    
                    if ($jsonContent === false) {
                        echo "  ✗ Failed to download from GitHub\n";
                        $failedBooks[] = $book['name'];
                        flush(); ob_flush();
                        continue;
                    }
                    
                    $bookData = json_decode($jsonContent, true);
                    
                    if (!$bookData || !isset($bookData['chapters'])) {
                        echo "  ✗ Invalid JSON format\n";
                        $failedBooks[] = $book['name'];
                        flush(); ob_flush();
                        continue;
                    }
                    
                    $bookVerseCount = 0;
                    
                    // Parse and insert verses
                    foreach ($bookData['chapters'] as $chapterData) {
                        $chapterNum = $chapterData['chapter'];
                        
                        foreach ($chapterData['verses'] as $verseData) {
                            $verseNum = $verseData['verse'];
                            $verseText = $verseData['text'];
                            
                            $verseStmt->bind_param("iiis", $book['id'], $chapterNum, $verseNum, $verseText);
                            
                            if ($verseStmt->execute()) {
                                $bookVerseCount++;
                                $totalVerses++;
                            }
                        }
                    }
                    
                    $successBooks++;
                    echo "  ✅ Imported {$bookVerseCount} verses\n\n";
                    flush(); ob_flush();
                    
                    // Small delay to be nice to GitHub
                    usleep(200000); // 0.2 seconds
                }
                
                $verseStmt->close();
                
                echo "========================================\n";
                echo "✅ IMPORT COMPLETE!\n";
                echo "========================================\n";
                echo "📚 Books imported: {$successBooks}/66\n";
                echo "📝 Total verses: " . number_format($totalVerses) . "\n";
                
                if (!empty($failedBooks)) {
                    echo "\n⚠️ Failed books: " . implode(', ', $failedBooks) . "\n";
                    echo "   (You can re-run the import to retry)\n";
                }
                
                echo "\n🎉 Your Bible app is ready to use!\n";
                echo "🔒 DELETE this file (import_web.php) for security!\n";
                
                echo '</div>';
                
                // Show final statistics
                echo '<div class="stats">';
                
                $bookCount = $db->query("SELECT COUNT(*) as count FROM books")->fetch_assoc()['count'];
                $verseCount = $db->query("SELECT COUNT(*) as count FROM verses")->fetch_assoc()['count'];
                
                echo '<div class="stat-card">';
                echo '<h4>Total Books</h4>';
                echo '<div class="number">' . $bookCount . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<h4>Total Verses</h4>';
                echo '<div class="number">' . number_format($verseCount) . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card">';
                echo '<h4>Success Rate</h4>';
                echo '<div class="number">' . round(($successBooks/66)*100) . '%</div>';
                echo '</div>';
                
                echo '</div>';
                
                if ($verseCount > 30000) {
                    echo '<div class="alert alert-success">';
                    echo '<strong>✅ Success!</strong> Import completed successfully. All books and verses are in your database.';
                    echo '</div>';
                }
                
                echo '<div class="alert alert-warning">';
                echo '<strong>⚠️ Security:</strong> DELETE or rename import_web.php immediately!';
                echo '</div>';
                
                echo '<a href="index.php" class="btn btn-success" style="display:inline-block; text-decoration:none;">Go to Bible App →</a>';
                
                $db->close();
            }
            ?>
        
        <?php endif; ?>
    </div>
</body>
</html>