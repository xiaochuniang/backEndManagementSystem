<?php
/**
 * Db — PDO 数据库连接单例
 *
 * 提供简洁的 query / fetchOne / fetchAll / execute 方法，
 * 供各 Model 调用，避免在每个 Model 中重复创建连接。
 */
class Db
{
    /** @var Db|null */
    private static ?Db $instance = null;

    /** @var \PDO */
    private \PDO $pdo;

    private function __construct(string $dsn, string $username, string $password)
    {
        $this->pdo = new \PDO($dsn, $username, $password, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(
        string $dsn = '',
        string $username = '',
        string $password = ''
    ): self {
        if (self::$instance === null) {
            self::$instance = new self($dsn, $username, $password);
        }
        return self::$instance;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * 查询多行
     *
     * @param  string $sql
     * @param  array  $params
     * @return array
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * 查询单行
     *
     * @param  string $sql
     * @param  array  $params
     * @return array|null
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * 执行 INSERT / UPDATE / DELETE，返回影响行数
     *
     * @param  string $sql
     * @param  array  $params
     * @return int
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 执行 INSERT，返回最后插入的自增 ID
     *
     * @param  string $sql
     * @param  array  $params
     * @return int
     */
    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }
}
