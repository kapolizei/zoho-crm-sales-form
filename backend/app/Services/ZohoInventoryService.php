<?php

namespace App\Services;

class ZohoInventoryService
{
    public function __construct(
        private ZohoAuthService $zohoAuthService
    ) {}

    public function listItems(): array
    {
        $response = $this->zohoAuthService->request('get', 'items?organization_id='.config('services.zoho.org_id'));

        return $response['items'];
    }

    public function storeItem(array $data): array
    {
        return $this->zohoAuthService->request('post', 'items?organization_id='.config('services.zoho.org_id'), $data);
    }

    public function showItem(string $id): array
    {
        return $this->zohoAuthService->request('get', 'items/'.$id.'?organization_id='.config('services.zoho.org_id'));

    }

    public function listSales(): array
    {
        return $this->zohoAuthService->request('get', 'items?organization_id='.config('services.zoho.org_id'));
    }

    public function storeSale(array $data): array
    {
        return $this->zohoAuthService->request('post', 'salesorders?organization_id='.config('services.zoho.org_id'), $data);
    }

    public function listContacts(?string $type = null): array
    {
        $params = ['organization_id' => config('services.zoho.org_id')];
        if ($type) {
            $params['contact_type'] = $type;
        }

        $response = $this->zohoAuthService->get('contacts', $params);

        return $response['contacts'] ?? [];
    }

    public function storeContact(array $data): array
    {
        return $this->zohoAuthService->request('post', 'contacts?organization_id='.config('services.zoho.org_id'), $data);
    }

    public function listPurchases(): array
    {
        return $this->zohoAuthService->request('get', 'items?organization_id='.config('services.zoho.org_id'));
    }

    public function storePurchase(array $data): array
    {
        return $this->zohoAuthService->request('post', 'purchaseorders?organization_id='.config('services.zoho.org_id'), $data);
    }
}
