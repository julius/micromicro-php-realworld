<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

$user = user_current();
if (!$user) {
    header("Location: /", true, 302);
    exit();
}

$form = [
    "email" => $user["email"],
    "name" => $user["name"],
    "bio" => $user["bio"],
    "image_url" => $user["image_url"],
];
$error_messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_POST();
function handle_POST()
{
    global $error_messages, $form, $user;
    $form = [
        "email" => filter_var(trim($_POST["email"]), FILTER_SANITIZE_STRING),
        "name" => filter_var(trim($_POST["name"]), FILTER_SANITIZE_STRING),
        "bio" => filter_var(trim($_POST["bio"]), FILTER_SANITIZE_STRING),
        "image_url" => filter_var(trim($_POST["image_url"]), FILTER_SANITIZE_URL),
    ];

    // Valid Email?
    if (!filter_var($form["email"], FILTER_VALIDATE_EMAIL)) {
        $error_messages[] = "Email address not valid. Please check.";
        return;
    }

    // Email exists?
    $stmt = db()->prepare("SELECT * FROM users WHERE email=:email AND id<>:id");
    $stmt->execute(["email" => $form["email"], "id" => $user["id"]]);
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
    $stmt = db()->prepare("SELECT * FROM users WHERE slug=:slug AND id<>:id");
    $stmt->execute(["slug" => user_slug_from_name($form["name"]), "id" => $user["id"]]);
    if ($stmt->fetch()) {
        $error_messages[] = "That username is already taken.";
        return;
    }

    // Update Password
    if (strlen($_POST["password"]) > 0) {
        db()->prepare("UPDATE users SET password=:password")
            ->execute(["password" => password_hash($_POST["password"], PASSWORD_DEFAULT)]);
    }

    // Update User
    db()->prepare("
        UPDATE users 
        SET email=:email, slug=:slug, name=:name, image_url=:image_url, bio=:bio
        WHERE id=:id
        ")
        ->execute([
            "id" => $user["id"],
            "email" => $form["email"],
            "slug" => user_slug_from_name($form["name"]),
            "name" => $form["name"],
            "bio" => $form["bio"],
            "image_url" => $form["image_url"],
        ]);


    // Redirect to profile
    header("Location: /@" . user_slug_from_name($form["name"]), true, 302);
    exit();
}

?>

<div class="settings-page">
    <div class="container page">
        <div class="row">

            <div class="col-md-6 offset-md-3 col-xs-12">
                <h1 class="text-xs-center">Your Settings</h1>

                <?php if (count($error_messages) > 0) : ?>
                    <ul class="error-messages">
                        <?php foreach ($error_messages as $message) : ?>
                            <li><?= $message ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <form action="" method="POST">
                    <fieldset>
                        <fieldset class="form-group">
                            <input name="image_url" value="<?= $form["image_url"] ?>" class="form-control form-control-lg" type="text" placeholder="URL of profile picture">
                        </fieldset>
                        <fieldset class="form-group">
                            <input name="name" value="<?= $form["name"] ?>" class="form-control form-control-lg" type="text" placeholder="Your Name">
                        </fieldset>
                        <fieldset class="form-group">
                            <textarea name="bio" class="form-control form-control-lg" rows="8" placeholder="Short bio about you"><?= $form["bio"] ?></textarea>
                        </fieldset>
                        <fieldset class="form-group">
                            <input name="email" value="<?= $form["email"] ?>" class="form-control form-control-lg" type="email" placeholder="Email">
                        </fieldset>
                        <fieldset class="form-group">
                            <input name="password" class="form-control form-control-lg" type="password" placeholder="Password">
                        </fieldset>
                        <button class="btn btn-lg btn-primary pull-xs-right">
                            Update Settings
                        </button>
                    </fieldset>
                </form>

                <hr>
                <a href="/logout" class="btn btn-outline-danger">
                    Or click here to logout.
                </a>
            </div>

        </div>
    </div>
</div>

<?php render_into_layout("layout.php"); ?>