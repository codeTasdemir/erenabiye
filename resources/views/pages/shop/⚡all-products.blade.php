<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Category;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;

new #[Layout('layouts.app')] class extends Component {
    #[Url]
    public array $selectedColors = [];
    #[Url]
    public array $selectedSizes = [];
    #[Url]
    public array $selectedCategories = [];
    #[Url]
    public string $sort = 'latest';
    #[Url]
    public string $minPrice = '';
    #[Url]
    public string $maxPrice = '';
    #[Url]
    public string $search = '';

    public int $perPage = 25;
    public bool $filterOpen = false;

    public function mount(): void
    {
        if ($this->minPrice === '') {
            $this->minPrice = (string) $this->minPossiblePrice;
        }
        if ($this->maxPrice === '') {
            $this->maxPrice = (string) $this->maxPossiblePrice;
        }
    }

    public function getMinPossiblePriceProperty(): int
    {
        return (int) (Product::where('is_active', true)->min('price') ?? 0);
    }

    public function getMaxPossiblePriceProperty(): int
    {
        return (int) (Product::where('is_active', true)->max('price') ?? 99999);
    }

    public function loadMore(): void
    {
        $this->perPage += 25;
    }

    public function clearFilters(): void
    {
        $this->selectedColors = [];
        $this->selectedSizes = [];
        $this->selectedCategories = [];
        $this->minPrice = (string) $this->minPossiblePrice;
        $this->maxPrice = (string) $this->maxPossiblePrice;
        $this->sort = 'latest';
        $this->perPage = 25;
    }

    public function toggleColor(int $colorId): void
    {
        if (in_array($colorId, $this->selectedColors)) {
            $this->selectedColors = array_values(array_filter($this->selectedColors, fn($c) => $c !== $colorId));
        } else {
            $this->selectedColors[] = $colorId;
        }
        $this->perPage = 25;
    }

    public function toggleSize(int $sizeId): void
    {
        if (in_array($sizeId, $this->selectedSizes)) {
            $this->selectedSizes = array_values(array_filter($this->selectedSizes, fn($s) => $s !== $sizeId));
        } else {
            $this->selectedSizes[] = $sizeId;
        }
        $this->perPage = 25;
    }

    public function toggleCategory(int $categoryId): void
    {
        if (in_array($categoryId, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_filter($this->selectedCategories, fn($c) => $c !== $categoryId));
        } else {
            $this->selectedCategories[] = $categoryId;
        }
        $this->perPage = 25;
    }

    public function getActiveFilterCountProperty(): int
    {
        $priceActive = ($this->minPrice !== '' && (int) $this->minPrice > $this->minPossiblePrice) || ($this->maxPrice !== '' && (int) $this->maxPrice < $this->maxPossiblePrice);
        return count($this->selectedColors) + count($this->selectedSizes) + count($this->selectedCategories) + ($priceActive ? 1 : 0);
    }

    public function with(): array
    {
        $query = Product::where('is_active', true)->with(['category', 'variants.color', 'variants.size', 'images']);

        if (!empty($this->selectedCategories)) {
            $query->whereIn('category_id', $this->selectedCategories);
        }
        if (!empty($this->selectedColors)) {
            $query->whereHas('variants', fn($q) => $q->whereIn('color_id', $this->selectedColors)->where('stock', '>', 0));
        }
        if (!empty($this->selectedSizes)) {
            $query->whereHas('variants', fn($q) => $q->whereIn('size_id', $this->selectedSizes)->where('stock', '>', 0));
        }
        if ($this->minPrice !== '' && (int) $this->minPrice > $this->minPossiblePrice) {
            $query->where('price', '>=', (float) $this->minPrice);
        }
        if ($this->maxPrice !== '' && (int) $this->maxPrice < $this->maxPossiblePrice) {
            $query->where('price', '<=', (float) $this->maxPrice);
        }
        if ($this->search !== '') {
            $query->where('name', 'ilike', '%' . $this->search . '%');
        }

        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'featured' => $query->orderBy('is_featured', 'desc')->latest(),
            'discounted' => $query->whereNotNull('compare_price')->orderByRaw('(compare_price - price) DESC'),
            default => $query->latest(),
        };

        $totalCount = $query->count();
        $products = $query->limit($this->perPage)->get();

        return [
            'products' => $products,
            'totalCount' => $totalCount,
            'hasMore' => $totalCount > $this->perPage,
            'availableColors' => Color::where('is_active', true)->orderBy('sort_order')->get(),
            'availableSizes' => Size::where('is_active', true)->orderBy('sort_order')->get(),
            'availableCategories' => Category::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->get(),
        ];
    }
};
?>

<div>
    <style>
        details.filter-dd {
            position: relative;
        }

        details.filter-dd summary {
            list-style: none;
            cursor: pointer;
            user-select: none;
        }

        details.filter-dd summary::-webkit-details-marker {
            display: none;
        }

        details.filter-dd .dd-panel {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 30;
            background: white;
            border: 1px solid rgba(194, 178, 160, 0.4);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }

        details.filter-dd .chevron {
            transition: transform 150ms;
        }

        details.filter-dd[open] .chevron {
            transform: rotate(180deg);
        }

        .range-thumb::-webkit-slider-thumb {
            -webkit-appearance: none;
            pointer-events: all;
            width: 16px;
            height: 16px;
            background: #1a1a1a;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

        .range-thumb::-moz-range-thumb {
            pointer-events: all;
            width: 16px;
            height: 16px;
            background: #1a1a1a;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }
    </style>

    {{-- Breadcrumb --}}
    <div class="border-b border-sand/20">
        <div class="max-w-screen-xl mx-auto px-6 py-3">
            <nav class="flex items-center gap-1 font-body text-xs text-smoke">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                <span class="text-ink">Abiye Modelleri</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-0 py-8">

        {{-- Başlık --}}
        <div class="text-center mb-8">
            <h1 class="font-display text-4xl md:text-5xl font-light text-ink mb-3">Abiye Modelleri</h1>
            <p class="font-body text-sm text-smoke max-w-2xl mx-auto leading-relaxed">
                Özel günleriniz için en şık abiye modelleri
            </p>
        </div>

        {{-- ── FİLTRE BARI ── --}}
        <div class="border border-sand/40 bg-white mb-8 sticky top-0 z-50 md:static md:z-auto">
            <div class="flex items-center flex-wrap gap-0 divide-x divide-sand/40">

                {{-- Renk --}}
                <details class="filter-dd" x-data="{ open: false }" :open="open" @toggle.prevent>
                    <summary @click.prevent="open = !open"
                        class="flex items-center gap-2 px-5 py-3 font-body text-xs
                               hover:bg-blush-light/50 transition-colors whitespace-nowrap
                               {{ count($selectedColors) > 0 ? 'text-ink font-medium' : 'text-smoke' }}">
                        Renk
                        @if (count($selectedColors) > 0)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[10px]">
                                {{ count($selectedColors) }}
                            </span>
                        @endif
                        <svg class="chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dd-panel p-4 min-w-[200px]">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableColors as $color)
                                <button wire:click="toggleColor({{ $color->id }})" title="{{ $color->name }}"
                                    class="w-7 h-7 rounded-full border-2 transition-all flex-shrink-0
                                           {{ in_array($color->id, $selectedColors) ? 'border-ink scale-110 shadow-md' : 'border-smoke/20 hover:border-smoke' }}"
                                    style="background-color: {{ $color->hex_code ?? '#ccc' }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details>

                {{-- Beden --}}
                <details class="filter-dd" x-data="{ open: false }" :open="open" @toggle.prevent>
                    <summary @click.prevent="open = !open"
                        class="flex items-center gap-2 px-5 py-3 font-body text-xs
                               hover:bg-blush-light/50 transition-colors whitespace-nowrap
                               {{ count($selectedSizes) > 0 ? 'text-ink font-medium' : 'text-smoke' }}">
                        Beden
                        @if (count($selectedSizes) > 0)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[10px]">
                                {{ count($selectedSizes) }}
                            </span>
                        @endif
                        <svg class="chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dd-panel p-4 min-w-[240px]">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableSizes as $size)
                                <button wire:click="toggleSize({{ $size->id }})"
                                    class="min-w-[40px] h-9 px-2 border font-body text-xs transition-all
                                           {{ in_array($size->id, $selectedSizes) ? 'bg-ink text-cream border-ink' : 'border-sand text-ink hover:border-ink' }}">
                                    {{ $size->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details>

                {{-- Fiyat --}}
                <details class="filter-dd" x-data="{ open: false }" :open="open" @toggle.prevent>
                    <summary @click.prevent="open = !open"
                        class="flex items-center gap-2 px-5 py-3 font-body text-xs
                               hover:bg-blush-light/50 transition-colors whitespace-nowrap
                               {{ $this->activeFilterCount > count($selectedColors) + count($selectedSizes) + count($selectedCategories) ? 'text-ink font-medium' : 'text-smoke' }}">
                        Fiyat
                        @if ($this->activeFilterCount > count($selectedColors) + count($selectedSizes) + count($selectedCategories))
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[10px]">1</span>
                        @endif
                        <svg class="chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dd-panel p-4 w-72" style="left:-45px" x-data="{
                        minVal: {{ (int) ($minPrice ?: $this->minPossiblePrice) }},
                        maxVal: {{ (int) ($maxPrice ?: $this->maxPossiblePrice) }},
                        min: {{ $this->minPossiblePrice }},
                        max: {{ $this->maxPossiblePrice }},
                        get minPercent() { return ((this.minVal - this.min) / (this.max - this.min)) * 100 },
                        get maxPercent() { return ((this.maxVal - this.min) / (this.max - this.min)) * 100 },
                        updateMin(val) {
                            this.minVal = Math.min(Number(val), this.maxVal - 1);
                            $wire.set('minPrice', String(this.minVal));
                        },
                        updateMax(val) {
                            this.maxVal = Math.max(Number(val), this.minVal + 1);
                            $wire.set('maxPrice', String(this.maxVal));
                        }
                    }" x-on:click.stop>
                        <p class="font-body text-xs text-smoke mb-4 uppercase tracking-widest">Fiyat Aralığı (₺)</p>
                        <div class="flex justify-between font-body text-xs text-ink mb-4">
                            <span>₺<span x-text="minVal.toLocaleString('tr-TR')"></span></span>
                            <span>₺<span x-text="maxVal.toLocaleString('tr-TR')"></span></span>
                        </div>
                        <div class="relative h-1 bg-sand rounded-full mx-2 mb-6">
                            <div class="absolute h-1 bg-ink rounded-full"
                                :style="`left: ${minPercent}%; right: ${100 - maxPercent}%`"></div>
                            <input type="range" :min="min" :max="max" x-model="minVal"
                                @input="updateMin($event.target.value)"
                                class="range-thumb absolute w-full h-1 appearance-none bg-transparent pointer-events-none" />
                            <input type="range" :min="min" :max="max" x-model="maxVal"
                                @input="updateMax($event.target.value)"
                                class="range-thumb absolute w-full h-1 appearance-none bg-transparent pointer-events-none" />
                        </div>
                        <div class="flex gap-2 items-center">
                            <input type="number" :value="minVal" @change="updateMin($event.target.value)"
                                class="w-full border border-sand px-2 py-1.5 font-body text-xs focus:outline-none focus:border-ink bg-transparent text-center" />
                            <span class="text-smoke text-xs flex-shrink-0">—</span>
                            <input type="number" :value="maxVal" @change="updateMax($event.target.value)"
                                class="w-full border border-sand px-2 py-1.5 font-body text-xs focus:outline-none focus:border-ink bg-transparent text-center" />
                        </div>
                    </div>
                </details>

                {{-- Sıralama --}}
                <div class="ml-auto px-3 py-2">
                    <select wire:model.live="sort"
                        class="font-body text-xs border-0 focus:outline-none bg-transparent cursor-pointer text-smoke hover:text-ink">
                        <option value="latest">En Yeni</option>
                        <option value="featured">Öne Çıkanlar</option>
                        <option value="price_asc">Fiyat ↑</option>
                        <option value="price_desc">Fiyat ↓</option>
                        <option value="discounted">En Çok İndirimli</option>
                        <option value="name_asc">A-Z</option>
                    </select>
                </div>

                {{-- Filtre Temizle --}}
                @if ($this->activeFilterCount > 0)
                    <button wire:click="clearFilters"
                        class="px-4 py-3 font-body text-xs text-smoke hover:text-ink
                               underline transition-colors whitespace-nowrap border-l border-sand/40">
                        Temizle ({{ $this->activeFilterCount }})
                    </button>
                @endif
            </div>

            {{-- Aktif Filtre Etiketleri --}}
            @if ($this->activeFilterCount > 0)
                <div class="flex flex-wrap gap-2 px-4 py-3 border-t border-sand/30">
                    @foreach ($availableCategories->whereIn('id', $selectedCategories) as $cat)
                        <button wire:click="toggleCategory({{ $cat->id }})"
                            class="flex items-center gap-1 bg-blush-light px-3 py-1 font-body text-xs text-ink hover:bg-blush transition-colors">
                            {{ $cat->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @foreach ($availableColors->whereIn('id', $selectedColors) as $color)
                        <button wire:click="toggleColor({{ $color->id }})"
                            class="flex items-center gap-1.5 bg-blush-light px-3 py-1 font-body text-xs text-ink hover:bg-blush transition-colors">
                            <span class="w-3 h-3 rounded-full border border-smoke/20"
                                style="background-color: {{ $color->hex_code }}"></span>
                            {{ $color->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @foreach ($availableSizes->whereIn('id', $selectedSizes) as $size)
                        <button wire:click="toggleSize({{ $size->id }})"
                            class="flex items-center gap-1 bg-blush-light px-3 py-1 font-body text-xs text-ink hover:bg-blush transition-colors">
                            {{ $size->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @if ((int) $minPrice > $this->minPossiblePrice || (int) $maxPrice < $this->maxPossiblePrice)
                        <button
                            wire:click="$set('minPrice', '{{ $this->minPossiblePrice }}'); $set('maxPrice', '{{ $this->maxPossiblePrice }}')"
                            class="flex items-center gap-1 bg-blush-light px-3 py-1 font-body text-xs text-ink hover:bg-blush transition-colors">
                            ₺{{ number_format((int) $minPrice, 0, ',', '.') }} —
                            ₺{{ number_format((int) $maxPrice, 0, ',', '.') }}
                            <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sonuç Sayısı --}}
        <div class="flex items-center justify-between mb-6 px-4 md:px-0">
            <p class="font-body text-xs text-smoke">
                <span class="text-ink font-medium">{{ $totalCount }}</span> ürün bulundu
            </p>
            <p class="font-body text-xs text-smoke">
                {{ min($perPage, $totalCount) }} / {{ $totalCount }} gösteriliyor
            </p>
        </div>

        {{-- ── ÜRÜN GRID ── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-1 md:gap-5">
            @forelse($products as $product)
                @php
                    $galleryImages = $product->images
                        ->whereNull('color_id')
                        ->sortBy('sort_order')
                        ->pluck('image')
                        ->values();
                    if ($product->main_image && !$galleryImages->contains($product->main_image)) {
                        $galleryImages = collect([$product->main_image])
                            ->concat($galleryImages)
                            ->values();
                    }
                    if ($galleryImages->isEmpty() && $product->main_image) {
                        $galleryImages = collect([$product->main_image]);
                    }
                @endphp

                <div class="group" wire:key="product-{{ $product->id }}">
                    <div class="relative aspect-[3/4] overflow-hidden bg-gray-50 mb-1 md:mb-3" x-data="{
                        current: 0,
                        timer: null,
                        images: {{ json_encode($galleryImages->map(fn($img) => asset('storage/' . $img))->values()->toArray()) }}
                    }"
                        @mouseenter="if(images.length > 1) { timer = setInterval(() => { current = (current + 1) % images.length }, 800) }"
                        @mouseleave="clearInterval(timer); timer = null; current = 0">

                        <a href="{{ route('product', $product->slug) }}" class="block absolute inset-0">
                            @if ($galleryImages->isNotEmpty())
                                <template x-for="(img, index) in images" :key="index">
                                    <img :src="img" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover absolute inset-0 transition-opacity duration-500"
                                        :class="current === index ? 'opacity-100 z-10' : 'opacity-0 z-0'" />
                                </template>
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                            @endif
                        </a>

                        {{-- Görsel nokta indikatörleri --}}
                        @if ($galleryImages->count() > 1)
                            <div
                                class="absolute bottom-8 left-0 right-0 flex justify-center gap-1 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <template x-for="(img, index) in images" :key="index">
                                    <span class="w-1.5 h-1.5 rounded-full transition-colors duration-200"
                                        :class="current === index ? 'bg-white' : 'bg-white/50'"></span>
                                </template>
                            </div>
                        @endif

                        @livewire('wishlist-button', ['productId' => $product->id], key('wl-' . $product->id))

                        @if ($product->compare_price)
                            <span
                                class="absolute bottom-2 left-2 bg-ink text-cream pointer-events-none
                                 font-body text-[10px] tracking-widest uppercase px-2 py-1 z-20">
                                %{{ $product->discount_percentage }} İndirim
                            </span>
                        @endif
                        @if ($product->is_new)
                            <span
                                class="absolute bottom-2 right-2 bg-sand text-ink pointer-events-none
                                 font-body text-[10px] tracking-widest uppercase px-2 py-1 z-20">
                                Yeni
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1 px-1 md:px-0">
                        <h3 class="font-body text-xs text-ink leading-snug line-clamp-2">
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
                            <span class="font-body text-xs md:text-sm font-medium text-ink">
                                ₺{{ number_format($product->price, 2, ',', '.') }}
                            </span>
                        </div>

                        @php
                            $productColors = $product->variants->pluck('color')->filter()->unique('id');
                            $visibleColors = $productColors->take(5);
                            $extraCount = $productColors->count() - 5;
                        @endphp
                        @if ($visibleColors->count())
                            <div class="flex items-center gap-1 pt-1">
                                @foreach ($visibleColors as $c)
                                    @php
                                        $colorThumb = $product->images
                                            ->where('color_id', $c->id)
                                            ->sortBy('sort_order')
                                            ->first();
                                    @endphp
                                    <a href="{{ route('product', $product->slug) }}" title="{{ $c->name }}"
                                        class="relative overflow-hidden flex-shrink-0 rounded-full border-2 border-gray-200 hover:border-gray-500 transition-all"
                                        style="width:24px; height:24px;">
                                        @if ($colorThumb)
                                            <img src="{{ asset('storage/' . $colorThumb->image) }}"
                                                alt="{{ $c->name }}" class="w-full h-full object-cover"
                                                loading="lazy" />
                                        @else
                                            <div class="w-full h-full rounded-full"
                                                style="background-color: {{ $c->hex_code ?? '#ccc' }}"></div>
                                        @endif
                                    </a>
                                @endforeach
                                @if ($extraCount > 0)
                                    <span class="font-body text-[10px] text-smoke">+{{ $extraCount }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-20">
                    <p class="font-display text-3xl text-smoke font-light mb-3">Ürün bulunamadı</p>
                    <p class="font-body text-sm text-smoke/70 mb-6">Filtrelerinizi değiştirmeyi deneyin</p>
                    <button wire:click="clearFilters"
                        class="font-body text-xs tracking-widest uppercase border border-ink px-8 py-3 hover:bg-ink hover:text-cream transition-all">
                        Filtreleri Temizle
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Daha Fazla Göster --}}
        @if ($hasMore)
            <div class="text-center mt-12">
                <p class="font-body text-xs text-smoke mb-4">
                    {{ min($perPage, $totalCount) }} / {{ $totalCount }} ürün gösteriliyor
                </p>
                <button wire:click="loadMore"
                    class="inline-flex items-center gap-3 border border-ink text-ink
                           font-body text-xs tracking-widest uppercase px-10 py-3.5
                           hover:bg-ink hover:text-cream transition-all duration-300">
                    <span wire:loading.remove wire:target="loadMore">Daha Fazla Göster</span>
                    <span wire:loading wire:target="loadMore">Yükleniyor...</span>
                    <svg wire:loading.remove wire:target="loadMore" class="w-4 h-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>
        @endif

    </div>

    @php
        $schemaData = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Abiye Modelleri',
            'description' => "Tüm abiye modelleri — Eren Abiye'de.",
            'url' => route('all-products'),
            'breadcrumb' => [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => config('app.url')],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Abiye Modelleri',
                        'item' => route('all-products'),
                    ],
                ],
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $products
                    ->values()
                    ->map(
                        fn($p, $i) => [
                            '@type' => 'ListItem',
                            'position' => $i + 1,
                            'url' => route('product', $p->slug),
                            'name' => $p->name,
                        ],
                    )
                    ->values()
                    ->all(),
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
</div>
