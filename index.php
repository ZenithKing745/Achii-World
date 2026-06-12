<?php
/**
 * Achii's World - Personal Love Site
 * Homepage with feature cards and countdown timer
 */

require_once 'php/db.php';

// Fetch next upcoming date from database
$nextDate = null;
try {
    $stmt = $pdo->prepare('
        SELECT date_value, time_value, label 
        FROM dates 
        WHERE date_value >= CURDATE() 
        ORDER BY date_value ASC 
        LIMIT 1
    ');
    $stmt->execute();
    $row = $stmt->fetch();
    
    if ($row) {
        // Combine date and time into DateTime
        $dateStr = $row['date_value'];
        $timeStr = $row['time_value'] ?? '00:00:00';
        $dateTime = new DateTime("{$dateStr} {$timeStr}");
        
        // Convert to Unix timestamp (in seconds)
        $timestamp = $dateTime->getTimestamp();
        $label = $row['label'] ?? 'Our Next Date';
        
        $nextDate = [
            'timestamp' => $timestamp,
            'label' => $label
        ];
    }
} catch (PDOException $e) {
    // Silently fail, countdown will show "no date set"
    error_log('Date query error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achii's World 🌸</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ========== SECTION 1: HERO ========== -->
    <section style="padding: 80px 20px 60px; text-align: center;">
        <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2.8rem, 7vw, 4.8rem); color: var(--color-accent); margin: 0;">
            Halloo, Achii 🌸
        </h1>
        <p style="font-family: 'Lato', sans-serif; font-weight: 300; font-size: 1.2rem; color: var(--color-text-muted); margin-top: 12px; margin-bottom: 28px;">
            What do you need?
        </p>
        <div style="font-size: 1.4rem; letter-spacing: 4px; opacity: 0.4;">
            🌹🌹🌹
        </div>
    </section>

    <!-- ========== SECTION 2: FEATURE CARDS ========== -->
    <section class="page-wrapper">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            
            <!-- Card 1: Set a Date -->
            <a href="set-date.php" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">📅</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        Set a Date
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Plan our next adventure
                    </p>
                </div>
            </a>

            <!-- Card 2: All My Letters -->
            <a href="letters.php" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">💌</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        All My Letters
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Letters just for you
                    </p>
                </div>
            </a>

            <!-- Card 3: Photo Booth -->
            <a href="photo-booth.html" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">🤳</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        Photo Booth
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Strike a pose together
                    </p>
                </div>
            </a>

            <!-- Card 4: Countdown -->
            <a href="#countdown" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">⏳</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        Countdown
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Days until the next moment
                    </p>
                </div>
            </a>

            <!-- Card 5: Our Playlist -->
            <a href="playlist.html" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">🎵</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        Our Playlist
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Songs that are ours
                    </p>
                </div>
            </a>

            <!-- Card 6: Why I Love You -->
            <a href="why-i-love-you.php" style="text-decoration: none; color: inherit;">
                <div class="card" style="text-align: center; cursor: pointer; height: 100%;">
                    <div style="font-size: 2.8rem; display: block; margin-bottom: 12px;">💛</div>
                    <h3 style="font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--color-accent); margin: 0;">
                        Why I Love You
                    </h3>
                    <p style="font-family: 'Lato', sans-serif; font-size: 0.88rem; color: var(--color-text-muted); margin-top: 6px; margin-bottom: 0;">
                        Let me count the ways
                    </p>
                </div>
            </a>

        </div>
    </section>

    <!-- ========== SECTION 3: COUNTDOWN ========== -->
    <section id="countdown" class="page-wrapper" style="padding-top: 60px; text-align: center;">
        <div id="countdown-container"></div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer style="text-align: center; font-family: 'Lato', sans-serif; font-size: 0.85rem; color: var(--color-text-muted); padding: 40px 0 24px;">
        Made with 💕 just for you
    </footer>

    <!-- ========== COUNTDOWN LOGIC ========== -->
    <script>
        const nextDate = <?= $nextDate ? json_encode($nextDate) : 'null' ?>;

        function updateCountdown() {
            const container = document.getElementById('countdown-container');

            if (!nextDate) {
                container.innerHTML = `
                    <div>
                        <p style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--color-accent); margin-bottom: 12px;">
                            No date set yet 🌸
                        </p>
                        <p style="font-family: 'Lato', sans-serif; font-size: 1rem; color: var(--color-text-muted); margin: 0;">
                            — go <a href="set-date.php" style="color: var(--color-primary); text-decoration: underline;">set one</a>!
                        </p>
                    </div>
                `;
                return;
            }

            const now = Math.floor(Date.now() / 1000);
            const timeLeft = nextDate.timestamp - now;

            if (timeLeft <= 0) {
                container.innerHTML = `
                    <div>
                        <p style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--color-primary); margin: 0;">
                            ✨ It's time! ✨
                        </p>
                    </div>
                `;
                return;
            }

            const days = Math.floor(timeLeft / (60 * 60 * 24));
            const hours = Math.floor((timeLeft % (60 * 60 * 24)) / (60 * 60));
            const minutes = Math.floor((timeLeft % (60 * 60)) / 60);
            const seconds = timeLeft % 60;

            container.innerHTML = `
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--color-accent); margin-bottom: 28px;">
                        Next: ${nextDate.label}
                    </h2>
                    <div style="display: flex; justify-content: center; gap: 16px; align-items: center; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-primary); font-weight: 700;">
                                ${days}
                            </div>
                            <div style="font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                                Days
                            </div>
                        </div>
                        <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--color-primary); opacity: 0.3;">:</div>
                        <div style="text-align: center;">
                            <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-primary); font-weight: 700;">
                                ${String(hours).padStart(2, '0')}
                            </div>
                            <div style="font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                                Hours
                            </div>
                        </div>
                        <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--color-primary); opacity: 0.3;">:</div>
                        <div style="text-align: center;">
                            <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-primary); font-weight: 700;">
                                ${String(minutes).padStart(2, '0')}
                            </div>
                            <div style="font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                                Minutes
                            </div>
                        </div>
                        <div style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: var(--color-primary); opacity: 0.3;">:</div>
                        <div style="text-align: center;">
                            <div style="font-family: 'Playfair Display', serif; font-size: 2.5rem; color: var(--color-primary); font-weight: 700;">
                                ${String(seconds).padStart(2, '0')}
                            </div>
                            <div style="font-family: 'Lato', sans-serif; font-size: 0.75rem; color: var(--color-text-muted); margin-top: 4px;">
                                Seconds
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Initial render
        updateCountdown();

        // Update every second
        setInterval(updateCountdown, 1000);
    </script>

    <script src="js/main.js"></script>
</body>
</html>
