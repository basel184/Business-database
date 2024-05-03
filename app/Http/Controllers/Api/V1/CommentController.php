<?php

namespace App\Http\Controllers\Api\V1;

use App\Jobs\AddComment;
use App\Jobs\DeleteComment;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
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

        $id=request('id');
        $validatedData = Validator::make($request->all(), [
            'content' => 'required|string|max:255',
        ]);


        if ($validatedData->fails()) {
            return $this->failed($validatedData->errors(), 422);
        }
        $request['user_id'] = auth()->user()->id;
        $request['user_name'] = auth()->user()->name;
        $request['id'] = $id;
        AddComment::dispatch($request->all())->onQueue('posting');
        return $this->success('done', 'added Successfully . . .');
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
    public function destroy(Request $request)
    {

        $request['user_id'] = auth()->user()->id;
        $request['id'] = request('id');
        DeleteComment::dispatch($request->all())->onQueue('posting');
        return $this->success('done', 'deleted Successfully . . .');
    }

    public function userComments(){
        $comments= Comment::where('user_id',request('id'))->with('ad')->paginate(30);
        return $this->success($comments, 'Done');
    }
}
