# API Endpoints

## Аутентификация

### POST /api/auth/telegram
- **Описание**: Аутентификация пользователя через Telegram WebApp.
- **Параметры**:
  - `initData` (string, required): Данные от Telegram WebApp (query_id, user и т.д.).
- **Авторизация**: Не требуется.
- **Пример запроса**:
  ```json
  {
      "initData": "query_id=AAEHJD8v...&user=..."
  }
  ```
- **Пример ответа (успех)**:
  ```json
  {
      "token": "eyJ0eXAiOiJKV1QiLCJh..."
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "No initData provided",
      "code": "missing_init_data"
  }
  ```

## Пользователь

### GET /api/user
- **Описание**: Получение данных текущего пользователя.
- **Параметры**: Нет.
- **Авторизация**: Требуется (JWT-токен в заголовке `Authorization: Bearer <token>`).
- **Пример ответа (успех)**:
  ```json
  {
    "id": 8,
    "telegram_user_id": "012ff23553d33706e263cd6640d64574c8ffdad0b6d30f40a1260c12307697ff",
    "first_name": "Вася",
    "last_name": "",
    "birthdate": "2001-01-01",
    "phone": "7088888888",
    "role": "user",
    "created_at": "2025-04-28T09:20:25.000000Z",
    "updated_at": "2025-04-28T09:20:25.000000Z"
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Unauthorized",
      "code": "no_user"
  }
  ```

### GET /api/users/{telegram_id}
- **Описание**: Получение данных пользователя по Telegram ID.
- **Параметры**:
  - `telegram_id` (string, required): Telegram ID пользователя.
- **Авторизация**: Требуется (JWT-токен в заголовке `Authorization: Bearer <token>`).
- **Пример ответа (успех)**:
  ```json
  {
      "id": 8,
      "telegram_user_id": "012ff23553d33706e263cd6640d64574c8ffdad0b6d30f40a1260c12307697ff",
      "first_name": "Вася",
      "last_name": "",
      "phone": "7088888888",
      "birthdate": "2001-01-01",
      "role": "user"
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Unauthorized",
      "code": "invalid_token"
  }
  ```

### POST /api/users
- **Описание**: Создание нового пользователя.
- **Параметры**:
  - `telegram_user_id` (string, required): Telegram ID пользователя.
  - `first_name` (string, required): Имя пользователя.
  - `last_name` (string, optional): Фамилия пользователя.
  - `phone` (string, optional): Телефон пользователя.
  - `birthdate` (string, optional): Дата рождения пользователя (формат: `YYYY-MM-DD`).
- **Авторизация**: Не требуется.
- **Пример запроса**:
  ```json
  {
      "telegram_user_id": "42264144",
      "first_name": "Вася",
      "last_name": "",
      "phone": "7088888888",
      "birthdate": "01.01.2001"
  }
  ```
- **Пример ответа (успех)**:
  ```json
  {
    "id": 8,
    "telegram_user_id": "012ff23553d33706e263cd6640d64574c8ffdad0b6d30f40a1260c12307697ff",
    "first_name": "Вася",
    "last_name": "",
    "birthdate": "2001-01-01",
    "phone": "7088888888",
    "role": "user",
    "created_at": "2025-04-28T09:20:25.000000Z",
    "updated_at": "2025-04-28T09:20:25.000000Z"
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Validation failed",
      "details": {
          "telegram_id": "The telegram_id field is required."
      }
  }
  ```

## Эксперты

### GET /api/experts
- **Описание**: Получение списка всех экспертов.
- **Параметры**: Нет.
- **Авторизация**: Не требуется.
- **Пример ответа (успех)**:
  ```json
  [
          {
        "id": 1,
        "user_id": 8,
        "first_name": "Василий",
        "last_name": "Васильев",
        "biography": "Пеку пирожки",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/rzfvNy5xSb4AsEhdweR7joKinrU55vGatcaBuWz0.jpg",
        "experience": "10 лет учу печь пирожки",
        "education": "3 года учился у бабушки печь пирожки",
        "created_at": "2025-04-28T19:21:28.000000Z",
        "updated_at": "2025-04-28T19:21:28.000000Z",
        "categories": [
            {
                "id": 2,
                "category": "Психология и коучинг",
                "description": "(самооценка, уверенность, страхи)",
                "created_at": "2025-04-28T19:32:43.000000Z",
                "updated_at": "2025-04-28T19:32:43.000000Z",
                "pivot": {
                    "expert_id": 1,
                    "category_id": 2
                }
            }
        ]
    },
          {
        "id": 2,
        "user_id": 3,
        "first_name": "Петя",
        "last_name": "Петров",
        "biography": "10 лет работаю качегаром",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/rzfvNy5xSb4AsEhdweR7joKinrU55vGatcaBuWz0.jpg",
        "experience": "стаж 5 лет",
        "education": "3 года учился в техникуме",
        "created_at": "2025-04-28T19:21:28.000000Z",
        "updated_at": "2025-04-28T19:21:28.000000Z",
        "categories": [
            {
                "id": 2,
                "category": "Философия и мировозрение",
                "description": "(поиск смысла жизни, самопознание)",
                "created_at": "2025-04-28T19:32:43.000000Z",
                "updated_at": "2025-04-28T19:32:43.000000Z",
                "pivot": {
                    "expert_id": 1,
                    "category_id": 2
                }
            }
        ]
    }
  ]
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Server error",
      "code": "server_error"
  }
  ```

### GET /api/experts/{expertId}
- **Описание**: Получение данных конкретного эксперта.
- **Параметры**:
  - `expertId` (integer, required): ID эксперта.
- **Авторизация**: Не требуется.
- **Пример ответа (успех)**:
  ```json
  {
    "id": 1,
    "user_id": 8,
    "first_name": "Василий",
    "last_name": "Васильев",
    "biography": "Пеку пирожки",
    "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/rzfvNy5xSb4AsEhdweR7joKinrU55vGatcaBuWz0.jpg",
    "experience": "10 лет учу печь пирожки",
    "education": "3 года учился у бабушки печь пирожки",
    "created_at": "2025-04-28T19:21:28.000000Z",
    "updated_at": "2025-04-28T19:21:28.000000Z",
    "categories": [
        {
            "id": 2,
            "category": "Психология и коучинг",
            "description": "(самооценка, уверенность, страхи)",
            "created_at": "2025-04-28T19:32:43.000000Z",
            "updated_at": "2025-04-28T19:32:43.000000Z",
            "pivot": {
                "expert_id": 1,
                "category_id": 2
            }
        }
    ]
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Expert not found",
      "code": "not_found"
  }
  ```

### POST /api/experts
- **Описание**: Создание нового эксперта.
- **Параметры**:
  - `first_name` (string, required): имя эксперта.
  - `last_name` (integer, required): фамилия категории.
  - `biography` (string, optional): биография эксперта.
  - `photo` (string, optional): фотография эксперта.
  - `experience` (string, optional): опыт эксперта.
  - `education` (string, optional): обучение эксперта.
- **Авторизация**: Требуется (JWT-токен в заголовке `Authorization: Bearer <token>`).
- **Пример запроса**:
  ```json
  {
      "first_name": "Василий",
      "last_name": "Васильев",
      "biography": "Пеку пирожки",
      "photo": "https://example.com/photo.jpg",
      "experience": "10 лет учу печь пирожки",
      "education": "3 года учился у бабушки печь пирожки"
  }
  ```
- **Пример ответа (успех)**:
  ```json
  {
    "message": "Эксперт успешно создан",
    "expert": {
        "first_name": "Василий",
        "last_name": "Васильев",
        "biography": "Пеку пирожки",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/rzfvNy5xSb4AsEhdweR7joKinrU55vGatcaBuWz0.jpg",
        "experience": "10 лет учу печь пирожки",
        "education": "3 года учился у бабушки печь пирожки",
        "user_id": 8,
        "updated_at": "2025-04-28T19:21:28.000000Z",
        "created_at": "2025-04-28T19:21:28.000000Z",
        "id": 1
    }
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Validation failed",
      "details": {
          "name": "The name field is required."
      }
  }
  ```

### PATCH /api/experts/{expertId}
- **Описание**: Обновление данных эксперта.
- **Параметры**:
  - `first_name` (integer, required): имя эксперта.
  - `last_name` (string, optional): фамилия эксперта.
  - `biography` (integer, optional): биография категории.
  - `photo` (string, optional): фото эксперта.
  - `experience` (string, optional): опыт эксперта.
  - `education` (string, optional): образование эксперта.
- **Авторизация**: Требуется (JWT-токен в заголовке `Authorization: Bearer <token>`).
- **Пример запроса**:
  ```json
  {
      "first_name": "Джон",
      "last_name": "Уик",
      "biography": "Люблю собак"
  }
  ```
- **Пример ответа (успех)**:
  ```json
  {
    "message": "Экспер успешно обновлен",
    "expert": {
        "id": 1,
        "user_id": 8,
        "first_name": "Джон",
        "last_name": "Уик",
        "biography": "Люблю собак",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/rzfvNy5xSb4AsEhdweR7joKinrU55vGatcaBuWz0.jpg",
        "experience": "10 лет учу печь пирожки",
        "education": "3 года учился у бабушки печь пирожки",
        "created_at": "2025-04-28T19:21:28.000000Z",
        "updated_at": "2025-04-28T19:45:39.000000Z",
        "categories": [
            {
                "id": 2,
                "category": "Психология и коучинг",
                "description": "(самооценка, уверенность, страхи)",
                "created_at": "2025-04-28T19:32:43.000000Z",
                "updated_at": "2025-04-28T19:32:43.000000Z",
                "pivot": {
                    "expert_id": 1,
                    "category_id": 2
                }
            }
        ]
    }
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Expert not found",
      "code": "not_found"
  }
  ```

## Категории

### GET /api/categories
- **Описание**: Получение списка всех категорий.
- **Параметры**: Нет.
- **Авторизация**: Не требуется.
- **Пример ответа (успех)**:
  ```json
  [
    {
        "id": 1,
        "category": "Личностный рост",
        "description": "(цели, дисциплина, продуктивность)",
        "created_at": "2025-04-28T19:32:43.000000Z",
        "updated_at": "2025-04-28T19:32:43.000000Z"
    },
    {
        "id": 2,
        "category": "Психология и коучинг",
        "description": "(самооценка, уверенность, страхи)",
        "created_at": "2025-04-28T19:32:43.000000Z",
        "updated_at": "2025-04-28T19:32:43.000000Z"
    }
  ]
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Server error",
      "code": "server_error"
  }
  ```

## Telegram Webhook

### POST /api/telegram/{bot}/webhook
- **Описание**: Обработка вебхуков от Telegram (внутренний эндпоинт для бота).
- **Параметры**:
  - `bot` (string, required): Идентификатор бота.
  - Тело запроса: Данные вебхука от Telegram.
- **Авторизация**: Не требуется.
- **Пример запроса**:
  ```json
  {
      "update_id": 591571337,
      "message": {
          "message_id": 2406,
          "from": {
              "id": 792577174,
              "is_bot": false,
              "first_name": "Вася",
              "username": "vasya",
              "language_code": "ru"
          },
          "chat": {
              "id": 792577174,
              "first_name": "Вася",
              "username": "vasya",
              "type": "private"
          },
          "date": 1745860933,
          "text": "/start",
          "entities": [
              {
                  "offset": 0,
                  "length": 6,
                  "type": "bot_command"
              }
          ]
      }
  }
  ```
- **Пример ответа (успех)**:
  ```json
  {
      "status": "success"
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Invalid webhook data"
  }
  ```
