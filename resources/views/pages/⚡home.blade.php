<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Product;
use App\Models\Category;
use App\Models\Slider;

new #[Layout('layouts.app')] #[Title('Eren Abiye – Yeni Sezon Kadın Giyim')] class extends Component {
    public function with(): array
    {
        return [
            'sliders' => Slider::where('is_active', true)->orderBy('sort_order')->get(),
            'categories' => Category::where('is_active', true)->withCount('products')->orderBy('sort_order')->limit(12)->get(),
            'featuredProducts' => Product::where('is_active', true)
                ->where('is_featured', true)
                ->with(['category', 'variants.color', 'variants.size'])
                ->latest()
                ->take(8)
                ->get(),
            'newProducts' => Product::where('is_active', true)
                ->where('is_new', true)
                ->with(['category', 'variants.color'])
                ->latest()
                ->take(4)
                ->get(),
            'discountedProducts' => Product::where('is_active', true)
                ->whereNotNull('compare_price')
                ->whereColumn('compare_price', '>', 'price')
                ->with(['category'])
                ->orderByRaw('(compare_price - price) DESC')
                ->take(1)
                ->get(),
        ];
    }
};
?>

<div>
    {{-- ── HERO SLIDER ── --}}
    <section class="relative h-[30vh] min-h-[450px] overflow-hidden bg-blush-light" x-data="{
        current: 0,
        total: {{ $sliders->count() ?: 1 }},
        init() {
            setInterval(() => { this.current = (this.current + 1) % this.total }, 5000)
        }
    }">

        @forelse($sliders as $index => $slider)
            <div class="hero-slide" :class="{ 'active': current === {{ $index }} }">
                <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title }}"
                    class="w-full h-full object-cover" />
                <div class="absolute inset-0 bg-ink/20"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-cream slide-text px-6">
                        @if ($slider->title)
                            <p class="font-body text-xs tracking-widest2 uppercase mb-4 text-cream/80">
                                {{ $slider->subtitle }}
                            </p>
                            <h1 class="font-body text-5xl md:text-7xl font-light mb-6">
                                {{ $slider->title }}
                            </h1>
                        @endif
                        @if ($slider->button_text)
                            <a href="{{ $slider->button_url ?? route('category', 'uzun-abiye') }}"
                                class="inline-block border border-cream text-cream font-body text-xs
                                      tracking-widest2 uppercase px-8 py-3 hover:bg-cream hover:text-ink
                                      transition-all duration-300">
                                {{ $slider->button_text }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            {{-- Slider yoksa varsayılan --}}
            <div class="hero-slide active">
                <div
                    class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light flex items-center justify-center">
                    <div class="text-center slide-text px-6">
                        <p class="font-body text-xs tracking-widest2 uppercase mb-4 text-smoke">Yeni Sezon</p>
                        <h1 class="font-body text-5xl md:text-7xl font-light text-ink mb-6">
                            Eren Abiye
                        </h1>
                        <a href="{{ route('category', 'uzun-abiye') }}"
                            class="inline-block border border-ink text-ink font-body text-xs
                                  tracking-widest2 uppercase px-8 py-3 hover:bg-ink hover:text-cream
                                  transition-all duration-300">
                            Koleksiyonu Keşfet
                        </a>
                    </div>
                </div>
            </div>
        @endforelse

        {{-- Slider Dots --}}
        @if ($sliders->count() > 1)
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
                @foreach ($sliders as $index => $slider)
                    <button @click="current = {{ $index }}"
                        :class="current === {{ $index }} ? 'bg-cream w-6' : 'bg-cream/40 w-2'"
                        class="h-2 rounded-full transition-all duration-300">
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    @php
        $marketplaces = collect([
            [
                'name' => 'Trendyol',
                'logo' => \App\Models\Setting::get('marketplace_trendyol_logo'),
                'url' => \App\Models\Setting::get('marketplace_trendyol_url'),
            ],
            [
                'name' => 'Hepsiburada',
                'logo' => \App\Models\Setting::get('marketplace_hepsiburada_logo'),
                'url' => \App\Models\Setting::get('marketplace_hepsiburada_url'),
            ],
            [
                'name' => 'N11',
                'logo' => \App\Models\Setting::get('marketplace_n11_logo'),
                'url' => \App\Models\Setting::get('marketplace_n11_url'),
            ],
        ])->filter(fn($m) => !empty($m['url']));
    @endphp

    @if ($marketplaces->count() > 0)
        <div class="bg-gray-50 py-8 border-t border-gray-100">
            <div class="max-w-screen-xl mx-auto px-4">
                <div class="flex items-center justify-center  gap-8 ">
                    @foreach ($marketplaces as $item)
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener"
                            class="flex flex-col items-center gap-3 group">
                            <div
                                class="w-24 h-24 rounded-full border-2 border-[#c9a96e] flex items-center justify-center bg-white transition-shadow group-hover:shadow-lg">
                                @if ($item['logo'])
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item['logo']) }}"
                                        alt="{{ $item['name'] }}" class="w-24 h-24 object-contain" />
                                @else
                                    <span class="text-sm font-semibold text-gray-500">{{ $item['name'] }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ── BİLGİ ÇUBUĞU ── --}}
    <div class="bg-white border-b border-gray-100 py-4">
        <div class="max-w-screen-xl mx-auto px-4">

            {{-- Mobil: 2 kolon grid | Desktop: yatay flex --}}
            <div class="grid grid-cols-2 gap-3 md:flex md:items-center md:justify-between md:gap-0">

                {{-- Güvenli Alışveriş --}}
                <div class="flex items-center gap-2.5">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.955 11.955 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <span class="text-xs md:text-sm text-gray-600 font-medium">Güvenli Alışveriş</span>
                </div>
                <div class="hidden md:block w-px h-5 bg-gray-200"></div>

                {{-- Sorunsuz İade --}}
                <div class="flex items-center gap-2.5">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span class="text-xs md:text-sm text-gray-600 font-medium">Sorunsuz İade</span>
                </div>

                <div class="hidden md:block w-px h-5 bg-gray-200"></div>

                {{-- Kapıda Ödeme --}}
                <div class="flex items-center gap-2.5">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                    </svg>
                    <span class="text-xs md:text-sm text-gray-600 font-medium">Kapıda Ödeme</span>
                </div>
                <div class="hidden md:block w-px h-5 bg-gray-200"></div>

                {{-- Ücretsiz Kargo --}}
                <div class="flex items-center gap-2.5">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    <span class="text-xs md:text-sm text-gray-600 font-medium">Ücretsiz Kargo</span>
                </div>

                {{-- Ayırıcı (sadece desktop) --}}
                <div class="hidden md:block w-px h-5 bg-gray-200"></div>

                {{-- Aynı Gün Kargo --}}
                <div class="flex items-center gap-2.5 col-span-2 md:col-span-1 justify-center md:justify-start">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="text-xs md:text-sm text-gray-600 font-medium">Aynı Gün Kargo</span>
                </div>

            </div>
        </div>
    </div>

    {{-- ── KATEGORİLER ── --}}
    <section class="max-w-screen-xl mx-auto  ">

        @php
            $cats = $categories->values();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-1">

            {{-- GRUP 1 --}}
            @foreach ($cats->slice(0, 3) as $category)
                <a href="{{ route('category', $category->slug) }}" class="group relative overflow-hidden bg-gray-900"
                    style="aspect-ratio:3/2.2;">
                    @if ($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-1 md:pl-3 md:pt-1 md:pb-1 bg-black/50">
                        <h3
                            class="font-body text-[11px] md:text-[20px] tracking-widest uppercase text-white font-medium">
                            {{ $category->name }}
                        </h3>
                    </div>
                </a>
            @endforeach

            {{-- GRUP 2 --}}
            @foreach ($cats->slice(3, 3) as $category)
                <a href="{{ route('category', $category->slug) }}" class="group relative overflow-hidden bg-gray-900"
                    style="aspect-ratio:3/2.2;">
                    @if ($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-1 md:pl-3 md:pt-1 md:pb-1 bg-black/50">
                        <h3
                            class="font-body text-[11px] md:text-[20px] tracking-widest uppercase text-white font-medium">
                            {{ $category->name }}
                        </h3>
                    </div>
                </a>
            @endforeach

            {{-- GRUP 3 --}}
            @if ($cats->count() > 6)
                @php $bigCat = $cats->get(6); @endphp
                <a href="{{ route('category', $bigCat->slug) }}"
                    class="group relative overflow-hidden bg-gray-900 md:col-span-2 md:row-span-2"
                    style="aspect-ratio:3/2.2;">
                    @if ($bigCat->image)
                        <img src="{{ asset('storage/' . $bigCat->image) }}" alt="{{ $bigCat->name }}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-1 md:pl-3 md:pt-1 md:pb-1 bg-black/50">
                        <h3
                            class="font-body text-[11px] md:text-[20px] tracking-widest uppercase text-white font-medium">
                            {{ $bigCat->name }}
                        </h3>
                    </div>
                </a>

                @foreach ($cats->slice(7, 2) as $category)
                    <a href="{{ route('category', $category->slug) }}"
                        class="group relative overflow-hidden bg-gray-900" style="aspect-ratio:3/2.2;">
                        @if ($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900"></div>
                        @endif
                        <div
                            class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300">
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-1 md:pl-3 md:pt-1 md:pb-1 bg-black/50">
                            <h3
                                class="font-body text-[11px] md:text-[20px] tracking-widest uppercase text-white font-medium">
                                {{ $category->name }}
                            </h3>
                        </div>
                    </a>
                @endforeach
            @endif

            {{-- GRUP 4 --}}
            @foreach ($cats->slice(9) as $category)
                <a href="{{ route('category', $category->slug) }}" class="group relative overflow-hidden bg-gray-900"
                    style="aspect-ratio:3/2.2;">
                    @if ($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-gray-700 to-gray-900"></div>
                    @endif
                    <div class="absolute inset-0 bg-black/30 group-hover:bg-black/40 transition-colors duration-300">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-1 md:pl-3 md:pt-1 md:pb-1 bg-black/50">
                        <h3
                            class="font-body text-[11px] md:text-[20px] tracking-widest uppercase text-white font-medium">
                            {{ $category->name }}
                        </h3>
                    </div>
                </a>
            @endforeach

        </div>
    </section>


    <section class="max-w-screen-xl mx-auto py-4">
        <a href="{{ route('page', 'musteri-memnuniyeti') }}">
            <img class="w-full" src="{{ asset('storage/memnuniyet.webp') }}" alt="">
        </a>
    </section>

    <section class="bg-white py-5">
        <div class="max-w-screen-xl mx-auto">

            {{-- Başlık --}}
            <div class="flex items-center justify-between mb-8">
                <div class="flex-1 h-px bg-sand/50"></div>
                <h2
                    class="font-body text-[11px] md:text-[20px] tracking-widest2 uppercase text-ink px-6 whitespace-nowrap">
                    Bu İndirimler Kaçmaz
                </h2>
                <div class="flex-1 h-px bg-sand/50"></div>
            </div>

            {{-- Mobil --}}
            <div class="flex flex-col md:flex-row gap-0">

                {{-- ── HAFTANIN ÜRÜNLERİ ETİKETİ ── --}}
                <div class="flex md:flex-shrink-0 md:w-20 items-center justify-center bg-sand/20 py-3 md:py-0">

                    <p class="md:hidden font-body text-sm font-light text-ink tracking-widest uppercase">
                        Haftanın Ürünleri
                    </p>

                    <p class="hidden md:flex font-body text-lg font-light text-ink"
                        style="writing-mode: vertical-rl; transform: rotate(180deg); letter-spacing: 0.15em;">
                        Haftanın Ürünleri
                    </p>

                </div>

                {{-- ── ÜRÜN SLİDER ── --}}
                <div class="flex-1 relative overflow-hidden" x-data="{ current: 0, total: {{ max(0, count($featuredProducts) - 3) }}, isMobile: window.innerWidth < 768 }" x-init="() => {
                    isMobile = window.innerWidth < 768;
                    window.addEventListener('resize', () => { isMobile = window.innerWidth < 768 })
                }">

                    {{-- Ok Butonları --}}
                    <button @click="current = Math.max(0, current - 1)"
                        class="absolute left-1 md:left-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-9 md:h-9 bg-white/90
                           border border-sand flex items-center justify-center
                           hover:bg-ink hover:text-cream hover:border-ink transition-all">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button
                        @click="current = Math.min(isMobile ? {{ max(0, count($featuredProducts) - 1) }} : total, current + 1)"
                        class="absolute right-1 md:right-2 top-1/2 -translate-y-1/2 z-10 w-8 h-8 md:w-9 md:h-9 bg-white/90
                           border border-sand flex items-center justify-center
                           hover:bg-ink hover:text-cream hover:border-ink transition-all">
                        <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                    {{-- Ürünler --}}
                    <div class="flex transition-transform duration-500"
                        :style="isMobile
                            ?
                            `transform: translateX(calc(-${current} * 50%))` :
                            `transform: translateX(calc(-${current} * 33.333%))`">
                        @foreach ($featuredProducts as $product)
                            <div class="w-1/2 md:w-1/3 flex-shrink-0 px-1.5 md:px-2 group">
                                <div class="relative aspect-[3/4] overflow-hidden bg-blush-light mb-2 md:mb-3">
                                    <a href="{{ route('product', $product->slug) }}" class="block absolute inset-0">
                                        @if ($product->main_image)
                                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                                alt="{{ $product->name }}"
                                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                        @else
                                            <div
                                                class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light">
                                            </div>
                                        @endif
                                    </a>
                                    @livewire('wishlist-button', ['productId' => $product->id], key('wl-feat-' . $product->id))
                                </div>

                                <p class="font-body text-xs text-ink leading-snug line-clamp-2 mb-1">
                                    <a href="{{ route('product', $product->slug) }}"
                                        class="hover:text-smoke transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </p>
                                <div class="flex items-center gap-1.5 md:gap-2">
                                    @if ($product->compare_price)
                                        <span class="font-body text-xs text-smoke line-through">
                                            ₺{{ number_format($product->compare_price, 2, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="font-body text-xs md:text-sm font-medium text-ink">
                                        ₺{{ number_format($product->price, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── SAĞ: FIRSAT ÜRÜNLERİ BANNER ── --}}
                <div
                    class="flex-shrink-0 md:w-56 relative overflow-hidden bg-ink
                        h-32 md:h-auto flex md:block items-center">
                    @php $saleProduct = $discountedProducts->first(); @endphp
                    <img src="{{ asset('storage/products/colors/01KKV53W451H5SJJXTQX8DX8E5.jpg') }}"
                        alt="Fırsat Ürünleri" class="w-full h-full object-cover opacity-60 absolute inset-0" />
                    <div
                        class="relative z-10 flex md:flex-col items-center md:justify-center
                            w-full h-full px-6 md:p-6 gap-4 md:gap-0 md:text-center">
                        <div class="flex items-baseline gap-0.5">
                            <p class="font-body text-5xl md:text-6xl font-light text-cream">80</p>
                            <sup class="font-body text-sm text-cream/80">%</sup>
                        </div>
                        <div class="flex-1 md:flex-none">
                            <p class="font-body text-xs text-cream/70 tracking-widest uppercase md:-mt-2">'e varan</p>
                            <p class="font-body text-xl md:text-2xl font-light text-cream md:mt-1">indirimler</p>
                        </div>
                        <a href="{{ route('category', 'firsat') }}"
                            class="shrink-0 md:w-full text-center bg-sand text-ink font-body text-xs
                               tracking-widest uppercase px-4 md:px-0 py-2.5 md:mt-4 hover:bg-cream transition-colors">
                            Fırsat Ürünleri
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ── YENİ GELENLER ── --}}
    <section class="max-w-screen-xl mx-auto py-5">
        <div class="overflow-hidden" x-data="{ current: 0, total: {{ max(0, count($newProducts) - 4) }}, isMobile: window.innerWidth < 768 }" x-init="window.addEventListener('resize', () => { isMobile = window.innerWidth < 768 })">

            <div class="flex items-center justify-between mb-8">
                <div class="flex-1 h-px bg-sand/50"></div>
                <h2 class="font-body text-[11px] md:text-[20px] tracking-widest2 uppercase text-ink whitespace-nowrap">
                    Son Eklenen Ürünler
                </h2>
                <div class="flex-1 h-px bg-sand/50"></div>
                <div class="flex items-center gap-2 ml-4">
                    <button @click="current = Math.max(0, current - 1)"
                        class="w-8 h-8 border border-sand/60 flex items-center justify-center
                           hover:border-ink hover:bg-ink hover:text-cream transition-all text-ink">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <button
                        @click="current = Math.min(isMobile ? {{ max(0, count($newProducts) - 2) }} : total, current + 1)"
                        class="w-8 h-8 border border-sand/60 flex items-center justify-center
                           hover:border-ink hover:bg-ink hover:text-cream transition-all text-ink">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex transition-transform duration-500"
                :style="isMobile
                    ?
                    `transform: translateX(calc(-${current} * 50%))` :
                    `transform: translateX(calc(-${current} * 25%))`">

                @foreach ($newProducts as $product)
                    <div class="w-1/2 md:w-1/4 flex-shrink-0 px-3 group">

                        <div class="relative aspect-[3/4] overflow-hidden bg-gray-100 mb-3">
                            <a href="{{ route('product', $product->slug) }}" class="block absolute inset-0">
                                @if ($product->main_image)
                                    <img src="{{ asset('storage/' . $product->main_image) }}"
                                        alt="{{ $product->name }}"
                                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                                @endif
                            </a>
                            @livewire('wishlist-button', ['productId' => $product->id], key('wl-new-' . $product->id))
                        </div>

                        <h3 class="font-body text-xs text-ink leading-snug line-clamp-2 mb-1">
                            <a href="{{ route('product', $product->slug) }}"
                                class="hover:text-smoke transition-colors">
                                {{ $product->name }}
                            </a>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if ($product->compare_price)
                                <span class="font-body text-xs text-smoke line-through">
                                    ₺{{ number_format($product->compare_price, 2, ',', '.') }}
                                </span>
                            @endif
                            <span class="font-body text-sm font-medium text-ink">
                                ₺{{ number_format($product->price, 2, ',', '.') }}
                            </span>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @php
        $appUrl = config('app.url');
        $siteName = \App\Models\Setting::get('site_name', 'Eren Abiye');
        $phone = \App\Models\Setting::get('contact_phone', '');
        $instagram = \App\Models\Setting::get('social_instagram', '');
        $facebook = \App\Models\Setting::get('social_facebook', '');

        $websiteSchema = json_encode(
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $appUrl,
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $appUrl . '/arama?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );

        $orgSchema = json_encode(
            [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $appUrl,
                'logo' => asset('images/logo.png'),
                'sameAs' => [$instagram, $facebook],
                'contactPoint' => [
                    '@type' => 'ContactPoint',
                    'telephone' => $phone,
                    'contactType' => 'customer service',
                    'availableLanguage' => 'Turkish',
                ],
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
        );
    @endphp

    <script type="application/ld+json">{!! $websiteSchema !!}</script>
    <script type="application/ld+json">{!! $orgSchema !!}</script>
</div>
