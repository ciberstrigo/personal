<?php

function route($routes) {
    $requestPath = explode('?', $_SERVER['REQUEST_URI'])[0];

    // call a function if rute completely math with a path
    if (array_key_exists($requestPath, $routes)) {
        echo $routes[$requestPath]();
        return;
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
        call_user_func_array($routes[$origPath], $arguments);
    }

    locate('/');
}

route([
    '/articles' => static function () {
        return (new Template)->render('pages/articles.phtml', [
            'articles' => articleList()
        ]);
    },
    '/articles/{id}' => static function ($id) {
        var_dump($id);die;
    },
    '/articles/{id}/comments/{comments_id}' => static function ($id, $commentsId) {
        var_dump($id);
        var_dump($commentsId);
        die;
    },
    '/contact' => static function () {
        return (new Template)->render('pages/contact.phtml', []);
    },
    '/' => static function () {
        return (new Template)->render('pages/main.phtml', []);
    },
    '/login' => static function () {
        if (isset($_SESSION['login'])) {
            header('Location: /');die;
        }

        if ('POST' === $_SERVER['REQUEST_METHOD']) {
            cleanQuery(['login', 'password']);

            if (empty($_POST['login']) || empty($_POST['password'])) {
                flash('login', 'Username or password is incorrect', FLASH_ERROR);
                header('Location: /login');die;
            }

            if (!$user = login($_POST['login'], $_POST['password'])) {
                flash('login', 'Username or password is incorrect', FLASH_ERROR);
                header('Location: /login');die;
            }

            session_regenerate_id();

            $_SESSION['login'] = $user['login'];
            $_SESSION['user_id']  = $user['id'];

            flash('login', 'Login success', FLASH_SUCCESS);
            header('Location: /login');die;
        }

        return (new Template)->render('pages/login.phtml', []);
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

        header('Location: /') && die;
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
    }
]);