<?php

namespace Tests\Feature\CampusApi;

use App\Http\Middleware\SecurityHeaders;
use App\Services\CampusApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class Milestone1ChallengerTest extends TestCase
{
    protected string $bridgeUrl = 'https://bridge.uinssc.ac.id';
    protected string $sahajaUrl = 'https://sahaja.uinssc.ac.id';
    protected string $defaultSahajaKey = 'shj_live_8f92a4c7e1b6d0359a2c4e7b1d8f3a6c';

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('campus_api_health_status');

        Config::set('services.campus_api.base_url', $this->bridgeUrl);
        Config::set('services.campus_api.bridge_url', $this->bridgeUrl);
        Config::set('services.campus_api.sahaja_url', $this->sahajaUrl);
        Config::set('services.campus_api.key', $this->defaultSahajaKey);
        Config::set('services.campus_api.sahaja_key', $this->defaultSahajaKey);
        Config::set('services.campus_api.timeout', 5);
        Config::set('services.campus_api.bridge_timeout', 5);
        Config::set('services.campus_api.sahaja_timeout', 5);
        Config::set('services.campus_api.connect_timeout', 1);
        Config::set('services.campus_api.verify_ssl', false);
        Config::set('services.campus_api.bridge_verify_ssl', false);
        Config::set('services.campus_api.sahaja_verify_ssl', false);
        Config::set('services.campus_api.health_cache_ttl', 300);
    }

    // =========================================================================
    // 1. SAHAJA CLIENT & X-API-KEY HEADER EMPIRICAL TESTS
    // =========================================================================

    /**
     * EMP-01: sahajaClient() transmits X-API-KEY header matching configured key
     */
    public function test_sahaja_client_transmits_default_x_api_key_header(): void
    {
        Http::fake([
            'https://sahaja.uinssc.ac.id/api/v1/mahasantri/mukim*' => Http::response(['status' => 'success'], 200),
        ]);

        $service = app(CampusApiService::class);
        $client = $service->sahajaClient();
        $response = $client->get('https://sahaja.uinssc.ac.id/api/v1/mahasantri/mukim');

        $this->assertTrue($response->successful());

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://sahaja.uinssc.ac.id/api/v1/mahasantri/mukim'
                && $request->hasHeader('X-API-KEY', $this->defaultSahajaKey)
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    /**
     * EMP-02: sahajaClient($customKey) transmits overridden X-API-KEY header
     */
    public function test_sahaja_client_transmits_custom_override_api_key(): void
    {
        $customKey = 'shj_test_custom_override_key_999888';

        Http::fake([
            'https://sahaja.uinssc.ac.id/*' => Http::response(['status' => 'success'], 200),
        ]);

        $service = app(CampusApiService::class);
        $client = $service->sahajaClient($customKey);
        $response = $client->get('https://sahaja.uinssc.ac.id/test-endpoint');

        $this->assertTrue($response->successful());

        Http::assertSent(function (Request $request) use ($customKey) {
            return $request->hasHeader('X-API-KEY', $customKey)
                && !$request->hasHeader('X-API-KEY', $this->defaultSahajaKey);
        });
    }

    /**
     * EMP-03: sahajaClient() omits X-API-KEY safely when key is empty without crashing
     */
    public function test_sahaja_client_omits_x_api_key_when_both_key_and_config_empty(): void
    {
        Config::set('services.campus_api.sahaja_key', '');
        Config::set('services.campus_api.key', '');

        Http::fake([
            'https://sahaja.uinssc.ac.id/*' => Http::response(['status' => 'success'], 200),
        ]);

        $service = new CampusApiService();
        $client = $service->sahajaClient();
        $response = $client->get('https://sahaja.uinssc.ac.id/test-no-key');

        $this->assertTrue($response->successful());

        Http::assertSent(function (Request $request) {
            return !$request->hasHeader('X-API-KEY');
        });
    }

    /**
     * EMP-04: sahajaClient() verifies internal PendingRequest properties (timeout <= 5s, connectTimeout <= 1.0s)
     */
    public function test_sahaja_client_applies_timeout_and_connect_timeout(): void
    {
        $service = app(CampusApiService::class);
        $client = $service->sahajaClient();

        $options = $client->getOptions();
        $this->assertSame(5, $options['timeout'] ?? null, 'sahajaClient timeout must be 5s');
        $this->assertLessThanOrEqual(5, $options['timeout'] ?? null, 'sahajaClient timeout must not exceed 5s');
        $this->assertSame(1.0, (float) ($options['connect_timeout'] ?? 0), 'connectTimeout must be 1.0s');
    }

    // =========================================================================
    // 2. CHECKHEALTH & CACHE TTL EMPIRICAL TESTS
    // =========================================================================

    /**
     * EMP-05: checkHealth() caches positive result for >= 300 seconds and does not re-query
     */
    public function test_check_health_caches_positive_status_for_at_least_300_seconds(): void
    {
        $requestCount = 0;
        Http::fake([
            'https://bridge.uinssc.ac.id*' => function () use (&$requestCount) {
                $requestCount++;
                return Http::response(['status' => 'healthy'], 200);
            },
        ]);

        $service = app(CampusApiService::class);

        // 1st call: Cache miss, network queried
        $res1 = $service->checkHealth();
        $this->assertTrue($res1);
        $this->assertSame(1, $requestCount, 'Initial checkHealth must trigger exactly 1 HTTP request');
        $this->assertTrue(Cache::has('campus_api_health_status'));

        // 2nd call: Immediate repeat, must hit cache (0 additional network requests)
        $res2 = $service->checkHealth();
        $this->assertTrue($res2);
        $this->assertSame(1, $requestCount, 'Immediate repeat checkHealth must NOT trigger additional HTTP request');

        // Fast-forward 299 seconds: still inside 300s TTL
        $this->travel(299)->seconds();
        $res3 = $service->checkHealth();
        $this->assertTrue($res3);
        $this->assertSame(1, $requestCount, 'Call at 299s must STILL be served from cache');

        // Fast-forward 2 more seconds: total 301 seconds, TTL expired
        $this->travel(2)->seconds();
        $res4 = $service->checkHealth();
        $this->assertTrue($res4);
        $this->assertSame(2, $requestCount, 'Call at 301s must refresh cache and trigger 2nd HTTP request');
    }

    /**
     * EMP-06: checkHealth(true) forces cache invalidation and immediate re-query
     */
    public function test_check_health_force_refresh_bypasses_cache_immediately(): void
    {
        Cache::put('campus_api_health_status', true, 300);

        $requestCount = 0;
        Http::fake([
            'https://bridge.uinssc.ac.id*' => function () use (&$requestCount) {
                $requestCount++;
                return Http::response(['status' => 'healthy'], 200);
            },
        ]);

        $service = app(CampusApiService::class);

        // Without force refresh: 0 requests
        $this->assertTrue($service->checkHealth(false));
        $this->assertSame(0, $requestCount);

        // With forceRefresh = true: 1 request
        $this->assertTrue($service->checkHealth(true));
        $this->assertSame(1, $requestCount, 'checkHealth(true) must bypass cache and query network');
    }

    /**
     * EMP-07: healthCheck() alias behaves identically to checkHealth()
     */
    public function test_health_check_backward_compatibility_alias(): void
    {
        Cache::put('campus_api_health_status', true, 300);

        $requestCount = 0;
        Http::fake([
            'https://bridge.uinssc.ac.id*' => function () use (&$requestCount) {
                $requestCount++;
                return Http::response(['status' => 'healthy'], 200);
            },
        ]);

        $service = app(CampusApiService::class);

        // healthCheck(false) hits cache
        $this->assertTrue($service->healthCheck(false));
        $this->assertSame(0, $requestCount);

        // healthCheck(true) forces refresh
        $this->assertTrue($service->healthCheck(true));
        $this->assertSame(1, $requestCount);
    }

    /**
     * EMP-08: checkHealth() gracefully handles server offline (503 / timeout) and caches false
     */
    public function test_check_health_caches_failure_gracefully_when_server_offline(): void
    {
        $requestCount = 0;
        Http::fake([
            'https://bridge.uinssc.ac.id*' => function () use (&$requestCount) {
                $requestCount++;
                return Http::response(['error' => 'Service Unavailable'], 503);
            },
        ]);

        $service = app(CampusApiService::class);

        $res1 = $service->checkHealth();
        $this->assertFalse($res1);
        $this->assertSame(2, $requestCount, 'Failure tries root then /health before declaring offline');

        // Assert false is cached
        $this->assertTrue(Cache::has('campus_api_health_status'));
        $this->assertFalse(Cache::get('campus_api_health_status'));

        // Immediate subsequent call must hit cache and return false with zero new calls
        $res2 = $service->checkHealth();
        $this->assertFalse($res2);
        $this->assertSame(2, $requestCount, 'Subsequent call must return cached false without re-querying');
    }

    // =========================================================================
    // 3. BOUNDS, FALLBACKS & RESILIENCE EMPIRICAL TESTS
    // =========================================================================

    /**
     * EMP-09: health_cache_ttl is strictly bounded to max(300, config)
     */
    public function test_health_cache_ttl_floor_enforcement_at_least_300_seconds(): void
    {
        // Test values below 300: 0, 50, 120, 299
        foreach ([0, 50, 120, 299] as $invalidTtl) {
            Config::set('services.campus_api.health_cache_ttl', $invalidTtl);
            $service = new CampusApiService();
            $this->assertGreaterThanOrEqual(300, $service->getHealthCacheTtl(), "TTL {$invalidTtl} must be elevated to >= 300s");
            $this->assertSame(300, $service->getHealthCacheTtl());
        }

        // Test valid value >= 300: 600
        Config::set('services.campus_api.health_cache_ttl', 600);
        $service = new CampusApiService();
        $this->assertSame(600, $service->getHealthCacheTtl());
    }

    /**
     * EMP-10: connect_timeout is strictly clamped between 0.1s and 1.0s
     */
    public function test_connect_timeout_clamped_between_0_1_and_1_0_seconds(): void
    {
        // Too high (> 1.0s) -> clamped to 1.0s
        Config::set('services.campus_api.connect_timeout', 10);
        $serviceHigh = new CampusApiService();
        $this->assertSame(1.0, $serviceHigh->getConnectTimeout());

        // Too low (<= 0s) -> clamped to 0.1s
        Config::set('services.campus_api.connect_timeout', 0);
        $serviceLow = new CampusApiService();
        $this->assertSame(0.1, $serviceLow->getConnectTimeout());

        // Negative -> clamped to 0.1s
        Config::set('services.campus_api.connect_timeout', -5);
        $serviceNeg = new CampusApiService();
        $this->assertSame(0.1, $serviceNeg->getConnectTimeout());

        // In range -> exact
        Config::set('services.campus_api.connect_timeout', 0.5);
        $serviceMid = new CampusApiService();
        $this->assertSame(0.5, $serviceMid->getConnectTimeout());
    }

    /**
     * EMP-11: Fallbacks for bridge_url, sahaja_url, and sahaja_key
     */
    public function test_configuration_fallbacks_resolve_safely(): void
    {
        // Case A: Missing sahaja_key falls back to campus_api.key
        Config::set('services.campus_api.sahaja_key', null);
        Config::set('services.campus_api.key', 'legacy_fallback_key');
        $serviceA = new CampusApiService();
        $this->assertSame('legacy_fallback_key', $serviceA->getSahajaKey());

        // Case B: bridge_url missing falls back to base_url
        Config::set('services.campus_api.bridge_url', null);
        Config::set('services.campus_api.base_url', 'https://fallback-base.test');
        $serviceB = new CampusApiService();
        $refB = new ReflectionClass($serviceB);
        $bridgeUrlProp = $refB->getProperty('bridgeUrl');
        $bridgeUrlProp->setAccessible(true);
        $this->assertSame('https://fallback-base.test', $bridgeUrlProp->getValue($serviceB));

        // Case C: sahaja_url missing falls back to base_url
        Config::set('services.campus_api.sahaja_url', null);
        $serviceC = new CampusApiService();
        $refC = new ReflectionClass($serviceC);
        $sahajaUrlProp = $refC->getProperty('sahajaUrl');
        $sahajaUrlProp->setAccessible(true);
        $this->assertSame('https://fallback-base.test', $sahajaUrlProp->getValue($serviceC));
    }

    // =========================================================================
    // 4. SECURITY HEADERS & CSP EMPIRICAL TESTS
    // =========================================================================

    /**
     * EMP-12: SecurityHeaders CSP connect-src dynamically incorporates bridge and sahaja endpoints
     */
    public function test_security_headers_middleware_connect_src_contains_bridge_and_sahaja_urls(): void
    {
        $middleware = new SecurityHeaders();
        $request = HttpRequest::create('/', 'GET');

        $response = $middleware->handle($request, function () {
            return new Response('OK');
        });

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);

        $this->assertStringContainsString("connect-src", $csp);
        $this->assertStringContainsString("'self'", $csp);
        $this->assertStringContainsString("https://bridge.uinssc.ac.id", $csp);
        $this->assertStringContainsString("https://sahaja.uinssc.ac.id", $csp);

        // Security baseline headers
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    /**
     * EMP-13: sahajaClient enforces strict clamping [1s, 5s] on custom timeout and respects SSL configuration
     */
    public function test_sahaja_client_custom_timeout_and_ssl_verification(): void
    {
        // Upper bound clamp: 25s must clamp to 5s
        Config::set('services.campus_api.sahaja_timeout', 25);
        Config::set('services.campus_api.sahaja_verify_ssl', true);

        $service = new CampusApiService();
        $client = $service->sahajaClient();

        $options = $client->getOptions();
        $this->assertSame(5, $options['timeout'] ?? null, 'Custom timeout of 25s must be clamped to 5s');
        $this->assertArrayNotHasKey('verify', $options, 'When verify_ssl is true, withoutVerifying() is not called');

        // Lower bound clamp: 0s or negative must clamp to 1s
        Config::set('services.campus_api.sahaja_timeout', 0);
        $clientLow = (new CampusApiService())->sahajaClient();
        $optionsLow = $clientLow->getOptions();
        $this->assertSame(1, $optionsLow['timeout'] ?? null, 'Custom timeout of 0s must be clamped to 1s');

        // Within-range preservation: 3s remains 3s
        Config::set('services.campus_api.sahaja_timeout', 3);
        $clientMid = (new CampusApiService())->sahajaClient();
        $optionsMid = $clientMid->getOptions();
        $this->assertSame(3, $optionsMid['timeout'] ?? null, 'Custom timeout of 3s must be preserved');

        // When verify_ssl is false
        Config::set('services.campus_api.sahaja_verify_ssl', false);
        $serviceInsecure = new CampusApiService();
        $clientInsecure = $serviceInsecure->sahajaClient();
        $optionsInsecure = $clientInsecure->getOptions();
        $this->assertFalse($optionsInsecure['verify'] ?? true, 'When verify_ssl is false, verify option must be false');
    }

    /**
     * EMP-14: Trailing slashes on base_url, bridge_url, and sahaja_url are safely stripped
     */
    public function test_trailing_slashes_in_urls_are_properly_trimmed(): void
    {
        Config::set('services.campus_api.base_url', 'https://bridge.uinssc.ac.id///');
        Config::set('services.campus_api.bridge_url', 'https://bridge.uinssc.ac.id/');
        Config::set('services.campus_api.sahaja_url', 'https://sahaja.uinssc.ac.id/');

        $service = new CampusApiService();
        $ref = new ReflectionClass($service);

        foreach (['baseUrl', 'bridgeUrl', 'sahajaUrl'] as $propName) {
            $prop = $ref->getProperty($propName);
            $prop->setAccessible(true);
            $val = $prop->getValue($service);
            $this->assertStringEndsNotWith('/', $val, "Property {$propName} must not end with a trailing slash");
        }
    }

    /**
     * EMP-15: checkHealth returns false safely without attempting HTTP request when target URL is empty
     */
    public function test_check_health_returns_false_when_bridge_url_is_empty(): void
    {
        Config::set('services.campus_api.base_url', '');
        Config::set('services.campus_api.bridge_url', '');

        $requestMade = false;
        Http::fake([
            '*' => function () use (&$requestMade) {
                $requestMade = true;
                return Http::response([], 200);
            },
        ]);

        $service = new CampusApiService();
        $res = $service->checkHealth();

        $this->assertFalse($res);
        $this->assertFalse($requestMade, 'Zero HTTP requests must be sent when target URL is empty');
    }

    /**
     * EMP-16: SecurityHeaders neutralizes semicolon injection and directive splitting in CSP
     */
    public function test_security_headers_neutralizes_semicolon_directive_injection(): void
    {
        Config::set('services.campus_api.sahaja_url', 'https://sahaja.uinssc.ac.id; script-src https://evil.com');
        $middleware = new SecurityHeaders();
        $request = HttpRequest::create('/', 'GET');

        $response = $middleware->handle($request, fn() => new Response('OK'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString('https://sahaja.uinssc.ac.id', $csp);
        $this->assertStringNotContainsString('https://evil.com', $csp);
        $this->assertSame(1, substr_count($csp, 'script-src '), 'CSP must contain exactly one script-src directive');
    }

    /**
     * EMP-17: SecurityHeaders rejects CRLF injection and prevents HTTP response splitting
     */
    public function test_security_headers_rejects_crlf_header_injection(): void
    {
        Config::set('services.campus_api.sahaja_url', "https://sahaja.uinssc.ac.id\r\nX-Injected: evil");
        $middleware = new SecurityHeaders();
        $request = HttpRequest::create('/', 'GET');

        $response = $middleware->handle($request, fn() => new Response('OK'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringNotContainsString("\r", $csp);
        $this->assertStringNotContainsString("\n", $csp);
        $this->assertStringNotContainsString('X-Injected', $csp);
    }

    /**
     * EMP-18: SecurityHeaders extracts clean origins and preserves non-standard ports
     */
    public function test_security_headers_extracts_clean_origin_and_non_standard_port(): void
    {
        Config::set('app.url', 'http://127.0.0.1:8080');
        Config::set('services.campus_api.bridge_url', 'https://bridge.uinssc.ac.id:8443/api/v2/login?key=val#hash');

        $middleware = new SecurityHeaders();
        $request = HttpRequest::create('/', 'GET');

        $response = $middleware->handle($request, fn() => new Response('OK'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('http://127.0.0.1:8080', $csp);
        $this->assertStringContainsString('https://bridge.uinssc.ac.id:8443', $csp);
        $this->assertStringNotContainsString('/api/v2/login', $csp);
        $this->assertStringNotContainsString('?key=val', $csp);
    }

    /**
     * EMP-19: services.php enforces secure-by-default SSL verification fallback (true)
     */
    public function test_services_config_defaults_ssl_verification_to_true_when_env_unset(): void
    {
        $backupCampus = $_ENV['CAMPUS_API_VERIFY_SSL'] ?? null;
        $backupBridge = $_ENV['BRIDGE_API_VERIFY_SSL'] ?? null;
        $backupSahaja = $_ENV['SAHAJA_API_VERIFY_SSL'] ?? null;

        unset($_ENV['CAMPUS_API_VERIFY_SSL'], $_SERVER['CAMPUS_API_VERIFY_SSL']);
        unset($_ENV['BRIDGE_API_VERIFY_SSL'], $_SERVER['BRIDGE_API_VERIFY_SSL']);
        unset($_ENV['SAHAJA_API_VERIFY_SSL'], $_SERVER['SAHAJA_API_VERIFY_SSL']);
        putenv('CAMPUS_API_VERIFY_SSL');
        putenv('BRIDGE_API_VERIFY_SSL');
        putenv('SAHAJA_API_VERIFY_SSL');

        try {
            $services = require config_path('services.php');

            $this->assertTrue($services['campus_api']['verify_ssl']);
            $this->assertTrue($services['campus_api']['bridge_verify_ssl']);
            $this->assertTrue($services['campus_api']['sahaja_verify_ssl']);
            $this->assertTrue($services['bridge']['verify_ssl']);
            $this->assertTrue($services['sahaja']['verify_ssl']);
        } finally {
            if ($backupCampus !== null) {
                $_ENV['CAMPUS_API_VERIFY_SSL'] = $backupCampus;
                putenv("CAMPUS_API_VERIFY_SSL={$backupCampus}");
            }
            if ($backupBridge !== null) {
                $_ENV['BRIDGE_API_VERIFY_SSL'] = $backupBridge;
                putenv("BRIDGE_API_VERIFY_SSL={$backupBridge}");
            }
            if ($backupSahaja !== null) {
                $_ENV['SAHAJA_API_VERIFY_SSL'] = $backupSahaja;
                putenv("SAHAJA_API_VERIFY_SSL={$backupSahaja}");
            }
        }
    }
}

