<?php

/**
 * Lead Automation Configuration
 *
 * Central config for lead temperature scoring, action execution,
 * logging, and retention policies.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Scoring Conditions
    |--------------------------------------------------------------------------
    |
    | Each condition is checked and points are added if matched.
    | Conditions are evaluated in order. First matching threshold wins.
    |
    */
    'conditions' => [
        // === HOT signals (+) ===
        ['field' => 'description', 'operator' => 'contains', 'value' => 'urgent',       'points' => 10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'asap',          'points' => 10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'buy now',       'points' => 10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'immediately',   'points' => 10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'emergency',     'points' => 10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'contract',      'points' => 8],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'signing',       'points' => 8],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'need today',    'points' => 10],

        // === WARM signals (+) ===
        ['field' => 'description', 'operator' => 'contains', 'value' => 'interested',    'points' => 6],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'follow up',     'points' => 5],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'call me',       'points' => 5],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'contact me',    'points' => 5],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'send info',     'points' => 4],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'pricing',       'points' => 4],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'later',         'points' => 3],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'maybe',         'points' => 3],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'thinking',      'points' => 3],

        // === COLD signals (-) ===
        ['field' => 'description', 'operator' => 'contains', 'value' => 'not interested', 'points' => -10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'wrong number',  'points' => -10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'dont call',     'points' => -10],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'spam',          'points' => -8],
        ['field' => 'description', 'operator' => 'contains', 'value' => 'remove',        'points' => -8],
    ],

    /*
    |--------------------------------------------------------------------------
    | Score Thresholds → Actions
    |--------------------------------------------------------------------------
    |
    | When total points reach a threshold, execute all configured actions.
    | Thresholds are checked in order (first match wins).
    | Use 'min_score' of -9999 for default (cold) classification.
    |
    | Available actions (must be registered in LeadActionRegistry):
    |   - add_tag          → params: ['tag' => 'hot']
    |   - remove_tag       → params: ['tag' => 'cold']
    |   - notify_user      → params: ['title' => '...', 'body' => '...', 'priority' => 'high']
    |   - create_task      → params: ['title' => '...', 'due_offset_hours' => 24]
    |   - move_to_stage    → params: ['stage_id' => 1] or ['stage_code' => 'warm']
    |   - update_field     → params: ['field' => 'status', 'value' => 'nurture']
    |   - webhook          → params: ['url' => 'https://...', 'method' => 'POST', 'payload' => {...}]
    |
    */
    'thresholds' => [
        [
            'min_score' => 15,
            'actions' => [
                ['action' => 'add_tag',   'params' => ['tag' => 'hot']],
                ['action' => 'notify_user', 'params' => [
                    'title' => '🔥 Hot Lead Alert',
                    'body' => 'Lead {lead_title} requires immediate attention',
                    'priority' => 'high',
                ]],
                ['action' => 'create_task', 'params' => [
                    'title' => 'Follow up with hot lead: {lead_title}',
                    'due_offset_hours' => 1,
                ]],
            ],
        ],
        [
            'min_score' => 5,
            'actions' => [
                ['action' => 'add_tag', 'params' => ['tag' => 'warm']],
                ['action' => 'create_task', 'params' => [
                    'title' => 'Schedule follow-up: {lead_title}',
                    'due_offset_hours' => 24,
                ]],
            ],
        ],
        [
            'min_score' => -9999, // default = cold
            'actions' => [
                ['action' => 'add_tag',   'params' => ['tag' => 'cold']],
                ['action' => 'move_to_stage', 'params' => ['stage_code' => 'cold']],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fields to Analyze
    |--------------------------------------------------------------------------
    */
    'analyze_fields' => ['description', 'title'],

    /*
    |--------------------------------------------------------------------------
    | Replace Existing Temperature Tags on Update
    |--------------------------------------------------------------------------
    */
    'replace_on_update' => true,

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    */
    'logging' => [
        // Log every automation execution
        'enabled' => true,

        // Log level: debug, info, warning, error
        'level' => 'info',

        // Log failed actions only (reduces volume)
        'failed_only' => false,

        // Include matched conditions in log context
        'include_conditions' => true,

        // Include score calculation details
        'include_score_details' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Retention Policy
    |--------------------------------------------------------------------------
    |
    | Prevents log explosion in production.
    |
    */
    'retention' => [
        // How long to keep automation logs (days)
        'keep_days' => 30,

        // Keep failed logs longer for debugging
        'keep_failed_days' => 90,

        // Maximum logs per lead (oldest will be pruned)
        'max_per_lead' => 100,

        // Run cleanup job daily
        'cleanup_schedule' => 'daily',
    ],
];
