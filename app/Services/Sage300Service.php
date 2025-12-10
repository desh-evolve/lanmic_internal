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
            'IC/ICInternalUsages' => 'Internal Usages',
            'IC/ICInternalUsages/{usageNumber}/Details' => 'Internal Usage Details',
            'IC/ICInternalUsages/{usageNumber}/Post' => 'Post Internal Usage',
            'IC/ICReceipts' => 'Receipts',
            'IC/ICReceipts/{receiptNumber}/Details' => 'Receipt Details',
            'IC/ICReceipts/{receiptNumber}/Post' => 'Post Receipt',
            'IC/ICDayEndProcessing' => 'Day End Processing',
        ];
    }

    /**
     * Get all items
     *
     * @return array
     */
    public function getItems(): array
    {
        $result = $this->get('IC/ICItems');
        return $result['success'] ? ($result['data']['value'] ?? []) : [];
    }

    /**
     * Get single item by code
     *
     * @param string $code
     * @return array|null
     */
    public function getItem(string $code): ?array
    {
        $result = $this->get("IC/ICItems('{$code}')");
        return $result['success'] ? $result['data'] : null;
    }

    /**
     * Get item pricing
     *
     * @param string $code
     * @return array|null
     */
    public function getItemPricing(string $code): ?array
    {
        $result = $this->get('IC/ICItemPricing', [
            '$filter' => "UnformattedItemNumber eq '{$code}'"
        ]);
        return $result['success'] ? ($result['data']['value'][0] ?? null) : null;
    }

    /**
     * Get item available quantity
     *
     * @param string $code
     * @return int
     */
    public function getItemQuantity(string $code): int
    {
        $item = $this->getItem($code);
        return $item['QuantityAvailable'] ?? 0;
    }

    public function getItemLocationQuantity(string $code, string $location): ?int
    {
        $result = $this->get("IC/ICLocationDetails(ItemNumber='{$code}',Location='{$location}')");
        
        if (!$result['success'] || !isset($result['data'])) {
            return null;
        }
        
        return (int)($result['data']['QuantityOnHand'] ?? 0);
    }

    /**
     * Get all locations with quantities for a specific item
     *
     * @param string $code - Item code
     * @return array
     */
    public function getItemLocations(string $code): array
    {
        // Use $filter to get all locations for this item
        $result = $this->get('IC/ICLocationDetails', [
            '$filter' => "ItemNumber eq '{$code}'"
        ]);
        
        if (!$result['success']) {
            Log::error("Failed to get item locations for {$code}", [
                'error' => $result['message'] ?? 'Unknown error'
            ]);
            return [];
        }
        
        $locations = $result['data']['value'] ?? [];
        
        // Filter and format locations with available quantity
        $itemLocations = [];
        
        foreach ($locations as $location) {
            $quantityAvailable = (int)($location['QuantityOnHand'] ?? 0);
            
            // Only include locations with available quantity
            if ($quantityAvailable > 0) {
                $itemLocations[] = [
                    'location_code' => $location['Location'] ?? '',
                    'location_name' => $location['Name'] ?? $location['Location'] ?? '',
                    'quantity' => (int)($location['QuantityOnHand'] ?? 0),
                    //'average_cost' => (float)($location['AverageCost'] ?? 0),
                    //'in_use' => (bool)($location['InUse'] ?? false),
                    //'allowed' => (bool)($location['Allowed'] ?? false),
                ];
            }
        }
        
        return $itemLocations;
    }

    /**
     * Get single item location details
     *
     * @param string $code - Item code
     * @param string $location - Location code (default '1')
     * @return array|null
     */
    public function getItemLocation(string $code, string $location): ?array
    {
        // Use the direct endpoint format: ICLocationDetails(ItemNumber='code',Location='loc')
        $result = $this->get("IC/ICLocationDetails(ItemNumber='{$code}',Location='{$location}')");
        return $result['success'] ? $result['data'] : null;
    }

    /**
     * Get all locations
     *
     * @return array
     */
    public function getLocations(): array
    {
        $result = $this->get('IC/ICLocations');
        return $result['success'] ? ($result['data']['value'] ?? []) : [];
    }

    /**
     * Get one location 
     *
     * @param string $locationCode - Location code
     * @return array
     */
    public function getLocation(string $locationCode): array
    {
        // Use $filter to get all locations for this item
        $result = $this->get('IC/ICLocations', [
            '$filter' => "LocationKey eq '{$locationCode}'"
        ]);
        
        if (!$result['success']) {
            Log::error("Failed to get location for {$locationCode}", [
                'error' => $result['message'] ?? 'Unknown error'
            ]);
            return [];
        }
        
        $locationData = $result['data']['value'][0] ?? [];

        return $locationData;
    }
}