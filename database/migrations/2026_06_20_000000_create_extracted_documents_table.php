<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracted_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->float('confidence')->default(0);
            $table->json('fields');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracted_documents');
    }
};
