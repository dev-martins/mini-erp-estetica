<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->string('verification_code')->nullable()->after('phone_verified_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');
            $table->json('verification_channels')->nullable()->after('verification_code_expires_at');
            $table->rememberToken();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'password',
                'email_verified_at',
                'phone_verified_at',
                'verification_code',
                'verification_code_expires_at',
                'verification_channels',
                'remember_token',
            ]);

            $table->dropSoftDeletes();
        });
    }
};
