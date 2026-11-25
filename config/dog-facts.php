<?php

return [
    'cache_minutes' => env('DOG_FACTS_CACHE_MINUTES', 5),
    'index_timeout_ms' => env('DOG_FACTS_INDEX_TIMEOUT_MS', 2000),
    'default_message' => 'Data is still being processed, please try again shortly',
];
