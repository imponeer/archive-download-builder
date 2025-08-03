# Archive Download Builder

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE.md)
[![Latest Release](https://img.shields.io/github/v/release/imponeer/archive-download-builder)](https://github.com/imponeer/archive-download-builder/releases)
[![Downloads](https://img.shields.io/packagist/dt/imponeer/archive-download-builder)](https://packagist.org/packages/imponeer/archive-download-builder)

A PHP library for building and downloading archive files (ZIP and TAR.GZ) with support for various file sources through the [Flysystem](https://flysystem.thephpleague.com/) abstraction layer.

This library is a modern rewrite of the [XOOPS](https://xoops.org/) downloader classes ([Downloader](https://github.com/XOOPS/XoopsCore25/blob/master/htdocs/class/downloader.php), [ZipDownloader](https://github.com/XOOPS/XoopsCore25/blob/master/htdocs/class/zipdownloader.php), [TarDownloader](https://github.com/XOOPS/XoopsCore25/blob/master/htdocs/class/tardownloader.php)) with MIT license compatibility and modern PHP coding standards.

## Installation

Install via Composer:

```bash
composer require imponeer/archive-download-builder
```

## Examples

### Simple ZIP Archive (using default filesystem)

```php
<?php

use Imponeer\ArchiveDownloadBuilder\ZipDownloader;

// Create ZIP downloader (uses entire filesystem as base when no filesystem is specified)
$downloader = new ZipDownloader();

// Add files from anywhere on the filesystem
$downloader->addFile('/path/to/document.pdf');
$downloader->addFile('/home/user/image.jpg', 'renamed-image.jpg');

// Add file data directly
$downloader->addFileData('Hello World!', 'hello.txt');

// Generate and download
$downloader->download('my-archive');
```

### ZIP Archive with Custom Filesystem

```php
<?php

use Imponeer\ArchiveDownloadBuilder\ZipDownloader;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

// Create filesystem adapter for a specific directory
$filesystem = new Filesystem(new LocalFilesystemAdapter('/path/to/files'));

// Create ZIP downloader with custom filesystem
$downloader = new ZipDownloader(filesystem: $filesystem);

// Add files to archive (paths are relative to the filesystem adapter)
$downloader->addFile('document.pdf');
$downloader->addFile('image.jpg', 'renamed-image.jpg');

// Add file data directly
$downloader->addFileData('Hello World!', 'hello.txt');

// Generate and download
$downloader->download('my-archive');
```

### Basic TAR.GZ Archive

```php
<?php

use Imponeer\ArchiveDownloadBuilder\TarDownloader;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

// Create filesystem adapter
$filesystem = new Filesystem(new LocalFilesystemAdapter('/path/to/files'));

// Create TAR downloader
$downloader = new TarDownloader(filesystem: $filesystem);

// Add files to archive
$downloader->addFile('config.json');
$downloader->addBinaryFile('data.bin', 'backup-data.bin');

// Generate and download
$downloader->download('backup');
```

### Custom MIME Types and Extensions

```php
// Custom ZIP configuration
$zipDownloader = new ZipDownloader(
    ext: '.custom.zip',
    mimetype: 'application/custom-zip',
    filesystem: $filesystem
);

// Custom TAR configuration
$tarDownloader = new TarDownloader(
    ext: '.backup.tar.gz',
    mimetype: 'application/x-compressed-tar',
    filesystem: $filesystem,
    tmpPath: '/custom/temp/path'
);
```

### Working with Different Filesystems

```php
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

// In-memory filesystem
$memoryFs = new Filesystem(new InMemoryFilesystemAdapter());
$downloader = new ZipDownloader(filesystem: $memoryFs);

// FTP filesystem
$ftpFs = new Filesystem(new FtpAdapter($ftpConfig));
$downloader = new ZipDownloader(filesystem: $ftpFs);

// AWS S3 filesystem
$s3Fs = new Filesystem(new AwsS3V3Adapter($s3Client, $bucket));
$downloader = new ZipDownloader(filesystem: $s3Fs);
```

### PSR-7 Response Integration

```php
// Get PSR-7 response instead of direct download
$response = $downloader->toResponse('archive-name');

// Use with your favorite HTTP framework
// Symfony
return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());

// Laravel
return response($response->getBody(), $response->getStatusCode(), $response->getHeaders());

// Slim Framework
return $response; // Direct usage
```

### Bulk File Operations

```php
use League\Flysystem\FileAttributes;

// Add all files from a directory
foreach ($filesystem->listContents('/documents', true) as $item) {
    if ($item instanceof FileAttributes) {
        $downloader->addFile($item->path());
    }
}

// Add files with custom naming pattern
$files = ['report1.pdf', 'report2.pdf', 'summary.doc'];
foreach ($files as $index => $file) {
    $downloader->addFile($file, sprintf('report_%02d_%s', $index + 1, basename($file)));
}
```

### Error Handling

```php
use PhpZip\Exception\ZipException;
use League\Flysystem\FilesystemException;

try {
    $downloader = new ZipDownloader(filesystem: $filesystem);
    $downloader->addFile('nonexistent-file.txt');
    $downloader->download('archive');
} catch (FilesystemException $e) {
    // Handle filesystem errors (file not found, permission issues, etc.)
    echo "Filesystem error: " . $e->getMessage();
} catch (ZipException $e) {
    // Handle ZIP-specific errors
    echo "ZIP error: " . $e->getMessage();
}
```

## Documentation

API documentation is automatically generated and available in the [project's wiki](https://github.com/imponeer/archive-download-builder/wiki). For more detailed information about the classes and methods, please refer to the project wiki.

## Development

### Running Tests

```bash
composer test
```

### Code Quality

```bash
# PHP CodeSniffer
composer phpcs

# Fix coding standards
composer phpcbf

# Static analysis
composer phpstan
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code follows PSR-12 standards and includes appropriate tests.

## Support

For issues and questions, please use the [GitHub Issues](https://github.com/imponeer/archive-download-builder/issues) page.
