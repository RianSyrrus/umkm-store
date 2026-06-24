<x-layouts::app :title="__('Dashboard Admin')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                <flux:heading size="lg">Omzet</flux:heading>
                <flux:text class="mt-2 text-2xl font-bold">Rp0</flux:text>
            </div>
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                <flux:heading size="lg">Pesanan</flux:heading>
                <flux:text class="mt-2 text-2xl font-bold">0</flux:text>
            </div>
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm">
                <flux:heading size="lg">Stok Menipis</flux:heading>
                <flux:text class="mt-2 text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 bg-white dark:bg-neutral-800 shadow-sm">
            <flux:heading size="xl">Selamat Datang di Admin UMKM Store</flux:heading>
            <flux:text class="mt-2">Gunakan sidebar untuk mengelola toko Anda.</flux:text>
        </div>
    </div>
</x-layouts::app>
