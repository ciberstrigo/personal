<?php
function previousLocation(): ?string
{
    if (isset($_GET['previous']) && "" != $_GET['previous']) {
        return $_GET['previous'];
    }

    if (isset($_POST['previous']) && "" != $_POST['previous']) {
        return $_POST['previous'];
    }

    return isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'])['path'] : '/';
}

function changePreviousLocation(string $location): void
{
    $_SESSION['previous'] = $location;
}

function locate(string $location = '/'): void
{
    header('Location: ' . $location);
    die;
}