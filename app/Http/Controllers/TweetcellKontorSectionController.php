<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellKontorSection;
use App\Models\TweetcellKontor;
use App\Models\TweetcellKontorOrder;
use Illuminate\Support\Facades\DB;

class TweetcellKontorSectionController extends Controller
{
    public function index()
    { 
        $services=DB::table('tweetcell_kontor_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
        return view('backend.tweetcellKontor.tweetcellSections.index', compact('services'));
    }


    


    public function store(Request $request)
    {
        $input = $request->all();
      
         if($request->file('image')!="")
         {
            if ($file = $request->file('image')) {
                $name = 'game'.time().$file->getClientOriginalName();
                $file->move('assets/images/tweetcellKontorSections/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
      
        TweetcellKontorSection::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }

    public function show(string $id)
    {
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {
        $game = TweetcellKontorSection::findOrFail($id);
        $input = $request->all();
      
        if($request->file('image')!="")
        {
           if ($file = $request->file('image')) {
               $name = 'game'.time().$file->getClientOriginalName();
               $file->move('assets/images/tweetcellKontorSections/', $name);
               $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= $game['image'];
        }
         if($game->increase_percentage != $input['increase_percentage'])
     {  $services=$game->tweetcellKontors;
    
        foreach($services as $s)
        {
       $newPrice = $s->basic_price + ($s->basic_price *$input['increase_percentage'] / 100);
        $s->update(['price' => $newPrice]);
        }
     }
        $game->update( $input);
   
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function changeStatus(string $id)
    {

        $myservice= TweetcellKontorSection::findOrFail($id);
       
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
        $game= TweetcellKontorSection::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }


}
