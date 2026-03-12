<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\Request;
use App\Services\ZohoInventoryService;

class PurchaseController extends Controller
{
        public function __construct(private ZohoInventoryService $zohoInventoryService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        try {
            $purchase = $this->zohoInventoryService->storePurchase($request->validated());
            return response()->json($purchase, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
