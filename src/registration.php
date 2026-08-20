<?php

require_once __DIR__ . '/lib/disc.php';
require_once __DIR__ . '/dbconnect.php';

$errors = [];
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $answers = [];
    foreach (array_keys(DISC_QUESTIONS) as $questionNo) {
        $value = $_POST['q' . $questionNo] ?? null;
        if ($value === null || !ctype_digit((string) $value) || (int) $value < 1 || (int) $value > 5) {
            $errors[] = '質問' . $questionNo . 'への回答が不正です。';
            continue;
        }
        $answers[$questionNo] = (int) $value;
    }

    if ($old['name'] === '') {
        $errors[] = '名前を入力してください。';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = '有効なメールアドレスを入力してください。';
    }
    if ($password === '') {
        $errors[] = 'パスワードを入力してください。';
    }

    if (empty($errors)) {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'このメールアドレスは既に登録されています。';
        }
    }

    if (empty($errors)) {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$old['name'], $old['email'], password_hash($password, PASSWORD_DEFAULT)]);
        $userId = (int) $pdo->lastInsertId();

        $stmt = $pdo->prepare('INSERT INTO disc_answers (user_id, question_no, answer_value) VALUES (?, ?, ?)');
        foreach ($answers as $questionNo => $value) {
            $stmt->execute([$userId, $questionNo, $value]);
        }

        // デモ用チーム「ALPHA」(id=1)へ自動参加
        $stmt = $pdo->prepare('INSERT INTO team_members (team_id, user_id) VALUES (1, ?)');
        $stmt->execute([$userId]);

        $pdo->commit();

        header('Location: login.php?registered=1');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>新規登録 - Deep Diver</title>
<link rel="stylesheet" href="assets/styles/common.css">
</head>
<body class="page-registration">
<div class="phone-card">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form id="registration-form" method="post" novalidate>
        <div class="field">
            <input type="text" name="name" placeholder="名前" value="<?php echo htmlspecialchars($old['name'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="field">
            <input type="email" name="email" placeholder="メールアドレス" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="field">
            <input type="password" name="password" placeholder="パスワード">
        </div>

        <div class="quiz">
            <p class="quiz-progress-label">質問<span id="current-step">1</span>/10</p>
            <div class="progress-bar"><div class="progress-bar-fill" id="progress-fill"></div></div>

            <?php foreach (DISC_QUESTIONS as $questionNo => $meta): ?>
            <div class="quiz-step<?php echo $questionNo === 1 ? ' active' : ''; ?>" data-step="<?php echo $questionNo; ?>">
                <p class="quiz-question"><?php echo htmlspecialchars($meta['text'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="scale-buttons">
                    <?php for ($value = 1; $value <= 5; $value++): ?>
                    <label class="scale-button">
                        <input type="radio" name="q<?php echo $questionNo; ?>" value="<?php echo $value; ?>">
                        <span><?php echo $value; ?></span>
                    </label>
                    <?php endfor; ?>
                </div>
                <div class="scale-caption">
                    <span>当てはまらない</span>
                    <span>どちらでもない</span>
                    <span>当てはまる</span>
                </div>
            </div>
            <?php endforeach; ?>

            <button type="button" id="next-button" class="btn-primary">次へ</button>
        </div>
    </form>
</div>
<script src="assets/scripts/main.js"></script>
</body>
</html>
