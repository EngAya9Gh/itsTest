<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\ProgramOrder;
use Illuminate\Support\Facades\DB;

class ProgramOrderController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        // $programOrders=DB::table('program_orders')->select('*')->orderBy('id', 'desc')->paginate(500);
        $programOrders = DB::table('program_orders')
        ->join('users', 'program_orders.user_id', '=', 'users.id')
        ->join('programs', 'program_orders.program_id', '=', 'programs.id')
        ->where('users.agent_id', '=', $currentUser->id) // إضافة شرط agent_id
        ->select('program_orders.*', 'users.name as user_name', 'programs.name as program_name')
        ->get();
        return view('backend.program.programOrders.index', compact('programOrders'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        ProgramOrder::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');
    }
    public function reject( $id)
    {
        $order= ProgramOrder::findOrFail($id);
        $order->status="مرفوض";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function accept( $id)
    {
        $order= ProgramOrder::findOrFail($id);
        $order->status="مقبول";
        $order->save();
        return back()->with('message', 'تمت العملية  بنجاح');
    }
    public function update(Request $request,  $id)
    {
        $programOrder = ProgramOrder::findOrFail($id);
        $input = $request->all();
        $programOrder->update($input);
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $programOrder= ProgramOrder::findOrFail($id);
        $programOrder->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
