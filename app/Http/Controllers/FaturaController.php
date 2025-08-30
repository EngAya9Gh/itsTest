<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaturaSection;
use App\Models\Fatura;
use Illuminate\Support\Facades\DB;
  use App\Utils\ProfitCalculationService;

class FaturaController extends Controller
{

    protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    
    public function update(Request $request, string $id)
    {
        $s = Fatura::findOrFail($id);
        $input = $request->all();
       if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'fatura'.time().$file->getClientOriginalName();
                $file->move('assets/images/fatura/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
        $s->update( $input);
       
        return back()->with('message', 'تم التعديل بنجاح');
    }

   
    public function index()
    {
       
    }
   public function store(Request $request)
    {
        $input = $request->all();
      
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'fatura'.time().$file->getClientOriginalName();
                $file->move('assets/images/fatura/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
        Fatura::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }


    public function showFaturas($id)
    {  $section=FaturaSection::where('id',$id)->first();
        $services=DB::table('faturas')->select('*')->where('section_id',$id)->get();
   
        $sections=DB::table('fatura_sections')->select('*')->get();
       foreach ($services as $service) {
            $service->basic_price = $this->profitService->getPrice($service);
     //    $service->image = $section->image;
        }
        return view('backend.fatura.faturas.index',compact('services','section'));
    }
    public function changeStatus(string $id)
    {

        $myservice= Fatura::findOrFail($id);
       
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
   public function offStatus()
    {

        $services=DB::table('faturas')->select('*')->where('status',2)->get();
   
        $sections=DB::table('fatura_sections')->select('*')->get();
       foreach ($services as $service) {
            $service->basic_price = $this->profitService->getPrice($service);
     $service->image = FaturaSection::where('id',$service->section_id)->first()->image;
        }
        return view('backend.fatura.faturas.index',compact('services','section'));
    }
  
    public function destroy(string $id)
    {
        $game= Fatura::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
