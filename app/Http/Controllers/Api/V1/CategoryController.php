<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
     $categories = Category::whereNull('parent_id')
    ->get();

    return Response()->json(['data' => $categories]);
    }

    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $category = Category::whereNull('parent_id')->findOrFail(request('id'));
        return Response()->json(['data' => $category]);
    }
}
