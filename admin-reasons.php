<?php
/**
 * Admin Reasons Manager
 * Password-protected interface for managing reasons
 */

session_start();

define('ADMIN_PASSWORD', 'changeme123'); // ← Use the SAME password as admin-letters.php

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (isset($_POST['password']) && $_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin-reasons.php');
        exit;
    } else {
        $loginError = true;
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin-reasons.php');
    exit;
}

// If not authenticated, show login screen and exit
if (!isset($_SESSION['admin'])) {
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

// === Authenticated section ===
require_once 'php/db.php';

$editReason = null;
$allReasons = [];

// Handle CRUD actions via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create') {
        $content = $_POST['content'] ?? '';
        if (trim($content) !== '') {
            try {
                $stmt = $pdo->prepare('INSERT INTO reasons (content) VALUES (?)');
                $stmt->execute([$content]);
                header('Location: admin-reasons.php');
                exit;
            } catch (PDOException $e) {
                error_log('Create reason error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'update') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $content = $_POST['content'] ?? '';
        if ($id > 0 && trim($content) !== '') {
            try {
                $stmt = $pdo->prepare('UPDATE reasons SET content=? WHERE id=?');
                $stmt->execute([$content, $id]);
                header('Location: admin-reasons.php');
                exit;
            } catch (PDOException $e) {
                error_log('Update reason error: ' . $e->getMessage());
            }
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM reasons WHERE id=?');
                $stmt->execute([$id]);
                header('Location: admin-reasons.php');
                exit;
            } catch (PDOException $e) {
                error_log('Delete reason error: ' . $e->getMessage());
            }
        }
    }
}

// Fetch all reasons
try {
    $stmt = $pdo->prepare('SELECT id, content, created_at FROM reasons ORDER BY id ASC');
    $stmt->execute();
    $allReasons = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching reasons: ' . $e->getMessage());
}

// If editing a reason, find it
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($allReasons as $r) {
        if ($r['id'] === $editId) {
            $editReason = $r;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reasons Manager - Achii's World</title>
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
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

        .admin-header-right a:hover { color: var(--color-primary); }

        textarea {
            font-family: 'Lato', sans-serif;
            font-size: 0.95rem;
            line-height: 1.8;
            background-color: #FFFAF8;
            border: 1.5px solid var(--color-card-border);
            border-radius: 10px;
            padding: 12px 16px;
            resize: vertical;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        textarea:focus { outline: none; border-color: var(--color-primary); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02), 0 0 0 3px rgba(232,114,138,0.1); }

        .form-button-row { display: flex; gap: 12px; }
        .form-button-row .btn, .form-button-row .btn-outline { flex: 1; }

        .reasons-count { background: var(--color-secondary); color: var(--color-text); padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; margin-left: 8px; }

        .reasons-list { display: flex; flex-direction: column; gap: 16px; }

        .reason-item { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }

        .reason-left { display: flex; gap: 10px; align-items: flex-start; flex: 1; }

        .reason-badge { background: var(--color-secondary); color: white; padding: 6px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }

        .reason-content { font-family: 'Playfair Display', serif; font-style: italic; font-size: 0.95rem; color: var(--color-text); line-height: 1.7; margin: 0; }

        .reason-date { font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 6px; }

        .reason-actions { display: flex; gap: 8px; }

        .small-outline { padding: 8px 14px; font-size: 13px; border-radius: 8px; text-decoration: none; border: 1.5px solid var(--color-primary); color: var(--color-primary); background: transparent; display: inline-block; }

        .small-delete { padding: 8px 14px; font-size: 13px; border-radius: 8px; border: 1.5px solid #C45C78; color: #C45C78; background: transparent; cursor: pointer; }

        .small-outline:hover { background: var(--color-primary); color: white; }
        .small-delete:hover { background: #C45C78; color: white; }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--color-text-muted); font-family: 'Lato', sans-serif; }

        @media (max-width: 768px) { .admin-header { flex-direction: column; gap: 16px; align-items: flex-start; } .reason-item { flex-direction: column; } .reason-actions { width: 100%; justify-content: flex-start; } }

    </style>
</head>
<body>
    <div class="admin-header">
        <h1>💛 Reasons Manager</h1>
        <div class="admin-header-right">
            <a href="index.php">← View Site</a>
            <a href="admin-letters.php">✍️ Letters</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="card" id="reason-form" style="margin-bottom: 32px;">
            <?php if ($editReason): ?>
                <h2 class="section-title">Edit Reason ✏️</h2>
            <?php else: ?>
                <h2 class="section-title">Add a New Reason 💛</h2>
            <?php endif; ?>

            <form method="POST" style="margin-top: 16px;">
                <div style="margin-bottom: 12px;">
                    <label for="content" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">The reason</label>
                    <textarea id="content" name="content" placeholder="What do you love about her? Be specific." rows="5"><?= htmlspecialchars($editReason['content'] ?? '') ?></textarea>
                </div>

                <div class="form-button-row">
                    <?php if ($editReason): ?>
                        <button type="submit" class="btn">Save Changes 💾</button>
                        <a href="admin-reasons.php" class="btn-outline" style="display: flex; align-items: center; justify-content: center;">Cancel</a>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $editReason['id'] ?>">
                    <?php else: ?>
                        <button type="submit" class="btn">Add Reason 💕</button>
                        <input type="hidden" name="action" value="create">
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div>
            <h2 class="section-title">All Reasons <span class="reasons-count"><?= count($allReasons) ?></span></h2>

            <?php if (empty($allReasons)): ?>
                <div class="empty-state">No reasons yet. Add your first one above 💛</div>
            <?php else: ?>
                <div class="reasons-list">
                    <?php foreach ($allReasons as $index => $reason): ?>
                        <div class="card reason-item">
                            <div class="reason-left">
                                <div class="reason-badge">#<?= str_pad($index+1, 2, '0', STR_PAD_LEFT) ?></div>
                                <div>
                                    <p class="reason-content"><?= htmlspecialchars($reason['content']) ?></p>
                                    <div class="reason-date">Added: <?= date('F j, Y', strtotime($reason['created_at'])) ?></div>
                                </div>
                            </div>
                            <div class="reason-actions">
                                <a href="admin-reasons.php?edit=<?= $reason['id'] ?>" class="small-outline">✏️ Edit</a>
                                <form method="POST" style="display: inline-block; margin: 0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $reason['id'] ?>">
                                    <button type="submit" class="small-delete" onclick="return confirm('Delete this reason? This cannot be undone.');">🗑 Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['edit'])): ?>
        <script>document.getElementById('reason-form').scrollIntoView({ behavior: 'smooth' });</script>
    <?php endif; ?>
</body>
</html>
