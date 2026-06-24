<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-900 text-zinc-800 dark:text-zinc-200 antialiased flex flex-col pb-20 md:pb-0">
    <!-- Desktop Header / Navigation -->
    <header class="sticky top-0 z-40 w-full border-b border-zinc-200/80 bg-white/80 backdrop-blur-md dark:border-zinc-800 dark:bg-zinc-950/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg text-zinc-900 dark:text-white" wire:navigate>
                <flux:icon icon="shopping-bag" class="h-6 w-6 text-zinc-900 dark:text-white" />
                <span>UMKM Store</span>
            </a>
            
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-sm font-medium text-zinc-900 dark:text-white" wire:navigate>
                    Katalog
                </a>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                        Admin Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                        Masuk Admin
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-zinc-200 bg-white/95 backdrop-blur-md py-2 md:hidden dark:border-zinc-800 dark:bg-zinc-950/95 shadow-lg">
        <div class="flex items-center justify-around">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-xs font-medium text-zinc-950 dark:text-white">
                <flux:icon icon="shopping-bag" class="h-6 w-6" />
                <span>Katalog</span>
            </a>
            
            @auth
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    <flux:icon icon="home" class="h-6 w-6" />
                    <span>Admin</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                    <flux:icon icon="arrow-right-start-on-rectangle" class="h-6 w-6" />
                    <span>Masuk</span>
                </a>
            @endauth
        </div>
    </div>

    @fluxScripts
</body>
</html>
