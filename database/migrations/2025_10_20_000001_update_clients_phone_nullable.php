<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('clients_tmp', function (Blueprint $table): void {
                $table->id();
                $table->string('full_name');
                $table->string('phone')->nullable()->index();
                $table->string('email')->nullable()->index();
                $table->date('birthdate')->nullable();
                $table->string('instagram')->nullable();
                $table->boolean('consent_marketing')->default(false);
                $table->string('source')->nullable();
                $table->timestamp('last_appointment_at')->nullable();
                $table->json('tags')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('verification_code')->nullable();
                $table->timestamp('verification_code_expires_at')->nullable();
                $table->json('verification_channels')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement(
                'INSERT INTO clients_tmp (
                    id, full_name, phone, email, birthdate, instagram, consent_marketing, source,
                    last_appointment_at, tags, password, email_verified_at, phone_verified_at,
                    verification_code, verification_code_expires_at, verification_channels,
                    remember_token, created_at, updated_at, deleted_at
                )
                SELECT
                    id, full_name, phone, email, birthdate, instagram, consent_marketing, source,
                    last_appointment_at, tags, password, email_verified_at, phone_verified_at,
                    verification_code, verification_code_expires_at, verification_channels,
                    remember_token, created_at, updated_at, deleted_at
                FROM clients'
            );

            Schema::drop('clients');
            Schema::rename('clients_tmp', 'clients');
        } else {
            $driver = DB::getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE clients MODIFY phone VARCHAR(255) NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE clients ALTER COLUMN phone DROP NOT NULL');
            } elseif ($driver === 'sqlsrv') {
                DB::statement('ALTER TABLE clients ALTER COLUMN phone VARCHAR(255) NULL');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::create('clients_tmp', function (Blueprint $table): void {
                $table->id();
                $table->string('full_name');
                $table->string('phone')->index();
                $table->string('email')->nullable()->index();
                $table->date('birthdate')->nullable();
                $table->string('instagram')->nullable();
                $table->boolean('consent_marketing')->default(false);
                $table->string('source')->nullable();
                $table->timestamp('last_appointment_at')->nullable();
                $table->json('tags')->nullable();
                $table->string('password')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('verification_code')->nullable();
                $table->timestamp('verification_code_expires_at')->nullable();
                $table->json('verification_channels')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });

            DB::statement(
                'INSERT INTO clients_tmp (
                    id, full_name, phone, email, birthdate, instagram, consent_marketing, source,
                    last_appointment_at, tags, password, email_verified_at, phone_verified_at,
                    verification_code, verification_code_expires_at, verification_channels,
                    remember_token, created_at, updated_at, deleted_at
                )
                SELECT
                    id, full_name, phone, email, birthdate, instagram, consent_marketing, source,
                    last_appointment_at, tags, password, email_verified_at, phone_verified_at,
                    verification_code, verification_code_expires_at, verification_channels,
                    remember_token, created_at, updated_at, deleted_at
                FROM clients'
            );

            Schema::drop('clients');
            Schema::rename('clients_tmp', 'clients');
        } else {
            $driver = DB::getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                DB::statement('ALTER TABLE clients MODIFY phone VARCHAR(255) NOT NULL');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE clients ALTER COLUMN phone SET NOT NULL');
            } elseif ($driver === 'sqlsrv') {
                DB::statement('ALTER TABLE clients ALTER COLUMN phone VARCHAR(255) NOT NULL');
            }
        }
    }
};
