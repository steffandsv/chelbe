<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create boards table
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deck_board_id')->unique();
            $table->string('title', 255);
            $table->string('owner', 100)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamps();
            
            $table->index('title');
        });

        // 2. Create stacks table
        Schema::create('stacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('deck_stack_id');
            $table->string('title', 255);
            $table->unsignedInteger('stack_order')->default(0);
            $table->timestamps();
            
            $table->unique(['board_id', 'deck_stack_id']);
            $table->index('title');
        });

        // 3. Create assigned_users table
        Schema::create('assigned_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->onDelete('cascade');
            $table->string('uid', 100);
            $table->string('displayname', 255)->nullable();
            $table->unsignedTinyInteger('participant_type')->default(0);
            $table->unsignedBigInteger('deck_assignment_id')->nullable();
            $table->timestamps();
            
            $table->unique(['card_id', 'uid']);
            $table->index('uid');
        });

        // 4. Add new columns to cards table
        Schema::table('cards', function (Blueprint $table) {
            // Deck IDs for deduplication
            $table->unsignedBigInteger('deck_card_id')->nullable()->after('trello_id');
            $table->unsignedBigInteger('deck_board_id')->nullable()->after('deck_card_id');
            $table->unsignedBigInteger('deck_stack_id')->nullable()->after('deck_board_id');
            
            // Stack/Column name
            $table->string('stack_name', 255)->nullable()->after('list_name');
            
            // Owner info
            $table->string('owner_uid', 100)->nullable()->after('analyst');
            $table->string('owner_displayname', 255)->nullable()->after('owner_uid');
            
            // Card metadata
            $table->unsignedInteger('card_order')->default(0)->after('owner_displayname');
            $table->boolean('archived')->default(false)->after('card_order');
            $table->boolean('done')->nullable()->after('archived');
            $table->boolean('notified')->default(false)->after('done');
            $table->unsignedInteger('comments_count')->default(0)->after('notified');
            
            // Original timestamps from Deck
            $table->timestamp('deck_created_at')->nullable()->after('comments_count');
            $table->timestamp('deck_modified_at')->nullable()->after('deck_created_at');
            
            // ETag for sync
            $table->string('etag', 64)->nullable()->after('deck_modified_at');
            
            // Import type (lost, won, tracking)
            $table->string('import_type', 20)->nullable()->after('etag');
            
            // Indexes
            $table->index('deck_card_id');
            $table->index('deck_board_id');
            $table->index('import_type');
        });

        // 5. Add import_type to import_logs
        Schema::table('import_logs', function (Blueprint $table) {
            $table->string('import_type', 20)->nullable()->after('board_name');
            $table->unsignedInteger('cards_skipped')->default(0)->after('cards_updated');
        });
    }

    public function down(): void
    {
        // Remove columns from cards
        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex(['deck_card_id']);
            $table->dropIndex(['deck_board_id']);
            $table->dropIndex(['import_type']);
            
            $table->dropColumn([
                'deck_card_id',
                'deck_board_id',
                'deck_stack_id',
                'stack_name',
                'owner_uid',
                'owner_displayname',
                'card_order',
                'archived',
                'done',
                'notified',
                'comments_count',
                'deck_created_at',
                'deck_modified_at',
                'etag',
                'import_type',
            ]);
        });

        // Remove columns from import_logs
        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn(['import_type', 'cards_skipped']);
        });

        // Drop tables in reverse order
        Schema::dropIfExists('assigned_users');
        Schema::dropIfExists('stacks');
        Schema::dropIfExists('boards');
    }
};
