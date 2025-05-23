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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recurrent_task_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable();
            $table->integer('level')->default(0);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->boolean('finished')->default(false);
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
