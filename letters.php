<?php
/**
 * All My Letters - Love letters collection
 */

require_once 'php/db.php';

// Fetch all letters ordered by created_at DESC
$letters = [];
try {
    $stmt = $pdo->prepare('
        SELECT id, title, content, created_at 
        FROM letters 
        ORDER BY created_at DESC
    ');
    $stmt->execute();
    $letters = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching letters: ' . $e->getMessage());
}

// JSON encode for JavaScript
$lettersJson = json_encode($letters);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All My Letters - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
            /* Envelope Container */
        .envelopes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            perspective: 1000px;
        }

        /* Envelope Component */
        .envelope {
            position: relative;
            height: 220px;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .envelope.hover-enabled:hover {
            transform: translateY(-8px);
        }

        .envelope.hover-enabled:hover .envelope-body {
            box-shadow: 0 12px 32px rgba(232,114,138,0.22);
        }

        .envelope.hover-enabled:hover .envelope-flap::before {
            transform: rotateX(-15deg);
        }

        .envelope-body {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 0 0 12px 12px;
            border: 2px solid rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
            transition: box-shadow 0.25s ease;
            box-shadow: 0 4px 16px rgba(232, 114, 138, 0.1);
        }

        .envelope-body.color-1 { background-color: #FFE8EF; }
        .envelope-body.color-2 { background-color: #FFF0E0; }
        .envelope-body.color-3 { background-color: #E8F0FF; }

        /* Envelope Flap */
        .envelope-flap {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 120px;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }

        .envelope-flap::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 120px;
            left: 0;
            top: 0;
            border-left: 50% solid transparent;
            border-right: 50% solid transparent;
            border-top: 120px solid currentColor;
            opacity: 0.2;
            transition: transform 0.25s ease;
            transform-origin: top center;
            transform-style: preserve-3d;
        }

        .envelope-flap.color-1::before { color: #FFB3D9; }
        .envelope-flap.color-2::before { color: #FFD4A3; }
        .envelope-flap.color-3::before { color: #B3D9FF; }

        .envelope-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.15rem;
            font-style: italic;
            color: var(--color-accent);
            margin: 0;
            margin-bottom: 8px;
        }

        .envelope-seal { font-size: 1.8rem; }

        .envelope-tooltip {
            position: absolute;
            bottom: -28px;
            left: 50%;
            transform: translateX(-50%);
            font-family: 'Lato', sans-serif;
            font-size: 12px;
            color: var(--color-text-muted);
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }

        .envelope.hover-enabled:hover .envelope-tooltip {
            opacity: 1;
        }

        /* Modal */
        .letter-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 6000;
            background: rgba(61, 43, 53, 0.6);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .letter-modal-backdrop.open {
            opacity: 1;
            pointer-events: all;
        }

        .letter-modal-paper {
            background: #FFFAF8;
            border-radius: 20px;
            padding: 48px 52px;
            max-width: 600px;
            width: 100%;
            max-height: 82vh;
            overflow-y: auto;
            position: relative;
            transform: translateY(24px);
            transition: transform 0.35s ease;
            background-image: repeating-linear-gradient(
                transparent,
                transparent 31px,
                #FFE8EF 31px,
                #FFE8EF 32px
            );
            background-attachment: local;
        }

        .letter-modal-backdrop.open .letter-modal-paper { transform: translateY(0); }

        #letter-modal-close {
            position: absolute;
            top: 20px;
            right: 24px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--color-text-muted);
            line-height: 1;
            padding: 4px;
            transition: color 0.2s;
        }

        #letter-modal-close:hover { color: var(--color-accent); }

        .letter-modal-label {
            display: block;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--color-text-muted);
            margin-bottom: 10px;
        }

        #modal-letter-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--color-accent);
            margin-bottom: 24px;
            line-height: 1.3;
        }

        #modal-letter-body {
            font-family: 'Lato', sans-serif;
            font-size: 1rem;
            line-height: 2;
            color: var(--color-text);
            white-space: pre-wrap;
        }

        .letter-modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid var(--color-card-border);
        }

        #modal-letter-count {
            font-size: 13px;
            color: var(--color-text-muted);
        }

        @media (max-width: 480px) {
            .letter-modal-paper { padding: 32px 24px; }
            #modal-letter-title { font-size: 1.3rem; }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .envelopes-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .envelopes-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .envelope {
                height: 240px;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--color-text-muted);
        }

        .empty-state p {
            font-family: 'Lato', sans-serif;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Back Link -->
        <a href="index.php" class="back-link">Back to Home</a>

        <!-- Heading -->
        <h1 class="section-title">All My Letters 💌</h1>
        <p class="section-sub">Each one was written with you in mind.</p>

        <!-- Envelopes Grid -->
        <div id="envelopes-container" class="envelopes-grid"></div>
    </div>

    <!-- Modal Overlay -->
    <div id="letter-modal" class="letter-modal-backdrop" aria-hidden="true">
        <div class="letter-modal-paper" role="dialog" aria-modal="true" aria-labelledby="modal-letter-title">
            <button id="letter-modal-close" aria-label="Close letter">✕</button>
            <span class="letter-modal-label">love letter</span>
            <h2 id="modal-letter-title"></h2>
            <div id="modal-letter-body"></div>
            <div class="letter-modal-footer">
                <div id="modal-letter-count"></div>
                <div class="letter-modal-hint">Tap outside the paper or press Escape to close</div>
            </div>
        </div>
    </div>

    <!-- Letters Data & Rendering Script -->
    <script>
        const letters = <?= $lettersJson ?>;
        const colors = ['color-1', 'color-2', 'color-3'];
        const modal = document.getElementById('letter-modal');
        const modalTitle = document.getElementById('modal-letter-title');
        const modalBody = document.getElementById('modal-letter-body');
        const modalCount = document.getElementById('modal-letter-count');
        const modalClose = document.getElementById('letter-modal-close');

        function renderLetters() {
            const container = document.getElementById('envelopes-container');

            if (letters.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1;">
                        <div class="empty-state">
                            <p>No letters yet... but there will be. 🌸</p>
                        </div>
                    </div>
                `;
                return;
            }

            container.innerHTML = letters.map((letter, index) => {
                const colorClass = colors[index % colors.length];
                return `
                    <div class="envelope hover-enabled" data-id="${letter.id}" data-index="${index + 1}">
                        <div class="envelope-flap ${colorClass}"></div>
                        <div class="envelope-body ${colorClass}">
                            <div class="envelope-front">
                                <h3 class="envelope-title">${letter.title}</h3>
                                <div class="envelope-seal">💌</div>
                            </div>
                            <span class="envelope-tooltip">Tap to open</span>
                        </div>
                    </div>
                `;
            }).join('');

            attachEnvelopeListeners();
        }

        function openModal(letter, index) {
            modalTitle.textContent = letter.title;
            modalBody.textContent = letter.content;
            modalCount.textContent = `Letter ${index} of ${letters.length}`;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function attachEnvelopeListeners() {
            document.querySelectorAll('.envelope').forEach(envelope => {
                envelope.addEventListener('click', () => {
                    const letterId = parseInt(envelope.dataset.id, 10);
                    const index = parseInt(envelope.dataset.index, 10);
                    const letter = letters.find(l => l.id === letterId);
                    if (letter) {
                        openModal(letter, index);
                    }
                });
            });
        }

        modalClose.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('open')) {
                closeModal();
            }
        });

        renderLetters();
    </script>
    <script src="js/main.js"></script>
</body>
</html>
