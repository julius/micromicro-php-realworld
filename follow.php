<?php
require_once("_micro_micro.php");
require_once("_user.php");

$user_target_id = $_GET["user"];
$user_current_id = user_current_id();

// User logged in?
if (!$user_current_id) {
    header("Location: /register", true, 302);
    exit();
}

// Target user exists
$stmt = db()->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute(["id" => $user_target_id]);
$user_target = $stmt->fetch();
if (!$user_target) {
    header("Location: /", true, 302);
    exit();
}

// Already following?
$stmt = db()->prepare("SELECT COUNT(1) as follow_count FROM follows WHERE user_id=:user_id AND user_target_id=:user_target_id");
$stmt->execute(["user_id" => $user_current_id, "user_target_id" => $user_target_id]);
$followed_by_me = $stmt->fetch()["follow_count"] > 0;

// Follow / UnFollow
if ($followed_by_me) {
    db()->prepare("
        DELETE FROM follows
        WHERE user_id=:user_id AND user_target_id=:user_target_id
        ")
        ->execute(["user_id" => $user_current_id, "user_target_id" => $user_target_id]);
} else {
    db()->prepare("
        REPLACE INTO follows (user_id, user_target_id)
        VALUES (:user_id, :user_target_id)
        ")
        ->execute(["user_id" => $user_current_id, "user_target_id" => $user_target_id]);
}

// Go Back
if (isset($_GET["return_url"])) {
    header("Location: " . $_GET["return_url"], true, 302);
    exit();
}

header("Location: /@" . $user_target["slug"], true, 302);
exit();
