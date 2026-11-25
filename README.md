# Animal Facts API — PHP Human Interview Challenge

## Description

A REST API for animal facts built with Laravel 9. It exposes two resources, **Cat Facts** and **Dog Facts**, each with paginated listing, retrieval, creation, update and deletion. All requests and responses are JSON.

This repository is my solution to the Jobsity PHP Human Interview Challenge, which asked for two things: fix the bug in storing cat facts, and build Dog Facts following the conventions already in place for Cat Facts.

| | What it was | What it is now |
|---|---|---|
| **Resources** | Cat Facts only | Cat Facts and Dog Facts, with the same five endpoints each |
| **Storing a cat fact** | Always rejected with `403` | Works |
| **Installing** | `composer install` failed on current PHP versions | Installs with the documented command |
| **Starting** | `vendor/bin/sail up` failed to build the image | Starts on PHP 8.2 and MySQL 8.0 |
| **Pagination links** | Dropped `pageSize`, so the next page fell back to 25 items | Keep the requested `pageSize` |
| **Tests** | 18 tests, 4 failing | 46 tests, all passing |
| **Documentation** | The challenge statement and setup steps | Setup, API reference, structure, decisions and the AI conversation log |

The original statement is summarised in [The challenge](#the-challenge), and each change is explained in [What was done](#what-was-done).

## Contents

- [Description](#description)
- [Requirements](#requirements)
- [Setup](#setup)
- [Running the tests](#running-the-tests)
- [API reference](#api-reference)
- [Project structure](#project-structure)
- [The challenge](#the-challenge)
- [What was done](#what-was-done)
- [Notes for evaluators](#notes-for-evaluators)
- [AI usage](#ai-usage)

## Requirements

- Docker with Docker Compose
- Free ports on the host: `8042` (API) and `33042` (MySQL)

PHP and Composer are not needed on the host. Everything runs in containers through [Laravel Sail](https://laravel.com/docs/9.x/sail) on PHP 8.2 and MySQL 8.0.

## Setup

1. Clone the repository and enter it.

2. Create the environment file. The example needs no changes.

   ```bash
   cp .env.example .env
   ```

3. Install the PHP dependencies.

   ```bash
   docker run --rm -v "$(pwd)":/opt -w /opt composer:2 bash -c "composer install"
   ```

4. Start the containers in the background. The first run builds the image and takes a few minutes.

   ```bash
   vendor/bin/sail up -d
   ```

5. Generate the application key.

   ```bash
   vendor/bin/sail artisan key:generate
   ```

6. Create the tables and seed them with 250 cat facts and 250 dog facts.

   ```bash
   vendor/bin/sail artisan migrate --seed
   ```

   If this fails with a connection error, MySQL is still starting; wait a few seconds and run it again.

7. Open [http://localhost:8042/api/v1](http://localhost:8042/api/v1). The response should be:

   ```json
   {"name":"Jobsity PHP Human Interview challenge API","version":1}
   ```

Useful commands afterwards:

| Command | What it does |
|---|---|
| `vendor/bin/sail up -d` | Start the API and the database |
| `vendor/bin/sail down` | Stop them |
| `vendor/bin/sail down -v` | Stop them and delete the database volume |
| `vendor/bin/sail logs -f` | Follow the logs |
| `vendor/bin/sail artisan route:list --path=api` | List the API routes |

## Running the tests

```bash
vendor/bin/sail test
```

The suite has 46 tests and all of them pass.

The tests run against the same database as the local environment and rely on the seeded data (for example, the "page 2" tests need more than one page of facts). Run `vendor/bin/sail artisan migrate --seed` before the first test run. Tests clean up the rows they create.

## API reference

Base URL: `http://localhost:8042/api/v1`

Both resources have the same endpoints. Replace `dog-facts` with `cat-facts` for Cat Facts.

| Method | Path | Description | Success |
|---|---|---|---|
| `GET` | `/dog-facts` | Paginated list | `200` |
| `GET` | `/dog-facts/{id}` | Retrieve one fact | `200` |
| `POST` | `/dog-facts` | Store a fact | `200` |
| `PUT` | `/dog-facts/{id}` | Update a fact | `200` |
| `DELETE` | `/dog-facts/{id}` | Delete a fact | `204` |

### Fact object

```json
{
  "id": 1,
  "fact": "This is dog fact number 1",
  "created_at": "2026-11-25T14:25:09.000000Z",
  "updated_at": "2026-11-25T14:25:09.000000Z"
}
```

### Listing and pagination

| Query parameter | Default | Description |
|---|---|---|
| `page` | `1` | Page to return |
| `pageSize` | `25` | Facts per page |

The default page size comes from `PAGINATION_DEFAULT_SIZE` (see `config/pagination.php`).

```bash
curl "http://localhost:8042/api/v1/dog-facts?pageSize=2&page=3"
```

```json
{
  "current_page": 3,
  "data": [
    {"id": 5, "fact": "This is dog fact number 5", "created_at": "...", "updated_at": "..."},
    {"id": 6, "fact": "This is dog fact number 6", "created_at": "...", "updated_at": "..."}
  ],
  "first_page_url": "http://localhost:8042/api/v1/dog-facts?pageSize=2&page=1",
  "from": 5,
  "last_page": 125,
  "last_page_url": "http://localhost:8042/api/v1/dog-facts?pageSize=2&page=125",
  "links": ["..."],
  "next_page_url": "http://localhost:8042/api/v1/dog-facts?pageSize=2&page=4",
  "path": "http://localhost:8042/api/v1/dog-facts",
  "per_page": 2,
  "prev_page_url": "http://localhost:8042/api/v1/dog-facts?pageSize=2&page=2",
  "to": 6,
  "total": 250
}
```

When `pageSize` is sent, the page links carry it, so following `next_page_url` keeps the same page size.

### Retrieve

```bash
curl http://localhost:8042/api/v1/dog-facts/1
```

### Store

The body must be JSON. `fact` is required and must be a string.

```bash
curl -X POST http://localhost:8042/api/v1/dog-facts \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"fact": "Dogs have about 300 million olfactory receptors"}'
```

### Update

Same body rules as store.

```bash
curl -X PUT http://localhost:8042/api/v1/dog-facts/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"fact": "A dog nose print is unique"}'
```

### Delete

```bash
curl -X DELETE http://localhost:8042/api/v1/dog-facts/1 -H "Accept: application/json"
```

### Errors

Send `Accept: application/json` so that errors are also returned as JSON.

| Status | When |
|---|---|
| `404` | The fact does not exist |
| `422` | `fact` is missing, null or not a string |
| `500` | The fact could not be saved or deleted |

A validation error looks like this:

```json
{
  "message": "The fact field is required.",
  "errors": {
    "fact": ["The fact field is required."]
  }
}
```

## Project structure

Dog Facts mirrors the structure that already existed for Cat Facts:

```
app/
  Http/
    Controllers/API/V1/{CatFacts,DogFacts}/Rest/   one invokable controller per action
    Requests/API/V1/{CatFacts,DogFacts}/Rest/      validation for store and update
  Models/                                          CatFact, DogFact
  Repositories/                                    CatFactRepository, DogFactRepository
database/
  migrations/                                      cat_facts and dog_facts tables
  seeders/                                         250 facts for each resource
routes/
  api.php                                          /api/v1 group
  v1/cat-facts.php, v1/dog-facts.php               resource routes
tests/Feature/
  API/V1/{CatFacts,DogFacts}/Rest/                 endpoint tests
  Repositories/                                    repository tests
```

Controllers handle HTTP only: they validate through a form request, call the repository and turn the result into a JSON response. Repositories are the only place that talks to Eloquent, and controllers receive them through the constructor, which is what lets the tests replace them with mocks to simulate storage failures.

## The challenge

1. **Fix the cat facts storing.** There is an error in the code; find it, fix it and make all feature tests pass.
2. **Build Dog Facts.** Following the conventions in place for Cat Facts, create the infrastructure and endpoints to list, retrieve, store, update and delete Dog Facts, with feature tests.
   - The list is paginated, the page size can be chosen, the default is 25 and further pages can be requested.
   - The fact text is required to store or update a fact.

## What was done

**Setup fixes.** The project did not start as delivered:

- `composer install` failed on current PHP versions because `composer.lock` pinned old dependency versions. The lock file was updated within the existing constraints (the project stays on Laravel 9), and `composer.json` pins the platform to PHP 8.1 so the resolved versions are stable.
- `vendor/bin/sail up` failed because the Sail PHP 8.1 runtime installs `php8.1-swoole`, which is no longer available in the PHP apt repository. `docker-compose.yml` now uses the PHP 8.2 runtime, which Laravel 9 supports.

**Cat facts bug.** `StoreRequest::authorize()` for cat facts returned `false`, so every `POST /api/v1/cat-facts` was rejected with `403` before validation ran. It now returns `true`, like the update request. This took the suite from 4 failing tests to all passing.

**Dog Facts.** Added the migration, model, seeder, repository, form requests, the five controllers, the routes and the feature tests, following the Cat Facts conventions.

**Beyond the statement:**

- Pagination links now keep `pageSize`. Before, following `next_page_url` silently fell back to 25 per page. This was fixed for both resources.
- Dog Facts has an explicit test for the default page size of 25, and repository-level tests in addition to the endpoint tests.

## Notes for evaluators

- **No authentication.** The API is open, as it was for Cat Facts; the form requests authorize every caller.
- **Shared test database.** Tests use the local database and depend on seeded data. This is how the existing Cat Facts tests worked, so Dog Facts follows the same approach instead of introducing a separate test database.
- **Store returns `200`.** A `201 Created` would be more conventional, but the existing Cat Facts tests assert `200`, and Dog Facts was kept consistent with them.
- **Duplicated code.** The Dog Facts classes are near copies of the Cat Facts ones. The statement asks to follow the existing conventions, so I did not introduce a shared abstraction (a base repository or generic controllers). With a third animal, that refactor would be worth doing.
- **Laravel 9 is end-of-life.** `composer audit` reports advisories against `laravel/framework`. Upgrading the framework major version was out of scope for this challenge.
- **Git history.** Each step is a separate commit: the Sail fix and the cat facts fix went through their own branches and pull requests, and Dog Facts was built incrementally on `feature/dog-facts`.

## AI usage

This solution is AI-assisted, as allowed by the challenge's AI usage policy. The full conversations, with my annotations, are in [`prompt.md`](prompt.md).

**Tool:** Claude Code (Anthropic's CLI), running the Claude Fable 5.2 model.

### How I work with AI

I direct the work and the AI writes the code. Decisions about code structure, tests and validation are mine; what I delegate is the writing itself, and I review what comes back before it is committed.

On new work, the process is:

1. **Describe what I need**, pointing at the structures that already exist in the codebase, so the new code follows them instead of inventing its own.
2. **Have the code and its tests written together**, test-first where possible (TDD), covering what needs to be covered.
3. **Validate the result myself**: read the code, run the suite and exercise the behaviour.
4. **Commit in small increments** and open a pull request describing how things were, what was done and how they are now.

On a project that is already in progress, I start one step earlier: **tests first, to cover the code that exists**, and only then the change, followed by the same process.

### How that went in this challenge

- **Existing code first.** The Cat Facts suite was already there and was the starting point: its four failing store tests located the bug, and the same tests confirmed the fix.
- **Existing structures as the specification.** Dog Facts was built by pointing the AI at the Cat Facts conventions (one invokable controller per action, form requests, a repository, feature tests), in increments I paced and reviewed: data layer, repository, HTTP layer, documentation.
- **Tests.** The Dog Facts tests were written alongside each increment, mirroring the Cat Facts ones, rather than strictly before the code. I held the repository back until it had its own automated test, and the suite gained a check for the default page size, which the original tests did not cover.
- **Corrections.** What I changed, rejected or sent back is annotated in `prompt.md`: for example the placeholder migration timestamp, a repository interface suggested from the wrong version of the statement, the decision to keep `pageSize` in the pagination links for both resources, and how the work was split into commits.
- **Validation.** The full suite ran after every increment (from 18 tests to 46), plus manual requests against the running API: pagination, validation errors and the store, update and delete cycle.
- **Git.** Branches, pull requests and merges are mine. The Sail fix and the cat facts fix each went through their own branch and pull request, and Dog Facts was developed on `feature/dog-facts`.
