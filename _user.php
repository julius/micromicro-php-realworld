<?php
require_once("_micro_micro.php");

if (session_status() == PHP_SESSION_NONE) session_start();

function user_logged_in()
{
    $user_id = user_current_id();
    return !!$user_id;
}

function user_current_id()
{
    $stmt = db()->prepare("SELECT user_id FROM sessions WHERE id=:id");
    $stmt->execute(["id" => session_id()]);
    $session = $stmt->fetch();

    if (!$session) return false;

    return $session["user_id"];
}

function user_current()
{
    $user_id = user_current_id();
    if (!$user_id) return false;

    return user_get($user_id);
}

function user_get($user_id)
{
    $stmt = db()->prepare("SELECT * FROM users WHERE id=:id");
    $stmt->execute(["id" => $user_id]);
    return $stmt->fetch();
}

function user_slug_from_name($user_name)
{
    $user_name = preg_replace('~[^\pL\d]+~u', '-', $user_name);
    $user_name = iconv('utf-8', 'us-ascii//TRANSLIT', $user_name);
    $user_name = preg_replace('~[^-\w\s]+~', '', $user_name);
    $user_name = preg_replace('~-+~', '-', $user_name);
    return $user_name;
}
