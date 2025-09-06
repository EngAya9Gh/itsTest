<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            direction: rtl;
            text-align: right;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 30px;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }
        .reset-button:hover {
            transform: translateY(-2px);
            text-decoration: none;
            color: white;
        }
        .alternative-link {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 14px;
            color: #6c757d;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        .icon {
            font-size: 36px;
            background: rgba(255, 255, 255, 0.2);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">🛡️</div>
            <h1>إعادة تعيين كلمة المرور</h1>
        </div>

        <div class="content">
            <div class="greeting">
                @if($userName)
                    مرحباً {{ $userName }},
                @else
                    مرحباً,
                @endif
            </div>

            <div class="message">
                <p>لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك. إذا كنت قد طلبت ذلك، يرجى النقر على الزر أدناه لإعادة تعيين كلمة المرور:</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ $resetLink }}" class="reset-button">
                    إعادة تعيين كلمة المرور
                </a>
            </div>

            <div class="message">
                <p>إذا لم يعمل الزر أعلاه، يمكنك نسخ الرابط التالي ولصقه في متصفحك:</p>
            </div>

            <div class="alternative-link">
                {{ $resetLink }}
            </div>

            <div class="warning">
                <strong>تنبيه أمني:</strong>
                <ul style="margin: 10px 0; padding-right: 20px;">
                    <li>هذا الرابط صالح لمدة 60 دقيقة فقط</li>
                    <li>إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد</li>
                    <li>لا تشارك هذا الرابط مع أي شخص آخر</li>
                </ul>
            </div>

            <div class="message">
                <p>إذا كنت تواجه أي مشاكل، يرجى التواصل مع فريق الدعم الفني.</p>
                <p>شكراً لك،<br>فريق {{ config('app.name') }}</p>
            </div>
        </div>

        <div class="footer">
            <p>هذا بريد إلكتروني تلقائي، يرجى عدم الرد عليه.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} - جميع الحقوق محفوظة</p>
        </div>
    </div>
</body>
</html>
