# Book API

Мікросервісне REST API для управління базою книг. Частина проєкту підбору книг на основі вподобань користувача.

## Технології

- **PHP 8.3**
- **Laravel 13**
- **MySQL**
- **Docker**
- **Redis** (черга для імпорту)

## Функціонал

- Імпорт книг з CSV-файлу (асинхронно через чергу)
- CRUD для книг з підтримкою авторів, жанрів та видавництв

## Запуск

### 1. Клонування репозиторію

```bash
git clone https://github.com/docktor10fps/docktor10fps-test-task-5pro-software.git
cd docktor10fps-test-task-5pro-software
```

### 2. Налаштування середовища

```bash
cp .env.example .env
```

### 3. Запуск контейнерів

```bash
docker compose up -d
```

### 4. Встановлення залежностей та міграції

```bash
docker exec -it book_api_app composer install
docker exec -it book_api_app php artisan key:generate
docker exec -it book_api_app php artisan migrate
```

### 5. Запуск обробника черги

```bash
docker exec -it book_api_app php artisan queue:listen
```

## API Endpoints

| Метод | URL | Опис |
|---|---|---|
| `GET` | `/api/books` | Список книг (з пагінацією) |
| `GET` | `/api/books/{id}` | Детальна інформація про книгу |
| `POST` | `/api/books` | Створення книги |
| `PUT` | `/api/books/{id}` | Оновлення книги |
| `DELETE` | `/api/books/{id}` | Видалення книги |
| `POST` | `/api/books/import` | Імпорт книг з CSV |

## Імпорт CSV

Запит типу `multipart/form-data` з полем `file` (CSV до 10 МБ).

```bash
curl -X POST http://localhost/api/books/import \
  -F "file=@books.csv"
```

Імпорт виконується асинхронно. У відповідь повертається статус `202 Accepted`.

Очікувані колонки у CSV: `Title`, `Authors`, `Publisher`, `Year`, `ISBN`, `Description`, `Edition`, `Pages`, `Format`, `Country`, `Genre`.

## Тести

```bash
docker exec -it book_api_app php artisan test
```

Проєкт містить 24 тести (86 assertions):

| Тип | Файл | Що покриває |
|---|---|---|
| Unit | `RowNormalizerTest` | Парсинг рядків CSV |
| Feature | `BookControllerTest` | CRUD ендпоінти |
| Feature | `BookImportControllerTest` | Завантаження CSV файлу |
