<?php

class WA_Modules_Base_ShortCode implements WA_Modules_Interfaces_IShortCode
{
    protected $module;
    protected $shortCodeName;
    protected $args;

    public function __construct(WA_Modules_Interfaces_IModule $module, $shortCodeName, array $args = null)
    {
        $this->module = $module;
        $this->shortCodeName = $shortCodeName;
        $this->args = $args;

        add_shortcode($shortCodeName, array($this, 'render'));
    }

    public function render($attributes, $content, $shortCodeName)
    {
        return '';
    }

    protected function disablePageCache()
    {
        if (!defined('DONOTCACHEPAGE'))
        {
            define('DONOTCACHEPAGE',true);
        }
    }
} 