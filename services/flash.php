<?php
const FLASH = 'FLASH_MESSAGES';

const FLASH_ERROR = 'error';
const FLASH_WARNING = 'warning';
const FLASH_INFO = 'info';
const FLASH_SUCCESS = 'success';

function createFlash(string $name, string $message, string $type)
{
    if (isset($_SESSION[FLASH][$name])) {
        unset($_SESSION[FLASH][$name]);
    }

    $_SESSION[FLASH][$name] = ['message' => $message, 'type' => $type];
}

function displayFlash($name): string
{
    if (!isset($_SESSION[FLASH][$name])) {
        return '';
    }

    $flash_message = $_SESSION[FLASH][$name];
    unset($_SESSION[FLASH][$name]);

    return sprintf('<div class="alert alert-%s">%s</div>',
        $flash_message['type'],
        $flash_message['message']
    );
}

function flash(string $name = '', string $message = '', string $type = '')
{
    if ($name !== '' && $message !== '' && $type !== '') {
        createFlash($name, $message, $type);
    } elseif ($name !== '' && $message === '' && $type === '') {
        return displayFlash($name);
    }
}