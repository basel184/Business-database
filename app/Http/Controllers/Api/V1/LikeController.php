<?php

namespace App\Http\Controllers\Api\V1;


use App\Jobs\UnLike;
use App\Traits\ApiResponses;
use App\Http\Controllers\Controller;
use App\Jobs\AddLike;

class LikeController extends Controller
{
    use ApiResponses;

    public function likeAd()
    {
        $id=request('id');
        $data = [
            "user_id" => auth()->user()->id,
            "id" => $id
        ];

        AddLike::dispatch($data)->onQueue('posting');
        return $this->success('done', 'Liked Successfully . . .');
    }

    public function unlikeAd()
    {
        $data = [
            "user_id" => auth()->user()->id,
            "id" => request('id')
        ];

        UnLike::dispatch($data)->onQueue('posting');
        return $this->success('done', ' unLiked Successfully . . .');
    }


    public function userLikes(){
        $likes= Like::where('user_id',request('id'))->with('ad')->paginate(30);
        return $this->success($likes, 'Done');
    }
}
