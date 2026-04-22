<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Review;
use App\Models\DiscountUsage;
use App\Models\ReviewLike;
use App\Models\CustomerMessage;

class CheckRelations extends Command
{
    protected $signature = 'check:relations';

    protected $description = 'Manual checks for relations after model changes (read-only)';

    public function handle()
    {
        $this->info('Starting relation checks (read-only)');

        $this->checkOrders();
        $this->checkReviews();
        $this->checkDiscountUsages();
        $this->checkReviewLikes();
        $this->checkCustomerMessages();

        $this->info('Relation checks completed.');

        return 0;
    }

    protected function checkOrders()
    {
        $this->line('\n[Orders]');
        $orders = Order::limit(5)->get();
        if ($orders->isEmpty()) {
            $this->line('  No orders found');
            return;
        }

        foreach ($orders as $order) {
            try {
                $cust = $order->customer; // accessor may return Customer or User or null
                $type = $cust ? get_class($cust) : 'null';
                $userId = $cust ? ($cust->user_id ?? ($cust->id ?? 'n/a')) : 'null';
                $this->line("  Order {$order->id}: customer -> {$type}, user_id={$userId}");
            } catch (\Exception $e) {
                $this->error("  Order {$order->id}: ERROR - " . $e->getMessage());
            }
        }
    }

    protected function checkReviews()
    {
        $this->line('\n[Reviews]');
        $rows = Review::limit(5)->get();
        if ($rows->isEmpty()) {
            $this->line('  No reviews found');
            return;
        }

        foreach ($rows as $r) {
            try {
                $cust = $r->customer; // expected to be User (we changed)
                $type = $cust ? get_class($cust) : 'null';
                $id = $cust ? ($cust->id ?? 'n/a') : 'null';
                $this->line("  Review {$r->id}: customer -> {$type}, id={$id}");
            } catch (\Exception $e) {
                $this->error("  Review {$r->id}: ERROR - " . $e->getMessage());
            }
        }
    }

    protected function checkDiscountUsages()
    {
        $this->line('\n[DiscountUsages]');
        $rows = DiscountUsage::limit(5)->get();
        if ($rows->isEmpty()) {
            $this->line('  No discount usages found');
            return;
        }

        foreach ($rows as $r) {
            try {
                $cust = $r->customer; // expected to be User per patch
                $type = $cust ? get_class($cust) : 'null';
                $id = $cust ? ($cust->id ?? 'n/a') : 'null';
                $this->line("  DiscountUsage {$r->id}: customer -> {$type}, id={$id}");
            } catch (\Exception $e) {
                $this->error("  DiscountUsage {$r->id}: ERROR - " . $e->getMessage());
            }
        }
    }

    protected function checkReviewLikes()
    {
        $this->line('\n[ReviewLikes]');
        $rows = ReviewLike::limit(5)->get();
        if ($rows->isEmpty()) {
            $this->line('  No review likes found');
            return;
        }

        foreach ($rows as $r) {
            try {
                $cust = $r->customer; // expected to be Customer per migration
                $type = $cust ? get_class($cust) : 'null';
                $id = $cust ? ($cust->id ?? 'n/a') : 'null';
                $this->line("  ReviewLike {$r->id}: customer -> {$type}, id={$id}");
            } catch (\Exception $e) {
                $this->error("  ReviewLike {$r->id}: ERROR - " . $e->getMessage());
            }
        }
    }

    protected function checkCustomerMessages()
    {
        $this->line('\n[CustomerMessages]');
        $rows = CustomerMessage::limit(5)->get();
        if ($rows->isEmpty()) {
            $this->line('  No customer messages found');
            return;
        }

        foreach ($rows as $r) {
            try {
                $cust = $r->customer; // constrained to users in migration
                $type = $cust ? get_class($cust) : 'null';
                $id = $cust ? ($cust->id ?? 'n/a') : 'null';
                $this->line("  CustomerMessage {$r->id}: customer -> {$type}, id={$id}");
            } catch (\Exception $e) {
                $this->error("  CustomerMessage {$r->id}: ERROR - " . $e->getMessage());
            }
        }
    }
}
