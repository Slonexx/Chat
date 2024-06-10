<?php

return [


    'default' => env('QUEUE_CONNECTION', 'sync'),


    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
            'timeout' => 600, // время в секундах
        ],

        'webhook_agent' => [
            'driver' => 'database',
            'table' => 'jobs_webhook_agent',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
            'timeout' => 600, // время в секундах
        ],

        'webhook_agent_intgr' => [
            'driver' => 'database',
            'table' => 'jobs_webhook_agent_intgr',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
            'timeout' => 600, // время в секундах
        ],

        'customerorder' => [
            'driver' => 'database',
            'table' => 'jobs_customerorder',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
            'timeout' => 600, // время в секундах
        ],

        'customerorder_intgr' => [
            'driver' => 'database',
            'table' => 'jobs_customerorder_intgr',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
            'timeout' => 600,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

    ],


    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
