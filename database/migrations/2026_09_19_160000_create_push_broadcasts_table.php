<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_broadcasts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source', 8);                       // api | cli
            $table->string('key_id', 8)->nullable();           // first 8 hex of the API key hash; never the key
            $table->string('title', 65);
            $table->string('body', 240);
            $table->string('url', 200)->default('/');
            $table->string('tag', 64)->nullable();
            $table->string('urgency', 12)->default('normal');
            $table->unsignedInteger('ttl');
            $table->string('audience', 16)->default('all');
            $table->string('status', 12)->default('queued')->index(); // queued | sending | done | cancelled
            $table->unsignedInteger('devices_total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->unsignedInteger('expired_removed')->default(0);
            $table->unsignedInteger('chunks_total')->default(0);
            $table->unsignedInteger('chunks_done')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('created_at'); // the frequency caps count recent rows
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_broadcasts');
    }
};
