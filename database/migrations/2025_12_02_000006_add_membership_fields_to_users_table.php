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
            $table->boolean('is_member')->default(false)->after('loyalty_points');
            $table->string('member_status')->nullable()->after('is_member'); // active, inactive, frozen, cancelled
            $table->foreignId('current_membership_id')->nullable()->after('member_status')->constrained('memberships')->onDelete('set null');
            $table->timestamp('member_since')->nullable()->after('current_membership_id');
            $table->timestamp('membership_expires_at')->nullable()->after('member_since');
            
            $table->index('is_member');
            $table->index('member_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_membership_id']);
            $table->dropIndex(['is_member']);
            $table->dropIndex(['member_status']);
            $table->dropColumn([
                'is_member',
                'member_status',
                'current_membership_id',
                'member_since',
                'membership_expires_at',
            ]);
        });
    }
};
