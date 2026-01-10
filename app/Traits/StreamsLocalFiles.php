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

        // Verify we can open the file before starting the response
        $testStream = @fopen($realPath, 'rb');
        if ($testStream === false) {
            Log::warning('Unable to open file for streaming', ['path' => $realPath]);
            return response()->json(['error' => 'Unable to open file for streaming'], 500);
        }
        fclose($testStream);

        // Create a streamed response with range support
        return response()->stream(
            function () use ($realPath) {
                $stream = fopen($realPath, 'rb');
                if ($stream === false) {
                    return;
                }

                // Stream in larger chunks for better performance (64KB)
                while (!feof($stream)) {
                    echo fread($stream, 65536);
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
}
