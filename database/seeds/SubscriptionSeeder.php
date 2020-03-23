<?php

use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
    		[
		        'key' => 'is_subscription',
		        'value' => 1
		    ],
		    [
		        'key' => 'is_spam',
		        'value' => 1
		    ],
		    [
		        'key' => 'is_payper_view',
		        'value' => 1
		    ]
		]);
    }
}
