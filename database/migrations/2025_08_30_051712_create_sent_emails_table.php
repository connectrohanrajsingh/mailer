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
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();

            $table->string('message_id')->nullable()->index();
            $table->string('from_email', 255);
            $table->string('from_name', 255)->nullable();
            $table->string('subject', 800)->nullable();

            $table->json('to_emails');
            $table->json('cc_emails')->nullable();
            $table->json('bcc_emails')->nullable();
            $table->json('reply_to')->nullable();

            $table->longText('text_body')->nullable();

            $table->integer('attachments_count')->default(0);
            $table->integer('retry_count')->default(0);

            $table->enum('status', ['QUEUED', 'SENT', 'FAILED'])->default('QUEUED')->index();

            $table->string('remark', 1200)->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sent_email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sent_email_id')->constrained('sent_emails')->onDelete('cascade');

            $table->string('email', 255);
            $table->string('name');
            $table->string('name_uuid', 64);
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->string('checksum', 64)->nullable()->index();
            $table->string('storage_disk', 50);
            $table->string('storage_path', 1024);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_email_attachments');
        Schema::dropIfExists('sent_emails');
    }
};
