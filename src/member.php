<?php

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/disc.php';
require_once __DIR__ . '/dbconnect.php';

requireLogin();
$pdo = getDbConnection();

$targetUserId = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
$stmt->execute([$targetUserId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT question_no, answer_value FROM disc_answers WHERE user_id = ?');
$stmt->execute([$targetUserId]);
$answers = [];
foreach ($stmt->fetchAll() as $row) {
    $answers[(int) $row['question_no']] = (int) $row['answer_value'];
}

$scores = calculateDiscScores($answers);
$radarData = json_encode(['labels' => ['D', 'I', 'S', 'C'], 'values' => array_values($scores)]);

$categories = [];
foreach (DISC_QUESTIONS as $questionNo => $meta) {
    $categories[$meta['category']][] = [
        'no' => $questionNo,
        'text' => $meta['text'],
        'value' => $answers[$questionNo] ?? null,
    ];
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> - Deep Diver</title>
<link rel="stylesheet" href="assets/styles/common.css">
</head>
<body class="page-member">
<div class="phone-card">
    <a href="index.php" class="back-link">&larr; 戻る</a>

    <div class="member-profile">
        <span class="avatar avatar-large"><?php echo htmlspecialchars(mb_substr($user['name'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
        <div class="member-profile-info">
            <p class="member-profile-name"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?> <span class="badge-online">ONLINE</span></p>
            <p class="member-profile-email"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
    </div>

    <section class="radar-section">
        <p class="radar-title"><?php echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?>の思考傾向 (DISC)</p>
        <canvas id="member-radar" data-radar='<?php echo htmlspecialchars($radarData, ENT_QUOTES, 'UTF-8'); ?>'></canvas>
    </section>

    <section class="answers-section">
        <p class="answers-title">回答内容</p>
        <?php foreach ($categories as $categoryName => $questions): ?>
        <div class="answer-category">
            <p class="answer-category-title"><?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php foreach ($questions as $question): ?>
            <div class="answer-item">
                <p class="answer-question"><?php echo htmlspecialchars($question['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="answer-value">回答: <?php echo $question['value'] !== null ? (int) $question['value'] : '未回答'; ?> / 5</p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </section>
</div>
<script src="assets/scripts/main.js"></script>
</body>
</html>
