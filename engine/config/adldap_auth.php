<?php

return [

    'connection' => env('ADLDAP_CONNECTION', 'default'),
    'provider' => Adldap\Laravel\Auth\DatabaseUserProvider::class,

    'rules' => [
        Adldap\Laravel\Validation\Rules\DenyTrashed::class,
        // Adldap\Laravel\Validation\Rules\OnlyImported::class,
    ],
    'scopes' => [
        //Adldap\Laravel\Scopes\UpnScope::class,
        Adldap\Laravel\Scopes\UidScope::class,
    ],

    'usernames' => [
        'ldap' => [
            'discover' => 'cn',
            'authenticate' => 'mail',
        ],
        'eloquent' => 'email',
        'windows' => [
            'discover' => 'samaccountname',
            'key' => 'AUTH_USER',
        ],

    ],

    'passwords' => [
        'sync' => env('ADLDAP_PASSWORD_SYNC', true),
        'column' => 'password',

    ],
    'login_fallback' => env('ADLDAP_LOGIN_FALLBACK', true),
    'sync_attributes' => [
        'email' => 'mail',//'userprincipalname',
        'name' => 'cn',
    ],
    'logging' => [
        'enabled' => true,
        'events' => [
            \Adldap\Laravel\Events\Importing::class => \Adldap\Laravel\Listeners\LogImport::class,
            \Adldap\Laravel\Events\Synchronized::class => \Adldap\Laravel\Listeners\LogSynchronized::class,
            \Adldap\Laravel\Events\Synchronizing::class => \Adldap\Laravel\Listeners\LogSynchronizing::class,
            \Adldap\Laravel\Events\Authenticated::class => \Adldap\Laravel\Listeners\LogAuthenticated::class,
            \Adldap\Laravel\Events\Authenticating::class => \Adldap\Laravel\Listeners\LogAuthentication::class,
            \Adldap\Laravel\Events\AuthenticationFailed::class => \Adldap\Laravel\Listeners\LogAuthenticationFailure::class,
            \Adldap\Laravel\Events\AuthenticationRejected::class => \Adldap\Laravel\Listeners\LogAuthenticationRejection::class,
            \Adldap\Laravel\Events\AuthenticationSuccessful::class => \Adldap\Laravel\Listeners\LogAuthenticationSuccess::class,
            \Adldap\Laravel\Events\DiscoveredWithCredentials::class => \Adldap\Laravel\Listeners\LogDiscovery::class,
            \Adldap\Laravel\Events\AuthenticatedWithWindows::class => \Adldap\Laravel\Listeners\LogWindowsAuth::class,
            \Adldap\Laravel\Events\AuthenticatedModelTrashed::class => \Adldap\Laravel\Listeners\LogTrashedModel::class,

        ],
    ],

];
