<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait StreamsLocalFiles
{
    /**
     * Stream a local media file with support for range requests
     *
     * @param  string  $filePath  The absolute path to the local file
     * @return StreamedResponse|\Illuminate\Http\Response
     */
    protected function streamLocalFile(string $filePath)
    {
        // Security: Validate the file path to prevent directory traversal
        $realPath = realpath($filePath);
        if ($realPath === false || !file_exists($realPath)) {
            Log::warning('Local file not found or inaccessible', ['path' => $filePath]);
            return response()->json(['error' => 'File not found'], 404);
        }

        // Additional security: Restrict access to allowed base directories
        $allowedPaths = $this->getAllowedMediaPaths();
        $isAllowed = false;
        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($realPath, $allowedPath)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            Log::warning('Access denied: File path not in allowed directories', [
                'path' => $realPath,
                'allowed_paths' => $allowedPaths,
            ]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Additional security: Ensure the file is readable
        if (!is_readable($realPath)) {
            Log::warning('Local file is not readable', ['path' => $realPath]);
            return response()->json(['error' => 'File not accessible'], 403);
        }

        // Validate filesize
        $fileSize = filesize($realPath);
        if ($fileSize === false) {
            Log::warning('Unable to determine file size', ['path' => $realPath]);
            return response()->json(['error' => 'Unable to determine file size'], 500);
        }

        $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';

        // Create a streamed response with range support
        return response()->stream(
            function () use ($realPath) {
                $stream = @fopen($realPath, 'rb');
                if ($stream === false) {
                    Log::error('Failed to open file for streaming', ['path' => $realPath]);
                    // Output error message in response body
                    echo json_encode(['error' => 'Failed to open file for streaming']);
                    return;
                }

                // Stream in larger chunks for better performance (64KB)
                while (!feof($stream)) {
                    $chunk = fread($stream, 65536);
                    if ($chunk === false) {
                        Log::error('Failed to read from file stream', ['path' => $realPath]);
                        break;
                    }
                    echo $chunk;
                    flush();
                }

                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Length' => $fileSize,
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]
        );
    }

    /**
     * Get allowed media paths for local file access
     * Override this method in controllers if custom paths are needed
     *
     * @return array
     */
    protected function getAllowedMediaPaths(): array
    {
        // Default allowed paths - can be configured via environment
        $configuredPaths = config('media.allowed_paths', []);
        
        // Add common media directories
        $defaultPaths = [
            '/media',
            '/mnt/media',
            '/data/media',
            '/storage/media',
        ];

        $allPaths = array_merge($configuredPaths, $defaultPaths);

        // Resolve all paths to their real paths and filter out invalid ones
        return array_filter(array_map(function($path) {
            $realPath = realpath($path);
            return $realPath !== false ? $realPath : null;
        }, $allPaths));
    }
}
