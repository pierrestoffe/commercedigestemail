<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Settings Model
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_SettingsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        
        $cfSettings = $this->getCFSettings();
        $cpSettings = $this->getCPSettings();
        $settings = array_merge($cpSettings, $cfSettings);
        
        foreach($settings as $key => $name) {
            $this[$key] = $name;
        }
    }
    
    protected function getCFSettings()
    {
        $settings = array();
        
        foreach($this->attributeNames() as $name) {
            if(craft()->config->exists($name, 'commercedigestemail')) {
                $settings[$name] = craft()->config->get($name, 'commercedigestemail');
            }
        }
        
        return $settings;
    }
    
    protected function getCPSettings()
    {
        $settings = array();
        
        $cpSettings = craft()->plugins->getPlugin('commerceDigestEmail')->getSettings();
        foreach ($cpSettings as $key => $name) {
            $settings[$key] = craft()->plugins->getPlugin('commerceDigestEmail')->getSettings()[$key];
        }
        
        return $settings;
    }

    protected function defineAttributes()
    {
        $attributes = array(
            'customTemplate' => array(AttributeType::String, 'default' => null),
            'enabled' => array(AttributeType::Bool, 'default' => true)
        );
        
        $cpSettingsDefinition = craft()->plugins->getPlugin('commerceDigestEmail')->getSettingsDefinition();
        foreach($cpSettingsDefinition as $key => $name) {
            $attributes[$key] = $name;
        }
        
        return $attributes;
    }

    public function getSetting($name)
    {
        return $this[$name];
    }

}