<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            // Tetap gunakan incident_ticket_id sesuai struktur teman Anda
            $table->foreignId('incident_ticket_id')->constrained('incident_tickets')->onDelete('cascade');
            $table->text('description');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable(); // Tambahan opsional agar tidak error saat Eloquent save
        });
    }

    public function down()
    {
        Schema::dropIfExists('corrective_actions');
    }
};
