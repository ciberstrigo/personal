<?php
function previousLocation(): string
{
    $exploded = isset($_SERVER['HTTP_REFERER']) ? explode('/',$_SERVER['HTTP_REFERER']) : null;
    $location = $exploded ? end($exploded) : '';

    return '/' . $location;
}

function locate(string $location = '/'): string
{
    header('Location: ' . $location) && die;
}