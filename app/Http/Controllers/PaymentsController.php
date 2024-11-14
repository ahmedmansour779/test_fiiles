<?php

namespace App\Http\Controllers;

use App\Models\FooterImageLink;
use App\Models\Helper\ControllerHelper;
use App\Models\Helper\FileHelper;
use App\Models\Helper\Response;
use App\Models\Helper\Utils;
use App\Models\Helper\Validation;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PaymentsController extends ControllerHelper
{


    public function allPayments(Request $request)
    {
        try {
            $lang = $request->header('language');
            $query = Payment::query();

            if ($lang) {
                $query = $query->leftJoin('brand_langs as b', function ($join) use ($lang) {
                    $join->on('b.brand_id', '=', 'brands.id');
                    $join->where('b.lang', $lang);
                });
                if ($request->q) {
                    $query = $query->where('b.title', 'LIKE', "%{$request->q}%");
                }

                $query = $query->select('brands.id', 'b.title');

            } else {
                if ($request->q) {
                    $query = $query->where('brands.title', 'LIKE', "%{$request->q}%");
                }
                $query = $query->select('brands.id', 'brands.title');
            }

            $query = $query->orderBy('brands.created_at');
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

    public static function paginate($items, $perPage, $page) {
        $offset = ($page - 1) * $perPage;
        return array_slice($items, $offset, $perPage, true);
    }

// Function to search the array
    public static function search($items, $searchQuery) {
        return array_filter($items, function ($item) use ($searchQuery) {
            return stripos($item, $searchQuery) !== false;
        });
    }
    public function save(Request $request)
    {
        try{
            if($can = Utils::userCan($this->user, 'setting.edit')){
                return $can;
            }

            $validate = Validation::payment($request);
            if($validate){
                return response()->json($validate);
            }

            $request['created_at'] =  null;
            $request['updated_at'] =  null;

            $data = Payment::first();

            if(is_null($data)){
                $request['admin_id'] = $this->user->id;

                Payment::create($request->all());
            }else{
                Payment::where('id', $data->id)->update($request->all());
            }

            return response()->json(new Response($request->token, $request->all()));

        } catch (\Exception $ex) {
            return response()->json(Validation::error($request->token, $ex->getMessage()));
        }
    }


    public function find(Request $request)
    {
        $payment = Payment::first();

        return response()->json(new Response($request->token, $payment));
    }
}
