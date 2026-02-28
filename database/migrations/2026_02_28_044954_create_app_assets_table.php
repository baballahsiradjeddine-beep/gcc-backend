<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_assets', function (Blueprint $table) {
            $table->id();
            // Unique key to identify the asset in Flutter (e.g. 'subscribe_banner')
            $table->string('key')->unique();
            // Human-readable label for the dashboard
            $table->string('label');
            // Description shown in the dashboard
            $table->string('description')->nullable();
            // The URL of the uploaded image (via Spatie Media or manual URL)
            $table->string('image_url')->nullable();
            // Version hash – Flutter compares this to decide if re-download is needed
            $table->string('version_hash')->nullable();
            // Whether this asset is active
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_assets');
    }
};
