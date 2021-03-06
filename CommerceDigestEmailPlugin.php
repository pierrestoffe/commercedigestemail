<?php
/**
 * Commerce digest email plugin for Craft CMS
 *
 * Send a weekly/monthly digest of your Craft Commerce e-sho's activity
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmailPlugin extends BasePlugin
{
    public function init()
    {
        parent::init();
    }

    public function getName()
    {
         return Craft::t('Commerce digest email');
    }

    public function getDescription()
    {
        return Craft::t('Send a weekly/monthly digest of your Craft Commerce e-shop\'s activity');
    }

    public function getDocumentationUrl()
    {
        return 'https://github.com/pierrestoffe/commercedigestemail/blob/master/README.md';
    }

    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/pierrestoffe/commercedigestemail/master/releases.json';
    }

    public function getVersion()
    {
        return '1.0.0';
    }

    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    public function getDeveloper()
    {
        return 'Pierre Stoffe';
    }

    public function getDeveloperUrl()
    {
        return 'https://pierrestoffe.be';
    }

    public function hasCpSection()
    {
        return true;
    }

    /**
     * Called right before your plugin’s row gets stored in the plugins database table, and tables have been created
     * for it based on its records.
     */
    public function onBeforeInstall()
    {
    }

    /**
     * Called right after your plugin’s row has been stored in the plugins database table, and tables have been
     * created for it based on its records.
     */
    public function onAfterInstall()
    {
    }

    /**
     * Called right before your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onBeforeUninstall()
    {
    }

    /**
     * Called right after your plugin’s record-based tables have been deleted, and its row in the plugins table
     * has been deleted.
     */
    public function onAfterUninstall()
    {
    }

    /**
     * Defines the attributes that model your plugin’s available settings.
     *
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'someSetting' => array(AttributeType::String, 'label' => 'Some Setting', 'default' => ''),
        );
    }

    /**
     * Returns the HTML that displays your plugin’s settings.
     *
     * @return mixed
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('commercedigestemail/CommerceDigestEmail_Settings', array(
           'settings' => $this->getSettings()
       ));
    }

    /**
     * If you need to do any processing on your settings’ post data before they’re saved to the database, you can
     * do it with the prepSettings() method:
     *
     * @param mixed $settings  The Widget's settings
     *
     * @return mixed
     */
    public function prepSettings($settings)
    {
        // Modify $settings here...

        return $settings;
    }

}