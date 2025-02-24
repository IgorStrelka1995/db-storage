<?php

declare(strict_types=1);

namespace Istrelka\Storage;

use Istrelka\Storage\Exception\ParameterException;
use Istrelka\Storage\Exception\QueryException;
use Istrelka\Storage\Exception\ConnectionException;

class Storage
{
    /**
     * @var \PDO|null
     */
    private ?\PDO $pdo = null;

    /**
     * @return $this
     * @throws ConnectionException
     */
    public function connect(): Storage
    {
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        $dsn = getenv('DB_DSN');

        try {
            $this->pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (ConnectionException $e) {
            throw new ConnectionException("Database connection failed: " . $e->getMessage());
        }

        return $this;
    }

    /**
     * @return \PDO
     * @throws ConnectionException
     */
    public function getConnection(): \PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * @param string $tableName
     * @param array $columns Format of the columns ['column1', 'column2']
     * @param int $mode
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function findAll(string $tableName, array $columns = [], int $mode = \PDO::FETCH_ASSOC): array
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        $statement = empty($columns) ? '*' : implode(', ', $columns);

        $sql = "SELECT {$statement} FROM {$tableName}";

        try {
            $stmt = $connection->prepare($sql);

            $stmt->execute();

            return $stmt->fetchAll($mode);
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }

    /**
     * @param string $tableName
     * @param array $condition Format of the condition ['column' => 'value']
     * @param array $columns Format of the columns ['column1', 'column2']
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function findOne(string $tableName, array $condition, array $columns = []): array
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        if (!$condition) {
            throw new ParameterException("Please, provide condition values.");
        }

        if (array_keys($condition) === range(0, count($condition) - 1)) {
            throw new ParameterException("The format of condition data is wrong. Right format is '['column' => 'value']'");
        }

        $column = key($condition);

        $val = current($condition);

        $statement = empty($columns) ? '*' : implode(', ', $columns);

        $sql = "SELECT {$statement} FROM {$tableName} WHERE {$column} = :{$column}";

        try {
            $stmt = $connection->prepare($sql);

            $stmt->bindParam(":{$column}", $val);

            $stmt->execute();

            return $stmt->fetch();
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }

    /**
     * @param string $tableName
     * @param array $conditions Format of the conditions [['column', 'equal', 'value], ['column', 'equal' 'value']]
     * @param array $columns Format of the columns ['column1', 'column2']
     * @return array
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function find(string $tableName, array $conditions, array $columns = []): array
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        if (!$conditions) {
            throw new ParameterException("Please, provide condition values.");
        }

        $statement = empty($columns) ? '*' : implode(', ', $columns);

        $sql = "SELECT {$statement} FROM {$tableName}";

        $sql .= " WHERE ";

        $conditionParts = [];

        foreach ($conditions as $condition) {
            list($column, $state) = $condition;

            $conditionParts[] = "{$column} {$state} :{$column}";
        }

        $sql .= implode(' AND ', $conditionParts);

        try {
            $stmt = $connection->prepare($sql);

            foreach ($conditions as $condition) {
                list($column,, $value) = $condition;

                $stmt->bindValue(":{$column}", $value);
            }

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }

    /**
     * @param string $tableName
     * @param array $insertData Format of the insert data ['column' => 'value']
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function insert(string $tableName, array $insertData): bool
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        if (!$insertData) {
            throw new ParameterException("Please, provide data for insert query.");
        }

        if (array_keys($insertData) === range(0, count($insertData) - 1)) {
            throw new ParameterException("The format of insert data is wrong. Right format is '['column' => 'value']'");
        }

        $columns = implode(', ', array_keys($insertData));

        $placeholders = implode(", ", array_map(fn($key) => ":$key", array_keys($insertData)));

        $sql = "INSERT INTO {$tableName} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $connection->prepare($sql);

            foreach ($insertData as $column => $value) {
                $stmt->bindParam(":{$column}", $value);
            }

            return $stmt->execute();
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }

    /**
     * @param string $tableName
     * @param array $updatedData Format of the update data ['column' => 'value']
     * @param array $conditions Format of the conditions [['column', 'equal', 'value], ['column', 'equal' 'value']]
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function update(string $tableName, array $updatedData, array $conditions): bool
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        if (!$updatedData) {
            throw new ParameterException("Please, provide data for update query.");
        }

        if (!$conditions) {
            throw new ParameterException("Please, provide condition values.");
        }

        if (array_keys($updatedData) === range(0, count($updatedData) - 1)) {
            throw new ParameterException("The format of update data is wrong. Right format is '['column' => 'value']'");
        }

        $columnUpdated = key($updatedData);

        $sql = "UPDATE {$tableName} SET {$columnUpdated} = :{$columnUpdated}_updated";

        $sql .= " WHERE ";

        $conditionParts = [];

        foreach ($conditions as $condition) {
            list($column, $state) = $condition;

            $conditionParts[] = "{$column} {$state} :{$column}";
        }

        $sql .= implode(' AND ', $conditionParts);

        try {
            $stmt = $connection->prepare($sql);

            $stmt->bindValue(":{$columnUpdated}_updated", current($updatedData));

            foreach ($conditions as $condition) {
                list($column,, $value) = $condition;

                $stmt->bindValue(":{$column}", $value);
            }

            return $stmt->execute();
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }

    /**
     * @param string $tableName
     * @param array $condition Format of the condition ['column' => 'value']
     * @return bool
     * @throws ConnectionException
     * @throws ParameterException
     */
    public function delete(string $tableName, array $condition): bool
    {
        $connection = $this->getConnection();

        if (!$tableName) {
            throw new ParameterException("Please, provide a name of database table.");
        }

        if (!$condition) {
            throw new ParameterException("Please, provide condition values.");
        }

        if (array_keys($condition) === range(0, count($condition) - 1)) {
            throw new ParameterException("The format of delete data is wrong. Right format is '['column' => 'value']'");
        }

        $column = key($condition);

        $val = current($condition);

        $sql = "DELETE FROM {$tableName} WHERE {$column} = :{$column}";

        try {
            $stmt = $connection->prepare($sql);

            $stmt->bindParam(":{$column}", $val);

            return $stmt->execute();
        } catch (\PDOException $e) {
            throw new QueryException("An error occurred while process of the query. {$e->getMessage()}");
        }
    }
}