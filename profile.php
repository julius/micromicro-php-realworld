<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

// Path should be /@SomeUser[/favorites]
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (substr($path, 0, 2) != "/@") {
    header("Location: /", true, 302);
    exit();
}

// Load User
$stmt = db()->prepare("SELECT * FROM users WHERE slug=:slug");
$stmt->execute(["slug" => substr(explode("/", $path)[1], 1)]);
$user = $stmt->fetch();

$user_current_id = user_current_id();
$is_current_user = ($user["id"] == $user_current_id);

// 404? -> Home
if (!$user) {
    header("Location: /", true, 302);
    exit();
}

$page = 1;
if (isset($_GET["page"]))
    $page = intval($_GET["page"]);

// Load articles
if (strpos($path, "/favorites")) {
    $stmt = db()->prepare("
        SELECT COUNT(1) as articles_count FROM favorites
        JOIN articles 
        ON articles.id = article_id
        WHERE favorites.user_id = :user_id
    ");
    $stmt->execute(["user_id" => $user["id"]]);
    $page_count = ceil($stmt->fetch()["articles_count"] / 10);
    $page = max(1, min($page_count, $page));

    $stmt = db()->prepare("
        SELECT * FROM favorites
        JOIN articles 
        ON articles.id = article_id
        WHERE favorites.user_id = :user_id
        LIMIT :offset, 10
    ");
    $stmt->execute([
        "user_id" => $user["id"],
        "offset" => ($page - 1) * 10,
    ]);
    $articles = $stmt->fetchAll();
} else {
    $stmt = db()->prepare("
        SELECT COUNT(1) as articles_count FROM articles WHERE author_user_id=:author_user_id
    ");
    $stmt->execute(["author_user_id" => $user["id"]]);
    $page_count = ceil($stmt->fetch()["articles_count"] / 10);
    $page = max(1, min($page_count, $page));

    $stmt = db()->prepare("
        SELECT * FROM articles 
        WHERE author_user_id=:author_user_id
        LIMIT :offset, 10
    ");
    $stmt->execute([
        "author_user_id" => $user["id"],
        "offset" => ($page - 1) * 10,
    ]);
    $articles = $stmt->fetchAll();
}


?>

<div class="profile-page">

    <div class="user-info">
        <div class="container">
            <div class="row">

                <div class="col-xs-12 col-md-10 offset-md-1">
                    <img src="<?= $user["image_url"] ?>" class="user-img" />
                    <h4><?= $user["name"] ?></h4>
                    <p><?= nl2br($user["bio"]) ?></p>

                    <?php
                    $stmt = db()->prepare("SELECT COUNT(1) as follow_count FROM follows WHERE user_id=:user_id AND user_target_id=:user_target_id");
                    $stmt->execute(["user_id" => $user_current_id, "user_target_id" => $user["id"]]);
                    $followed_by_me = $stmt->fetch()["follow_count"] > 0;
                    ?>

                    <?php if ($followed_by_me) : ?>
                        <a href="/follow.php?user=<?= $user["id"] ?>" class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Unfollow <?= $user["name"] ?>
                        </a>
                    <?php else : ?>
                        <a href="/follow.php?user=<?= $user["id"] ?>" class="btn btn-sm btn-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Follow <?= $user["name"] ?>
                        </a>
                    <?php endif ?>
                </div>

            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">

            <div class="col-xs-12 col-md-10 offset-md-1">
                <div class="articles-toggle">
                    <ul class="nav nav-pills outline-active">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($path, "/favorites") ? "" : "active" ?>" href="/@<?= $user["slug"] ?>">My Articles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($path, "/favorites") ? "active" : "" ?>" href="/@<?= $user["slug"] ?>/favorites">Favorited Articles</a>
                        </li>
                    </ul>
                </div>

                <?php foreach ($articles as $article) : ?>
                    <div class="article-preview">
                        <div class="article-meta">

                            <?php
                            $stmt = db()->prepare("SELECT * FROM users WHERE id=:id");
                            $stmt->execute(["id" => $article["author_user_id"]]);
                            $author = $stmt->fetch();
                            ?>
                            <a href="/@<?= $author["slug"] ?>"><img src="<?= $author["image_url"] ?>" /></a>
                            <div class="info">
                                <a href="/@<?= $author["slug"] ?>" class="author"><?= $author["name"] ?></a>
                                <span class="date"><?= (new DateTime($article["created_at"]))->format("F d, Y") ?></span>
                            </div>


                            <?php
                            $stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id");
                            $stmt->execute(["article_id" => $article["id"]]);
                            $favorite_count = $stmt->fetch()["favorite_count"];

                            $favorited_by_me = false;
                            if ($user) {
                                $stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id AND user_id=:user_id");
                                $stmt->execute(["article_id" => $article["id"], "user_id" => $user_current_id]);
                                $favorited_by_me = $stmt->fetch()["favorite_count"] > 0;
                            }
                            ?>
                            <a href="/article_favorite.php?<?= http_build_query(["id" => $article["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn <?= $favorited_by_me ? "btn-primary" : "btn-outline-primary" ?> btn-sm pull-xs-right">
                                <i class="ion-heart"></i> <?= $favorite_count ?>
                            </a>
                        </div>
                        <a href="/article/<?= $article["slug"] ?>" class="preview-link">
                            <h1><?= $article["title"]; ?></h1>
                            <p><?= $article["description"] ?></p>
                            <span>Read more...</span>

                            <?php
                            $stmt = db()->prepare("SELECT tag FROM tags WHERE article_id=:article_id");
                            $stmt->execute(["article_id" => $article["id"]]);
                            $tags = array_map(function ($row) {
                                return $row["tag"];
                            }, $stmt->fetchAll());
                            ?>
                            <ul class="tag-list">
                                <?php foreach ($tags as $tag) : ?>
                                    <li class="tag-default tag-pill tag-outline"><?= $tag ?></li>
                                <?php endforeach ?>
                            </ul>
                        </a>
                    </div>
                <?php endforeach ?>

                <?php if ($page_count > 1) : ?>
                    <list-pagination>
                        <nav>
                            <ul class="pagination">
                                <?php for ($p = 1; $p <= $page_count; $p++) : ?>
                                    <li class="page-item <?= $p == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= $_SERVER["REQUEST_URI"] ?>?<?= http_build_query(array_merge($_GET, ["page" => $p])) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor ?>
                            </ul>
                        </nav>
                    </list-pagination>
                <?php endif ?>

            </div>

        </div>
    </div>

</div>

<?php render_into_layout("./layout.php"); ?>