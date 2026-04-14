@php($loc = app()->getLocale())
<nav
    x-data="{ open: false, pwaInstall: false }"
    @mansavibes:pwa-installable.window="pwaInstall = true"
    @mansavibes:pwa-update.window="if (confirm(@json(__('pwa.update_ready')))) location.reload()"
    class="bg-mansa-black border-b border-gold-600/35"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ auth()->user()->role === 'tailleur' ? route('tailor.workspace') : route('dashboard') }}" class="flex flex-col leading-tight">
                        <span class="text-sm font-bold tracking-tight text-white">MANSA <span class="text-gold-400">VIBES</span></span>
                        <span class="text-[10px] text-gold-500/70 uppercase tracking-wider">{{ \App\Support\CurrentTenant::get()?->name ?? auth()->user()->tenant->name ?? '' }}</span>
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    @if (auth()->user()->role === 'tailleur')
                        <x-nav-link :href="route('tailor.workspace')" :active="request()->routeIs('tailor.workspace')">
                            {{ __('nav.tailor_workspace') }}
                        </x-nav-link>
                        <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                            {{ __('nav.clients') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">
                            {{ __('nav.clients') }}
                        </x-nav-link>
                        <x-nav-link :href="route('measurement-templates.index')" :active="request()->routeIs('measurement-templates.*')">
                            {{ __('nav.measurement_models') }}
                        </x-nav-link>
                        <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                            {{ __('nav.orders') }}
                        </x-nav-link>
                        <x-nav-link :href="route('finance.index')" :active="request()->routeIs('finance.*')">
                            {{ __('nav.finance') }}
                        </x-nav-link>
                        <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">
                            {{ __('nav.suppliers') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-3">
                <button
                    type="button"
                    x-show="pwaInstall"
                    x-cloak
                    @click="window.mansaInstallPwa()"
                    class="hidden sm:inline-flex items-center px-2.5 py-1.5 rounded-md text-xs font-semibold text-mansa-black bg-gold-500 hover:bg-gold-400 border border-gold-600/30"
                    title="{{ __('pwa.install_ios') }}"
                >
                    {{ __('pwa.install') }}
                </button>
                <div class="flex items-center rounded-md border border-gold-600/40 overflow-hidden text-xs font-semibold">
                    <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                        @csrf
                        <input type="hidden" name="locale" value="fr" />
                        <button type="submit" class="px-2.5 py-1.5 transition {{ $loc === 'fr' ? 'bg-gold-500 text-mansa-black' : 'text-gold-200 hover:bg-white/10 text-white' }}" title="{{ __('nav.french') }}">FR</button>
                    </form>
                    <form method="POST" action="{{ route('locale.switch') }}" class="inline border-l border-gold-600/40">
                        @csrf
                        <input type="hidden" name="locale" value="en" />
                        <button type="submit" class="px-2.5 py-1.5 transition {{ $loc === 'en' ? 'bg-gold-500 text-mansa-black' : 'text-gold-200 hover:bg-white/10 text-white' }}" title="{{ __('nav.english') }}">EN</button>
                    </form>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-gold-600/40 rounded-md text-sm leading-4 font-medium text-white bg-white/5 hover:bg-white/10 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-gold-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-500 border-b border-gray-100">{{ __('nav.language') }}</div>
                        <form method="POST" action="{{ route('locale.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="fr" />
                            <button type="submit" class="block w-full text-start px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">{{ __('nav.french') }}</button>
                        </form>
                        <form method="POST" action="{{ route('locale.switch') }}">
                            @csrf
                            <input type="hidden" name="locale" value="en" />
                            <button type="submit" class="block w-full text-start px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">{{ __('nav.english') }}</button>
                        </form>
                        <div class="border-t border-gray-100"></div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gold-300 hover:text-white hover:bg-white/10 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-mansa-surface border-t border-gold-600/25">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <div class="px-2 pb-2 sm:hidden" x-show="pwaInstall" x-cloak>
                <button
                    type="button"
                    @click="window.mansaInstallPwa()"
                    class="w-full rounded-md py-2 text-sm font-semibold text-mansa-black bg-gold-500"
                >
                    {{ __('pwa.install') }}
                </button>
                <p class="text-[10px] text-gold-200/80 mt-1 text-center">{{ __('pwa.install_ios') }}</p>
            </div>
            <div class="flex gap-2 px-2 pb-2">
                <form method="POST" action="{{ route('locale.switch') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="locale" value="fr" />
                    <button type="submit" class="w-full rounded-md border border-gold-600/40 py-2 text-sm font-medium {{ $loc === 'fr' ? 'bg-gold-500 text-mansa-black' : 'text-white' }}">{{ __('nav.french') }}</button>
                </form>
                <form method="POST" action="{{ route('locale.switch') }}" class="flex-1">
                    @csrf
                    <input type="hidden" name="locale" value="en" />
                    <button type="submit" class="w-full rounded-md border border-gold-600/40 py-2 text-sm font-medium {{ $loc === 'en' ? 'bg-gold-500 text-mansa-black' : 'text-white' }}">{{ __('nav.english') }}</button>
                </form>
            </div>
            @if (auth()->user()->role === 'tailleur')
                <x-responsive-nav-link :href="route('tailor.workspace')" :active="request()->routeIs('tailor.workspace')">
                    {{ __('nav.tailor_workspace') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('nav.clients') }}</x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('nav.clients') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('measurement-templates.index')" :active="request()->routeIs('measurement-templates.*')">{{ __('nav.measurement_models') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">{{ __('nav.orders') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('finance.index')" :active="request()->routeIs('finance.*')">{{ __('nav.finance') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')">{{ __('nav.suppliers') }}</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-3 border-t border-gold-600/25">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gold-200/80">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
