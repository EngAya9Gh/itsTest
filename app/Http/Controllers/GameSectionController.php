<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GameSection;
use App\Models\App;
use App\Models\Game;

use App\Models\DataCommunicationSection;
use App\Models\DataCommunication;
use App\Models\AppSection;
use Illuminate\Support\Facades\DB;
class GameSectionController extends Controller
{
    public function index()
    { 
        $games=DB::table('game_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
        return view('backend.game.gameSections.index', compact('games'));
    }
public function moveGameSectionToDataSection($gameSectionId)
{
    // البحث عن GameSection المطلوب
    $gameSection = GameSection::find($gameSectionId);

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
    $games = Game::where('section_id', $gameSectionId)->get();

    // تحويل الألعاب إلى data
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
            'player_no' => $game->user_id_game, // تعيين player_no من user_id_game
            'status' => $game->status,
        ]);

        // حذف اللعبة من جدول Game
        $game->delete();
    }

    // حذف GameSection
    $gameSection->delete();

    return back()->with('message', 'تمت التحويل بنجاح');
}

    public function moveGameSectionToAppSection($gameSectionId)
{
    // البحث عن GameSection المطلوب
    $gameSection = GameSection::find($gameSectionId);

    if (!$gameSection) {
        return response()->json(['error' => 'GameSection not found'], 404);
    }

    // إنشاء AppSection جديد بناءً على بيانات GameSection
    $appSection = AppSection::create([
        'name' => $gameSection->name,
        'image' => $gameSection->image,
        'image_url' => $gameSection->image_url,
        'status' => $gameSection->status,
    ]);

    // جلب جميع الألعاب المرتبطة بـ GameSection
    $games = Game::where('section_id', $gameSectionId)->get();

    // تحويل الألعاب إلى Apps
    foreach ($games as $game) {
        App::create([
            'id' => $game->id, // الحفاظ على نفس المعرف
            'section_id' => $appSection->id,
            'name' => $game->name,
            'image' => $game->image,
            'image_url' => $game->image_url,
            'basic_price' => $game->basic_price,
            'price' => $game->price,
            'amount' => $game->amount,
            'note' => $game->note,
            'player_no' => $game->user_id_game, // تعيين player_no من user_id_game
            'status' => $game->status,
        ]);

        // حذف اللعبة من جدول Game
        $game->delete();
    }

    // حذف GameSection
    $gameSection->delete();

    return back()->with('message', 'تمت التحويل بنجاح');
}


    public function store(Request $request)
    {
        $input = $request->all();
      
         if($request->file('image')!="")
         {
            if ($file = $request->file('image')) {
                $name = 'game'.time().$file->getClientOriginalName();
                $file->move('assets/images/gameSections/', $name);
                $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= "";
        }
        GameSection::create($input);
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
        $game = GameSection::findOrFail($id);
        $input = $request->all();
      
        if($request->file('image')!="")
        {
           if ($file = $request->file('image')) {
               $name = 'game'.time().$file->getClientOriginalName();
               $file->move('assets/images/gameSections/', $name);
               $input['image'] = $name;
            }
        }
        else
        {
            $input['image']= $game['image'];
        }
        $game->update( $input);
   
        return back()->with('message', 'تم التعديل بنجاح');
    }
    public function changeStatus(string $id)
    {

        $myservice= GameSection::findOrFail($id);
       
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
        $game= GameSection::findOrFail($id);
        $game->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
