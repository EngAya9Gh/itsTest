<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TweetcellKontorOrderController extends Controller
{
    public function index()
    {   $currentUser = auth()->user();
        $orders = DB::table('tweetcell_kontor_orders')
        ->join('users', 'tweetcell_kontor_orders.user_id', '=', 'users.id')
        ->join('tweetcell_kontors', 'tweetcell_kontor_orders.tweetcell_kontor_id', '=', 'tweetcell_kontors.id')
        ->join('tweetcell_kontor_sections', 'tweetcell_kontors.section_id', '=','tweetcell_kontor_sections.id')
        ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
        ->select('tweetcell_kontor_orders.*', 'users.name as user_name', 'tweetcell_kontors.name as service_name')
        ->get();
     
        return view('backend.tweetcellKontor.tweetcellOrders.index', compact('orders'));
    }


}
