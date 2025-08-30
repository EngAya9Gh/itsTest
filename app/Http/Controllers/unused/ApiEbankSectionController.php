<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EbankSection;

class ApiEbankSectionController extends Controller
{
    public function index()
    {
       $ebankSections=DB::table('ebank_sections')->select('*')->orderBy('id', 'desc')->paginate(500);
       foreach ($ebankSections as $app) {
         $app->image_url = asset('assets/images/ebankSections/' . $app->image);  // إنشاء رابط للصورة
       
        }
       return response()->json(['ebankSections'=> $ebankSections ]);
    }

    public function getEbanks(string $section_id)
    {
       $section = EbankSection::find($section_id);
       $ebanks = $section->ebanks;
       foreach ($ebanks as $app) {
         $app->image_url = asset('assets/images/ebanks/' . $app->image);  // إنشاء رابط للصورة
         $app->save();
        }
       return response()->json(['ebanks'=> $ebanks ]);
    }
}
