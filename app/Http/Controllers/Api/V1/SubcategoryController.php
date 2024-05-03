<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Traits\ImageTrait;
class SubcategoryController extends Controller
{
    
    use ImageTrait;
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
     if(request('category_id')){
        $categories = Category::where('parent_id',request('category_id'))->get();
     }else{
        $categories = Category::whereNotNull('parent_id')->get();
     }

        return Response()->json(['data' => $categories]);
    }

    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $category = Category::whereNotNull('parent_id')->where('id',request('id'))->first();
         if($category){
        
        return Response()->json(['data' => $category]);
         }else{
           return Response()->json(['message' => 'SubCategory Not Found']);    
         }
    }
    
    
        public function store(Request $request): JsonResponse
    {
       
       
       // dd($request->all());
          if ($request->image_web) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image_web, $filename, 'subcategories_web');
            $image_link = asset('images/subcategories_web/' . $imagename);
            $request['file_web'] =   $imagename;
        }

        if ($request->image_app) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image_app, $filename, 'subcategories_app');
            
            
            $image_link = asset('images/subcategories_app/' . $imagename);
            $request['file_app'] =   $imagename;
        }

          

        $category = Category::create([
            "name_ar"=>$request->name_ar,
            "name_en"=>$request->name_en,
            "name_fr"=>$request->name_fr,
            "parent_id"=>$request->parent_id,
            "image_app"=> $request['file_app'],
            "image_web"=>$request['file_web']
        
        ]);
        return Response()->json(['data' => $category]);
    }
    
    
    
    public function update(Request $request): JsonResponse
    {
       
      
       // dd($request['id']);
          if ($request->image_web) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image_web, $filename, 'subcategories_web');
            $image_link = asset('images/subcategories_web/' . $imagename);
            $request['file_web'] =   $imagename;
        }

        if ($request->image_app) {

            $filename =  time() . '.' . "png";
            $imagename =  $this->uploadImage($request->image_app, $filename, 'subcategories_app');
            
            
            $image_link = asset('images/subcategories_app/' . $imagename);
            $request['file_app'] =   $imagename;
        }

          

$category = Category::where('id', $request['id'])
    ->where('parent_id', $request->parent_id)
    ->update([
        "name_ar" => $request->name_ar,
        "name_en" => $request->name_en,
        "name_fr" => $request->name_fr,
        "parent_id" => $request->parent_id,
        "image_app" => $request['file_app'],
        "image_web" => $request['file_web']
    ]);
        return Response()->json(['message'=>'Updated','data' => $category]);
    }
    
    
    
   public function destroy(Request $request) {
    
   Category::whereNotNull('parent_id')->where('id', request('id'))->delete();
      
    
    return response()->json(['message' => 'Deleted']);
}
}
