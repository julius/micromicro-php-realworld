<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

$error_messages = [];
$form = ["email" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_POST();
function handle_POST()
{
    global $error_messages, $form;
    $form = [
        "email" => filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING),
    ];

    // Valid Email?
    if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $error_messages[] = "Email address not valid. Please check.";
        return;
    }

    // Password valid?
    if (strlen($_POST["password"]) == 0) {
        $error_messages[] = "Please enter a password.";
        return;
    }

    // User Exists?
    $stmt = db()->prepare("SELECT * FROM users WHERE email=:email");
    $stmt->execute(["email" => $form["email"]]);
    if (!$user = $stmt->fetch()) {
        $error_messages[] = "Email/Password not valid.";
        return;
    }

    // Right password?
    if (!password_verify($_POST["password"], $user["password"])) {
        $error_messages[] = "Email/Password not valid.";
        return;
    }

    // Create and activate session
    if (session_status() == PHP_SESSION_NONE) session_start();
    session_regenerate_id();
    db()->prepare("INSERT INTO sessions (id, user_id) VALUES (:id, :user_id)")
        ->execute(["id" => session_id(), "user_id" => $user["id"]]);

    // Redirect to profile
    header("Location: /@" . $user["slug"], true, 302);
    exit();
}
?>

<div class="auth-page">
    <div class="container page">
        <div class="row">

            <div class="col-md-6 offset-md-3 col-xs-12">
                <h1 class="text-xs-center">Sign in</h1>
                <p class="text-xs-center">
                    <a href="/register">Need an account?</a>
                </p>

                <?php if (count($error_messages) > 0) : ?>
                    <ul class="error-messages">
                        <?php foreach ($error_messages as $message) : ?>
                            <li><?= $message ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <form action="" method="POST">
                    <fieldset class="form-group">
                        <input name="email" value="<?= $form["email"] ?>" class="form-control form-control-lg" type="email" placeholder="Email">
                    </fieldset>
                    <fieldset class="form-group">
                        <input name="password" class="form-control form-control-lg" type="password" placeholder="Password">
                    </fieldset>
                    <input type="submit" value="Sign in" class="btn btn-lg btn-primary pull-xs-right">
                </form>
            </div>

        </div>
    </div>
</div>

<?php render_into_layout("layout.php"); ?>