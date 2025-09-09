<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
        }
        .reset-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .reset-header {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .reset-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .reset-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .reset-body {
            padding: 40px;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #59C4BC;
            box-shadow: 0 0 0 0.2rem rgba(89, 196, 188, 0.25);
        }
        .btn-reset {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
        }
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
        }
        .password-requirements {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 14px;
            color: #6c757d;
        }
        .password-requirements ul {
            margin: 0;
            padding-right: 20px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #59C4BC;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="fas fa-key"></i>
                <h2>إعادة تعيين كلمة المرور</h2>
                <p class="mb-0">أدخل كلمة المرور الجديدة</p>
            </div>

            <div class="reset-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="type" value="{{ $type ?? 'admin' }}">
                    <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
                    <input type="hidden" name="redirect_to" value="{{ request()->query('redirect_to') }}">

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                        </label>
                        <input id="email" type="email" class="form-control" value="{{ $email }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>كلمة المرور الجديدة
                        </label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" required autocomplete="new-password" placeholder="أدخل كلمة المرور الجديدة">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-lock me-2"></i>تأكيد كلمة المرور
                        </label>
                        <input id="password_confirmation" type="password" class="form-control"
                               name="password_confirmation" required autocomplete="new-password"
                               placeholder="أعد إدخال كلمة المرور">
                    </div>

                    <div class="password-requirements">
                        <strong>متطلبات كلمة المرور:</strong>
                        <ul>
                            <li>يجب ألا تقل عن 8 أحرف</li>
                            <li>يُفضل استخدام مزيج من الأحرف والأرقام والرموز</li>
                            <li>تجنب استخدام معلومات شخصية واضحة</li>
                        </ul>
                    </div>

                    <button type="submit" class="btn btn-primary btn-reset">
                        <i class="fas fa-key me-2"></i>إعادة تعيين كلمة المرور
                    </button>
                </form>

                <div class="back-link">
                    <a href="/">
                        <i class="fas fa-arrow-right me-2"></i>العودة للصفحة الرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // التحقق من تطابق كلمات المرور
        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;

            if (password !== confirmation && confirmation.length > 0) {
                this.setCustomValidity('كلمات المرور غير متطابقة');
            } else {
                this.setCustomValidity('');
            }
        });

        // التحقق من قوة كلمة المرور
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;

            if (password.length < 8) {
                this.setCustomValidity('يجب ألا تقل كلمة المرور عن 8 أحرف');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
