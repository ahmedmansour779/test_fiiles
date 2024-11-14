<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class AddOfferBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $admin1 = Admin::where('id', 1)->first();

        $offerBanner = [
            [
                'id' => 10,
                'type' => Config::get('constants.banner.BANNER_10'),
                'image' => 'banner-6.webp',
                'title' => 'Offer',
                'slug' => 'offer',
                'source_type' => Config::get('constants.sliderSourceType.BRAND'),
                'status' => Config::get('constants.status.PUBLIC'),
                'closable' => Config::get('constants.status.PRIVATE'),
                'admin_id' => 1
            ]
        ];


        if($admin1){
            foreach ($offerBanner as $i) {
                $banner = Banner::where('slug', $i['slug'])->first();
                if(!$banner) {
                    Banner::create($i);
                }
            }
        }
    }
}
