<?php

return [
    'cash_drawer' => [
        // Variance beyond this amount requires manager/admin approval after closing.
        'variance_threshold' => (float) env('POS_CASH_VARIANCE_THRESHOLD', 500),
    ],
];
