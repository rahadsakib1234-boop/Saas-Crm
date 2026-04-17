<?php

return [
    'automation' => [
        'title' => 'Automation',
        'automation' => 'Automation',
        'automation-info' => 'Turn leads into customers automatically with simple rules and visible outcomes.',

        'tabs' => [
            'rules' => 'Rules',
            'temperature' => 'Scoring',
            'logs' => 'Logs',
            'outcomes' => 'Outcomes',
        ],

        'index' => [
            'title' => 'Automation Rules',
            'create-btn' => 'Create Rule',
            'create-success' => 'Automation rule created successfully.',
            'update-success' => 'Automation rule updated successfully.',
            'delete-success' => 'Automation rule deleted successfully.',
        ],

        'create' => [
            'title' => 'Create Automation Rule',
            'save-btn' => 'Save Rule',
            'basic-details' => 'Basic Details',
            'name' => 'Rule Name',
            'description' => 'Description',
            'trigger' => 'Trigger',
            'logic' => 'Condition Logic',
            'outcome' => 'Outcome Preview',
            'outcome-info' => 'Show what the customer sees: the lead gets qualified, tagged, routed, and followed up automatically.',
        ],

        'edit' => [
            'title' => 'Edit Automation Rule',
            'save-btn' => 'Update Rule',
        ],

        'temperature' => [
            'title' => 'Lead Scoring',
            'section' => [
                'scoring' => 'Scoring Configuration',
                'scoring-desc' => 'Points-based scoring is easier to tune than keyword-only rules.',
            ],
            'hot' => 'Hot',
            'warm' => 'Warm',
            'cold' => 'Cold',
            'hot-desc' => 'Ready to buy now',
            'warm-desc' => 'Interested, needs follow-up',
            'cold-desc' => 'Needs nurturing',
            'conditions-title' => 'Score Signals',
            'actions-title' => 'What happens next',
            'col' => [
                'keyword' => 'Signal',
                'operator' => 'Match',
                'field' => 'Field',
                'points' => 'Points',
            ],
            'action' => [
                'notify' => 'Notify agent instantly',
                'create_task' => 'Create a priority task',
                'add_tag' => 'Add a tag',
                'schedule_followup' => 'Schedule a follow-up',
                'nurture' => 'Move to nurture flow',
            ],
        ],

        'logs' => [
            'title' => 'Execution Logs',
            'subtitle' => 'See what ran, why it ran, and whether it succeeded.',
        ],

        'outcomes' => [
            'title' => 'Outcome View',
            'cards' => [
                'qualified' => [
                    'title' => 'When a lead is ready now',
                    'body' => 'The CRM marks it HOT, alerts the team, and creates the next step.',
                ],
                'followup' => [
                    'title' => 'When a lead needs attention',
                    'body' => 'The CRM keeps it WARM and schedules a follow-up for later.',
                ],
                'nurture' => [
                    'title' => 'When a lead is not ready',
                    'body' => 'The CRM sends it to a nurture path instead of letting it go cold.',
                ],
            ],
            'story' => [
                'title' => 'What happens to the lead',
            ],
        ],
    ],
];
