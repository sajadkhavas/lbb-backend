<?php

return [
    'roadmap_version' => '2026-07-20-phase-18',
    'internal_gates' => [
        'frontend_integrated' => [
            'status' => 'ready',
            'target_phase' => 17,
            'evidence' => 'sajadkhavas/cooci#12',
            'contract_version' => '2026-07-20-phase-16',
        ],
        'end_to_end_verified' => [
            'status' => 'ready',
            'target_phase' => 18,
            'evidence' => 'coordinated-backend-api-and-browser-acceptance-ci',
            'contract_version' => '2026-07-20-phase-16',
        ],
        'production_deployed' => [
            'status' => 'not-started',
            'target_phase' => 19,
            'topology' => 'single-server-two-virtual-hosts',
        ],
    ],
];
