<?php
require_once("./_micro_micro.php");
require_once("./_user.php");
require_once("./Parsedown.php");

// PATH == /article/[slug]
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$slug = substr($path, strlen("/article/"));

// Load article
$stmt = db()->prepare("SELECT * FROM articles WHERE slug=:slug");
$stmt->execute(["slug" => $slug]);
$article = $stmt->fetch();

define("LAYOUT_TITLE", $article["title"]);

// 404?
if (!$article) {
    header("Location: /", true, 302);
    exit();
}

// Load author
$stmt = db()->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute(["id" => $article["author_user_id"]]);
$author = $stmt->fetch();

// Load tags
$stmt = db()->prepare("SELECT tag FROM tags WHERE article_id=:article_id");
$stmt->execute(["article_id" => $article["id"]]);
$tags = array_map(function ($row) {
    return $row["tag"];
}, $stmt->fetchAll());

$user_current_id = user_current_id();
$is_author_current_user = ($user_current_id == $author["id"]);

?>

<div class="article-page">


    <!--    

    Banner: Title + Author + Some Buttons

    -->

    <div class="banner">
        <div class="container">

            <h1><?= $article["title"] ?></h1>

            <div class="article-meta">
                <a href="/@<?= $author["slug"] ?>"><img src="<?= $author["image_url"] ?>" /></a>
                <div class="info">
                    <a href="/@<?= $author["slug"] ?>" class="author"><?= $author["name"] ?></a>
                    <span class="date"><?= (new DateTime($article["created_at"]))->format("F d, Y") ?></span>
                </div>

                <?php if ($is_author_current_user) : ?>
                    <a class="btn btn-outline-secondary btn-sm" href="/editor/<?= $article["slug"] ?>">
                        <i class="ion-edit"></i> Edit Article
                    </a>
                    &nbsp;&nbsp;
                    <a class="btn btn-outline-danger btn-sm" href="/article_delete.php?id=<?= $article["id"] ?>">
                        <i class="ion-trash-a"></i> Delete Article
                    </a>

                <?php elseif ($user_current_id) : ?>
                    <?php
                    $stmt = db()->prepare("SELECT COUNT(1) as follow_count FROM follows WHERE user_id=:user_id AND user_target_id=:user_target_id");
                    $stmt->execute(["user_id" => $user_current_id, "user_target_id" => $author["id"]]);
                    $followed_by_me = $stmt->fetch()["follow_count"] > 0;
                    ?>

                    <?php if ($followed_by_me) : ?>
                        <a href="/follow.php?<?= http_build_query(["user" => $author["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Unfollow <?= $author["name"] ?>
                        </a>
                    <?php else : ?>
                        <a href="/follow.php?<?= http_build_query(["user" => $author["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm btn-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Follow <?= $author["name"] ?>
                        </a>
                    <?php endif ?>
                    &nbsp;&nbsp;

                    <?php
                    $stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id");
                    $stmt->execute(["article_id" => $article["id"]]);
                    $favorite_count = $stmt->fetch()["favorite_count"];

                    $favorited_by_me = false;
                    if ($user_current_id) {
                        $stmt = db()->prepare("SELECT COUNT(1) as favorite_count FROM favorites WHERE article_id=:article_id AND user_id=:user_id");
                        $stmt->execute(["article_id" => $article["id"], "user_id" => $user_current_id]);
                        $favorited_by_me = $stmt->fetch()["favorite_count"] > 0;
                    }
                    ?>
                    <a href="/article_favorite.php?<?= http_build_query(["id" => $article["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm <?= $favorited_by_me ? "btn-primary" : "btn-outline-primary" ?>">
                        <i class="ion-heart"></i>
                        &nbsp;
                        <?= $favorited_by_me ? "Unfavorite Article" : "Favorite Article" ?> <span class="counter">(<?= $favorite_count ?>)</span>
                    </a>

                <?php endif ?>
            </div>

        </div>
    </div>

    <div class="container page">


        <!--    
        
        Main Article + Tags
        
        -->

        <div class="row article-content">
            <div class="col-md-12">
                <?php
                $parsedown = new Parsedown();
                $parsedown->setSafeMode(true);
                echo $parsedown->text($article["body"]);
                ?>
            </div>

            <div class="col-md-12">
                <ul class="tag-list">
                    <?php foreach ($tags as $tag) : ?>
                        <li class="tag-default tag-pill tag-outline"><?= $tag ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>

        <hr />


        <!--    
            
        Author + Some Buttons

        -->

        <div class="article-actions">
            <div class="article-meta">
                <a href="/@<?= $author["slug"] ?>"><img src="<?= $author["image_url"] ?>" /></a>
                <div class="info">
                    <a href="/@<?= $author["slug"] ?>" class="author"><?= $author["name"] ?></a>
                    <span class="date"><?= (new DateTime($article["created_at"]))->format("F d, Y") ?></span>
                </div>

                <?php if ($is_author_current_user) : ?>
                    <a class="btn btn-outline-secondary btn-sm" href="/editor/<?= $article["slug"] ?>">
                        <i class="ion-edit"></i> Edit Article
                    </a>
                    &nbsp;&nbsp;
                    <a class="btn btn-outline-danger btn-sm" href="/article_delete.php?id=<?= $article["id"] ?>">
                        <i class="ion-trash-a"></i> Delete Article
                    </a>

                <?php elseif ($user_current_id) : ?>
                    <?php if ($followed_by_me) : ?>
                        <a href="/follow.php?<?= http_build_query(["user" => $author["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm btn-outline-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Unfollow <?= $author["name"] ?>
                        </a>
                    <?php else : ?>
                        <a href="/follow.php?<?= http_build_query(["user" => $author["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm btn-secondary action-btn">
                            <i class="ion-plus-round"></i>
                            &nbsp;
                            Follow <?= $author["name"] ?>
                        </a>
                    <?php endif ?>
                    &nbsp;&nbsp;
                    <a href="/article_favorite.php?<?= http_build_query(["id" => $article["id"], "return_url" => $_SERVER['REQUEST_URI']]) ?>" class="btn btn-sm <?= $favorited_by_me ? "btn-primary" : "btn-outline-primary" ?>">
                        <i class="ion-heart"></i>
                        &nbsp;
                        <?= $favorited_by_me ? "Unfavorite Article" : "Favorite Article" ?> <span class="counter">(<?= $favorite_count ?>)</span>
                    </a>

                <?php endif ?>
            </div>
        </div>


        <!--    
            
        Comments

        -->

        <div class="row">

            <div class="col-xs-12 col-md-8 offset-md-2">

                <?php if ($user_current_id) : ?>
                    <form class="card comment-form" action="/comment_create.php" method="POST">
                        <div class="card-block">
                            <textarea name="comment_text" class="form-control" placeholder="Write a comment..." rows="3"></textarea>
                        </div>
                        <div class="card-footer">
                            <img src="<?= $author["image_url"] ?>" class="comment-author-img" />
                            <input type="submit" value="Post Comment" class="btn btn-sm btn-primary">
                            <input type="hidden" name="article_id" value="<?= $article["id"] ?>">
                        </div>
                    </form>
                <?php else : ?>
                    <p show-authed="false" style="display: inherit;">
                        <a href="/login">Sign in</a> or <a href="/register">sign up</a> to add comments on this article.
                    </p>
                <?php endif ?>


                <?php
                $stmt = db()->prepare("SELECT * FROM comments WHERE article_id=:article_id ORDER BY created_at DESC");
                $stmt->execute(["article_id" => $article["id"]]);
                $comments = $stmt->fetchAll();
                ?>
                <?php foreach ($comments as $comment) : ?>
                    <div class="card">
                        <div class="card-block">
                            <p class="card-text"><?= nl2br($comment["body"]) ?></p>
                        </div>
                        <div class="card-footer">
                            <?php
                            $stmt = db()->prepare("SELECT * FROM users WHERE id=:id");
                            $stmt->execute(["id" => $comment["author_user_id"]]);
                            $comment_author = $stmt->fetch();
                            ?>
                            <a href="/@<?= $comment_author["slug"] ?>" class="comment-author">
                                <img src="<?= $comment_author["image_url"] ?>" class="comment-author-img" />
                            </a>
                            &nbsp;
                            <a href="/@<?= $comment_author["slug"] ?>" class="comment-author"><?= $comment_author["name"] ?></a>
                            <span class="date-posted"><?= (new DateTime($comment["created_at"]))->format("F d, Y") ?></span>

                            <?php if ($user_current_id == $comment_author["id"]) : ?>
                                <span class="mod-options">
                                    <a href="/comment_delete.php?<?= http_build_query(["comment_id" => $comment["id"], "return_url" => $_SERVER["REQUEST_URI"]]) ?>"><i class="ion-trash-a"></i></a>
                                </span>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endforeach ?>

                <style>
                    .article-page .card .mod-options a {
                        color: #333;
                    }
                </style>
            </div>

        </div>

    </div>

</div>

<?php render_into_layout("./layout.php");
