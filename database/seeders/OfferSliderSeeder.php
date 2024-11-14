<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\HomeSlider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class OfferSliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $offerSliders = [
            [
                'type' => Config::get('constants.homeSlider.MAIN'),
                'image' => 'slider-3.webp',
                'title' => 'Winter sale',
                'source_type' => Config::get('constants.sliderSourceType.CATEGORY'),
                'status' => Config::get('constants.status.PUBLIC'),
                'slider_type' => Config::get('constants.sliderType.OFFER'),
                'admin_id' => 1
            ],
            [
                'type' => Config::get('constants.homeSlider.MAIN'),
                'image' => 'slider-2.webp',
                'title' => 'Flash 50 % off',
                'source_type' => Config::get('constants.sliderSourceType.CATEGORY'),
                'status' => Config::get('constants.status.PUBLIC'),
                'slider_type' => Config::get('constants.sliderType.OFFER'),
                'admin_id' => 1
            ],
            [
                'type' => Config::get('constants.homeSlider.RIGHT_TOP'),
                'image' => 'slider-4.webp',
                'title' => 'Backpack for Men',
                'source_type' => Config::get('constants.sliderSourceType.CATEGORY'),
                'status' => Config::get('constants.status.PUBLIC'),
                'slider_type' => Config::get('constants.sliderType.OFFER'),
                'admin_id' => 1
            ],
            [
                'type' => Config::get('constants.homeSlider.RIGHT_BOTTOM'),
                'image' => 'slider-5.webp',
                'title' => 'Puma Stylist Shoes',
                'source_type' => Config::get('constants.sliderSourceType.BRAND'),
                'status' => Config::get('constants.status.PUBLIC'),
                'slider_type' => Config::get('constants.sliderType.OFFER'),
                'admin_id' => 1
            ]
        ];


        $admin1 = Admin::where('id', 1)->first();

        $offerSlider = HomeSlider::where('slider_type', Config::get('constants.sliderType.OFFER'))
            ->first();

        if(!$offerSlider && $admin1){
            foreach ($offerSliders as $i) {
                HomeSlider::create($i);
            }
        }

    }
}
