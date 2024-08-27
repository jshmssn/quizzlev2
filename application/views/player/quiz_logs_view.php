<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #333;
        }

        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
        }

        .card-body {
            font-size: 14px;
            color: #6c757d;
        }

        .status.correct {
            color: #198754; /* Bootstrap success color */
        }

        .status.incorrect {
            color: #dc3545; /* Bootstrap danger color */
        }

        .btn-download {
            font-size: 16px;
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>

<div class="container position-relative">
    <h1>Quiz Logs</h1>

    <!-- Download Button Positioned in Upper Right -->
    <a href="#" class="btn btn-primary btn-download">Download as PDF</a>

    <div class="row">
        <?php if (!empty($quiz_logs)): ?>
            <?php foreach ($quiz_logs as $log): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        Participant: <?= htmlspecialchars($log['participant_name'] ?? 'N/A') ?>
                    </div>
                    <div class="card-body">
                        <p><strong>Question:</strong> <?= htmlspecialchars($log['question_text'] ?? 'N/A') ?></p>
                        <p><strong>Your Answer:</strong> <?= htmlspecialchars($log['answer_text'] ?? 'N/A') ?></p>
                        <p><strong>Correct Answer:</strong> <?= htmlspecialchars($log['correct_answer'] ?? 'N/A') ?></p>
                        <p class="status <?= isset($log['is_correct']) && $log['is_correct'] ? 'correct' : 'incorrect' ?>">
                            <strong>Status:</strong> <?= isset($log['is_correct']) ? ($log['is_correct'] ? 'Correct' : 'Incorrect') : 'N/A' ?>
                        </p>
                        <p><strong>Time Answered:</strong> <?= htmlspecialchars($log['response_time'] ?? 'N/A') ?> seconds</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        No logs available.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
