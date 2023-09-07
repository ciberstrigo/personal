<?php
function previousLocation(): string
{
    return isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'])['path'] : '/';
}

function locate(string $location = '/'): void
{
    header('Location: ' . $location) && die;
}