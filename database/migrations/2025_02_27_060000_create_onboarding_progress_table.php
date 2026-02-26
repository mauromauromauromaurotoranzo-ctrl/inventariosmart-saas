<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('current_step');
            $table->json('completed_steps')->default('[]');
            $table->json('step_data')->default('{}');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
