<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\HeaderLink;
use App\Models\HeaderLinkLang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class AddHeaderLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [
            [
                'id' => 9,
                'title' => 'OFFERS',
                'url' => '/offers',
                'type' => Config::get('constants.headerLinkType.LEFT'),
                'admin_id' => 1
            ],

        ];


        $offerHeaderLink = HeaderLink::where('id', 9)->first();

        $admin1 = Admin::where('id', 1)->first();

        if(!$offerHeaderLink && HeaderLink::first() && $admin1){
            foreach ($items as $i) {
                HeaderLink::create($i);
            }
        }


        $langItems = [
            [
                'header_link_id' => 9,
                'title' => 'TEKLİFLER',
                'lang' => 'tr'
            ],
            [
                'header_link_id' => 9,
                'title' => "العروض",
                'lang' => 'ar'
            ],
            [
                'header_link_id' => 9,
                'title' => 'ऑफ़र',
                'lang' => 'hi'
            ],
            [
                'header_link_id' => 9,
                'title' => 'OFFRES',
                'lang' => 'fr'
            ],
        ];


        $item9 = HeaderLink::where('id', 9)->first();
        $offerHeaderLinkLang = HeaderLinkLang::where('id', 9)->where('lang', 'tr')->first();


        if(!$offerHeaderLinkLang && HeaderLinkLang::first() && $item9){
            foreach ($langItems as $i) {
                HeaderLinkLang::create($i);
            }
        }
    }
}
