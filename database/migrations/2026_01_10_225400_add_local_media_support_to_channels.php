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
        Schema::table('channels', function (Blueprint $table) {
            // Add media_type column to distinguish between URL and local file
            $table->enum('media_type', ['url', 'local_file'])
                ->default('url')
                ->after('url_custom');
            
            // Add local_file_path column to store the path to local media files
            $table->text('local_file_path')
                ->nullable()
                ->after('media_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'local_file_path']);
        });
    }
};
