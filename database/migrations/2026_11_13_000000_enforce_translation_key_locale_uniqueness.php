<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['pages', 'services', 'products', 'projects', 'posts'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            $duplicate = DB::table($table)
                ->select('translation_key', 'locale')
                ->whereNotNull('translation_key')
                ->groupBy('translation_key', 'locale')
                ->havingRaw('COUNT(*) > 1')
                ->first();

            if ($duplicate !== null) {
                throw new RuntimeException("Duplicatas de translation_key por locale em {$table}; migração abortada sem alterar dados.");
            }
        }

        // Inspect every table before DDL because MySQL implicitly commits schema changes.
        $missingIndexes = [];
        foreach (self::TABLES as $table) {
            $index = "{$table}_translation_key_locale_unique";
            $existing = $this->namedIndex($table, $index);

            if ($existing !== null && (! $existing['unique'] || $existing['columns'] !== ['translation_key', 'locale'])) {
                throw new RuntimeException("Índice {$index} existente tem definição inesperada; migração abortada sem alterar schema.");
            }

            if ($existing === null) {
                $missingIndexes[$table] = $index;
            }
        }

        foreach ($missingIndexes as $table => $index) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->unique(['translation_key', 'locale'], $index));
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            $index = "{$table}_translation_key_locale_unique";
            $existing = $this->namedIndex($table, $index);

            if ($existing !== null && (! $existing['unique'] || $existing['columns'] !== ['translation_key', 'locale'])) {
                throw new RuntimeException("Índice {$index} existente tem definição inesperada; rollback abortado sem alterar schema.");
            }
        }

        foreach (array_reverse(self::TABLES) as $table) {
            $index = "{$table}_translation_key_locale_unique";

            if ($this->namedIndex($table, $index) !== null) {
                Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropUnique($index));
            }
        }
    }

    private function namedIndex(string $table, string $name): ?array
    {
        foreach (Schema::getIndexes($table) as $index) {
            if (strcasecmp($index['name'], $name) === 0) {
                return $index;
            }
        }

        return null;
    }
};
