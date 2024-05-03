<?php
namespace App\Http\Controllers\Api\V1;
use App\Models\Ad;
use App\Models\User;

use App\Models\AdImage;
use App\Models\Category;

use App\Traits\ImageTrait;
use App\Models\Transaction;

use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\PromotionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\AdDeleted;
use Illuminate\Support\Facades\Validator;

class AdController extends Controller
{
    use ImageTrait;
    use ApiResponses;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $region = request('region');
        $ads = Ad::where('region', $region)
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function show(): JsonResponse
    {
        $ad = Ad::with('comments')->findOrFail(request('id'));
        if($ad){
            $ad->increment('views');
            return $this->success($ad, 'Done');
        }else{

            return $this->failed('This Ads Not Found',400);
        }


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

   DB::beginTransaction();

  try {
    $request['user_id'] = auth()->user()->id;

    $ad = Ad::create([
        'type' => $request->type,
        'user_type' => $request->user_type,
        'title' => $request->title,
        'description' => $request->description,
        'price' => $request->price,
        'city' => $request->city,
        'region' => $request->region,
        'category_id' => $request->category_id,
        'user_id' => $request->user_id,
        'company_id' => $request->company_id ?? null,
        'approved' => $request->approved,
        'sub_category_id' => $request->sub_category_id ?? 0,
    ]);

    if ($request->has('attributes')) {
        $attributes = $request->input('attributes');

        foreach ($attributes as $attribute) {
            DB::table('ad_attribute')->insert([
                'ad_id' => $ad->id,
                'attribute_id' => $attribute['attribute_id'],
                'attribute_option_id' => $attribute['attribute_option_id']
            ]);
        }
    }

    $files = [];
    if ($request->images) {
        foreach ($request->images as $key => $image) {
            $filename = $key . time() . '.' . 'png';
            $imagename = $this->uploadImage($image, $filename, 'product');
            $image_link = asset('images/product/' . $imagename);

            AdImage::create([
                'ad_id' => $ad->id,
                'image' => $image_link
            ]);
            array_push($files, $image_link);
        }
    }

    $request['links'] = $files;

    DB::commit();

    return $this->success('done', 'added Successfully . . .');
} catch (\Exception $e) {
    DB::rollBack();
    return $this->error('error', 'An error occurred while processing the request.');
}

    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validatedData = Validator::make($request->all(), [
            'type' => 'required|in:product,service,required_service',
            'title' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'city' => 'required|string',
            'region' => 'required|in:egypt,morocco',
            'category_id' => 'required',
            'approved' => 'integer',
            //'company_id' => 'nullable|numeric',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable',
            'attributes.*.attribute_option_id' =>'nullable',
            //'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }

        $request['user_id'] = auth()->user()->id;
        $request['id'] = request('id');
        $files = [];


        if ($request->hasFile('images')) {
            $images = $request->file('images');
            foreach ($images as $key => $image) {

                $filename = $key . time() . '.' . 'png';
                $imagename =  $this->uploadImage($request->image, $filename, 'product');
                $image = 'images/product/' . $imagename;
                array_push($files, $image);
            }
        }
          $request['links'] = $files;
          $ad= Ad::whereId($request['id'])->update([

            'type' => $request->type,
            'user_type' => $request->user_type,
           'title' => $request->title,
           'description' => $request->description,
           'price' => $request->price,
           'city' => $request->city,
           'region' => $request->region,
           'category_id' => $request->category_id,
           'approved' => $request->approved,
           'user_id' => $request->user_id,
           'company_id' => $request->company_id ?? null,
           'sub_category_id' =>$request->sub_category_id?? 0,

       ]);
        return $this->success('done', 'Updated Successfully . . .');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        $data = [
            'user_id' => auth()->user()->id,
            'id' => request('id')
        ];
       $ad= Ad::findOrFail($data['id']);
        $ad->images()->delete();
        $ad->delete();
        return $this->success('done', 'deleted Successfully . . .');
    }

     public function promote(Request $request)
    {
        try {
            DB::beginTransaction();
            $user=User::whereId(auth()->user()->id)->first();

            $adId=request('adId');
            $ad = Ad::findOrFail($adId);
            if ($ad->promotion_expiry >= now()) {
                echo 'Ad Already Promoted' . PHP_EOL;
                return;
            }
            $promotionPlan = PromotionPlan::findOrFail($request->promotion_plan_id);
            $duration = $request->duration;

            $promotionPrice = $promotionPlan->getPromotionPrice($duration);

            $ad->promote($promotionPlan, $duration);
            $ad->save();

            // Create a new transaction record
            $transaction = new Transaction();
            $transaction->type = 'ad_promotion';
            $transaction->ad_id = $ad->id;
            $transaction->amount = $promotionPrice;
            $transaction->save();

            if ($user->wallet = 0 || $user->wallet<$transaction->amount) {
                return response()->json(['message' => 'Wallet is Empty']);

            } else {
               $user->wallet -= $transaction->amount;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e->getMessage();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ad Promoted Successfully . . .',
        ], 200);
    }
  public function endPromotion()
    {
        $adId=request('adId');
        $data['id'] = $adId;
        try {
            $ad = Ad::findOrFail($adId);

            $ad->endPromotion();
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Ad Promotion Ended Succsessfully . . .',
        ], 200);
    }



    public function getProducts(): JsonResponse
    {
        $region = request('region');
        $category_id = request('category_id');
        $ads = Ad::where('type', 'product')
            ->where('region', $region)
            ->where(function ($query) use ($category_id) {
                $query->where('category_id', $category_id)
                    ->orWherehas('category', function ($query) use ($category_id) {
                        $query->whereNotNull('parent_id')
                            ->where('parent_id', $category_id);
                    });
            })->sortedAds()->paginate(30);

        return $this->success($ads, 'Done');
    }

    public function getTypes(): JsonResponse
    {
        $region = request('region');
        $category_id = request('category_id');
        $type=request('type');
        $ads = Ad::where('type', $type)
            ->where('approved', 1)
            ->where('region', $region)
            ->where(function ($query) use ($category_id) {
                $query->where('category_id', $category_id)
                    ->orWherehas('category', function ($query) use ($category_id) {
                        $query->whereNotNull('parent_id')
                            ->where('parent_id', $category_id);
                    });
            })
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');

    }


    public function byCategory(): JsonResponse
    {
        $region = request('region');
        $categoryId = request('category_id');
        // Get the category and its child categories
        $category = Category::with('children')->findOrFail($categoryId);
        $categoryIds = $category->children->pluck('id')->push($categoryId);

        $ads = Ad::where('approved', false)
            ->whereIn('category_id', $categoryIds)
            ->where('region', $region)
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function bySubCategory(): JsonResponse
    {

        $region = request('region');
        $categoryId = request('sub_category_id');
        // Get the category and its child categories

        $ads = Ad::where('approved', false)
            ->where('category_id', $categoryId)
            ->where('region', $region)
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function byCompany(): JsonResponse
    {
        $region = request('region');
        $companyId = request('company_id');
        $ads = Ad::where('approved', false)
            ->where('company_id', $companyId)
            ->where('region', $region)
            ->sortedAds()->paginate(30);

        return $this->success($ads, 'Done');
    }

    public function getPending(): JsonResponse
    {
        $ads = Ad::where('approved', false)
            ->where('region', request('region'))
            ->sortedAds()->paginate(30);
        return $this->success($ads, 'Done');
    }

    public function searchAd(): JsonResponse
    {
        $search = request('search');
        $region = request('region');
        $ads = Ad::query();

        if ($search) {
            $ads->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        $ads->where('region', $region);

        $results = $ads->sortedAds()->paginate(30);

        return $this->success($results, 'Done');
    }

    public function filterByAttributes(Request $request): JsonResponse
    {
        $region = request('region');
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'attributes' => 'array',
            'attributes.*' => 'exists:attribute_options,id',
            'city' => 'nullable|string',
            'subCategory' => 'nullable|exists:categories,id',
            'price_from' => 'nullable|numeric',
            'price_to' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $categoryID = $request->input('category_id');
        $attributeOptions = $request->input('attributes');
        $city = $request->input('city');
        $subCategoryID = $request->input('subCategory');
        $priceFrom = $request->input('price_from');
        $priceTo = $request->input('price_to');

        $ads = Ad::where('approved', true)
            ->where('category_id', $categoryID)
            ->where('region', $region);

        if ($city) {
            $ads->where('city', $city);
        }

        if ($subCategoryID) {
            $ads->whereHas('category', function ($query) use ($subCategoryID) {
                $query->where('parent_id', $subCategoryID);
            });
        }

        if (!empty($attributeOptions)) {
            foreach ($attributeOptions as $attributeId => $optionId) {
                $ads->whereHas('attributes', function ($query) use ($attributeId, $optionId) {
                    $query->where('attribute_id', $attributeId)
                        ->where('attribute_option_id', $optionId);
                });
            }
        }

        if ($priceFrom && $priceTo) {
            $ads->whereBetween('price', [$priceFrom, $priceTo]);
        } elseif ($priceFrom) {
            $ads->where('price', '>=', $priceFrom);
        } elseif ($priceTo) {
            $ads->where('price', '<=', $priceTo);
        }

        $filteredAds = $ads->distinct()->sortedAds()->paginate(30);

        return response()->json([
            'data' => $filteredAds
        ]);
    }

    public function userAds(){
        $ads=Ad::where('user_id',request('id'))->where('approved',1)->paginate(30);

        return $this->success($ads, 'Done');


    }

    public function prometedUserAds(){
        $ads=Ad::where('user_id',request('id'))->where('promotion_plan_id' ,'!=',null)->paginate(30);
        return $this->success($ads, 'Done');
    }
}
