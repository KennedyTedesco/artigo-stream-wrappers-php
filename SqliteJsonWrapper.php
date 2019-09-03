<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Capsule\Manager as Capsule;

final class SqliteJsonWrapper
{
    /** @var resource */
    public $context;

    /** @var string */
    private $result;

    /** @var Connection */
    private $connection;

    /** @var int */
    private $position = 0;

    public function stream_open(string $path, string $mode, int $options) : bool
    {
        $streamContext = \stream_context_get_options($this->context);

        if (empty($streamContext['database'])) {
            throw new \RuntimeException('Missing Stream Context');
        }

        $this->connect($streamContext);
        $this->query($path);

        return true;
    }

    public function stream_read(int $count)
    {
        $chunk = \mb_substr($this->result, $this->position, $count);

        $this->position += $count;

        return $chunk;
    }

    public function stream_eof() : bool
    {
        return ! ($this->position < \mb_strlen($this->result));
    }

    public function stream_tell() : int
    {
        return $this->position;
    }

    public function stream_stat() : ?array
    {
        return null;
    }

    private function connect(array $options) : void
    {
        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => 'sqlite',
            'database'  =>  $options['database']['file'],
            'prefix'    => '',
        ]);

        $this->connection = $capsule->getConnection();
    }

    private function query(string $path) : void
    {
        $table = \parse_url($path, \PHP_URL_HOST);

        $where = [];
        if ($path = \parse_url($path, \PHP_URL_PATH)) {
            $criteria = \explode('/', $path);

            $where = [
                $criteria[1] => $criteria[2]
            ];
        }

        $this->result = $this->connection->table($table)->where($where)->get()->toJson();
    }
}
