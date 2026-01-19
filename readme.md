# @MRR/MiniStravaAPI

## About
Laravel 11 + Filament backend dla aplikacji mobilnej MiniStrava. Oferuje auth (rejestracja, logowanie, reset hasła), aktywności z GPS (lista, szczegóły, zdjęcia, podsumowania, eksport GPX/CSV), profile użytkowników (edycja, avatar, eksport CSV), statystyki i rankingi, zarządzanie tokenami push oraz dokumentację OpenAPI pod `/api/documentation`.

## Stack
- Laravel 11
- PostgreSQL
- Redis (cache, kolejki, push tokens)
- Filament (panel admina)

## Local development
```bash
cp .env.example .env
task init
task run         
```
