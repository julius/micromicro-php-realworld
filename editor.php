<?php
require_once("./_micro_micro.php");
require_once("./_user.php");

if (!user_current_id()) {
    header("Location: /", true, 302);
    exit();
}

// PATH == /editor[/slug]
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$article = false;
$tags = false;
if (strlen($path) > strlen("/editor/")) {
    // get article
    $slug = substr($path, strlen("/editor/"));
    $stmt = db()->prepare("SELECT * FROM articles WHERE slug=:slug");
    $stmt->execute(["slug" => $slug]);
    $article = $stmt->fetch();

    // get tags
    if ($article) {
        $stmt = db()->prepare("SELECT tag FROM tags WHERE article_id=:article_id");
        $stmt->execute(["article_id" => $article["id"]]);
        $tags = array_map(function ($row) {
            return $row["tag"];
        }, $stmt->fetchAll());
    }
}

$error_messages = [];
$form = [
    "title" => $article ? $article["title"] : "",
    "description" => $article ? $article["description"] : "",
    "body" => $article ? $article["body"] : "",
    "tags" => $tags ? implode(", ", $tags) : "",
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_POST();
function handle_POST()
{
    global $error_messages, $form, $article;

    $form = [
        "title" => filter_var(trim($_POST["title"]), FILTER_SANITIZE_STRING),
        "description" => filter_var(trim($_POST["description"]), FILTER_SANITIZE_STRING),
        "body" => $_POST["body"],
        "tags" => filter_var(trim($_POST["tags"]), FILTER_SANITIZE_STRING),
    ];

    // tags-comma-separated-string => tags array
    $form["tags"] = array_map(function ($tag) {
        return trim($tag);
    }, explode(",", $form["tags"]));
    $form["tags"] = array_filter($form["tags"], function ($tag) {
        return $tag != "";
    });
    $form["tags"] = array_unique($form["tags"]);

    if ($form["title"] == "") {
        $error_messages[] = "Enter a title.";
        return;
    }

    if ($form["body"] == "") {
        $error_messages[] = "Write an article. Your text is empty.";
        return;
    }

    $slug = slug_from_title($form["title"]) . "-" . substr(uniqid(), 0, 6);

    if ($article) {
        // Update article
        db()->prepare("
            UPDATE articles 
            SET author_user_id=:author_user_id, slug=:slug, title=:title, description=:description, body=:body
            WHERE id=:id
            ")
            ->execute([
                "id" => $article["id"],
                "author_user_id" => user_current_id(),
                "slug" => $slug,
                "title" => $form["title"],
                "description" => $form["description"],
                "body" => $form["body"],
            ]);

        // Update tags
        db()->prepare("
            DELETE FROM tags
            WHERE article_id=:article_id
            ")
            ->execute(["article_id" => $article["id"]]);

        foreach ($form["tags"] as $tag) {
            db()->prepare("INSERT INTO tags (tag, article_id) VALUES (:tag, :article_id)")
                ->execute(["tag" => $tag, "article_id" => $article["id"]]);
        }
    } else {

        // Create article
        db()->prepare("
            INSERT INTO articles (author_user_id, slug, title, description, body)
            VALUES (:author_user_id, :slug, :title, :description, :body)
            ")
            ->execute([
                "author_user_id" => user_current_id(),
                "slug" => $slug,
                "title" => $form["title"],
                "description" => $form["description"],
                "body" => $form["body"],
            ]);
        $article_id = db()->lastInsertId();

        // Create Tags
        foreach ($form["tags"] as $tag) {
            db()->prepare("INSERT INTO tags (tag, article_id) VALUES (:tag, :article_id)")
                ->execute(["tag" => $tag, "article_id" => $article_id]);
        }
    }

    // show article
    header("Location: /article/" . $slug, true, 302);
    exit();
}

function slug_from_title($title)
{
    $title = preg_replace('~[^\pL\d]+~u', '-', $title);
    $title = iconv('utf-8', 'us-ascii//TRANSLIT', $title);
    $title = preg_replace('~[^-\w\s]+~', '', $title);
    $title = preg_replace('~-+~', '-', $title);
    $title = strtolower($title);
    return $title;
}


?>

<div class="editor-page">
    <div class="container page">
        <div class="row">

            <div class="col-md-10 offset-md-1 col-xs-12">

                <?php if (count($error_messages) > 0) : ?>
                    <ul class="error-messages">
                        <?php foreach ($error_messages as $message) : ?>
                            <li><?= $message ?></li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>

                <form method="POST" action="">
                    <fieldset>
                        <fieldset class="form-group">
                            <input name="title" value="<?= $form["title"] ?>" type="text" class="form-control form-control-lg" placeholder="Article Title">
                        </fieldset>
                        <fieldset class="form-group">
                            <input name="description" value="<?= $form["description"] ?>" type="text" class="form-control" placeholder="What's this article about?">
                        </fieldset>
                        <fieldset class="form-group">
                            <textarea name="body" class="form-control" rows="8" placeholder="Write your article (in markdown)"><?= $form["body"] ?></textarea>
                        </fieldset>
                        <fieldset class="form-group">
                            <input name="tags" value="<?= $form["tags"] ?>" type="text" class="form-control" placeholder="Enter tags (comma separated)">
                        </fieldset>
                        <input type="submit" class="btn btn-lg pull-xs-right btn-primary" value="Publish Article">
                    </fieldset>
                </form>
            </div>

        </div>
    </div>
</div>

<?php render_into_layout("layout.php");
