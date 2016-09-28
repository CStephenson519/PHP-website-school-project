# editable

composer installable module for editable markdown on a web page. Add an .htaccess in a folder named www in your repository:

    AddHandler application/x-httpd-php .phtml
    RewriteEngine On

    RewriteCond %{REQUEST_URI}::$1 ^(.*?/)(.*)::\2$
    RewriteRule ^(.*)$ - [E=BASE:%1]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME}.%{TIME_YEAR}.html -f
    # Rewrite /foo/bar to /foo/bar.html
    RewriteRule ^(.*)$ %{REQUEST_URI}.%{TIME_YEAR}.html [L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME}.html -f
    # Rewrite /foo/bar to /foo/bar.html
    RewriteRule ^(.*)$ %{REQUEST_URI}.html [L]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME}.php -f
    # Rewrite /foo/bar to /foo/bar.php
    RewriteRule ^(.*)$ %{REQUEST_URI}.php [L]

    RewriteCond %{REQUEST_FILENAME} -d
    RewriteCond %{REQUEST_FILENAME}index.%{TIME_YEAR}.html -f
    # Rewrite /foo/bar to have it compiled to a new php file
    RewriteRule ^(.*)$ %{REQUEST_URI}index.%{TIME_YEAR}.html [L]

    RewriteCond %{REQUEST_FILENAME} -d
    RewriteCond %{REQUEST_FILENAME}index.html -f
    # Rewrite /foo/bar to have it compiled to a new php file
    RewriteRule ^(.*)$ %{REQUEST_URI}index.html [L]

    RewriteCond %{REQUEST_FILENAME} -d
    RewriteCond %{REQUEST_FILENAME}index.php -f
    # Rewrite /foo/bar to have it compiled to a new php file
    RewriteRule ^(.*)$ %{REQUEST_URI}index.php [L]

    RewriteCond %{REQUEST_FILENAME} !-f
    # Rewrite /foo/bar to have it compiled to a new php file
    RewriteRule ^(.*)$ %{ENV:BASE}indexTransform.php/$1 [L]

Then you will also need some sort of routing. I added slim to my composer.json file:

    "require":{
        "rhildred/editable":"*",
        "slim/slim": "*"
    }
    
Finally, also in the www folder I added:

    <?php require_once(__DIR__ . '/../vendor/autoload.php');

    $sScript = dirname(__FILE__) . $_SERVER['PATH_INFO'];
    if(substr($sScript, -1) == "/") $sScript .= "index";
    if (file_exists($sScript . ".phtml")){
        \RHildred\Editable\Phtml::render($sScript);
    }else{


        session_start();

        $sUrl = "http";
        if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
        {
            $sUrl .= "s";
        }
        $sUrl .= "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . "/callback";


        $app = new \Slim\Slim();

        $app->get('/login', function () use ($app){
            global $sUrl;
            $oOauth2 = new \RHildred\Editable\Oauth2($sUrl);
            $app->redirect($oOauth2->redirect());
        });

        $app->get('/login/callback', function () use ($app) {
            global $sUrl;
            $oOauth2 = new \RHildred\Editable\Oauth2($sUrl);
            $rc= $oOauth2->handleCode($_GET["code"]);
            $_SESSION["currentuser"] = $rc;
            $app->redirect("../");
        });

        $app->get('/logout', function () {
            unset($_SESSION["currentuser"]);
            $rc = new stdClass();
            $rc->result = "success";
            echo json_encode($rc);

        });

        $app->get('/CurrentUser', function () {
            if(!isset($_SESSION["currentuser"])) throw new Exception('no user logged in');
            echo json_encode($_SESSION["currentuser"]);
        });

        $app->post('/ToMd/:sId', function($sId){
            $sReferer = !empty($_SERVER['HTTP_REFERER'])? basename($_SERVER['HTTP_REFERER']):"index";
            if($sReferer == "www" || $sReferer == "" || $sReferer == "salesucation.com") $sReferer = "index";

            echo \RHildred\Editable\Markdown::save($sId, $_POST["sValue"], $sReferer);
        });

        $app->run();

    }