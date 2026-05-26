<nav x-data="{ open: false }" class="bg-zinc-900 border-b border-zinc-800 text-zinc-100 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="text-amber-500 font-extrabold text-2xl tracking-widest font-mono">MANAKE</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-sans hidden sm:inline-block">V2</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-zinc-300 hover:text-amber-500 border-transparent hover:border-amber-500">
                        Home
                    </x-nav-link>
                    <x-nav-link :href="route('catalog')" :active="request()->routeIs('catalog')" class="text-zinc-300 hover:text-amber-500 border-transparent hover:border-amber-500">
                        Katalog Alat
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')" class="text-zinc-300 hover:text-amber-500 border-transparent hover:border-amber-500 flex items-center gap-1.5">
                            Keranjang
                            @php
                                $cartCount = app(\App\Services\CartService::class)->count(Auth::user());
                            @endphp
                            @if($cartCount > 0)
                                <span class="bg-amber-500 text-zinc-950 text-[10px] font-bold px-1.5 py-0.2 rounded-full">{{ $cartCount }}</span>
                            @endif
                        </x-nav-link>
                        <x-nav-link :href="route('checkout.index')" :active="request()->routeIs('checkout.index')" class="text-zinc-300 hover:text-amber-500 border-transparent hover:border-amber-500">
                            Checkout
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown / Auth triggers -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-zinc-800 text-sm leading-4 font-medium rounded-sm text-zinc-300 bg-zinc-900 hover:text-amber-500 hover:border-amber-500/40 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content" class="bg-zinc-900 border border-zinc-800">
                            <x-dropdown-link :href="route('profile.edit')" class="text-zinc-300 hover:bg-zinc-800 hover:text-amber-500">
                                Profil Saya
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        class="text-zinc-300 hover:bg-zinc-800 hover:text-amber-500"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    Keluar (Logout)
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm text-zinc-300 hover:text-amber-500 font-medium transition duration-150">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="text-sm bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold px-4 py-1.5 rounded-sm transition duration-150">
                            Daftar
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-zinc-400 hover:text-amber-500 hover:bg-zinc-800 focus:outline-none transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-zinc-900 border-t border-zinc-850">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="text-zinc-300 hover:text-amber-500">
                Home
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('catalog')" :active="request()->routeIs('catalog')" class="text-zinc-300 hover:text-amber-500">
                Katalog Alat
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.index')" class="text-zinc-300 hover:text-amber-500 flex items-center justify-between">
                    <span>Keranjang</span>
                    @if($cartCount > 0)
                        <span class="bg-amber-500 text-zinc-950 text-xs font-bold px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('checkout.index')" :active="request()->routeIs('checkout.index')" class="text-zinc-300 hover:text-amber-500">
                    Checkout
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-zinc-800">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-zinc-100">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-zinc-400">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" class="text-zinc-300 hover:text-amber-500">
                        Profil Saya
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                class="text-zinc-300 hover:text-amber-500"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            Keluar (Logout)
                        </x-responsive-nav-link>
                    </form>
                </div>
            @else
                <div class="px-4 py-2 space-y-2">
                    <a href="{{ route('login') }}" class="block w-full text-center text-sm text-zinc-300 hover:text-amber-500 font-medium py-2 transition duration-150">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="block w-full text-center text-sm bg-amber-500 hover:bg-amber-600 text-zinc-950 font-bold py-2 rounded-sm transition duration-150">
                        Daftar
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>
