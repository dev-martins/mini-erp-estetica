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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('client_package_id')
                ->nullable()
                ->after('service_id')
                ->constrained('client_packages')
                ->nullOnDelete();

            $table->integer('package_session_number')
                ->nullable()
                ->after('client_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_package_id');
            $table->dropColumn('package_session_number');
        });
    }
};
