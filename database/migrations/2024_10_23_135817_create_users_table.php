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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique()->nullable();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('avatar_path')->default('/avatars/default.jpg');
            $table->boolean('signed_waiver')->default(0);
            $table->string('last_location')->nullable();
            $table->string('signature')->nullable();
            $table->string('default_location')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('preferred_position')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->bigInteger('tenant_id');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
