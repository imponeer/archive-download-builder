<?php

declare(strict_types=1);

namespace Imponeer\ArchiveDownloadBuilder;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PhpZip\Exception\ZipEntryNotFoundException;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use Psr\Http\Message\ResponseInterface;

class ZipDownloader extends Downloader
{
    private ZipFile $archive;

    public function __construct(
        string $ext = '.zip',
        string $mimetype = 'application/x-zip',
        ?Filesystem $filesystem = null,
    ) {
        parent::__construct($ext, $mimetype, $filesystem);

        $this->archive = new ZipFile();
    }


    /**
     * @throws FilesystemException
     * @throws ZipException
     */
    public function addFile(string $filePath, ?string $newFilename = null): void
    {
        $this->archive->addFromStream(
            $this->filesystem->readStream($filePath),
            $this->resolveFilename($filePath, $newFilename)
        );
    }

    /**
     * @throws FilesystemException
     * @throws ZipException
     */
    public function addBinaryFile(string $filePath, ?string $newFilename = null): void
    {
        $this->addFile($filePath, $newFilename);
    }

    /**
     * @throws ZipEntryNotFoundException
     * @throws ZipException
     */
    public function addFileData(string $data, string $filename, int $time = 0): void
    {
        $this->archive->addFromString($filename, $data);
        if ($time > 0) {
            $this->archive->getEntry($filename)->setTime($time);
        }
    }

    /**
     * @throws ZipEntryNotFoundException
     * @throws ZipException
     */
    public function addBinaryFileData(string $data, string $filename, int $time = 0): void
    {
        $this->addFileData($data, $filename, $time);
    }

    /**
     * @throws ZipException
     */
    public function toResponse(string $name = 'archive'): ResponseInterface
    {
        $responseFactory = new Psr17Factory();

        $body = $responseFactory->createStream(
            $this->archive->outputAsString()
        );

        return $responseFactory->createResponse(200)
            ->withHeader('Content-Type', $this->mimetype)
            ->withHeader('Content-Disposition', sprintf('attachment; filename="%s%s"', $name, $this->ext))
            ->withHeader('Expires', '0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody(
                $body
            );
    }
}
