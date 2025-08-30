<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Currency;
use Carbon\Carbon;
use PragmaRX\Google2FA\Google2FA;
use App\Models\AppOrder;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Auth;
  use App\Mail\LoginEbank;
use Illuminate\Support\Facades\Mail;   
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
     
use App\Models\TweetcellOrder; // إذا كنت تستخدم الـ Model لجدول tweetcell_orders
use App\Models\ServiceOrder; // إذا كنت تستخدم الـ Model لجدول service_orders


class ApiUserController extends Controller 
{  
   public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();

    // إنشاء رابط إعادة تعيين كلمة المرور
    $token = Str::random(60);
    DB::table('password_resets')->updateOrInsert(
        ['email' => $user->email],
        ['token' => $token, 'created_at' => now()]
    );

    $resetLink = url('/reset-password?token=' . $token);

    // إرسال البريد الإلكتروني
    Mail::to($user->email)->send(new PasswordReset($resetLink));

    return response()->json(['success' => true, 'message' => 'تم إرسال رابط استعادة كلمة المرور.']);
}
    public function checkEmailForReset(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    $user = User::where('email', $request->email)->first();
      if($user)
   return response()->json([
            'user' => $user,
        ]);
      else
          return response()->json([
            'user' => false,
        ]);
    }
    public function status(Request $request)
    {
        $user = Auth::user();
        return response()->json(['enabled' => $user->two_factor_enabled]);
    }
    
    public function enable(Request $request)
    {
        $user = Auth::user();
        $google2fa = new Google2FA();
    
        $user->google2fa_secret = $google2fa->generateSecretKey();
      //  $user->two_factor_enabled = true;
       $user->save();
    
     
    // إنشاء QR Code URL
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'اسم التطبيق',      // اسم التطبيق
        $user->email,        // بريد المستخدم
        $user->google2fa_secret // المفتاح السري
    );

    // إعداد QR Code باستخدام مكتبة BaconQrCode
    $renderer = new ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
        new SvgImageBackEnd()
    );
    $writer = new Writer($renderer);

    // إنشاء QR Code بصيغة SVG
    $qrCodeSvg = $writer->writeString($qrCodeUrl);
        return response()->json([
            'qrCode' => $qrCodeSvg,
            'secretKey' => $user->google2fa_secret
        ]);
    }
    
  

   
     public function verifyOtp(Request  $request)
     { 
     
       $user = auth()->user();
        $google2fa = new Google2FA();

       $valid = $google2fa->verifyKey(
        $user->google2fa_secret, 
        $request->input('otp')
       );

        if ($valid) {
            $user->two_factor_enabled =  $request->input('isEnabling');
            $user->save();
             // إبطال جميع الرموز الحالية
    $user->tokens()->delete();

 

            return response()->json([
                'success' => true ,'message' => 'تم تفعيل المصادقة الثنائية. الرجاء تسجيل الدخول مجددًا.'   ]);
        } else {
          //  $user->two_factor_enabled = false;
            return response()->json([
                'success' => false  ]);        }
     }
     

      public function verifyOtpLogin(Request $request)
{
        $request->validate([
            'otp' => 'required|numeric',
            'name' => 'required|string',
        ]);

        $user = User::where('name', $request->name)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $google2fa = new Google2FA();

        if ($google2fa->verifyKey($user->google2fa_secret, $request->otp)) {
            $token = $user->createToken('auth_token')->accessToken;
            return response()->json(['success' => true, 'token' => $token], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح.'], 401);
        }
}
public function qrCreate()
{
  $user = auth()->user();
  

    // تهيئة مكتبة Google2FA
    $google2fa = new Google2FA();
    $user->google2fa_secret = $secret;
    $user->save();
    // إنشاء QR Code URL
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        'اسم التطبيق',      // اسم التطبيق
        $user->email,        // بريد المستخدم
        $user->google2fa_secret // المفتاح السري
    );

    // إعداد QR Code باستخدام مكتبة BaconQrCode
    $renderer = new ImageRenderer(
        new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
        new SvgImageBackEnd()
    );
    $writer = new Writer($renderer);

    // إنشاء QR Code بصيغة SVG
    $qrCodeSvg = $writer->writeString($qrCodeUrl);

    // إرجاع QR Code والمفتاح السري كاستجابة JSON
    return response()->json([
        'qrCode' => $qrCodeSvg,              // QR Code بصيغة SVG
        'secretKey' => $user->google2fa_secret ,
        'user_id'=>$user->id,
    ]);
}
  public function send($role)
    {    $user=Auth::user();
         if($role=="A")
          $agents=DB::table('users')->select('*')->where('agent_id', $user->id)->where('role', 2)->get();
        else
          $agents=DB::table('users')->select('*')->where('agent_id', $user->id)->whereIn('role', [3, 4])->get();
        return response()->json(['agents'=> $agents]);  

    }
 public function getAgents()
        {    $user=auth()->user();
        
            $agents=DB::table('users')->select('*')->where('agent_id',1)->get();
        
            return response()->json(['agents'=> $agents]);  

    } 
 public function getProfit()
        {    $user=auth()->user();
             if($user->balance_profit>=100)
             { $user->balance +=$user->balance_profit;
               $user->balance_profit=0;
               $user->save();
               return response()->json(['message'=> 'تم الاضافة الى رصيدك']);  
             }
         else
         { return response()->json(['message'=> false]);
         }

     } 
   public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'unique:users', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'name.required' => 'الاسم مطلوب.',
                'name.string' => 'يجب أن يكون الاسم نصاً.',
                'name.unique' => 'الاسم مستخدم بالفعل، يرجى اختيار اسم آخر.',
                'name.max' => 'يجب ألا يزيد طول الاسم عن 255 حرفًا.',
                'email.required' => 'البريد الإلكتروني مطلوب.',
                'email.string' => 'يجب أن يكون البريد الإلكتروني نصًا.',
                'email.email' => 'يرجى إدخال عنوان بريد إلكتروني صالح.',
                'email.max' => 'يجب ألا يزيد طول البريد الإلكتروني عن 255 حرفًا.',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل، يرجى اختيار بريد آخر.',
                'password.required' => 'كلمة المرور مطلوبة.',
                'password.string' => 'يجب أن تكون كلمة المرور نصية.',
                'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            ]);
    
                // إنشاء المستخدم
                $input = $request->all();
                $input['mobile'] = $input['code'] . $input['mobile'];
                $input['password'] = bcrypt($input['password']);
                $user = User::create($input);
                // تسجيل الدخول تلقائيًا
                Auth::login($user);  $token = $user->createToken('auth_token')->accessToken;
               return response()->json(['token' => $token,'user'=>$user], 200);
           
    } catch (\Illuminate\Validation\ValidationException $e) 
           {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->errors(),
                ], 422);
            }
    }
    
    
    public function storeAgent(Request $request,$agent)
    {

      try {
            $request->validate([
                'name' => ['required', 'string', 'unique:users', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ], [
                'name.required' => 'الاسم مطلوب.',
                'name.string' => 'يجب أن يكون الاسم نصاً.',
                'name.unique' => 'الاسم مستخدم بالفعل، يرجى اختيار اسم آخر.',
                'name.max' => 'يجب ألا يزيد طول الاسم عن 255 حرفًا.',
                'email.required' => 'البريد الإلكتروني مطلوب.',
                'email.string' => 'يجب أن يكون البريد الإلكتروني نصًا.',
                'email.email' => 'يرجى إدخال عنوان بريد إلكتروني صالح.',
                'email.max' => 'يجب ألا يزيد طول البريد الإلكتروني عن 255 حرفًا.',
                'email.unique' => 'البريد الإلكتروني مستخدم بالفعل، يرجى اختيار بريد آخر.',
                'password.required' => 'كلمة المرور مطلوبة.',
                'password.string' => 'يجب أن تكون كلمة المرور نصية.',
                'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
                'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            ]);
    
           $input = $request->all();
           $name = explode('_', $agent)[0];

           $type = explode('_', $agent)[1];
        if ( !in_array($type, ['A','B', 'C'])) {
            return response()->json(['error' => true]);
        }
          $user=User::where('name',$name)->first();
           // فحص agent_id بناءً على النوع (type)
        if ($type == 'A' && $user->role != 1) { 
          return response()->json(['error' => true]);
        } elseif ($type == 'B' && !in_array($user->role, [1, 2])) {
          return response()->json(['error' => true]);
        } elseif ($type == 'C' && !in_array($user->role, [1, 2, 3])) {
          return response()->json(['error' => true]);      }
          if($type=='A' )
          {    $input['role'] =2;
          }
          else  if($type=='B')
          {   $input['role'] =3;
          }
          else   
          $input['role'] =4;
        
          $input['vip_id']=$input['role'];
            $input['mobile'] = $input['code'] . $input['mobile'];
            $input['password'] = bcrypt($input['password']);
            $input['agent_id'] =$user->id;
        
            $user = User::create($input); 
            Auth::login($user);
            $token = $user->createToken('auth_token')->accessToken;
            return response()->json(['token' => $token,'user'=>$user], 200);
           
                // رد بنجاح
   
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'errors' => $e->errors(),
                    ], 422);
                }
   
    }
    public function authCheck(Request $request)
    {  
      
       if (auth()->check()) {
      
        return response()->json(['authenticated' => true ,'auth'=>Auth::user()], 200);
       }
      return response()->json(['authenticated' => false], 200);
  
    }
    public function login(Request $request)
    {   //  return response()->json("hello");
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);
    
      
        if (Auth::attempt($credentials)) {
           
            $user = Auth::user();
            if($user->two_factor_enabled)
            {
                return response()->json(['two_factor_enabled' => true,'name'=>$user->name]);
            }
            else{
            $token = $user->createToken('auth_token')->accessToken;
            return response()->json(['token' => $token,'user'=>$user], 200);
            }
        }
        else
        {
        // إذا فشل تسجيل الدخول، نقوم بالتحقق إذا كان البريد الإلكتروني موجودًا في النظام
        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            // إذا كان المستخدم موجودًا ولكن كلمة المرور غير صحيحة
            return response()->json(['message' => 'كلمة المرور غير صحيحة'], 401);
        } else {
            // إذا كان البريد الإلكتروني غير موجود في النظام
            return response()->json(['message' => 'البريد الإلكتروني غير موجود'], 401);
        }
    }
    }

    public function getLoggedInUser()
    {
        return response()->json(Auth::user());
    }

public function findOrderByUuid($uuid)
{
    // البحث في جدول tweetcell_orders

    $tweetcellOrder = DB::table('tweetcell_sections')
        ->join('tweetcells', 'tweetcell_sections.id', '=', 'tweetcells.section_id')
        ->join('tweetcell_orders as orders', 'tweetcells.id', '=', 'orders.tweetcell_id')
        ->where('orders.uuid', $uuid)
        ->select(  
             'tweetcells.id',
            'tweetcells.name',
            'orders.price',
             'orders.player_no',
             'orders.count',
              'orders.status',
              'orders.created_at',
               'orders.uuid',
        )
        ->first();
  
  
  
  
  
  
    // إذا تم العثور على السجل في tweetcell_orders
    if ($tweetcellOrder) {
        return response()->json([
            'table' => 'tweetcell_orders',
            'order' => $tweetcellOrder
        ]);
    }

  
  
   $serviceOrder = DB::table('services')
        ->join('service_orders as orders', 'services.id', '=', 'orders.service_id')
        ->where('orders.uuid', $uuid)
        ->select(
            'services.id',
            'services.name',
            'orders.price',
            'orders.ime',
            'orders.username',
            'orders.password',
            'orders.email',
            'orders.count',
            'orders.note',
            'orders.status',
            'orders.created_at',
             'orders.uuid',
            'services.image_url',
            DB::raw("'Service' as type"),)->first();

    // إذا تم العثور على السجل في service_orders
    if ($serviceOrder) {
        return response()->json([
            'table' => 'service_orders',
            'order' => $serviceOrder
        ]);
    }
 
  
  $tweetcellKontorOrder = DB::table('tweetcell_kontor_sections')
    ->join('tweetcell_kontors', 'tweetcell_kontor_sections.id', '=', 'tweetcell_kontors.section_id')
    ->join('tweetcell_kontor_orders as orders', 'tweetcell_kontors.id', '=', 'orders.tweetcell_kontor_id')
    ->where('orders.uuid', $uuid)
    ->select(
        'tweetcell_kontors.id',
        'tweetcell_kontors.name',
        'orders.price',
        'orders.mobile',
        'orders.status',
        'orders.created_at',
        'orders.uuid',
    )
    ->first();

  
  
  
  
  
  
    // إذا تم العثور على السجل في tweetcell_orders
    if ($tweetcellKontorOrder) {
        return response()->json([
            'order' => $tweetcellKontorOrder
        ]);
    }

  
  
   $faturaOrder = DB::table('faturas')
        ->join('fatura_orders as orders', 'faturas.id', '=', 'orders.fatura_id')
        ->where('orders.uuid', $uuid)
        ->select(
            'faturas.id',
            'faturas.name',
            'orders.price',
            'orders.fatura_no',
            'orders.note',
            'orders.status',
            'orders.created_at',
             'orders.uuid',
            'services.image_url',
            DB::raw("'Fatura' as type"),)->first();

    // إذا تم العثور على السجل في service_orders
    if ($faturaOrder) {
        return response()->json([
            'order' => $faturaOrder
        ]);
    }

    // إذا لم يتم العثور على أي سجل في كلا الجدولين
    return response()->json(['message' => 'Order not found'], 404);
}
    
 


  public function update(Request $request,  $id)
    { 
        try {
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
            $input['password'] = bcrypt($input['password']);
            $user->update($input);
            return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
            ]);
        }
         catch(\Exception $e) 
        {
            return response()->json(['message'=>'حدث خطا أثناء محاولة تعديل المعلومات']);

        }
    }
public function userCurrency()
{
    if (auth()->check()) {
        $currencyId = auth()->user()->preferred_currency_id;

        try {
            $curr = Currency::findOrFail($currencyId);
            return response()->json(['currency' => $curr->name]);
        } catch (\Exception $e) {
            // في حال ID غير صالح أو لم يجد العملة
            return response()->json(['currency' => 'TL']);
        }
    }

    return response()->json(['currency' => 'TL']);
}

}