<?php
/**
 * Source code for the Postgres.Postgres unit test class.
 *
 */
namespace Postgres\Test\TestCase\Database\Driver;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * The class Postgres.PostgresTest is responsible for testing the
 * Postgres.Postgres database driver class.
 */
class PostgresTest extends TestCase
{
    /**
     * List of plugin functions.
     *
     * @var array
     */
    protected $functions = [
        [
            'schema' => 'public',
            'name' => 'cakephp_validate__ipv4',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate__ipv6',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_alpha_numeric',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_between',
            'result' => 'boolean',
            'arguments' => 'text, integer, integer'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_blank',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text, boolean'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text, boolean, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text[]'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text[], boolean'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_cc',
            'result' => 'boolean',
            'arguments' => 'text, text[], boolean, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_comparison',
            'result' => 'boolean',
            'arguments' => 'double precision, text, double precision'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_decimal',
            'result' => 'boolean',
            'arguments' => 'double precision'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_decimal',
            'result' => 'boolean',
            'arguments' => 'double precision, integer'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_decimal',
            'result' => 'boolean',
            'arguments' => 'double precision, integer, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_email',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_email',
            'result' => 'boolean',
            'arguments' => 'text, boolean'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_email',
            'result' => 'boolean',
            'arguments' => 'text, boolean, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_in_list',
            'result' => 'boolean',
            'arguments' => 'integer, integer[]'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_in_list',
            'result' => 'boolean',
            'arguments' => 'text, text[]'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_ip',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_ip',
            'result' => 'boolean',
            'arguments' => 'text, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_luhn',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_luhn',
            'result' => 'boolean',
            'arguments' => 'text, boolean'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_max_length',
            'result' => 'boolean',
            'arguments' => 'text, integer'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_min_length',
            'result' => 'boolean',
            'arguments' => 'text, integer'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_not_empty',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_phone',
            'result' => 'boolean',
            'arguments' => 'text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_phone',
            'result' => 'boolean',
            'arguments' => 'text, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_phone',
            'result' => 'boolean',
            'arguments' => 'text, text, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_range',
            'result' => 'boolean',
            'arguments' => 'double precision, double precision, double precision'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_ssn',
            'result' => 'boolean',
            'arguments' => 'text, text, text'
        ],
        [
            'schema' => 'public',
            'name' => 'cakephp_validate_uuid',
            'result' => 'boolean',
            'arguments' => 'text'
        ]
    ];

    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Postgres.Comments',
    ];

    /**
     * setUp() method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Comments = TableRegistry::get('Comments');
        $this->connection = $this->Comments->connection();
    }

    /**
     * Test de la méthode Postgres.Postgres::version()
     */
    public function testVersion()
    {
        $result = $this->connection->driver()->version();
        $this->assertRegExp('/^[0-9]+\.[0-9]+/', $result);

        $result = $this->connection->driver()->version(true);
        $this->assertRegExp('/^PostgreSQL [0-9]+\.[0-9]+/', $result);
    }

    /**
     * Test de la méthode Postgres.Postgres::checkSqlSyntax()
     */
    public function testCheckSqlSyntax()
    {
        // 1. Succès
        $sql = "SELECT NOW() + interval '4 DAY 1 MONTH'";
        $result = $this->connection->driver()->checkSqlSyntax($sql);
        $expected = [
            'success' => true,
            'message' => null,
            'value' => 'SELECT NOW() + interval \'4 DAY 1 MONTH\'',
        ];
        $this->assertEquals($expected, $result, var_export($result, true));

        // 2. Erreur
        $sql = "SELECT NOW() + interval '4 DBY 1 MONTH'";
        $result = $this->connection->driver()->checkSqlSyntax($sql);
        $expected = [
            'success' => false,
            'message' => '7: ERROR:  invalid input syntax for type interval: "4 DBY 1 MONTH"',
            'value' => 'SELECT NOW() + interval \'4 DBY 1 MONTH\'',
        ];
        if (preg_match('/ERR(O|EU)R.*interval.*4 DBY 1 MONTH/', $result['message'])) {
            $expected['message'] = $result['message'];
        }
        $this->assertEquals($expected, $result, var_export($result, true));
    }

    /**
     * Test de la méthode Postgres.Postgres::checkIntervalSyntax()
     */
    public function testCheckIntervalSyntax()
    {
        // 1. Succès
        $interval = '4 DAY 1 MONTH';
        $result = $this->connection->driver()->checkIntervalSyntax($interval);
        $expected = [
            'value' => $interval,
            'success' => true,
            'message' => null
        ];
        $this->assertEquals($expected, $result, var_export($result, true));

        // 2. Erreur
        $interval = '4 DBY 1 MONTH';
        $result = $this->connection->driver()->checkIntervalSyntax($interval);
        $expected = [
            'value' => $interval,
            'success' => false,
            'message' => '7: ERROR:  invalid input syntax for type interval: "4 DBY 1 MONTH"'
        ];
        if (preg_match('/ERR(O|EU)R.*interval.*4 DBY 1 MONTH/', $result['message'])) {
            $expected['message'] = $result['message'];
        }
        $this->assertEquals($expected, $result, var_export($result, true));
    }

    /**
     * Test de la méthode Postgres.Postgres::functions()
     *
     * @see $functions
     */
    public function testFunctions()
    {
        $conditions = [
            "pg_namespace.nspname = 'public'",
            "pg_proc.proname ~ 'cakephp_validate'"
        ];
        $result = $this->connection->driver()->functions($conditions);
        $this->assertEquals($this->functions, $result, var_export($result, true));
    }
}
