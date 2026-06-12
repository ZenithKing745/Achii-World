<?php
/**
 * Admin Playlist Manager
 * Password-protected interface for managing playlist settings and songs
 */

session_start();

define('ADMIN_PASSWORD', 'changeme123'); // ← Use the SAME password as admin-letters.php

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    if (isset($_POST['password']) && $_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin-playlist.php');
        exit;
    } else {
        $loginError = true;
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: admin-playlist.php');
    exit;
}

// Show login screen if not authenticated
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

// Authenticated section
require_once 'php/db.php';

$editSong = null;
$settings = [
    'playlist_subtitle' => '',
    'songs_section_heading' => ''
];
$songs = [];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'save_settings') {
        $subtitle = $_POST['playlist_subtitle'] ?? '';
        $heading = $_POST['songs_section_heading'] ?? '';
        try {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'playlist_subtitle'");
            $stmt->execute([$subtitle]);
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'songs_section_heading'");
            $stmt->execute([$heading]);
            header('Location: admin-playlist.php');
            exit;
        } catch (PDOException $e) {
            error_log('Save settings error: ' . $e->getMessage());
        }
    }

    if ($action === 'create_song') {
        $title = $_POST['title'] ?? '';
        $artist = $_POST['artist'] ?? '';
        $personal_note = $_POST['personal_note'] ?? '';
        try {
            $stmt = $pdo->prepare('INSERT INTO songs (title, artist, personal_note, display_order) VALUES (?, ?, ?, (SELECT COALESCE(MAX(display_order), 0) + 1 FROM songs))');
            $stmt->execute([$title, $artist, $personal_note]);
            header('Location: admin-playlist.php');
            exit;
        } catch (PDOException $e) {
            error_log('Create song error: ' . $e->getMessage());
        }
    }

    if ($action === 'update_song') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = $_POST['title'] ?? '';
        $artist = $_POST['artist'] ?? '';
        $personal_note = $_POST['personal_note'] ?? '';
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE songs SET title = ?, artist = ?, personal_note = ? WHERE id = ?');
                $stmt->execute([$title, $artist, $personal_note, $id]);
                header('Location: admin-playlist.php');
                exit;
            } catch (PDOException $e) {
                error_log('Update song error: ' . $e->getMessage());
            }
        }
    }

    if ($action === 'delete_song') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM songs WHERE id = ?');
                $stmt->execute([$id]);
                header('Location: admin-playlist.php');
                exit;
            } catch (PDOException $e) {
                error_log('Delete song error: ' . $e->getMessage());
            }
        }
    }

    if ($action === 'reorder') {
        $order = $_POST['order'] ?? '';
        $ids = array_filter(array_map('trim', explode(',', $order)), 'strlen');
        try {
            $pdo->beginTransaction();
            $position = 1;
            $stmt = $pdo->prepare('UPDATE songs SET display_order = ? WHERE id = ?');
            foreach ($ids as $id) {
                $stmt->execute([$position, (int)$id]);
                $position++;
            }
            $pdo->commit();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok']);
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Reorder songs error: ' . $e->getMessage());
            header('Content-Type: application/json', true, 500);
            echo json_encode(['status' => 'error']);
            exit;
        }
    }
}

// Fetch settings
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('playlist_subtitle','songs_section_heading')");
    $stmt->execute();
    $settingsRows = $stmt->fetchAll();
    foreach ($settingsRows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log('Fetch settings error: ' . $e->getMessage());
}

// Fetch songs
try {
    $stmt = $pdo->prepare('SELECT id, title, artist, personal_note, display_order FROM songs ORDER BY display_order ASC, id ASC');
    $stmt->execute();
    $songs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch songs error: ' . $e->getMessage());
}

// Determine edit song
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($songs as $song) {
        if ($song['id'] === $editId) {
            $editSong = $song;
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
    <title>Playlist Manager - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body::before,
        body::after,
        html::before,
        html::after { display: none !important; }

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

        input[type="text"], textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--color-card-border);
            border-radius: 10px;
            font-family: 'Lato', sans-serif;
            font-size: 15px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="text"]:focus, textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(232,114,138,0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
            line-height: 1.8;
            background-color: #FFFAF8;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        .section-card { margin-bottom: 32px; }
        .form-button-row { display: flex; gap: 12px; flex-wrap: wrap; }
        .form-button-row .btn, .form-button-row .btn-outline { flex: 1; }

        .songs-count { background: var(--color-secondary); color: var(--color-text); padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 500; margin-left: 8px; }

        .songs-subtext { font-family: 'Lato', sans-serif; font-size: 0.9rem; color: var(--color-text-muted); margin-top: 6px; }

        .songs-list { display: flex; flex-direction: column; gap: 16px; }

        .song-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px 22px;
            background: var(--color-card);
            border-radius: 12px;
            box-shadow: var(--shadow);
            cursor: grab;
        }

        .song-row.drag-over { border-top: 3px solid var(--color-primary); }

        .drag-handle { font-size: 1.2rem; color: var(--color-text-muted); cursor: grab; flex-shrink: 0; }

        .song-info { flex: 1; display: flex; flex-direction: column; }

        .song-title { font-family: 'Lato', sans-serif; font-weight: 500; font-size: 1rem; color: var(--color-text); margin: 0; }
        .song-artist { font-family: 'Lato', sans-serif; font-weight: 300; font-size: 0.85rem; color: var(--color-text-muted); margin: 4px 0 0 0; }
        .song-note-preview { font-family: 'Lato', sans-serif; font-style: italic; font-size: 0.8rem; color: var(--color-text-muted); margin-top: 8px; }

        .song-actions { display: flex; gap: 8px; flex-shrink: 0; }

        .song-actions .btn-outline, .song-actions .small-delete { padding: 8px 14px; font-size: 13px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }

        .song-actions .btn-outline { border: 1.5px solid var(--color-primary); color: var(--color-primary); background: transparent; }

        .song-actions .btn-outline:hover { background: var(--color-primary); color: white; }

        .song-actions .small-delete { border: 1.5px solid #C45C78; color: #C45C78; background: transparent; cursor: pointer; }

        .song-actions .small-delete:hover { background: #C45C78; color: white; }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--color-text-muted); font-family: 'Lato', sans-serif; }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .song-row { flex-direction: column; align-items: stretch; }
            .song-actions { width: 100%; justify-content: flex-start; flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🎵 Playlist Manager</h1>
        <div class="admin-header-right">
            <a href="index.php">← View Site</a>
            <a href="admin-letters.php">✍️ Letters</a>
            <a href="admin-reasons.php">💛 Reasons</a>
            <a href="?action=logout">Logout</a>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="card section-card">
            <h2 class="section-title" style="font-size: 1.3rem;">Page Texts ✏️</h2>
            <p class="section-sub" style="font-size: 0.95rem; margin-bottom: 20px;">These appear on the playlist page your girlfriend sees.</p>
            <form method="POST">
                <div style="margin-bottom: 16px;">
                    <label for="playlist_subtitle" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">Page subtitle</label>
                    <input type="text" id="playlist_subtitle" name="playlist_subtitle" placeholder="Songs that belong to us." value="<?= htmlspecialchars($settings['playlist_subtitle']) ?>">
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="songs_section_heading" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">Section heading</label>
                    <input type="text" id="songs_section_heading" name="songs_section_heading" placeholder="Songs That Mean Something 🎶" value="<?= htmlspecialchars($settings['songs_section_heading']) ?>">
                </div>
                <input type="hidden" name="action" value="save_settings">
                <button type="submit" class="btn">Save Texts 💾</button>
            </form>
        </div>

        <div class="card section-card" id="song-form">
            <?php if ($editSong): ?>
                <h2 class="section-title">Edit Song ✏️</h2>
            <?php else: ?>
                <h2 class="section-title">Add a Song 🎵</h2>
            <?php endif; ?>
            <form method="POST" style="margin-top: 18px;">
                <div style="margin-bottom: 16px;">
                    <label for="title" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">Song title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. All of Me" value="<?= htmlspecialchars($editSong['title'] ?? '') ?>" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label for="artist" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">Artist</label>
                    <input type="text" id="artist" name="artist" placeholder="e.g. John Legend" value="<?= htmlspecialchars($editSong['artist'] ?? '') ?>" required>
                </div>
                <div style="margin-bottom: 20px;">
                    <label for="personal_note" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px;">Personal note (optional)</label>
                    <textarea id="personal_note" name="personal_note" placeholder="Why does this song mean something? e.g. This was playing the night we..." rows="2"><?= htmlspecialchars($editSong['personal_note'] ?? '') ?></textarea>
                </div>
                <div class="form-button-row">
                    <?php if ($editSong): ?>
                        <button type="submit" class="btn">Save Changes 💾</button>
                        <a href="admin-playlist.php" class="btn-outline" style="display: flex; align-items: center; justify-content: center;">Cancel</a>
                        <input type="hidden" name="action" value="update_song">
                        <input type="hidden" name="id" value="<?= $editSong['id'] ?>">
                    <?php else: ?>
                        <button type="submit" class="btn">Add Song 💕</button>
                        <input type="hidden" name="action" value="create_song">
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="section-card">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <h2 class="section-title" style="margin: 0;">All Songs</h2>
                <span class="songs-count"><?= count($songs) ?></span>
            </div>
            <p class="songs-subtext">Drag to reorder how they appear on the playlist page.</p>

            <?php if (empty($songs)): ?>
                <div class="empty-state">No songs yet. Add your first one above 🎵</div>
            <?php else: ?>
                <div class="songs-list" id="songs-list">
                    <?php foreach ($songs as $song): ?>
                        <div class="card song-row" draggable="true" data-id="<?= $song['id'] ?>">
                            <div class="drag-handle">⠿</div>
                            <div class="song-info">
                                <p class="song-title"><?= htmlspecialchars($song['title']) ?></p>
                                <p class="song-artist"><?= htmlspecialchars($song['artist']) ?></p>
                                <?php $preview = strlen($song['personal_note']) > 60 ? substr($song['personal_note'], 0, 60) . '...' : $song['personal_note']; ?>
                                <?php if ($preview): ?>
                                    <p class="song-note-preview"><?= htmlspecialchars($preview) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="song-actions">
                                <a href="admin-playlist.php?edit=<?= $song['id'] ?>" class="btn-outline">✏️ Edit</a>
                                <form method="POST" style="margin: 0; display: inline-block;">
                                    <input type="hidden" name="action" value="delete_song">
                                    <input type="hidden" name="id" value="<?= $song['id'] ?>">
                                    <button type="submit" class="small-delete" onclick="return confirm('Delete this song? This cannot be undone.');">🗑 Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_GET['edit'])): ?>
        <script>document.getElementById('song-form').scrollIntoView({ behavior: 'smooth' });</script>
    <?php endif; ?>

    <script>
        const songsList = document.getElementById('songs-list');
        let dragSrcEl = null;

        if (songsList) {
            songsList.querySelectorAll('.song-row').forEach(row => {
                row.addEventListener('dragstart', e => {
                    dragSrcEl = row;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', row.dataset.id);
                    row.style.opacity = '0.5';
                });

                row.addEventListener('dragend', () => {
                    row.style.opacity = '';
                    songsList.querySelectorAll('.song-row').forEach(r => r.classList.remove('drag-over'));
                });

                row.addEventListener('dragover', e => {
                    e.preventDefault();
                    row.classList.add('drag-over');
                });

                row.addEventListener('dragleave', () => {
                    row.classList.remove('drag-over');
                });

                row.addEventListener('drop', e => {
                    e.preventDefault();
                    row.classList.remove('drag-over');
                    const draggedId = e.dataTransfer.getData('text/plain');
                    if (!draggedId) return;
                    const draggedEl = songsList.querySelector(`[data-id="${draggedId}"]`);
                    if (!draggedEl || draggedEl === row) return;

                    const bounding = row.getBoundingClientRect();
                    const offset = e.clientY - bounding.top;
                    const insertBefore = offset < bounding.height / 2;
                    songsList.insertBefore(draggedEl, insertBefore ? row : row.nextSibling);

                    const order = Array.from(songsList.querySelectorAll('.song-row')).map(item => item.dataset.id).join(',');
                    fetch('admin-playlist.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ action: 'reorder', order })
                    }).then(response => response.json()).then(data => {
                        if (data.status !== 'ok') {
                            alert('Unable to save new order. Please refresh.');
                        }
                    }).catch(() => {
                        alert('Unable to save new order. Please refresh.');
                    });
                });
            });
        }
    </script>
</body>
</html>
