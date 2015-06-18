<?php

class WA_Http_Client
{
    const TIMEOUT = 10;

    public function sendRequest($url, $args)
    {
        $args['user-agent'] = 'WP - Wild Apricot Login (' . WA_Utils::sanitizeString(home_url()) . ')';
        $args['sslverify'] = false;
        $args['timeout'] = self::TIMEOUT;

        return wp_remote_request($url, $args);
    }
}