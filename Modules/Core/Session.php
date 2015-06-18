<?php

class WA_Modules_Core_Session implements WA_Modules_Interfaces_ISession
{
    private $dataKey;
    private $data = array();

    public function __construct($dataKey)
    {
        $this->dataKey = $dataKey;

        add_action('init', array($this, 'init'), 1);
        register_shutdown_function(array($this, 'save'));
    }

    public function init()
    {
        if( !session_id())
        {
            session_start();
        }

        $sessionData = (isset($_SESSION[$this->dataKey]) && is_array($_SESSION[$this->dataKey])) ? $_SESSION[$this->dataKey] : array();
        $this->data = array_merge($sessionData, $this->data);
    }

    public function get($moduleId, $dataKey)
    {
        $moduleId = WA_Utils::sanitizeKey($moduleId);
        $dataKey = WA_Utils::sanitizeKey($dataKey);

        return (isset($this->data[$moduleId]) && isset($this->data[$moduleId][$dataKey])) ? $this->data[$moduleId][$dataKey] : null;
    }

    public function set($moduleId, $dataKey, $value)
    {
        $moduleId = WA_Utils::sanitizeKey($moduleId);
        $this->createModuleData($moduleId);

        $dataKey = WA_Utils::sanitizeKey($dataKey);
        $this->data[$moduleId][$dataKey] = $value;
    }

    public function remove($moduleId, $dataKey)
    {
        $moduleId = WA_Utils::sanitizeKey($moduleId);
        $dataKey = WA_Utils::sanitizeKey($dataKey);

        if (isset($this->data[$moduleId]) && isset($this->data[$moduleId][$dataKey]))
        {
            unset($this->data[$moduleId][$dataKey]);
        }
    }

    public function clean()
    {
        $this->data = array();
    }

    public function save()
    {
        if (isset($_SESSION))
        {
            $_SESSION[$this->dataKey] = $this->data;
        }
    }

    public function __destruct()
    {
        $this->save();
    }

    private function createModuleData($moduleId)
    {
        if (!isset($this->data[$moduleId]) || !is_array($this->data[$moduleId]))
        {
            $this->data[$moduleId] = array();
        }
    }
}