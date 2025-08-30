<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AppSection;
use App\Models\GameSection;
use App\Models\Game;
use App\Models\DataCommunicationSection;
use App\Models\DataCommunication;
use App\Models\App;
use Illuminate\Support\Facades\DB;

class AppSectionContoller extends Controller
{
  
  
    public function moveAppSectionToDataSection($gameSectionId)
    {
        // البحث عن GameSection المطلوب
        $gameSection = AppSection::find($gameSectionId);
    
        if (!$gameSection) {
            return response()->json(['error' => 'GameSection not found'], 404);
        }
    
        // إنشاء AppSection جديد بناءً على بيانات GameSection
        $appSection = DataCommunicationSection::create([
            'name' => $gameSection->name,
            'image' => $gameSection->image,
            'image_url' => $gameSection->image_url,
            'status' => $gameSection->status,
        ]);
    
        // جلب جميع الألعاب المرتبطة بـ GameSection
        $games = App::where('section_id', $gameSectionId)->get();
    
        // تحويل الألعاب إلى Apps
        foreach ($games as $game) {
            DataCommunication::create([
                'id' => $game->id, // الحفاظ على نفس المعرف
                'section_id' => $appSection->id,
                'name' => $game->name,
                'image' => $game->image,
                'image_url' => $game->image_url,
                'basic_price' => $game->basic_price,
                'price' => $game->price,
                'amount' => $game->amount,
                'note' => $game->note,
                'player_no' => $game->player_no, // تعيين player_no من user_id_game
                'status' => $game->status,
            ]);
    
            // حذف اللعبة من جدول Game
            $game->delete();
        }
    
        // حذف GameSection
        $gameSection->delete();
    
        return back()->with('message', 'تمت التحويل بنجاح');
    }

    public function moveAppSectionToGameSection($gameSectionId)
{
    // البحث عن GameSection المطلوب
    $gameSection = AppSection::find($gameSectionId);

    if (!$gameSection) {
        return response()->json(['error' => 'GameSection not found'], 404);
    }

    // إنشاء AppSection جديد بناءً على بيانات GameSection
    $appSection = GameSection::create([
        'name' => $gameSection->name,
        'image' => $gameSection->image,
        'image_url' => $gameSection->image_url,
        'status' => $gameSection->status,
    ]);

    // جلب جميع الألعاب المرتبطة بـ GameSection
    $games = App::where('section_id', $gameSectionId)->get();

    // تحويل الألعاب إلى Apps
    foreach ($games as $game) {
        Game::create([
            'id' => $game->id, // الحفاظ على نفس المعرف
            'section_id' => $appSection->id,
            'name' => $game->name,
            'image' => $game->image,
            'image_url' => $game->image_url,
            'basic_price' => $game->basic_price,
            'price' => $game->price,
            'amount' => $game->amount,
            'note' => $game->note,
            'user_id_game' => $game->player_no, // تعيين player_no من user_id_game
            'status' => $game->status,
        ]);

        // حذف اللعبة من جدول Game
        $game->delete();
    }

    // حذف GameSection
    $gameSection->delete();

    return back()->with('message', 'تمت التحويل بنجاح');
}

  
    public function index()
    {
       $apps=DB::table('app_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
       return view('backend.app.appSections.index', compact('apps'));
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $input = $request->all();
        if($request->file('image')!="")
        {
        if ($file = $request->file('image')) {
            $name = 'app'.time().$file->getClientOriginalName();
            $file->move('assets/images/appSections/', $name);
            $input['image'] = $name;
            }
        }
        else
        {
            $input['image'] ="";
        }
        AppSection::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
 
    }

    public function update(Request $request, string $id)
    {
        $app = AppSection::findOrFail($id);
        $input = $request->all();
        
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
                $name = 'app_'.time().$file->getClientOriginalName();
                $file->move('assets/images/tweetcellSections/', $name);
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

        $myservice= AppSection::findOrFail($id);
       
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
        $app = AppSection::findOrFail($id);
        $app->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
