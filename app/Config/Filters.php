<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseConfig {

    public function __construct() {
        //enable csrf and set csrf exclude uris
        /*if (config('App')->csrf_protection) {
            $this->globals['before'] = array(
                'csrf' => array(
                    "except" => config('Rise')->app_csrf_exclude_uris
                )
            );
        }*/

        // Mantén CORS y añade CSRF con exclusiones
        if (config('App')->csrf_protection) {
            $except = array_merge(
                config('Rise')->app_csrf_exclude_uris ?? [],
                [
                    // ⇩⇩ añade aquí las rutas que estás consultando por AJAX sin token
                    'calls_system/get_user_status',
                    'calls_system/check_incoming_calls',
                    'calls_system/check_call_status',
                    'api/save-fcm-token',
                ]
            );

            // No sobrescribas; mezcla con lo que ya hay (cors)
            $this->globals['before'] = array_merge($this->globals['before'], [
                'csrf' => ['except' => $except],
            ]);
        }
    }


    public $csrf_exceptions  = [
        'api/certificate/validateByPrefix',
    ];

    

    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     */
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => \App\Filters\Cors::class,
        'sessionrenewal' => \App\Filters\SessionRenewal::class,
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     */
    public array $globals = [
        'before' => [
            'cors',
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don’t expect could bypass the filter.
     */
    public array $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     */
    public array $filters = [];

    
}
