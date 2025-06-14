<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('operators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('telegram_id')->unique();
            $table->enum('status', ['available', 'at_lunch', 'returned'])->default('available');
            $table->integer('lunch_order')->nullable();
            $table->date('last_lunch_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operators');
    }
};
