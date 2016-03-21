<?php

namespace App\Providers;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use function view;

class AppServiceProvider extends ServiceProvider {

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
        $extractVersion = function($versionString) {
            if (!preg_match('/^(\d+?\.\d+\.\d+)/', $versionString, $matches)) {
                return $versionString;
            }
            return $matches[1];
        };
        $appVersions = \Cache::remember(md5('app_versions'), 1440, function() {
                    return [
                        'laravel_version' => Application::VERSION,
                        'php_version' => PHP_VERSION,
                        'mysql_version' => \DB::table('information_schema.GLOBAL_VARIABLES')
                                ->select('VARIABLE_VALUE as val')
                                ->where('VARIABLE_NAME', '=', 'VERSION')
                                ->get()[0]->val,
                        'last_commit_hash' => substr(shell_exec("git rev-parse HEAD"), 0, 7)
                    ];
                });

        foreach ($appVersions as $viewVarName => $rawVersion) {
            $version = $extractVersion($rawVersion);
            view()->share($viewVarName, $version);
        }
        view()->share('last_commit_hash_link', env('APP_GITHUB_REPO') . '/commit/' . $appVersions['last_commit_hash']);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        //
    }

}
