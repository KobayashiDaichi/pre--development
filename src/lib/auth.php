<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

function requireLogin(): int
{
    $id = currentUserId();
    if ($id === null) {
        header('Location: login.php');
        exit;
    }
    return $id;
}

function loginAs(int $userId): void
{
    $_SESSION['user_id'] = $userId;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
