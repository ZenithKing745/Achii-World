<?php
/**
 * Why I Love You - Flip card reasons page
 */

require_once 'php/db.php';

$reasons = [];
try {
    $stmt = $pdo->prepare('SELECT id, content FROM reasons ORDER BY id ASC');
    $stmt->execute();
    $reasons = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching reasons: ' . $e->getMessage());
}

$reasonsJson = json_encode($reasons, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Why I Love You - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .reasons-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }

        @media (max-width: 768px) {
            .reasons-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 480px) {
            .reasons-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .flip-card {
            perspective: 1000px;
            cursor: pointer;
            height: 220px;
        }

        .flip-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            transform-style: preserve-3d;
            transition: transform 0.65s ease;
        }

        .flip-card.flipped .flip-card-inner {
            transform: rotateY(180deg);
        }

        .flip-card-front,
        .flip-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 20px;
            text-align: center;
        }

        .flip-card-front {
            color: white;
        }

        .front-gradient-1 { background: linear-gradient(135deg, #FFD6E0, #FFAEC0); }
        .front-gradient-2 { background: linear-gradient(135deg, #FFE4B2, #FFCB77); }
        .front-gradient-3 { background: linear-gradient(135deg, #D4E8FF, #A8CAFF); }
        .front-gradient-4 { background: linear-gradient(135deg, #D8F5E4, #A8E6C0); }

        .card-number {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem;
            font-weight: 700;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
            margin: 0;
        }

        .card-sparkle {
            font-size: 1.2rem;
            margin-top: 6px;
        }

        .flip-card-back {
            transform: rotateY(180deg);
            background: white;
            border: 1.5px solid var(--color-card-border);
            color: var(--color-text);
        }

        .reason-text {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 0.95rem;
            line-height: 1.7;
            color: var(--color-text);
            padding: 0 8px;
        }

        .reason-heart { margin-top: 12px; font-size: 1.2rem; }

        .empty-state { text-align: center; padding: 60px 20px; color: var(--color-text-muted); }

    </style>
</head>
<body>
    <div class="page-wrapper">
        <a href="index.php" class="back-link">Back to Home</a>

        <h1 class="section-title">Why I Love You 💛</h1>
        <p class="section-sub">Click a card to find out 🌸</p>

        <div id="reasons-root">
            <!-- JS will render the grid here -->
        </div>
    </div>

    <script>const reasons = <?= $reasonsJson ?>;</script>
    <script>
        // Shuffle array (Fisher-Yates)
        function shuffle(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        const root = document.getElementById('reasons-root');

        if (!reasons || reasons.length === 0) {
            root.innerHTML = '<div class="empty-state">No reasons yet — check back soon 🌸</div>';
        } else {
            const shuffled = shuffle(reasons.slice());
            const grid = document.createElement('div');
            grid.className = 'reasons-grid';

            const gradients = ['front-gradient-1','front-gradient-2','front-gradient-3','front-gradient-4'];

            shuffled.forEach((reason, idx) => {
                const card = document.createElement('div');
                card.className = 'flip-card';
                card.setAttribute('data-id', reason.id);

                const inner = document.createElement('div');
                inner.className = 'flip-card-inner';

                const front = document.createElement('div');
                front.className = 'flip-card-front ' + gradients[idx % gradients.length];

                const number = document.createElement('div');
                number.className = 'card-number';
                const displayNumber = String(idx + 1).padStart(2, '0');
                number.textContent = displayNumber;

                const sparkle = document.createElement('div');
                sparkle.className = 'card-sparkle';
                sparkle.textContent = '✨';

                front.appendChild(number);
                front.appendChild(sparkle);

                const back = document.createElement('div');
                back.className = 'flip-card-back';

                const text = document.createElement('div');
                text.className = 'reason-text';
                text.textContent = reason.content;

                const heart = document.createElement('div');
                heart.className = 'reason-heart';
                heart.textContent = '💕';

                back.appendChild(text);
                back.appendChild(heart);

                inner.appendChild(front);
                inner.appendChild(back);
                card.appendChild(inner);

                // Toggle flip on click
                card.addEventListener('click', () => {
                    card.classList.toggle('flipped');
                });

                grid.appendChild(card);
            });

            root.appendChild(grid);
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
