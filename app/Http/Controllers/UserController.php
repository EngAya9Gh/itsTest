<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vip;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TransferMoneyFirmOrder;
use Illuminate\Support\Facades\Http;

use App\Utils\ProfitCalculationService;

class UserController extends Controller
{   protected $profitService;
    public function __construct(ProfitCalculationService $profitService)
    {
        $this->profitService = $profitService;
    }
    public function test()
    {
     
$response = Http::withHeaders([
    'api-token' => 'b74d86453d879b6fb9582b12dba2a9eb2519949b4f0fed7c',
])->get('https://api.masar-card.com/client/api/products?products_id=1332');

$data = $response->json(); // تحويل الاستجابة إلى مصفوفة
      dd($data);
    }
        public function test1()
    {
     
$response = Http::withHeaders([
    'api-token' => 'b74d86453d879b6fb9582b12dba2a9eb2519949b4f0fed7c',
])->get('https://api.masar-card.com/client/api/newOrder/581/params?qty=1&playerId=8209802&order_uuid=73ffd5e-b465-4bba-902a-713c38f76326');

$data = $response->json(); // تحويل الاستجابة إلى مصفوفة
      dd($data);
    } 
    public function index()
    { 
        $users=DB::table('users')->select('*')->orderBy('id', 'desc')->paginate(500);
        $vips=DB::table('vips')->select('*')->orderBy('id', 'desc')->get();
        $currencies=DB::table('currencies')->select('*')->orderBy('id', 'desc')->get();
       return view('backend.users.index', compact('users','vips','currencies'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        if($request->file('image')!="")
        {
            if ($file = $request->file('image')) {
               $name = 'user'.time().$file->getClientOriginalName();
               $file->move('images/users/', $name);
               $input['image'] = $name;
            }
        }
        else
        {
            $input['image']="";
        }
        $input['mobile']= $input['code']. $input['mobile'];
        User::create($input);
        return back()->with('message', 'تمت الاضافة بنجاح');        
    }

  public function getAgentWallets()
  {     $user=Auth::user();
       // جلب المستخدمين التابعين لهذا الوكيل
        $users = DB::table('users')
                   ->select('*')
                   ->where('agent_id', $user->id)
                   ->orderBy('id', 'desc')
                   ->paginate(500);

        // حساب القيم المالية لكل مستخدم
        foreach ($users as &$userItem) {
            // حساب القيم المالية للمستخدم
            $financials = $this->profitService->calculateUserFinancials($userItem->id);

            // دمج القيم المالية مع بيانات المستخدم
            $userItem->financials = $financials;
        }
 return view('backend.users.wallets',compact('users'));
    
  }
    public function showCategory($id)
    {
        $users=DB::table('users')->select('*')->where('role',$id)->orderBy('id', 'desc')->paginate(500);
        $vips=DB::table('vips')->select('*')->orderBy('id', 'desc')->get();
        return view('backend.users.index',compact('users','vips'));
    }
    public function getAgents()
    {   $user=Auth::user();
        $users=DB::table('users')->select('*')->where('agent_id',$user->id)->orderBy('id', 'desc')->paginate(500);
        $vips=DB::table('vips')->select('*')->orderBy('id', 'desc')->get();
     
        $currencies=DB::table('currencies')->select('*')->orderBy('id', 'desc')->get();
        return view('backend.users.index',compact('users','vips','currencies'));
    }
      public function getOtherAgents($id)
    {   $user=Auth::user();
        if($user->role==1)
        { $users=DB::table('users')->select('*')->where('agent_id',$id)->orderBy('id', 'desc')->paginate(500);
        $vips=DB::table('vips')->select('*')->orderBy('id', 'desc')->get();
         
        $currencies=DB::table('currencies')->select('*')->orderBy('id', 'desc')->get();
        return view('backend.users.index',compact('users','vips','currencies'));
        }
     else
     {
       return back();
     }
    }
    public function addBalanceToAgent(Request $request)
    {   $user=Auth::user();
        $agent=User::find($request->agent_id);
        if( $user->role==1 || floatval($request->value)<=$user->balance)
        {
        $user->balance=$user->balance-floatval($request->value);
        $user->save();
        $agent->balance=$agent->balance+floatval($request->value);
        $agent->save();
          
       $order= TransferMoneyFirmOrder::create([
            'user_id' =>$agent->id,
            'value' => floatval($request->value),
            'amount' => floatval($request->value),
            'currency'=>"TRY",
           'status'=>$request->payment_done? "مقبول":"دين" ,
         
        ]);
        Transaction::create([
            'from_user_id' => $user->id,
            'to_user_id' =>$agent->id,
            'amount' => floatval($request->value),
            'payment_done'=>$request->payment_done,
            'order_id'=>$order->id
          
         
        ]);
        return back()->with('message', 'تم اضافة الرصيد بنجاح');
          }
           return back()->with('message', 'رصيدك غير كافي  ');
    }

    
    public function changePassword(Request $request,  $id)
    {
        $user = User::findOrFail($id);
        $input = $request->all();
        $user->password =bcrypt($input['password']);;
      
        $user->save();
        
        return back()->with('message', 'تم التعديل بنجاح');
    }

  public function update(Request $request,  $id)
    {
        $user = User::findOrFail($id);
        $input = $request->all();
        if($request->file('image')!="")
        {
      
        if ($file = $request->file('image')) {
            $name = 'user_'.time().$file->getClientOriginalName();
            $file->move('images/users/', $name);
            $input['image'] = $name;
        }
        }
        else
        {
            $input['image'] =$user['image'];
        }
        $user->update($input);
        
        return back()->with('message', 'تم التعديل بنجاح');
    }

    public function destroy( $id)
    {
        $user= User::findOrFail($id);
        $user->delete();
        return back()->with('message', 'تم الحذف  بنجاح');
    }
}
