<?php

namespace App\Http\Controllers;

use App\Models\Helper\ControllerHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Utils;
use App\Models\Helper\Validation;
use App\Models\OfferMeta;
use App\Models\OfferMetaLang;
use Illuminate\Http\Request;

class OfferMetasController extends ControllerHelper
{
    public function find(Request $request)
    {
        try {

            $lang = $request->header('language');

            $query = OfferMeta::query();
            if ($lang) {
                $query = $query->leftJoin('offer_meta_langs as cl', function ($join) use ($lang) {
                    $join->on('cl.offer_meta_id', '=', 'offer_metas.id');
                    $join->where('cl.lang', $lang);
                });
                $query = $query->select('offer_metas.*', 'cl.meta_title', 'cl.meta_description',
                    'cl.meta_keywords');
            }
            $query = $query->where('offer_metas.admin_id', $this->user->id);
            $data = $query->first();


            return response()->json(new Response($request->token, $data));


        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function action(Request $request)
    {
        try {

            $lang = $request->header('language');


            $data = OfferMeta::where('admin_id', $this->user->id)->first();



            $request['created_at'] = $request['updated_at'] = '';

            $filtered = array_filter($request->all(), function ($element) {
                return '' !== trim($element);
            });

            if ($data) {


                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, [
                       'meta_title', 'meta_description', 'meta_keywords'
                    ]);
                    OfferMeta::where('admin_id', $this->user->id)->update($mainData);


                    $existingLang = OfferMetaLang::where('offer_meta_id', $data->id)
                        ->where('lang', $lang)->first();

                    if (!$existingLang) {
                        $langData['offer_meta_id'] = $request->id;
                        $langData['lang'] = $lang;
                        OfferMetaLang::create($langData);

                    } else {
                        OfferMetaLang::where('id', $existingLang->id)->update($langData);
                    }
                } else {

                    OfferMeta::where('admin_id', $this->user->id)->update($filtered);
                }

            } else {


                $filtered['admin_id'] = $this->user->id;


                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, [
                         'meta_title', 'meta_description', 'meta_keywords'
                    ]);
                    $siteSetting = OfferMeta::create($mainData);

                    $langData['offer_meta_id'] = $siteSetting->id;
                    $langData['lang'] = $lang;
                    OfferMetaLang::create($langData);

                } else {
                    OfferMeta::create($filtered);

                }
            }

            $query = OfferMeta::query();
            if ($lang) {
                $query = $query->leftJoin('offer_meta_langs as cl', function ($join) use ($lang) {
                    $join->on('cl.offer_meta_id', '=', 'offer_metas.id');
                    $join->where('cl.lang', $lang);
                });
                $query = $query->select('offer_metas.*', 'cl.meta_title',
                    'cl.meta_description', 'cl.meta_keywords'
                );
            }
            $query = $query->where('admin_id', $this->user->id);
            $data = $query->first();

            return response()->json(new Response($request->token, $data));


        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

}
