<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('site_settings')->count() > 1) {
            throw new RuntimeException('Mais de uma configuração existe; conciliação manual autorizada é necessária.');
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('singleton_guard')->virtualAs('1')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropUnique(['singleton_guard']);
            $table->dropColumn('singleton_guard');
        });
    }
};
