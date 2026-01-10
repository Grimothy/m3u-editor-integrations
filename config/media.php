<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Media Paths
    |--------------------------------------------------------------------------
    |
    | Define the base directories that are allowed for local media file access.
    | Only files within these directories (or their subdirectories) can be
    | streamed through the local media feature.
    |
    | These paths should be absolute paths. They will be resolved to their
    | real paths to prevent symlink attacks.
    |
    | Example:
    | 'allowed_paths' => [
    |     '/media',
    |     '/mnt/media',
    |     '/storage/media',
    | ],
    |
    */

    'allowed_paths' => env('MEDIA_ALLOWED_PATHS') 
        ? explode(',', env('MEDIA_ALLOWED_PATHS'))
        : [
            '/media',
            '/mnt/media',
            '/data/media',
            '/storage/media',
        ],

];
