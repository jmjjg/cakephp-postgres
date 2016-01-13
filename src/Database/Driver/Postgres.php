<?php
/**
 * Source code for the Postgres.Postgres driver class.
 */
namespace Postgres\Database\Driver;

use Cake\Cache\Cache;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Hash;

/**
 * The Postgres.Postgres driver class encapsulates several utility functions for
 * the PostgreSQL database.
 */
class Postgres extends \Cake\Database\Driver\Postgres
{
    /**
     * Executes an SQL query and fetch the associated results.
     *
     * @param string $sql The SQL query.
     * @return array
     */
    protected function query($sql)
    {
        $name = Hash::get($this->_config, 'name');
        $conn = ConnectionManager::get($name);
        return $conn->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Permet d'obtenir la version de PostgreSQL utilisée.
     *
     * @param bool $full false pour obtenir uniquement le numéro de version
     * @return string
     */
    public function version($full = false)
    {
        $sql = 'SELECT version();';
        $version = Hash::get($this->query($sql), '0.version');

        if ($full === false) {
            $version = preg_replace('/.*PostgreSQL ([^ ]+) .*$/', '\1', $version);
        }

        return $version;
    }

    /**
     * Vérification de la syntaxe d'un morceau de code SQL par PostgreSQL,
     * en utilisant la méthode EXPLAIN $sql.
     *
     * Retourne un arry avec les clés success (boolean) et message qui contient
     * un éventuel message d'erreur ou la valeur NULL en cas de succès.
     *
     * @param string $sql Le code SQL à vérifier.
     * @return array
     */
    public function checkSqlSyntax($sql)
    {
        try {
            $success = ($this->query("EXPLAIN {$sql}") !== false);
            $message = null;
        } catch (\PDOException $e) {
            $success = false;
            $message = "Error {$e->errorInfo[0]}: {$e->errorInfo[2]}";
        }

        return [
            'success' => $success,
            'message' => $message,
            'value' => $sql
        ];
    }

    /**
     * Permet de vérifier la syntaxe d'un intervalle au sens PostgreSQL.
     *
     * @param string $interval L'intervalle à vérifier.
     * @return array
     */
    public function checkIntervalSyntax($interval)
    {
        $sql = "SELECT NOW() + interval '{$interval}'";
        return ['value' => $interval] + $this->checkSqlSyntax($sql);
    }

    /**
     * Retourne les conditions à utiliser dans une requête SQL.
     *
     * @param array $conditions Les conditions sous forme d'array de strings
     * @param bool $and true pour ajouter le terme AND au début
     * @return string
     */
    protected function conditions(array $conditions = [], $and = true)
    {
        return (!empty($conditions) ? ( $and ? ' AND ' : ' ' ) . implode(' AND ', $conditions) : '' );
    }

    /**
     * Retourne la liste des fonctions PostgreSQL disponibles (schema, name,
     * result, arguments).
     *
     * @param array $conditions Conditions supplémentaires éventuelles.
     * @return array
     */
    public function functions(array $conditions = [])
    {
        // FIXME: better cache key
        $cacheKey = Hash::get($this->_config, 'name') . '_' . str_replace('\\', '_', __CLASS__) . '_' . __FUNCTION__ . '_' . md5(serialize($conditions));
        $result = Cache::read($cacheKey);

        if ($result === false) {
            $sql = "SELECT
                            pg_namespace.nspname AS \"schema\",
                            pg_proc.proname AS \"name\",
                            FORMAT_TYPE( pg_proc.prorettype, NULL ) AS \"result\",
                            OIDVECTORTYPES( pg_proc.proargtypes ) AS \"arguments\"
                        FROM pg_proc
                            INNER JOIN pg_namespace ON ( pg_proc.pronamespace = pg_namespace.oid )
                        WHERE
                            pg_proc.prorettype <> 0
                            AND (
                                pg_proc.pronargs = 0
                                OR OIDVECTORTYPES( pg_proc.proargtypes ) <> ''
                            )
                            " . $this->conditions($conditions) . "
                        ORDER BY \"schema\", \"name\", \"result\", \"arguments\";";

            $result = $this->query($sql);
            Cache::write($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Read applied check constraints.
     *
     * @param array $conditions Conditions supplémentaires éventuelles.
     * @return array
     */
    public function constraints(array $conditions = [])
    {
        $cacheKey = Hash::get($this->_config, 'name') . '_' . str_replace('\\', '_', __CLASS__) . '_' . __FUNCTION__ . '_' . md5(serialize($conditions));
        $result = Cache::read($cacheKey);

        if ($result === false) {
            $sql = "SELECT
                        pg_constraint.consrc AS source
                    FROM pg_catalog.pg_constraint
                        INNER JOIN pg_catalog.pg_class ON (
                            pg_class.oid = pg_constraint.conrelid
                        )
                        INNER JOIN pg_catalog.pg_namespace ON (
                            pg_namespace.oid = pg_class.relnamespace
                        )
                    WHERE
                        pg_constraint.contype = 'c'
                        " . $this->conditions($conditions) . "
                    ORDER BY pg_constraint.conname;";

            $result = $this->query($sql);
            Cache::write($cacheKey, $result);
        }

        return $result;
    }

    /**
     * Returns the list of sequences from the database schema.
     *
     * @param array $conditions Conditions supplémentaires éventuelles.
     * @return array
     */
    public function sequences(array $conditions = [])
    {
        $cacheKey = Hash::get($this->_config, 'name') . '_' . str_replace('\\', '_', __CLASS__) . '_' . __FUNCTION__ . '_' . md5(serialize($conditions));
        $result = Cache::read($cacheKey);

        if ($result === false) {
            $sql = "SELECT table_name AS \"table\",
                column_name	AS \"column\",
                column_default AS \"sequence\"
                FROM information_schema.columns
                WHERE column_default LIKE 'nextval(%::regclass)'
                    " . $this->conditions($conditions) . "
                ORDER BY table_name, column_name";

            $result = $this->query($sql);
            Cache::write($cacheKey, $result);
        }

        return $result;
    }
}
