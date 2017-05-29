<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Potential Sales Service
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_PotentialSalesService extends BaseApplicationComponent
{
    public function getPotentialSales()
    {
        $potential_sales = (object) array();
        
        $potential_sales->carts = $this->getActiveCarts();
        
        $potential_sales->cartsStats = (object) array();
        $potential_sales->cartsStats->quantity = $this->getActiveCartsQuantity();
        
        $potential_sales->cartsStats->daysActivity = (object) array();
        $potential_sales->cartsStats->daysActivity->all = $this->getActiveCartsDates();
        $potential_sales->cartsStats->daysActivity->weekDays = $this->getActiveCartsWeekDaysActivity();
        $potential_sales->cartsStats->daysActivity->mostActiveWeekDays = $this->getActiveCartsWeekDaysMostActive();
        $potential_sales->cartsStats->daysActivity->mostActiveWeekDaysString = $this->getActiveCartsWeekDaysMostActiveString();
        
        return $potential_sales;
    }
    
    public function getActiveCarts()
    {
        $query = craft()->db->createCommand()
                ->select('id, totalPrice, dateCreated')
                ->from('commerce_orders')
                ->where('isCompleted != 1')
                ->andWhere('dateUpdated >= "' . craft()->commerceDigestEmail_dates->getStart() . '"')
                ->limit(-1)
                ->queryAll();
                
        return $query;
    }
    
    public function getActiveCartsQuantity()
    {
        $active_carts_quantity = count($this->getActiveCarts());
        
        return $active_carts_quantity;
    }
    
    public function getActiveCartsDates()
    {
        $active_carts = $this->getActiveCarts();
        if(!count($active_carts)) {
            return null;
        }
        
        $active_dates = array();
        foreach($active_carts as $cart) {
            $dateCreated = date('Y-m-d', strtotime($cart['dateCreated']));
            
            if($dateCreated >= craft()->commerceDigestEmail_dates->getStart() && $dateCreated <= craft()->commerceDigestEmail_dates->getEnd()) {
                $active_dates[] = $dateCreated;
            }
        }
        
        return $active_dates;
    }
    
    public function getActiveCartsWeekDaysActivity()
    {
        $active_dates = $this->getActiveCartsDates();
        if(!count($active_dates)) {
            return null;
        }
        
        $active_week_days = array();
        foreach($active_dates as $date) {
            $day_of_the_week = date('N', strtotime($date));
            $active_week_days[$day_of_the_week][] = $date;
        }
        ksort($active_week_days);
        
        return $active_week_days;
    }
    
    public function getActiveCartsWeekDaysMostActive()
    {
        $active_week_days = $this->getActiveCartsWeekDaysActivity();
        if(!count($active_week_days)) {
            return null;
        }
        
        $highest = 0;
        $most_active_week_days = array();
        foreach($active_week_days as $key => $day) {
            $day_count = count($day);
            if($day_count == $highest) {
                $most_active_week_days[$key] = $day;
            } else if($day_count > $highest) {
                $highest = $day_count;
                $most_active_week_days = array();
                $most_active_week_days[$key] = $day;
            }
        }
        ksort($most_active_week_days);
        
        return $most_active_week_days;
    }
    
    public function getActiveCartsWeekDaysMostActiveString()
    {
        $most_active_week_days = $this->getActiveCartsWeekDaysMostActive();
        if(!count($most_active_week_days)) {
            return null;
        }
        
        $most_active_week_days_string = array();
        foreach($most_active_week_days as $key => $day) {
            $day_string = date('l', strtotime($day[0]));
            $most_active_week_days_string[] = $day_string;
        }
        
        return $most_active_week_days_string;
    }

}