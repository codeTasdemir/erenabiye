<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Category;
use App\Models\Product;
use App\Models\Color;
use App\Models\Size;

new #[Layout('layouts.app')] class extends Component {
    public string $slug = '';
    public ?Category $category = null;

    #[Url]
    public array $colors = [];
    #[Url]
    public array $sizes = [];
    #[Url]
    public string $sort = 'latest';
    #[Url]
    public string $minPrice = '';
    #[Url]
    public string $maxPrice = '';

    public bool $filterOpen = false;
    public int $perPage = 25;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $seo = \App\Services\SeoService::forCategory($this->category);
        $this->js('document.title = ' . json_encode($seo['title']));
    }

    public function loadMore(): void
    {
        $this->perPage += 25;
    }

    public function updatedColors(): void
    {
        $this->perPage = 25;
    }
    public function updatedSizes(): void
    {
        $this->perPage = 25;
    }
    public function updatedSort(): void
    {
        $this->perPage = 25;
    }
    public function updatedMinPrice(): void
    {
        $this->perPage = 25;
    }
    public function updatedMaxPrice(): void
    {
        $this->perPage = 25;
    }

    public function clearFilters(): void
    {
        $this->colors = [];
        $this->sizes = [];
        $this->minPrice = '';
        $this->maxPrice = '';
        $this->sort = 'latest';
        $this->perPage = 25;
    }

    public function toggleColor(int $colorId): void
    {
        if (in_array($colorId, $this->colors)) {
            $this->colors = array_values(array_filter($this->colors, fn($c) => $c !== $colorId));
        } else {
            $this->colors[] = $colorId;
        }
        $this->perPage = 25;
    }

    public function toggleSize(int $sizeId): void
    {
        if (in_array($sizeId, $this->sizes)) {
            $this->sizes = array_values(array_filter($this->sizes, fn($s) => $s !== $sizeId));
        } else {
            $this->sizes[] = $sizeId;
        }
        $this->perPage = 25;
    }

    public function getActiveFilterCountProperty(): int
    {
        return count($this->colors) + count($this->sizes) + ($this->minPrice ? 1 : 0) + ($this->maxPrice ? 1 : 0);
    }

    public function with(): array
    {
        $categoryIds = Category::where('parent_id', $this->category->id)->pluck('id')->push($this->category->id)->toArray();

        $query = Product::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->with(['category', 'variants.color', 'variants.size', 'images']);

        if (!empty($this->colors)) {
            $query->whereHas('variants', fn($q) => $q->whereIn('color_id', $this->colors)->where('stock', '>', 0));
        }
        if (!empty($this->sizes)) {
            $query->whereHas('variants', fn($q) => $q->whereIn('size_id', $this->sizes)->where('stock', '>', 0));
        }
        if ($this->minPrice !== '') {
            $query->where('price', '>=', (float) $this->minPrice);
        }
        if ($this->maxPrice !== '') {
            $query->where('price', '<=', (float) $this->maxPrice);
        }

        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'featured' => $query->orderBy('is_featured', 'desc')->latest(),
            default => $query->latest(),
        };

        $totalCount = $query->count();
        $products = $query->take($this->perPage)->get();

        return [
            'products' => $products,
            'totalCount' => $totalCount,
            'hasMore' => $totalCount > $this->perPage,
            'availableColors' => Color::where('is_active', true)->orderBy('sort_order')->get(),
            'availableSizes' => Size::where('is_active', true)->orderBy('sort_order')->get(),
            'subcategories' => $this->category->children()->where('is_active', true)->get(),
        ];
    }
};
?>

<div>
    <style>
        details.filter-dropdown {
            position: relative;
        }

        details.filter-dropdown summary {
            list-style: none;
            cursor: pointer;
        }

        details.filter-dropdown summary::-webkit-details-marker {
            display: none;
        }

        details.filter-dropdown .dropdown-panel {
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 4px;
            background: white;
            border: 1px solid rgba(194, 178, 160, 0.6);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            z-index: 30;
        }

        details.filter-dropdown .chevron {
            transition: transform 150ms;
        }

        details.filter-dropdown[open] .chevron {
            transform: rotate(180deg);
        }
    </style>

    {{-- Breadcrumb --}}
    <div class="border-b border-sand/20">
        <div class="max-w-screen-xl mx-auto px-4 md:px-6 py-3">
            <nav class="flex items-center gap-2 font-body text-xs text-smoke">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                @if ($category->parent)
                    <a href="{{ route('category', $category->parent->slug) }}"
                        class="hover:text-ink transition-colors">{{ $category->parent->name }}</a>
                    <span>/</span>
                @endif
                <span class="text-ink">{{ $category->name }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-4 md:px-6 py-8 md:py-10">

        {{-- Sayfa Başlığı --}}
        <div class="text-center mb-6 md:mb-8">
            <h1 class="font-display text-3xl md:text-5xl font-light text-ink">{{ $category->name }}</h1>
            @if ($category->description)
                <p class="font-body text-xs md:text-sm text-smoke mt-3 max-w-2xl mx-auto leading-relaxed">
                    {{ $category->description }}
                </p>
            @endif
        </div>

        {{-- Alt Kategoriler --}}
        @if ($subcategories->count())
            <div class="mb-8">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-2 max-w-3xl mx-auto">
                    @foreach ($subcategories as $sub)
                        <a href="{{ route('category', $sub->slug) }}"
                            class="flex items-center gap-2 font-body text-xs text-ink hover:text-smoke transition-colors py-1">
                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                            {{ $sub->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── FİLTRE ÇUBUĞU ── --}}
        <div class=" border  border-sand/40 py-1 mb-6 md:mb-8">
            <div class="flex flex-wrap items-center gap-1 md:gap-0">

                {{-- Renk Filtresi --}}
                <details class="filter-dropdown">
                    <summary
                        class="flex items-center gap-1 font-body text-xs text-ink  md:px-4 py-2
                                   border-r border-sand/40 hover:text-smoke transition-colors whitespace-nowrap">
                        Renk
                        @if (count($this->colors) > 0)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[9px]">
                                {{ count($this->colors) }}
                            </span>
                        @endif
                        <svg class="chevron w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dropdown-panel p-4 w-64">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableColors as $color)
                                <button wire:click="toggleColor({{ $color->id }})" title="{{ $color->name }}"
                                    class="w-7 h-7 rounded-full border-2 transition-all flex-shrink-0
                                           {{ in_array($color->id, $this->colors) ? 'border-ink scale-110' : 'border-transparent hover:border-smoke/50' }}"
                                    style="background-color: {{ $color->hex_code ?? '#ccc' }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1)">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details>

                {{-- Beden Filtresi --}}
                <details class="filter-dropdown">
                    <summary
                        class="flex items-center gap-1.5 font-body text-xs text-ink px-3 md:px-4 py-2
                                   border-r border-sand/40 hover:text-smoke transition-colors whitespace-nowrap">
                        Beden
                        @if (count($this->sizes) > 0)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[9px]">
                                {{ count($this->sizes) }}
                            </span>
                        @endif
                        <svg class="chevron w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dropdown-panel p-4 w-56">
                        <div class="flex flex-wrap gap-2">
                            @foreach ($availableSizes as $size)
                                <button wire:click="toggleSize({{ $size->id }})"
                                    class="min-w-[2.5rem] h-9 px-2 border font-body text-xs transition-all
                                           {{ in_array($size->id, $this->sizes) ? 'bg-ink text-cream border-ink' : 'border-sand text-ink hover:border-ink' }}">
                                    {{ $size->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </details>

                {{-- Fiyat Filtresi --}}
                <details class="filter-dropdown">
                    <summary
                        class="flex items-center gap-1.5 font-body text-xs text-ink px-3 md:px-4 py-2
                                   border-r border-sand/40 hover:text-smoke transition-colors whitespace-nowrap">
                        Fiyat
                        @if ($this->minPrice || $this->maxPrice)
                            <span
                                class="bg-ink text-cream rounded-full w-4 h-4 flex items-center justify-center text-[9px]">1</span>
                        @endif
                        <svg class="chevron w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="dropdown-panel p-4 w-56">
                        <p class="font-body text-xs text-smoke mb-3 uppercase tracking-widest">Fiyat Aralığı (₺)</p>
                        <div class="flex gap-2 items-center">
                            <input wire:model.live.debounce.600ms="minPrice" type="number" placeholder="Min"
                                class="w-full border border-sand px-3 py-2 font-body text-xs
                                          focus:outline-none focus:border-ink bg-transparent" />
                            <span class="text-smoke text-xs">—</span>
                            <input wire:model.live.debounce.600ms="maxPrice" type="number" placeholder="Max"
                                class="w-full border border-sand px-3 py-2 font-body text-xs
                                          focus:outline-none focus:border-ink bg-transparent" />
                        </div>
                    </div>
                </details>

                {{-- Temizle --}}
                @if ($this->activeFilterCount > 0)
                    <button wire:click="clearFilters"
                        class="font-body text-xs text-smoke hover:text-ink underline transition-colors px-3 py-2 whitespace-nowrap">
                        Temizle ({{ $this->activeFilterCount }})
                    </button>
                @endif

                {{-- Sıralama --}}
                <div class="ml-auto">
                    <select wire:model.live="sort"
                        class="font-body text-xs border-0 border-l border-sand/40 pl-3 md:pl-4 py-2
                               focus:outline-none bg-transparent cursor-pointer text-ink">
                        <option value="latest">Sıralama</option>
                        <option value="latest">En Yeni</option>
                        <option value="featured">Öne Çıkanlar</option>
                        <option value="price_asc">Fiyat: Düşükten Yükseğe</option>
                        <option value="price_desc">Fiyat: Yüksekten Düşüğe</option>
                        <option value="name_asc">İsme Göre A-Z</option>
                    </select>
                </div>
            </div>

            {{-- Aktif Filtre Etiketleri --}}
            @if ($this->activeFilterCount > 0)
                <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-sand/30">
                    @foreach ($availableColors->whereIn('id', $this->colors) as $color)
                        <button wire:click="toggleColor({{ $color->id }})"
                            class="flex items-center gap-1.5 bg-sand/20 px-3 py-1 font-body text-xs hover:bg-sand/40 transition-colors">
                            <span class="w-3 h-3 rounded-full flex-shrink-0"
                                style="background-color: {{ $color->hex_code }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1)"></span>
                            {{ $color->name }}
                            <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @foreach ($availableSizes->whereIn('id', $this->sizes) as $size)
                        <button wire:click="toggleSize({{ $size->id }})"
                            class="flex items-center gap-1 bg-sand/20 px-3 py-1 font-body text-xs hover:bg-sand/40 transition-colors">
                            {{ $size->name }}
                            <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endforeach
                    @if ($this->minPrice || $this->maxPrice)
                        <button wire:click="$set('minPrice', ''); $set('maxPrice', '')"
                            class="flex items-center gap-1 bg-sand/20 px-3 py-1 font-body text-xs hover:bg-sand/40 transition-colors">
                            ₺{{ $this->minPrice ?: '0' }} — ₺{{ $this->maxPrice ?: '∞' }}
                            <span class="text-smoke ml-0.5">×</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Sonuç Sayısı --}}
        <p class="font-body text-xs text-smoke mb-4">
            <span class="text-ink font-medium">{{ $totalCount }}</span> ürün bulundu
        </p>

        {{-- Ürün Grid --}}
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


        {{-- Daha Fazla Yükle --}}
        @if ($hasMore)
            <div class="mt-10 flex flex-col items-center gap-2">
                <button wire:click="loadMore" wire:loading.attr="disabled"
                    class="font-body text-xs tracking-widest2 uppercase border border-ink
                           px-12 py-3.5 hover:bg-ink hover:text-cream transition-all duration-200
                           disabled:opacity-50 disabled:cursor-wait">
                    <span wire:loading.remove wire:target="loadMore">Daha Fazla Göster</span>
                    <span wire:loading wire:target="loadMore">Yükleniyor...</span>
                </button>
                <p class="font-body text-xs text-smoke">
                    {{ $products->count() }} / {{ $totalCount }} ürün gösteriliyor
                </p>
            </div>
        @endif

    </div>

    {{-- Mobil Filtre Drawer --}}
    @if ($filterOpen)
        <div class="fixed inset-0 z-50 lg:hidden">
            <div class="absolute inset-0 bg-ink/50" wire:click="$toggle('filterOpen')"></div>
            <div class="absolute right-0 top-0 bottom-0 w-80 bg-cream overflow-y-auto p-6 space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="font-body text-xs tracking-widest2 uppercase font-medium">Filtrele</h2>
                    <button wire:click="$toggle('filterOpen')" class="text-smoke hover:text-ink">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div>
                    <h3 class="font-body text-xs tracking-widest uppercase text-smoke mb-4">Renk</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableColors as $color)
                            <button wire:click="toggleColor({{ $color->id }})" title="{{ $color->name }}"
                                class="w-8 h-8 rounded-full border-2 transition-all
                                       {{ in_array($color->id, $this->colors) ? 'border-ink scale-110' : 'border-transparent' }}"
                                style="background-color: {{ $color->hex_code ?? '#ccc' }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1)">
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h3 class="font-body text-xs tracking-widest uppercase text-smoke mb-4">Beden</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableSizes as $size)
                            <button wire:click="toggleSize({{ $size->id }})"
                                class="w-12 h-10 border font-body text-xs transition-all
                                       {{ in_array($size->id, $this->sizes) ? 'bg-ink text-cream border-ink' : 'border-sand text-ink' }}">
                                {{ $size->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h3 class="font-body text-xs tracking-widest uppercase text-smoke mb-4">Fiyat (₺)</h3>
                    <div class="flex gap-2 items-center">
                        <input wire:model.live.debounce.600ms="minPrice" type="number" placeholder="Min"
                            class="w-full border border-sand px-3 py-2 font-body text-xs focus:outline-none bg-transparent" />
                        <span class="text-smoke">—</span>
                        <input wire:model.live.debounce.600ms="maxPrice" type="number" placeholder="Max"
                            class="w-full border border-sand px-3 py-2 font-body text-xs focus:outline-none bg-transparent" />
                    </div>
                </div>
                @if ($this->activeFilterCount > 0)
                    <button wire:click="clearFilters"
                        class="w-full border border-ink font-body text-xs tracking-widest2
                               uppercase py-3 hover:bg-ink hover:text-cream transition-all">
                        Filtreleri Temizle ({{ $this->activeFilterCount }})
                    </button>
                @endif
            </div>
        </div>
    @endif

</div>

@php
    $schemaDescription = $category->meta_description ?? $category->name . " modelleri Eren Abiye'de.";
@endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": @json($category->name),
    "description": @json($schemaDescription),
    "url": "{{ route('category', $category->slug) }}",
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Ana Sayfa",
                "item": "{{ config('app.url') }}"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": @json($category->name),
                "item": "{{ route('category', $category->slug) }}"
            }
        ]
    },
    "mainEntity": {
        "@type": "ItemList",
        "itemListElement": [
            @foreach($products as $index => $product)
{
    "@type": "ListItem",
    "position": {{ $loop->iteration }},
    "url": "{{ route('product', $product->slug) }}",
    "name": @json($product->name)
}@if(!$loop->last),@endif
@endforeach
        ]
    }
}
</script>
