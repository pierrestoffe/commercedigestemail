<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Settings Service
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_SettingsService extends BaseApplicationComponent
{
    public function getSettings()
    {
        return new CommerceDigestEmail_SettingsModel;
    }
    
    public function getSetting($name)
    {
        $settings = $this->getSettings();
        return $settings[$name];
    }

}