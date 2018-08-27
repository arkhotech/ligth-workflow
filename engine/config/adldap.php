<?php

return [
    'connections' => [
        'default' => [
            'auto_connect' => env('ADLDAP_AUTO_CONNECT', true),
            'connection' => Adldap\Connections\Ldap::class,
            'schema' =>  Adldap\Schemas\OpenLDAP::class, //Adldap\Schemas\ActiveDirectory::class,
            'connection_settings' => [
                'account_prefix' => env('ADLDAP_ACCOUNT_PREFIX', 'cn='),
                'account_suffix' => env('ADLDAP_ACCOUNT_SUFFIX', ',ou=People,dc=arkhotech,dc=com'),
                'domain_controllers' => explode(' ', env('ADLDAP_CONTROLLERS', 'ldap')),
                'port' => env('ADLDAP_PORT', 389),
                'timeout' => env('ADLDAP_TIMEOUT', 5),
                'base_dn' => env('ADLDAP_BASEDN', 'dc=arkhotech,dc=com'),
                'admin_account_prefix' => env('ADLDAP_ADMIN_ACCOUNT_PREFIX', ''),
                'admin_account_suffix' => env('ADLDAP_ADMIN_ACCOUNT_SUFFIX', ''),
                'admin_username' => env('ADLDAP_ADMIN_USERNAME', 'username'),
                'admin_password' => env('ADLDAP_ADMIN_PASSWORD', 'password'),
                'follow_referrals' => false,
                'use_ssl' => env('ADLDAP_USE_SSL', false),
                'use_tls' => env('ADLDAP_USE_TLS', false),

            ],

        ],

    ],

];
