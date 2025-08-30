<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 
use App\Models\ServiceCategories;
use App\Models\Service;
use App\Utils\ProfitCalculationService;
class ServiceController extends Controller
{
    protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    { 
        $categories=ServiceCategories::all();
        $services=Service::all();
        foreach ($services as $service) {
            $service->basic_price = $this->profitService->getPrice($service);    // إنشاء رابط 
        }
        return view('backend.service.services.index', compact('categories','services'));
    }
    public function showServices($id)
    {
        $services=DB::table('services')->select('*')->where('section_id',$id)->orderBy('id', 'desc')->get();
   
        $section=DB::table('service_categories')->select('*')->where('id',$id)->first();
        return view('backend.service.services.index',compact('services','section'));
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
        Service::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);
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

        $myservice= Service::findOrFail($id);
       
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
        $service= Service::findOrFail($id);
        $service->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
