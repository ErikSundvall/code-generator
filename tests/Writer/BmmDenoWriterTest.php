<?php

namespace Tests\Writer;

use OpenEHR\Tools\CodeGen\Writer\BmmDenoWriter;
use PHPUnit\Framework\TestCase;
use OpenEHR\Tools\CodeGen\Reader\BmmJsonReader;

class BmmDenoWriterTest extends TestCase
{
    private BmmDenoWriter $writer;

    protected function setUp(): void
    {
        $this->writer = new BmmDenoWriter();
        if (!is_dir(BmmDenoWriter::DIR)) {
            mkdir(BmmDenoWriter::DIR, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        if (is_dir(BmmDenoWriter::DIR)) {
            $this->deleteDirectory(BmmDenoWriter::DIR);
        }
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(BmmDenoWriter::class, $this->writer);
    }

    public function testWriteGeneratesCorrectFiles(): void
    {
        $reader = new BmmJsonReader();
        $reader->read('test_schema');

        $this->writer->setReader($reader);
        $this->writer->write();

        $tsFile = BmmDenoWriter::DIR . 'TestPackage/TestClass.ts';
        $jsFile = BmmDenoWriter::DIR . 'TestPackage/TestClass.js';

        $this->assertFileExists($tsFile);
        $this->assertFileExists($jsFile);

        $tsContent = file_get_contents($tsFile);
        $jsContent = file_get_contents($jsFile);

        $this->assertStringContainsString('class TESTCLASS', $tsContent);
        $this->assertStringContainsString('testProperty: string;', $tsContent);
        $this->assertStringContainsString('test_function(): string', $tsContent);

        $this->assertStringContainsString('class TESTCLASS', $jsContent);
        $this->assertStringContainsString('@type {string}', $jsContent);
        $this->assertStringContainsString('testProperty;', $jsContent);
        $this->assertStringContainsString('test_function()', $jsContent);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }
}
