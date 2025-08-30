<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ServiceCategories;

class ServiceCategoryController extends Controller
{
    public function index()
    { 
        $services=ServiceCategories::all();
        return view('backend.service.categories.index', compact('services'));
    }
   public function getType($type)
    { 
        $services=ServiceCategories::where('type',$type)->get();
        return view('backend.service.categories.index', compact('services','type'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
      
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'service'.time().$file->getClientOriginalName();
                $file->move('assets/images/service/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
        ServiceCategories::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function update(Request $request, string $id)
    {
        $service = ServiceCategories::findOrFail($id);
        $input = $request->all();
      
        if($request->file('image')!="")
        {
           if ($file = $request->file('image')) {
               $name = 'service'.time().$file->getClientOriginalName();
               $file->move('assets/images/service/', $name);
               $input['image'] = $name;
             
            }

        }
        else
        {
            $input['image']= $service['image'];
           
        }
           $service->update( $input);
       
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function changeStatus(string $id)
    {

        $myservice= ServiceCategories::findOrFail($id);
       
        if($myservice->status)
         { $myservice->status=0;
           $myservice->save();
            return back()->with('message', 'تم الغاء تفعيل الخدمة  بنجاح');
         }
        else 
         { $myservice->status=1;
            $myservice->save();
          return back()->with('message', 'تم تفعيل الخدمة  بنجاح');
         }
    }
    public function destroy(string $id)
    {
        $service= ServiceCategories::findOrFail($id);
        $service->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
