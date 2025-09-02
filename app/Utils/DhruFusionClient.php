<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DhruFusionClient
{
    private string $baseUrl;
    private string $username;
    private string $apiKey;

    public function __construct(string $baseUrl, string $username, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->username = $username;
        $this->apiKey = $apiKey;
    }

    /**
     * Perform a Dhru API request
     * @param string $action
     * @param array $parameters
     * @return array [success => bool, data => mixed, error => string|null]
     */
    public function request(string $action, array $parameters = []): array
    {
        $payload = [
            'username' => $this->username,
            'apiaccesskey' => $this->apiKey,
            'action' => $action,
            'parameters' => base64_encode(json_encode($parameters)),
        ];

        // Force JSON conversion in proxy for robustness
        $url = $this->baseUrl;// . (str_contains($this->baseUrl, '?') ? '&' : '?') . 'format=json';

        $resp = Http::asForm()->acceptJson()->timeout(30)->post($url, $payload);

        if ($resp->successful()) {
            $json = $resp->json();
            if (!is_array($json)) {
                Log::warning('DhruFusionClient: Non-array JSON response', ['head' => substr((string)$resp->body(), 0, 200)]);
                return ['success' => false, 'data' => null, 'error' => 'Invalid JSON'];
            }
            return ['success' => true, 'data' => $json, 'error' => null];
        }
        Log::warning('DhruFusionClient: HTTP error', ['status' => $resp->status(), 'head' => substr((string)$resp->body(), 0, 200)]);
        return ['success' => false, 'data' => null, 'error' => 'HTTP '.$resp->status().': '.$resp->body()];
    }

    /**
     * Fetch IMEI service list grouped by group name.
     * Normalizes multiple possible shapes from Dhru XML->JSON.
     * Returns [ 'GroupName' => [ [ 'SERVICEID' => ..., 'SERVICENAME' => ..., 'CREDIT' => ..., 'INFO' => ... ], ... ] ]
     */
    public function getImeiServiceList(): array
    {
        $res = $this->request('imeiservicelist');
        if (!$res['success']) {
            Log::warning('Dhru getImeiServiceList: request failed', ['error' => $res['error'] ?? null]);
            return [];
        }
        $data = $res['data'];

        // Normalize keys to uppercase for robustness (providers differ in casing)
        $data = $this->normalizeKeysUpper($data);

        // Try common shapes produced by XML->JSON conversion
        $successNode = $data['SUCCESS'] ?? null;

        // Case A: SUCCESS is an object with RESULT
        if (is_array($successNode) && array_key_exists('RESULT', $successNode)) {
            $results = $successNode['RESULT'];
            if (!is_array($results) || (array_keys($results) !== range(0, count($results) - 1))) {
                $results = [$results];
            }
            return $this->extractGroupsFromResults($results);
        }

        // Case B: SUCCESS is already an array of result items
        if (is_array($successNode)) {
            // If keys are numeric, assume it's a list of result objects
            $isNumericList = array_keys($successNode) === range(0, count($successNode) - 1);
            if ($isNumericList) {
                return $this->extractGroupsFromResults($successNode);
            }
        }

        // Case C: Some providers may use MESSAGE/LIST at top-level
        if (isset($data['LIST']) && is_array($data['LIST'])) {
            return $this->extractGroupsFromList($data['LIST']);
        }

        Log::warning('Dhru getImeiServiceList: unrecognized response shape', [
            'keys' => is_array($data) ? array_keys($data) : gettype($data),
            'preview' => is_array($data) ? array_slice($data, 0, 3, true) : null,
        ]);
        return [];
    }

    /**
     * Recursively convert all array keys to uppercase.
     */
    private function normalizeKeysUpper($value)
    {
        if (!is_array($value)) {
            return $value;
        }
        $out = [];
        foreach ($value as $k => $v) {
            $key = is_string($k) ? strtoupper($k) : $k;
            $out[$key] = $this->normalizeKeysUpper($v);
        }
        return $out;
    }

    /**
     * @param array $results Each result item may contain LIST
     */
    private function extractGroupsFromResults(array $results): array
    {
        $list = [];
        foreach ($results as $item) {
            if (!is_array($item)) { continue; }
            if (isset($item['LIST']) && is_array($item['LIST'])) {
                $groups = $this->extractGroupsFromList($item['LIST']);
                // merge groups (same group name will be overwritten by later entries)
                foreach ($groups as $gName => $services) {
                    $list[$gName] = $services;
                }
            }
        }
        return $list;
    }

    /**
     * LIST may be an object keyed by group name, each with SERVICES map
     */
    private function extractGroupsFromList(array $listNode): array
    {
        $out = [];
        foreach ($listNode as $groupKey => $group) {
            if (!is_array($group)) { continue; }

            // Meta
            $groupType = $group['GROUPTYPE'] ?? null;
            $groupName = $group['GROUPNAME'] ?? $groupKey;

            // Normalize SERVICES into a numeric array of service items
            $servicesNode = $group['SERVICES'] ?? [];
            $services = [];
            if (is_array($servicesNode)) {
                // Case: SERVICES has direct map of SERVICEID => item
                if (!array_key_exists('RESULT', $servicesNode)) {
                    $services = array_values($servicesNode);
                } else {
                    // SERVICES => { RESULT: {...} | [ {...}, {...} ] }
                    $res = $servicesNode['RESULT'];
                    if (is_array($res)) {
                        $services = array_is_list($res) ? $res : [$res];
                    }
                }
            }

            $out[$groupKey] = [
                'GROUPTYPE' => $groupType,
                'GROUPNAME' => $groupName,
                'SERVICES'  => $services,
            ];
        }
        return $out;
    }
}
