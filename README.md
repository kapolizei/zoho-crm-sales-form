# zoho-crm-sales-form

Интеграция с Zoho Inventory для создания Sales Orders и автоматической генерации Purchase Orders при нехватке товара на складе.

## Стек

**Frontend** — Vue 3 + Vite  
**Backend** — Laravel 11 + MySql  
**API** — Zoho Inventory (EU)

## Обязательные переменные (.env)
```env
ZOHO_CLIENT_ID=
ZOHO_CLIENT_SECRET=
ZOHO_ORG_ID=
ZOHO_BASE_URL=https://www.zohoapis.eu/crm/v2
ZOHO_AUTH_URL=https://accounts.zoho.eu/oauth/v2/token
ZOHO_INV_URL=https://www.zohoapis.eu/inventory/v1
VUE_URL=http://localhost:5173
```