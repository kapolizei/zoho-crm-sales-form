# Zoho Inventory Integration — Laravel Backend
Laravel API сервер для интеграции с Zoho Inventory. Проксирует запросы от Vue фронтенда к Zoho API, управляет OAuth авторизацией и токенами.

## Авторизация Zoho OAuth

1. Перейти на `http://localhost:5173`
2. Vue перенаправит на callback и сохранит токен в БД
3. Токен автоматически обновляется при истечении

## API Endpoints

### Авторизация
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/zoho/auth` | Редирект на Zoho OAuth |
| GET | `/zoho/callback` | Callback после авторизации |
| GET | `/api/crm/status` | Проверка авторизации |

### Контакты
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/contacts` | Список контактов. Параметры: `?type=customer\|vendor` |
| POST | `/api/contacts` | Создать контакт |

### Товары
| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/items` | Список товаров. Параметры: `?search=name` |
| GET | `/api/items/{id}` | Получить товар по ID |
| POST | `/api/items` | Создать товар |

### Заказы
| Метод | URL | Описание |
|-------|-----|----------|
| POST | `/api/sales` | Создать Sales Order |
| POST | `/api/purchase` | Создать Purchase Order |

---

## Структура проекта

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ZohoAuthController.php    # OAuth авторизация
│   │   ├── ContactsController.php    # Управление контактами
│   │   ├── ItemController.php        # Управление товарами
│   │   ├── SalesController.php       # Sales Orders
│   │   └── PurchaseController.php    # Purchase Orders
│   └── Requests/
│       ├── StoreContactRequest.php   # Валидация контакта
│       ├── StoreItemRequest.php      # Валидация товара
│       ├── StoreSalesRequest.php     # Валидация Sales Order
│       └── StorePurchaseRequest.php  # Валидация Purchase Order
├── Models/
│   └── ZohoToken.php                 # Модель OAuth токена
└── Services/
    ├── ZohoAuthService.php           # HTTP клиент + управление токенами
    └── ZohoInventoryService.php      # Бизнес логика Zoho API
```

---

## Модели

### ZohoToken
Хранит OAuth токены Zoho. В таблице всегда одна запись.

| Поле | Тип | Описание |
|------|-----|----------|
| `access_token` | string | Токен доступа (истекает через 1 час) |
| `refresh_token` | string | Токен обновления (бессрочный) |
| `expires_at` | datetime | Время истечения access_token |

Метод `isExpired()` — проверяет истёк ли токен.

## Примечания

- `organization_id` обязателен в каждом запросе к Zoho API
- `refresh_token` выдаётся Zoho только один раз — при первой авторизации