<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('category', 100)->nullable();
            $table->string('value', 100)->nullable();
            $table->string('raw_label', 255)->nullable();
            $table->string('color', 50)->nullable();
            $table->timestamps();
            
            $table->index('category');
            $table->index('value');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_labels');
    }
};
