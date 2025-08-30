<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\DB;

class SliderController extends Controller
{
    public function index()
    {
        $sliders=DB::table('sliders')->select('*')->orderBy('id', 'desc')->paginate(500);
      $type='slide';
        return view('backend.slider.index', compact('sliders','type'));
    }
    public function heroType($type)
    {$sliders=[];
      if($type=='slide')
        $sliders=DB::table('sliders')->select('*')->where('is_news',0)->paginate(500);
     
      else if($type=='news')
         $sliders=DB::table('sliders')->select('*')->where('is_news',1)->paginate(500);

       return view('backend.slider.index', compact('sliders','type'));
    }

    public function store(Request $request)
    {
        

        $input = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = 'slider'.time().$file->getClientOriginalName();
            $file->move('assets/images/sliders/', $name);
            $input['image'] = $name;
         
        }
       Slider::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function update(Request $request, $id)
    {
        $slider = Slider::findOrFail($id);
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'nullable|string|max:255',
        ]);

        $input = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $name = 'slider'.time().$file->getClientOriginalName();
            $file->move('assets/images/sliders/', $name);
            $input['image'] = $name;
            $slider->update($input);
        }

        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
