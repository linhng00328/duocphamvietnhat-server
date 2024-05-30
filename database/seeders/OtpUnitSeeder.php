<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OtpUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('otp_units')->insert([
            'store_id' => 359,
            'sender' => 'IKI TECH',
            'token' => 'sourDP3FvL96maW7uk_7HbT-oDWsCu15',
            'content' => '[IKI TECH] Ma xac thuc cua ban tai DoApp la ',
            'image_url' => 'IKI TECH',
            'is_default' => true,
            'is_use' => true,
        ]);
    }
}
