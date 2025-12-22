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
    public function getLocation(string $locationCode = null): array
    {
        if(empty($locationCode)){
            return [];
        }

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

    /**
     * Get quantity for a specific item at a specific location
     * 
     * @param string $itemCode
     * @param string $locationCode
     * @return float
     */
    public function getLocationQuantity(string $itemCode, string $locationCode): float
    {
        try {
            $locations = $this->getItemLocations($itemCode);
            $location = collect($locations)->firstWhere('location_code', $locationCode);
            
            return $location ? (float) $location['quantity'] : 0;
        } catch (\Exception $e) {
            \Log::error("Failed to get location quantity for {$itemCode} at {$locationCode}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Post issue transaction to SAGE300
     * 
     * @param array $data
     * @return array Response from SAGE300 with unit_price, reference_numbers, etc.
     */
    public function postIssue(array $data): array
    {
        try {
            // Example API call structure - adjust based on your actual SAGE300 API
            $response = $this->client->post('/api/inventory/issue', [
                'json' => [
                    'item_code' => $data['item_code'],
                    'location_code' => $data['location_code'],
                    'quantity' => $data['quantity'],
                    'requisition_number' => $data['requisition_number'],
                    'transaction_date' => now()->format('Y-m-d'),
                ]
            ]);
            
            $result = json_decode($response->getBody()->getContents(), true);
            
            // Return the data structure expected by the controller
            return [
                'unit_price' => $result['unit_price'] ?? 0,
                'reference_number_1' => $result['reference_number_1'] ?? $result['document_number'] ?? null,
                'reference_number_2' => $result['reference_number_2'] ?? $result['batch_number'] ?? null,
                'success' => true
            ];
            
        } catch (\Exception $e) {
            \Log::error("Failed to post issue to SAGE300: " . $e->getMessage());
            
            // Return default values on error
            return [
                'unit_price' => 0,
                'reference_number_1' => null,
                'reference_number_2' => null,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Post adjustment/issue transaction to SAGE300
     * 
     * @param array $adjustmentData
     * @return array Response with unit_price, reference_numbers, etc.
     */
    public function postAdjustment(array $adjustmentData): array
    {
        try {
            $payload = [
                'Description' => $adjustmentData['description'] ?? 'Requisition Issue',
                'AdjustmentDate' => now()->format('Y-m-d\TH:i:s.v\Z'),
                'Reference' => $adjustmentData['requisition_number'] ?? '',
                'AdjustmentDetails' => [
                    [
                        'ItemNumber' => $adjustmentData['item_code'],
                        'Location' => $adjustmentData['location_code'],
                        'TransactionType' => 'BothDecrease',
                        'Quantity' => (int) $adjustmentData['quantity'],
                        'Comments' => $adjustmentData['notes'] ?? '',
                    ]
                ]
            ];
            //dd($payload);
            // Use the existing post() method from this service
            $result = $this->post('IC/ICAdjustments', $payload);
            //dd($result);
            // Check if the request was successful
            if (!$result['success']) {
                Log::error("SAGE300 Adjustment API failed", [
                    'payload' => $payload,
                    'response' => $result
                ]);
                
                return [
                    'success' => false,
                    'unit_price' => 0,
                    'reference_number_1' => null,
                    'reference_number_2' => null,
                    'error' => $result['error'] ?? $result['message'] ?? 'Unknown error'
                ];
            }
            
            $data = $result['data'];
            
            // Extract unit price from response (AverageCost from AdjustmentDetails)
            $unitPrice = 0;
            if (isset($data['AdjustmentDetails'][0]['AverageCost'])) {
                $unitPrice = $data['AdjustmentDetails'][0]['AverageCost'];
            }
            
            return [
                'success' => true,
                'unit_price' => $unitPrice,
                'reference_number_1' => $data['AdjustmentNumber'] ?? null,
                'reference_number_2' => $data['TransactionNumber'] ?? null,
                'sequence_number' => $data['SequenceNumber'] ?? null,
                'cost_adjustment' => $data['AdjustmentDetails'][0]['CostAdjustment'] ?? 0,
                'record_status' => $data['RecordStatus'] ?? null,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to post adjustment to SAGE300: " . $e->getMessage(), [
                'item_code' => $adjustmentData['item_code'] ?? null,
                'location' => $adjustmentData['location_code'] ?? null,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'unit_price' => 0,
                'reference_number_1' => null,
                'reference_number_2' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Post GRN adjustment (Both Increase) to SAGE300
     * 
     * @param array $grnData
     * @return array Response with unit_price, reference_numbers, etc.
     */
    public function postGrnAdjustment(array $grnData): array
    {
        try {
            $payload = [
                'Description' => $grnData['description'] ?? 'GRN Return',
                'AdjustmentDate' => now()->format('Y-m-d\TH:i:s.v\Z'),
                'Reference' => $grnData['reference'] ?? '',
                'AdjustmentDetails' => [
                    [
                        'ItemNumber' => $grnData['item_code'],
                        'Location' => $grnData['location_code'],
                        'TransactionType' => 'BothIncrease', // For GRN returns - increase both qty and cost
                        'Quantity' => (int) $grnData['quantity'],
                        'UnitCost' => (float) $grnData['unit_price'],
                        'Comments' => $grnData['notes'] ?? '',
                    ]
                ]
            ];
            
            // Use the existing post() method from this service
            $result = $this->post('IC/ICAdjustments', $payload);
            
            // Check if the request was successful
            if (!$result['success']) {
                Log::error("SAGE300 GRN Adjustment API failed", [
                    'payload' => $payload,
                    'response' => $result
                ]);
                
                return [
                    'success' => false,
                    'unit_price' => $grnData['unit_price'] ?? 0,
                    'reference_number_1' => null,
                    'reference_number_2' => null,
                    'error' => $result['error'] ?? $result['message'] ?? 'Unknown error'
                ];
            }
            
            $data = $result['data'];
            
            // Extract unit price from response (AverageCost from AdjustmentDetails)
            $unitPrice = $grnData['unit_price'] ?? 0;
            if (isset($data['AdjustmentDetails'][0]['AverageCost'])) {
                $unitPrice = $data['AdjustmentDetails'][0]['AverageCost'];
            }
            
            return [
                'success' => true,
                'unit_price' => $unitPrice,
                'reference_number_1' => $data['AdjustmentNumber'] ?? null,
                'reference_number_2' => $data['TransactionNumber'] ?? null,
                'sequence_number' => $data['SequenceNumber'] ?? null,
                'cost_adjustment' => $data['AdjustmentDetails'][0]['CostAdjustment'] ?? 0,
                'record_status' => $data['RecordStatus'] ?? null,
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to post GRN adjustment to SAGE300: " . $e->getMessage(), [
                'item_code' => $grnData['item_code'] ?? null,
                'location' => $grnData['location_code'] ?? null,
                'exception' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'unit_price' => $grnData['unit_price'] ?? 0,
                'reference_number_1' => null,
                'reference_number_2' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}