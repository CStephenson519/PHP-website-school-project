# slimphpviews
a Slim/View descendent with shared layouts that uses modern php for templating

Composer.json
---
```
    "require": {
        "slim/slim": "2.*",
        "rhildred/slimphpviews": "dev-master"
    },
```
Example `public/index.php`
----
```
<?php
    require '../vendor/autoload.php';
// make a new slim object with view Engine PHPView
    $app = new \Slim\Slim(array(
        'view' => new \PHPView\PHPView(),
        'templates.path' => __DIR__ . "/../views"));
    $app->get('/', $index = function () use($app) {
        $app->render("index.phtml", array("page" => "index"));
    });
    $app->run();
```
`views/index.phtml`
----
```
<?php $this->layout("_layout.phtml") ?>
<div class="starter-template">
    <h1>Bootstrap starter template</h1>
    <p class="lead">Use this document as a way to quickly start any new project.
        <br>All you get is this text and a mostly barebones HTML document.</p>
</div>
```
`views/_layout.phtml`
------
```
<!DOCTYPE html>
<html>
<head>
<title>Rich On Sourceforge</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
</head>
<body>
        <?php
        $this->renderBody();
        ?>
</body>
</html>
```

