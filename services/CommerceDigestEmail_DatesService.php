<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Dates Service
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_DatesService extends BaseApplicationComponent
{
    public $frequency;

    public function __construct()
    {
        $this->frequency = craft()->commerceDigestEmail_settings->getSetting('frequency');
        $this->setTimezone();
    }
    
    public function getDates()
    {    
        $dates = (object) array();
        $dates->today = $this->getToday();
        
        $dates->week = (object) array();
        $dates->week->first = $this->getFirstDayOfTheWeek();
        $dates->week->last = $this->getLastDayOfTheWeek();
        
        $dates->month = (object) array();
        $dates->month->first = $this->getFirstDayOfTheMonth();
        $dates->month->last = $this->getLastDayOfTheMonth();
        
        return $dates;
    }
    
    public function setTimezone()
    {
        date_default_timezone_set('GMT');
    }
    
    public function getToday()
    {
        return date('Y-m-d');
    }
    
    public function getFirstDayOfTheMonth()
    {
        return date('Y-m-01');
    }
    
    public function getLastDayOfTheMonth()
    {
        return date('Y-m-t');
    }
    
    public function getFirstDayOfTheWeek()
    {
        return date('Y-m-d', strtotime('monday this week'));
    }
    
    public function getLastDayOfTheWeek()
    {
        return date('Y-m-d', strtotime('sunday this week'));
    }
    
    public function getStart()
    {
        $start = ($this->frequency == 'monthly' ? $this->getFirstDayOfTheMonth() : $this->getFirstDayOfTheWeek());
        
        return $start;
    }
    
    public function getEnd()
    {
        $end = ($this->frequency == 'monthly' ? $this->getLastDayOfTheMonth() : $this->getLastDayOfTheWeek());
        
        return $end;
    }

}