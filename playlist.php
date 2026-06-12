<?php
require_once 'php/db.php';

// Fetch settings
$settings = [
    'playlist_subtitle' => 'Songs that belong to us.',
    'songs_section_heading' => 'Songs That Mean Something 🎶'
];
try {
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('playlist_subtitle','songs_section_heading')");
    $stmt->execute();
    $rows = $stmt->fetchAll();
    foreach ($rows as $r) {
        $settings[$r['setting_key']] = $r['setting_value'];
    }
} catch (PDOException $e) {
    error_log('Fetch playlist settings error: ' . $e->getMessage());
}

// Fetch songs
$songs = [];
try {
    $stmt = $pdo->prepare('SELECT id, title, artist, personal_note, display_order FROM songs ORDER BY display_order ASC, id ASC');
    $stmt->execute();
    $songs = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch songs error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Our Playlist 🎵 - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .playlist-card {
            max-width: 640px;
            margin: 0 auto;
            padding: 20px;
        }

        .songs-section {
            max-width: 900px;
            margin: 56px auto 0;
        }

        .song-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 20px 24px;
            background: var(--color-card);
            border-radius: 12px;
            border-left: 4px solid var(--color-primary);
            box-shadow: var(--shadow);
            margin-bottom: 14px;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .song-card:hover {
            transform: translateX(4px);
        }

        .song-left {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .song-title {
            font-family: 'Lato', sans-serif;
            font-weight: 500;
            font-size: 1rem;
            color: var(--color-text);
            margin: 0;
        }

        .song-artist {
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            font-size: 0.88rem;
            color: var(--color-text-muted);
            margin: 0;
        }

        .song-note {
            font-family: 'Lato', sans-serif;
            font-style: italic;
            font-size: 0.88rem;
            color: var(--color-text-muted);
            margin-top: 8px;
        }

        .song-right {
            font-size: 1.6rem;
        }

        .empty-state { text-align: center; padding: 40px 20px; color: var(--color-text-muted); font-family: 'Lato', sans-serif; }

        @media (max-width: 640px) {
            .song-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .song-right {
                align-self: flex-end;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <a href="index.php" class="back-link">Back to Home</a>

        <h1 class="section-title">Our Playlist 🎵</h1>
        <p class="section-sub"><?= htmlspecialchars($settings['playlist_subtitle']) ?></p>

        <!-- SECTION 1: Spotify embed -->
        <div class="card playlist-card">
            <!-- Spotify embed: replace the src with your own playlist if desired -->
            <iframe style="border-radius:12px"
                src="https://open.spotify.com/embed/playlist/1xQoCbrO4DrYW2rOHViZfm?utm_source=generator&theme=0"
                width="100%" height="380" frameborder="0" allowfullscreen=""
                allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
                loading="lazy"></iframe>
        </div>

        <!-- SECTION 2: Special Songs list -->
        <div class="songs-section">
            <h2 class="section-title" style="font-size:1.4rem; margin-top:56px;"><?= htmlspecialchars($settings['songs_section_heading']) ?></h2>

            <?php if (empty($songs)): ?>
                <div class="empty-state">No songs yet. Add one from the admin. 🎵</div>
            <?php else: ?>
                <?php foreach ($songs as $song): ?>
                    <div class="song-card">
                        <div class="song-left">
                            <p class="song-title"><?= htmlspecialchars($song['title']) ?></p>
                            <p class="song-artist"><?= htmlspecialchars($song['artist']) ?></p>
                            <?php if (!empty($song['personal_note'])): ?>
                                <p class="song-note"><?= htmlspecialchars($song['personal_note']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="song-right">🎵</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>

    </div>
    <script src="js/main.js"></script>
</body>

</html>