<?php
  
namespace App\Http\Controllers\API;
  
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
  
class ImageUploadController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function imageUpload()
    {
        return view('imageUpload');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $imageName = time().'.'.$request->image->extension();  
     
        $request->image->move(public_path('images'), 'new.'.$request->image->extension());
        dd('done');
  
        /* Store $imageName name in DATABASE from HERE */
    
        // return back()
        //     ->with('success','You have successfully upload image.')
        //     ->with('image',$imageName); 
    }
}