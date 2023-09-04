<?php

function route($routes) {
    echo array_key_exists($_SERVER['REQUEST_URI'], $routes) ?
        $routes[$_SERVER['REQUEST_URI']]() :
        (header('Location: /') && die);
}

route([
    'articles' => static function () {
        return BaseTemplate(
            (new Template)->render('pages/articles.phtml')
        );
    },
    '/contact' => static function () {
        return BaseTemplate(
            (new Template)->render('pages/contact.phtml')
        );
    },
    '/' => static function () {
        return BaseTemplate(
            (new Template)->render('pages/main.phtml')
        );
    }
]);