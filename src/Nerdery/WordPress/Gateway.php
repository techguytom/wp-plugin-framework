<?php
/**
 * File Gateway.php
 * 
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */

namespace Nerdery\WordPress;

use \wpdb;
use \Exception;

/**
 * Class Gateway
 *
 * @package Nerdery\Plugin\WordPress
 * @author Douglas Linsmeyer <douglas.linsmeyer@nerdery.com>
 */
class Gateway
{
    /*
     * Error constants
     */
    const ERROR_TABLE_PREFIX_NOT_STRING = 'Table prefix argument must be of type string.';

    /**
     * @var wpdb
     */
    private $wpdb;

    /**
     * @var string
     */
    private $tablePrefix;

    /**
     * Constructor
     *
     * @param wpdb $wpdb
     * @param string $tablePrefix
     *
     * @throws Exception
     * @return self
     */
    public function __construct(wpdb $wpdb, $tablePrefix)
    {
        if (false === is_string($tablePrefix)) {
            throw new Exception(self::ERROR_TABLE_PREFIX_NOT_STRING);
        }

        $this->wpdb = $wpdb;
        $this->tablePrefix = $tablePrefix;

        return $this;
    }

    /**
     * Get the WordPress database access layer object
     *
     * @return wpdb
     */
    public function getWpDbal()
    {
        return $this->wpdb;
    }

    /**
     * Get the tablePrefix
     *
     * @return string
     */
    public function getTablePrefix()
    {
        $wpdb = $this->getWpDbal();
        $wpPrefix = $wpdb->prefix;

        return $wpPrefix . $this->tablePrefix;
    }

    /**
     * Query the database
     *
     * @param string $query
     *
     * @return false|int
     */
    public function query($query)
    {
        $wpdb = $this->getWpDbal();
        $result = $wpdb->query($query);
        return $result;
    }

    /**
     * Fetch a single row
     *
     * @param $query
     *
     * @return array
     */
    public function fetchRow($query)
    {
        $wpdb = $this->getWpDbal();
        $result = $wpdb->get_row($query, ARRAY_A);
        return $result;
    }

    /**
     * fetchRows
     *
     * @param $query
     *
     * @return array
     */
    public function fetchRows($query)
    {
        $wpdb = $this->getWpDbal();
        $result = $wpdb->get_results($query, ARRAY_A);
        return $result;
    }

    /**
     * Prepare an SQL query
     *
     * This will protect the query against SQL injection by leveraging
     * the WordPress DBAL functionality within it's "prepare()" method.
     *
     * @param string $sql
     * @param array $arguments
     *
     * @return false|null|string
     */
    public function prepareQuery($sql, array $arguments)
    {
        $arguments = $this->fixNull($arguments);
        $wpdb = $this->getWpDbal();
        $result = $wpdb->prepare($sql, $arguments);

        return $result;
    }

    /**
     * Update a record in the database
     *
     * @param string $tableName
     * @param array $data
     * @param array $where
     *
     * @return false|int
     */
    public function update($tableName, array $data, array $where)
    {
        $data = $this->fixNull($data);
        $dbal = $this->getWpDbal();
        $result = $dbal->update($tableName, $data, $where);

        return $result;
    }

    /**
     * Insert a new record into the database
     *
     * @param string $tableName
     * @param array $data
     *
     * @return false|int False on failure, ID of new row on insert
     */
    public function insert($tableName, array $data)
    {
        $data = $this->fixNull($data);
        $dbal = $this->getWpDbal();
        $result = $dbal->insert($tableName, $data);

        return $result;
    }

    /**
     * Delete a record from the database
     *
     * @param string $tableName
     * @param array $where
     *
     * @return false|int
     */
    public function delete($tableName, array $where)
    {
        $dbal = $this->getWpDbal();
        $result = $dbal->delete($tableName, $where);

        return $result;
    }

    /**
     * Show last database error
     *
     * @return null|string
     */
    public function getError()
    {
        $wpdb = $this->getWpDbal();
        $error = $wpdb->last_error;

        return $error;
    }

    /**
     * getDbHandle
     *
     * @return resource
     */
    public function getDbHandle()
    {
        $wpdb = $this->getWpDbal();
        $dbh = $wpdb->dbh;

        return $dbh;
    }

    /**
     * fixNull
     * This is a hack to fix a quirk with the WordPress DBAL. If you
     * pass in a NULL value for a column it will cast the NULL to a
     * string resulting in an empty string ("") which is not a valid
     * INT value for MySQL and can cause problems.
     * To prevent this strange behavior we're replacing NULL values
     * with "NULL" string which will be properly handled by MySQL.
     *
     * @param array $arguments
     *
     * @return array
     */
    private function fixNull(array $arguments)
    {
        foreach ($arguments as $argumentKey => $argumentValue) {
            if (null === $argumentValue) {
                $arguments[$argumentKey] = 'NULL';
            }

            if (is_bool($argumentValue)) {
                $arguments[$argumentKey] = (int) $argumentValue;
            }
        }

        return $arguments;
    }

    /**
     * Perform a database transaction
     * This allows us to perform transactions on our database, it does
     * however require that all tables be InnoDB (which may not be the case
     * especially with older installations of WordPress). Use this
     * method at your own risk.
     * If any exceptions are thrown within the callable, the transaction
     * will be rolled back, otherwise it will be committed.
     *
     * @param callable $callable
     *
     * @throws \Exception If transaction must be rolled back
     * @return mixed Returns the result of the callable
     */
    public function transaction(callable $callable)
    {
        $dbh = $this->getDbHandle();
        mysql_query('START TRANSACTION', $dbh);

        try {
            $result = $callable();
            mysql_query('COMMIT', $dbh);
        } catch (Exception $e) {
            mysql_query('ROLLBACK', $dbh);
            throw new Exception('A database error has occurred resulting in a rolled back transaction.');
        }

        return $result;
    }
}
