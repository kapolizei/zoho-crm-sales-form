<?php

namespace App\Http\Controllers;

use App\Services\ZohoInventoryService;
use App\Http\Requests\StoreItemRequest;

use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private ZohoInventoryService $zohoInventoryService) {}

    public function index(Request $request)
    {
        try {
            $items = $this->zohoInventoryService->listItems();
            if($search = $request->get('search')) {
                $search = strtolower($search);
                $items = array_filter($items, function ($item) use ($search) {
                    return str_contains(strtolower($item['name']), $search);
                });
            }
            return response()->json(['items' => array_values($items)]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreItemRequest $request)
    {
       try {
            $item = $this->zohoInventoryService->storeItem($request->validated());
            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $item = $this->zohoInventoryService->showItem($id);
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
