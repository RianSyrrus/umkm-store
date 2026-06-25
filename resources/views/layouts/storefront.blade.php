<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-stone-50 dark:bg-stone-950 text-stone-900 dark:text-stone-100 antialiased flex flex-col pb-20 md:pb-0">
    <!-- Desktop Header / Navigation -->
    <header class="sticky top-0 z-40 w-full border-b border-stone-200/80 bg-white/80 backdrop-blur-md dark:border-stone-800 dark:bg-stone-950/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl text-stone-950 dark:text-white tracking-tight" wire:navigate>
                <flux:icon icon="shopping-bag" class="h-6 w-6 text-accent" />
                <span aria-hidden="true">UMKM <span class="text-accent">Store</span></span>
                <span class="sr-only">UMKM Store</span>
            </a>
            
            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-accent' : 'text-stone-600 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white' }}" wire:navigate>
                    Katalog
                </a>
                
                <a href="{{ route('home.cart') }}" class="relative text-sm font-semibold {{ request()->routeIs('home.cart') ? 'text-accent' : 'text-stone-600 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white' }} flex items-center gap-1.5" wire:navigate>
                    <flux:icon icon="shopping-cart" class="h-5 w-5" />
                    <span>Keranjang</span>
                    @php $cartCount = (new \App\Services\Cart\CartService)->get()->sum('quantity'); @endphp
                    @if ($cartCount > 0)
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-accent text-[10px] font-bold text-white shadow-sm transition-transform duration-350 ease-out-quint">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-stone-600 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white" wire:navigate>
                        Admin Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-stone-600 hover:text-stone-950 dark:text-stone-400 dark:hover:text-white" wire:navigate>
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
    <div class="fixed bottom-0 left-0 right-0 z-40 border-t border-stone-200 bg-white/95 backdrop-blur-md py-2.5 md:hidden dark:border-stone-850 dark:bg-stone-950/95 shadow-lg">
        <div class="flex items-center justify-around">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('home') ? 'text-accent' : 'text-stone-500 dark:text-stone-400' }}">
                <flux:icon icon="squares-2x2" class="h-5.5 w-5.5" />
                <span>Katalog</span>
            </a>

            <a href="{{ route('home.cart') }}" class="relative flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('home.cart') ? 'text-accent' : 'text-stone-500 dark:text-stone-400' }}" wire:navigate>
                <flux:icon icon="shopping-cart" class="h-5.5 w-5.5" />
                <span>Keranjang</span>
                @php $cartCount = (new \App\Services\Cart\CartService)->get()->sum('quantity'); @endphp
                @if ($cartCount > 0)
                    <span class="absolute -top-1 right-2.5 flex h-4 w-4 items-center justify-center rounded-full bg-accent text-[9px] font-bold text-white shadow-sm">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
            
            @auth
                <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('admin.*') ? 'text-accent' : 'text-stone-500 dark:text-stone-400' }}">
                    <flux:icon icon="home" class="h-5.5 w-5.5" />
                    <span>Admin</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex flex-col items-center gap-1 text-xs font-semibold {{ request()->routeIs('login') ? 'text-accent' : 'text-stone-500 dark:text-stone-400' }}">
                    <flux:icon icon="arrow-right-start-on-rectangle" class="h-5.5 w-5.5" />
                    <span>Masuk</span>
                </a>
            @endauth
        </div>
    </div>

    @fluxScripts
</body>
</html>
