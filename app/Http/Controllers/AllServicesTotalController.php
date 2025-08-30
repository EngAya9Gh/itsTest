<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Tweetcell;
use App\Models\TweetcellKontor;
use App\Models\Program;
use App\Models\Service;
use App\Models\User;
use App\Models\TransferMoneyFirm;
use App\Utils\ProfitCalculationService;
use Illuminate\Support\Facades\Log;
class AllServicesTotalController extends Controller
{ protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;

    }    

    public function index()
    {    $appRecords =  Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 2);
         })->count();
       $gameRecords = Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 1);
         })->count();
       
        $ecardRecords = Tweetcell::whereHas('tweetcellSection', function ($query) {
        $query->where('type', 3);
         })->count();
      
       $serviceRecords = Service::whereHas('category', function ($query) {
        $query->where('type',1);
         })->count();
        $itsRecords = Service::whereHas('category', function ($query) {
        $query->where('type',2);
         })->count();

       $programRecords = Program::count();
       $dataCommunicationRecords=TweetcellKontor::count();
   
       $transferMoneyFirmRecords = TransferMoneyFirm::count();
       
       $users = User::where('agent_id',auth()->user()->id)->count();
       $transferMoneyFirmRecords = TransferMoneyFirm::count();
       $balance=auth()->user()->balance;
       $financials = $this->profitService->calculateUserFinancials( auth()->user()->id); 
      
       return view('backend.dashboard',compact( 'appRecords',
       'dataCommunicationRecords',
       'serviceRecords',
       'ecardRecords',
       'gameRecords',
        'itsRecords',
                                               
       'balance',
       'programRecords',
       'transferMoneyFirmRecords',
    'users','financials'));

  
    }

}
