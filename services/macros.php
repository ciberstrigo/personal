<?php
    function style(string $path): string
    {
        return '<link rel="stylesheet" type="text/css" href="'.$path.'">';
    }

    function language(): string
    {
        return isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en';
    }

    function trans(string $s): string
    {
        return isset(TRANSLATION[$s][language()]) ? TRANSLATION[$s][language()] : '$s';
    }