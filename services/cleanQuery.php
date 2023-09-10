<?php

function cleanQuery(array $fields, string $type='POST'): void
{
    foreach ($fields as $field) {
        if ('POST' === $type) {
            $_POST[$field] = htmlspecialchars($_POST[$field], ENT_QUOTES, 'UTF-8');
        }

        if ('GET' === $type) {
            $_GET[$field] = htmlspecialchars($_POST[$field], ENT_QUOTES, 'UTF-8');
        }
    }
}