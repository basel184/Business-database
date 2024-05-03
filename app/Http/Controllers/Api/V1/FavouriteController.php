<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\UnFavourite;
use App\Models\Favourite;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

use App\Models\FavouriteItem;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Jobs\Favourite as JobsFavourite;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponses as TraitsApiResponses;

class FavouriteController extends Controller
{

    use ApiResponses;


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

        $validatedData = Validator::make($request->all(), [
            'ad_id'=>'required'
        ]);
        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }

        $request['user_id'] = auth()->user()->id;

        $user=  $request['user_id'];
        $favourite=Favourite::where('user_id',$user)->first();
        if(is_null($favourite)){
            $favourite=Favourite::create([

                'user_id'=>$user
       ]);}
       $item=FavouriteItem::where('favourite_id',$favourite->id)
       ->where('ad_id',$request->ad_id)->first();
       if(is_null($item)){

        $favourite->items()->create([
            'favourite_id'=>$favourite->id,
            'ad_id'=> $request->ad_id
        ]);

         return $this->success('done', 'Added to favourite');
       }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {




        $id=request('ad_id');
        $data = [
            "user_id" => auth()->user()->id,
            "id" => $id
        ];

        UnFavourite::dispatch($data)->onQueue('posting');

        return $this->success(null, 'Deleted', Response::HTTP_NO_CONTENT);

    }

    public function userFavourites(){
        $favourites= FavouriteItem::whereHas('favourite',function($q){
            $q->where('user_id',request('id'));
        })->with('ad')->paginate(30);
        return $this->success($favourites, 'Done');
    }
}
