<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class Sage300Service
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('sage300.base_url'), '/');
        $this->username = config('sage300.username');
        $this->password = config('sage300.password');
        $this->timeout = config('sage300.timeout');
    }

    /**
     * GET request to any Sage 300 endpoint
     *
     * @param string $endpoint - e.g., 'APVendors', 'ARCustomers', 'ICItems'
     * @param array $params - Query parameters (optional)
     * @return array
     */
    public function get(string $endpoint, array $params = []): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            
            $response = Http::withBasicAuth($this->username, $this->password)
                ->withOptions([
                    'verify' => false, // Disable SSL verification for local/self-signed certs
                ])
                ->timeout($this->timeout)
                ->accept('application/json')
                ->get($url, $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'message' => 'API request failed',
                'error' => $response->body(),
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::error('Sage300 GET Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Connection error',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    /**
     * POST request to any Sage 300 endpoint
     *
     * @param string $endpoint - e.g., 'APVendors', 'ARCustomers'
     * @param array $data - Data to send
     * @return array
     */
    public function post(string $endpoint, array $data): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

            $response = Http::withBasicAuth($this->username, $this->password)
                ->withOptions([
                    'verify' => false,
                ])
                ->timeout($this->timeout)
                ->accept('application/json')
                ->contentType('application/json')
                ->post($url, $data);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status(),
                ];
            }

            return [
                'success' => false,
                'message' => 'API request failed',
                'error' => $response->body(),
                'status' => $response->status(),
            ];

        } catch (Exception $e) {
            Log::error('Sage300 POST Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Connection error',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    /**
     * Get available endpoints info
     *
     * @return array
     */
    public function getEndpoints(): array
    {
        return [
            'IC/ICItems' => 'Items',
            'IC/ICItemPricing' => 'Item Prices',
            'IC/ICLocations' => 'Locations',
            'IC/ICLocationDetails' => 'Location details',
        ];
    }

    public function postEndpoints(): array
    {
        return [
            'IC/ICInternalUsages' => 'Items',
            'IC/ICInternalUsages/{usageNumber}/Details' => 'Items',
            'IC/ICInternalUsages/{usageNumber}/Post' => 'Items',
            'IC/ICReceipts' => 'Items',
            'IC/ICReceipts/{receiptNumber}/Details' => 'Items',
            'IC/ICReceipts/{receiptNumber}/Post' => 'Items',
            'IC/ICDayEndProcessing' => 'Items',
        ];
    }

    //get data for the system
    public function getItems(): array
    {
        $result = $this->get('IC/ICItems');
        return $result['success'] ? ($result['data']['value'] ?? []) : [];
    }

    public function getItem(string $code): ?array
    {
        $result = $this->get("IC/ICItems('{$code}')");
        return $result['success'] ? $result['data'] : null;
    }

    public function getItemPricing(string $code): ?array
    {
        $result = $this->get('IC/ICItemPricing', [
            '$filter' => "UnformattedItemNumber eq '{$code}'"
        ]);
        return $result['success'] ? ($result['data']['value'][0] ?? null) : null;
    }

    public function getItemLocation(string $code, string $location = '1'): ?array
    {
        $result = $this->get('IC/ICLocationDetails', [
            '$filter' => "ItemNumber eq '{$code}' and Location eq '{$location}'"
        ]);
        return $result['success'] ? ($result['data']['value'][0] ?? null) : null;
    }

    public function getLocations(): array
    {
        $result = $this->get('IC/ICLocations');
        return $result['success'] ? ($result['data']['value'] ?? []) : [];
    }
}