<?php

namespace App\Http\Controllers;

use App\Models\Helper\Response;
use App\Models\Helper\Validation;
use App\Models\Offer;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{
    public function allPaymentMethods(Request $request)
    {
        try {
            $lang = $request->header('language');
            $query = PaymentMethod::query();


                if ($request->q) {
                    $query = $query->where('payment_methods.title', 'LIKE', "%{$request->q}%");
                }
                $query = $query->select('payment_methods.id', 'payment_methods.title');


            $query = $query->orderBy('payment_methods.created_at');
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
}
