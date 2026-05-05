<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Services\IssueInsightService;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(IssueInsightService::class);

        $items = [
            [
                'title' => 'Checkout API timeout on peak hours',
                'description' => 'Payment confirmations fail intermittently between 11:00 and 13:00 causing repeated customer retries.',
                'priority' => 'high',
                'category' => 'payments',
                'status' => 'open',
            ],
            [
                'title' => 'Unable to login after password reset',
                'description' => 'Several users report account lock after resetting password from mobile app.',
                'priority' => 'medium',
                'category' => 'authentication',
                'status' => 'in_progress',
            ],
            [
                'title' => 'CSV export missing timezone field',
                'description' => 'Operations team cannot reconcile shipment records because timezone is absent in export output.',
                'priority' => 'low',
                'category' => 'reporting',
                'status' => 'open',
            ],
        ];

        foreach ($items as $item) {
            $insight = $service->generate($item['title'], $item['description'], $item['priority']);
            Issue::query()->create([
                ...$item,
                'summary' => $insight['summary'],
                'suggested_next_action' => $insight['next_action'],
                'is_escalated' => in_array($item['priority'], ['high', 'critical'], true),
            ]);
        }
    }
}
