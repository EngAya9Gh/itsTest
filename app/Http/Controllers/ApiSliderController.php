<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiSliderController extends Controller
{
    public function sliders()
    {
        $slider=DB::table('sliders')->select('*')->where('is_news',0)->paginate(500);
        foreach ($slider as $app) {
            $app->image_url = asset('assets/images/sliders/' . $app->image);  // إنشاء رابط للصورة
        }
        return response()->json(['slider'=>$slider]);
    }
      public function news()
    {
        $slider=DB::table('sliders')->select('*')->where('is_news',1)->get();
        foreach ($slider as $app) {
            $app->image_url = asset('assets/images/sliders/' . $app->image);  // إنشاء رابط للصورة
        }
      //  dd($slider);
        return response()->json(['news'=>$slider]);
    }
}
 