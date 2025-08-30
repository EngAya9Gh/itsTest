<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataCommunicationSection;
use Illuminate\Support\Facades\DB;

class DataCommunicationSectionController extends Controller
{
    public function index()
    { 
        
       $dataSections=DB::table('data_communication_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
     //  dd('fkkkkkk');
       return view('backend.data.dataSections.index', compact('dataSections'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $input = $request->all();
        if($request->file('image')!="")
        {
        if ($file = $request->file('image')) {
            $name = 'data'.time().$file->getClientOriginalName();
            $file->move('assets/images/dataCommunicationSections/', $name);
            $input['image'] = $name;
            }
        }
        else
        {
            $input['image'] ="";
        }
        DataCommunicationSection::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
 
    }

    public function update(Request $request, string $id)
    {
        $app = DataCommunicationSection::findOrFail($id);
        $input = $request->all();
        
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'data_'.time().$file->getClientOriginalName();
                $file->move('assets/images/dataCommunicationSections/', $name);
                $input['image'] = $name;
            }
       }
       else
       {
            $input['image'] =$app['image'];
       }
        $app->update($input);
       
        return back()->with('message', 'تم التعديل بنجاح');

    }

    public function changeStatus(string $id)
    {

        $myservice= DataCommunicationSection::findOrFail($id);
       
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
        $data = DataCommunicationSection::findOrFail($id);
        $data->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
