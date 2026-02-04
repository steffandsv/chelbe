<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('trello_id', 64)->unique();
            $table->string('title', 500);
            $table->longText('description')->nullable();
            $table->string('board_name', 255)->nullable();
            $table->string('list_name', 255)->nullable();
            $table->string('analyst', 100)->nullable();
            
            // Extracted dates
            $table->date('extracted_date')->nullable();
            $table->date('due_date')->nullable();
            
            // User-editable status
            $table->enum('user_status', ['pending', 'tracking', 'won', 'lost'])->default('pending');
            $table->text('defeat_reason')->nullable();
            $table->text('user_notes')->nullable();
            
            // Normalized metrics (Alta/Média/Baixa)
            $table->string('viabilidade_tatica', 20)->nullable();
            $table->string('complexidade_operacional', 20)->nullable();
            $table->string('lucratividade_potencial', 20)->nullable();
            $table->string('chance_vitoria', 20)->nullable();
            $table->string('risco_operacional', 20)->nullable();
            $table->string('ipm_score', 20)->nullable();
            
            // Tender metadata
            $table->string('portal', 100)->nullable();
            $table->string('pregao_number', 100)->nullable();
            $table->decimal('valor_estimado', 15, 2)->nullable();
            $table->string('orgao', 255)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('analyst');
            $table->index('user_status');
            $table->index('extracted_date');
            $table->index('board_name');
            $table->index('list_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
