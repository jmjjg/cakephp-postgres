<?php
/**
 * Source code for the Postgres.MaintenanceShell unit test class.
 *
 */
namespace Postgres\Test\TestCase\Shell;

use Cake\Console\ConsoleIo;
use Cake\TestSuite\Stub\ConsoleOutput;
use Cake\TestSuite\TestCase;

/**
 * The class Postgres.MaintenanceShellTest is responsible for testing the
 * Postgres.MaintenanceShell class.
 */
class MaintenanceShellTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Postgres.Comments',
    ];

    /**
     * Préparation antérieure au test
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->out = new ConsoleOutput();
        $io = new ConsoleIo($this->out);

        $this->Shell = $this->getMock(
            'Postgres\Shell\MaintenanceShell',
            ['in', 'err', '_stop' ],
            [$io ]
        );

        $this->Shell->params['connection'] = 'test';
    }

    /**
     * Nettoyage postérieur au test.
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        unset($this->Shell);
    }

    /**
     * Test the Postgres.MaintenanceShell::main() method.
     *
     * @return void
     */
    public function testMain()
    {
        $this->Shell->startup();
        $this->Shell->main();

        $output = implode("\n", $this->out->messages());

        $expected = "/sequences/";
        $this->assertRegExp($expected, $output);

        $expected = "/REINDEX DATABASE \".*\";/";
        $this->assertRegExp($expected, $output);

        $expected = "/VACUUM ANALYZE;/";
        $this->assertRegExp($expected, $output);

        $expected = "/CLUSTER;/";
        $this->assertRegExp($expected, $output);
    }

    /**
     * Test the Postgres.MaintenanceShell::sequences() method.
     *
     * @return void
     */
    public function testSequences()
    {
        $this->Shell->command = 'sequences';
        $this->Shell->startup();
        $this->Shell->sequences();

        $output = implode("\n", $this->out->messages());

        $expected = "/sequences/";
        $this->assertRegExp($expected, $output);
    }

    /**
     * Test the Postgres.MaintenanceShell::reindex() method.
     *
     * @return void
     */
    public function testReindex()
    {
        $this->Shell->command = 'reindex';
        $this->Shell->startup();
        $this->Shell->reindex();

        $output = implode("\n", $this->out->messages());

        $expected = "/REINDEX DATABASE \".*\";/";
        $this->assertRegExp($expected, $output);
    }

    /**
     * Test the Postgres.MaintenanceShell::vacuum() method.
     *
     * @return void
     */
    public function testVacuum()
    {
        $this->Shell->command = 'vacuum';
        $this->Shell->startup();
        $this->Shell->vacuum();

        $output = implode("\n", $this->out->messages());

        $expected = "/VACUUM ANALYZE;/";
        $this->assertRegExp($expected, $output);
    }

    /**
     * Test the Postgres.MaintenanceShell::cluster() method.
     *
     * @return void
     */
    public function testCluster()
    {
        $this->Shell->command = 'cluster';
        $this->Shell->startup();
        $this->Shell->cluster();

        $output = implode("\n", $this->out->messages());

        $expected = "/CLUSTER;/";
        $this->assertRegExp($expected, $output);
    }

    /**
     * Test the Postgres.MaintenanceShell --help
     *
     * @return void
     */
    public function testHelp()
    {
        $this->Shell->startup();
        $this->Shell->runCommand([], false, ['help' => true]);

        $output = implode("\n", $this->out->messages());

        $expected = "/\-\-connection, \-c/";
        $this->assertRegExp($expected, $output);

        $expected = "/\(default: default\)/";
        $this->assertRegExp($expected, $output);
    }
}
