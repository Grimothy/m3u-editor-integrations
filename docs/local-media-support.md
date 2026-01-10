# Local Media Support for Custom Channels

## Overview

m3u-editor now supports creating custom live channels using local media files stored on the server, similar to functionality provided by ErsatzTV. This feature allows you to use your own media files as streaming sources instead of relying solely on external URLs.

## Feature Highlights

- **Local File Support**: Use local media files stored on the m3u-editor server as channel sources
- **Custom Channels Only**: This feature is available exclusively for custom channels
- **Secure Streaming**: Built-in path traversal protection and security checks
- **Efficient Delivery**: Files are streamed in chunks for optimal performance with large media files

## Requirements

- Local media files must be accessible from the m3u-editor server
- Files must have read permissions for the server process
- Only available for custom channels (channels with `is_custom = true`)

## How to Use

### Creating a Channel with Local Media

1. **Navigate to Custom Playlists**
   - Go to your Custom Playlist in the m3u-editor interface
   - Create a new channel or edit an existing custom channel

2. **Select Media Type**
   - In the channel form, find the "Media Type" selector
   - Choose "Local File" instead of "URL"

3. **Enter File Path**
   - Provide the absolute path to your media file
   - Example: `/media/movies/example-movie.mp4`
   - The path must be accessible from the m3u-editor server

4. **Save the Channel**
   - Complete the rest of the channel configuration (name, group, etc.)
   - Save the channel

### Supported File Paths

- **Absolute Paths**: Use full paths starting from the root
  - Example: `/media/videos/channel1.mp4`
  - Example: `/home/user/videos/stream.mkv`

- **Mount Points**: You can use Docker volume mounts
  - Map your media directory to a path inside the container
  - Example Docker compose:
    ```yaml
    volumes:
      - /path/to/your/media:/media
    ```
  - Then use: `/media/your-file.mp4`

### Supported Media Formats

The local media feature supports any video format that:
- Can be streamed by ffmpeg (when using transcoding profiles)
- Is directly playable by the client (when not using transcoding)

Common formats include:
- MP4 (H.264/H.265)
- MKV
- AVI
- TS (Transport Stream)
- WebM

## Security Considerations

### Path Security

The system implements several security measures:

1. **Path Traversal Protection**
   - Uses `realpath()` to resolve the actual file path
   - Prevents `../` attacks and symlink exploits

2. **Allowed Directory Whitelist**
   - Only files within configured allowed directories can be accessed
   - Default allowed directories: `/media`, `/mnt/media`, `/data/media`, `/storage/media`
   - Configure custom paths via `MEDIA_ALLOWED_PATHS` environment variable
   - Example: `MEDIA_ALLOWED_PATHS="/media,/mnt/videos,/storage/media"`

3. **File Access Validation**
   - Checks if the file exists before streaming
   - Verifies read permissions
   - Returns appropriate error codes for unauthorized access

4. **Best Practices**
   - Store media in dedicated directories
   - Use Docker volumes to control accessible paths
   - Set appropriate file system permissions
   - Consider using read-only mounts for security

### Configuring Allowed Paths

You can configure allowed media paths in two ways:

1. **Environment Variable** (recommended for Docker):
   ```env
   MEDIA_ALLOWED_PATHS="/media,/mnt/videos,/custom/path"
   ```

2. **Config File** (`config/media.php`):
   ```php
   'allowed_paths' => [
       '/media',
       '/mnt/media',
       '/your/custom/path',
   ],
   ```

### Recommended Security Setup

```yaml
# Docker compose example with read-only media mount
services:
  m3u-editor:
    environment:
      - MEDIA_ALLOWED_PATHS=/media,/recordings
    volumes:
      - /path/to/media:/media:ro  # Read-only mount
      - /path/to/recordings:/recordings:ro
```

## M3U Playlist Generation

When you generate an M3U playlist that includes channels with local media:

1. **File Protocol**: The M3U will contain `file://` URLs for local files
2. **Streaming**: When accessed through the Xtream API or proxy, files are streamed directly
3. **Compatibility**: Most IPTV players support file:// protocol or will stream via the proxy

Example M3U entry:
```
#EXTINF:-1 tvg-id="1" tvg-name="My Local Channel" tvg-logo="http://example.com/logo.png" group-title="Local Media",My Local Channel
file:///media/videos/channel1.mp4
```

## Streaming Behavior

### With Proxy Enabled
- Local files are streamed directly through the m3u-editor proxy
- Supports chunked transfer for large files
- Compatible with transcoding profiles

### Without Proxy Enabled
- The M3U contains direct `file://` URLs
- Client must have access to the file system path
- Typically used in local network scenarios

## Troubleshooting

### File Not Found Errors

**Symptom**: 404 error when playing the channel

**Solutions**:
1. Verify the file path is correct and absolute
2. Check file permissions (must be readable by the server process)
3. Ensure the file exists at the specified location
4. For Docker deployments, verify volume mounts are configured correctly

### Access Denied Errors

**Symptom**: 403 error when playing the channel

**Solutions**:
1. Check file permissions: `ls -la /path/to/file`
2. Ensure the m3u-editor process has read access
3. For Docker, ensure the user inside the container can access the mount

### Playback Issues

**Symptom**: Channel loads but doesn't play

**Solutions**:
1. Verify the media file is not corrupted
2. Check if the file format is supported by your player
3. Try using a transcoding profile for better compatibility
4. Test the file locally with a media player (VLC, mpv, etc.)

## Example Use Cases

### 1. Local Movie Library
Create channels for your movie collection:
```
Channel: "Action Movies"
Path: /media/movies/action/die-hard.mp4

Channel: "Comedy"
Path: /media/movies/comedy/superbad.mp4
```

### 2. Recorded Content
Stream previously recorded TV shows:
```
Channel: "Recorded News"
Path: /media/recordings/news-2024-01-10.ts

Channel: "Sports Archive"
Path: /media/recordings/game-final.mkv
```

### 3. Mixed Content Playlist
Combine local media with streaming URLs in a custom playlist:
- Some channels pointing to IPTV URLs
- Other channels using local media files
- All managed in one unified playlist

## Limitations

1. **Custom Channels Only**: Standard playlist channels cannot use local files
2. **Server Access Required**: Files must be on the server's filesystem
3. **No Live Streaming**: Local files are static content (not live TV feeds)
4. **Path Requirements**: Must use absolute paths

## Future Enhancements

Potential future improvements could include:
- Directory scanning for automatic channel creation
- Playlist support for sequential playback
- Schedule-based content rotation
- Network share support (SMB/NFS)

## Related Features

- **Custom Playlists**: Container for organizing custom channels
- **Transcoding Profiles**: Convert local files to compatible formats
- **Failover Channels**: Use local files as backup sources

## Questions?

For issues or questions about local media support:
1. Check the [GitHub Issues](https://github.com/sparkison/m3u-editor/issues)
2. Join the [Discord Community](https://discord.gg/rS3abJ5dz7)
3. Review the main [Documentation](https://sparkison.github.io/m3u-editor-docs/)
