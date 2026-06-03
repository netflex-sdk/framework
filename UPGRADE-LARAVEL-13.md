# Upgrading to Netflex Framework 7.x (Laravel 13)

Framework **7.x** requires Laravel 13, PHP 8.4, and the renamed Apriil packages. Stay on **6.x** for Laravel 12.

## Framework dependency changes

| Was (6.x) | Now (7.x) |
|---|---|
| `laravel/framework` ^12 | ^13 |
| `laravel/tinker` ^2 | ^3 |
| `apility/rule-builder` | `apriil/rule-builder` ^1.0 |
| `apility/seotools` | `apriil/seotools` ^1.0 |
| `ultrono/laravel-sitemap` ^9.5 | ^10.0 |
| `nunomaduro/laravel-console-menu` ^3.0 | ^3.7 |

### SEOTools namespace

The Apriil fork uses the `Apriil\SEOTools` namespace (replacing `Apility\SEOTools`). Update imports and facade aliases in application code that references SEOTools directly.

## Laravel 13 application changes

These apply to consumer SDK apps, not the framework package itself:

- **CSRF middleware** — rename references from `VerifyCsrfToken` to `PreventRequestForgery` in tests and route middleware exclusions.
- **Cache / session defaults** — if the app relied on framework-generated cache prefixes or session cookie names, set `CACHE_PREFIX`, `REDIS_PREFIX`, and `SESSION_COOKIE` explicitly in `.env`.
- **Cache objects** — Laravel 13 defaults `cache.serializable_classes` to `false`. Allow-list classes if the app stores PHP objects in cache.
- **Password reset mail** — default subject is now `Reset your password` (was `Reset Password Notification`).

## Suggested upgrade steps

1. Upgrade the SDK app to Laravel 13 per the [Laravel upgrade guide](https://laravel.com/docs/13.x/upgrade).
2. Bump `netflex/framework` to `^7.0` on the `7.x`-compatible branch or tag.
3. Replace `apility/seotools` and `apility/rule-builder` with `apriil/seotools` and `apriil/rule-builder` at `^1.0`.
4. Run `composer update` and fix SEOTools namespace imports.
5. Regression-test dynamic page routing, scheduler jobs, and queued automation emails.
