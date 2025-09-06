<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * عرض صفحة إعادة تعيين كلمة المرور
     */
    public function showResetForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (!$token || !$email) {
            return redirect('/')->with('error', 'رابط إعادة تعيين كلمة المرور غير صالح.');
        }

        // التحقق من وجود الـ token في قاعدة البيانات
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            return redirect('/')->with('error', 'رابط إعادة تعيين كلمة المرور غير صالح.');
        }

        // التحقق من صحة الـ token
        if (!password_verify($token, $passwordReset->token)) {
            return redirect('/')->with('error', 'رابط إعادة تعيين كلمة المرور غير صالح.');
        }

        // التحقق من انتهاء صلاحية الـ token (60 دقيقة)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            // حذف الـ token المنتهي الصلاحية
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return redirect('/')->with('error', 'انتهت صلاحية رابط إعادة تعيين كلمة المرور. يرجى طلب رابط جديد.');
        }

        return view('auth.passwords.reset', compact('token', 'email'));
    }

    /**
     * معالجة طلب إعادة تعيين كلمة المرور
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'token.required' => 'رمز التحقق مطلوب.',
            'email.required' => 'البريد الإلكتروني مطلوب.',
            'email.email' => 'يرجى إدخال عنوان بريد إلكتروني صالح.',
            'email.exists' => 'البريد الإلكتروني غير مسجل في النظام.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min' => 'يجب ألا تقل كلمة المرور عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        // البحث عن token في قاعدة البيانات
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'رابط إعادة تعيين كلمة المرور غير صالح.']);
        }

        // التحقق من صحة الـ token
        if (!password_verify($request->token, $passwordReset->token)) {
            return back()->withErrors(['email' => 'رابط إعادة تعيين كلمة المرور غير صالح.']);
        }

        // التحقق من انتهاء صلاحية الـ token (60 دقيقة)
        if (Carbon::parse($passwordReset->created_at)->addMinutes(60)->isPast()) {
            // حذف الـ token المنتهي الصلاحية
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return back()->withErrors(['email' => 'انتهت صلاحية رابط إعادة تعيين كلمة المرور. يرجى طلب رابط جديد.']);
        }

        // تحديث كلمة المرور
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // حذف الـ token بعد الاستخدام
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // إبطال جميع الرموز المميزة الحالية للمستخدم
        $user->tokens()->delete();

        return redirect('/')->with('status', 'تم تغيير كلمة المرور بنجاح. يرجى تسجيل الدخول مرة أخرى.');
    }
}
