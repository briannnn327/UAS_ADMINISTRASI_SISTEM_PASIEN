<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password — Klinik Gen-Z</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1565C0', light: '#1976D2', pale: '#E3F2FD', dark: '#0D47A1' },
                        accent: { DEFAULT: '#00ACC1', light: '#E0F7FA' },
                    },
                    fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui'] },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body { 
            background: linear-gradient(135deg, #E3F2FD, #B3E5FC); 
            font-family: 'Poppins', sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 1.5rem; 
            margin: 0; 
        }
        .forgot-card { 
            background: white; 
            border-radius: 2rem; 
            box-shadow: 0 20px 60px rgba(21, 101, 192, 0.15); 
            padding: 2.5rem; 
            width: 100%; 
            max-width: 440px; 
            transition: transform 0.2s; 
        }
        .forgot-card:hover { transform: translateY(-4px); }
        .input-group { position: relative; margin-bottom: 1.25rem; }
        .input-group .icon { 
            position: absolute; 
            left: 1rem; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #94a3b8; 
            font-size: 1rem; 
        }
        .input-group input { 
            width: 100%; 
            padding: 0.75rem 1rem 0.75rem 2.8rem; 
            border: 1.5px solid #e2e8f0; 
            border-radius: 1rem; 
            font-size: 0.9rem; 
            transition: border-color 0.2s, box-shadow 0.2s; 
            background: #f8fafc; 
            outline: none; 
        }
        .input-group input:focus { 
            border-color: #1565C0; 
            box-shadow: 0 0 0 4px rgba(21, 101, 192, 0.1); 
            background: white; 
        }
        .btn-primary { 
            width: 100%; 
            background: linear-gradient(135deg, #1565C0, #0D47A1); 
            color: white; 
            padding: 0.85rem; 
            border: none; 
            border-radius: 1rem; 
            font-weight: 700; 
            font-size: 1rem; 
            cursor: pointer; 
            transition: all 0.2s; 
            box-shadow: 0 8px 20px rgba(21, 101, 192, 0.3); 
        }
        .btn-primary:hover { 
            transform: scale(1.01); 
            box-shadow: 0 12px 28px rgba(21, 101, 192, 0.4); 
        }
        .btn-primary:active { transform: scale(0.98); }
    </style>
</head>
<body>

    <div class="forgot-card">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center text-white text-2xl mx-auto mb-4 shadow-lg shadow-yellow-200">
                <i class="fa-solid fa-key"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900">Lupa Password</h1>
            <p class="text-gray-400 text-sm mt-1">Masukkan email terdaftar Anda</p>
        </div>

        <!-- Flash / Error Messages -->
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <!-- Form -->
        <form method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="input-group">
                <span class="icon"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" placeholder="Alamat Email" value="{{ old('email') }}" required autofocus>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane mr-2"></i>Kirim Instruksi Reset
            </button>
        </form>

        <p class="text-center text-sm text-gray-400 mt-6">
            <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">
                <i class="fa-solid fa-arrow-left mr-1"></i>Kembali ke Login
            </a>
        </p>

    </div>

</body>
</html>