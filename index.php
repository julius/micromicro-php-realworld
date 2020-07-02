<?php

require_once("./_micro_micro.php");
require_once("./_user.php");

$current_user_id = user_current_id();
$is_user_logged_in = !!$current_user_id;

$page = 1;
if (isset($_GET["page"]))
    $page = intval($_GET["page"]);

$feed = "my";
if (isset($_GET["feed"]))
    $feed = $_GET["feed"];
if (!$is_user_logged_in && $feed == "my")
    $feed = "global";

$feed_tag = false;
if (strpos($feed, "tag_") === 0)
    $feed_tag = substr($feed, strlen("tag_"));

if ($feed == "global") {

    /*
        FEED: global
    */
    $stmt = db()->prepare("SELECT COUNT(1) as articles_count FROM articles");
    $stmt->execute();
    $page_count = ceil($stmt->fetch()["articles_count"] / 10);
    $page = max(1, min($page_count, $page));

    $stmt = db()->prepare("
        SELECT * FROM articles
        ORDER BY created_at DESC
        LIMIT :offset, 10
    ");
    $stmt->execute(["offset" => ($page - 1) * 10]);
    $articles = $stmt->fetchAll();
} elseif ($feed == "my") {

    /*
        FEED: my followed articles
    */
    $stmt = db()->prepare("
        SELECT COUNT(1) as articles_count FROM follows
        JOIN articles
        ON articles.author_user_id = follows.user_target_id
        WHERE follows.user_id=:follows_user_id
        ");
    $stmt->execute(["follows_user_id" => $current_user_id]);
    $page_count = ceil($stmt->fetch()["articles_count"] / 10);
    $page = max(1, min($page_count, $page));

    $stmt = db()->prepare("
        SELECT * FROM follows
        JOIN articles
        ON articles.author_user_id = follows.user_target_id
        WHERE follows.user_id=:follows_user_id
        ORDER BY articles.created_at DESC
        LIMIT :offset, 10
    ");
    $stmt->execute([
        "follows_user_id" => $current_user_id,
        "offset" => ($page - 1) * 10,
    ]);
    $articles = $stmt->fetchAll();
} elseif ($feed_tag) {

    /*
        FEED: specific Tag
    */
    $stmt = db()->prepare("
        SELECT COUNT(1) as articles_count
        FROM tags
        JOIN articles 
        ON tags.article_id = articles.id
        WHERE tags.tag = :tag
    ");
    $stmt->execute(["tag" => $feed_tag]);
    $page_count = ceil($stmt->fetch()["articles_count"] / 10);
    $page = max(1, min($page_count, $page));

    $stmt = db()->prepare("
        SELECT * 
        FROM tags
        JOIN articles 
        ON tags.article_id = articles.id
        WHERE tags.tag = :tag
        ORDER BY articles.created_at DESC
        LIMIT :offset, 10
    ");
    $stmt->execute([
        "tag" => $feed_tag,
        "offset" => ($page - 1) * 10,
    ]);
    $articles = $stmt->fetchAll();
} else {
    header("Location: /", false, 302);
    exit();
}

?>


<div class="home-page">

    <?php if (!$is_user_logged_in) : ?>
        <div class="banner">
            <div class="container">
                <h1 class="logo-font">conduit</h1>
                <p>A place to share your knowledge.</p>
            </div>
        </div>
    <?php endif ?>

    <div class="container page">
        <div class="row">

            <div class="col-md-9">
                <div class="feed-toggle">
                    <ul class="nav nav-pills outline-active">
                        <?php if ($is_user_logged_in) : ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $feed == 'my' ? 'active' : '' ?>" href="/">Your Feed</a>
                            </li>
                        <?php endif ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $feed == 'global' ? 'active' : '' ?>" href="/?feed=global">Global Feed</a>
                        </li>
                        <?php if ($feed_tag) : ?>
                            <li class="nav-item">
                                <a class="nav-link active" href="/?<?= http_build_query(["feed" => $feed]) ?>">#<?= $feed_tag ?></a>
                            </li>
                        <?php endif ?>
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
                            if ($is_user_logged_in) {
                                $stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id AND user_id=:user_id");
                                $stmt->execute(["article_id" => $article["id"], "user_id" => $current_user_id]);
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
                                        <a class="page-link" href="/?<?= http_build_query(array_merge($_GET, ["page" => $p])) ?>"><?= $p ?></a>
                                    </li>
                                <?php endfor ?>
                            </ul>
                        </nav>
                    </list-pagination>
                <?php endif ?>
            </div>

            <div class="col-md-3">
                <div class="sidebar">
                    <p>Popular Tags</p>

                    <?php
                    $stmt = db()->prepare("SELECT tag, COUNT(1) as tag_count FROM tags GROUP BY tag ORDER BY tag_count DESC LIMIT 0,20");
                    $stmt->execute();
                    $tags = array_map(function ($row) {
                        return $row["tag"];
                    }, $stmt->fetchAll());
                    ?>

                    <div class="tag-list">
                        <?php foreach ($tags as $tag) : ?>
                            <a href="/?<?= http_build_query(["feed" => "tag_" . $tag]) ?>" class="tag-pill tag-default"><?= $tag ?></a>
                        <?php endforeach ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>


<?php render_into_layout("layout.php"); ?>