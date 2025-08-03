<?php

namespace Imponeer\ArchiveDownloadBuilder;

use HttpSoft\Emitter\SapiEmitter;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Http\Message\ResponseInterface;

abstract class Downloader
{
    protected string $ext;
    protected string $mimetype;
    protected Filesystem $filesystem;

    /**
     * Constructor
     */
    public function __construct(
        string $ext,
        string $mimetype,
        ?Filesystem $filesystem = null,
    ) {
        $this->ext = trim($ext);
        $this->mimetype = trim($mimetype);
        $this->filesystem = $this->resolveFilesystem($filesystem);
    }

    private function resolveFilesystem(?Filesystem $filesystem): Filesystem
    {
        return $filesystem ?? new Filesystem(
            new LocalFilesystemAdapter('/')
        );
    }

    protected function resolveFilename(string $filePath, ?string $newFilename = null): string
    {
        if (isset($newFilename)) {
            $newFilename = trim($newFilename);
            if ($newFilename !== '') {
                return trim($newFilename);
            }
        }

        return basename($filePath);
    }

    abstract public function addFile(string $filePath, ?string $newFilename = null): void;

    abstract public function addBinaryFile(string $filePath, ?string $newFilename = null): void;

    abstract public function addFileData(string $data, string $filename, int $time = 0): void;

    abstract public function addBinaryFileData(string $data, string $filename, int $time = 0): void;

    public function download(string $name): void
    {
        $response = $this->toResponse($name);

        $emitter = new SapiEmitter();
        $emitter->emit($response);
    }

    abstract public function toResponse(string $name = 'archive'): ResponseInterface;
}
