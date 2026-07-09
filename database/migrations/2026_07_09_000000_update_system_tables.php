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
        // 1. Update documents table: drop unique constraint safely, add softDeletes
        Schema::table('documents', function (Blueprint $table) {
            try {
                $table->dropUnique('documents_registration_number_unique');
            } catch (\Exception $e) {
                // Ignore if unique index was not created or already dropped
            }
            $table->softDeletes();
        });

        // 2. Create document_types table
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 3. Create language_directions table
        Schema::create('language_directions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 4. Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('user_id', 36)->nullable();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->string('model_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('language_directions');
        Schema::dropIfExists('document_types');

        Schema::table('documents', function (Blueprint $table) {
            $table->string('registration_number')->unique()->change();
            $table->dropSoftDeletes();
        });
    }
};
