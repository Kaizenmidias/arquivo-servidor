<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureCanonicalUrlGeneration();

        if (env('TRAE_DEBUG_HTTPS_IMAGE_MIXED_CONTENT') && app()->runningInConsole() === false) {
            // #region debug-point A:app-url-context
            rescue(function (): void {
                $request = request();

                Http::timeout(1)->post('http://127.0.0.1:7777/event', [
                    'sessionId' => 'https-image-mixed-content',
                    'runId' => 'pre-fix',
                    'hypothesisId' => 'A',
                    'location' => 'app/Providers/AppServiceProvider.php:boot',
                    'msg' => '[DEBUG] App URL context resolved during request bootstrap',
                    'data' => [
                        'app_url_config' => config('app.url'),
                        'url_root' => url('/'),
                        'request_scheme' => $request->getScheme(),
                        'request_is_secure' => $request->isSecure(),
                        'request_host' => $request->getHost(),
                        'server_https' => $request->server('HTTPS'),
                        'server_port' => $request->server('SERVER_PORT'),
                        'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
                        'x_forwarded_host' => $request->headers->get('x-forwarded-host'),
                        'x_forwarded_port' => $request->headers->get('x-forwarded-port'),
                        'forwarded' => $request->headers->get('forwarded'),
                    ],
                    'ts' => (int) round(microtime(true) * 1000),
                ]);
            }, report: false);
            // #endregion
        }

        RateLimiter::for('property-image-uploads', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return [
                Limit::perMinute(600)->by('property-image-uploads:' . $key),
            ];
        });
    }

    private function configureCanonicalUrlGeneration(): void
    {
        $appUrl = trim((string) config('app.url'), '/');
        $appUrlHost = (string) (parse_url($appUrl, PHP_URL_HOST) ?? '');
        $appUrlScheme = strtolower((string) (parse_url($appUrl, PHP_URL_SCHEME) ?? ''));
        $request = app()->runningInConsole() ? null : request();
        $requestHost = trim((string) ($request?->getHost() ?? ''));
        $forwardedProto = strtolower(trim((string) ($request?->headers->get('x-forwarded-proto') ?? '')));
        $requestSuggestsHttps = ($request?->isSecure() ?? false) || str_contains($forwardedProto, 'https');
        $forceHttps = filter_var(
            env('FORCE_HTTPS', !app()->environment(['local', 'testing'])),
            FILTER_VALIDATE_BOOL
        );

        $canonicalHost = $requestHost !== '' ? $requestHost : $appUrlHost;

        if ($canonicalHost !== '') {
            $rootScheme = $forceHttps || $requestSuggestsHttps || $appUrlScheme === 'https'
                ? 'https'
                : ($appUrlScheme !== '' ? $appUrlScheme : 'http');

            URL::forceRootUrl($rootScheme . '://' . $canonicalHost);
        }

        if ($forceHttps || $requestSuggestsHttps || $appUrlScheme === 'https') {
            URL::forceScheme('https');
        }
    }
}
