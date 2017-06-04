<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Orders Service
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_OrdersService extends BaseApplicationComponent
{    
    public function getOrders()
    {
        $orders = (object) array();
        $orders->all = $this->getOrdersByIds();
        $orders->quantity = $this->getOrdersQuantity();
        
        return $orders;
    }
    
    public function getOrdersByIds($ids = null)
    {
        if(!count($ids)) {
            $ids = craft()->commerceDigestEmail_sales->getTransactionsIds();
        }
        
        if(!count($ids)) {
            return null;
        }
        
        $query = craft()->db->createCommand()
                ->select('id, shippingAddressId, baseShippingCost, paymentMethodId, totalPrice')
                ->from('commerce_orders')
                ->where('isCompleted = 1')
                ->andWhere('id IN (' . join($ids, ', ') . ')')
                ->limit(-1)
                ->queryAll();
                
        return $query;
    }
    
    public function getOrdersQuantity()
    {
        $orders = $this->getOrdersByIds();
        if(!count($orders)) {
            return (int) 0;
        }
        
        $orders_quantity = count($orders);
        
        return (int) $orders_quantity;
    }

}