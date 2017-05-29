<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Variable
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmailVariable
{
    public function getSettings()
    {
        return craft()->commerceDigestEmail_settings->getSettings();
    }
    
    public function getSales()
    {
        return craft()->commerceDigestEmail_sales->getSales();
    }
    
    public function getPotentialSales()
    {
        return craft()->commerceDigestEmail_potentialSales->getPotentialSales();
    }
    
    public function getDates()
    {
        return craft()->commerceDigestEmail_dates->getDates();
    }
}