<?php
// Konfigurasi PostgreSQL / Supabase.
// Isi dari Project Settings > Database > Connection string di Supabase.
$host = getenv('DB_HOST') ?: 'aws-1-ap-southeast-2.pooler.supabase.com';
$port = getenv('DB_PORT') ?: '6543';
$database = getenv('DB_NAME') ?: 'postgres';
$user = getenv('DB_USER') ?: 'postgres.ktebznfxclfunrsklwgl';
$password = getenv('DB_PASSWORD') ?: 'narelio2023';
$sslmode = getenv('DB_SSLMODE') ?: 'require';

try {
    $dsn = "pgsql:host={$host};port={$port};dbname={$database};sslmode={$sslmode}";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

class DbResult
{
    private array $rows;
    private int $index = 0;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function fetchAssoc(): ?array
    {
        if ($this->index >= count($this->rows)) {
            return null;
        }

        return $this->rows[$this->index++];
    }

    public function numRows(): int
    {
        return count($this->rows);
    }
}

function db_query(string $query, array $params = []): DbResult|bool
{
    global $conn;

    try {
        $statement = $conn->prepare($query);
        $statement->execute($params);

        if ($statement->columnCount() > 0) {
            return new DbResult($statement->fetchAll());
        }

        return true;
    } catch (PDOException $e) {
        $GLOBALS['db_last_error'] = $e->getMessage();
        return false;
    }
}

function db_fetch_assoc(DbResult|bool $result): ?array
{
    if (!$result instanceof DbResult) {
        return null;
    }

    return $result->fetchAssoc();
}

function db_num_rows(DbResult|bool $result): int
{
    if (!$result instanceof DbResult) {
        return 0;
    }

    return $result->numRows();
}

function db_escape(?string $value): string
{
    global $conn;

    $quoted = $conn->quote($value ?? '');
    return substr($quoted, 1, -1);
}

function db_error(): string
{
    return $GLOBALS['db_last_error'] ?? 'Unknown database error';
}

function db_insert_id(?string $sequence = null): string
{
    global $conn;

    return $sequence ? $conn->lastInsertId($sequence) : $conn->lastInsertId();
}
?>
