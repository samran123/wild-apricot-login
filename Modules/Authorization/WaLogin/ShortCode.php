<?php

class WA_Modules_Authorization_WaLogin_ShortCode extends WA_Modules_Base_ShortCode
{
    private $defaults;

    public function __construct(WA_Modules_Interfaces_IAuthorization $module, $shortCodeName, array $args = null)
    {
        parent::__construct($module, $shortCodeName, $args);
    }

    public function render($attributes, $content, $shortCodeName)
    {
        if (!$this->args['controller']->isValid())
        {
            return '<div class="wa_login_shortcode"><p class="error">'
            . __('Please configure "Wild Apricot Login" plugin.')
            . '</p></div>';
        }

        $defaults = $this->getDefaults();

        $attr = shortcode_atts
        (
            array(
                'login_label' => $defaults['loginLabel'],
                'logout_label' => $defaults['logoutLabel'],
                'redirect_page' => $defaults['redirectUrl']
            ),
            $attributes,
            $shortCodeName
        );

        $this->disablePageCache();

        return '<div class="wa_login_shortcode">'
            . (!is_user_logged_in() ? $this->getLoginForm($attr) : $this->getLogoutForm($attr))
            . '</div>';
    }

    public function getShortCodeString($attr)
    {
        $loginLabel = isset($attr['login_label']) ? WA_Utils::sanitizeString($attr['login_label']) : '';
        $logoutLabel = isset($attr['logout_label']) ? WA_Utils::sanitizeString($attr['logout_label']) : '';
        $redirectPage = isset($attr['redirect_page']) ? WA_Utils::sanitizeString($attr['redirect_page']) : '';

        return '[' . $this->shortCodeName
            . (!empty($loginLabel) ? ' login_label="' . esc_attr($loginLabel) . '"' : '')
            . (!empty($logoutLabel) ? ' logout_label="' . esc_attr($logoutLabel) . '"' : '')
            . (!empty($redirectPage) ? ' redirect_page="' . esc_attr($redirectPage) . '"' : '')
            . ']';
    }

    public function getDefaults()
    {
        if (empty($this->defaults))
        {
            $this->defaults = $this->args['controller']->getShortCodeDefaults();
        }

        return $this->defaults;
    }

    private function getLoginForm($attr)
    {
        $error = $this->args['controller']->getErrorMessage();
        $loginLabel = WA_Utils::sanitizeString($attr['login_label']);
        $loginArgs = $this->args['controller']->getLoginArgs($attr['redirect_page']);

        if (empty($loginLabel))
        {
            $loginLabel = $this->defaults['loginLabel'];
        }

        $loginForm = '<form action="' . $this->args['loginUrl'] .'" method="get">';

        foreach ($loginArgs as $key => $value)
        {
            $loginForm .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }

        $loginForm .= '<input type="submit" name="' . esc_attr($this->args['actionId']) . '" class="button button-primary" value="' . esc_attr($loginLabel)
            . '" title="' . esc_attr($loginLabel) . '" />';

        if (!empty($error))
        {
            $loginForm .= '<p class="error">' . $error . '</p>';
        }

        return $loginForm . '</form>';
    }

    private function getLogoutForm($attr)
    {
        $currentUser = wp_get_current_user();
        $error = $this->args['controller']->getErrorMessage();
        $logoutLabel = WA_Utils::sanitizeString($attr['logout_label']);
        $logoutArgs = $this->args['controller']->getLogoutArgs();

        if (empty($logoutLabel))
        {
            $logoutLabel = $this->defaults['logoutLabel'];
        }

        $logoutForm = '<form method="get">';
        $logoutForm .= '<p>' . esc_html($currentUser->display_name) . '</p>';

        foreach ($logoutArgs as $key => $value)
        {
            $logoutForm .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
        }

        $logoutForm .= '<input type="submit" name="' . esc_attr($this->args['actionId']) . '" class="button button-primary" value="' . esc_attr($logoutLabel)
            . '" title="' . esc_attr($logoutLabel) . '" />';

        if (!empty($error))
        {
            $logoutForm .= '<p class="error">' . $error . '</p>';
        }

        return $logoutForm . '</form>';
    }
} 