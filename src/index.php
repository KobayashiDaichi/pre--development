<?php

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/disc.php';
require_once __DIR__ . '/dbconnect.php';

$userId = requireLogin();
$pdo = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_team') {
    $teamName = trim($_POST['team_name'] ?? '');
    if ($teamName !== '') {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO teams (name) VALUES (?)');
        $stmt->execute([$teamName]);
        $newTeamId = (int) $pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO team_members (team_id, user_id) VALUES (?, ?)');
        $stmt->execute([$newTeamId, $userId]);
        $pdo->commit();
        $_SESSION['current_team_id'] = $newTeamId;
    }
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT t.id, t.name FROM teams t
     INNER JOIN team_members tm ON tm.team_id = t.id
     WHERE tm.user_id = ?
     ORDER BY t.id'
);
$stmt->execute([$userId]);
$myTeams = $stmt->fetchAll();

if (isset($_GET['team_id'])) {
    $requestedTeamId = (int) $_GET['team_id'];
    foreach ($myTeams as $team) {
        if ((int) $team['id'] === $requestedTeamId) {
            $_SESSION['current_team_id'] = $requestedTeamId;
            break;
        }
    }
    header('Location: index.php');
    exit;
}

$currentTeam = null;
if (!empty($myTeams)) {
    $currentTeamId = $_SESSION['current_team_id'] ?? null;
    foreach ($myTeams as $team) {
        if ((int) $team['id'] === (int) $currentTeamId) {
            $currentTeam = $team;
            break;
        }
    }
    if ($currentTeam === null) {
        $currentTeam = $myTeams[0];
        $_SESSION['current_team_id'] = (int) $currentTeam['id'];
    }
}

$members = [];
$teamAverage = ['D' => 0, 'I' => 0, 'S' => 0, 'C' => 0];

if ($currentTeam !== null) {
    $stmt = $pdo->prepare(
        'SELECT u.id, u.name FROM users u
         INNER JOIN team_members tm ON tm.user_id = u.id
         WHERE tm.team_id = ?
         ORDER BY u.id'
    );
    $stmt->execute([$currentTeam['id']]);
    $memberRows = $stmt->fetchAll();

    $answerStmt = $pdo->prepare('SELECT question_no, answer_value FROM disc_answers WHERE user_id = ?');
    $memberScoresList = [];

    foreach ($memberRows as $memberRow) {
        $answerStmt->execute([$memberRow['id']]);
        $answers = [];
        foreach ($answerStmt->fetchAll() as $row) {
            $answers[(int) $row['question_no']] = (int) $row['answer_value'];
        }
        $scores = calculateDiscScores($answers);
        $members[] = ['id' => $memberRow['id'], 'name' => $memberRow['name'], 'scores' => $scores];
        $memberScoresList[] = $scores;
    }

    $teamAverage = calculateTeamAverageScores($memberScoresList);
}

$radarData = json_encode(['labels' => ['D', 'I', 'S', 'C'], 'values' => array_values($teamAverage)]);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ホーム - Deep Diver</title>
<link rel="stylesheet" href="assets/styles/common.css">
</head>
<body class="page-home">
<div class="phone-card">
    <header class="home-header">
        <button type="button" id="open-team-modal" class="team-switcher">
            <?php if ($currentTeam !== null): ?>
            プロジェクト「<?php echo htmlspecialchars($currentTeam['name'], ENT_QUOTES, 'UTF-8'); ?>」
            <?php else: ?>
            チーム未所属
            <?php endif; ?>
        </button>
        <div class="home-header-icons">
            <span class="icon-bell" aria-hidden="true">&#128276;</span>
            <a href="logout.php" class="icon-logout" title="ログアウト">&#9211;</a>
        </div>
    </header>
    <div class="search-box">
        <input type="text" placeholder="検索" disabled>
    </div>

    <?php if ($currentTeam !== null): ?>
    <section class="radar-section">
        <p class="radar-title">チーム平均の思考傾向 (TEAM DISC)</p>
        <canvas id="team-radar" data-radar='<?php echo htmlspecialchars($radarData, ENT_QUOTES, 'UTF-8'); ?>'></canvas>
    </section>

    <section class="members-section">
        <p class="members-title">チームメンバー</p>
        <div class="members-grid">
            <?php foreach ($members as $member): ?>
            <a href="member.php?id=<?php echo (int) $member['id']; ?>" class="member-card">
                <span class="avatar"><?php echo htmlspecialchars(mb_substr($member['name'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="member-name"><?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?></span>
            </a>
            <?php endforeach; ?>
            <button type="button" id="invite-member" class="member-card member-card-add">
                <span class="avatar avatar-add">+</span>
                <span class="member-name">メンバー招待</span>
            </button>
        </div>
    </section>
    <?php else: ?>
    <p class="empty-state">所属しているチームがありません。チームを作成してください。</p>
    <?php endif; ?>
</div>

<div id="team-modal" class="modal-overlay">
    <div class="modal-box">
        <h2>チームを切り替え</h2>
        <ul class="team-list">
            <?php foreach ($myTeams as $team): ?>
            <li>
                <a href="index.php?team_id=<?php echo (int) $team['id']; ?>">
                    <?php echo htmlspecialchars($team['name'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <form method="post" class="create-team-form">
            <input type="hidden" name="action" value="create_team">
            <input type="text" name="team_name" placeholder="新しいチーム名" required>
            <button type="submit" class="btn-primary">チームを作成</button>
        </form>
        <button type="button" id="close-team-modal" class="link-secondary">閉じる</button>
    </div>
</div>

<script src="assets/scripts/main.js"></script>
</body>
</html>
