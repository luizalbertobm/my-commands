<?php

namespace MyCommands\Tests\Helper;

use MyCommands\Helper\ZipHelper;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ZipHelperTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('testDir');
    }

    public function testZipDirectoryCreatesZipFile(): void
    {
        // Create test files in virtual filesystem
        $testContent = 'test content';
        vfsStream::newFile('file1.txt')->withContent($testContent)->at($this->root);
        vfsStream::newFile('subdir/file2.txt')->withContent($testContent)->at($this->root);

        $sourceDir = vfsStream::url('testDir');
        $zipPath = sys_get_temp_dir().'/test.zip';

        // Delete the zip file if it already exists
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $zipHelper = new ZipHelper();
        $zipHelper->zipDirectory($sourceDir, $zipPath);

        // Assert zip file exists
        $this->assertFileExists($zipPath);

        // Verify zip contents
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath));

        $this->assertEquals(2, $zip->numFiles);
        $this->assertTrue(false !== $zip->locateName('file1.txt'));
        $this->assertTrue(false !== $zip->locateName('subdir/file2.txt'));

        $zip->close();

        // Clean up
        unlink($zipPath);
    }

    public function testZipDirectoryThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(\RuntimeException::class);

        $zipHelper = new ZipHelper();
        $nonExistentPath = '/path/that/doesnt/exist';
        $zipPath = sys_get_temp_dir().'/test_invalid.zip';

        $zipHelper->zipDirectory($nonExistentPath, $zipPath);
    }
}
