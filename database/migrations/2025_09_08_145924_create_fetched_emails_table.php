<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('fetched_email_overviews', function (Blueprint $table) {
            $table->id();
            $table->string('folder', 100);                           // IMAP folder to scan
            $table->bigInteger('uid');                               // IMAP UID (for incremental sync)
            $table->string('message_id');                            // RFC message-id
            $table->string('message_id_hashed', 64);       // Deduplication 
            $table->string('subject')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('priority')->nullable();
            $table->integer('size')->nullable();
            $table->unsignedTinyInteger('seen')->default(0);
            $table->unsignedTinyInteger('answered')->default(0);
            $table->unsignedTinyInteger('flagged')->default(0);
            $table->timestamp('date')->nullable()->index();           // Sent date
            $table->timestamp('received_date')->nullable();           // Received date
            $table->string('thread_id')->nullable();                  // conversation/thread ID
            $table->string('in_reply_to')->nullable();
            $table->unsignedTinyInteger('have_attachments')->default(0);
            $table->timestamps();

            $table->unique(['folder', 'uid'], 'unq_folder_uid');
            $table->unique(['folder', 'message_id_hashed'], 'unq_folder_message_id');

            $table->index('sender_email', 'idx_sender_email');
            $table->index('thread_id', 'idx_thread_id');
        });

        Schema::create('fetched_email_bodies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('fetched_email_overviews')->onDelete('cascade');

            $table->longText('body_text')->nullable();
            $table->longText('body_html')->nullable();
            $table->longText('raw_body')->nullable();                 // raw MIME body (fallback)
            $table->json('flags')->nullable();                        // extra flags
            $table->json('headers')->nullable();                      // raw headers
            $table->timestamps();
        });

        Schema::create('fetched_email_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('fetched_email_overviews')->onDelete('cascade');

            $table->string('type', 20);
            $table->string('name')->nullable();
            $table->string('email');
            $table->timestamps();

            $table->index('email', 'idx_email');
        });

        Schema::create('fetched_email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('fetched_email_overviews')->onDelete('cascade');

            $table->string('email');
            $table->string('name', 1024);
            $table->string('name_uuid', 64);
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->enum('disposition', ['inline', 'attachment'])->default('attachment');
            $table->unsignedTinyInteger('inline')->default(0);       // quick flag
            $table->string('content_id')->nullable();
            $table->string('checksum', 64)->nullable()->index();     // hash for deduplication
            $table->string('storage_disk',50);
            $table->string('storage_path',1024);
            $table->timestamps();
            $table->index('email', 'idx_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fetched_email_attachments');
        Schema::dropIfExists('fetched_email_addresses');
        Schema::dropIfExists('fetched_email_bodies');
        Schema::dropIfExists('fetched_email_overviews');
    }
};
