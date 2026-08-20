<?php

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/dbconnect.php';

if (currentUserId() !== null) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = ['email' => ''];
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($old['email'] === '' || $password === '') {
        $errors[] = 'メールアドレスとパスワードを入力してください。';
    } else {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'メールアドレスまたはパスワードが正しくありません。';
        } else {
            loginAs((int) $user['id']);
            header('Location: index.php');
            exit;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ログイン - Deep Diver</title>
<link rel="stylesheet" href="assets/styles/common.css">
</head>
<body class="page-login">
<div class="phone-card">
    <div class="brand-header">
        <img src="assets/img/logo.svg" alt="Deep Diver" class="brand-logo">
        <h1>Deep Diver</h1>
    </div>

    <?php if ($registered && empty($errors)): ?>
    <div class="alert alert-success"><p>登録が完了しました。ログインしてください。</p></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $error): ?>
        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="field">
            <input type="email" name="email" placeholder="メールアドレス" value="<?php echo htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="field">
            <input type="password" name="password" placeholder="パスワード">
        </div>
        <button type="submit" class="btn-primary">ログイン</button>
    </form>
    <a href="registration.php" class="link-secondary">新規登録</a>
</div>
</body>
</html>
