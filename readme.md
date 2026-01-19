# @MRR/MiniStravaAPI

## About
MiniStrava is a lightweight fitness-tracking system composed of a mobile app (Android/iOS), a web admin panel, and a REST API. Users record runs, rides, and walks with GPS; view routes, pace, distance, and basic stats; name activities, add notes/photos, and browse/filter history with weekly leaderboards. Admins manage users and activities and see global stats. The API includes OpenAPI docs at /api/documentation and supports GPX export (with optional CSV export and push notifications).

## Stack
- Laravel 11
- PostgreSQL
- Redis

## Local development
```
cp .env.example .env
task init
task vite
```
