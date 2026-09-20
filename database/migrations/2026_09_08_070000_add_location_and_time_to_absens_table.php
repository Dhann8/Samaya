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
        Schema::table('absens', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('status');
            $table->string('longitude')->nullable()->after('latitude');
            $table->text('lokasi')->nullable()->after('longitude');
            $table->time('waktu_absen')->nullable()->after('lokasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absens', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'lokasi', 'waktu_absen']);
        });
    }
};
