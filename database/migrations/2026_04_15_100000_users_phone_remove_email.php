<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['email']);
            });
        }

        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 32)->nullable()->after('name');
            });
        }

        $select = ['id', 'phone'];
        if (Schema::hasColumn('users', 'email')) {
            $select[] = 'email';
        }

        foreach (DB::table('users')->select($select)->orderBy('id')->cursor() as $row) {
            $current = (string) ($row->phone ?? '');
            if ($current !== '' && ctype_digit($current)) {
                continue;
            }

            $candidate = '';
            if (property_exists($row, 'email') && $row->email) {
                $local = explode('@', (string) $row->email)[0] ?? '';
                $candidate = preg_replace('/\D+/', '', $local) ?? '';
            }

            if (strlen($candidate) < 6) {
                $candidate = '9'.str_pad((string) $row->id, 8, '0', STR_PAD_LEFT);
            }

            DB::table('users')->where('id', $row->id)->update(['phone' => $candidate]);
        }

        if (Schema::hasColumn('users', 'email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['email', 'email_verified_at']);
            });
        }

        if (! Schema::hasIndex('users', ['phone'], 'unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('phone');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('users', ['phone'], 'unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['phone']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
