<?php

require_once __DIR__ . '/dbconnect.php';

try {
    getDbConnection();
    $dbStatus = 'OK: データベースに接続できました';
} catch (PDOException $e) {
    $dbStatus = 'NG: データベース接続エラー - ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>環境構築確認</title>
</head>
<body>
<h1>PHP + MySQL 動作確認</h1>
<p><?php echo htmlspecialchars($dbStatus, ENT_QUOTES, 'UTF-8'); ?></p>
</body>
</html>
