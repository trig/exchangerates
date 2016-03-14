<?php

namespace App\Providers;

use App\Contracts\HttpRequestProvider;
use Illuminate\Foundation\Application;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use function env;

class WebRequestorServiceProvider extends ServiceProvider implements HttpRequestProvider {

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton(self::class, function($app) {
            return new WebRequestorServiceProvider($app);
        });
    }

    /**
     * @inheritdoc
     */
    public function request($method = 'GET', $url = '', $data = null, $headers = []) {
        $opts = [
            'http' => [
                'method' => $method,
                'user_agent' => sprintf('%s on Laravel(%s)', env('APP_NAME', 'undefined'), Application::VERSION),
                'ignore_errors' => true
            ]
        ];

        $preparedHeaders = [];
        foreach ($headers as $k => $v) {
            $k = trim($k);
            $v = trim($v);
            $preparedHeaders[] = "{$k}: {$v}";
        }

        if (!empty($preparedHeaders)) {
            $opts['http']['header'] = implode("\r\n", $preparedHeaders);
        }

        $rawResponse = file_get_contents($url, false, stream_context_create($opts));

        $responseHeaders = $http_response_header;

        $status = 200;
        $normalizedHeaders = [];
        
        /** detecting status code */
        if ($responseHeaders) {
            preg_match('~HTTP/\d\.\d (\d+)~', $responseHeaders[0], $statusMatch);

            if (!empty($statusMatch)) {
                $status = $statusMatch[1];
            }
        
        }
        
        /* normalize headers for Response */
        $responseHeaders = array_slice($responseHeaders, 1);
        foreach($responseHeaders as $hdrString){
            $hdrStringSplitted = explode(':', $hdrString);
            $normalizedHeaders[$hdrStringSplitted[0]] = trim(implode(':', array_slice($hdrStringSplitted, 1)));
        }

        return new Response($rawResponse, $status, $normalizedHeaders);
    }

}
