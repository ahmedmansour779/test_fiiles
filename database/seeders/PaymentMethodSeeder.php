<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin2 = Admin::where('id', 1)->first();

        if(!PaymentMethod::first() && $admin2){
            foreach (Config::get('constants.paymentMethodIn') as $key => $value) {
                PaymentMethod::create([
                    'id' => $key,
                    'title' => $value,
                ]);
            }
        }
    }
}
