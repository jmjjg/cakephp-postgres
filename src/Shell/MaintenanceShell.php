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
     * La constante à utiliser dans la méthode _stop() en cas de succès.
     */
    const SUCCESS = 0;

    /**
     * La constante à utiliser dans la méthode _stop() en cas d'erreur.
     */
    const ERROR = 1;

    /**
     * Liste des sous-commandes et de leur description.
     *
     * @var array
     */
    public $commands = [
        'sequences' => [
            'help' => 'Mise à jour des compteurs des champs auto-incrémentés'
        ],
        'reindex' => [
            'help' => 'Reconstruction des indexes'
        ],
        'vacuum' => [
            'help' => 'Nettoyage de la base de données et mise à jour des statistiques du planificateur'
        ],
        'cluster' => [
            'help' => "Effectue toutes les opérations de maintenance ( reindex, sequence, vacuum )."
        ]
    ];

    /**
     * Liste des options et de leur description.
     *
     * @var array
     */
    public $options = [
        'connection' => [
            'short' => 'c',
            'help' => 'Le nom de la connection à la base de données',
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
     * Executes all maintenance commands.
     *
     * @return void
     */
    public function main()
    {
        $success = true;

        foreach (array_keys($this->commands) as $command) {
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
     * Rebuild indexes
     *
     * @return bool
     */
    public function reindex()
    {
        $sql = sprintf('REINDEX DATABASE "%s";', Hash::get($this->connection->config(), 'database'));
        return $this->singleQuery($sql, __FUNCTION__);
    }

    /**
     * Mise à jour des compteurs des champs auto-incrémentés.
     *
     * @return bool
     */
    public function sequences()
    {
        $this->out(sprintf('%s - %s', date('H:i:s'), 'sequences'));

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
     * Nettoyage de la base de données et mise à jour des statistiques du planificateur
     *
     * @url http://docs.postgresqlfr.org/8.4/maintenance.html (pas FULL)
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

        $parser->description('Script de maintenance de base de données PostgreSQL');
        $parser->addSubcommands($this->commands);
        $parser->addOptions($this->options);

        return $parser;
    }
}
