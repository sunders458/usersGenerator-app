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
        Schema::table('users', function (Blueprint $table) {
            // Rename name to firstName
            $table->renameColumn('name', 'firstName');
            
            // Add new fields
            $table->string('lastName')->after('firstName');
            $table->date('birthDate')->nullable()->after('lastName');
            $table->string('city')->nullable()->after('birthDate');
            $table->string('country', 2)->nullable()->after('city');
            $table->string('avatar')->nullable()->after('country');
            $table->string('company')->nullable()->after('avatar');
            $table->string('jobPosition')->nullable()->after('company');
            $table->string('mobile')->nullable()->after('jobPosition');
            $table->string('username')->unique()->after('mobile');
            $table->enum('role', ['admin', 'user'])->default('user')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Due to SQLite limitations, we need to drop fields one by one
        Schema::table('users', function (Blueprint $table) {
            // Rename firstName back to name
            $table->renameColumn('firstName', 'name');
        });
        
        Schema::table('users', function (Blueprint $table) {
            // Remove other fields
            $table->dropColumn('lastName');
            $table->dropColumn('birthDate');
            $table->dropColumn('city');
            $table->dropColumn('country');
            $table->dropColumn('avatar');
            $table->dropColumn('company');
            $table->dropColumn('jobPosition');
            $table->dropColumn('mobile');
            $table->dropColumn('username');
            $table->dropColumn('role');
        });
    }
};
