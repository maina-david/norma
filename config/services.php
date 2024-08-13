<?php

use App\OAuth\SSO\Providers\AngloAmericanProvider;
use App\OAuth\SSO\Providers\CleanChainProvider;
use App\OAuth\SSO\Providers\Passport360Provider;
use App\OAuth\SSO\Providers\RegwatchProvider;
use App\OAuth\SSO\Providers\XGRCProvider;

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-west-1'),
        'libryo_app_security_group' => 'sg-a3b4acda',
        'subnets' => ['subnet-0eb4e956', 'subnet-9c0716ea', 'subnet-c27767a6'],
        'ecs' => [
            'ocr_my_pdf_task_definition_arn' => 'arn:aws:ecs:eu-west-1:392419968532:task-definition/ocrmypdf:1',
        ],
    ],

    'fabric' => [
        'enabled' => env('FABRIC_ENABLED', true),
        'end_point' => env('FABRIC_API_ENDPOINT', 'http://fabric.libryo.rocks:4500'),
        'guzzle_verify_ssl' => env('GUZZLE_VERIFY_SSL', true),
    ],

    'google_recaptcha' => [
        'enabled' => env('SERVICES_GOOGLE_RECAPTCHA_ENABLED', false),
        'key' => env('SERVICES_GOOGLE_RECAPTCHA_KEY'),
        'secret' => env('SERVICES_GOOGLE_RECAPTCHA_SECRET'),
    ],

    'google_cloud' => [
        'project_id' => env('SERVICES_GOOGLE_PROJECT_ID'),
        'key_file' => [
            'type' => env('SERVICES_GOOGLE_KEY_TYPE'),
            'project_id' => env('SERVICES_GOOGLE_KEY_PROJECT_ID'),
            'private_key_id' => env('SERVICES_GOOGLE_KEY_PRIVATE_KEY_ID'),
            'private_key' => str_replace('\\n', "\n", env('SERVICES_GOOGLE_KEY_PRIVATE_KEY') ?? ''),
            'client_email' => env('SERVICES_GOOGLE_KEY_CLIENT_EMAIL'),
            'client_id' => env('SERVICES_GOOGLE_KEY_CLIENT_ID'),
            'auth_uri' => env('SERVICES_GOOGLE_KEY_AUTH_URI'),
            'token_uri' => env('SERVICES_GOOGLE_KEY_TOKEN_URI'),
            'auth_provider_x509_cert_url' => env('SERVICES_GOOGLE_KEY_AUTH_PROVIDER_X509_CERT_URL'),
            'client_x509_cert_url' => env('SERVICES_GOOGLE_KEY_CLIENT_X509_CERT_URL'),
        ],
    ],

    'google_maps' => [
        'key' => env('SERVICES_GOOGLE_MAPS_KEY'),
        'zoom' => [
            'min' => 0,
            'max' => 17,
        ],
        'geohash' => [
            'min' => 1,
            'max' => 12,
        ],
    ],

    'hubspot' => [
        'enabled' => env('HUBSPOT_ENABLED', false),
        'api_key' => env('HUBSPOT_KEY'),
    ],

    'laconic' => [
        'enabled' => env('LACONIC_ENABLED', true),
        'end_point' => env('LACONIC_API_ENDPOINT', 'https://laconic.libryo.rocks'),
        'guzzle_verify_ssl' => env('GUZZLE_VERIFY_SSL', true),
    ],

    'libryo_ai' => [
        'enabled' => env('LIBRYO_AI_ENABLED', false),
        'host' => env('LIBRYO_AI_HOST', 'https://ai.libryo.rocks'),
    ],

    'magi' => [
        'enabled' => env('MAGI_ENABLED', false),
        'endpoint' => env('MAGI_ENDPOINT', 'http://magi.libryo.rocks:7000'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ocrmypdf' => [
        'docker_run_command' => env('OCRMYPDF_DOCKER_COMMAND', 'docker run -i --rm -v ~/dev/document-tools:/var/www/html ocr-my-pdf'),
    ],
    'openai' => [
        'chat_model' => env('OPENAI_CHAT_MODEL', 'gpt-3.5-turbo-1106'),
        'token' => env('OPENAI_TOKEN'),
    ],

    'pdfinfo' => [
        'binary' => env('PDF_INFO_BINARY', '/usr/bin/pdfinfo'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'scrapfly' => [
        'key' => env('SERVICES_SCRAPFLY_API_KEY'),
        'base_url' => env('SERVICES_SCRAPFLY_BASE_URL', 'https://api.scrapfly.io'),
    ],

    'scraping_bee' => [
        'key' => env('SERVICES_SCRAPING_BEE_API_KEY'),
        'base_url' => env('SERVICES_SCRAPING_BEE_BASE_URL', 'https://app.scrapingbee.com/api/v1/'),
    ],

    'sentry' => [
        'enabled' => env('SENTRY_ENABLED', false),
    ],

    'sso' => [
        'angloamerican' => [
            'provider' => AngloAmericanProvider::class,
            'client_id' => env('ANGLO_AMERICAN_CLIENT_ID', '12345'),
            'client_secret' => env('ANGLO_AMERICAN_CLIENT_SECRET', '12345'),
            'partner_id' => env('ANGLO_AMERICAN_PARTNER_ID', 29),
            'tenant_id' => env('ANGLO_AMERICAN_TENANT_ID', '2864f69d-77c3-4fbe-bbc0-97502052391a'),
        ],
        'cleanchain' => [
            'provider' => CleanChainProvider::class,
            'authorize_url' => env('CLEANCHAIN_AUTHORIZE_URL', 'https://www.my-aip.com/OAuth2/Login.aspx'),
            'token_url' => env('CLEANCHAIN_TOKEN_URL', 'https://www.my-aip.com/OAuth2/rest/libryo/onAfterAuth'),
            'user_url' => env('CLEANCHAIN_USER_URL', 'https://www.my-aip.com/OAuth2/rest/libryo/user'),
            'client_id' => env('CLEANCHAIN_CLIENT_ID', 'e2b5d0503ab2d8bab6b5'),
            'client_secret' => env('CLEANCHAIN_CLIENT_SECRET', 'b2df19070fa5374bf8fc7b7f9b33a43e0f6f35ee445f59780ccd12e503fe'),
            // 'redirect' => env('CLEANCHAIN_CALLBACK', 'https://www.my-aip.com/OAuth2/OnAfterLogin.aspx?ClientId=e2b5d0503ab2d8bab6b5&ClientSecret=b2df19070fa5374bf8fc7b7f9b33a43e0f6f35ee445f59780ccd12e503fe'),
            'partner_id' => env('CLEANCHAIN_PARTNER_ID', 27),
        ],
        'passport360' => [
            'provider' => Passport360Provider::class,
            'authorize_url' => env('PASSPORT360_AUTHORIZE_URL', 'https://staging.passport360.com/libryo/login'),
            'token_url' => env('PASSPORT360_TOKEN_URL', 'https://staging.passport360.com/api/libryo/accesstoken'),
            'user_url' => env('PASSPORT360_USER_URL', 'https://staging.passport360.com/api/libryo/userinfo'),
            'client_id' => env('PASSPORT360_CLIENT_ID', ''),
            'client_secret' => env('PASSPORT360_CLIENT_SECRET', ''),
            'partner_id' => env('PASSPORT360_PARTNER_ID', 28),
        ],
        'regwatch' => [
            'provider' => RegwatchProvider::class,
            'authorize_url' => env('RUBICON_AUTHORIZE_URL', 'https://customer.dev.aws.rubicon.com/account/login'),
            'token_url' => env('RUBICON_TOKEN_URL', 'https://customer-api-core.dev.aws.rubiconglobal.com/open-auth/token/public'),
            'user_url' => env('RUBICON_USER_URL', 'https://customer-api-core.dev.aws.rubiconglobal.com/legislation/sites/'),
            'client_id' => env('RUBICON_CLIENT_ID', '1919fe44-8d32-4680-8d3a-20a040bf1648'),
            'client_secret' => env('RUBICON_CLIENT_SECRET', 'UA%>6qqzQ1Db)<r6%$i$`zi+2W3`u2Q\'-g0?&H?G1)gQPCeEmIrw6lGyTLx)y@;'),
            'partner_id' => env('RUBICON_PARTNER_ID', 26),
        ],
        'xgrc' => [
            'provider' => XGRCProvider::class,
            'authorize_url' => env('XGRC_AUTHORIZE_URL', 'https://xgrc-rsa-dev-app01.azurewebsites.net/authorize.aspx'),
            'token_url' => env('XGRC_TOKEN_URL', 'https://xgrc-rsa-dev-app01.azurewebsites.net/oauth/oauth/token'),
            'user_url' => env('XGRC_USER_URL', 'https://xgrc-rsa-dev-app01.azurewebsites.net/oauth/xgrc/GetProfile'),
            'client_id' => env('XGRC_CLIENT_ID', ''),
            'client_secret' => env('XGRC_CLIENT_SECRET', ''),
            'partner_id' => env('XGRC_PARTNER_ID', 10),
        ],
    ],

    'sendgrid' => [
        'webhook_key' => env('MAIL_SENDGRID_WEBHOOK_KEY', ''),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'tika' => [
        'enabled' => env('SERVICES_TIKA_ENABLED', false),
        'host' => env('SERVICES_TIKA_HOST'),
    ],

    'transferwise' => [
        'url' => env('TRANSFERWISE_URL', 'https://api.sandbox.transferwise.tech/'),
        'access_token' => env('TRANSFERWISE_ACCESS_TOKEN', 'f5053be0-12ea-448a-9585-496dcb3e2dcc'),
        // 'client_id' => env('TRANSFERWISE_CLIENT_ID'),
        // 'client_secret' => env('TRANSFERWISE_CLIENT_SECRET'),
        // 'refresh_token' => env('TRANSFERWISE_REFRESH_TOKEN'),
        // 'recipient_name' => 'accountHolderName',
        // 'recipient_account_id' => 'profile',
        // 'rate-type' => 'FIXED',
        // 'payout-type' => 'BALANCE_PAYOUT',
        // 'fund-type' => 'BALANCE',
        // 'profile-id' => env('TRANSFERWISE_PROFILE_ID'),
        // 'token_cache_key' => 'transferwise_api_token',
        // 'source_currency' => 'GBP',
        // 'turk_payment_reference' => 'Libryo Collaborate Payment',
    ],

    'wachete' => [
        'webhook_secret' => env('SERVICES_WACHETE_WEBHOOK_SECRET'),
    ],
];
