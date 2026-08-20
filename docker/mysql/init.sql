SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS app_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE app_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS disc_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    question_no TINYINT NOT NULL,
    answer_value TINYINT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_question (user_id, question_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_team_user (team_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ダミーデータ: デモ用チーム「ALPHA」と4名のメンバー
-- 全員パスワードは password123
INSERT INTO teams (id, name) VALUES (1, 'ALPHA');

INSERT INTO users (id, name, email, password_hash) VALUES
    (1, '山田太郎', 'yamada@example.com', '$2y$10$swAU.4SuUGbvwz8FrBVW0OrwxdtanOLmfer22GFhKirBzIXjKvWuO'),
    (2, '佐藤花子', 'sato@example.com', '$2y$10$swAU.4SuUGbvwz8FrBVW0OrwxdtanOLmfer22GFhKirBzIXjKvWuO'),
    (3, '田中一郎', 'tanaka@example.com', '$2y$10$swAU.4SuUGbvwz8FrBVW0OrwxdtanOLmfer22GFhKirBzIXjKvWuO'),
    (4, '高橋愛', 'takahashi@example.com', '$2y$10$swAU.4SuUGbvwz8FrBVW0OrwxdtanOLmfer22GFhKirBzIXjKvWuO');

INSERT INTO team_members (team_id, user_id) VALUES
    (1, 1), (1, 2), (1, 3), (1, 4);

-- 質問番号: 1=D,2=C,3=I,4=S,5=C,6=I/D,7=D,8=S,9=D/I,10=S/C
-- 山田太郎: Dが高め
INSERT INTO disc_answers (user_id, question_no, answer_value) VALUES
    (1, 1, 5), (1, 2, 2), (1, 3, 3), (1, 4, 2), (1, 5, 2),
    (1, 6, 4), (1, 7, 5), (1, 8, 1), (1, 9, 4), (1, 10, 2),
-- 佐藤花子: Iが高め
    (2, 1, 2), (2, 2, 2), (2, 3, 5), (2, 4, 3), (2, 5, 2),
    (2, 6, 4), (2, 7, 3), (2, 8, 2), (2, 9, 4), (2, 10, 2),
-- 田中一郎: Sが高め
    (3, 1, 2), (3, 2, 3), (3, 3, 2), (3, 4, 5), (3, 5, 3),
    (3, 6, 2), (3, 7, 2), (3, 8, 5), (3, 9, 2), (3, 10, 4),
-- 高橋愛: Cが高め
    (4, 1, 2), (4, 2, 5), (4, 3, 2), (4, 4, 3), (4, 5, 5),
    (4, 6, 2), (4, 7, 2), (4, 8, 3), (4, 9, 2), (4, 10, 4);
