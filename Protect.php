<?php

namespace SimplePHP\Form;

// Класс для защиты формы, пока реализованы методы защита от CSRF
class Protect {
    static function randStr($len)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $s = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < $len; $i++) {
            $s .= $characters[mt_rand(0, $max)];
        }
        return $s;
    }

    static function createToken($formId)
    {
        $salt = self::randStr(16);
        $secret = self::randStr(32);
        $token = $salt . ':' . md5($salt + ':' + $secret);
        $_SESSION[$formId.'_'.$salt] = $secret;
        return $token;
    }

    static function checkToken($formId, $token)
    {
        list($salt, $hash) = explode(':', $token);
        if (isset($_SESSION[$formId.'_'.$salt])) {
            if ($hash === md5($salt . ':' . $_SESSION[$formId.'_'.$salt])) {
                unset($_SESSION[$formId.'_'.$salt]);
                return true;
            };
        }
        return false;
    }
}