<?php
/**
 * Admin Letter Manager
 * Password-protected interface for managing letters
 */

session_start();

// Define admin password
define('ADMIN_PASSWORD', 'changeme123'); // ← Change this to your own password

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin-letters.php');
        exit;
    } else {
        $loginError = true;
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin-letters.php');
    exit;
}

// Check authentication
if (!isset($_SESSION['admin'])) {
    // Show login screen
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Access - Achii's World</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--color-bg);">
        <div class="card" style="max-width: 380px; width: 100%; margin: 20px;">
            <div style="text-align: center; margin-bottom: 24px;">
                <div style="font-size: 2rem; margin-bottom: 16px;">🔒</div>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--color-accent); margin: 0; margin-bottom: 8px;">
                    Admin Access
                </h1>
                <p style="font-family: 'Lato', sans-serif; font-size: 0.9rem; color: var(--color-text-muted); margin: 0;">
                    This page is just for you.
                </p>
            </div>

            <?php if (isset($loginError)): ?>
                <div style="background-color: #FFE0E6; border-left: 4px solid var(--color-primary); padding: 10px 14px; margin-bottom: 16px; border-radius: 6px; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-primary);">
                    Wrong password. Try again.
                </div>
            <?php endif; ?>

            <form method="POST" style="margin-top: 24px;">
                <div style="margin-bottom: 16px;">
                    <label for="password" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter password"
                        required
                        autofocus
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px;"
                    >
                </div>
                <input type="hidden" name="action" value="login">
                <button type="submit" class="btn" style="width: 100%;">
                    Enter 🔑
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// === AUTHENTICATED SECTION ===

require_once 'php/db.php';

$editLetter = null;
$allLetters = [];

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if ($title && $content) {
            try {
                $stmt = $pdo->prepare('INSERT INTO letters (title, content) VALUES (?, ?)');
                $stmt->execute([$title, $content]);
                header('Location: admin-letters.php');
                exit;
            } catch (PDOException $e) {
                error_log('Create letter error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'update') {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        if ($id && $title && $content) {
            try {
                $stmt = $pdo->prepare('UPDATE letters SET title=?, content=?, updated_at=NOW() WHERE id=?');
                $stmt->execute([$title, $content, $id]);
                header('Location: admin-letters.php');
                exit;
            } catch (PDOException $e) {
                error_log('Update letter error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        if ($id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM letters WHERE id=?');
                $stmt->execute([$id]);
                header('Location: admin-letters.php');
                exit;
            } catch (PDOException $e) {
                error_log('Delete letter error: ' . $e->getMessage());
            }
        }
    }
}

// Fetch all letters
try {
    $stmt = $pdo->prepare('SELECT id, title, content, created_at, updated_at FROM letters ORDER BY created_at DESC');
    $stmt->execute();
    $allLetters = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching letters: ' . $e->getMessage());
}

// Check if editing a letter
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($allLetters as $letter) {
        if ($letter['id'] === $editId) {
            $editLetter = $letter;
            break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter Manager - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Disable floating hearts on this page */
        body::before,
        body::after,
        html::before,
        html::after {
            display: none !important;
        }

        /* Header Bar */
        .admin-header {
            background: white;
            border-bottom: 1px solid var(--color-card-border);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: var(--color-accent);
            margin: 0;
        }

        .admin-header-right {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .admin-header-right a {
            font-family: 'Lato', sans-serif;
            font-size: 0.9rem;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .admin-header-right a:hover {
            color: var(--color-primary);
        }

        /* Textarea styling */
        textarea {
            font-family: 'Lato', sans-serif;
            font-size: 0.95rem;
            line-height: 1.9;
            background-color: #FFFAF8;
            border: 1.5px solid var(--color-card-border);
            border-radius: 10px;
            padding: 12px 16px;
            resize: vertical;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02), 0 0 0 3px rgba(232, 114, 138, 0.1);
        }

        /* Form button row */
        .form-button-row {
            display: flex;
            gap: 12px;
        }

        .form-button-row .btn {
            flex: 1;
        }

        .form-button-row .btn-outline {
            flex: 1;
        }

        /* Letters count badge */
        .letters-count {
            background: var(--color-secondary);
            color: var(--color-text);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-left: 8px;
        }

        /* Letters list */
        .letters-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .letter-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
        }

        .letter-item-left {
            flex: 1;
        }

        .letter-item-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-style: italic;
            color: var(--color-accent);
            margin: 0 0 6px 0;
        }

        .letter-item-preview {
            font-family: 'Lato', sans-serif;
            font-size: 0.88rem;
            color: var(--color-text-muted);
            margin: 0 0 8px 0;
            line-height: 1.5;
        }

        .letter-item-timestamps {
            font-family: 'Lato', sans-serif;
            font-size: 0.75rem;
            color: var(--color-text-muted);
            margin: 0;
        }

        .letter-item-right {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 100px;
        }

        .letter-item-right .btn-outline,
        .letter-item-delete-btn {
            padding: 8px 18px;
            font-size: 13px;
            border-radius: 8px;
            font-family: 'Lato', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            border: none;
            background: transparent;
            border: 1.5px solid var(--color-primary);
            color: var(--color-primary);
        }

        .letter-item-right .btn-outline:hover {
            background-color: var(--color-primary);
            color: white;
        }

        .letter-item-delete-btn {
            border-color: #C45C78;
            color: #C45C78;
            background: transparent;
        }

        .letter-item-delete-btn:hover {
            background-color: #C45C78;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--color-text-muted);
            font-family: 'Lato', sans-serif;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }

            .admin-header-right {
                width: 100%;
                justify-content: space-between;
            }

            .letter-item {
                flex-direction: column;
            }

            .letter-item-right {
                width: 100%;
                flex-direction: row;
                gap: 8px;
            }

            .letter-item-right button,
            .letter-item-right a {
                flex: 1;
            }
        }

        @media (max-width: 480px) {
            .admin-header {
                padding: 16px;
            }

            .form-button-row {
                flex-direction: column;
            }

            .letter-item-right {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header Bar -->
    <div class="admin-header">
        <h1>✍️ Letter Manager</h1>
        <div class="admin-header-right">
            <a href="index.php">← View Site</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="page-wrapper">
        <!-- Write / Edit Form -->
        <div class="card" id="letter-form" style="margin-bottom: 40px;">
            <?php if ($editLetter): ?>
                <h2 class="section-title">Edit Letter ✏️</h2>
            <?php else: ?>
                <h2 class="section-title">Write a New Letter 💌</h2>
            <?php endif; ?>

            <form method="POST" style="margin-top: 20px;">
                <!-- Title Input -->
                <div style="margin-bottom: 16px;">
                    <label for="title" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Letter Title
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        placeholder="e.g. The Night Everything Changed"
                        value="<?= htmlspecialchars($editLetter['title'] ?? '') ?>"
                        required
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px;"
                    >
                </div>

                <!-- Content Textarea -->
                <div style="margin-bottom: 20px;">
                    <label for="content" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Letter Content
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        placeholder="Write from the heart..."
                        rows="12"
                        required
                    ><?= htmlspecialchars($editLetter['content'] ?? '') ?></textarea>
                </div>

                <!-- Button Row -->
                <div class="form-button-row">
                    <?php if ($editLetter): ?>
                        <button type="submit" class="btn">Save Changes 💾</button>
                        <a href="admin-letters.php" class="btn-outline" style="display: flex; align-items: center; justify-content: center;">Cancel</a>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editLetter['id'] ?>">
                    <?php else: ?>
                        <button type="submit" class="btn">Save Letter 💕</button>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Letters List -->
        <div>
            <h2 class="section-title">
                All Letters
                <span class="letters-count"><?= count($allLetters) ?></span>
            </h2>

            <?php if (empty($allLetters)): ?>
                <div class="empty-state">
                    <p>No letters yet. Write your first one above 🌸</p>
                </div>
            <?php else: ?>
                <div class="letters-list">
                    <?php foreach ($allLetters as $letter): ?>
                        <div class="card letter-item">
                            <div class="letter-item-left">
                                <h3 class="letter-item-title"><?= htmlspecialchars($letter['title']) ?></h3>
                                <p class="letter-item-preview">
                                    <?= htmlspecialchars(substr($letter['content'], 0, 120)) ?>...
                                </p>
                                <p class="letter-item-timestamps">
                                    Written: <?= date('F j, Y', strtotime($letter['created_at'])) ?><br>
                                    Last edited: <?= $letter['updated_at'] ? date('F j, Y', strtotime($letter['updated_at'])) : '—' ?>
                                </p>
                            </div>
                            <div class="letter-item-right">
                                <a href="admin-letters.php?edit=<?= $letter['id'] ?>" class="btn-outline">✏️ Edit</a>
                                <form method="POST" style="display: contents;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $letter['id'] ?>">
                                    <button 
                                        type="submit" 
                                        class="letter-item-delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this letter? This cannot be undone.');"
                                    >
                                        🗑 Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scroll to form on edit -->
    <?php if (isset($_GET['edit'])): ?>
        <script>
            document.getElementById('letter-form').scrollIntoView({ behavior: 'smooth' });
        </script>
    <?php endif; ?>
</body>
</html>
