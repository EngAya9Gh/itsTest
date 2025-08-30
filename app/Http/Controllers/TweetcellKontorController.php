<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TweetcellKontorSection;
use App\Models\TweetcellKontor;
use Illuminate\Support\Facades\DB;
class TweetcellKontorController extends Controller
{
    public function update(Request $request, string $id)
    {
        $s = TweetcellKontor::findOrFail($id);
        $input = $request->all();
        if($request->file('image')!="")
        {
           if ($file = $request->file('image')) {
               $name = 'tweet'.time().$file->getClientOriginalName();
               $file->move('assets/images/tweetcellKontor/', $name);
               $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= $s['image'];
        }
        $s->update( $input);
       
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function showGames($id)
    {
        $services=DB::table('tweetcell_kontors')->select('*')->where('section_id',$id)->orderBy('id', 'desc')->paginate(500);
   
        $sections=DB::table('tweetcell_kontor_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
        return view('backend.tweetcellKontor.tweetcells.index',compact('services','sections'));
    }
    public function changeStatus(string $id)
    {

        $myservice= TweetcellKontor::findOrFail($id);
       
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
  
   public function firsat(string $id)
    {

        $myservice= TweetcellKontor::findOrFail($id);
       
        if($myservice->is_firsat)
         { $myservice->is_firsat=0;
           $myservice->save();
            return back()->with('message', 'تم بنجاح ');
         }
        else
         { $myservice->is_firsat=1;
            $myservice->save();
          return back()->with('message', 'تم بنجاح');
         }
    }
  
    public function destroy(string $id)
    {
        $game= TweetcellKontor::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }


}
