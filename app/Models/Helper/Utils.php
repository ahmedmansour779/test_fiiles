<?php

namespace App\Models\Helper;
use App\Models\GuestUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Utils
{

    public static function offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang){
         return $query->with(['offer' => function ($query) use ($userId, $guestUserId, $now, $lang) {

            $query = self::userOfferQuery($userId, $guestUserId, $query);

            $query->leftJoin('offer_langs as ol', function ($join) use ($lang) {
                $join->on('offers.id', '=', 'ol.offer_id');
                $join->where('ol.lang', $lang);
            })
                ->select('offers.*', 'ol.detail_title', 'ol.listing_title')
                ->with(['offer_schema'])
                ->where('offers.status', true)
                ->where('offers.start_time', '<=', $now)
                ->where('offers.end_time', '>=', $now);
        }]);
    }

    public static function offerCheckQuery($userId, $guestUserId, $query, $now){
        return  $query->with(['offer' => function ($query) use ($userId, $guestUserId, $now) {
            $query = self::userOfferQuery($userId, $guestUserId, $query);

            $query->with(['offer_schema'])
                ->select('offers.*')
                ->where('offers.status', true)
                ->where('offers.start_time', '<=', $now)
                ->where('offers.end_time', '>=', $now);
        }]);
    }


  public static function userOfferQuery($userId, $guestUserId, $query){
      return $query->leftJoin('offer_users', 'offers.id', '=', 'offer_users.offer_id')
          ->leftJoin('offer_guest_users', 'offers.id', '=', 'offer_guest_users.offer_id')
          ->where(function ($query) use ($userId, $guestUserId) {
              $query->where(function ($q) use ($userId, $guestUserId) {
                  // If the offer is associated with a specific registered user, ensure the user_id matches
                  $q->whereNotNull('offer_users.user_id')
                      ->where('offer_users.user_id', $userId);
              })
                  ->orWhere(function ($q) use ($guestUserId) {
                      // If the offer is associated with a specific guest user, check for their association
                      $q->whereNotNull('offer_guest_users.guest_user_id')
                          ->where('offer_guest_users.guest_user_id', $guestUserId); // Assuming guest users also have user_id field
                  })
                  ->orWhere(function ($q) {
                      // If the offer is not associated with any user, show to all users
                      $q->whereNull('offer_users.user_id')
                          ->whereNull('offer_guest_users.guest_user_id');
                  });
          });
  }

    public static function offerQuery($request, $query){
        $userId = null;
        $guestUserId = null;
        if ($request->user('user')) {
            $userId = $request->user('user')->id;
        } else if ($request->user_token) {
            $guestUser = GuestUser::where('user_token', $request->user_token)->first();
            if($guestUser){
                $guestUserId = $guestUser->id;
            }
        }


        $now = date('Y-m-d H:i:s');

        $query->with(['offer_admin' => function ($query) use ($userId, $guestUserId, $now) {
            self::offerCheckQuery($userId, $guestUserId, $query, $now);
        }]);

        $query->with(['shipping_rule' => function ($query)use ($userId, $guestUserId, $now) {
            self::offerCheckQuery($userId, $guestUserId, $query, $now);
        }]);

        $query->with(['product_categories' => function ($query) use ($userId, $guestUserId,$now) {
            $query->with(['category' => function ($query) use ($userId, $guestUserId,$now){
                $query->with(['offer_category' => function ($query) use ($userId, $guestUserId,$now) {
                    self::offerCheckQuery($userId, $guestUserId, $query, $now);
                }]);
                $query->select('categories.id');
            }]);
        }]);

        $query->with(['brand' => function ($query) use ($userId, $guestUserId,$now){
            $query->with(['offer_brand' => function ($query) use ($userId, $guestUserId, $now){
                self::offerCheckQuery($userId, $guestUserId, $query, $now);
            }]);
        }]);

        $query->with(['offer_product' => function ($query) use ($userId, $guestUserId, $now){
            self::offerCheckQuery($userId, $guestUserId, $query, $now);
        }]);

        return $query;
    }


    public static function offerQueryLang($request,  $query, $lang){
        $userId = null;
        $guestUserId = null;
        if ($request->user('user')) {
            $userId = $request->user('user')->id;
        } else if ($request->user_token) {
            $guestUser = GuestUser::where('user_token', $request->user_token)->first();
            if($guestUser){
                $guestUserId = $guestUser->id;
            }
        }
        $now = date('Y-m-d H:i:s');

        $query->with(['product_categories' => function ($query) use ($userId, $guestUserId, $now, $lang) {
            $query->with(['category' => function ($query) use ($userId, $guestUserId, $now, $lang) {
                $query->with(['offer_category' => function ($query) use ($userId, $guestUserId, $now, $lang) {
                    self::offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang);
                }]);
                $query->select('categories.id');
            }]);
        }]);

        $query->with(['brand' => function ($query) use ($userId, $guestUserId, $now, $lang) {
            $query->with(['offer_brand' => function ($query) use ($userId, $guestUserId, $now, $lang) {
                self::offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang);
            }]);
        }]);


        $query->with(['offer_admin' => function ($query) use ($userId, $guestUserId, $now, $lang) {
            self::offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang);
        }]);


        $query->with(['shipping_rule' => function ($query) use ($userId, $guestUserId, $now, $lang) {
            self::offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang);
        }]);

        $query->with(['offer_product' => function ($query) use ($userId, $guestUserId, $now, $lang) {
            self::offerCheckLangQuery($userId, $guestUserId, $query, $now, $lang);
        }]);

        return $query;
    }

    public static function discountedPrice($qty, $price, $offer) {
        $offerSchemaPrice = 0;

        if(!$offer || !isset($offer['offer_schema'])){
            return 0;
        }
        $offerSchema = $offer['offer_schema'];

        if (isset($offerSchema['last_product_quantity']) && $offerSchema['last_product_quantity'] > 0) {
             $tempQty = $qty;
            $discountedPrice = 0;
            $maxPrice = floatval($offer['total_value_limit']);

            $minQuantity  = floatval($offerSchema['first_product_quantity']);
            $maxDiscountedItems = floatval($offerSchema['last_product_quantity']);

              while($tempQty >= $minQuantity) {
                  $discounted = 0;
                  if(($tempQty - $minQuantity) >= $maxDiscountedItems) {
                    $discounted = $maxDiscountedItems;
                  } else if($tempQty - $minQuantity > 0)  {
                    $discounted = $tempQty - $minQuantity;
                  }
                  $discountedPrice += ($discounted * $price) * ($offerSchema['discount'] / 100);
                  $tempQty -= ($maxDiscountedItems + $minQuantity);
              }

            if(floatval($discountedPrice) > $maxPrice  && $maxPrice > 0) {
                $offerSchemaPrice += $maxPrice;
              } else {
                $offerSchemaPrice += floatval($discountedPrice);
              }
        } else {
            // When last_product_quantity is 0 or not set
            if ($qty >= $offerSchema['first_product_quantity']) {

                // Calculate offer schema value
                $os = ($qty * $price) * ($offerSchema['discount'] / 100);

                // Compare with total value limit
                if (floatval($os) > floatval($offer['total_value_limit']) && floatval($offer['total_value_limit']) > 0) {
                    $offerSchemaPrice += floatval($offer['total_value_limit']);
                } else {
                    $offerSchemaPrice += floatval($os);
                }
            }
        }

        return $offerSchemaPrice;
    }


    public static function getCurrentOffer($product) {
        // Check if product has offer_product and offer
        if (isset($product['offer_product']['offer'])) {
            return $product['offer_product']['offer'];
        }

        // Check if product's category offer is available via getOfferCategory
        $offerCategory = self::getOfferCategory($product);
        if (isset($offerCategory['offer_category']['offer'])) {
            return $offerCategory['offer_category']['offer'];
        }

        // Check if product has brand offer via getBrandData
        $brandData = $product->brand;
        if (isset($brandData['offer_brand']['offer'])) {
            return $brandData['offer_brand']['offer'];
        }

        // Finally, check if product has a shipping rule offer
        if (isset($product['shipping_rule']['offer'])) {
            return $product['shipping_rule']['offer'];
        }

        if (isset($product['offer_admin']['offer'])) {
            return $product['offer_admin']['offer'];
        }

        // If no offers are found, return null
        return null;
    }

    public static function getOfferCategory($product) {
        if (!isset($product['product_categories'])) {
            return null;
        }

        foreach ($product['product_categories'] as $categoryItem) {
            if (isset($categoryItem['category']) && isset($categoryItem['category']['offer_category'])) {
                return $categoryItem['category'];
            }
        }

        return null;
    }



    public static function isUploadable($url) {
        return self::isImageUrl($url) && config('env.media.STORAGE') != config('env.media.URL');
    }

    public static function isImageUrl($url) {
        // A regular expression pattern to identify if it's a URL with common image file extensions
        $pattern = "/^https?:\/\/.*\.(jpeg|jpg|png|gif|webp|bmp|tif|svg)$/i";

        // Additional pattern to catch URLs ending with "-jpg", "-jpeg", etc.
        $pattern_with_dash = "/^https?:\/\/.*\-(jpeg|jpg|png|gif|webp|bmp|tif|svg)$/i";

        // Checks if the URL matches either of the patterns
        return preg_match($pattern, trim($url)) === 1 || preg_match($pattern_with_dash, trim($url)) === 1;
    }

    public static function copyImageFromUrl($url, $prefix = null) {

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Set User-Agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537');

        // Disable SSL Verification (for testing purposes only)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);


        // Execute cURL session and get the content
        $image_content = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            // Handle error, e.g., print error message
            echo 'Curl error: ' . curl_error($ch);
            return false;
        }

        // Close cURL session
        curl_close($ch);

        if ($image_content !== false) {
            // Save to a temporary file
            $tmpfname = tempnam(sys_get_temp_dir(), 'downloaded_image');
            file_put_contents($tmpfname, $image_content);


            // Create an UploadedFile instance, which has the getOriginalClientExtension() method
            $file = new UploadedFile($tmpfname, basename($url), mime_content_type($tmpfname), 0, true, true);

            $uploaded_image = [];

            // Now you can use this $file with your uploadImage method
            $uploaded_image = FileHelper::uploadImage($file, $prefix);

            // Don’t forget to delete the temporary file after you are done
            unlink($tmpfname);
            unset($file);

            // Check if the image was uploaded successfully
            if ($uploaded_image['name']) {
                // echo 'Image uploaded successfully!';
                return $uploaded_image['name'];
            } else {
                // echo 'Failed to upload the image.';
                return false;
            }
        } else {
            echo 'Failed to fetch the image from the URL.';
            return false;
        }
    }


    public static function findCommonElements($arrays) {
        if (count($arrays) === 0) {
            return [];
        }

        // Use the first array as the base for comparison
        $baseArray = $arrays[0];

        // Find the common elements using array_intersect
        $commonElements = array_intersect($baseArray, ...$arrays);

        return $commonElements;
    }



    public static function startsWith($string, $startString) {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }


    public static function scanDir($dir) {
        $ignored = array('.', '..', '.svn', '.htaccess');

        $files = array();
        foreach (scandir($dir) as $file) {
            if (in_array($file, $ignored)) continue;
            $files[$file] = filemtime($dir . '/' . $file);
        }

        arsort($files);
        $files = array_keys($files);

        return $files;
    }


    public static function getRequest($url, $request){
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_TIMEOUT => 20,

            // CURLOPT_SSL_VERIFYHOST => 0,
            // CURLOPT_SSL_VERIFYPEER => 0,

            CURLOPT_HTTPHEADER => array(
                "Base: {$request->url('/')}",
                //"Base: https://admin.ishop.com",//
            )
        ));

        $response = @curl_exec($ch);



        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch) > 0) {
            throw new \Exception(__('lang.failed') . ": " . curl_error($ch));
        }

        if ($responseCode == 404) {

            $body = @json_decode($response);


            throw new \Exception($body->data->form[0]);

        } else if ($responseCode !== 200) {

            throw new \Exception( __('lang.error', ['status' => $responseCode]));
        }

        $body = @json_decode($response);

        if ($body === false && json_last_error() !== JSON_ERROR_NONE) {

            throw new \Exception( __('lang.error_parse'));
        }

        return $body;

    }


    public static function seperateLangData($data, $langFields){
        $langData = [];
        $mainData = [];

        foreach ($data as $key => $val) {
            if (!in_array($key, $langFields)) {
                $mainData[$key] = $val;
            } else {
                $langData[$key] = $val;
            }
        }

        return [$langData, $mainData];

    }

    public static function orderDetailRedirect(){
        return config('env.url.CLIENT_BASE_URL') . config('env.redirect.ORDER_DETAIL_REDIRECT');
    }

    public static function frontendSocialRedirect(){
        return config('env.url.CLIENT_BASE_URL') . config('env.redirect.FRONTEND_SOCIAL_REDIRECT');
    }

    public static function backendSocialRedirect(){
        return config('env.url.APP_URL') . config('env.redirect.BACKEND_SOCIAL_REDIRECT');
    }

    public static function userCan($user, $role){
        if(is_null($user) || !$user->can($role)){
            return response()->json(Validation::unauthorized());
        }
        return false;
    }

    public static function isDataOwner($user, $data){
        if(is_null($user) || ($user->id != $data->admin_id)){
            return response()->json(Validation::unauthorized());
        }
        return false;
    }

    public static function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }

    public static function cacheRemember($cacheKey, $query, $expiration = 60 * 60 * 24){

        return cache()->remember($cacheKey, $expiration, $query);
    }

    public static function convertTimeToUTCzone($str, $userTimezone, $format = 'Y-m-d H:i:s'){


        $new_str = new \DateTime($str, new \DateTimeZone( $userTimezone  ) );


        $new_str->setTimeZone(new \DateTimeZone('UTC'));
        return $new_str->format( $format);
    }

    public  static function convertTimeToUSERzone($str, $userTimezone, $format = 'Y-m-d H:i:s'){
        if(empty($str)){
            return '';
        }

        $new_str = new \DateTime($str, new \DateTimeZone('UTC') );
        $new_str->setTimeZone(new \DateTimeZone( $userTimezone ));
        return $new_str->format( $format);
    }

    public  static function idGenerator($model){
        $lastValue = $model::select('id')->orderBy('created_at','desc')->first();
        if($lastValue && $lastValue->id){
            $lastId = substr($lastValue->id,5);
            $lastId = (int)$lastId + 3;
        }else{
            $lastId = 111;
        }
        $id = rand(6, 9) . rand(100, 999) . rand(0, 5) . $lastId;
        return $id;
    }

    public static function formatAddress($address){
        if($address){
            $addressArr = [];
            array_push($addressArr, $address->address_1);
            array_push($addressArr, $address->address_2);
            array_push($addressArr, $address->city);
            array_push($addressArr, $address->state);
            array_push($addressArr, $address->country);
            array_push($addressArr, $address->zip);

            $filtered = array_filter($addressArr, function ($element) {
                return '' !== trim($element);
            });

            return join(', ', $filtered);
        }
        return 'N/A';
    }

    public static function formatDate($date, $format = 'h:i a, d M, y'){
        return Carbon::parse($date)->format($format);
    }


    public static function formatErrors($errors)
    {
        $errors_arr = [];
        foreach ($errors as $key => $value) {
            foreach ($errors[$key] as $error) {
                array_push($errors_arr, $error);
            }
        }
        return $errors_arr;
    }

    public static function orderId($item)
    {
        return self::formatDate($item->created_at, 'Ydm') . $item->id . $item->user_id . $item->status;
    }

    public static function calcPrice($order) {
        $subtotal = 0;
        $shipping_price = 0;
        $tax = 0;
        $offered_discount = 0;
        $bundle_offer = 0;

        foreach ($order->ordered_products as $item){
            $subtotal += (float)$item->selling * (int)$item->quantity;
            $shipping_price += (float)$item->shipping_price;
            $tax += (float)$item->tax_price;
            $bundle_offer += (float)$item->bundle_offer * (int)$item->selling;
            $offered_discount += (float)$item->offer_discount ;
        }

        $calculated['subtotal'] = $subtotal;
        $calculated['shipping_price'] = $shipping_price;
        $calculated['offered_discount'] = $offered_discount;
        $calculated['bundle_offer'] = $bundle_offer;
        $calculated['tax'] = round($tax, 2);

        // Voucher price calculation
        $voucher = $order->voucher;
        $voucherPrice = 0;
        $totalPriceWithoutShipping = $calculated['subtotal'] - $calculated['bundle_offer'];
        if($voucher){
            if((int)$voucher->type == (int)Config::get('constants.priceType.FLAT')){
                $voucherPrice = $voucher->price;
            } else {
                $voucherPrice = number_format((float)($voucher->price * $totalPriceWithoutShipping) / 100, 2, '.', '');
            }
            if(!is_null($voucher->capped_price) && $voucherPrice > $voucher->capped_price){
                $voucherPrice = (int) $voucher->capped_price;
            }
        }
        $calculated['voucher_price'] = $voucherPrice;

        $calculated['total_price'] = $totalPriceWithoutShipping + $calculated['shipping_price']
            + (float)$calculated['tax'] - $voucherPrice - $calculated['offered_discount'];

        return $calculated;
    }

    public  static function jsDecryption($encrypted){
        $key = hex2bin("0123456470abcdef0123456789abcdef");
        $iv =  hex2bin("abcdef1876343516abcdef9876543210");
        $decrypted = openssl_decrypt($encrypted, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        return trim($decrypted);
    }


    public  static function decryptLicence($encrypted, $secret_key, $secret_iv){
        $encrypt_method = "AES-256-CBC";

        // hash
        $key = hash('sha256', $secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $decryptToken = openssl_decrypt(base64_decode($encrypted), $encrypt_method, $key, 0, $iv);

        return json_decode($decryptToken);
    }

    public  static function generateTrackingId($item){
        $now = Carbon::now();
        return Utils::formatDate($now, 'Ydm') . Utils::generateRandomString(5) . $item['user_id'];
    }



    public static function makeKeyword($string) {
        $removeWords = [
            'a', 'an', 'the', 'and', 'or', 'but', 'about', 'above', 'after', 'against',
            'along', 'among', 'around', 'at', 'before', 'behind', 'below', 'beneath',
            'beside', 'between', 'by', 'down', 'during', 'for', 'from', 'in', 'inside',
            'into', 'near', 'of', 'off', 'on', 'out', 'over', 'through', 'to', 'toward',
            'under', 'up', 'with', 'within', 'without'
        ];

        // Split the string into words
        $words = preg_split("/[\s,]+/", $string);

        // Filter out the articles, prepositions, and numbers
        $filteredWords = array_filter($words, function($word) use ($removeWords) {
            return !in_array(strtolower($word), $removeWords) && !is_numeric($word);
        });

        // Join the remaining words into a comma-separated string
        $result = implode(",", $filteredWords);

        return $result;
    }




}
