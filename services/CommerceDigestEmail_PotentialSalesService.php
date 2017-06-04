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
        $potential_sales->quantity = $this->getActiveCartsQuantity();
        $potential_sales->total = $this->getActiveCartsTotal();
        
        $potential_sales->daysActivity = (object) array();
        $potential_sales->daysActivity->all = $this->getActiveCartsDates();
        $potential_sales->daysActivity->weekDays = $this->getActiveCartsWeekDaysActivity();
        $potential_sales->daysActivity->weekDaysMoney = $this->getActiveCartsWeekDaysActivityMoney();
        $potential_sales->daysActivity->mostActiveWeekDay = $this->getActiveCartsWeekDaysMostActive();
        $potential_sales->daysActivity->mostActiveWeekDayPercentage = $this->getActiveCartsWeekDaysMostActivePercentage();
        $potential_sales->daysActivity->mostActiveWeekDayString = $this->getActiveCartsWeekDaysMostActiveString();
        
        return $potential_sales;
    }
    
    /**
     * Get all active carts in desired period
     */
    public function getActiveCarts()
    {
        $query = craft()->db->createCommand()
                ->select('id, totalPrice, dateCreated')
                ->from('commerce_orders')
                ->where('isCompleted != 1')
                ->andWhere('dateUpdated >= "' . craft()->commerceDigestEmail_dates->getStart()->format('Y-m-d H:i') . '"')
                ->andWhere('dateUpdated <= "' . craft()->commerceDigestEmail_dates->getEnd()->format('Y-m-d H:i') . '"')
                ->limit(-1)
                ->queryAll();
                
        return $query;
    }
    
    /**
     * Calculate the amount of active carts
     */
    public function getActiveCartsQuantity()
    {
        $active_carts_quantity = count($this->getActiveCarts());
        
        return (int) $active_carts_quantity;
    }
    
    /**
     * Calculate the sum of all totalPrice values
     */
    public function getActiveCartsTotal()
    {
        $active_carts = $this->getActiveCarts();
        if(!count($active_carts)) {
            return (int) '0';
        }
        
        $active_carts_total_column = array_column($active_carts, 'totalPrice');
        
        $active_carts_total = array_sum($active_carts_total_column);
        
        return (float) $active_carts_total;
    }
    
    /**
     * For each dateUpdated (if dateCreated is in desired period)
     * add the dateCreated in an array
     */
    public function getActiveCartsDates()
    {
        $active_carts = $this->getActiveCarts();
        if(!count($active_carts)) {
            return null;
        }
        
        $active_dates = array();
        foreach($active_carts as $cart) {
            $dateCreated = date('Y-m-d', strtotime($cart['dateCreated']));
            $totalPrice = $cart['totalPrice'];
            
            if($dateCreated >= craft()->commerceDigestEmail_dates->getStart() && $dateCreated <= craft()->commerceDigestEmail_dates->getEnd()) {
                $active_dates_item = (object) array();
                $active_dates_item->dateCreated = $dateCreated;
                $active_dates_item->totalPrice = (float) $totalPrice;
                $active_dates[] = $active_dates_item;
            }
        }
        
        return $active_dates;
    }
    
    
    /**
     * Group dates by week day and sort them by week day
     */
    public function getActiveCartsWeekDaysActivity()
    {
        $active_dates = $this->getActiveCartsDates();
        if(!count($active_dates)) {
            return null;
        }
        
        $active_week_days = array();
        foreach($active_dates as $date) {
            $day_of_the_week = date('N', strtotime($date->dateCreated));
            $active_week_days[$day_of_the_week][] = $date;
        }
        ksort($active_week_days);
        
        return $active_week_days;
    }
    
    /**
     * Get an array of the money spent per week day
     */
    public function getActiveCartsWeekDaysActivityMoney()
    {
        $active_week_days = $this->getActiveCartsWeekDaysActivity();
        if(!count($active_week_days)) {
            return null;
        }
        
        $active_week_days_money = array();
        foreach($active_week_days as $key => $day) {
            $day_total_column = array_column($day, 'totalPrice');
            $day_total = array_sum($day_total_column);
            $active_week_days_money[$key] = $day_total;
        }
        
        return $active_week_days_money;
    }
    
    /**
     * Get an array of the most active week days
     */
    public function getActiveCartsWeekDaysMostActive()
    {
        $active_week_days = $this->getActiveCartsWeekDaysActivity();
        $active_week_days_money = $this->getActiveCartsWeekDaysActivityMoney();
        if(!count($active_week_days) || !count($active_week_days_money)) {
            return null;
        }
        
        $highest = (object) array();
        $highest->day = 0;
        $highest->money = 0;
        $most_active_week_day_transactions = array();
        foreach($active_week_days as $key => $day) {
            $day_count = count($day);
            // If new highest value, put it in the array, with the same key
            if($day_count > $highest->day) {
                $highest->day = $day_count;
                $highest->money = $active_week_days_money[$key];
                $most_active_week_day_transactions = $day;
            // If same as highest value, compare the totalPrice sum and add it only if 
            }else if($day_count == $highest->day) {
                if($active_week_days_money[$key] > $highest->money) {    
                    $most_active_week_day_transactions = $day;
                } 
            }
        }
        
        return $most_active_week_day_transactions;
    }
    
    /**
     * Get the percentage of carts created per week day
     */
    public function getActiveCartsWeekDaysMostActivePercentage()
    {
        $most_active_week_day_carts = $this->getActiveCartsWeekDaysMostActive();
        $active_carts_quantity = $this->getActiveCartsQuantity();
        if(!count($most_active_week_day_carts)) {
            return (int) '0';
        }
        
        $most_active_week_days_percentage = count($most_active_week_day_carts) / $active_carts_quantity * 100;
        $most_active_week_days_percentage = number_format($most_active_week_days_percentage, 2);
        
        return (float) $most_active_week_days_percentage;
    }
    
    /**
     * Get the name of the most active day
     */
    public function getActiveCartsWeekDaysMostActiveString()
    {
        $most_active_week_day_carts = $this->getActiveCartsWeekDaysMostActive();
        if(!count($most_active_week_day_carts)) {
            return null;
        }
        
        $day_string = date('l', strtotime($most_active_week_day_carts[0]->dateCreated));
        
        return $day_string;
    }

}