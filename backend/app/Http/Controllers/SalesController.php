<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalesRequest;
use App\Services\ZohoInventoryService;
use Illuminate\Http\Request;

class SalesController extends Controller
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
    public function store(StoreSalesRequest $request)
    {
        try {
            $sale = $this->zohoInventoryService->storeSale($request->validated());

            return response()->json($sale, 201);
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
