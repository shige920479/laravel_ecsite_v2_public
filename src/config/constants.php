<?php

return [
    'TAX_RATE' => '0.1',
    'session_key' => [
        'tmp_image', 'tmp_item_image', 'item', 'update_item'
    ],
    'slack_webhook_url' => env('SLACK_WEBHOOK_URL'),
    'pagination' => [
        'per_page' => [8, 12, 16],
    ],
    'item_sort' => [
        'options' => [
            'price_asc' => ['price_ex_tax', 'asc'],
            'price_desc' => ['price_ex_tax', 'desc'],
            'date_desc' => ['created_at', 'desc'],
            'shop_asc' => ['shop_id', 'asc']
        ],
    ],
    'item_search' => [
        'max_length' => 50,
    ],
    'owner_item_sort' => [
        'options' => [
            'sales' => ['sales', 'desc'],
            'rating' => ['avg_star', 'desc'],
            'view' => ['view_counts', 'desc'],
        ],
    ],
    'ranking' => [
        'period' => [
            'weekly' => 7,
            'monthly' => 30,
        ],
        'type' => [
            'views' => [
                'column' => 'view_counts',
                'direction' => 'desc',
                'label' => 'ビューランキング',
            ],
            'sales' => [
                'column' => 'sales',
                'direction' => 'desc',
                'label' => '販売数ランキング',
            ],
            'review' => [
                'column' => 'reviews_count',
                'direction' => 'desc',
                'label' => '高評価ランキング',
            ],
        ]
    ]
];