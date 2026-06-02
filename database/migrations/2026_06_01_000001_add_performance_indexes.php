<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // P-13 — Filtre tri-colonnes dans AttemptController::start() et TestController::show()
        // WHERE user_id = ? AND test_id = ? AND status = ?
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->index(['user_id', 'test_id', 'status'], 'attempts_user_test_status_idx');
        });

        // P-14 — Filtre dans SequenceRunner::trigger()
        // WHERE trigger_event = ? AND enabled = ?
        if (Schema::hasTable('email_sequences')) {
            Schema::table('email_sequences', function (Blueprint $table) {
                $table->index(['trigger_event', 'enabled'], 'email_seq_trigger_enabled_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('test_attempts', function (Blueprint $table) {
            $table->dropIndex('attempts_user_test_status_idx');
        });

        if (Schema::hasTable('email_sequences')) {
            Schema::table('email_sequences', function (Blueprint $table) {
                $table->dropIndex('email_seq_trigger_enabled_idx');
            });
        }
    }
};
