<?php
enum Language: string
{
    case ENGLISH = 'en';
    case RUSSIAN = 'ru';
}

function setLanguage(?Language $language): void
{
    $_SESSION['lang'] = $language->value;
}

function language(): ?Language
{
    return isset($_SESSION['lang']) ? Language::from($_SESSION['lang']) : Language::ENGLISH;
}

function trans(string $s): string
{
    return isset(TRANSLATION[$s][language()->value]) ? TRANSLATION[$s][language()->value] : $s;
}

