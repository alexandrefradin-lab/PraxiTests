<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mirror_exercises', function (Blueprint $table) {
            $table->text('prompt')->change();
        });
    }

    public function down(): void
    {
        Schema::table('mirror_exercises', function (Blueprint $table) {
            $table->string('prompt')->change();
        });
    }
};
