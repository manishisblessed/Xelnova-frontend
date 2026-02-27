<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix user_type based on roles
        // - Users with a role: user_type = role name
        // - Users without a role: user_type = 'customer'
        
        // Get all users with their roles
        $users = DB::table('users')
            ->leftJoin('model_has_roles', function($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\\Models\\User');
            })
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('users.id', 'users.user_type', 'roles.name as role_name')
            ->get()
            ->groupBy('id');
        
        foreach ($users as $userId => $userRoles) {
            $roleNames = $userRoles->pluck('role_name')->filter()->toArray();
            
            // Determine correct user_type based on role
            $correctUserType = 'customer'; // default for users with no role
            
            if (!empty($roleNames)) {
                // Use the first role name as user_type
                $correctUserType = $roleNames[0];
            }
            
            // Get current user_type
            $currentUserType = $userRoles->first()->user_type;
            
            // Update if different
            if ($currentUserType !== $correctUserType) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update(['user_type' => $correctUserType]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data fix
    }
};
