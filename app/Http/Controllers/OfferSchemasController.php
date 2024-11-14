<?php

namespace App\Http\Controllers;

use App\Models\Helper\ControllerHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Utils;
use App\Models\Helper\Validation;
use App\Models\Offer;
use App\Models\OfferSchema;
use App\Models\OfferSchemaLang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class OfferSchemasController extends ControllerHelper
{
    public function all(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($can = Utils::userCan($this->user, 'offer.view')) {
                return $can;
            }

            $query = OfferSchema::query();
            $query = $query->orderBy('offer_schemas.' . $request->orderby, $request->type);

            if ($this->isVendor) {
                $query = $query->where('admin_id', $this->user->id);
            }

            if ($lang) {
                $query = $query->leftJoin('offer_schema_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_schema_id', '=', 'offer_schemas.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offer_schemas.*', 'b.title');

                if ($request->q) {
                    $query = $query->where('b.title', 'LIKE', "%{$request->q}%");
                }
            } else {
                if ($request->q) {
                    $query = $query->where('offer_schemas.title', 'LIKE', "%{$request->q}%");
                }
            }

            $data = $query->paginate(Config::get('constants.api.PAGINATION'));

            foreach ($data as $item) {
                if ($request->time_zone) {
                    $item['created'] = Utils::convertTimeToUSERzone($item->created_at, $request->time_zone);
                } else {
                    $item['created'] = Utils::formatDate($item->created_at);
                }
            }

            return response()->json(new Response($request->token, $data));
        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function allOfferSchemas(Request $request)
    {
        try {
            $lang = $request->header('language');
            $query = OfferSchema::query();

            if ($lang) {
                $query = $query->leftJoin('offer_schema_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_schema_id', '=', 'offer_schemas.id');
                    $join->where('b.lang', $lang);
                });
                if ($request->q) {
                    $query = $query->where('b.title', 'LIKE', "%{$request->q}%");
                }

                $query = $query->select('offer_schemas.id', 'b.title');

            } else {
                if ($request->q) {
                    $query = $query->where('offer_schemas.title', 'LIKE', "%{$request->q}%");
                }
                $query = $query->select('offer_schemas.id', 'offer_schemas.title');
            }

            $query = $query->orderBy('offer_schemas.created_at');
            if($request->per_page) {
                $data = $query->paginate($request->per_page);
            } else{
                $data = $query->get();
            }

            return response()->json(new Response($request->token, $data));

        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


    public function find(Request $request, $id)
    {
        try {
            $lang = $request->header('language');


            if ($can = Utils::userCan($this->user, 'offer.view')) {
                return $can;
            }

            $query = OfferSchema::query();


            if ($lang) {


                $query = $query->leftJoin('offer_schema_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_schema_id', '=', 'offer_schemas.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offer_schemas.*', 'b.title');
            }
            $brand = $query->find($id);


            if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $brand)) {
                return $isOwner;
            }

            if (is_null($brand)) {
                return response()->json(Validation::noDataLang($lang));
            }

            return response()->json(new Response($request->token, $brand));


        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


    public function action(Request $request, $id = null)
    {
        try {
            $lang = $request->header('language');

            $filtered = $request->except([]);
            $filtered['admin_id'] = $request->user()->id;

            if ($id) {
                if ($can = Utils::userCan($this->user, 'offer.edit')) {
                    return $can;
                }

                $existing = OfferSchema::find($id);
                if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $existing)) {
                    return $isOwner;
                }

                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, ['title']);
                    OfferSchema::where('id', $id)->update($mainData);
                    $existingLang = OfferSchemaLang::where('offer_schema_id', $id)
                        ->where('lang', $lang)->first();

                    if (!$existingLang) {
                        $langData['offer_schema_id'] = $id;
                        $langData['lang'] = $lang;

                        OfferSchemaLang::create($langData);
                    } else {
                        OfferSchemaLang::where('id', $existingLang->id)->update($langData);
                    }
                } else {
                    OfferSchema::where('id', $id)->update($filtered);
                }
            } else {
                if ($can = Utils::userCan($this->user, 'offer.create')) {
                    return $can;
                }

                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, ['title']);
                    $brand = OfferSchema::create($mainData);

                    $langData['offer_schema_id'] = $brand->id;
                    $langData['lang'] = $lang;
                    OfferSchemaLang::create($langData);
                    $id = $brand->id;

                } else {

                    $brand = OfferSchema::create($filtered);
                    $id = $brand->id;
                }
            }


            $query = OfferSchema::query();

            if ($lang) {

                $query = $query->leftJoin('offer_schema_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_schema_id', '=', 'offer_schemas.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offer_schemas.*', 'b.title');
            }
            $brand = $query->find($id);

            return response()->json(new Response($request->token, $brand));
        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


    public function delete(Request $request, $id)
    {
        try {

            $lang = $request->header('language');

            if ($can = Utils::userCan($this->user, 'offer.delete')) {
                return $can;
            }
            $ids =  explode(",", $id);

            foreach ($ids as $i){

                $item = OfferSchema::find($i);

                if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $item)) {
                    return $isOwner;
                }

                if (is_null($item)) {
                    return response()->json(Validation::noDataLang($lang));
                }

                Offer::where('offer_schema_id', $id)->update(['offer_schema_id' => null]);

                $item->delete();
            }

            return response()->json(new Response($request->token, true));

            // return response()->json(Validation::error($request->token, null, 'form', $lang));

        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }
}
