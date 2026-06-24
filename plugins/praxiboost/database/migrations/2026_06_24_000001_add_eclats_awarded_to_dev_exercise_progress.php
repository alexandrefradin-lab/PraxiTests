<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dev_exercise_progress', function (Blueprint $table) {
            $table->boolean('eclats_awarded')->default(false)->after('notes'); // anti double-octroi
        });
    }

    public function down(): void
    {
        Schema::table('dev_exercise_progress', function (Blueprint $table) {
            $table->dropColumn('eclats_awarded');
        });
    }
};
