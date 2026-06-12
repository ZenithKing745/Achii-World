<?php
/**
 * Set a Date - Plan upcoming dates
 */

require_once 'php/db.php';

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_value = $_POST['date'] ?? null;
    $time_value = $_POST['time'] ?? null;
    $label = $_POST['label'] ?? null;
    $note = $_POST['note'] ?? null;

    // Basic validation
    if ($date_value) {
        try {
            $stmt = $pdo->prepare('
                INSERT INTO dates (date_value, time_value, label, note)
                VALUES (?, ?, ?, ?)
            ');
            $stmt->execute([$date_value, $time_value, $label, $note]);
            
            // Post/Redirect/Get
            header('Location: set-date.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Could not save date. Please try again.';
        }
    }
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM dates WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: set-date.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Could not delete date.';
    }
}

// Fetch upcoming dates
$upcomingDates = [];
try {
    $stmt = $pdo->prepare('
        SELECT * FROM dates 
        WHERE date_value >= CURDATE() 
        ORDER BY date_value ASC
    ');
    $stmt->execute();
    $upcomingDates = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Error fetching dates: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set a Date - Achii's World</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            background: var(--color-card);
            border-color: var(--color-card-border);
            box-shadow: var(--shadow);
        }
        .flatpickr-day.selected,
        .flatpickr-day.startRange,
        .flatpickr-day.endRange {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
        }
        .flatpickr-day:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        .flatpickr-time input {
            border-color: var(--color-card-border);
        }
        input[type="text"]:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(232, 114, 138, 0.1);
        }
        textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(232, 114, 138, 0.1);
        }
        .delete-btn {
            background: transparent;
            border: none;
            color: var(--color-text-muted);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 6px 10px;
            transition: color 0.2s ease;
        }
        .delete-btn:hover {
            color: var(--color-accent);
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Back Link -->
        <a href="index.php" class="back-link">Back to Home</a>

        <!-- Heading -->
        <h1 class="section-title">Set a Date 📅</h1>
        <p class="section-sub">Plan something beautiful.</p>

        <?php if (isset($error)): ?>
            <div style="background-color: #FFE0E6; border-left: 4px solid var(--color-accent); padding: 12px 16px; margin-bottom: 20px; border-radius: 6px; color: var(--color-accent);">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="card" style="margin-bottom: 40px;">
            <form method="POST">
                <!-- Date Input -->
                <div style="margin-bottom: 16px;">
                    <label for="datePicker" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Date *
                    </label>
                    <input 
                        type="text" 
                        id="datePicker" 
                        name="date" 
                        placeholder="Select a date"
                        required
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px;"
                    >
                </div>

                <!-- Time Input -->
                <div style="margin-bottom: 16px;">
                    <label for="timePicker" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Time (optional)
                    </label>
                    <input 
                        type="text" 
                        id="timePicker" 
                        name="time" 
                        placeholder="Select a time"
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px;"
                    >
                </div>

                <!-- Label Input -->
                <div style="margin-bottom: 16px;">
                    <label for="labelInput" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        What are we doing?
                    </label>
                    <input 
                        type="text" 
                        id="labelInput" 
                        name="label" 
                        placeholder="e.g., Movie night 🎬"
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px;"
                    >
                </div>

                <!-- Note Textarea -->
                <div style="margin-bottom: 20px;">
                    <label for="noteInput" style="display: block; font-family: 'Lato', sans-serif; font-size: 13px; color: var(--color-text-muted); margin-bottom: 6px; font-weight: 400;">
                        Any details?
                    </label>
                    <textarea 
                        id="noteInput" 
                        name="note" 
                        placeholder="Optional notes..."
                        rows="3"
                        style="width: 100%; padding: 12px 16px; border: 1.5px solid var(--color-card-border); border-radius: 10px; font-family: 'Lato', sans-serif; font-size: 15px; resize: vertical;"
                    ></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn" style="width: 100%;">
                    Save Date 💕
                </button>
            </form>
        </div>

        <!-- Upcoming Dates Section -->
        <div>
            <h2 class="section-title" style="font-size: 1.4rem;">Upcoming Dates 🗓️</h2>

            <?php if (empty($upcomingDates)): ?>
                <p style="text-align: center; color: var(--color-text-muted); font-family: 'Lato', sans-serif; padding: 40px 0;">
                    Nothing planned yet — add one above 🌸
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($upcomingDates as $row): ?>
                        <?php
                            $dateObj = new DateTime($row['date_value']);
                            $dateFormatted = $dateObj->format('l, F j');
                            $timeStr = $row['time_value'] ? date('g:i A', strtotime($row['time_value'])) : '';
                            $dateTimeDisplay = $timeStr ? "$dateFormatted · $timeStr" : $dateFormatted;
                        ?>
                        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <p style="font-family: 'Lato', sans-serif; font-size: 14px; color: var(--color-text-muted); margin: 0; margin-bottom: 6px;">
                                    <?= htmlspecialchars($dateTimeDisplay) ?>
                                </p>
                                <h3 style="font-family: 'Playfair Display', serif; font-size: 1rem; color: var(--color-accent); margin: 0; margin-bottom: 4px;">
                                    <?= htmlspecialchars($row['label'] ?? 'Special Date') ?>
                                </h3>
                                <?php if ($row['note']): ?>
                                    <p style="font-family: 'Lato', sans-serif; font-size: 0.85rem; color: var(--color-text-muted); margin: 0;">
                                        <?= htmlspecialchars($row['note']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <a 
                                href="set-date.php?delete=<?= $row['id'] ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this date?');"
                            >
                                ✕
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flatpickr CDN -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="js/main.js"></script>
    <script>
        // Date Picker
        flatpickr('#datePicker', {
            minDate: 'today',
            dateFormat: 'Y-m-d'
        });

        // Time Picker
        flatpickr('#timePicker', {
            enableTime: true,
            noCalendar: true,
            time_24hr: false,
            dateFormat: 'h:i K'
        });
    </script>
</body>
</html>
