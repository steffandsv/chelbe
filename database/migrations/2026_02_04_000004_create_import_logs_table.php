<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255);
            $table->string('board_name', 255)->nullable();
            $table->unsignedInteger('cards_imported')->default(0);
            $table->unsignedInteger('cards_updated')->default(0);
            $table->unsignedInteger('labels_imported')->default(0);
            $table->timestamps();
            
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
