<?php
require_once("_micro_micro.php");
require_once("_user.php");

$article_id = $_GET["id"];

// Check author
$stmt = db()->prepare("SELECT author_user_id FROM articles WHERE id=:id");
$stmt->execute(["id" => $article_id]);
if ($stmt->fetch()["author_user_id"] != user_current_id()) {
    header("Location: /", true, 302);
    exit();
}

// Delete
db()->prepare("DELETE FROM articles WHERE id=:id")
    ->execute(["id" => $article_id]);

header("Location: /", true, 302);
