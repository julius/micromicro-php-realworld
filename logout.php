<?php
require_once("_micro_micro.php");
require_once("_user.php");

if (!user_logged_in()) {
    header("Location: /", true, 302);
    exit();
}

db()->prepare("DELETE FROM sessions WHERE id=:id")
    ->execute(["id" => session_id()]);

header("Location: /", true, 302);
