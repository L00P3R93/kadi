<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">
    <div class="min-h-screen pt-16 px-6">
        <div class="max-w-md mx-auto py-12">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-white text-xl font-bold">⚙️ Sportsbook Admin</h1>
                <a href="{{ route('dashboard') }}" class="text-xs text-gray-500 hover:text-[#f5c542] transition">← Dashboard</a>
            </div>
            <livewire:admin.odds-api-quota-widget />
        </div>
    </div>
    @fluxScripts
</body>
</html>
