<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

$article_id = $_POST["article_id"];
$comment_text = filter_var(trim($_POST["comment_text"]), FILTER_SANITIZE_STRING);
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


// Create Comment
db()->prepare("
    INSERT INTO comments
    (author_user_id, article_id, body)
    VALUES
    (:author_user_id, :article_id, :body)
    ")
    ->execute([
        "author_user_id" => $user_id, 
        "article_id" => $article_id, 
        "body" => $comment_text,
    ]);

// Go Back
if (isset($_POST["return_url"])) {
    header("Location: " . $_POST["return_url"], true, 302);
    exit();
}

header("Location: /article/" . $article["slug"], true, 302);
exit();
