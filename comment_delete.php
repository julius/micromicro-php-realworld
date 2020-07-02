<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

$comment_id = $_GET["comment_id"];
$user_id = user_current_id();

// User logged in?
if (!$user_id) {
    header("Location: /register", true, 302);
    exit();
}

// Delete comment
db()->prepare("
    DELETE FROM comments
    WHERE author_user_id=:author_user_id AND id=:comment_id
    ")
    ->execute([
        "author_user_id" => $user_id,
        "comment_id" => $comment_id,
    ]);

// Go Back
if (isset($_GET["return_url"])) {
    header("Location: " . $_GET["return_url"], true, 302);
    exit();
}

header("Location: /", true, 302);
exit();
