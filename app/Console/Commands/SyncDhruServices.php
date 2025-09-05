<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ServiceCategories;
use App\Models\Service;
use App\Utils\DhruFusionClient;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SyncDhruServices extends Command
{
    protected $signature = 'dhru:sync-services';
    protected $description = 'Sync categories and services from Dhru Fusion API and upsert into DB without duplicates';

    public function handle(): int
    {
        $baseUrl = config('dhru.base_url');
        $username = config('dhru.username');
        $apiKey   = config('dhru.api_key');

        if (!$baseUrl || !$username || !$apiKey) {
            $this->error('Missing Dhru config (config/dhru.php)');
            return self::FAILURE;
        }

        $client = new DhruFusionClient($baseUrl, $username, $apiKey);

        $this->info('Fetching IMEI service list from Dhru...');
        $groups = $client->getImeiServiceList();

        if (empty($groups)) {
            $this->warn('No data returned (empty groups).');
            return self::SUCCESS;
        }

        $stats = [
            'groups_seen' => 0,
            'groups_skipped' => 0,
            'categories_created' => 0,
            'services_created' => 0,
            'services_updated' => 0,
            'service_errors' => 0,
            'category_errors' => 0,
        ];

        foreach ($groups as $groupKey => $group) {
            $stats['groups_seen']++;

            // Normalize and validate group data
            $groupType = is_string($group['GROUPTYPE'] ?? null) ? trim($group['GROUPTYPE']) : (string)($group['GROUPTYPE'] ?? '');
            $groupName = is_string($group['GROUPNAME'] ?? null) ? trim($group['GROUPNAME']) : (string)($group['GROUPNAME'] ?? $groupKey);
            $services  = $group['SERVICES'] ?? [];

            // Filter: only IMEI and SERVER by GROUPTYPE (not GROUPNAME)
            if (!in_array($groupType, ['SERVER','IMEI'], true)) {
                $stats['groups_skipped']++;
                continue;
            }

            // Upsert category with isolated error handling
            try {
                $category = ServiceCategories::firstOrCreate(
                    ['name' => $groupType ?: $groupKey],
                    [
                        'description' => $groupName,
                        'status' => 1,
                    ]
                );
                if ($category->wasRecentlyCreated) {
                    $stats['categories_created']++;
                }
            } catch (\Throwable $e) {
                $stats['category_errors']++;
                Log::warning('Dhru sync: category upsert failed', [
                    'groupKey' => $groupKey,
                    'groupType' => $groupType,
                    'groupName' => $groupName,
                    'error' => $e->getMessage(),
                ]);
                // Skip services for this group to avoid orphaned relations
                continue;
            }

            // Process each service independently so one failure doesn't stop others
            foreach ($services as $srv) {
                try {
                    // Extract fields (keep full text, you already increased lengths)
                    $externalId = $srv['SERVICEID'] ?? ($srv['SERVICECID'] ?? null);
                    $name = is_string($srv['SERVICENAME'] ?? null) ? $srv['SERVICENAME'] : ('Service '.($externalId ?? ''));
                    $credit = $srv['CREDIT'] ?? ($srv['PRICE'] ?? 0);
                    $info = $srv['INFO'] ?? ($srv['DESCRIPTION'] ?? null);
                    $note = is_string($info) ?
                    $info : "";
                    // (is_array($info) ?
                    //  json_encode($info, JSON_UNESCAPED_UNICODE)
                    //  : null);

                    // Find existing by external_id when available, else by (section_id, name)
                    $query = Service::where('section_id', $category->id);
                    if ($externalId) {
                        $query = $query->where('external_id', (int)$externalId);
                    } else {
                        $query = $query->where('name', $name);
                    }
                    $existing = $query->first();

                    if ($existing) {
                        $existing->update([
                            'name' => $name,
                            'external_id' => $externalId ? (int)$externalId : $existing->external_id,
                            'basic_price' => $credit,
                            'price' =>  round(( $credit + ($credit * $category->increase_percentage / 100)),4),
                            'note' => $note,
                            'status' => $existing->status ?? 1,
                            'type' => $existing->type ?? '1',
                            'sale_price' => $credit,
                        ]);
                        $stats['services_updated']++;
                    } else {
                        Service::create([
                            'section_id' => $category->id,
                            'name' => $name,
                            'external_id' => $externalId ? (int)$externalId : null,
                            'basic_price' => $credit,
                            'sale_price' => $credit,
                            'price' =>
                            round(( $credit + ($credit * $category->increase_percentage / 100)),4),
                            'note' => $note,
                            'status' => 1,
                            'type' => '1',
                        ]);
                        $stats['services_created']++;
                    }
                } catch (\Throwable $e) {
                    $stats['service_errors']++;
                    Log::warning('Dhru sync: service upsert failed', [
                        'category_id' => $category->id ?? null,
                        'service_payload' => is_array($srv) ? array_intersect_key($srv, array_flip(['SERVICEID','SERVICECID','SERVICENAME'])) : gettype($srv),
                        'error' => $e->getMessage(),
                    ]);
                    // continue to next service
                }
            }
        }

        // Summary
        $this->info(sprintf(
            'Sync done. Groups: %d (skipped %d), Categories created: %d, Services created: %d, updated: %d, errors: %d(cat) + %d(srv)'.
            '',
            $stats['groups_seen'], $stats['groups_skipped'], $stats['categories_created'],
            $stats['services_created'], $stats['services_updated'],
            $stats['category_errors'], $stats['service_errors']
        ));

        return self::SUCCESS;
    }
}
