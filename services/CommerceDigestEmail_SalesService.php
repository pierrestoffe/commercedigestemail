<?php
/**
 * Commerce Digest Email plugin for Craft CMS
 *
 * CommerceDigestEmail Sales Service
 *
 * @author    Pierre Stoffe
 * @copyright Copyright (c) 2017 Pierre Stoffe
 * @link      https://pierrestoffe.be
 * @package   CommerceDigestEmail
 * @since     1.0.0
 */

namespace Craft;

class CommerceDigestEmail_SalesService extends BaseApplicationComponent
{
    public $frequency;
    
    public function __construct()
    {
        $this->frequency = craft()->commerceDigestEmail_settings->getSetting('frequency');
    }
    
    public function getSales()
    {
        $sales = (object) array();
        
        $sales->transactions = $this->getTransactions();
        $sales->purchaseTransactions = $this->getPurchaseTransactions();
        $sales->refundedTransactions = $this->getRefundedTransactions();
        $sales->mostExpensiveTransaction = $this->getMostExpensiveTransaction();
        $sales->orders = $this->getOrders();
        
        $sales->transactionsStats = (object) array();
        $sales->transactionsStats->subtotal = $this->getTransactionsPurchaseSubtotal();
        $sales->transactionsStats->refunded = $this->getTransactionsRefundSubtotal();
        $sales->transactionsStats->total = $this->getTransactionsTotal();
        
        $sales->mostExpensiveTransactionStats = (object) array();
        $sales->mostExpensiveTransactionStats->total = $this->getMostExpensiveTransactionTotal();
        
        $sales->ordersStats = (object) array();
        $sales->ordersStats->quantity = $this->getOrdersQuantity();
        
        return $sales;
    }
    
    public function getStart()
    {
        $start = ($this->frequency == 'weekly' ? craft()->commerceDigestEmail_dates->getFirstDayOfTheWeek() : craft()->commerceDigestEmail_dates->getFirstDayOfTheMonth());
        
        return $start;
    }
    
    public function getEnd()
    {
        $end = ($this->frequency == 'weekly' ? craft()->commerceDigestEmail_dates->getEndDayOfTheWeek() : craft()->commerceDigestEmail_dates->getEndDayOfTheMonth());
        
        return $end;
    }
    
    public function getOrders()
    {
        $transactions_ids = array_column($this->getTransactions(), 'orderId');
        
        $query = craft()->db->createCommand()
                ->select('id, shippingAddressId, baseShippingCost, paymentMethodId, totalPrice')
                ->from('commerce_orders')
                ->where('isCompleted = 1')
                ->andWhere('id IN (' . join($transactions_ids, ', ') . ')')
                ->limit(-1)
                ->queryAll();
                
        return $query;
    }
    
    public function getTransactions()
    {
        $query = craft()->db->createCommand()
                ->select('id, paymentMethodId, amount, paymentAmount, orderId, type')
                ->from('commerce_transactions')
                ->where('type IN ("purchase", "refund")')
                ->andWhere('status = "success"')
                ->andWhere('dateCreated >= "' . $this->getStart() . '"')
                ->limit(-1)
                ->queryAll();
                
        return $query;
    }
    
    public function getPurchaseTransactions()
    {
        $transactions = $this->getTransactions();
        if(!count($transactions)) {
            return null;
        }
        
        $purchase_transactions = array_filter($transactions, function ($row) {
            return ($row['type'] == 'purchase');
        });
        
        return $purchase_transactions;
    }
    
    public function getRefundedTransactions()
    {
        $transactions = $this->getTransactions();
        if(!count($transactions)) {
            return null;
        }
        
        $refunded_transactions = array_filter($transactions, function ($row) {
            return ($row['type'] == 'refund');
        });
        
        return $refunded_transactions;
    }
    
    public function getTransactionsPurchaseSubtotal()
    {
        $purchase_transactions = $this->getPurchaseTransactions();
        if(!count($purchase_transactions)) {
            return null;
        }
        
        $transactions_subtotal_column = array_column($purchase_transactions, 'paymentAmount');
        $transactions_subtotal = array_sum($transactions_subtotal_column);
        
        return $transactions_subtotal;
    }
    
    public function getTransactionsRefundSubtotal()
    {
        $refunded_transactions = $this->getRefundedTransactions();
        if(!count($refunded_transactions)) {
            return null;
        }
        
        $refunded_transactions_total_column = array_column($refunded_transactions, 'paymentAmount');
        $refunded_transactions_total = array_sum($refunded_transactions_total_column);
        
        return $refunded_transactions_total;
    }
    
    public function getTransactionsTotal()
    {
        $purchase_transactions_subtotal = $this->getTransactionsPurchaseSubtotal();
        $refunded_transactions_subtotal = $this->getTransactionsRefundSubtotal();
        if(!count($purchase_transactions_subtotal) || !count($refunded_transactions_subtotal)) {
            return null;
        }
        
        $transactions_total = $purchase_transactions_subtotal - $refunded_transactions_subtotal;
        
        return $transactions_total;
    }
    
    public function getOrdersQuantity()
    {
        $transactions_quantity = count($this->getOrders());
        
        return $transactions_quantity;
    }
    
    public function getMostExpensiveTransaction()
    {
        $purchase_transactions = $this->getPurchaseTransactions();
        if(!count($purchase_transactions)) {
            return null;
        }
        
        $most_expensive_transaction_column = array_reduce($purchase_transactions, function ($current, $highest) {
            return $current['paymentAmount'] > $highest['paymentAmount'] ? $current : $highest;
        });
        
        return $most_expensive_transaction_column;
    }
    
    public function getMostExpensiveTransactionTotal()
    {
        $most_expensive_transaction = $this->getMostExpensiveTransaction();
        if(!count($most_expensive_transaction)) {
            return null;
        }
        
        $most_expensive_transaction_total = $this->getMostExpensiveTransaction()['paymentAmount'];
        
        return $most_expensive_transaction_total;
    }

}