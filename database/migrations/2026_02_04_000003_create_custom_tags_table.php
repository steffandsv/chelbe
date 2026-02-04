<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('tag_name', 100);
            $table->string('tag_value', 255)->nullable();
            $table->timestamps();
            
            $table->index('tag_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_tags');
    }
};
