<?php

namespace Cerberus;

use \Symfony\Component\Console\Output\StreamOutput;
use \Symfony\Component\Console\Formatter\OutputFormatter;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    protected $stream;

    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'a', false);
    }

    protected function tearDown()
    {
        $this->stream = null;
    }

    public function testEscapedFormattedOutput()
    {
        $output = new StreamOutput($this->stream, StreamOutput::VERBOSITY_NORMAL, true, null);
        $output->writeln('<info>' . OutputFormatter::escape('<error>some error</error>') . '</info>');
        rewind($output->getStream());
        $this->assertEquals(
            "\033[32m<error>some error</error>\033[39m" . PHP_EOL,
            stream_get_contents($output->getStream())
        );
    }

    public function testEscape()
    {
        $console = new Console;
        $this->assertEquals('\<error>some error\</error>', $console->escape('<error>some error</error>'));
    }
}