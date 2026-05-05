<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->string('category');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->text('summary')->nullable();
            $table->string('suggested_next_action')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('due_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'category', 'priority']);
            $table->index(['is_escalated', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
