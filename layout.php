<?php

require_once("_user.php");
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined("LAYOUT_TITLE") ? LAYOUT_TITLE : 'Conduit' ?></title>

    <link rel="stylesheet" href="//demo.productionready.io/main.css" />
    <link href="//code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css" />
    <link href="//fonts.googleapis.com/css?family=Titillium+Web:700|Source+Serif+Pro:400,700|Merriweather+Sans:400,700|Source+Sans+Pro:400,300,600,700,300italic,400italic,600italic,700italic" rel="stylesheet" type="text/css" />
</head>

<body>
    <nav class="navbar navbar-light">
        <div class="container">
            <a class="navbar-brand" href="/">conduit</a>
            <ul class="nav navbar-nav pull-xs-right">
                <li class="nav-item">
                    <!-- Add "active" class when you're on that page" -->
                    <a class="nav-link <?= $path == '/' ? 'active' : '' ?>" href="/">Home</a>
                </li>

                <?php if (user_logged_in()) : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $path == '/editor' ? 'active' : '' ?>" href="/editor">
                            <i class="ion-compose"></i>&nbsp;New Post
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $path == '/settings' ? 'active' : '' ?>" href="/settings">
                            <i class="ion-gear-a"></i>&nbsp;Settings
                        </a>
                    </li>

                    <li class="nav-item">
                        <?php $user = user_current() ?>
                        <a class="nav-link <?= $path == '/@' . $user["slug"] ? 'active' : '' ?>" href="/@<?= $user["slug"] ?>">
                            <img class="user-pic" src="<?= $user['image_url'] ?>">
                            <?= $user["name"] ?>
                        </a>
                    </li>

                <?php else : ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $path == '/login' ? 'active' : '' ?>" href="/login">Sign in</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $path == '/register' ? 'active' : '' ?>" href="/register">Sign up</a>
                    </li>

                <?php endif ?>
            </ul>
        </div>
    </nav>

    <?= $body ?>

    <footer>
        <div class="container">
            <a href="/" class="logo-font">conduit</a>
            <span class="attribution">
                An interactive learning project from <a href="https://thinkster.io">Thinkster</a>. Code &amp; design licensed under MIT.
            </span>
        </div>
    </footer>
</body>

</html>