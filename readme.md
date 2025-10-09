## @blumilksoftware/MiniStravaAPI
### About application
> placeholder

### Local development
```
cp .env.example .env
task init
task vite
```
Application will be running under [localhost:83851](localhost:83851) and [http://MiniStravaAPI.blumilk.localhost/](http://MiniStravaAPI.blumilk.localhost/) in Blumilk traefik environment. If you don't have a Blumilk traefik environment set up yet, follow the instructions from this [repository](https://github.com/blumilksoftware/environment).

#### Commands
Before running any of the commands below, you must run shell:
```
make shell
```

| Command                 | Task                                        |
|:------------------------|:--------------------------------------------|
| `composer <command>`    | Composer                                    |
| `composer test`         | Runs backend tests                          |
| `composer analyse`      | Runs Larastan analyse for backend files     |
| `composer cs`           | Lints backend files                         |
| `composer csf`          | Lints and fixes backend files               |
| `php artisan <command>` | Artisan commands                            |
| `npm run dev`           | Compiles and hot-reloads for development    |
| `npm run build`         | Compiles and minifies for production        |
| `npm run lint`          | Lints frontend files                        |
| `npm run lintf`         | Lints and fixes frontend files              |
| `npm run tsc`           | Runs TypeScript checker                     |


#### Containers

| service    | container name            | default host port               |
|:-----------|:--------------------------|:--------------------------------|
| `app`      | `MiniStravaAPI-app-dev`     | [83851](http://localhost:83851) |
| `database` | `MiniStravaAPI-db-dev`      | 83853                           |
| `redis`    | `MiniStravaAPI-redis-dev`   | 83852                           |
| `mailpit`  | `MiniStravaAPI-mailpit-dev` | 83854                           |
