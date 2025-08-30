<?php

namespace App\Http\Controllers;

use App\Models\DataCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tweetcell;

class ApiDataCommunicationController extends Controller
{
    public function index()
    { 
        $dataCommunications=DB::table('tweetcell')->select('*')->orderBy('id', 'desc')->paginate(500);
        foreach ($dataCommunications as $app) {
            $app->image_url = asset('assets/images/tweetcellSections/' . $app->image);  // إنشاء رابط للصورة
           
        }
       
        return response()->json(['dataCommunications'=>$dataCommunications ]);
    }

    public function show($id)
    {
       $dataCommunication = Tweetcell::findOrFail($id); 
       
       return response()->json(['dataCommunication'=>$dataCommunication]);
    }
}
