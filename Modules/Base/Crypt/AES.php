<?php

class WA_Modules_Base_Crypt_AES
{
    const CONFIG_FILE = 'config.php';

    private static $AES;
    private static $config;

    public function __construct()
    {
        if (empty(self::$AES))
        {
            $this->setAES();
        }
    }

    public function encrypt($text)
    {
        return base64_encode(self::$AES->encrypt($text));
    }

    public function decrypt($text)
    {
        $text = trim(base64_decode($text));
        return self::$AES->decrypt($text);
    }

    public static function install()
    {
        $confString = implode(
            "\n",
            array(
                '<?php',
                '',
                'return array(',
                "    'salt' => '" . str_replace("'", '"', wp_generate_password(16, true, true)) . "',",
                "    'password' => '" . str_replace("'", '"', wp_generate_password(16, true, true)) . "',",
                "    'iv' => '" . str_replace("'", '"', base64_encode(self::createIV())) . "'",
                ');'
            )
        );

        return @file_put_contents(self::getConfigFilePath(), $confString, LOCK_EX);
    }

    private static function createIV()
    {
        return extension_loaded('mcrypt') ? WA_Modules_Base_Crypt_McryptAES::createIv() : '';
    }

    private static function getConfigFilePath()
    {
        return wp_normalize_path(dirname(__FILE__) . '/') . self::CONFIG_FILE;
    }

    private function setAES()
    {
        self::$config = require_once(self::getConfigFilePath());
        self::$config['iv'] = base64_decode(self::$config['iv']);

        if (extension_loaded('openssl') && function_exists('openssl_cipher_iv_length'))
        {
            self::$AES = new WA_Modules_Base_Crypt_OpenSslAES(self::$config);
        }
        else if (extension_loaded('mcrypt'))
        {
            self::$AES = new WA_Modules_Base_Crypt_McryptAES(self::$config);
        }
        else
        {
            self::$AES = new WA_Modules_Base_Crypt_CustomAES(self::$config);
        }
    }
}