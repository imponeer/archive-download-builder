<?php

declare(strict_types=1);

namespace Imponeer\ArchiveDownloadBuilder;

use Archive_Tar;
use League\Flysystem\Filesystem;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;

class TarDownloader extends Downloader
{
    private string $tmpFile;
    private Archive_Tar $archive;

    public function __construct(
        string $ext = '.tar.gz',
        string $mimetype = 'application/x-gzip',
        ?Filesystem $filesystem = null,
        ?string $tmpPath = null,
    ) {
        parent::__construct($ext, $mimetype, $filesystem);

        $this->tmpFile = ($tmpPath ?: sys_get_temp_dir()) . DIRECTORY_SEPARATOR . uniqid('tar_', true) . '.tar.gz';
        $this->archive = new Archive_Tar($this->tmpFile, 'gz');
    }

    public function addFile(string $filePath, ?string $newFilename = null): void
    {
        $this->archive->addString(
            $this->resolveFilename($filePath, $newFilename),
            $this->filesystem->read($filePath),
            $this->filesystem->lastModified($filePath),
        );
    }

    public function addBinaryFile(string $filePath, ?string $newFilename = null): void
    {
        $this->addFile($filePath, $newFilename);
    }

    public function addFileData(string $data, string $filename, int $time = 0): void
    {
        $this->archive->addString($filename, $data, $time);
    }

    public function addBinaryFileData(string $data, string $filename, int $time = 0): void
    {
        $this->addFileData($data, $filename, $time);
    }

    public function toResponse(string $name = 'archive'): ResponseInterface
    {
        $responseFactory = new Psr17Factory();
        $streamFactory = new Psr17Factory();

        $stream = $streamFactory->createStreamFromFile($this->tmpFile, 'rb');

        return $responseFactory->createResponse(200)
            ->withHeader('Content-Type', $this->mimetype)
            ->withHeader('Content-Disposition', sprintf('attachment; filename="%s%s"', $name, $this->ext))
            ->withHeader('Expires', '0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody($stream);
    }
}
