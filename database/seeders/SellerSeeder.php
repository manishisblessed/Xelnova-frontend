<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        // Create test sellers with different statuses
        $sellers = [
            [
                'business_name' => 'Tech Solutions Pvt Ltd',
                'business_type' => 'company',
                'business_registration_number' => 'CIN123456789',
                'business_address' => '123 MG Road, Bangalore',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'postal_code' => '560001',
                'phone' => '9876543210',
                'email' => 'tech@solutions.com',
                'gst_number' => '29ABCDE1234F1Z5',
                'pan_number' => 'ABCDE1234F',
                'status' => 'pending',
                'commission_rate' => 10.00,
            ],
            [
                'business_name' => 'Fashion Hub',
                'business_type' => 'individual',
                'business_address' => '456 Park Street, Mumbai',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
                'phone' => '9876543211',
                'email' => 'contact@fashionhub.com',
                'gst_number' => '27XYZAB5678G2H6',
                'pan_number' => 'XYZAB5678G',
                'status' => 'approved',
                'verification_status' => 'verified',
                'approved_at' => now()->subDays(5),
                'approved_by' => 1, // Assuming super admin
                'commission_rate' => 8.50,
            ],
            [
                'business_name' => 'Home Decor Emporium',
                'business_type' => 'partnership',
                'business_registration_number' => 'PART987654321',
                'business_address' => '789 Ring Road, Delhi',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'postal_code' => '110001',
                'phone' => '9876543212',
                'email' => 'info@homedecor.com',
                'gst_number' => '07PQRST9012K3L4',
                'pan_number' => 'PQRST9012K',
                'status' => 'suspended',
                'rejection_reason' => 'Multiple customer complaints regarding product quality',
                'commission_rate' => 12.00,
            ],
            [
                'business_name' => 'Electronics World',
                'business_type' => 'company',
                'business_registration_number' => 'CIN987654321',
                'business_address' => '321 Tech Park, Pune',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'postal_code' => '411001',
                'phone' => '9876543213',
                'email' => 'sales@electronicsworld.com',
                'gst_number' => '27MNOPQ3456R7S8',
                'pan_number' => 'MNOPQ3456R',
                'status' => 'approved',
                'verification_status' => 'verified',
                'approved_at' => now()->subDays(15),
                'approved_by' => 1,
                'commission_rate' => 9.00,
            ],
            [
                'business_name' => 'Organic Foods Co',
                'business_type' => 'individual',
                'business_address' => '654 Green Avenue, Chennai',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'postal_code' => '600001',
                'phone' => '9876543214',
                'email' => 'hello@organicfoods.com',
                'status' => 'pending',
                'commission_rate' => 10.00,
            ],
        ];

        foreach ($sellers as $sellerData) {
            // Create a user for each seller
            $user = User::firstOrCreate(
                ['email' => $sellerData['email']],
                [
                    'name' => $sellerData['business_name'],
                    'password' => bcrypt('password'),
                ]
            );

            // Assign seller role if it exists
            if ($user->roles()->count() === 0) {
                $sellerRole = \Spatie\Permission\Models\Role::where('name', 'seller')->first();
                if ($sellerRole) {
                    $user->assignRole($sellerRole);
                }
            }

            // Create seller
            Seller::firstOrCreate(
                ['email' => $sellerData['email']],
                array_merge($sellerData, ['user_id' => $user->id])
            );
        }
    }
}
