<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_interval_hours')
                ->default(0)
                ->after('duration_min');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_interval_hours')
                ->nullable()
                ->after('sessions_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('min_interval_hours');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('min_interval_hours');
        });
    }
};
