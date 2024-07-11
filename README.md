# Тестовое задание PHP Developer

## Задание 1

```sql
SELECT
    u.id ID,
    concat(u.first_name, ' ', u.last_name) Name,
    b.author Author,
    group_concat(b.name SEPARATOR ', ') Books
FROM
    users u
        JOIN
    (
        SELECT
            b.id,
            b.name,
            b.author,
            ub.user_id,
            ub.return_date,
            ub.get_date,
            count(*) OVER (PARTITION BY ub.user_id, b.author) AS same_author_amount,
                count(b.id) OVER (PARTITION BY ub.user_id) AS book_amount
        FROM
            user_books ub
                JOIN books b ON
                b.id = ub.book_id
        WHERE datediff(ub.return_date, ub.get_date) <= 14
    ) B ON
        b.user_id = u.id
            AND same_author_amount = 2
            AND book_amount = 2
WHERE
    timestampdiff(YEAR, birthday, now()) BETWEEN 7 AND 17
GROUP BY
    u.id,
    u.FIRST_name,
    u.last_name,
    b.author;
```

## Задание 2

## Требования
- Docker
- Docker Compose
- Make

## Использование

### 1. Запуск Docker контейнеров

Для запуска Docker контейнеров (PHP, Redis, Nginx):

```bash
make start
```
### 2. Установка зависимостей
Для установки зависимостей проекта с использованием Composer:

```bash
make install
```

### 3. Остановка Docker контейнеров
Для остановки Docker контейнеров:

```bash
make stop
```

### 4. Удаление Docker контейнеров, сетей и томов
Для полной остановки и удаления Docker контейнеров, сетей и томов:

```bash
make down
```

### 5. Просмотр логов Docker контейнеров
Для просмотра логов Docker контейнеров:

```bash
make logs
```

## Дополнительные команды

### Обновление зависимостей
Для обновления зависимостей проекта:
```bash
make update
```

### Помощь
Для посмотра списка всех доступных команд:
```bash
make help
```

## Развертывание

```bash
make start && make install
```

## Тестирование

### rates

Для отправки запроса: 
```http request
http://localhost:8080/api/v1?method=rates&<parameter>=<value>
```

Успешный результат:
```json
{
    "status": "success",
    "code": 200,
    "data": {
        "USD": <rate>
    }
}
```
Поле `data` должно содержать курсы всех запрошенных в параметре `currency` валют.
Если параметр не был задан метод должен вернуть курсы всех доступных валют.

В случае невалидных запросов пользователь получит исключение:

Запрос неподдерживаемой валюты:
```json
{
    "status":"error",
    "code":400,
    "message":"Unsupported currency: \"test\""
}
```

Запрос с неккоректным наименованием валют (например пустое значение параметра):
```json
{
    "status":"error",
    "code":400,
    "message":"Invalid currency symbol"
}
```

### convert

Для отправки запроса:
```http request
http://localhost:8080/api/v1?method=convert
```
Формат тела запроса:
```json
{
    "currency_from": <currency_from>,
    "currency_to": <currency_to>,
    "value": <value>
}
```

Успешный результат:

```json
{
    "status": "success",
    "code": 200,
    "data": {
        "currency_from": "EUR",
        "currency_to": "USD",
        "value": 0.01,
        "converted_value": 0.0106269122,
        "rate": 1.0626912153438328
    }
}
```

Поле `data` должно содержать поля `currency_from`, `currency_to`, `value`, `converted_value`, `rate`.
Поле `converted_value` должно содержать количество полученной валюты после обмена. 
Поле `rate` должно содержать курс, по которому был совершен обмен.

В случае невалидных запросов пользователь получит исключение:

Запрос на обмен без USD:
```json
{
    "status":"error",
    "code":400,
    "message":"One of currencies must be \"USD\""
}
```
Запрос неподдерживаемой валюты:
```json
{
    "status":"error",
    "code":400,
    "message":"Unsupported currency: \"test\""
}
```
Запрос без нужных параметров:
```json
{
    "status":"error",
    "code":400,
    "message":"Missing <parameter name>" 
}
```
Запрос с отрицательным значением `value`:
```json
{
    "status":"error",
    "code":400,
    "message":"Value should be positive"
}
```

### Исключения для всего API

Запрос без токена аутентификации либо с не валидным токеном:
```json
{
    "status":"error",
    "code":403,
    "message":"Invalid token"
}
```

Запрос несуществующего метода или запрос без указания имени метода:
```json
{
    "status":"error",
    "code":400,
    "message":"Invalid method name"
}
```

Запрос по неправильному пути к API:
```json
{
  "status":"error",
  "code":400,
  "message":"Invalid API path"
}
```

Запрос с неразрешенным для метода API HTTP методом:
```json
{
    "status":"error",
    "code":405,
    "message":"Invalid HTTP method"
}
```


