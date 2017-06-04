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
    public $timezone;
    public $format;

    public function __construct()
    {
        $this->frequency = craft()->commerceDigestEmail_settings->getSetting('frequency');
        $this->timezone = 'GMT';
        $this->format = 'Y-m-d';
        $this->setTimezone();
    }
    
    public function getDates()
    {
        $dates = (object) array();
        $dates->today = $this->getToday();
        $dates->start = $this->getStart();
        $dates->end = $this->getEnd();
        $dates->frequency = $this->frequency;
        
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
        date_default_timezone_set($this->timezone);
    }
    
    public function getToday()
    {
        return DateTime::createFromString(date($this->format . ' H:i', strtotime('now')));
    }
    
    public function getFirstDayOfTheMonth()
    {
        return DateTime::createFromString(date($this->format . ' 00:00:00', strtotime('first day of this month')));
    }
    
    public function getLastDayOfTheMonth()
    {
        return DateTime::createFromString(date($this->format . ' 23:59:59', strtotime('last day of this month')));
    }
    
    public function getFirstDayOfTheWeek()
    {
        return DateTime::createFromString(date($this->format . ' 00:00:00', strtotime('monday this week')));
    }
    
    public function getLastDayOfTheWeek()
    {
        return DateTime::createFromString(date($this->format . ' 23:59:59', strtotime('sunday this week')));
    }
    
    public function getStart()
    {
        $start = ($this->frequency == 'monthly' ? $this->getFirstDayOfTheMonth() : $this->getFirstDayOfTheWeek());
        
        return $start;
    }
    
    public function getEnd()
    {
        $end = ($this->frequency == 'monthly' ? $this->getLastDayOfTheMonth() : $this->getLastDayOfTheWeek());
        
        if($this->getToday() < $end) {
            $end = $this->getToday();
        }
        
        return $end;
    }

}