<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellSection;
use App\Models\Tweetcell;
use App\Models\TweetcellOrder;
use Illuminate\Support\Facades\DB;

class TweetcellSectionController extends Controller
{
    public function getByTypeByProvider($type,$apiNo)
    { //dd('kkk');
        $services=DB::table('tweetcell_sections')->select('*')->where('type',$type)->where('api_no',$apiNo)->orderBy('id', 'desc')->paginate(500);
        return view('backend.tweetcell.tweetcellSections.index', compact('services'));
    }


    


    public function store(Request $request)
    {
        $input = $request->all();
      
         if($request->file('image')!="")
         {
            if ($file = $request->file('image')) {
                $name = 'game'.time().$file->getClientOriginalName();
                $file->move('assets/images/tweetcellSections/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
        TweetcellSection::create($input);
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
        $game = TweetcellSection::findOrFail($id);
        $input = $request->all();
      
        if($request->file('image')!="")
        {
           if ($file = $request->file('image')) {
               $name = 'game'.time().$file->getClientOriginalName();
               $file->move('assets/images/tweetcellSections/', $name);
               $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= $game['image'];
        }
     if($game->increase_percentage != $input['increase_percentage'])
     {  $services=$game->tweetcells;
        foreach($services as $s)
        {
          $s->price =$s->basic_price+$s->basic_price*$input['increase_percentage']/100;
          $s->save();
        }
     }
         $game->update( $input);
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function changeStatus(string $id)
    {

        $myservice= TweetcellSection::findOrFail($id);
       
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
        $game= TweetcellSection::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }

}
