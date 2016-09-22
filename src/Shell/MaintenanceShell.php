<?php
/**
 * Source code for the MaintenanceShell class.
 */
namespace Postgres\Shell;

use Cake\Console\Shell;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

/**
 * Postgres maintenance shell.
 *
 * @todo: le sql dans le driver ?
 */
class MaintenanceShell extends Shell
{

    /**
     * The database connection.
     *
     * @var \Cake\Database\Connection
     */
    protected $connection = null;

    /**
     * The success value to use in the _stop method.
     */
    const SUCCESS = 0;

    /**
     * The general error value to use in the _stop method.
     */
    const ERROR = 1;

    /**
     * Subcommands and their definitions.
     *
     * @var array
     */
    public $commands = [
        'sequences' => [
            'help' => 'Set all sequence\'s current value to the lowest available field value.'
        ],
        'reindex' => [
            'help' => 'Rebuild indexes.'
        ],
        'vacuum' => [
            'help' => 'Garbage-collect and analyze the database.'
        ],
        'cluster' => [
            'help' => "Cluster all tables."
        ],
        'all' => [
            'help' => "Executes all maintenance commands ( reindex, sequence, vacuum, cluster )."
        ]
    ];

    /**
     * All maintenance commands.
     *
     * @var array
     */
    public $all = ['sequences', 'reindex', 'vacuum', 'cluster'];

    /**
     * Options and their descriptions.
     *
     * @var array
     */
    public $options = [
        'connection' => [
            'short' => 'c',
            'help' => 'The name of the database connection',
            'default' => 'default',
        ]
    ];

    /**
     * Initialize connection.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();
        $this->connection = ConnectionManager::get($this->params['connection']);
    }

    /**
     * Main function, errors with help.
     *
     * @return void
     */
    public function main()
    {
        $this->_stop(self::ERROR);
    }

    /**
     * Executes all maintenance commands.
     *
     * @return void
     */
    public function all()
    {
        $success = true;

        foreach ($this->all as $command) {
            $success = $success && $this->{$command}();
        }

        $this->_stop($success ? self::SUCCESS : self::ERROR);
    }

    /**
     * Executes a single SQL command maintenance action and returns the result
     * or stops the shell if it was the only command.
     *
     * @param string $sql The SQL to execute
     * @param string $sender The name of the calling function
     * @return bool or stops program execution if it was the only command
     */
    protected function singleQuery($sql, $sender)
    {
        $this->out(sprintf('%s - %s', date('H:i:s'), $sql));
        $success = $this->connection->query($sql)->fetchAll() !== false;

        if ($this->command === $sender) {
            $this->_stop($success ? self::SUCCESS : self::ERROR);
        }

        return $success;
    }

    /**
     * Rebuild indexes.
     *
     * @return bool
     */
    public function reindex()
    {
        $sql = sprintf('REINDEX DATABASE "%s";', Hash::get($this->connection->config(), 'database'));
        return $this->singleQuery($sql, __FUNCTION__);
    }

    /**
     * Set all sequence's current value to the lowest available field value.
     *
     * @return bool
     */
    public function sequences()
    {
        $this->out(sprintf('%s - %s', date('H:i:s'), 'Set all sequence\'s current values'));

        $success = ($this->connection->begin() !== false);

        $schema = Hash::get($this->connection->config(), 'schema') ? : 'public';
        $conditions = ["table_schema = '{$schema}'"];

        foreach ($this->connection->driver()->sequences($conditions) as $sequence) {
            $sequence['sequence'] = preg_replace('/^nextval\(\'(.*)\'.*\)$/', '\1', $sequence['sequence']);

            $sql = "SELECT setval('{$sequence['sequence']}', COALESCE(MAX({$sequence['column']}),0)+1, false) FROM {$sequence['table']};";
            $success = $success && ($this->connection->query($sql)->fetchAll('assoc') !== false);
        }

        if ($success) {
            $success = ($this->connection->commit() !== false) && $success;
        } else {
            $success = ($this->connection->rollback() !== false) && $success;
        }

        if ($this->command === __FUNCTION__) {
            $this->_stop($success ? self::SUCCESS : self::ERROR);
        }

        return $success;
    }

    /**
     * Garbage-collect and analyze the database.
     *
     * @url http://docs.postgresqlfr.org/8.4/maintenance.html
     *
     * @return bool
     */
    public function vacuum()
    {
        $sql = 'VACUUM ANALYZE;';
        return $this->singleQuery($sql, __FUNCTION__);
    }

    /**
     * Cluster all tables.
     *
     * @url http://www.postgresql.org/docs/8.4/interactive/sql-cluster.html
     * @return bool
     */
    public function cluster()
    {
        $sql = 'CLUSTER;';
        return $this->singleQuery($sql, __FUNCTION__);
    }

    /**
     * Paramétrages et aides du shell.
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->description('Maintenance script for PostgreSQL databases');
        $parser->addSubcommands($this->commands);
        $parser->addOptions($this->options);

        return $parser;
    }
}
