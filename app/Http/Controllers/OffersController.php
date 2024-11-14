<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Helper\ControllerHelper;
use App\Models\Helper\FileHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Utils;
use App\Models\Helper\Validation;
use App\Models\Offer;
use App\Models\OfferBrand;
use App\Models\OfferCategory;
use App\Models\OfferGuestUser;
use App\Models\OfferLang;
use App\Models\OfferPayment;
use App\Models\OfferPayments;
use App\Models\OfferProduct;
use App\Models\OfferUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OffersController extends ControllerHelper
{
    public function all(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($can = Utils::userCan($this->user, 'offer.view')) {
                return $can;
            }

            $query = Offer::query();
            $query = $query->orderBy('offers.' . $request->orderby, $request->type);
            $query = $query->with('admin');

            if ($this->isVendor) {
                $query = $query->where('admin_id', $this->user->id);
            }

            if ($lang) {
                $query = $query->leftJoin('offer_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_id', '=', 'offers.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offers.*', 'b.listing_title', 'b.detail_title');

                if ($request->q) {
                    $query = $query->where('b.listing_title', 'LIKE', "%{$request->q}%");
                    $query = $query->where('b.detail_title', 'LIKE', "%{$request->q}%");
                }
            } else {
                if ($request->q) {
                    $query = $query->where('offers.title', 'LIKE', "%{$request->q}%");
                }
            }

            $data = $query->paginate(Config::get('constants.api.PAGINATION'));

            foreach ($data as $item) {
                if ($request->time_zone) {
                    $item['created'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->created_at, $request->time_zone));
                    $item['updated'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->updated_at, $request->time_zone));
                    $item['start_time'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->start_time, $request->time_zone));
                    $item['end_time'] = Utils::formatDate(Utils::convertTimeToUSERzone($item->end_time, $request->time_zone));
                } else {
                    $item['created'] = Utils::formatDate($item->created_at);
                    $item['updated'] = Utils::formatDate($item->updated_at);
                    $item['start_time'] = Utils::formatDate($item->start_time);
                    $item['end_time'] = Utils::formatDate($item->end_time);
                }
            }

            return response()->json(new Response($request->token, $data));
        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }

    public function allOffers(Request $request)
    {
        try {
            $lang = $request->header('language');
            $query = Offer::query();

            if ($lang) {
                $query = $query->leftJoin('offer_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_id', '=', 'offers.id');
                    $join->where('b.lang', $lang);
                });
                if ($request->q) {
                    $query = $query->where('b.title', 'LIKE', "%{$request->q}%");
                }

                $query = $query->select('offers.id', 'b.detail_title', 'b.listing_title');

            } else {
                if ($request->q) {
                    $query = $query->where('offers.listing_title', 'LIKE', "%{$request->q}%");
                    $query = $query->where('offers.detail_title', 'LIKE', "%{$request->q}%");
                }
                $query = $query->select('offers.id', 'offers.title');
            }

            $query = $query->orderBy('offers.created_at');
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

            $query = Offer::query();
            $query = $query->with('vendor');
            $query = $query->with('users.user');
            $query = $query->with('guest_users.guest_user');



            if ($lang) {

                $query = $query->with(['categories' => function ($query) use ($lang) {
                    $query->with(['category' => function ($query) use ($lang) {
                        $query->leftJoin('category_langs as cl',
                            function ($join) use ($lang) {
                                $join->on('categories.id', '=', 'cl.category_id');
                                $join->where('cl.lang', $lang);
                            })
                            ->select('categories.*', 'cl.title');;
                    }]);
                }]);


                $query = $query->with(['categories' => function ($query) use ($lang) {
                    $query->with(['category' => function ($query) use ($lang) {
                        $query->leftJoin('category_langs as cl',
                            function ($join) use ($lang) {
                                $join->on('categories.id', '=', 'cl.category_id');
                                $join->where('cl.lang', $lang);
                            })
                            ->select('categories.*', 'cl.title');;
                    }]);
                }]);

                $query = $query->with(['products' => function ($query) use ($lang) {
                    $query->with(['product' => function ($query) use ($lang) {
                        $query->leftJoin('product_langs as pl',
                            function ($join) use ($lang) {
                                $join->on('products.id', '=', 'pl.product_id');
                                $join->where('pl.lang', $lang);
                            })
                            ->select('products.*', 'pl.title');;
                    }]);
                }]);

                $query = $query->with(['brands' => function ($query) use ($lang) {
                    $query->with(['brand' => function ($query) use ($lang) {
                        $query->leftJoin('brand_langs as bl',
                            function ($join) use ($lang) {
                                $join->on('brands.id', '=', 'bl.brand_id');
                                $join->where('bl.lang', $lang);
                            })
                            ->select('brands.*', 'bl.title');;
                    }]);
                }]);


                $query = $query->with(['offer_schema' => function ($query) use ($lang) {
                    $query->leftJoin('offer_schema_langs as osl',
                        function ($join) use ($lang) {
                            $join->on('offer_schemas.id', '=', 'osl.offer_schema_id');
                            $join->where('osl.lang', $lang);
                        });
                }]);

                $query = $query->with(['shipping_rule' => function ($query) use ($lang) {
                    $query->leftJoin('shipping_rule_langs as pl',
                        function ($join) use ($lang) {
                            $join->on('shipping_rules.id', '=', 'pl.shipping_rule_id');
                            $join->where('pl.lang', $lang);
                        });
                }]);

                $query = $query->leftJoin('offer_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_id', '=', 'offers.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offers.*', 'b.detail_title', 'b.listing_title');
            } else{
                $query = $query->with('brands.brand');
                $query = $query->with('categories.category');
                $query = $query->with('products.product');
                $query = $query->with('shipping_rule');
                $query = $query->with('offer_schema');
            }

            $query = $query->with('payment_methods.payment_method');

            $brand = $query->find($id);


            if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $brand)) {
                return $isOwner;
            }

            if (is_null($brand)) {
                return response()->json(Validation::noDataLang($lang));
            }

            $brand['sample'] = env('APP_URL') . '/uploads/sample/product-list.xlsx';

            return response()->json(new Response($request->token, $brand));


        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


    public function action(Request $request, $id = null)
    {
        try {
            $lang = $request->header('language');
            if($request->start_time || $request->end_time){
                if ($request->time_zone) {
                    $request['start_time'] = Utils::convertTimeToUTCzone($request->start_time, $request->time_zone);
                    $request['end_time'] = Utils::convertTimeToUTCzone($request->end_time, $request->time_zone);
                }
            }
            $filtered = $request->except(['vendor', 'shipping_rule', 'payment', 'time_zone', 'sample',
                'brands', 'users', 'guest_users', 'categories', 'payment_methods',
                'products', 'offer_schema']);
            $filtered['admin_id'] = $request->user()->id;

            if ($id) {
                if ($can = Utils::userCan($this->user, 'offer.edit')) {
                    return $can;
                }

                $existing = Offer::find($id);
                if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $existing)) {
                    return $isOwner;
                }

                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, ['detail_title', 'listing_title']);
                    Offer::where('id', $id)->update($mainData);
                    $existingLang = OfferLang::where('offer_id', $id)->where('lang', $lang)->first();

                    if (!$existingLang) {
                        $langData['offer_id'] = $id;
                        $langData['lang'] = $lang;

                        OfferLang::create($langData);
                    } else {
                        OfferLang::where('id', $existingLang->id)->update($langData);
                    }
                } else {
                    Offer::where('id', $id)->update($filtered);
                }
            } else {
                if ($can = Utils::userCan($this->user, 'offer.create')) {
                    return $can;
                }
                $request['admin_id'] = $request->user()->id;

                if ($lang) {
                    [$langData, $mainData] = Utils::seperateLangData($filtered, ['detail_title', 'listing_title']);
                    $brand = Offer::create($mainData);

                    $langData['offer_id'] = $brand->id;
                    $langData['lang'] = $lang;
                    OfferLang::create($langData);
                    $id = $brand->id;

                } else {
                    $brand = Offer::create($filtered);
                    $id = $brand->id;
                }
            }

            $existingProducts = OfferProduct::where('offer_id', $id)->get();
            $existingProductArr=[];
            foreach ($existingProducts as $item) {
                $existingProductArr[$item->product_id] = $item;
            }

            if(count($request->products) > 0 && gettype($request->products[0]) != 'array'){

                $prod = array_unique($request->products);
                foreach ($prod as $item) {
                    if(key_exists($item, $existingProductArr)) {
                       unset($existingProductArr[$item]);
                    } else {
                        OfferProduct::create(['offer_id' => $id, 'product_id' => $item]);
                    }
                }
                foreach ($existingProductArr as $key=>$value) {
                    OfferProduct::where('id', $value->id)->delete();
                }
            } else if (count($request->products) == 0){
                foreach ($existingProductArr as $key=>$value) {
                    OfferProduct::where('id', $value->id)->delete();
                }
            }


            $existingCategories = OfferCategory::where('offer_id', $id)->get();
            $existingCategoryArr=[];
            foreach ($existingCategories as $item) {
                $existingCategoryArr[$item->category_id] = $item;
            }
            if(count($request->categories) > 0 && gettype($request->categories[0]) != 'array'){

                $cat = array_unique($request->categories);

                foreach ($cat as $item) {
                    if(key_exists($item, $existingCategoryArr)) {
                        unset($existingCategoryArr[$item]);
                    } else {
                        OfferCategory::create(['offer_id' => $id, 'category_id' => $item]);
                    }
                }
                foreach ($existingCategoryArr as $key=>$value) {
                    OfferCategory::where('id', $value->id)->delete();
                }
            } else if (count($request->categories) == 0){
                foreach ($existingCategoryArr as $key=>$value) {
                    OfferCategory::where('id', $value->id)->delete();
                }
            }


            $existingPayments = OfferPayment::where('offer_id', $id)->get();
            $existingPaymentArr = [];
            foreach ($existingPayments as $item) {
                $existingPaymentArr[$item->payment_method_id] = $item;
            }
            if(count($request->payment_methods) > 0 && gettype($request->payment_methods[0]) != 'array'){

                $paymentMethods = array_unique($request->payment_methods);
                foreach ($paymentMethods as $item) {
                    if(key_exists($item, $existingPaymentArr)) {
                        unset($existingPaymentArr[$item]);
                    } else {
                        OfferPayment::create(['offer_id' => $id, 'payment_method_id' => $item]);
                    }
                }

                foreach ($existingPaymentArr as $key=>$value) {
                    OfferPayment::where('id', $value->id)->delete();
                }
            } else if (count($request->payment_methods) == 0){
                foreach ($existingPaymentArr as $key=>$value) {
                    OfferPayment::where('id', $value->id)->delete();
                }
            }




            $existingBrands = OfferBrand::where('offer_id', $id)->get();
            $existingBrandArr=[];
            foreach ($existingBrands as $item) {
                $existingBrandArr[$item->brand_id] = $item;
            }
            if(count($request->brands) > 0 && gettype($request->brands[0]) != 'array'){

                $brands = array_unique($request->brands);
                foreach ($brands as $item) {
                    if(key_exists($item, $existingBrandArr)) {
                        unset($existingBrandArr[$item]);
                    } else {
                        OfferBrand::create(['offer_id' => $id, 'brand_id' => $item]);
                    }
                }

                foreach ($existingBrandArr as $key=>$value) {
                    OfferBrand::where('id', $value->id)->delete();
                }
            } else if (count($request->brands) == 0){
                foreach ($existingBrandArr as $key=>$value) {
                    OfferBrand::where('id', $value->id)->delete();
                }
            }


            $existingUsers = OfferUser::where('offer_id', $id)->get();
            $existingUserArr=[];
            foreach ($existingUsers as $item) {
                $existingUserArr[$item->user_id] = $item;
            }
            if(count($request->users) > 0 && gettype($request->users[0]) != 'array'){

                $users = array_unique($request->users);

                foreach ($users as $item) {
                    if(key_exists($item, $existingUserArr)) {
                        unset($existingUserArr[$item]);
                    } else {
                        OfferUser::create(['offer_id' => $id, 'user_id' => $item]);
                    }
                }

                foreach ($existingUserArr as $key=>$value) {
                    OfferUser::where('id', $value->id)->delete();
                }
            } else if (count($request->users) == 0){
                foreach ($existingUserArr as $key=>$value) {
                    OfferUser::where('id', $value->id)->delete();
                }
            }


            $existingGuestUsers = OfferGuestUser::where('offer_id', $id)->get();
            $existingGuestUserArr=[];
            foreach ($existingGuestUsers as $item) {
                $existingGuestUserArr[$item->id] = $item;
            }
            if(count($request->guest_users) > 0 && gettype($request->guest_users[0]) != 'array'){

                $guestUsers = array_unique($request->guest_users);
                foreach ($guestUsers as $item) {
                    if(key_exists($item, $existingGuestUserArr)) {
                        unset($existingGuestUserArr[$item]);
                    } else {
                        OfferGuestUser::create(['offer_id' => $id, 'guest_user_id' => $item]);
                    }
                }

                foreach ($existingGuestUserArr as $key=>$value) {
                    OfferGuestUser::where('id', $key)->delete();
                }
            } else if (count($request->guest_users) == 0){
                foreach ($existingGuestUserArr as $key=>$value) {
                    OfferGuestUser::where('id', $key)->delete();
                }
            }


            $query = Offer::query();
            $query = $query->with('vendor');
            $query = $query->with('users.user');
            $query = $query->with('guest_users.guest_user');

            if ($lang) {

                $query = $query->with(['categories' => function ($query) use ($lang) {
                    $query->with(['category' => function ($query) use ($lang) {
                        $query->leftJoin('category_langs as cl',
                            function ($join) use ($lang) {
                                $join->on('categories.id', '=', 'cl.category_id');
                                $join->where('cl.lang', $lang);
                            })
                            ->select('categories.*', 'cl.title');;
                    }]);
                }]);

                $query = $query->with(['products' => function ($query) use ($lang) {
                    $query->with(['product' => function ($query) use ($lang) {
                        $query->leftJoin('product_langs as pl',
                            function ($join) use ($lang) {
                                $join->on('products.id', '=', 'pl.product_id');
                                $join->where('pl.lang', $lang);
                            })
                            ->select('products.*', 'pl.title');;
                    }]);
                }]);

                $query = $query->with(['brands' => function ($query) use ($lang) {
                    $query->with(['brand' => function ($query) use ($lang) {
                        $query->leftJoin('brand_langs as bl',
                            function ($join) use ($lang) {
                                $join->on('brands.id', '=', 'bl.brand_id');
                                $join->where('bl.lang', $lang);
                            })
                            ->select('brands.*', 'bl.title');;
                    }]);
                }]);


                $query = $query->with(['offer_schema' => function ($query) use ($lang) {
                    $query->leftJoin('offer_schema_langs as osl',
                        function ($join) use ($lang) {
                            $join->on('offer_schemas.id', '=', 'osl.offer_schema_id');
                            $join->where('osl.lang', $lang);
                        });
                }]);

                $query = $query->with(['shipping_rule' => function ($query) use ($lang) {
                    $query->leftJoin('shipping_rule_langs as pl',
                        function ($join) use ($lang) {
                            $join->on('shipping_rules.id', '=', 'pl.shipping_rule_id');
                            $join->where('pl.lang', $lang);
                        });
                }]);

                $query = $query->leftJoin('offer_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_id', '=', 'offers.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offers.*', 'b.detail_title', 'b.listing_title');
            } else{
                $query = $query->with('brands.brand');
                $query = $query->with('categories.category');
                $query = $query->with('products.product');
                $query = $query->with('shipping_rule');
                $query = $query->with('offer_schema');
            }

            $query = $query->with('payment_methods.payment_method');
            $brand = $query->find($id);

            return response()->json(new Response($request->token, $brand));
        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }



    public function upload(Request $request)
    {
        try {
            $lang = $request->header('language');

            if ($request->hasFile('product_excel')) {
                $file = $request->file('product_excel');
                $id = $request->id;
                $productIds = [];

                // Load the file
                $spreadsheet = IOFactory::load($file->getRealPath());

                // Get the first worksheet
                $worksheet = $spreadsheet->getActiveSheet();

                // Loop through each row in the worksheet
                foreach ($worksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue(); // Get cell value
                    }
                    if(count($rowData) > 0) {
                        if($rowData[0]) {
                            array_push($productIds, $rowData[0]);
                        }
                    }

                    // Process $rowData array as needed
                    // For example, $rowData[0] for first column, $rowData[1] for second column, etc.
                }
            }


            $existingProducts = OfferProduct::where('offer_id', $id)->get();
            $existingProductArr=[];
            foreach ($existingProducts as $item) {
                $existingProductArr[$item->product_id] = $item;
            }



            if(count($productIds) > 0 && gettype($productIds[0]) != 'array'){

                $prod = array_unique($productIds);
                foreach ($prod as $item) {
                    if(key_exists($item, $existingProductArr)) {
                        unset($existingProductArr[$item]);
                    } else {
                        OfferProduct::create(['offer_id' => $id, 'product_id' => $item]);
                    }
                }
            }

            $query = Offer::query();
            $query = $query->with('vendor');
            $query = $query->with('users.user');
            $query = $query->with('guest_users.guest_user');

            if ($lang) {

                $query = $query->with(['categories' => function ($query) use ($lang) {
                    $query->with(['category' => function ($query) use ($lang) {
                        $query->leftJoin('category_langs as cl',
                            function ($join) use ($lang) {
                                $join->on('categories.id', '=', 'cl.category_id');
                                $join->where('cl.lang', $lang);
                            })
                            ->select('categories.*', 'cl.title');;
                    }]);
                }]);

                $query = $query->with(['products' => function ($query) use ($lang) {
                    $query->with(['product' => function ($query) use ($lang) {
                        $query->leftJoin('product_langs as pl',
                            function ($join) use ($lang) {
                                $join->on('products.id', '=', 'pl.product_id');
                                $join->where('pl.lang', $lang);
                            })
                            ->select('products.*', 'pl.title');;
                    }]);
                }]);

                $query = $query->with(['brands' => function ($query) use ($lang) {
                    $query->with(['brand' => function ($query) use ($lang) {
                        $query->leftJoin('brand_langs as bl',
                            function ($join) use ($lang) {
                                $join->on('brands.id', '=', 'bl.brand_id');
                                $join->where('bl.lang', $lang);
                            })
                            ->select('brands.*', 'bl.title');;
                    }]);
                }]);


                $query = $query->with(['offer_schema' => function ($query) use ($lang) {
                    $query->leftJoin('offer_schema_langs as osl',
                        function ($join) use ($lang) {
                            $join->on('offer_schemas.id', '=', 'osl.offer_schema_id');
                            $join->where('osl.lang', $lang);
                        });
                }]);

                $query = $query->with(['shipping_rule' => function ($query) use ($lang) {
                    $query->leftJoin('shipping_rule_langs as pl',
                        function ($join) use ($lang) {
                            $join->on('shipping_rules.id', '=', 'pl.shipping_rule_id');
                            $join->where('pl.lang', $lang);
                        });
                }]);

                $query = $query->leftJoin('offer_langs as b', function ($join) use ($lang) {
                    $join->on('b.offer_id', '=', 'offers.id');
                    $join->where('b.lang', $lang);
                });
                $query = $query->select('offers.*', 'b.detail_title', 'b.listing_title');
            } else{
                $query = $query->with('brands.brand');
                $query = $query->with('categories.category');
                $query = $query->with('products.product');
                $query = $query->with('shipping_rule');
                $query = $query->with('offer_schema');
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

                $item = Offer::find($i);

                if ($this->isVendor && $isOwner = Utils::isDataOwner($this->user, $item)) {
                    return $isOwner;
                }

                if (is_null($item)) {
                    return response()->json(Validation::noDataLang($lang));
                }


                OfferBrand::where('offer_id', $id)->delete();
                OfferCategory::where('offer_id', $id)->delete();
                OfferGuestUser::where('offer_id', $id)->delete();
                OfferUser::where('offer_id', $id)->delete();
                OfferProduct::where('offer_id', $id)->delete();
                OfferLang::where('offer_id', $id)->delete();

                $item->delete();
            }

            return response()->json(new Response($request->token, true));

            // return response()->json(Validation::error($request->token, null, 'form', $lang));

        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


}
