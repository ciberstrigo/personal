<?php

function route($routes): string
{
    $requestPath = explode('?', $_SERVER['REQUEST_URI'])[0];

    // call a function if route completely math with a path
    if (array_key_exists($requestPath, $routes)) {
        return $routes[$requestPath]();
    }

    // or else we've to do some magick here
    foreach(array_keys($routes) as $path) {
        $origPath = $path;
        $pathMatches = [];
        $res = preg_match_all('/\{([^}]*)\}/', $path, $pathMatches);

        if (!$res) {
            continue;
        }

        $path = str_replace('/', '\/', $path);
        $path = '/^'.preg_replace('/\{([^}]*)\}/', '([^\/]+)', $path).'$/';

        $isMatch = preg_match($path, $requestPath, $matches);

        if (!$isMatch) {
            continue;
        }

        array_shift($matches);
        $argumentNames = array_map(function ($argName) {
            return preg_replace_callback('/_([a-z])/', function($match) {
                return strtoupper($match[1]);
            }, $argName);
        }, $pathMatches[1]);

        $arguments = array_combine($argumentNames, $matches);

        return call_user_func_array($routes[$origPath], $arguments);
    }

    return isset($routes['/404']) ? $routes['/404']() : '404 page not found';
}

$notFound = function () {
    http_response_code(404);
    return (new Template())->render('pages/404/404.phtml');
};

echo route([
    '/articles' => static function () {
        return (new Template)->render('pages/article/list.phtml', [
            'articles' => articleList(language()),
            'isAdmin' => isset($_SESSION['user_id']) && isAdmin($_SESSION['user_id'])
        ]);
    },
    '/article/{id}' => static function ($id) use ($notFound) {
        if (!is_numeric($id)) {
            return $notFound();
        }

        $article = getArticle($id, language());

        if (!$article) {
            return $notFound();
        }

        $comments = getComments($id);

        return (new Template)->render('pages/article/article.phtml', [
            'article' => $article,
            'comments' => $comments
        ]);
    },
    '/article/{articleId}/comment' => static function($articleId) {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['text', 'reply_to']);
            createComment(
                $_POST['captcha'],
                $articleId,
                $_SESSION['user_id'],
                $_POST['text'],
                '' === $_POST['reply_to'] ? null : $_POST['reply_to']
            );
            unset($_SESSION['REPLY_TO_ID']);
        }

        locate('/article/'.$articleId.'#comments');
    },
    '/article/{articleId}/comment/reply-to/undo' => static function($articleId) {
        unset($_SESSION['REPLY_TO_ID']);
        locate('/article/'.$articleId.'#comments');
    },
    '/article/{articleId}/comment/reply-to/{commentId}' => static function($articleId, $commentId) {
        $_SESSION['REPLY_TO_ID'] = $commentId;
        locate('/article/'.$articleId.'#comments');
    },
    '/article/{articleId}/comment/{commentId}/delete' => static function($articleId, $commentId) {
        deleteComment($commentId);
        locate('/article/'.$articleId.'#comments');
    },
    '/article/create' => static function() {
        if (empty($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
            locate(previousLocation());
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $id = createArticle($_SESSION['user_id'], $_POST['title'], $_POST['text'], language());
            locate('/article/'.$id);
        }

        return (new Template)->render('pages/article/create.phtml', []);
    },
    '/article/{articleId}/edit' => static function($articleId) {
        if (empty($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
            locate(previousLocation());
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            editArticle($articleId, $_POST['title'], $_POST['text']);
            locate('/article/'.$articleId);
        }

        $article = getArticle($articleId, language());

        return (new Template)->render('pages/article/edit.phtml', [
            'title' => $article['title'],
            'text' => $article['text'],
            'articleId' => $articleId
        ]);
    },
    '/contact' => static function () {
        return (new Template)->render('pages/contact/contact.phtml', []);
    },
    '/' => static function () {
        return (new Template)->render('pages/main.phtml', [
            'mainPage' => getArticle(1, language())
        ]);
    },
    '/login' => static function () {
        if (isset($_SESSION['login'])) {
            locate('/');
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['login', 'password']);
            $user = login($_POST['login'], $_POST['password']);
            if ($user) {
                flash('login', 'Login successfully!', FLASH_SUCCESS);
                locate(previousLocation());
            } else {
                flash('login', 'Username or password is incorrect', FLASH_ERROR);
            }
        }

        return (new Template)->render('pages/login/login.phtml', []);
    },
    '/registration' => static function () {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['login', 'password']);

            if (strlen($_POST['login']) > LOGIN_MAX_LENGTH) {
                flash('registration', 'Login is too long', FLASH_ERROR);
            }

            if (empty($_POST['login']) || empty($_POST['password'])) {
                flash('registration', 'Incorrect credentials', FLASH_ERROR);
            }

            if (alreadyRegistered($_POST['login'])) {
                flash('registration', 'User already registered', FLASH_ERROR);
            }

            $user = registration($_POST['login'], $_POST['password']);

            if ($user) {
                flash('registration', 'Successfully registered!', FLASH_SUCCESS);
                locate(previousLocation());
            }

            flash('registration', 'Error occurring during the registration', FLASH_ERROR);
        }

        return (new Template)->render('pages/registration.phtml', []);
    },
    '/logout' => static function () {
        unset($_SESSION['username'], $_SESSION['user_id']);
        session_destroy();

        locate(previousLocation());
    },
    '/set' => static function () {
        if (isset($_GET['lang'])) {
            setLanguage(Language::tryFrom($_GET['lang']));
        }

        if (isset($_GET['font'])) {
            $_SESSION['font'] = $_GET['font'];
        }

        locate(previousLocation());
    },
    '/captcha' => function () {
        header('Cache-Control: no-store, must-revalidate');
        header('Expires: 0');
        header('Content-Type: image/png');
        $captcha = createCaptcha();
        imagepng($captcha);
        imagedestroy($captcha);
    },
    '/404' => $notFound
]);