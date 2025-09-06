<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نسيان كلمة المرور</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
        }
        .forgot-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .forgot-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .forgot-header {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .forgot-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .forgot-header h2 {
            margin: 0;
            font-weight: 600;
        }
        .forgot-body {
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
        .btn-send {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-send:hover {
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
        .info-box {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #6c757d;
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

        /* Success State Styles */
        .success-container {
            padding: 30px 20px;
        }

        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
            animation: successPulse 2s ease-in-out;
        }

        @keyframes successPulse {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-title {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .success-message {
            color: #6c757d;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .success-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            color: #495057;
            font-size: 14px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-item i {
            color: #59C4BC;
        }

        .success-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-outline-primary {
            border-color: #59C4BC;
            color: #59C4BC;
        }

        .btn-outline-primary:hover {
            background-color: #59C4BC;
            border-color: #59C4BC;
        }

        .btn-outline-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-outline-primary:disabled:hover {
            background-color: transparent;
            border-color: #59C4BC;
            color: #59C4BC;
        }

        /* Custom button styling for success page */
        .success-actions .btn-send {
            background: linear-gradient(45deg, #59C4BC, #637AAE);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .success-actions .btn-send:hover {
            transform: translateY(-2px);
            color: white;
        }

        /* Animation for success container */
        .success-container {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <i class="fas fa-envelope"></i>
                <h2>نسيان كلمة المرور</h2>
                <p class="mb-0">أدخل بريدك الإلكتروني لإرسال رابط الاستعادة</p>
            </div>

            <div class="forgot-body">
                @if (session('status'))
                    <!-- Success State -->
                    <div class="success-container text-center">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="success-title">تم إرسال الرابط بنجاح!</h3>
                        <p class="success-message">
                            لقد تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني.
                            يرجى التحقق من صندوق الوارد وصندوق الرسائل غير المرغوب فيها.
                        </p>
                        <div class="success-info">
                            <div class="info-item">
                                <i class="fas fa-clock me-2"></i>
                                <span>الرابط صالح لمدة 60 دقيقة</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-envelope me-2"></i>
                                <span>تم الإرسال إلى: <strong>{{ session('email') }}</strong></span>
                            </div>
                        </div>
                        <div class="success-actions">
                            <button id="resendBtn" class="btn btn-outline-primary me-2" onclick="resendEmail()" disabled>
                                <i class="fas fa-redo me-2"></i>
                                <span id="resendText">إعادة الإرسال خلال <span id="countdown">60</span>ث</span>
                            </button>
                            <a href="/" class="btn btn-send">
                                <i class="fas fa-home me-2"></i>العودة للرئيسية
                            </a>
                        </div>
                    </div>
                @else

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

                <div class="info-box">
                    <i class="fas fa-info-circle me-2"></i>
                    سنرسل لك رابط إعادة تعيين كلمة المرور على بريدك الإلكتروني المسجل.
                </div>

                <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                    @csrf

                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>البريد الإلكتروني
                        </label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                               placeholder="أدخل بريدك الإلكتروني">
                    </div>

                    <button type="submit" class="btn btn-primary btn-send" id="sendBtn">
                        <i class="fas fa-paper-plane me-2"></i>
                        <span id="btnText">إرسال رابط الاستعادة</span>
                        <span id="btnSpinner" class="d-none">
                            <i class="fas fa-spinner fa-spin me-2"></i>جاري الإرسال...
                        </span>
                    </button>
                </form>

                <div class="back-link">
                    <a href="/">
                        <i class="fas fa-arrow-right me-2"></i>العودة للصفحة الرئيسية
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إضافة loading state عند الإرسال
        document.getElementById('forgotForm').addEventListener('submit', function() {
            const sendBtn = document.getElementById('sendBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            sendBtn.disabled = true;
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
        });

        // التحقق من صحة البريد الإلكتروني
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const email = this.value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (email && !emailRegex.test(email)) {
                    this.setCustomValidity('يرجى إدخال عنوان بريد إلكتروني صالح');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        // Countdown timer for resend button
        let countdownTimer;
        const countdownElement = document.getElementById('countdown');
        const resendBtn = document.getElementById('resendBtn');
        const resendText = document.getElementById('resendText');

        if (countdownElement && resendBtn) {
            let timeLeft = 60;

            countdownTimer = setInterval(function() {
                timeLeft--;
                countdownElement.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    resendBtn.disabled = false;
                    resendText.innerHTML = 'إرسال مرة أخرى';
                    resendBtn.onclick = function() {
                        window.location.href = '{{ route("password.request") }}';
                    };
                }
            }, 1000);
        }

        function resendEmail() {
            if (!resendBtn.disabled) {
                window.location.href = '{{ route("password.request") }}';
            }
        }
    </script>
</body>
</html>
