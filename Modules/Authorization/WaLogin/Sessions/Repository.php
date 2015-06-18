<?php

class WA_Modules_Authorization_WaLogin_Sessions_Repository
{
    const LOCK_ID_PREFIX = 'wa_integration_logged_in_session_';
    const LOCK_TIMEOUT = 5;

    const OAUTH2_TOKEN_OPTION_ID = 'OAuth2Token';
    const EXPIRATION_OPTION_ID = 'expiration';

    private $userId;
    private $options;

    public function __construct($userId, $optionName)
    {
        $this->userId = $userId;
        $this->options = new WA_Modules_Authorization_WaLogin_Sessions_Options($userId, $optionName);
    }

    public function setOAuth2Token($oAuth2Token, $cookie = '')
    {
        $lockId = $this->getLockId();
        $session = wp_parse_auth_cookie($cookie, 'logged_in');

        if (!WA_Utils::isNotEmptyArray($session) || empty($session['token'])) { return null; }

        WA_Lock_Provider::wait($lockId, self::LOCK_TIMEOUT);
        WA_Lock_Provider::acquire($lockId, self::LOCK_TIMEOUT);

        $this->removeExpiredTokens();
        $this->options->setOption($session['token'], self::OAUTH2_TOKEN_OPTION_ID, $oAuth2Token);
        $this->options->setOption($session['token'], self::EXPIRATION_OPTION_ID, $session['expiration']);
        $this->options->save();

        WA_Lock_Provider::release($lockId);
    }

    public function getOAuth2Token($cookie = '')
    {
        $lockId = $this->getLockId();
        $session = wp_parse_auth_cookie($cookie, 'logged_in');

        if (!WA_Utils::isNotEmptyArray($session) || empty($session['token'])) { return null; }

        WA_Lock_Provider::wait($lockId, self::LOCK_TIMEOUT);
        WA_Lock_Provider::acquire($lockId, self::LOCK_TIMEOUT);

        $this->removeExpiredTokens();
        $this->options->save();

        WA_Lock_Provider::release($lockId);

        return $this->options->getOption($session['token'], self::OAUTH2_TOKEN_OPTION_ID);
    }

    private function getLockId()
    {
        return self::LOCK_ID_PREFIX . $this->userId;
    }

    private function removeExpiredTokens()
    {
        $tokens = $this->options->getUnitKeys();

        if (!WA_Utils::isNotEmptyArray($tokens)) { return; }

        $currentTime = time();

        foreach ($tokens as $token)
        {
            $expiration = $this->options->getOption($token, self::EXPIRATION_OPTION_ID);

            if (!empty($expiration) && $expiration < $currentTime)
            {
                $this->options->unsetUnit($token);
            }
        }
    }
}