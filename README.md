##Jak uruchmoć projekt
1. Zainstaluj wymagane narzędzia: Na swoim komputerze potrzebujesz jedynie programów Git oraz Docker (z wtyczką Docker Compose).
2. Pobierz projekt z repozytorium `git clone https://github.com/MarekMikusek/booking-api.git`.
3. Wejdź do katalogu projektu `cd booking-api`.
4. Przygotuj plik konfiguracyjny `cp .env.example .env`
5. Zbuduj i uruchom kontenery: `docker compose up -d --build`
6. Wygeneruj klucz aplikacji `docker compose exec booking-api php artisan key:generate`
7. Uruchmom tworzenie tabel w bazie danych i wstawienie danych pierwszej lokalizacji `docker compose exec booking-api php artisan migrate --seed`

##Założenia
1. Nie implementuję logiki związanej z autoryzacją, uprawnieniami itp.
2. Zakładam, że wypełnienie rezerwacji będzie nieduże, dla zmniejszenia ilości danych rekordy w tabeli reservations są tworzone gdy jest taka potrzeba. Zmniejszy to ilość danych i ułatwi prowadzenie logiki biznesowej.
3. Zachowanie kolumny ends_at: Mimo stałej długości slotu, zdecydowałem się na przechowywanie ends_at w bazie danych. Zapobiega to zaburzeniu danych historycznych w przypadku zmiany długości slotu w konfiguracji w przyszłości (stare rezerwacje muszą zachować swój pierwotny, 30-minutowy czas trwania) oraz przygotowuje system pod obsługę rezerwacji o zmiennej długości.
4. W przyszłości system będzie używany dla innych lokalizacji. Lokalizacje są umieszczone w bazie danych, dla każdej można ustalić osobne godziny pracy i dlugość trwania slotu. Wartości domyślne ustawione zgodnie z danymi w zadaniu.
5. Zabezpieczenie przed masowym usuwaniem rezerwacji gości za pomocą unikalnych tokenów UUID, zapobiega to nieautoryzowanym działaniom.
6. W tabeli reservations utworzono indeks na 3 kolumnach żeby przyśpieszyć wykoanie zapytania o wolne sloty.
7. Żeby zapobiec rezerwacji tych samych slotów przez różnych użytkowników na zapis nowej rezerwacji założono transakcję z blokadą tabeli dla zapisu.


