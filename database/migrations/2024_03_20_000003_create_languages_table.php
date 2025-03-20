<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', static function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g., 'en', 'fr', 'es'
            $table->string('name'); // e.g., 'English', 'French', 'Spanish'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
