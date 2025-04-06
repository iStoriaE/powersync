<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PostgresPersister
{

    /**
     * Escapes an identifier for use in a query.
     */
    protected function escapeIdentifier(string $identifier): string
    {
        return '"' . str_replace(['"', '.'], ['""', '"."'], $identifier) . '"';
    }

    /**
     * Processes a batch of operations (PUT, PATCH, DELETE).
     */
    public function updateBatch(array $sentData): void
    {

        DB::transaction(function () use ($sentData) {

            foreach ($sentData as $key => $value) {

                $table = $sentData['table'];

                if (request()->method() === 'PUT') {

                    $data = $sentData['data'];

                    if (count($data) === 1 && isset($data['id'])) {
                        continue;
                    }

                    $withId = array_merge($data, ['id' => $data['id']]);

                    $columns = array_map([$this, 'escapeIdentifier'], array_keys($withId));

                    $columnsJoined = implode(', ', $columns);

                    $updateClauses = array_map(
                        fn($key) => $this->escapeIdentifier($key) . " = EXCLUDED." . $this->escapeIdentifier($key),
                        array_filter(array_keys($data), fn($key) => $key !== 'id')
                    );

                    $updateClause = !empty($updateClauses)
                        ? 'DO UPDATE SET ' . implode(', ', $updateClauses)
                        : 'DO NOTHING';

                    $query = "
                        WITH data_row AS (
                            SELECT (json_populate_record(NULL::{$table}, ?::json)).*
                        )
                        INSERT INTO {$table} ({$columnsJoined})
                        SELECT {$columnsJoined} FROM data_row
                        ON CONFLICT(id) {$updateClause}";

                    DB::connection('pgsql')->statement($query, [json_encode($withId)]);
                } elseif (request()->method()  === 'PATCH') {

                    $data = $sentData['data'];
                    
                    if (count($data) === 1 && isset($data['id'])) {
                        continue;
                    }

                    $withId = array_merge($data, ['id' => $data['id']]);

                    $updateClauses = array_map(
                        fn($key) => $this->escapeIdentifier($key) . " = data_row." . $this->escapeIdentifier($key),
                        array_filter(array_keys($data), fn($key) => $key !== 'id')
                    );

                    $query = "
                        WITH data_row AS (
                            SELECT (json_populate_record(NULL::{$table}, ?::json)).*
                        )
                        UPDATE {$table}
                        SET " . implode(', ', $updateClauses) . "
                        FROM data_row
                        WHERE {$table}.id = data_row.id";

                    DB::connection('pgsql')->statement($query, [json_encode($withId)]);
                } elseif (request()->method()  === 'DELETE') {
                    $id = $op['id'] ?? $op['data']['id'];
                    $query = "
                        WITH data_row AS (
                            SELECT (json_populate_record(NULL::{$table}, ?::json)).*
                        )
                        DELETE FROM {$table}
                        USING data_row
                        WHERE {$table}.id = data_row.id";

                    DB::connection('pgsql')->statement($query, [json_encode(['id' => $id])]);
                }
            }
        });
    }

    /**
     * Creates or updates a checkpoint for a user and client.
     */
    public function createCheckpoint(string $userId, string $clientId): int
    {
        $query = "
            INSERT INTO checkpoints (user_id, client_id, checkpoint)
            VALUES (?, ?, 1)
            ON CONFLICT (user_id, client_id)
            DO UPDATE SET checkpoint = checkpoints.checkpoint + 1
            RETURNING checkpoint";

        $result = DB::connection('pgsql')->selectOne($query, [$userId, $clientId]);

        return $result->checkpoint;
    }

    public static function make(): PostgresPersister
    {
        return new self();
    }
}
