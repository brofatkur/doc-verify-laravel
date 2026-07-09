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
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_id', 8)->unique();
            $table->string('registration_number');
            $table->date('document_date');
            $table->string('document_type');
            $table->string('language_pair');
            $table->string('client_name');
            $table->string('status')->default('Selesai');
            $table->boolean('is_qr_generated')->default(false);
            $table->uuid('translator_id');
            $table->timestamps();

            $table->foreign('translator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
