<?php
use Livewire\Component;

new class extends Component {
    public bool $mobileMenuOpen = false;
    public string $searchQuery = '';

    #[On('cart-updated')]
    public function refreshCart(): void {}

    #[On('wishlist-updated')]
    public function refreshWishlist(): void {}

    public function with(): array
    {
        return [
            'menu' => \App\Models\Menu::getByLocation('header'),
            'cartCount' => auth()->check() ? \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity') : collect(session()->get('cart', []))->sum('quantity'),
            'wishlistCount' => \App\Models\Wishlist::forCurrentUser()->count(),
        ];
    }
};
?>

<header x-data="{
    scrolled: false,
    searchOpen: false,
    mobileMenuOpen: false,
    activeMenu: null,
}" @scroll.window="scrolled = window.scrollY > 50"
    @keydown.escape.window="activeMenu = null; searchOpen = false; $wire.set('searchQuery', '')"
    @cart-updated.window="$wire.refreshCart()" @wishlist-updated.window="$wire.refreshWishlist()">
    {{-- Top Bar --}}
    <div class="bg-white pb-10 sm:pb-0 text-white text-xs  flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a class="py-2" href="{{ route('page', 'iletisim') }}"><span
                    class="bg-gold-second-color p-2  m-0 text-gray-white">Yardım</span></a>
            <span
                class="font-medium text-gray-400 tracking-wider">{{ \App\Models\Setting::get('contact_phone', '') }}</span>
        </div>
        <div class="flex sm:hidden pt-3 items-center gap-5 pr-1 md:pr-0">

            {{-- Hesap --}}
            <a href="{{ auth()->check() ? route('account.orders') : route('login') }}"
                class="flex text-gray-600 hover:text-gray-900 transition-colors" title="Hesabım">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </a>

            {{-- Favoriler --}}
            <a href="{{ auth()->check() ? route('account.orders', ['tab' => 'wishlist']) : route('favorites') }}"
                class="flex relative text-gray-600 hover:text-gray-900 transition-colors" title="Favorilerim">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                @if ($wishlistCount > 0)
                    <span
                        class="absolute -top-2 -right-2 bg-gray-900 text-white text-[9px]
                w-4 h-4 rounded-full flex items-center justify-center font-body">
                        {{ $wishlistCount }}
                    </span>
                @endif
            </a>

            {{-- Sepet --}}
            <a href="{{ route('cart') }}" class="flex relative text-gray-600 hover:text-gray-900 transition-colors"
                title="Sepet">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                </svg>
                @if ($cartCount > 0)
                    <span
                        class="absolute -top-2 -right-2 bg-gray-900 text-white text-[9px]
                w-4 h-4 rounded-full flex items-center justify-center font-body">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>

        </div>
    </div>

    {{-- Ana Nav --}}
    <nav :class="scrolled ? 'shadow-md' : ''" class="bg-white sticky top-0 z-50 transition-all duration-300">

        {{-- Üst Satır: Arama | Logo | İkonlar --}}
        <div class="border-b border-gray-100">
            <div class="max-w-screen-xl mx-auto px-6">
                <div class="flex items-center h-20 relative">

                    {{-- SOL: Arama Butonu --}}
                    <div class="flex-1 flex items-center">
                        <button
                            @click="searchOpen = !searchOpen; $nextTick(() => { if(searchOpen) $refs.searchInput?.focus() })"
                            class="flex items-center gap-3 cursor-pointer bg-transparent border-none p-0">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z" />
                            </svg>
                            <span class="hidden sm:block text-sm text-gray-400 font-light tracking-wide">
                                Aradığınız ürünü yazınız
                            </span>
                        </button>
                    </div>

                    {{-- ORTA: Logo --}}
                    <div class="absolute left-1/2 pb-8 -translate-x-1/2 text-center">
                        <a href="{{ route('home') }}" class="block">
                            @php $logo = \App\Models\Setting::get('site_logo'); @endphp

                            @if ($logo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                                    alt="{{ config('app.name') }}" class="h-25 w-auto" />
                            @else
                                <span class="font-display text-3xl font-light tracking-wide text-ink">
                                    {{ config('app.name', 'Eren Abiye') }}
                                </span>
                            @endif
                        </a>
                    </div>

                    {{-- SAĞ: İkonlar --}}
                    <div class="flex-1 flex items-center justify-end gap-5">

                        {{-- Hesap --}}
                        <a href="{{ auth()->check() ? route('account.orders') : route('login') }}"
                            class="hidden sm:flex text-gray-600 hover:text-gray-900 transition-colors" title="Hesabım">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </a>

                        {{-- Favoriler --}}
                        <a href="{{ auth()->check() ? route('account.orders', ['tab' => 'wishlist']) : route('favorites') }}"
                            class="hidden sm:flex relative text-gray-600 hover:text-gray-900 transition-colors"
                            title="Favorilerim">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                            </svg>
                            @if ($wishlistCount > 0)
                                <span
                                    class="absolute -top-2 -right-2 bg-gray-900 text-white text-[9px]
                     w-4 h-4 rounded-full flex items-center justify-center font-body">
                                    {{ $wishlistCount }}
                                </span>
                            @endif
                        </a>

                        {{-- Sepet --}}
                        <a href="{{ route('cart') }}"
                            class="hidden sm:flex relative flex text-gray-600 hover:text-gray-900 transition-colors"
                            title="Sepet">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                            </svg>
                            @if ($cartCount > 0)
                                <span
                                    class="absolute -top-2 -right-2 bg-gray-900 text-white text-[9px]
                                             w-4 h-4 rounded-full flex items-center justify-center font-body">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>

                        {{-- Mobil Menü --}}
                        <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="lg:hidden text-gray-600 hover:text-gray-900 transition-colors">
                            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                            </svg>
                            <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{-- ── ARAMA KUTUSU ── --}}
        <div x-show="searchOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="bg-white border-b border-gray-200 shadow-lg z-40">

            <form action="{{ route('all-products') }}" method="GET">
                <div class="max-w-screen-xl mx-auto px-6 py-4 flex items-center gap-3">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607z" />
                    </svg>
                    <input type="text" name="search" x-ref="searchInput"
                        placeholder="Ürün ara... (Enter veya Ara butonuna basın)"
                        class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700
                          placeholder-gray-400 font-light tracking-wide" />
                    <button type="submit"
                        class="font-body text-xs tracking-widest uppercase px-5 py-2
                           bg-gray-900 text-white hover:bg-gray-700 transition-colors flex-shrink-0">
                        Ara
                    </button>
                    <button type="button" @click="searchOpen = false"
                        class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- ── ARAMA SONUÇLARI ── --}}
        @if (strlen($searchQuery) >= 2)
            <div class="bg-white border-b border-gray-200 shadow-lg z-40">
                @php
                    $results = \App\Models\Product::where('is_active', true)
                        ->where('name', 'like', '%' . $searchQuery . '%')
                        ->with('category')
                        ->limit(8)
                        ->get();
                @endphp

                <div class="max-w-screen-xl mx-auto px-6 pb-6">
                    @if ($results->count())
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 pt-4">
                            @foreach ($results as $result)
                                <a href="{{ route('product', $result->slug) }}"
                                    @click="searchOpen = false; $wire.set('searchQuery', '')"
                                    class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 transition-colors group">
                                    <div class="w-10 h-14 flex-shrink-0 overflow-hidden bg-gray-100">
                                        @if ($result->main_image)
                                            <img src="{{ asset('storage/' . $result->main_image) }}"
                                                alt="{{ $result->name }}" class="w-full h-full object-cover" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="font-body text-xs text-gray-800 line-clamp-2 leading-snug
                                           group-hover:text-gray-500 transition-colors">
                                            {{ $result->name }}
                                        </p>
                                        <p class="font-body text-[10px] text-gray-400 mt-0.5">
                                            {{ $result->category->name }}
                                        </p>
                                        <p class="font-body text-xs font-medium text-gray-900 mt-0.5">
                                            ₺{{ number_format($result->price, 2, ',', '.') }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
                            <p class="font-body text-xs text-gray-400">
                                <span class="font-medium text-gray-700">{{ $results->count() }}</span> sonuç bulundu
                            </p>
                            <a href="{{ route('all-products') }}?search={{ urlencode($searchQuery) }}"
                                @click="searchOpen = false"
                                class="font-body text-xs text-gray-600 hover:text-gray-900 underline transition-colors">
                                Tüm sonuçları gör →
                            </a>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="font-body text-sm text-gray-400">
                                "<span class="text-gray-600">{{ $searchQuery }}</span>" için sonuç bulunamadı
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ── MASAÜSTÜ NAVİGASYON ── --}}
        <div class="hidden lg:block border-b border-gray-100 relative" @mouseleave="activeMenu = null">
            <div class="max-w-screen-xl mx-auto px-6">
                <ul class="flex items-center justify-center gap-10 h-12 list-none m-0 p-0">
                    @if ($menu)
                        @foreach ($menu->items->where('is_active', true) as $item)
                            <li class="h-full flex items-center"
                                @if ($item->children->count()) @mouseenter="activeMenu = '{{ $item->id }}'"
                                @else
                                    @mouseenter="activeMenu = null" @endif>
                                <a href="{{ $item->resolved_url }}" target="{{ $item->target }}"
                                    class="relative h-full flex items-center font-body text-xs
                                          tracking-widest uppercase transition-colors py-4
                                          text-gray-800 hover:text-gray-600">
                                    {{ $item->label }}
                                    @if ($item->children->count())
                                        <span
                                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-gray-900
                                                     transition-transform duration-200"
                                            :style="activeMenu === '{{ $item->id }}'
                                                ?
                                                'transform:scaleX(1)' :
                                                'transform:scaleX(0)'"
                                            style="transform-origin:center; transform:scaleX(0)">
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            {{-- Mega Menü Dropdown --}}
            <div x-show="activeMenu !== null" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                class="absolute top-full left-0 right-0 bg-white border-t border-gray-100 shadow-xl z-50">

                @if ($menu)
                    @foreach ($menu->items as $item)
                        @if ($item->children->count())
                            <div x-show="activeMenu === '{{ $item->id }}'"
                                class="max-w-screen-xl mx-auto px-6 py-8">
                                <div class="flex gap-10">

                                    {{-- Alt Kategori Listesi --}}
                                    <div class="w-52 flex-shrink-0 border-r border-gray-100 pr-8">
                                        <ul class="list-none m-0 p-0">
                                            @foreach ($item->children as $child)
                                                <li>
                                                    <a href="{{ $child->resolved_url }}"
                                                        target="{{ $child->target }}"
                                                        class="flex items-center gap-2 py-2 text-xs text-gray-500
                                                              hover:text-gray-900 transition-colors
                                                              border-b border-gray-50 hover:border-gray-200 tracking-wide">
                                                        <span class="text-gray-300 text-[10px]">›</span>
                                                        {{ $child->label }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    @php
                                        $featured = $item->children->where('is_active', true)->take(4);
                                        $cols = min(max($featured->count(), 1), 6);
                                        $categoryIds = $featured
                                            ->where('type', 'category')
                                            ->pluck('linkable_id')
                                            ->filter();
                                        $categories = $categoryIds->isNotEmpty()
                                            ? \App\Models\Category::whereIn('id', $categoryIds)->get()->keyBy('id')
                                            : collect();
                                        $blogIds = $featured
                                            ->whereIn('type', ['blog_post', 'post', 'blog'])
                                            ->pluck('linkable_id')
                                            ->filter();
                                        $blogPosts = $blogIds->isNotEmpty()
                                            ? \App\Models\BlogPost::whereIn('id', $blogIds)->get()->keyBy('id')
                                            : collect();
                                    @endphp

                                    <div class="flex-1 grid gap-5"
                                        style="grid-template-columns: repeat({{ $cols }}, minmax(0, 1fr))">
                                        @foreach ($featured as $card)
                                            @php
                                                $cardUrl = $card->resolved_url ?? ($card->url ?? '#');
                                                $cardLabel = $card->label ?? '';
                                                $cardTarget = $card->target ?? '_self';
                                                if ($card->type === 'category' && $card->linkable_id) {
                                                    $linked = $categories->get($card->linkable_id);
                                                    $cardImg = $linked?->image
                                                        ? asset('storage/' . $linked->image)
                                                        : null;
                                                    $cardDesc = $linked?->description ?? '';
                                                } elseif (
                                                    in_array($card->type, ['blog_post', 'post', 'blog']) &&
                                                    $card->linkable_id
                                                ) {
                                                    $linked = $blogPosts->get($card->linkable_id);
                                                    $cardImg = $linked?->image
                                                        ? asset('storage/' . $linked->image)
                                                        : null;
                                                    $cardDesc = $linked?->excerpt ?? '';
                                                } else {
                                                    $cardImg = null;
                                                    $cardDesc = '';
                                                }
                                            @endphp
                                            <a href="{{ $cardUrl }}" target="{{ $cardTarget }}"
                                                class="group block">
                                                <div class="h-75  w-auto overflow-hidden bg-gray-100 mb-3"
                                                    style="aspect-ratio:3/4;">
                                                    @if ($cardImg)
                                                        <img src="{{ $cardImg }}" alt="{{ $cardLabel }}"
                                                            loading="lazy"
                                                            class="w-full h-full object-cover object-top
                                                                    group-hover:scale-105 transition-transform duration-500" />
                                                    @else
                                                        <div
                                                            class="w-full h-full bg-gray-100 flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-300" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="1"
                                                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 3h18M3 3v18" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <h4
                                                    class="font-semibold text-xs text-gray-900 mb-1 tracking-wide
                                                           group-hover:text-gray-600 transition-colors">
                                                    {{ $cardLabel }}
                                                </h4>
                                                @if ($cardDesc)
                                                    <p class="text-xs text-gray-400 leading-relaxed line-clamp-2">
                                                        {{ $cardDesc }}
                                                    </p>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        {{-- ── MOBİL MENÜ ── --}}
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden border-t border-gray-100 bg-white">
            <div class="px-6 py-4">
                @if ($menu)
                    @foreach ($menu->items->where('is_active', true) as $item)
                        <a href="{{ $item->resolved_url }}" target="{{ $item->target }}"
                            class="block py-3 font-body text-xs tracking-widest uppercase
                                  border-b border-gray-100 text-gray-700 hover:text-gray-900 transition-colors">
                            {{ $item->label }}
                        </a>
                        @foreach ($item->children->where('is_active', true) as $child)
                            <a href="{{ $child->resolved_url }}" target="{{ $child->target }}"
                                class="block py-2 pl-5 font-body text-xs text-gray-400
                                      border-b border-gray-50 hover:text-gray-700 transition-colors">
                                — {{ $child->label }}
                            </a>
                        @endforeach
                    @endforeach
                @endif
            </div>
        </div>

    </nav>
</header>
