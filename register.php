<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

$error_messages = [];
$form = ["email" => "", "name" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_POST();
function handle_POST()
{
    global $error_messages, $form;
    $form = [
        "email" => filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING),
        "name" => filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING),
    ];

    // Valid Email?
    if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $error_messages[] = "Email address not valid. Please check.";
        return;
    }

    // User exists?
    $stmt = db()->prepare("SELECT * FROM users WHERE email=:email");
    $stmt->execute(["email" => $form["email"]]);
    if ($stmt->fetch()) {
        $error_messages[] = "That email is already taken.";
        return;
    }

    // Name valid?
    if (strlen($form["name"]) == 0) {
        $error_messages[] = "Please enter a name.";
        return;
    }

    // Name taken?
    $stmt = db()->prepare("SELECT * FROM users WHERE slug=:slug");
    $stmt->execute(["slug" => user_slug_from_name($form["name"])]);
    if ($stmt->fetch()) {
        $error_messages[] = "That username is already taken.";
        return;
    }

    // Password valid?
    if (strlen($_POST["password"]) == 0) {
        $error_messages[] = "Please enter a password.";
        return;
    }

    // Create user
    db()->prepare("
        INSERT INTO users (email, slug, name, password, image_url) 
        VALUES (:email, :slug, :name, :password, :image_url)
        ")
        ->execute([
            "email" => $form["email"],
            "slug" => user_slug_from_name($form["name"]),
            "name" => $form["name"],
            "password" => password_hash($_POST["password"], PASSWORD_DEFAULT),
            "image_url" => "http://www.gravatar.com/avatar/" . md5(strtolower($form["email"])) . "?d=mp",
        ]);
    $user_id = db()->lastInsertId();

    // Create and activate session
    if (session_status() == PHP_SESSION_NONE) session_start();
    session_regenerate_id();
    db()->prepare("INSERT INTO sessions (id, user_id) VALUES (:id, :user_id)")
        ->execute(["id" => session_id(), "user_id" => $user_id]);

    // Redirect to profile
    header("Location: /@" . user_slug_from_name($form["name"]), true, 302);
    exit();
}

?>

<div class="auth-page">
    <div class="container page">
        <div class="row">

            <div class="col-md-6 offset-md-3 col-xs-12">
                <h1 class="text-xs-center">Sign up</h1>
                <p class="text-xs-center">
                    <a href="/login">Have an account?</a>
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
                        <input class="form-control form-control-lg" name="name" value="<?= $form['name'] ?>" type="text" placeholder="Your Name">
                    </fieldset>
                    <fieldset class="form-group">
                        <input class="form-control form-control-lg" name="email" value="<?= $form['email'] ?>" type="email" placeholder="Email">
                    </fieldset>
                    <fieldset class="form-group">
                        <input class="form-control form-control-lg" name="password" type="password" placeholder="Password">
                    </fieldset>
                    <input type="submit" value="Sign up" class="btn btn-lg btn-primary pull-xs-right">
                </form>
            </div>

        </div>
    </div>
</div>

<?php render_into_layout("layout.php"); ?>