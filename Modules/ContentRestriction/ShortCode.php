<?php

class WA_Modules_ContentRestriction_ShortCode extends WA_Modules_Base_ShortCode
{
    const EXCLUDE_LOGIC_PREFIX = 'not:';
    const ROLES_DELIMITER = ',';
    const ENCODED_ROLES_DELIMITER = '%2c';
    const ROLE_ATTR_KEY = 'roles';
    const ROLES_ATTR_MAX_LENGTH = 4096;
    const MESSAGE_ATTR_KEY = 'message';
    const MESSAGE_ATTR_MAX_LENGTH = 4096;
    const LOGIN_LABEL_ATTR = 'login_label';

    private $currentUserRoles;

    public function render($attributes, $content, $shortCodeName)
    {
        $this->disablePageCache();

        $attr = shortcode_atts
        (
            array(
                self::ROLE_ATTR_KEY => '',
                self::MESSAGE_ATTR_KEY => '',
                self::LOGIN_LABEL_ATTR => ''
            ),
            $attributes,
            $shortCodeName
        );

        if (!is_user_logged_in())
        {
            return WA_Utils::sanitizeString($attr[self::MESSAGE_ATTR_KEY], self::MESSAGE_ATTR_MAX_LENGTH)
                . $this->module->getWaLoginForm(array(self::LOGIN_LABEL_ATTR => WA_Utils::sanitizeString($attr[self::LOGIN_LABEL_ATTR]))
            );
        }

        $rolesAttr = wp_specialchars_decode(WA_Utils::sanitizeString($attr[self::ROLE_ATTR_KEY], self::ROLES_ATTR_MAX_LENGTH));

        if (empty($rolesAttr)) { return $this->args['accessDeniedMessage']; }

        if (!WA_Utils::isNotEmptyArray($this->currentUserRoles))
        {
            $this->currentUserRoles = $this->getCurrentUserRoles();
        }

        if (!WA_Utils::isNotEmptyArray($this->currentUserRoles)) { return $this->args['accessDeniedMessage']; }

        $attrRoles = $this->getAttrRoles($rolesAttr);

        if (!WA_Utils::isNotEmptyArray($attrRoles)) { return $this->args['accessDeniedMessage']; }

        $suitableRoles = array_intersect($this->currentUserRoles, $attrRoles);

        return (count($suitableRoles) > 0) ? $content : $this->args['accessDeniedMessage'];
    }

    public function normalizeRoleName($roleName)
    {
        return strtolower(trim($roleName));
    }

    public function normalizeAttrRoleName($roleName)
    {
        return str_replace(self::ENCODED_ROLES_DELIMITER, self::ROLES_DELIMITER, trim($roleName));
    }

    private function getCurrentUserRoles()
    {
        $wpUser = wp_get_current_user();

        if (!WA_Utils::isNotEmptyArray($wpUser->roles)) { return $wpUser->roles; }

        $waContactMetaData = new WA_Modules_Base_WaContact_MetaData(0, $wpUser);

        if (!$waContactMetaData->isValid()) { return $wpUser->roles; }

        $levelId = $waContactMetaData->levelId;

        if (empty($levelId) || $waContactMetaData->isMembershipStatusActive()) { return $wpUser->roles; }

        $roles = array();

        foreach ($wpUser->roles as $roleId)
        {
            if ($roleId == $this->module->getWaRoleId($levelId))
            {
                $waDefaultRole = $this->module->getWaDefaultRole();
                $roleId = $waDefaultRole->name;
            }

            $roles[] = $roleId;
        }

        return array_unique($roles);
    }

    private function getAttrRoles($rolesAttr)
    {
        $rolesAttr = strtolower($rolesAttr);
        $excludeLogicPrefix = strtolower(self::EXCLUDE_LOGIC_PREFIX);
        $excludeLogicPrefixLength = strlen($excludeLogicPrefix);

        if (substr($rolesAttr, 0, $excludeLogicPrefixLength) == $excludeLogicPrefix)
        {
            $rolesAttr = substr($rolesAttr, $excludeLogicPrefixLength);
            $isExcludeLogic = true;
        }
        else
        {
            $isExcludeLogic = false;
        }

        $attrRolesNames = explode(self::ROLES_DELIMITER, $rolesAttr);

        if (!WA_Utils::isNotEmptyArray($attrRolesNames)) { return array(); }

        $attrRolesNames = array_map(array($this, 'normalizeAttrRoleName'), $attrRolesNames);

        return $this->getAttrRolesByNames($attrRolesNames, $isExcludeLogic);
    }

    private function getAttrRolesByNames($attrRolesNames, $isExcludeLogic)
    {
        $wpRoles = $this->getWpRoles();

        if (!WA_Utils::isNotEmptyArray($wpRoles->role_names)) { return array(); }

        $wpRolesNames = array_map(array($this, 'normalizeRoleName'), $wpRoles->role_names);
        $result = $isExcludeLogic ? array_diff($wpRolesNames, $attrRolesNames) : array_intersect($wpRolesNames, $attrRolesNames);

        return array_keys($result);
    }

    private function getWpRoles()
    {
        global $wp_roles;

        if (!isset($wp_roles))
        {
            $wp_roles = new WP_Roles();
        }

        return $wp_roles;
    }
} 