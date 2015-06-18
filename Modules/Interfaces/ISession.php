<?php

interface WA_Modules_Interfaces_ISession
{
    public function get($moduleId, $dataKey);
    public function set($moduleId, $dataKey, $value);
    public function remove($moduleId, $dataKey);
    public function clean();
    public function save();
}