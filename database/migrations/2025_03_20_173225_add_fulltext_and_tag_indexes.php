<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fulltext index on translations.value for better text search performance
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE translations ADD FULLTEXT search_index (value)');
        }

        // Add index on tag_id in the translation_tag table
        // This is helpful when querying which translations have a specific tag
        Schema::table('translation_tag', function (Blueprint $table) {
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove fulltext index if it exists
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE translations DROP INDEX search_index');
        }

        // Remove tag_id index if it exists
        Schema::table('translation_tag', function (Blueprint $table) {
            $table->dropIndex(['tag_id']);
        });
    }
};
