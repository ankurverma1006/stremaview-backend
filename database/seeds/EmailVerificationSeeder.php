<?php

use Illuminate\Database\Seeder;

class EmailVerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            'key' => "email_verify_control",
            'value' => 0,
        ]);
    }
}
