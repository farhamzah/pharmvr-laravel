<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrative Portal | PharmVR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Orbitron:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --enterprise-blue: #00E5FF;
            --portal-bg: #030508;
            --console-surface: #0A0F14;
            --control-edge: rgba(255, 255, 255, 0.08);
            --input-focus: rgba(0, 229, 255, 0.15);
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--portal-bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #FFFFFF;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 10%, rgba(0, 229, 255, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 90%, rgba(0, 229, 255, 0.02) 0%, transparent 40%),
                linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,229,255,0.02) 100%);
        }

        .auth-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 40px;
            z-index: 10;
        }

        /* Top Brand Strip */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--control-edge);
            padding-bottom: 24px;
        }

        .brand-identity {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .brand-logo {
            height: 56px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 10px rgba(0, 229, 255, 0.2));
        }

        .system-title {
            display: flex;
            flex-direction: column;
        }

        .system-title h1 {
            margin: 0;
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }

        .system-title span {
            font-size: 10px;
            font-weight: 800;
            color: var(--enterprise-blue);
            text-transform: uppercase;
            letter-spacing: 0.4em;
            opacity: 0.8;
            margin-top: 4px;
        }

        .security-indicator {
            text-align: right;
        }

        .security-indicator p {
            margin: 0;
            font-size: 9px;
            font-weight: 700;
            color: #455A64;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }

        .security-indicator .status {
            color: var(--enterprise-blue);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 6px;
        }

        /* Console Body */
        .console-shell {
            display: grid;
            grid-template-columns: 1fr 440px;
            background: var(--console-surface);
            border: 1px solid var(--control-edge);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 50px 100px -30px rgba(0, 0, 0, 0.9);
        }

        .info-pane {
            padding: 60px;
            background-image: 
                linear-gradient(45deg, rgba(0,0,0,0.4) 0%, transparent 100%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2300e5ff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2v-4h4v-2H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            border-right: 1px solid var(--control-edge);
        }

        .info-pane h2 {
            font-size: 36px;
            font-weight: 800;
            margin: 0 0 16px;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .info-pane p {
            font-size: 15px;
            color: #90A4AE;
            line-height: 1.6;
            margin: 0;
            max-width: 400px;
        }

        .auth-pane {
            padding: 60px;
            background: rgba(255, 255, 255, 0.01);
            display: flex;
            flex-direction: column;
        }

        .form-header {
            margin-bottom: 40px;
        }

        .form-header h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-header p {
            font-size: 12px;
            color: #607D8B;
            margin: 8px 0 0;
            font-weight: 500;
        }

        /* Inputs */
        .control-field {
            margin-bottom: 24px;
        }

        .control-label {
            display: block;
            font-size: 10px;
            font-weight: 900;
            color: #78909C;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-bottom: 10px;
            padding-left: 4px;
        }

        .input-wrapper {
            position: relative;
            background: #080C10;
            border: 1px solid var(--control-edge);
            border-radius: 12px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            padding: 0 18px;
        }

        .input-wrapper:focus-within {
            border-color: var(--enterprise-blue);
            background: var(--input-focus);
            box-shadow: 0 0 20px rgba(0, 229, 255, 0.05);
        }

        .input-wrapper input {
            width: 100%;
            background: transparent;
            border: none;
            color: #FFFFFF;
            padding: 18px 0;
            font-size: 14px;
            font-weight: 600;
            outline: none;
            margin-left: 14px;
        }

        .input-wrapper i {
            color: #546E7A;
            transition: color 0.3s;
        }

        .input-wrapper:focus-within i {
            color: var(--enterprise-blue);
        }

        /* Actions */
        .auth-submit {
            background: var(--enterprise-blue);
            color: #030508;
            border: none;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            font-weight: 900;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 16px;
        }

        .auth-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0, 229, 255, 0.3);
            background: #FFFFFF;
        }

        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 24px;
        }

        .remember-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .remember-toggle input {
            cursor: pointer;
        }

        .remember-toggle span {
            font-size: 11px;
            font-weight: 700;
            color: #546E7A;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .forgot-link {
            font-size: 11px;
            font-weight: 800;
            color: var(--enterprise-blue);
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: opacity 0.3s;
        }

        .forgot-link:hover {
            opacity: 0.7;
        }

        /* Utility */
        .error-alert {
            background: rgba(255, 82, 82, 0.1);
            border: 1px solid rgba(255, 82, 82, 0.2);
            color: #FF5252;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Autofill override */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px #080C10 inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        @media (max-width: 1024px) {
            .console-shell {
                grid-template-columns: 1fr;
            }
            .info-pane {
                display: none;
            }
            .auth-container {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Brand Header Section -->
        <header class="brand-header">
            <div class="brand-identity">
                <img src="{{ asset('images/logo.png') }}" alt="PharmVR Logo" class="brand-logo">
                <div class="system-title">
                    <h1>PharmVR Admin</h1>
                    <span>Authorized Personnel Only</span>
                </div>
            </div>
            <div class="security-indicator">
                <p>Access Gateway</p>
                <div class="status">
                    <i data-lucide="shield-check" style="width: 13px; height: 13px;"></i>
                    SECURE CONNECTION
                </div>
            </div>
        </header>

        <!-- Main Console Layout -->
        <main class="console-shell">
            <!-- Left Info Pane (Desktop Only) -->
            <div class="info-pane">
                <div style="margin-bottom: auto;">
                    <p style="font-size: 10px; font-weight: 800; color: var(--enterprise-blue); text-transform: uppercase; letter-spacing: 0.3em; margin-bottom: 24px;">Systems Protocol</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px;">
                        <div>
                            <p style="font-size: 9px; color: #607D8B; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Portal Status</p>
                            <p style="font-size: 14px; font-weight: 600; color: #FFFFFF; display: flex; align-items: center; gap: 8px;">
                                <span style="width: 6px; height: 6px; background: #00E5FF; border-radius: 50%; display: inline-block;"></span>
                                Online
                            </p>
                        </div>
                        <div>
                            <p style="font-size: 9px; color: #607D8B; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Global Uptime</p>
                            <p style="font-size: 14px; font-weight: 600; color: #FFFFFF;">99.98%</p>
                        </div>
                        <div>
                            <p style="font-size: 9px; color: #607D8B; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Auth Type</p>
                            <p style="font-size: 14px; font-weight: 600; color: #FFFFFF;">Personnel</p>
                        </div>
                        <div>
                            <p style="font-size: 9px; color: #607D8B; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">System ID</p>
                            <p style="font-size: 14px; font-weight: 600; color: #FFFFFF;">PH-VR-M1</p>
                        </div>
                    </div>
                </div>
                
                <h2 style="font-size: 32px; letter-spacing: -0.02em;">Administrative Portal</h2>
                <p style="color: #B0BEC5; font-size: 14px;">Governance console for PharmVR learning modules and neural telemetry oversight. Secure authentication required for access to internal data streams and audit modules.</p>
            </div>

            <!-- Right Auth Pane -->
            <div class="auth-pane">
                <div class="form-header">
                    <h3>Administrative Sign-in</h3>
                    <p>Enter your internal credentials to access the console.</p>
                </div>

                @if($errors->any())
                <div class="error-alert">
                    <i data-lucide="shield-alert"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                @endif

                <form action="{{ url('admin/login') }}" method="POST">
                    @csrf
                    
                    <div class="control-field">
                        <label class="control-label">Email Address</label>
                        <div class="input-wrapper">
                            <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="admin@pharmvr.internal">
                        </div>
                    </div>

                    <div class="control-field">
                        <label class="control-label">Password</label>
                        <div class="input-wrapper">
                            <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                            <input type="password" name="password" id="password" required placeholder="••••••••">
                            <i data-lucide="eye" id="togglePassword" style="cursor: pointer; width: 18px; opacity: 0.5;"></i>
                        </div>
                    </div>

                    <div class="auth-options">
                        <label class="remember-toggle">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Keep me signed in</span>
                        </label>
                        <a href="#" class="forgot-link" style="font-weight: 600; opacity: 0.5;">Forgot Password?</a>
                    </div>

                    <button type="submit" class="auth-submit">
                        Authenticate Access
                    </button>
                </form>

                <div style="margin-top: auto; padding-top: 40px; text-align: center; border-top: 1px solid var(--control-edge);">
                    <p style="font-size: 11px; color: #78909C; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">
                        System monitoring active. Unauthorized access is prohibited.
                    </p>
                </div>
            </div>
        </main>

        <!-- Layout Footer -->
        <footer style="text-align: center; opacity: 0.3;">
            <p style="font-size: 10px; font-weight: 700; color: #78909C; letter-spacing: 0.1em; text-transform: uppercase;">
                &copy; {{ date('Y') }} PharmVR Systems. All administrative rights reserved.
            </p>
        </footer>
    </div>
    
    <script>
        lucide.createIcons();

        // Use event delegation on the wrapper to handle clicks even if Lucide replaces the icon element
        const passwordWrapper = document.querySelector('.input-wrapper:has(#password)');
        const passwordInput = document.querySelector('#password');

        passwordWrapper.addEventListener('click', function (e) {
            // Check if clicking the eye icon
            const toggleBtn = e.target.closest('#togglePassword');
            if (!toggleBtn) return;

            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Update the data-lucide attribute for the next render
            toggleBtn.setAttribute('data-lucide', type === 'password' ? 'eye' : 'eye-off');
            
            // Re-render only the icons within this wrapper or globally
            lucide.createIcons();
        });
    </script>
</body>
</html>
