<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'uuid')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('uuid', 36)->nullable()->unique()->after('remember_token');
            });
        }

        DB::table('users')
            ->select('id')
            ->whereNull('uuid')
            ->orderBy('id')
            ->get()
            ->each(function (object $row): void {
                DB::table('users')
                    ->where('id', $row->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'uuid')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};
