<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', static function (Blueprint $table) {
            $table->id();
            $table->string('key')->index();
            $table->text('value');
            $table->foreignId('language_id')
                ->constrained(app(\App\Models\Language::class)->getTable())
                ->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Make combination of key and language_id unique
            $table->unique(['key', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
