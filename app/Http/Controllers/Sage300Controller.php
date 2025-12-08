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

    
    
    //get data for the system
    public function getItems(Request $request): JsonResponse
    {
        $items = $this->sage300->getItems();
        
        return response()->json([
            'success' => true,
            'data' => $items
        ]);
    }

    public function getItemDetails(Request $request, string $code): JsonResponse
    {
        $item = $this->sage300->getItem($code);
        $pricing = $this->sage300->getItemPricing($code);
        $location = $this->sage300->getItemLocation($code);
        
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'code' => $item['ItemNumber'],
                'name' => $item['Description'],
                'category' => $item['Category'],
                'unit' => $item['StockingUnitOfMeasure'],
                'unit_price' => $pricing['BasePrice'] ?? 0,
                'available_qty' => $location['QuantityAvailableToShip'] ?? 0,
                'qty_on_hand' => $location['QuantityOnHand'] ?? 0,
            ]
        ]);
    }

    public function getLocations(): JsonResponse
    {
        $locations = $this->sage300->getLocations();
        
        return response()->json([
            'success' => true,
            'data' => $locations
        ]);
    }


}