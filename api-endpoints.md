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

### GET /api/profile/{userId}
- **Описание**: Пользователь может зайти в свой профиль(кроме него больше никто не может зайти) и получить свои данные.
- **Параметры**:
  - `userId` (integer, required): ID пользователя.
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

### GET /api/users/{userId}
- **Описание**: Получение данных текущего пользователя.
- **Параметры**: Нет.
- **Авторизация**: 
- Требуется (JWT-токен в заголовке `Authorization: Bearer <token>`) 
- Получить может только эксперт и админ.
- Другие пользователи не имеют доступ к этому действию.
- **Пример ответа (успех)**:
  ```json
  {
    "user": {
        "id": 6,
        "telegram_user_id": "022fa23553d83706e263cd6600d64574c8ffdad0b6d30f40a1260c12307897ff",
        "first_name": "Артём",
        "last_name": "",
        "birthdate": "1002-02-01",
        "phone": "+7083458877",
        "role": "expert",
        "rating": 4.5,
        "created_at": "2025-05-15T11:04:11.000000Z",
        "updated_at": "2025-05-15T19:21:32.000000Z"
    },
    "reviews": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "expert_id": 3,
                "user_id": 6,
                "rating": 4,
                "comment": "Очень приятно было иметь дело с этим пользователем",
                "created_at": "2025-05-15T19:15:05.000000Z",
                "updated_at": "2025-05-15T19:15:05.000000Z",
                "user": {
                    "id": 6,
                    "telegram_user_id": "022fa23553d83706e263cd6600d64574c8ffdad0b6d30f40a1260c12307897ff",
                    "first_name": "Артём",
                    "last_name": "",
                    "birthdate": "1002-02-01",
                    "phone": "+7083458877",
                    "role": "expert",
                    "rating": 4.5,
                    "created_at": "2025-05-15T11:04:11.000000Z",
                    "updated_at": "2025-05-15T19:21:32.000000Z"
                }
            },
            {
                "id": 2,
                "expert_id": 3,
                "user_id": 6,
                "rating": 5,
                "comment": null,
                "created_at": "2025-05-15T19:21:32.000000Z",
                "updated_at": "2025-05-15T19:21:32.000000Z",
                "user": {
                    "id": 6,
                    "telegram_user_id": "022fa23553d83706e263cd6600d64574c8ffdad0b6d30f40a1260c12307897ff",
                    "first_name": "Артём",
                    "last_name": "",
                    "birthdate": "1002-02-01",
                    "phone": "+7083458877",
                    "role": "expert",
                    "rating": 4.5,
                    "created_at": "2025-05-15T11:04:11.000000Z",
                    "updated_at": "2025-05-15T19:21:32.000000Z"
                }
            }
        ],
        "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/users/6?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/users/6?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/users/6?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/users/6",
        "per_page": 5,
        "prev_page_url": null,
        "to": 2,
        "total": 2
    },
    "expertCanLeaveReview": false
  }
  ```
- **Пример ответа (ошибка)**:
  ```json
  {
      "error": "Unauthorized",
      "code": "no_user"
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
    "expert": {
        "id": 3,
        "user_id": 6,
        "first_name": "Артём",
        "last_name": "Андреев",
        "biography": "Пеку вафли",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/5OZcpJSkjEVEiRF8ovBAb1WiaSxlIxarL9aSzBEe.jpg",
        "experience": "5 лет пеку вафли",
        "education": "3 года учился на фабрике печь вафли",
        "rating": 4.6666666666667,
        "created_at": "2025-05-14T15:53:54.000000Z",
        "updated_at": "2025-05-14T19:17:19.000000Z",
        "categories": [
            {
                "id": 1,
                "title": "Личностный рост",
                "subtitle": "PersonalGrowth",
                "description": "(цели, дисциплина, продуктивность)",
                "created_at": "2025-05-14T14:22:26.000000Z",
                "updated_at": "2025-05-14T14:22:26.000000Z",
                "pivot": {
                    "expert_id": 3,
                    "category_id": 1
                }
            }
        ]
    },
    "reviews": {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "user_id": 6,
                "expert_id": 3,
                "rating": 5,
                "comment": "Всем рекомендую этого эксперта",
                "created_at": "2025-05-14T19:17:19.000000Z",
                "updated_at": "2025-05-14T19:17:19.000000Z",
                "user": {
                    "id": 6,
                    "telegram_user_id": "022fa23553d83706e263cd6600d64574c8ffdad0b6d30f40a1260c12307897ff",
                    "first_name": "Артём",
                    "last_name": "",
                    "birthdate": "2001-01-01",
                    "phone": "+77084354345",
                    "role": "expert",
                    "created_at": "2025-05-14T15:43:03.000000Z",
                    "updated_at": "2025-05-14T15:53:54.000000Z"
                }
            }
        ],
        "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts/3?page=1",
        "from": 1,
        "last_page": 1,
        "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts/3?page=1",
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts/3?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "next_page_url": null,
        "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts/3",
        "per_page": 5,
        "prev_page_url": null,
        "to": 3,
        "total": 3
    },
    "userCanLeaveReview": false
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
  - `profession` (string, required): профессия
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
      "profession": "Кулинар",
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
        "profession": "Кулинар",
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
  - `first_name` (integer, optional): имя эксперта.
  - `last_name` (string, optional): фамилия эксперта.
  - `profession` (string, optional): профессия
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
        "profession": "Собаковод",
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
- **Описание**: Получение списка всех категорий, роли пользователя, списка экспертов/пользователей которым можно оставить отзыв.
- **Параметры**: Нет.
- **Авторизация**: Есть.
- **Пример ответа (успех)**:
```json
{
    "categories": [
        {
            "id": 1,
            "title": "Личностный рост",
            "subtitle": "PersonalGrowth",
            "description": "(цели, дисциплина, продуктивность)",
            "position": 1,
            "created_at": "2025-05-26T13:45:05.000000Z",
            "updated_at": "2025-05-26T13:45:05.000000Z"
        },
        {
            "id": 2,
            "title": "Психология и коучинг",
            "subtitle": "PsychologyAndCoaching",
            "description": "(самооценка, уверенность, страхи)",
            "position": 2,
            "created_at": "2025-05-26T13:45:05.000000Z",
            "updated_at": "2025-05-26T13:45:05.000000Z"
        }
    ],
    "user_role": "expert",
    "pending_reviews": [
        {
            "user_id": 5,
            "first_name": "Игорь",
            "last_name": "Игривый",
            "role": "Клиент"
        }, 
        {
            "expert_id": 1,
            "first_name": "Петр",
            "last_name": "Петров",
            "photo": "https://randomuser.me/api/portraits/men/17.jpg",
            "role": "Эксперт"
        }
    ]
}
```
- **Пример ответа (ошибка)**:
  ```json
  {
      "message": "Не удалось получить данные о категориях"
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
### Аутентификация

### POST /api/auth/telegram/refresh
- **Описание**: Обновление access token с помощью refresh token.
- **Параметры**:
  - Нет (токен передаётся через куки refresh_token).
- **Авторизация**: Не требуется.
- **Пример запроса**:
```json
  {}
```
- **Пример ответа (успех)**:
```json
    {
        "message": "Token refreshed",
        "expires_in": 900
    }
```
- **Пример ответа (ошибка)**:
```json
    {
        "error": "Refresh token expired",
        "code": "refresh_token_expired"
    }
```
 
### Курсы
### GET api/services
- **Описание**: Выдает все курсы.
- **Параметры**:
    - Нет.
- **Авторизация**: Не требуется.
- **Пример запроса**:
```json
  {}
```
- **Пример ответа (успех)**:
```json
    {
        "id": 1,
        "expert_id": 3,
        "title": "мой самый крутой курс",
        "description": "описание самого крутого курса на свете",
        "price": 10000,
        "category_id": 1,
        "created_at": "2025-04-29T17:40:51.000000Z",
        "updated_at": "2025-04-29T17:40:51.000000Z",
        "category": {
            "id": 1,
            "category": "Личностный рост",
            "description": "(цели, дисциплина, продуктивность)",
            "created_at": "2025-04-28T19:32:43.000000Z",
            "updated_at": "2025-04-28T19:32:43.000000Z"
        }
    }
```
- **Пример ответа (ошибка)**:
```json
    {
      "message" : "Something went wrong",
      "error" : "Сообщение с информацией об ошибке"
    }
```

### GET /api/experts/{expertId}/services
- **Описание**: Выдает все курсы конкретного эксперта.
- **Параметры**:
    - `expertId` (integer, required): ID эксперта.
- **Авторизация**: Не требуется.
- **Пример ответа (успех)**:
```json
[
    {
        "id": 1,
        "expert_id": 3,
        "title": "мой самый крутой курс",
        "description": "описание самого крутого курса на свете",
        "price": 10000,
        "category_id": 1,
        "created_at": "2025-04-29T17:40:51.000000Z",
        "updated_at": "2025-04-29T17:40:51.000000Z",
        "category": {
            "id": 1,
            "category": "Личностный рост",
            "description": "(цели, дисциплина, продуктивность)",
            "created_at": "2025-04-28T19:32:43.000000Z",
            "updated_at": "2025-04-28T19:32:43.000000Z"
        }
    },
    {
        "id": 2,
        "expert_id": 3,
        "title": "мой второй самый крутой курс",
        "description": "описание второго самого крутого курса на свете",
        "price": 14990,
        "category_id": 1,
        "created_at": "2025-04-29T18:19:34.000000Z",
        "updated_at": "2025-04-29T18:19:34.000000Z",
        "category": {
            "id": 1,
            "category": "Личностный рост",
            "description": "(цели, дисциплина, продуктивность)",
            "created_at": "2025-04-28T19:32:43.000000Z",
            "updated_at": "2025-04-28T19:32:43.000000Z"
        }
    }
]
```
- **Пример ответа (ошибка)**:
```json
    {
      "message" : "Something went wrong",
      "error" : "Сообщение с информацией об ошибке"
    }
```

### POST /api/services
- **Описание**: Создает курс.
- **Параметры**:
    - `title` (string, required): Название курса.
    - `description` (text, required): Описание курса.
    - `price` (float, required): Цена курса.
    - `category_id` (integer, required): ID категории.
- **Авторизация**: Проверка на статус эксперта.
- **Пример запроса**:
```json
{
    "title": "мой третий самый крутой курс",
    "description": "описание третьего самого крутого курса на свет",
    "price": 19990,
    "category_id": 1
}
```
- **Пример ответа (успех)**:
```json
[
    {
        "message": "Course added successfully",
        "course": {
            "title": "мой третий самый крутой курс",
            "description": "описание третьего самого крутого курса на свете",
            "price": "19990",
            "category_id": "1",
            "expert_id": 3,
            "updated_at": "2025-04-29T19:41:45.000000Z",
            "created_at": "2025-04-29T19:41:45.000000Z",
            "id": 6
        }
    }
]
```
- **Пример ответа (ошибка)**:
```json
{
    "error": "Token not provided",
    "code": "missing_token"
}
```

### PATCH /api/services/{serviceId}
- **Описание**: Создает курс.
- **Параметры**: 
    - `title` (string, nullable): Название курса.
    - `description` (text, nullable): Описание курса.
    - `price` (float, nullable): Цена курса.
    - `category_id` (integer, nullable): ID категории.
    - `serviceId` (integer, required) ID услуги
- **Авторизация**: Проверка на статус эксперта и используется политика проверки принадлежности услуги эксперту.
- **Пример запроса**:
```json
{
    "title": "мой первый курс",
    "description": "описание первого курса",
    "price": 4499
}
```
- **Пример ответа (успех)**:
```json
[
    {
        "message": "Service updated successfully",
        "service": {
            "id": 1,
            "expert_id": 3,
            "title": "мой первый курс",
            "description": "описание первого курса",
            "price": "4499",
            "category_id": 1,
            "created_at": "2025-04-29T17:40:51.000000Z",
            "updated_at": "2025-04-30T19:49:09.000000Z"
        }
    }
]
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Service not updated",
    "error": "Сообщение с информацией об ошибке"
}
```

### DELETE /api/services/{serviceId}
- **Описание**: Создает курс.
- **Параметры**:
    - `serviceId` (integer, required) ID услуги
- **Авторизация**: Проверка на статус эксперта и используется политика проверки принадлежности услуги эксперту.
- **Пример запроса**:
```json
{}
```
- **Пример ответа (успех)**:
```json
[
    {
        "message": "Service deleted successfully"
    }
]
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Service not deleted",
    "error": "Сообщение с информацией об ошибке"
}
```

### Эксперты в избранном
### GET /api/favorites/experts
- **Описание**: Создает курс.
- **Параметры**:
    - `serviceId` (integer, required) ID услуги
- **Авторизация**: Проверка на статус эксперта и используется политика проверки принадлежности услуги эксперту.
- **Пример запроса**:
```/api/favorites/experts?experts_ids=1, 2```

```В Params указывается ключ "experts_ids" и в значении перечисление через запятую (например: 1, 2)```
- **Пример ответа (успех)**:
```json
[
    {
        "id": 1,
        "user_id": 1,
        "first_name": "Степан",
        "last_name": "Степкин",
        "biography": "какая-то там биография",
        "photo": "http://bluejay-pretty-clearly.ngrok-free.app/storage/experts/116p1ZUDKSexffKWPFWNAlQ8YvToaMS4eBKOOjgY.jpg",
        "experience": "какой-то там опыт",
        "education": "какое-то там образование",
        "rating": 0,
        "created_at": "2025-05-01T18:06:03.000000Z",
        "updated_at": "2025-05-01T18:06:03.000000Z",
        "categories": [
            {
                "id": 1,
                "category": "Личностный рост",
                "description": "(цели, дисциплина, продуктивность)",
                "created_at": "2025-05-01T18:07:01.000000Z",
                "updated_at": "2025-05-01T18:07:01.000000Z",
                "pivot": {
                    "expert_id": 1,
                    "category_id": 1
                }
            },
            {
                "id": 3,
                "category": "Отношения и коммуникация",
                "description": "(семья, переговоры)",
                "created_at": "2025-05-01T18:07:01.000000Z",
                "updated_at": "2025-05-01T18:07:01.000000Z",
                "pivot": {
                    "expert_id": 1,
                    "category_id": 3
                }
            }
        ]
    },
    {
        "expertId": "2",
        "error": "expert not found"
    }
]
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Не получилось получить данные экспертов",
    "error": "Сообщение об ошибке"
}
```

### Фильтры
### GET /api/experts?search=
- **Описание**: Фильтр по поиску.
- **Параметры**:
    - Пользователь может делать запрос на поиск по имени, фамилии, категории на русском, описанию категории
- **Авторизация**: Есть.
- **Пример запроса**:
  ```/api/experts?search=Петр```

- **Пример ответа (успех)**:
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 3,
            "first_name": "Петр",
            "last_name": "Брынчушкин",
            "biography": "В детстве я очень любил наблюдать за тем, как бабушка пекет пирожки. Теперь я сам их пеку.",
            "photo": "https://randomuser.me/api/portraits/men/17.jpg",
            "experience": "10 лет уже пеку лучшие пирожки в городе.",
            "education": "3 года в пекарно-кулинарном техникуме, а также у бабушки на каникулах.",
            "rating": 0,
            "created_at": "2025-05-05T18:15:18.000000Z",
            "updated_at": "2025-05-05T18:15:18.000000Z",
            "categories": [
                {
                    "id": 1,
                    "title": "Личностный рост",
                    "subtitle": "PersonalGrowth",
                    "description": "(цели, дисциплина, продуктивность)",
                    "created_at": "2025-05-07T08:34:27.000000Z",
                    "updated_at": "2025-05-07T08:34:27.000000Z",
                    "pivot": {
                        "expert_id": 1,
                        "category_id": 1
                    }
                }
            ]
        }
    ],
    "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```
- **Пример ответа (ошибка)**:
- (Если пользователь введет данные, а такого эксперта нет, то он ничего не получит)
```json
{
    "message": "Ошибка при получении данных"
}
```


### GET /api/experts?category=
- **Описание**: Фильтр по категориям.
- **Параметры**:
    - Пользователь выбирает категорию из списка и получает экспертов в этой категории
- **Авторизация**: Есть.
- **Пример запроса**:
  ```/api/experts?category=psychology```

- **Пример ответа (успех)**:
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 2,
            "user_id": 4,
            "first_name": "Магомед",
            "last_name": "Магомедов",
            "biography": "Последние 10 лет активно работаю и развиваюсь в области ораторского мастерства и эффективных коммуникаций.",
            "photo": "https://randomuser.me/api/portraits/men/64.jpg",
            "experience": "10 лет вещаю людям про жизнь и то, как важно быть оратором.",
            "education": "Учился в горном институте на оратора 4 года, а после этого практиковался на улице.",
            "rating": 0,
            "created_at": "2025-05-05T18:15:18.000000Z",
            "updated_at": "2025-05-05T18:15:18.000000Z",
            "categories": [
                {
                    "id": 2,
                    "title": "Психология и коучинг",
                    "subtitle": "PsychologyAndCoaching",
                    "description": "(самооценка, уверенность, страхи)",
                    "created_at": "2025-05-07T08:34:27.000000Z",
                    "updated_at": "2025-05-07T08:34:27.000000Z",
                    "pivot": {
                        "expert_id": 2,
                        "category_id": 2
                    }
                }
            ]
        }
    ],
    "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```
- **Пример ответа (ошибка)**:
- (Если экспертов в данной категории нет, то пользователь получит пустую страницу)
```json
{
    "message": "Ошибка при получении данных"
}
```

### GET /api/services?isAFree=
- **Описание**: Фильтр по стоимости(платно/бесплатно).
- **Параметры**:
    - Пользователь выбирает платно/бесплатно и получает курсы по фильтру
- **Авторизация**: Есть.
- **Пример запроса**:
  ```/api/services?isAFree=true(или false(или 1 и 0))```

- **Пример ответа (успех)**:
```json
{
    "data": [
        {
            "id": 4,
            "title": "Курс всех курсов курс",
            "description": "описание самого крутого курса в мире",
            "price": 0,
            "category_id": 4,
            "expert_id": 3
        },
        {
            "id": 5,
            "title": "Самый крутой курс на преокте",
            "description": "Описание самого крутого курса на проекте",
            "price": 0,
            "category_id": 4,
            "expert_id": 3
        }
    ],
    "links": {
        "first": "http://bluejay-pretty-clearly.ngrok-free.app/api/services?page=1",
        "last": "http://bluejay-pretty-clearly.ngrok-free.app/api/services?page=1",
        "prev": null,
        "next": null
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 1,
        "links": [
            {
                "url": null,
                "label": "&laquo; Previous",
                "active": false
            },
            {
                "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/services?page=1",
                "label": "1",
                "active": true
            },
            {
                "url": null,
                "label": "Next &raquo;",
                "active": false
            }
        ],
        "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/services",
        "per_page": 10,
        "to": 2,
        "total": 2
    }
}
```
- **Пример ответа (ошибка)**:
- (Если услуг по выбранному фильтру нет, то пользователь получит пустой список)
```json
{
    "message": "Ошибка при получении данных"
}
```

### GET /api/experts?search=петр&category=personal
- **Описание**: Совмещенный фильтр.
- **Параметры**:
- **Авторизация**: Есть.

- **Пример ответа (успех)**:
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 3,
            "first_name": "Петр",
            "last_name": "Брынчушкин",
            "biography": "В детстве я очень любил наблюдать за тем, как бабушка пекет пирожки. Теперь я сам их пеку.",
            "photo": "https://randomuser.me/api/portraits/men/17.jpg",
            "experience": "10 лет уже пеку лучшие пирожки в городе.",
            "education": "3 года в пекарно-кулинарном техникуме, а также у бабушки на каникулах.",
            "rating": 0,
            "created_at": "2025-05-05T18:15:18.000000Z",
            "updated_at": "2025-05-05T18:15:18.000000Z",
            "categories": [
                {
                    "id": 1,
                    "title": "Личностный рост",
                    "subtitle": "PersonalGrowth",
                    "description": "(цели, дисциплина, продуктивность)",
                    "created_at": "2025-05-07T08:34:27.000000Z",
                    "updated_at": "2025-05-07T08:34:27.000000Z",
                    "pivot": {
                        "expert_id": 1,
                        "category_id": 1
                    }
                }
            ]
        }
    ],
    "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```
- **Пример ответа (ошибка)**:
- (Если экспертов по выбранным фильтрам нет, то пользователь получит пустой список)
```json
{
    "message": "Ошибка при получении данных"
}
```

### GET /api/experts?rating=4.5
- **Описание**: Фильтр по рейтингу(выдает экспертов с указанным рейтингом и выше).
- **Параметры**:
- **Авторизация**: Есть.

- **Пример ответа (успех)**:
```json
{
    "current_page": 1,
    "data": [
        {
            "id": 2,
            "user_id": 4,
            "first_name": "Магомед",
            "last_name": "Магомедов",
            "biography": "Последние 10 лет активно работаю и развиваюсь в области ораторского мастерства и эффективных коммуникаций.",
            "photo": "https://randomuser.me/api/portraits/men/64.jpg",
            "experience": "10 лет вещаю людям про жизнь и то, как важно быть оратором.",
            "education": "Учился в горном институте на оратора 4 года, а после этого практиковался на улице.",
            "rating": 5,
            "created_at": "2025-05-20T15:08:06.000000Z",
            "updated_at": "2025-05-20T15:09:22.000000Z",
            "categories": [
                {
                    "id": 2,
                    "title": "Психология и коучинг",
                    "subtitle": "PsychologyAndCoaching",
                    "description": "(самооценка, уверенность, страхи)",
                    "position": 2,
                    "created_at": "2025-05-20T14:41:53.000000Z",
                    "updated_at": "2025-05-20T14:41:53.000000Z",
                    "pivot": {
                        "expert_id": 2,
                        "category_id": 2
                    }
                }
            ]
        }
    ],
    "first_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
    "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
    ],
    "next_page_url": null,
    "path": "http://bluejay-pretty-clearly.ngrok-free.app/api/experts",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
}
```
- **Пример ответа (ошибка)**:
- (Если экспертов по выбранным фильтрам нет, то пользователь получит пустой список)
```json
{
    "message": "Ошибка при получении данных"
}
```


### Расписание Эксперта
### GET /api/my-available-slots
- **Описание**: Выдает список свободных слотов текущего эксперта(Эксперт просматривает свои слоты).
- **Параметры**:
    - Нет
- **Авторизация**: Есть.
- **Пример ответа (успех)**:
```json
[
    {
        "id": 1,
        "expert_id": 3,
        "date": "2025-05-14",
        "time": "11:00:00",
        "created_at": "2025-05-11T13:38:17.000000Z",
        "updated_at": "2025-05-11T13:38:17.000000Z"
    },
    {
        "id": 2,
        "expert_id": 3,
        "date": "2025-05-14",
        "time": "12:00:00",
        "created_at": "2025-05-11T13:38:17.000000Z",
        "updated_at": "2025-05-11T13:38:17.000000Z"
    }
]
```
- **Пример ответа (ошибка)**:
- (Если пользователь введет данные, а такого эксперта нет, то он ничего не получит)
```json
{
    "message": "Вы не являетесь экспертом."
}
```


### POST /api/my-available-slots
- **Описание**: Создание свободных слотов экспертом.
- **Параметры**:
    - date(например: 14.10.2025)
    - time(например: ["11:00", "12:00"])
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "date": "14.05.2025",
    "time": ["11:00", "12:00"]
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "График успешно сохранен."
}
```
- **Пример ответа (ошибка)**:
- (Если пользователь введет данные, а такого эксперта нет, то он ничего не получит)
```json
{
    "message": "Не удалось сохранить график."
}
```

### DELETE /api/my-available-slots
- **Описание**: Удаляет свободный слот.
- **Параметры**:
    - slot_id(integer)
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "slot_id": 3
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Slot removed successfully."
}
```
- **Пример ответа (ошибка)**:
- (Если пользователь введет данные, а такого эксперта нет, то он ничего не получит)
```json
{
    "message": "Вы не являетесь экспертом или не имеете доступ к этому действию."
}
```

### Запись к эксперту
### GET /api/bookings/available/{expertId}
- **Описание**: Выдает список свободных слотов выбранного эксперта.
- **Параметры**:
    - expertId (integer)
- **Авторизация**: Есть.
- **Пример ответа (успех)**:
```json
[
    {
        "id": 5,
        "expert_id": 3,
        "date": "2025-05-10",
        "time": "13:00:00",
        "created_at": "2025-05-11T15:38:04.000000Z",
        "updated_at": "2025-05-11T15:38:04.000000Z"
    },
    {
        "id": 6,
        "expert_id": 3,
        "date": "2025-05-10",
        "time": "14:00:00",
        "created_at": "2025-05-11T15:38:04.000000Z",
        "updated_at": "2025-05-11T15:38:04.000000Z"
    },
    {
        "id": 7,
        "expert_id": 3,
        "date": "2025-05-11",
        "time": "16:00:00",
        "created_at": "2025-05-11T15:40:19.000000Z",
        "updated_at": "2025-05-11T15:40:19.000000Z"
    }
]
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Не удалось получить свободные места для записи к специалисту."
}
```

### POST /api/services/{serviceId}/bookings
- **Описание**: Создает запись к эксперту и запускает логику оплаты.
- **Параметры**:
    - serviceId (integer)
- **Авторизация**: Есть.
- **Пример запроса(id услуги в адресной строке)**:
```json
{
    "date": "13.05.2025",
    "time": "10:00"
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Запись к эксперту успешно создана.",
    "date": "2025-06-10",
    "time": "12:00",
    "service": "Бесплатный курс по пирожкам",
    "service_id": "1"
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Слот уже занят."
}
```


### Отзывы
### POST /api/experts/{expertId}
- **Описание**: Пользователь оставляет отзыв об эксперте(если он уже оставил или количество пройденных курсов у эксперта = кол-ву оставленных отзывов данного пользователя, то пользователь не сможет оставить отзыв).
- **Параметры**:
    - expertId (integer)
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "rating": 5,
    "comment": "Всем рекомендую этого эксперта"
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Отзыв об эксперт был успешно опубликован."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Вы уже оставили отзыв данному эксперту."
}
```

### POST /api/users/{userId}
- **Описание**: Эксперт оставляет отзыв об эксперте(если он уже оставил или количество пройденных курсов у эксперта = кол-ву оставленных отзывов данного пользователя, то пользователь не сможет оставить отзыв).
- **Параметры**:
    - userId (integer)
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "rating": 5,
    "comment": "Очень приятно работать с этим пользователем."
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Отзыв об пользователе успешно опубликован."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Вы уже оставили отзыв данному пользователю."
}
```


### Админ
### POST /api/categories
- **Описание**: Админ создает новую категорию. Он может указать позицию этой категории(если значение позиции будет пустым - категория встанет на последнее на данный момент место).
- **Параметры**:
    - title (required, string)
    - subtitle (required, string)
    - description (nullable, string)
    - position (nullable, string)
- **Авторизация**: Есть(и проверка на роль админа).
- **Пример запроса**:
```json
{
    "title": "тест категория",
    "subtitle": "TestCategory",
    "description": "описание тестовой категории",
    "position": 16
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Категория создана успешно."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### DELETE /api/categories/{categoryId}
- **Описание**: Админ может удалить категорию.
- **Параметры**:
    - categoryId (required, integer)
- **Авторизация**: Есть(и проверка на роль админа).
- **Пример ответа (успех)**:
```json
{
    "message": "Категория удалена успешно."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### DELETE /api/users/{userId}
- **Описание**: Админ может удалить пользователя.
- **Параметры**:
    - userId (required, integer)
- **Авторизация**: Есть(и проверка на роль админа).
- **Пример ответа (успех)**:
```json
{
    "message": "Пользователь успешно удален."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### DELETE /api/experts/{expertId}
- **Описание**: Админ может удалить эксперта(Роль пользователя будет изменена на "user" и он будет удален из базы экспретов).
- **Параметры**:
    - expertId (required, integer)
- **Авторизация**: Есть(и проверка на роль админа).
- **Пример ответа (успех)**:
```json
{
    "message": "Эксперт успешно удален."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### GET /api/experts-to-excel
- **Описание**: Админ может получить всех экспертов в excel-формате.
- **Авторизация**: Есть(и проверка на роль админа).
- ** При успешном ответе админу будет сразу загружаться файл excel-формата с экспертами.
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### GET /api/users-to-excel
- **Описание**: Админ может получить всех пользователей в excel-формате.
- **Авторизация**: Есть(и проверка на роль админа).
- ** При успешном ответе админу будет сразу загружаться файл excel-формата с пользователями.
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```

### GET /api/statistics
- **Описание**: Админ может получить статистику в excel-формате.
- **Авторизация**: Есть(и проверка на роль админа).
- ** При успешном ответе админу будет сразу загружаться файл excel-формата статистики.
- **Пример ответа (ошибка)**:
```json
{
    "message": "Доступ запрещен."
}
```


### Избранное
### GET /api/favorites
- **Описание**: Отображает список избранного
- **Авторизация**: Есть.
- **Пример ответа (успех)**:
```json
{
    "id": 1,
    "user_id": 1,
    "expert_id": 2,
    "created_at": "...",
    "updated_at": "..."
}
```

### POST /api/favorites
- **Описание**: Добавляет эксперта в избранное
- **Параметры**:
    - expert_id (required | integer)
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "expert_id": 1
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Эксперт был успешно добавлен в избранное."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Не удалось добавить эксперта в избранное."
}
```

### DELETE /api/favorites
- **Описание**: Отображает список избранного
- **Параметры**:
    - expert_id (required | integer)
- **Авторизация**: Есть.
- **Пример запроса**:
```json
{
    "expert_id": 1
}
```
- **Пример ответа (успех)**:
```json
{
    "message": "Эксперт успешно удален из избранного."
}
```
- **Пример ответа (ошибка)**:
```json
{
    "message": "Запись не найдена."
}
```
