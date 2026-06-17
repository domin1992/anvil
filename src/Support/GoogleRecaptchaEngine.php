<?php

namespace Anvil\Support;

class GoogleRecaptchaEngine
{
    public static function validateRecaptcha($gRecaptchaResponse)
    {
        $recaptcha_secret_key = get_field('recaptcha_secret_key', 'option');
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret_key . '&response=' . $gRecaptchaResponse);
        return json_decode($recaptcha);
    }
}