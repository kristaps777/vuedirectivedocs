<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Vue Directives Source Path
    |--------------------------------------------------------------------------
    |
    | The directory where your custom Vue directive files are located.
    |
    */
    'source' => resource_path('js/src/Directives'),

    /*
    |--------------------------------------------------------------------------
    | Files to Exclude
    |--------------------------------------------------------------------------
    |
    | Files that should be excluded from processing (e.g., index files,
    | interface files, etc.)
    |
    */
    'excludes' => [
        'Directives.ts',
    ],
];