<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataCommunication;
use App\Models\DataCommunicationSection;
use Illuminate\Support\Facades\DB;
use App\Utils\ProfitCalculationService;
class DataCommunicationController extends Controller
{
    protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function index()
    { 
        $datas=DB::table('data_communications')->select('*')->orderBy('id', 'desc')->paginate(500);
        $datCommunicationSections=DB::table('data_communication_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
       foreach ($datas as $service) {
            $service->price = $this->profitService->getPrice($service);    // إنشاء رابط 
        }
        return view('backend.data.datas.index', compact('datas','datCommunicationSections'));

        
    } 
    public function showData($id)
    {
        $datas=DB::table('data_communications')->select('*')->where('section_id',$id)->orderBy('id', 'desc')->paginate(500);
   
        $datCommunicationSections=DB::table('data_communication_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
        return view('backend.data.datas.index',compact('datas','datCommunicationSections'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
      
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'data'.time().$file->getClientOriginalName();
                $file->move('assets/images/data/', $name);
                $input['image'] = $name;
            } 
        }
        else
        {
           $input['image'] ="";
        }
         
        DataCommunication::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function update(Request $request, string $id)
    {
        $data = DataCommunication::findOrFail($id);
        $input = $request->all();
       
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'data'.time().$file->getClientOriginalName();
                $file->move('assets/images/data/', $name);
                $input['image'] = $name;
            } 
        }
        else
        {
            $input['image'] =$data['image'];
        }
        $data->update($input);
       
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function changeStatus(string $id)
    {

        $myservice= DataCommunication::findOrFail($id);
       
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
        $data= DataCommunication::findOrFail($id);
        $data->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
