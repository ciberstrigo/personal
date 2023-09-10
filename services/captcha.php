<?php
function createCaptcha(): GdImage
{
    $chars = '1234567890';
    $code = substr(str_shuffle($chars), 0, 4);
    $_SESSION['captcha'] = $code;

    $image = imagecreate(120, 50);
    imagecolorallocate($image, 4, 37, 236);

    imagefttext(
        $image,
        16,
        0,
        16,
        32,
        imagecolorexact($image, 5, 36, 237),
        './fonts/web_phoenix_bios.woff',
        $code
    );

    return $image;
}