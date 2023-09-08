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
        array_shift($matches);

        if (!$isMatch) {
            continue;
        }

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

echo route([
    '/articles' => static function () {
        return (new Template)->render('pages/article/list.phtml', [
            'articles' => articleList(language()),
            'isAdmin' => isset($_SESSION['user_id']) && isAdmin($_SESSION['user_id'])
        ]);
    },
    '/article/{id}' => static function ($id) {
        return (new Template)->render('pages/article/article.phtml', [
            'article' => getArticle($id, language())
        ]);
    },
    '/article/create' => static function() {
        if (empty($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'])) {
            locate(previousLocation());
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            $id = createArticle($_SESSION['user_id'], $_POST['title'], $_POST['text']);
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
            'title' => $article['name'],
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
            header('Location: /');die;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['login', 'password']);

            if (empty($_POST['login']) || empty($_POST['password'])) {
                flash('login', 'Username or password is incorrect', FLASH_ERROR);
                locate('/login');
            }

            if (!$user = login($_POST['login'], $_POST['password'])) {
                flash('login', 'Username or password is incorrect', FLASH_ERROR);
                locate('/login');
            }

            session_regenerate_id();

            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id']  = $user['id'];

            flash('login', 'Login success', FLASH_SUCCESS);
            locate('/login');
        }

        return (new Template)->render('pages/login/login.phtml', []);
    },
    '/registration' => static function () {
        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['login', 'password']);

            if (empty($_POST['login']) || empty($_POST['password'])) {
                flash('registration', 'Incorrect credentials', FLASH_ERROR);
                header('Location: /registration') && die;
            }

            if (alreadyRegistered($_POST['login'])) {
                flash('registration', 'User already registered', FLASH_ERROR);
                header('Location: /registration') && die;
            }

            if (!registration($_POST['login'], $_POST['password'])) {
                flash('registration', 'Error occurring during the registration', FLASH_ERROR);
                header('Location: /registration') && die;
            }

            flash('registration', 'Registered successfully!', FLASH_SUCCESS);
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
            $_SESSION['lang'] = $_GET['lang'];
        }

        if (isset($_GET['font'])) {
            $_SESSION['font'] = $_GET['font'];
        }

        locate(previousLocation());
    },
    '/test' => static function() {
        var_dump(articleList());die;
    },
    '/404' => static function() {
        return (new Template())->render('pages/404/404.phtml');
    }
]);