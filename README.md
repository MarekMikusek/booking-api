##Jak uruchmoć projekt
1. Zainstaluj programy git, composer, php, docker i docker-compose na swoim komputerze.
2. Pobierz projekt z repozytorium `git clone https://github.com/MarekMikusek/booking-api.git`.
3. Wejdź do katalogu projektu `cd booking-api`.
4. Zainstaluj zależności `composer install`
5. Skopiuj plik konfiguracyjny `cp .env.example .env`
6. Wygeneruj klucz aplikacji `php artisan key:generate`



##Założenia
1. Nie implementuję logiki związanej z autoryzacją, uprawnieniami itp.
2. Zakładam, że wypełnienie rezerwacji będzie nieduże, dla zmniejszenia ilości danych rekordy w tabeli reservations są tworzone gdy jest taka potrzeba. Zmniejszy to ilość danych i ułatwi prowadzenie logiki biznesowej.
3. Zachowanie kolumny ends_at: Mimo stałej długości slotu, zdecydowałem się na przechowywanie ends_at w bazie danych. Zapobiega to zaburzeniu danych historycznych w przypadku zmiany długości slotu w konfiguracji w przyszłości (stare rezerwacje muszą zachować swój pierwotny, 30-minutowy czas trwania) oraz przygotowuje system pod obsługę rezerwacji o zmiennej długości.
4. W przyszłości system będzie używany dla innych lokalizacji. Lokalizacje są umieszczone w bazie danych, dla każdej można ustalić osobne godziny pracy i dlugość trwania slotu. Wartości domyślne ustawione zgodnie z danymi w zadaniu.
5. Zabezpieczenie przed masowym usuwaniem rezerwacji gości za pomocą unikalnych tokenów UUID, zapobiega to nieautoryzowanym działaniom.
6. 



<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

