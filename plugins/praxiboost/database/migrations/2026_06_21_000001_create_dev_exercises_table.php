<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('summary');                       // visible même verrouillé
            $table->longText('body');                         // contenu markdown
            $table->unsignedSmallInteger('duration_min')->default(5);
            $table->string('icon')->nullable();
            $table->unsignedInteger('threshold_eclats')->default(0); // palier de déblocage
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'threshold_eclats']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_exercises');
    }
};
