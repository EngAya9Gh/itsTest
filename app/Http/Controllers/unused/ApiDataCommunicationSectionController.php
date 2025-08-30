<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\TweetcellSection;
use App\Models\Tweetcell;
use App\Jobs\UpdateKontorPackeges;

class ApiDataCommunicationSectionController extends Controller
{
    public function index() 
    {

        dispatch(new UpdateKontorPackeges());

        $dataSections=DB::table('tweetcell_sections')->where('type',3)->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($dataSections as $data) {
         $data->image_url = asset('assets/images/tweetcellSections/' . $data->image);  // إنشاء رابط للصورة
         
        }
       return response()->json(['dataSections'=> $dataSections]);
    }

    public function getData(string $section_id)
    {
       $section = TweetcellSection::find($section_id);
       $dataCommunication = $section->tweetcells;
       foreach ($dataCommunication as $app) {
         $app->image_url = asset('assets/images/tweetcell/' . $app->image);  // إنشاء رابط للصورة
         $app->save();
        }
       return response()->json(['data-communications'=> $dataCommunication ]);
    }

}
