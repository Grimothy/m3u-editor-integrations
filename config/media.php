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
    | real paths to prevent symlink attacks. Paths that don't exist yet
    | (e.g., Docker volume mounts) are allowed and will be validated when
    | they become available.
    |
    | If configured via environment variable, these defaults are NOT used.
    | The environment variable completely replaces the defaults.
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
        ? array_map('trim', explode(',', env('MEDIA_ALLOWED_PATHS')))
        : [
            '/media',
            '/mnt/media',
            '/data/media',
            '/storage/media',
        ],

];
