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
    public function getSales()
    {
        $sales = (object) array();
        
        $sales->transactions = $this->getTransactions();
        $sales->transactionsIds = $this->getTransactionsIds();
        $sales->purchaseTransactions = $this->getPurchaseTransactions();
        $sales->purchaseTransactionsIds = $this->getPurchaseTransactionsIds();
        $sales->refundedTransactions = $this->getRefundedTransactions();
        $sales->refundedTransactionsIds = $this->getRefundedTransactionsIds();
        $sales->mostExpensiveTransaction = $this->getMostExpensiveTransaction();
        
        $sales->transactionsStats = (object) array();
        $sales->transactionsStats->quantity = $this->getTransactionsQuantity();
        $sales->transactionsStats->subtotal = $this->getTransactionsPurchaseSubtotal();
        $sales->transactionsStats->refunded = $this->getTransactionsRefundSubtotal();
        $sales->transactionsStats->total = $this->getTransactionsTotal();
        
        $sales->mostExpensiveTransactionStats = (object) array();
        $sales->mostExpensiveTransactionStats->total = $this->getMostExpensiveTransactionTotal();
        
        return $sales;
    }
    
    public function getStart()
    {
        $start = ($this->frequency == 'monthly' ? craft()->commerceDigestEmail_dates->getFirstDayOfTheMonth() : craft()->commerceDigestEmail_dates->getFirstDayOfTheWeek());
        
        return $start;
    }
    
    public function getEnd()
    {
        $end = ($this->frequency == 'monthly' ? craft()->commerceDigestEmail_dates->getLastDayOfTheMonth() : craft()->commerceDigestEmail_dates->getLastDayOfTheWeek());
        
        return $end;
    }
    
    public function getTransactions()
    {
        $query = craft()->db->createCommand()
                ->select('id, paymentMethodId, amount, paymentAmount, orderId, type')
                ->from('commerce_transactions')
                ->where('type IN ("purchase", "refund")')
                ->andWhere('status = "success"')
                ->andWhere('dateCreated >= "' . craft()->commerceDigestEmail_dates->getStart() . '"')
                ->limit(-1)
                ->queryAll(); 
                
        return $query;
    }
    
    public function getTransactionsIds()
    {
        $transactions = $this->getTransactions();
        if(!count($transactions)) {
            return null;
        }
        
        $transaction_ids = array_column($transactions, 'orderId');
        
        return $transaction_ids;
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
    
    public function getPurchaseTransactionsIds()
    {
        $purchase_transaction = $this->getPurchaseTransactions();
        if(!count($purchase_transaction)) {
            return null;
        }
        
        $purchase_transaction_ids = array_column($purchase_transaction, 'orderId');
        
        return $purchase_transaction_ids;
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
    
    public function getRefundedTransactionsIds()
    {
        $refunded_transaction = $this->getRefundedTransactions();
        if(!count($refunded_transaction)) {
            return null;
        }
        
        $refunded_transaction_ids = array_column($refunded_transaction, 'orderId');
        
        return $refunded_transaction_ids;
    }
    
    public function getTransactionsQuantity()
    {
        $transactions_quantity = count($this->getTransactions());
        
        return $transactions_quantity;
    }
    
    public function getTransactionsPurchaseSubtotal()
    {
        $purchase_transactions = $this->getPurchaseTransactions();
        if(!count($purchase_transactions)) {
            return (int) '0';
        }
        
        $transactions_subtotal_column = array_column($purchase_transactions, 'paymentAmount');
        $transactions_subtotal = array_sum($transactions_subtotal_column);
        
        return (float) $transactions_subtotal;
    }
    
    public function getTransactionsRefundSubtotal()
    {
        $refunded_transactions = $this->getRefundedTransactions();
        if(!count($refunded_transactions)) {
            return (int) '0';
        }
        
        $refunded_transactions_total_column = array_column($refunded_transactions, 'paymentAmount');
        $refunded_transactions_total = array_sum($refunded_transactions_total_column);
        
        return (float) $refunded_transactions_total;
    }
    
    public function getTransactionsTotal()
    {
        $purchase_transactions_subtotal = $this->getTransactionsPurchaseSubtotal();
        $refunded_transactions_subtotal = $this->getTransactionsRefundSubtotal();
        if(!count($purchase_transactions_subtotal) || !count($refunded_transactions_subtotal)) {
            return (int) '0';
        }
        
        $transactions_total = $purchase_transactions_subtotal - $refunded_transactions_subtotal;
        
        return (float) $transactions_total;
    }
    
    public function getMostExpensiveTransaction()
    {
        $purchase_transactions = $this->getPurchaseTransactions();
        if(!count($purchase_transactions)) {
            return null;
        }
        
        $most_expensive_transaction_column = array_reduce($purchase_transactions, function ($a, $b) {
            return $a['paymentAmount'] > $b['paymentAmount'] ? $a : $b;
        });
        
        return $most_expensive_transaction_column;
    }
    
    public function getMostExpensiveTransactionTotal()
    {
        $most_expensive_transaction = $this->getMostExpensiveTransaction();
        if(!count($most_expensive_transaction)) {
            return (int) '0';
        }
        
        $most_expensive_transaction_total = $this->getMostExpensiveTransaction()['paymentAmount'];
        
        return (float) $most_expensive_transaction_total;
    }

}