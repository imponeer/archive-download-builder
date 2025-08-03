<?php

namespace Imponeer\ArchiveDownloadBuilder\Tests;

use Imponeer\ArchiveDownloadBuilder\TarDownloader;
use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use PHPUnit\Framework\TestCase;
use PhpZip\Exception\ZipException;

class TarDownloaderTest extends TestCase
{
    private Filesystem $filesystem;

    /**
     * @throws FilesystemException
     */
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $this->filesystem->write('a.txt', 'A');
        $this->filesystem->write('b.txt', 'B');
    }

    /**
     * @throws FilesystemException
     * @throws ZipException
     */
    public function testToResponseHeadersAndBody(): void
    {
        $downloader = new TarDownloader(
            filesystem: $this->filesystem
        );

        foreach ($this->filesystem->listContents('/') as $item) {
            if (!$item->isFile()) {
                continue;
            }

            assert($item instanceof FileAttributes);

            $downloader->addFile($item->path(), $item->path());
        }

        $response = $downloader->toResponse('pack');
        $this->assertEquals(
            'application/x-gzip',
            $response->getHeaderLine('Content-Type')
        );
        $this->assertStringContainsString(
            'attachment; filename="pack.tar.gz"',
            $response->getHeaderLine('Content-Disposition')
        );
        $body = (string) $response->getBody();
        $this->assertNotEmpty($body);
    }
}
