# Agent Guide - Hyperf Project

This file provides essential information for agentic coding agents working in this Hyperf framework repository.

## Build, Lint, and Test Commands

```bash
# Run all tests
composer test

# Run a single test (replace ExampleTest with your test class)
co-phpunit --prepend test/bootstrap.php --colors=always --filter testExample

# Run tests in a specific file
co-phpunit --prepend test/bootstrap.php --colors=always test/Cases/ExampleTest.php

# Fix code style issues
composer cs-fix

# Fix code style for specific file
php-cs-fixer fix app/Controller/ExampleController.php

# Run static analysis (PHPStan level 0)
composer analyse

# Start the development server
composer start
```

## Project Structure

- `app/` - Application source code
  - `Controller/` - HTTP controllers (extend `AbstractController`)
  - `Logic/` - Business logic layer
  - `Model/` - Database models (extend `App\Model\Model`)
  - `Middleware/` - HTTP middleware
  - `Listener/` - Event listeners
  - `Handler/` - Formatters and handlers
  - `Process/` - Processors
  - `Exception/Handler/` - Exception handlers
- `config/` - Configuration files (routes, autoloading, etc.)
- `test/` - PHPUnit tests (namespace: `HyperfTest`)
  - `Cases/` - Test cases ending with `Test.php`
- `runtime/` - Runtime cache (auto-cleared)

## Code Style Guidelines

### File Header
All PHP files must start with:
```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
```

### Naming Conventions
- Classes: `PascalCase` (e.g., `IndexController`, `IndexLogic`)
- Methods: `camelCase` (e.g., `getInfo`, `index`)
- Properties: `camelCase`
- Constants: `lower_case`
- Test classes: `ExampleTest` (suffix `Test`)
- Namespaces: `App\*` for app code, `HyperfTest\*` for tests

### Imports and Use Statements
- Import classes, constants alphabetically
- Function imports are disabled (`import_functions: null`)
- Use short array syntax `[]` and list syntax
- Use single quotes for strings
- Classes organized: classes → functions → constants (alphabetical)

### Type Safety
- Always declare `strict_types=1`
- Use typed properties and parameters where possible
- Use return types on all methods
- Dependency injection via `#[Inject]` attribute on properties

### Dependency Injection
```php
class ExampleController extends AbstractController
{
    #[Inject]
    protected IndexLogic $indexLogic;
}
```

### Routing
Routes defined in `config/routes.php`:
```php
use Hyperf\HttpServer\Router\Router;

Router::get('/path', 'App\Controller\ExampleController@method');
Router::addRoute(['GET', 'POST'], '/path', 'App\Controller\ExampleController@method');
```

### Controllers
- Extend `App\Controller\AbstractController`
- Access `$this->request`, `$this->response`, `$this->container`
- Return arrays for JSON responses

### Logic Layer
Business logic should go in `app/Logic/` classes, not controllers.

### Database Models
- Extend `App\Model\Model`
- Use Hyperf's Eloquent-style ORM

### Annotations
Use PHP 8 attributes:
- `#[Inject]` for dependency injection
- `#[Cacheable(prefix: 'key', ttl: 60, value: '_#{id}')]` for caching

### Error Handling
Exceptions handled by `App\Exception\Handler\AppExceptionHandler`. Log errors using `StdoutLoggerInterface`.

### Testing
- Extend `Hyperf\Testing\TestCase`
- Use fluent assertions: `$this->get('/')->assertOk()->assertSee('text')`
- Test files in `test/Cases/` directory
- Bootstrap: `test/bootstrap.php`

### Static Analysis
PHPStan runs at level 0 due to magic methods in Hyperf. Ignored errors include static calls to Router:: and Db:: methods.

### Code Quality
- Run `composer cs-fix` before committing
- Run `composer test` to ensure tests pass
- Run `composer analyse` for static analysis
- PHPStan ignores: `#Static call to instance method Hyperf\HttpServer\Router\Router::*#` and `#Static call to instance method Hyperf\DbConnection\Db::*#`

### Requirements
- PHP >= 8.1
- Swoole extension >= 5.0 (set `swoole.use_shortname=Off` in php.ini)
- Extensions: json, pcntl, openssl, pdo, pdo_mysql, redis (as needed)
