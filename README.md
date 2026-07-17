# Jak uruchmoć projekt
1. Na swoim komputerze potrzebujesz programów Git oraz Docker (z wtyczką Docker Compose).
2. Pobierz projekt z repozytorium `git clone https://github.com/MarekMikusek/booking-api.git`.
3. Wejdź do katalogu projektu `cd booking-api`.
4. Przygotuj plik konfiguracyjny `cp .env.example .env`
5. Zbuduj i uruchom kontenery: `docker compose up -d --build`
6. Wygeneruj klucz aplikacji `docker compose exec booking-api php artisan key:generate`
7. Uruchmom tworzenie tabel w bazie danych i wstawienie danych pierwszej lokalizacji `docker compose exec booking-api php artisan migrate --seed`
8. Dużą ilość rezerwacji można wygenerowac komendą `php artisan reservations:generate count=20000 chunk=500`

# Założenia
1. Nie implementuję logiki związanej z autoryzacją, uprawnieniami itp.
2. Zakładam, że wypełnienie rezerwacji będzie nieduże, dla zmniejszenia ilości danych rekordy w tabeli reservations są tworzone gdy jest taka potrzeba. Zmniejszy to ilość danych i ułatwi prowadzenie logiki biznesowej.
3. Zachowanie kolumny ends_at: Mimo stałej długości slotu, zdecydowałem się na przechowywanie ends_at w bazie danych. Zapobiega to zaburzeniu danych historycznych w przypadku zmiany długości slotu w konfiguracji w przyszłości (stare rezerwacje muszą zachować swój pierwotny, 30-minutowy czas trwania) oraz przygotowuje system pod obsługę rezerwacji o zmiennej długości.
4. W przyszłości system będzie używany dla innych lokalizacji. Lokalizacje są umieszczone w bazie danych, dla każdej można ustalić osobne godziny pracy i dlugość trwania slotu. Wartości domyślne ustawione zgodnie z danymi w zadaniu.
5. Zabezpieczenie przed masowym usuwaniem rezerwacji gości za pomocą unikalnych tokenów UUID, zapobiega to nieautoryzowanym działaniom.
6. W tabeli reservations utworzono indeks unikalny na 2 kolumnach (lokalizacja i godzina rozpoczęcia) aby zabezpieczyć tabelę przed wielokrotną rezerwacją tego samego slotu i przyśpieszyć wykoanie zapytania o wolne sloty.

# Kolejne kroki
1. Imlementacja użytkowników i uprawnień.
2. Filtrowanie rezerwacji.
3. Możliwość przesunięcia rezerwacji.

# Dokumentacja API Systemu Rezerwacji

Niniejszy plik zawiera pełną dokumentację punktów końcowych (endpoints) API przygotowaną dla Twojej aplikacji Laravel. Dokumentacja uwzględnia reguły walidacji danych wejściowych, parametry zapytań, nagłówki oraz przykłady żądań i odpowiedzi.

---

## Spis treści
1. [Pobieranie wolnych slotów (GET /api/slots)](#1-pobieranie-wolnych-slotów-get-apislots)
2. [Tworzenie nowej rezerwacji (POST /api/reservations)](#2-tworzenie-nowej-rezerwacji-post-apireservations)
3. [Anulowanie rezerwacji (DELETE /api/reservations/{id})](#3-anulowanie-rezerwacji-delete-apireservationsid)
4. [Dodawanie dnia wolnego / święta (POST /api/holidays)](#4-dodawanie-dnia-wolnego--święta-post-apiholidays)
5. [Standardowe kody odpowiedzi HTTP](#5-standardowe-kody-odpowiedzi-http)

---

## 1. Pobieranie wolnych slotów (GET /api/slots)

Służy do sprawdzania dostępnych godzin rezerwacji w wybranej lokalizacji na dany dzień.

* **Adres URL:** `/api/slots`
* **Metoda HTTP:** `GET`
* **Nagłówki:** `Accept: application/json`

### Parametry zapytania (Query Parameters)

| Parametr | Typ | Wymagany | Reguły walidacji | Opis |
| :--- | :--- | :---: | :--- | :--- |
| `date` | `string` (date) | **Tak** | `date_format:Y-m-d`, `after_or_equal:today` | Data, dla której chcemy sprawdzić wolne terminy. Nie może być z przeszłości. |
| `location_id` | `integer` | Nie | `exists:locations,id` | ID lokalizacji. W przypadku braku, system domyślnie przyjmuje wartość `1`. |

### Przykład żądania

```http
GET /api/slots?date=2026-07-20&location_id=1 HTTP/1.1
Host: localhost
Accept: application/json
```

### Przykład poprawnej odpowiedzi (`200 OK`)

```json
{
  "date": "2026-07-20",
  "location_id": 1,
  "available_slots": [
    "09:00",
    "09:30",
    "10:00",
    "10:30",
    "14:00",
    "14:30"
  ]
}
```

---

## 2. Tworzenie nowej rezerwacji (POST /api/reservations)

Umożliwia klientowi dokonanie rezerwacji na określony termin w wybranej lokalizacji.

* **Adres URL:** `/api/reservations`
* **Metoda HTTP:** `POST`
* **Nagłówki:** `Content-Type: application/json`, `Accept: application/json`

### Parametry żądania (Request Body)

| Parametr | Typ | Wymagany | Reguły walidacji | Opis |
| :--- | :--- | :---: | :--- | :--- |
| `location_id` | `integer` | Nie | `exists:locations,id` | ID lokalizacji, w której dokonywana jest rezerwacja (domyślnie `1`). |
| `customer_name` | `string` | **Tak** | `max:255` | Imię i nazwisko klienta dokonującego rezerwacji. |
| `customer_email` | `string` (email) | **Tak** | `email`, `max:255` | Poprawny adres e-mail klienta, na który zostanie przesłany m.in. token. |
| `starts_at` | `string` (datetime) | **Tak** | `date`, `after:now` | Data i godzina rozpoczęcia rezerwacji. Musi to być data przyszła. |

### Przykład żądania

```http
POST /api/reservations HTTP/1.1
Host: localhost
Content-Type: application/json
Accept: application/json

{
  "location_id": 1,
  "customer_name": "Jan Kowalski",
  "customer_email": "jan.kowalski@example.com",
  "starts_at": "2026-07-20 14:00:00"
}
```

### Przykład poprawnej odpowiedzi (`201 Created`)

```json
{
  "message": "Rezerwacja została pomyślnie utworzona!",
  "data": {
    "id": 42,
    "location_id": 1,
    "customer_name": "Jan Kowalski",
    "customer_email": "jan.kowalski@example.com",
    "starts_at": "2026-07-20T14:00:00.000000Z",
    "ends_at": "2026-07-20T15:00:00.000000Z",
    "status": "active",
    "token": "d290f1ee-6c54-4b01-90e6-d701748f0851",
    "created_at": "2026-07-17T10:19:45.000000Z",
    "updated_at": "2026-07-17T10:19:45.000000Z"
  }
}
```

---

## 3. Anulowanie rezerwacji (DELETE /api/reservations/{id})

Umożliwia anulowanie istniejącej rezerwacji. Wymaga podania jej identyfikatora oraz unikalnego tokenu UUID.

* **Adres URL:** `/api/reservations/{id}`
* **Metoda HTTP:** `DELETE`
* **Nagłówki:** `Accept: application/json`

### Parametry ścieżki i zapytania (Parameters)

| Miejsce | Parametr | Typ | Wymagany | Reguły walidacji | Opis |
| :--- | :--- | :--- | :---: | :--- | :--- |
| **Path** | `id` | `integer` | **Tak** | - | Identyfikator (ID) rezerwacji do usunięcia. |
| **Query** | `token` | `string` (UUID) | **Tak** | `uuid` | Token zabezpieczający przypisany do tej rezerwacji. |

### Przykład żądania

```http
DELETE /api/reservations/42?token=d290f1ee-6c54-4b01-90e6-d701748f0851 HTTP/1.1
Host: localhost
Accept: application/json
```

### Przykład poprawnej odpowiedzi (`200 OK`)

```json
{
  "message": "Rezerwacja została pomyślnie anulowana."
}
```

---

## 4. Dodawanie dnia wolnego / święta (POST /api/holidays)

Umożliwia administratorowi dodanie dnia, w którym placówka/lokalizacja jest nieczynna i nie można w tym dniu rezerwować terminów.

* **Adres URL:** `/api/holidays`
* **Metoda HTTP:** `POST`
* **Nagłówki:** `Content-Type: application/json`, `Accept: application/json`

### Parametry żądania (Request Body)

| Parametr | Typ | Wymagany | Reguły walidacji | Opis |
| :--- | :--- | :---: | :--- | :--- |
| `date` | `string` (date) | **Tak** | `date`, `unique:holidays,date` | Data święta. Musi być unikalna w bazie danych. |
| `name` | `string` | Nie | `max:255` | Nazwa święta / opcjonalny powód (np. "Boże Narodzenie"). |

### Przykład żądania

```http
POST /api/holidays HTTP/1.1
Host: localhost
Content-Type: application/json
Accept: application/json

{
  "date": "2026-11-11",
  "name": "Narodowe Święto Niepodległości"
}
```

### Przykład poprawnej odpowiedzi (`201 Created`)

```json
{
  "message": "Dzień wolny został pomyślnie dodany.",
  "data": {
    "id": 5,
    "date": "2026-11-11",
    "name": "Narodowe Święto Niepodległości",
    "created_at": "2026-07-17T10:19:45.000000Z",
    "updated_at": "2026-07-17T10:19:45.000000Z"
  }
}
```

---

## 5. Standardowe kody odpowiedzi HTTP

Wszystkie błędy są zwracane w ujednoliconej formie JSON, typowej dla frameworka Laravel.

*   **`200 OK`** – Zapytanie zakończyło się sukcesem (np. przy pobieraniu slotów lub anulowaniu rezerwacji).
*   **`201 Created`** – Zasób został pomyślnie utworzony (np. nowa rezerwacja lub dzień wolny).
*   **`422 Unprocessable Content`** – Błąd walidacji danych wejściowych. Odpowiedź zawiera listę niespełnionych reguł.
    
    *Przykład błędu walidacji (np. błędna data w `/api/slots`):*
    ```json
    {
      "message": "The date field must be a valid date.",
      "errors": {
        "date": [
          "The date field must be a valid date."
        ]
      }
    }
    ```
*   **`404 Not Found`** – Szukany zasób (np. rezerwacja o podanym ID) nie istnieje.
