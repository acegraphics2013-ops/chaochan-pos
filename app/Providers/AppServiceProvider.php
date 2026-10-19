<?php
namespace App\Providers;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {}
    public function boot(): void {
        if ($this->app->runningInConsole()) return;
        if (filter_var(request()->getHost(), FILTER_VALIDATE_IP) !== false) URL::forceRootUrl(request()->getSchemeAndHttpHost());
    }
}
