<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();

            // Which upstream API delivered the article. external_id is a sha256 of
            // the provider's natural id (often a long URL), hashed so it stays
            // indexable whatever its original length.
            $table->string('provider', 20);
            $table->string('external_id', 64);

            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->string('url', 1000);
            $table->string('image_url', 1000)->nullable();
            $table->timestamp('published_at')->index();

            $table->timestamps();

            // Same article fetched twice should update, not duplicate.
            $table->unique(['provider', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
