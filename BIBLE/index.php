<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

// -------------------------------
// LANGUAGE HANDLING
// -------------------------------
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
// -------------------------------

$db = getDBConnection();

// Get all books for navigation
$books = getAllBooks($db);

// Handle different actions
$action = $_GET['action'] ?? 'home';
$bookId = $_GET['book'] ?? null;
$chapter = $_GET['chapter'] ?? 1;
$searchQuery = $_GET['q'] ?? '';

?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bible Reader</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">

        <!-- Header -->
        <header>
            <h1>📖 Online Bible Reader</h1>

            <div style="display:flex;gap:20px;align-items:center;">

                <!-- Search -->
                <div class="search-bar">
                    <form action="index.php" method="GET">
                        <input type="hidden" name="action" value="search">
                        <input type="text" name="q" placeholder="Search the Bible..." 
                            value="<?= htmlspecialchars($searchQuery) ?>" required>
                        <button type="submit">Search</button>
                    </form>
                </div>

                <!-- LANGUAGE SWITCHER -->
                <div class="language-switcher">
                    <form method="GET" onchange="this.submit()">

                        <!-- Preserve existing GET parameters -->
                        <input type="hidden" name="action" value="<?= $action ?>">
                        <?php if ($bookId): ?><input type="hidden" name="book" value="<?= $bookId ?>"><?php endif; ?>
                        <?php if ($chapter): ?><input type="hidden" name="chapter" value="<?= $chapter ?>"><?php endif; ?>
                        <?php if ($searchQuery): ?><input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>"><?php endif; ?>

                        <select name="lang">
                            <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>English</option>
                            <option value="fr" <?= $lang === 'fr' ? 'selected' : '' ?>>French</option>
                            <option value="es" <?= $lang === 'es' ? 'selected' : '' ?>>Spanish</option>
                            <option value="sw" <?= $lang === 'sw' ? 'selected' : '' ?>>Swahili</option>
                        </select>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="main-content">

            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <h3>Books of the Bible</h3>

                <div class="testament-section">
                    <h4>Old Testament</h4>
                    <ul class="book-list">
                        <?php foreach ($books as $book): ?>
                            <?php if ($book['testament'] === 'Old'): ?>
                                <li>
                                    <a href="?action=read&book=<?= $book['id'] ?>&chapter=1&lang=<?= $lang ?>"
                                       class="<?= $bookId == $book['id'] ? 'active' : '' ?>">
                                        <?= htmlspecialchars($book['book_name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="testament-section">
                    <h4>New Testament</h4>
                    <ul class="book-list">
                        <?php foreach ($books as $book): ?>
                            <?php if ($book['testament'] === 'New'): ?>
                                <li>
                                    <a href="?action=read&book=<?= $book['id'] ?>&chapter=1&lang=<?= $lang ?>"
                                       class="<?= $bookId == $book['id'] ? 'active' : '' ?>">
                                        <?= htmlspecialchars($book['book_name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>

            <!-- Content Area -->
            <main class="content">
                <?php if ($action === 'home'): ?>

                    <div class="welcome">
                        <h2>Welcome to the Online Bible Reader</h2>
                        <p>Select a book from the sidebar to start reading, or search for specific verses.</p>

                        <div class="quick-links">
                            <h3>Quick Access:</h3>
                            <a href="?action=read&book=1&chapter=1&lang=<?= $lang ?>" class="quick-link">Genesis 1</a>
                            <a href="?action=read&book=19&chapter=23&lang=<?= $lang ?>" class="quick-link">Psalm 23</a>
                            <a href="?action=read&book=40&chapter=5&lang=<?= $lang ?>" class="quick-link">Matthew 5</a>
                            <a href="?action=read&book=43&chapter=3&lang=<?= $lang ?>" class="quick-link">John 3</a>
                        </div>
                    </div>

                <?php elseif ($action === 'read' && $bookId): ?>
                    <?php
                    $bookInfo = getBookById($db, $bookId);
                    if ($bookInfo):
                        $verses = getVerses($db, $bookId, $chapter); // You can extend to multi-language later
                    ?>
                        <div class="reading-header">
                            <h2><?= htmlspecialchars($bookInfo['book_name']) ?> - Chapter <?= $chapter ?></h2>

                            <div class="chapter-nav">
                                <?php if ($chapter > 1): ?>
                                    <a href="?action=read&book=<?= $bookId ?>&chapter=<?= $chapter - 1 ?>&lang=<?= $lang ?>" class="nav-btn">← Previous</a>
                                <?php endif; ?>

                                <select onchange="location.href='?action=read&book=<?= $bookId ?>&chapter=' + this.value + '&lang=<?= $lang ?>'">
                                    <?php for ($i = 1; $i <= $bookInfo['chapters']; $i++): ?>
                                        <option value="<?= $i ?>" <?= $i == $chapter ? 'selected' : '' ?>>
                                            Chapter <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>

                                <?php if ($chapter < $bookInfo['chapters']): ?>
                                    <a href="?action=read&book=<?= $bookId ?>&chapter=<?= $chapter + 1 ?>&lang=<?= $lang ?>" class="nav-btn">Next →</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="verses">
                            <?php if (empty($verses)): ?>
                                <p class="no-content">No verses found for this chapter.</p>
                            <?php else: ?>
                                <?php foreach ($verses as $verse): ?>
                                    <div class="verse" id="verse-<?= $verse['verse'] ?>">
                                        <span class="verse-number"><?= $verse['verse'] ?></span>
                                        <span class="verse-text"><?= nl2br(htmlspecialchars($verse['text'])) ?></span>

                                        <button class="bookmark-btn"
                                            onclick="bookmarkVerse(<?= $bookId ?>, <?= $chapter ?>, <?= $verse['verse'] ?>)"
                                            title="Bookmark this verse">🔖</button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <p class="error">Book not found.</p>
                    <?php endif; ?>

                <?php elseif ($action === 'search' && $searchQuery): ?>

                    <?php
                    $searchResults = searchBible($db, $searchQuery);
                    ?>
                    <div class="search-results">
                        <h2>Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h2>
                        <p class="result-count"><?= count($searchResults) ?> verses found</p>

                        <?php if (empty($searchResults)): ?>
                            <p class="no-results">No results found.</p>

                        <?php else: ?>
                            <?php foreach ($searchResults as $result): ?>
                                <div class="search-result">
                                    <div class="result-reference">
                                        <a href="?action=read&book=<?= $result['book_id'] ?>&chapter=<?= $result['chapter'] ?>#verse-<?= $result['verse'] ?>&lang=<?= $lang ?>">
                                            <strong><?= htmlspecialchars($result['book_name']) ?> <?= $result['chapter'] ?>:<?= $result['verse'] ?></strong>
                                        </a>
                                    </div>
                                    <div class="result-text">
                                        <?= highlightSearchTerm(htmlspecialchars($result['text']), $searchQuery) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <p class="error">Invalid action or missing parameters.</p>
                <?php endif; ?>

            </main>
        </div>

        <footer>
            <p>&copy; <?= date('Y') ?> Online Bible Reader | Built with PHP & MySQL</p>
        </footer>

    </div>

    <script>
        function bookmarkVerse(bookId, chapter, verse) {
            if (confirm('Add bookmark for this verse?')) {
                fetch('bookmark.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `book_id=${bookId}&chapter=${chapter}&verse=${verse}`
                })
                .then(response => response.json())
                .then(data => { alert(data.message); })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>

</body>
</html>
