<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Sage300Service;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class Sage300Controller extends Controller
{
    protected Sage300Service $sage300;

    public function __construct(Sage300Service $sage300)
    {
        $this->sage300 = $sage300;
    }

    /**
     * Main page - Sage 300 API Explorer
     */
    public function index(): View
    {
        $endpoints = $this->sage300->getEndpoints();
        return view('admin.sage300.index', compact('endpoints'));
    }

    /**
     * API: GET data from any endpoint
     */
    public function getData(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint', 'APVendors');
        $params = $request->except(['endpoint', '_token']);

        $result = $this->sage300->get($endpoint, $params);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * API: POST data to any endpoint
     */
    public function postData(Request $request): JsonResponse
    {
        $endpoint = $request->input('endpoint');
        $data = $request->input('data', []);

        if (empty($endpoint)) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint is required'
            ], 400);
        }

        $result = $this->sage300->post($endpoint, $data);

        return response()->json($result, $result['status'] ?? 200);
    }

    /**
     * Get all items from Sage 300
     */
    public function getItems(Request $request): JsonResponse
    {
        $items = $this->sage300->getItems();
        
        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    /**
     * Get detailed information for a specific item
     */
    public function getItemDetails(Request $request, string $code): JsonResponse
    {
        $item = $this->sage300->getItem($code);
        $pricing = $this->sage300->getItemPricing($code);
        
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $item['UnformattedItemNumber'],
                'name' => $item['Description'],
                'category' => $item['Category'],
                'unit' => $item['StockingUnitOfMeasure'],
                'unit_price' => $pricing['BasePrice'] ?? 0,
                'available_qty' => $item['QuantityOnHand'] ?? 0,
                'qty_on_hand' => $item['QuantityOnHand'] ?? 0,
            ]
        ]);
    }

    /**
     * Get all locations with their quantities for a specific item
     */
    public function getItemLocations(Request $request, string $itemCode): JsonResponse
    {
        try {
            $locations = $this->sage300->getItemLocations($itemCode);
            
            if (empty($locations)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No locations found for this item'
                ]);
            }
            
            // Transform the data to a consistent format
            $formattedLocations = $locations;
            
            // Filter out locations with zero quantity
            $formattedLocations = array_filter($formattedLocations, function($loc) {
                return $loc['quantity'] > 0;
            });
            
            // Re-index array
            $formattedLocations = array_values($formattedLocations);
            
            return response()->json([
                'success' => true,
                'data' => $formattedLocations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch item locations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all locations from Sage 300
     */
    public function getLocations(): JsonResponse
    {
        $locations = $this->sage300->getLocations();
        
        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }

    /*
    public function getLocation(): JsonResponse
    {
        
        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }*/
}