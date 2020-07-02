<?php
require_once("_micro_micro.php");
require_once("_user.php");

$article_id = $_GET["id"];
$user_id = user_current_id();

// User logged in?
if (!$user_id) {
    header("Location: /register", true, 302);
    exit();
}

// Article exists
$stmt = db()->prepare("SELECT * FROM articles WHERE id=:id");
$stmt->execute(["id" => $article_id]);
$article = $stmt->fetch();
if (!$article) {
    header("Location: /", true, 302);
    exit();
}

// Already favorited?
$stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id AND user_id=:user_id");
$stmt->execute(["article_id" => $article_id, "user_id" => $user_id]);
$favorited_by_me = $stmt->fetch()["favorite_count"] > 0;

// Favorite / Unfavorite
if ($favorited_by_me) {
    db()->prepare("
        DELETE FROM favorites
        WHERE user_id=:user_id AND article_id=:article_id
        ")
        ->execute([
            "user_id" => $user_id,
            "article_id" => $article_id,
        ]);
} else {
    db()->prepare("
        REPLACE INTO favorites (user_id, article_id)
        VALUES (:user_id, :article_id)
        ")
        ->execute([
            "user_id" => $user_id,
            "article_id" => $article_id,
        ]);
}

// Go Back
if (isset($_GET["return_url"])) {
    header("Location: " . $_GET["return_url"], true, 302);
    exit();
}

header("Location: /article/" . $article["slug"], true, 302);
exit();
