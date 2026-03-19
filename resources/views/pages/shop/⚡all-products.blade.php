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

    public function loadMore(): void
    {
        $this->perPage += 25;
    }

    public function clearFilters(): void
    {
        $this->selectedColors = [];
        $this->selectedSizes = [];
        $this->selectedCategories = [];
        $this->minPrice = '';
        $this->maxPrice = '';
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
        return count($this->selectedColors) + count($this->selectedSizes) + count($this->selectedCategories) + ($this->minPrice ? 1 : 0) + ($this->maxPrice ? 1 : 0);
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
        if ($this->minPrice !== '') {
            $query->where('price', '>=', (float) $this->minPrice);
        }
        if ($this->maxPrice !== '') {
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

    <div class="max-w-screen-xl mx-auto px-1 py-8">

        {{-- Başlık --}}
        <div class="text-center mb-8">
            <h1 class="font-display text-4xl md:text-5xl font-light text-ink mb-3">
                Abiye Modelleri
            </h1>
            <p class="font-body text-sm text-smoke max-w-2xl mx-auto leading-relaxed">
                Özel günleriniz için en şık abiye modelleri
            </p>
        </div>

        {{-- ── FİLTRE BARI ── --}}
        <div class="border border-sand/40 bg-white mb-8">
            <div class="flex items-center flex-wrap gap-0 divide-x divide-sand/40">

                {{-- Kategori --}}
                {{-- <details class="filter-dd">
                    <summary class="flex items-center gap-2 px-5 py-3 font-body text-xs
                                   hover:bg-blush-light/50 transition-colors whitespace-nowrap
                                   {{ count($selectedCategories) > 0 ? 'text-ink font-medium' : 'text-smoke' }}">
                        Kategori
                        @if (count($selectedCategories) > 0)
                            <span class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[10px]">
                                {{ count($selectedCategories) }}
                            </span>
                        @endif
                        <svg class="chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </summary>
                    <div class="dd-panel p-4 min-w-[220px]">
                        <div class="flex flex-col gap-2">
                            @foreach ($availableCategories as $cat)
                                <button wire:click="toggleCategory({{ $cat->id }})"
                                    class="flex items-center gap-2 text-left font-body text-xs
                                           py-1 hover:text-ink transition-colors
                                           {{ in_array($cat->id, $selectedCategories) ? 'text-ink font-medium' : 'text-smoke' }}">
                                    <span class="w-4 h-4 border flex items-center justify-center flex-shrink-0
                                                 {{ in_array($cat->id, $selectedCategories) ? 'bg-ink border-ink' : 'border-sand' }}">
                                        @if (in_array($cat->id, $selectedCategories))
                                            <svg class="w-2.5 h-2.5 text-cream" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </span>
                                    {{ $cat->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details> --}}

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
                                           {{ in_array($color->id, $selectedColors)
                                               ? 'border-ink scale-110 shadow-md'
                                               : 'border-smoke/20 hover:border-smoke' }}"
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
                                           {{ in_array($size->id, $selectedSizes)
                                               ? 'bg-ink text-cream border-ink'
                                               : 'border-sand text-ink hover:border-ink' }}">
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
                                   {{ $minPrice || $maxPrice ? 'text-ink font-medium' : 'text-smoke' }}">
                        Fiyat
                        @if ($minPrice || $maxPrice)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[10px]">1</span>
                        @endif
                        <svg class="chevron w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dd-panel p-4 min-w-[220px]">
                        <p class="font-body text-xs text-smoke mb-3">Fiyat Aralığı (₺)</p>
                        <div class="flex gap-2 items-center">
                            <input wire:model.live.debounce.600ms="minPrice" type="number" placeholder="Min"
                                class="w-full border border-sand px-3 py-2 font-body text-xs
                                       focus:outline-none focus:border-ink bg-transparent" />
                            <span class="text-smoke font-body text-xs">—</span>
                            <input wire:model.live.debounce.600ms="maxPrice" type="number" placeholder="Max"
                                class="w-full border border-sand px-3 py-2 font-body text-xs
                                       focus:outline-none focus:border-ink bg-transparent" />
                        </div>
                    </div>
                </details>

                {{-- Sıralama --}}
                <div class="ml-auto px-3 py-2">
                    <select wire:model.live="sort"
                        class="font-body text-xs border-0 focus:outline-none bg-transparent
                               cursor-pointer text-smoke hover:text-ink">
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
                            class="flex items-center gap-1 bg-blush-light px-3 py-1
                                   font-body text-xs text-ink hover:bg-blush transition-colors">
                            {{ $cat->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @foreach ($availableColors->whereIn('id', $selectedColors) as $color)
                        <button wire:click="toggleColor({{ $color->id }})"
                            class="flex items-center gap-1.5 bg-blush-light px-3 py-1
                                   font-body text-xs text-ink hover:bg-blush transition-colors">
                            <span class="w-3 h-3 rounded-full border border-smoke/20"
                                style="background-color: {{ $color->hex_code }}"></span>
                            {{ $color->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @foreach ($availableSizes->whereIn('id', $selectedSizes) as $size)
                        <button wire:click="toggleSize({{ $size->id }})"
                            class="flex items-center gap-1 bg-blush-light px-3 py-1
                                   font-body text-xs text-ink hover:bg-blush transition-colors">
                            {{ $size->name }} <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @if ($minPrice || $maxPrice)
                        <button wire:click="$set('minPrice', ''); $set('maxPrice', '')"
                            class="flex items-center gap-1 bg-blush-light px-3 py-1
                                   font-body text-xs text-ink hover:bg-blush transition-colors">
                            ₺{{ $minPrice ?: '0' }} — ₺{{ $maxPrice ?: '∞' }}
                            <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sonuç Sayısı --}}
        <div class="flex items-center justify-between mb-6">
            <p class="font-body text-xs text-smoke">
                <span class="text-ink font-medium">{{ $totalCount }}</span> ürün bulundu
            </p>
            <p class="font-body text-xs text-smoke">
                {{ min($perPage, $totalCount) }} / {{ $totalCount }} gösteriliyor
            </p>
        </div>

        {{-- ── ÜRÜN GRID ── --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
            @forelse($products as $product)
                <div class="group" wire:key="product-{{ $product->id }}">

                    <div class="relative aspect-[3/4] overflow-hidden bg-gray-50 mb-3">
                        <a href="{{ route('product', $product->slug) }}" class="block absolute inset-0">
                            @if ($product->main_image)
                                <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                            @endif
                        </a>

                        @livewire('wishlist-button', ['productId' => $product->id], key('wl-' . $product->id))

                        @if ($product->compare_price)
                            <span
                                class="absolute bottom-2 left-2 bg-ink text-cream pointer-events-none
                                 font-body text-[10px] tracking-widest uppercase px-2 py-1">
                                %{{ $product->discount_percentage }} İndirim
                            </span>
                        @endif

                        @if ($product->is_new)
                            <span
                                class="absolute bottom-2 right-2 bg-sand text-ink pointer-events-none
                                 font-body text-[10px] tracking-widest uppercase px-2 py-1">
                                Yeni
                            </span>
                        @endif
                    </div>

                    <div class="space-y-1">
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
                            <div class="flex items-center gap-1 pt-0.5">
                                @foreach ($visibleColors as $c)
                                    @if ($c->hex_code)
                                        <span class="w-3.5 h-3.5 rounded-full flex-shrink-0"
                                            style="background-color: {{ $c->hex_code }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12)"
                                            title="{{ $c->name }}">
                                        </span>
                                    @endif
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
                        class="font-body text-xs tracking-widest2 uppercase border border-ink
                       px-8 py-3 hover:bg-ink hover:text-cream transition-all">
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
                           font-body text-xs tracking-widest2 uppercase px-10 py-3.5
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
            'description' => 'Tüm abiye modelleri — Eren Abiye\'de.',
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
