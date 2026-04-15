<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fetched_email_trackers', function (Blueprint $table) {
            $table->id();
            $table->date('fetch_date');
            $table->string('folder', 100);
            $table->integer('total_emails')->default(0);
            $table->integer('processed_emails')->default(0);
            $table->integer('total_page')->default(0);
            $table->integer('processed_pages')->default(0);
            $table->bigInteger('start_uid')->nullable();
            $table->bigInteger('last_uid')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED'])->default('PENDING');
            $table->timestamps();

            $table->unique(['folder', 'fetch_date']);

            $table->index(['folder', 'fetch_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fetched_email_trackers');
    }
};
