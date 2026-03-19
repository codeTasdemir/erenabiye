<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Product;
use App\Models\ProductVariant;

new #[Layout('layouts.app')] class extends Component {
    public Product $product;
    public ?int $selectedColorId = null;
    public ?int $selectedSizeId = null;
    public int $quantity = 1;
    public int $activeImage = 0;
    public bool $addedToCart = false;
    public array $images = [];
    public ?string $activeVideoId = null;
    public string $variant = 'relative';

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'images', 'variants.color', 'variants.size', 'colorVideos.color'])
            ->firstOrFail();

        $seo = \App\Services\SeoService::forProduct($this->product);
        $this->js('document.title = ' . json_encode($seo['title']));

        $firstVariant = $this->product->variants->where('is_active', true)->where('stock', '>', 0)->first();

        if ($firstVariant) {
            $this->selectedColorId = $firstVariant->color_id;

            $colorVideo = $this->product->colorVideos->firstWhere('color_id', $firstVariant->color_id);
            $this->activeVideoId = $colorVideo?->video_id ?? null;
        }

        $this->images = $this->buildImagesFor($this->selectedColorId);

        $this->syncQuantityFromCart();
    }

    private function buildImagesFor(?int $colorId): array
    {
        if (!$colorId) {
            return [];
        }

        $colorImages = $this->product->images->where('color_id', $colorId)->sortBy('sort_order')->pluck('image');

        if ($colorImages->isNotEmpty()) {
            return $colorImages->filter()->unique()->values()->toArray();
        }

        return [];
    }
    #[Computed(cache: false)]
    public function availableSizes()
    {
        if (!$this->selectedColorId) {
            return collect();
        }

        return ProductVariant::where('product_id', $this->product->id)
            ->where('color_id', (int) $this->selectedColorId)
            ->whereNotNull('size_id')
            ->with('size')
            ->get()
            ->map(
                fn($v) => [
                    'size' => $v->size,
                    'stock' => $v->stock,
                    'variant' => $v,
                ],
            )
            ->values();
    }

    #[Computed(cache: false)]
    public function availableColors()
    {
        return ProductVariant::where('product_id', $this->product->id)
            ->whereNotNull('color_id')
            ->with('color')
            ->get()
            ->groupBy('color_id')
            ->map(
                fn($variants) => [
                    'color' => $variants->first()->color,
                    'inStock' => $variants->sum('stock') > 0,
                ],
            )
            ->values();
    }

    #[Computed(cache: false)]
    public function selectedVariant(): ?ProductVariant
    {
        if (!$this->selectedColorId || !$this->selectedSizeId) {
            return null;
        }

        return ProductVariant::where('product_id', $this->product->id)->where('color_id', (int) $this->selectedColorId)->where('size_id', (int) $this->selectedSizeId)->where('is_active', true)->first();
    }

    #[Computed(cache: false)]
    public function finalPrice(): float
    {
        if ($this->selectedVariant) {
            return $this->product->price + $this->selectedVariant->price_modifier;
        }
        return $this->product->price;
    }

    #[Computed(cache: false)]
    public function finalComparePrice(): ?float
    {
        if (!$this->product->compare_price) {
            return null;
        }

        if ($this->selectedVariant) {
            return $this->product->compare_price + $this->selectedVariant->price_modifier;
        }

        return $this->product->compare_price;
    }

    #[Computed(cache: false)]
    public function discountPercentage(): ?int
    {
        $compare = $this->finalComparePrice;
        if (!$compare || $compare <= $this->finalPrice) {
            return null;
        }

        return (int) round((($compare - $this->finalPrice) / $compare) * 100);
    }

    public function selectColor(int $colorId): void
    {
        $this->selectedColorId = $colorId;
        $this->selectedSizeId = null;
        $this->addedToCart = false;
        $this->images = $this->buildImagesFor($colorId);
        $this->activeImage = 0;

        $colorVideo = $this->product->colorVideos->firstWhere('color_id', $colorId);
        $this->activeVideoId = $colorVideo?->video_id ?? null;

        $color = $this->product->variants->where('color_id', $colorId)->first()?->color;

        $this->dispatch('images-updated', images: array_map(fn($img) => asset('storage/' . $img), $this->images), colorHex: $color?->hex_code ?? '');
    }

    public function selectSize(int $sizeId): void
    {
        $this->selectedSizeId = $sizeId;
        $this->addedToCart = false;
        $this->syncQuantityFromCart();
    }

    private function syncQuantityFromCart(): void
    {
        if (!$this->selectedSizeId || !$this->selectedColorId) {
            $this->quantity = 1;
            return;
        }

        $variant = $this->selectedVariant;
        if (!$variant) {
            $this->quantity = 1;
            return;
        }

        if (auth()->check()) {
            $cartItem = \App\Models\CartItem::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->where('product_variant_id', $variant->id)
                ->first();
            $this->quantity = $cartItem?->quantity ?? 1;
        } else {
            $cart = session()->get('cart', []);
            $key = $this->product->id . '_' . $variant->id;
            $this->quantity = $cart[$key]['quantity'] ?? 1;
        }
    }
    public function incrementQuantity(): void
    {
        $maxStock = $this->selectedVariant?->stock ?? $this->product->stock;
        if ($this->quantity < $maxStock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        if ($this->product->variants->count() > 0) {
            if (!$this->selectedColorId) {
                $this->addError('cart', 'Lütfen bir renk seçin.');
                return;
            }
            if (!$this->selectedSizeId) {
                $this->addError('cart', 'Lütfen bir beden seçin.');
                return;
            }
            if (!$this->selectedVariant) {
                $this->addError('cart', 'Seçilen varyant stokta yok.');
                return;
            }
        }

        $stock = $this->selectedVariant?->stock ?? $this->product->stock;

        if ($this->quantity > $stock) {
            $this->addError('cart', "Bu üründen en fazla {$stock} adet ekleyebilirsiniz.");
            return;
        }

        if (auth()->check()) {
            $cartItem = \App\Models\CartItem::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->where('product_variant_id', $this->selectedVariant?->id)
                ->first();

            if ($cartItem) {
                $cartItem->update(['quantity' => $this->quantity]);
            } else {
                \App\Models\CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->selectedVariant?->id,
                    'quantity' => $this->quantity,
                ]);
            }
        } else {
            $cart = session()->get('cart', []);
            $key = $this->product->id . '_' . ($this->selectedVariant?->id ?? 0);

            if (isset($cart[$key])) {
                $cart[$key]['quantity'] = $this->quantity;
            } else {
                $cart[$key] = [
                    'product_id' => $this->product->id,
                    'product_variant_id' => $this->selectedVariant?->id,
                    'quantity' => $this->quantity,
                    'name' => $this->product->name,
                    'price' => $this->finalPrice,
                    'image' => $this->product->main_image,
                    'variant_info' => $this->selectedVariant?->label,
                ];
            }
            session()->put('cart', $cart);
        }

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
        $this->js("setTimeout(() => { \$wire.addedToCart = false }, 3000)");
    }

    public function with(): array
    {
        $related = Product::where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->with(['variants.color'])
            ->take(4)
            ->get();

        return ['related' => $related];
    }
};
?>

<div>
    {{-- ── BREADCRUMB ── --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-screen-xl mx-auto px-4 py-2">
            <nav class="flex items-center gap-1 text-[11px] text-gray-500 flex-wrap leading-relaxed">
                <a href="{{ route('home') }}" class="hover:text-gray-800 transition-colors">Anasayfa</a>
                @php
                    $breadcrumbs = [];
                    $cat = $product->category;
                    while ($cat) {
                        array_unshift($breadcrumbs, $cat);
                        $cat = method_exists($cat, 'parent') ? $cat->parent ?? null : null;
                    }
                @endphp
                @foreach ($breadcrumbs as $bc)
                    <span class="text-gray-300 mx-0.5">/</span>
                    <a href="{{ route('category', $bc->slug) }}" class="hover:text-gray-800 transition-colors">
                        {{ $bc->name }}
                    </a>
                @endforeach
                <span class="text-gray-300 mx-0.5">/</span>
                <span class="text-gray-700 line-clamp-1">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    {{-- ── ANA ALAN ── --}}
    <div class="max-w-screen-xl mx-auto px-4 py-6">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- ── SOL: GÖRSELLER ── --}}
            <div class="lg:w-[500px] flex-shrink-0">
                <div class="flex gap-2" x-data="{
                    activeImage: 0,
                    images: {{ json_encode(array_map(fn($img) => asset('storage/' . $img), $images)) }},
                    colorHex: '{{ collect($this->availableColors)->firstWhere('color.id', $selectedColorId)['color']->hex_code ?? '' }}'
                }"
                    @images-updated.window="images = $event.detail.images; colorHex = $event.detail.colorHex; activeImage = 0"
                    @set-active-image.window="activeImage = $event.detail.index">

                    {{-- Thumbnail'ler --}}
                    <template x-if="images.length > 1">
                        <div class="flex flex-col gap-1.5 w-[68px] flex-shrink-0">
                            <template x-for="(img, index) in images" :key="index">
                                <button @click="$dispatch('set-active-image', { index: index })"
                                    class="w-[68px] aspect-[2/3] overflow-hidden border-2 transition-all flex-shrink-0"
                                    :class="activeImage === index ? 'border-gray-800' : 'border-gray-200 hover:border-gray-400'">
                                    <img :src="img" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover" loading="lazy" />
                                </button>
                            </template>
                        </div>
                    </template>

                    {{-- Ana Görsel --}}
                    <div class="flex-1 relative overflow-hidden bg-gray-50" style="aspect-ratio: 2/3;"
                        x-on:livewire:navigating.window="$data.videoOpen = false" x-data="{ videoOpen: false }">

                        <div x-show="!videoOpen" class="absolute inset-0">
                            <template x-if="images.length > 0">
                                <div class="absolute inset-0">
                                    <template x-for="(img, index) in images" :key="index">
                                        <img :src="img" alt="{{ $product->name }}"
                                            class="w-full h-full object-cover absolute inset-0 transition-opacity duration-300"
                                            :class="activeImage === index ? 'opacity-100 z-10' : 'opacity-0 z-0'" />
                                    </template>
                                </div>
                            </template>

                            {{-- Görseli olmayan renk için placeholder --}}
                            <template x-if="images.length === 0">
                                <div
                                    class="absolute inset-0 bg-gray-100 flex flex-col items-center justify-center gap-2">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="font-body text-xs text-gray-400">Ürünün seçilen rengine ait görsel
                                        henüz yüklenmemiştir!</span>
                                </div>
                            </template>

                            {{-- Sol Ok --}}
                            <button @click="activeImage = activeImage > 0 ? activeImage - 1 : images.length - 1"
                                x-show="images.length > 1"
                                class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white/90 border border-gray-200
               flex items-center justify-center hover:bg-white transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                            </button>

                            {{-- Sağ Ok --}}
                            <button @click="activeImage = activeImage < images.length - 1 ? activeImage + 1 : 0"
                                x-show="images.length > 1"
                                class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white/90 border border-gray-200
               flex items-center justify-center hover:bg-white transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>

                        @if ($activeVideoId)
                            <div x-show="videoOpen" class="absolute inset-0 z-30 bg-black">
                                <iframe
                                    x-bind:src="videoOpen ?
                                        'https://www.youtube.com/embed/{{ $activeVideoId }}?autoplay=1&mute=1&controls=0&rel=0&playsinline=1&loop=1&playlist={{ $activeVideoId }}&disablekb=1&iv_load_policy=3&cc_load_policy=0&fs=0' :
                                        ''"
                                    class="absolute inset-0 w-full h-full pointer-events-none" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        @endif

                        @if ($activeVideoId)
                            <button @click="videoOpen = !videoOpen"
                                class="d-block absolute top-3 left-3 z-40 items-center gap-3
                           text-white transition-all pl-3 pr-3 py-3 rounded-full shadow-md backdrop-blur-sm"
                                :class="videoOpen ? 'bg-black/80 hover:bg-black' : 'bg-black/60 hover:bg-black/80'">
                                <svg x-show="!videoOpen" class="w-10 h-10 flex-shrink-0" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                                <svg x-show="videoOpen" class="w-10 h-10 flex-shrink-0" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="font-body text-[11px] tracking-wide uppercase"
                                    x-text="videoOpen ? 'Kapat' : 'Video'"></span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── SAĞ: ÜRÜN BİLGİLERİ ── --}}
            <div class="flex-1 min-w-0">

                {{-- Başlık + Favori --}}
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h1 class="text-lg font-semibold text-gray-900 leading-snug">
                        {{ $product->name }}
                    </h1>
                    <div class="flex flex-col items-center gap-0.5 flex-shrink-0">
                        @livewire('wishlist-button', ['productId' => $product->id, 'variant' => 'relative'], key('wl-' . $product->id))
                    </div>
                </div>

                {{-- Fiyat --}}
                <div class="flex items-center gap-3 mb-4">
                    @if ($this->finalComparePrice && $this->finalComparePrice > $this->finalPrice)
                        <span class="text-sm text-gray-400 line-through">
                            ₺{{ number_format($this->finalComparePrice, 2, ',', '.') }}
                        </span>
                    @endif
                    <span class="text-2xl font-bold text-gray-900">
                        ₺{{ number_format($this->finalPrice, 2, ',', '.') }}
                    </span>
                    @if ($this->discountPercentage)
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">
                            %{{ $this->discountPercentage }} İndirim
                        </span>
                    @endif
                </div>

                {{-- Kargo Avantajları --}}
                <div class="flex flex-col gap-1 mb-5">
                    @foreach (['Aynı Gün Kargo', '3000TL Üzeri Ücretsiz Kargo', 'Ücretsiz İade', 'Kapıda Ödeme'] as $badge)
                        <div class="flex items-center gap-2 text-sm">
                            <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="{{ $loop->first ? 'text-green-700 font-medium' : 'text-gray-700' }}">
                                {{ $badge }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 mb-5"></div>

                {{-- Renk Seçenekleri --}}
                @if ($this->availableColors->count())
                    <div class="mb-5">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Renk Seçenekleri</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->availableColors as $item)
                                @if ($item['color'])
                                    @php
                                        $colorThumb = $this->product->images
                                            ->where('color_id', $item['color']->id)
                                            ->sortBy('sort_order')
                                            ->first();
                                        $thumbSrc = $colorThumb
                                            ? asset('storage/' . $colorThumb->image)
                                            : ($this->product->main_image
                                                ? asset('storage/' . $this->product->main_image)
                                                : null);
                                    @endphp
                                    <button wire:click="selectColor({{ $item['color']->id }})"
                                        title="{{ $item['color']->name }}"
                                        class="relative overflow-hidden border-2 transition-all flex-shrink-0
           {{ !$item['inStock'] ? 'opacity-40' : '' }}
           {{ $selectedColorId === $item['color']->id ? 'border-gray-900' : 'border-gray-200 hover:border-gray-500' }}"
                                        style="width:58px; aspect-ratio:2/3;"
                                        {{ !$item['inStock'] ? 'disabled' : '' }}>
                                        @php
                                            $colorThumb = $this->product->images
                                                ->where('color_id', $item['color']->id)
                                                ->sortBy('sort_order')
                                                ->first();
                                        @endphp
                                        @if ($colorThumb)
                                            <img src="{{ asset('storage/' . $colorThumb->image) }}"
                                                alt="{{ $item['color']->name }}"
                                                class="w-full h-full object-cover" />
                                        @else
                                            <div class="w-full h-full"
                                                style="background-color: {{ $item['color']->hex_code ?? '#ccc' }}">
                                            </div>
                                        @endif
                                        @if (!$item['inStock'])
                                            <div class="absolute inset-0 bg-white/50 flex items-center justify-center">
                                                <span
                                                    class="w-[140%] h-px bg-gray-500 rotate-45 block absolute"></span>
                                            </div>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Beden Seçimi --}}
                @if ($selectedColorId)
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-body text-xs tracking-widest uppercase text-ink">Beden</h3>
                        </div>
                        <div class="flex flex-wrap gap-2" wire:key="sizes-{{ $selectedColorId }}">
                            @foreach ($this->availableSizes as $item)
                                @if ($item['size'])
                                    <button wire:click="selectSize({{ $item['size']->id }})"
                                        wire:key="size-{{ $item['size']->id }}"
                                        class="min-w-[44px] h-11 px-3 border font-body text-xs transition-all relative
                               {{ $item['stock'] <= 0 ? 'opacity-40 cursor-not-allowed line-through' : '' }}
                               {{ $selectedSizeId === $item['size']->id
                                   ? 'bg-ink text-cream border-ink'
                                   : 'border-sand text-ink hover:border-ink' }}"
                                        {{ $item['stock'] <= 0 ? 'disabled' : '' }}>
                                        {{ $item['size']->name }}
                                        @if ($item['stock'] > 0 && $item['stock'] <= 3)
                                            <span
                                                class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-sand rounded-full"></span>
                                        @endif
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        @if ($this->selectedVariant && $this->selectedVariant->stock <= 5)
                            <p class="font-body text-xs text-sand-dark mt-2">
                                ⚠️ Son {{ $this->selectedVariant->stock }} adet kaldı!
                            </p>
                        @endif
                    </div>

                    {{-- Adet Seçici --}}
                    @if ($selectedSizeId && $this->selectedVariant)
                        <div class="flex items-center gap-4 mb-5">
                            <h3 class="font-body text-xs tracking-widest uppercase text-ink">Adet</h3>
                            <div class="flex items-center border border-sand">
                                <button wire:click="decrementQuantity"
                                    class="w-10 h-10 flex items-center justify-center text-ink hover:bg-sand/20 transition-colors
                           font-body text-lg {{ $quantity <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                                    −
                                </button>
                                <span
                                    class="w-10 h-10 flex items-center justify-center font-body text-sm font-medium text-ink border-x border-sand">
                                    {{ $quantity }}
                                </span>
                                <button wire:click="incrementQuantity"
                                    class="w-10 h-10 flex items-center justify-center text-ink hover:bg-sand/20 transition-colors
                           font-body text-lg {{ $quantity >= ($this->selectedVariant->stock ?? 1) ? 'opacity-30 cursor-not-allowed' : '' }}">
                                    +
                                </button>
                            </div>
                            <span class="font-body text-xs text-smoke">
                                Stok: {{ $this->selectedVariant->stock }} adet
                            </span>
                        </div>
                    @endif
                @endif

                {{-- Hata --}}
                @error('cart')
                    <p class="text-xs text-red-600 bg-red-50 border border-red-200 px-3 py-2 mb-3 rounded">
                        {{ $message }}
                    </p>
                @enderror

                {{-- Sepete Ekle --}}
                <button wire:click="addToCart" @disabled(
                    ($this->availableColors->count() && !$selectedColorId) ||
                        ($selectedColorId && $this->availableSizes->count() && !$selectedSizeId))
                    class="w-full h-12 text-sm font-bold uppercase tracking-wider transition-all duration-200
           flex items-center justify-center gap-2 rounded
           disabled:opacity-50 disabled:cursor-not-allowed
           {{ $addedToCart
               ? 'bg-green-600 text-white'
               : 'bg-orange-500 hover:bg-orange-600 active:scale-[0.99] text-white' }}">
                    <span wire:loading.remove wire:target="addToCart" class="flex items-center justify-center gap-2">
                        @if ($addedToCart)
                            ✓ Sepete Eklendi
                        @elseif(!$selectedColorId && $this->availableColors->count())
                            Renk Seçin
                        @elseif(!$selectedSizeId && $selectedColorId)
                            Beden Seçin
                        @else
                            Sepete Ekle
                        @endif
                    </span>
                    <span wire:loading.flex wire:target="addToCart" class="items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Ekleniyor...
                    </span>
                </button>

            </div>
        </div>

        {{-- ── TABS ── --}}
        <div class="mt-10 border-t border-gray-200" x-data="{ tab: 'desc' }">
            <div class="flex overflow-x-auto">
                @foreach ([['key' => 'desc', 'label' => 'Ürün Açıklaması'], ['key' => 'taksit', 'label' => 'Taksit Seçenekleri'], ['key' => 'teslimat', 'label' => 'Teslimat ve İade']] as $t)
                    <button @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}'
                            ?
                            'border-b-2 border-orange-500 text-orange-500 font-semibold' :
                            'text-gray-600 hover:text-gray-900 border-b-2 border-transparent'"
                        class="pr-5 py-3 text-sm whitespace-nowrap transition-colors flex-shrink-0 -mb-px">
                        {{ $t['label'] }}
                    </button>
                @endforeach
            </div>

            <div class="pt-6 pb-4">
                <div x-show="tab === 'desc'" x-cloak>
                    @if ($product->description)
                        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                            {!! $product->description !!}
                        </div>
                    @elseif($product->short_description)
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $product->short_description }}</p>
                    @else
                        <p class="text-gray-400 text-sm">Ürün açıklaması bulunmamaktadır.</p>
                    @endif
                </div>
                <div x-show="tab === 'taksit'" x-cloak class="text-sm text-gray-600">
                    <p>Taksit bilgileri yakında eklenecek.</p>
                </div>
                <div x-show="tab === 'teslimat'" x-cloak class="text-sm text-gray-600 space-y-2">
                    <p>✓ Siparişleriniz aynı gün kargoya verilir.</p>
                    <p>✓ 3000 TL üzeri alışverişlerde kargo ücretsizdir.</p>
                    <p>✓ Ürün elinize ulaştıktan sonra 7 gün içinde iade edebilirsiniz.</p>
                </div>
            </div>
        </div>

        {{-- ── İLGİLİ ÜRÜNLER ── --}}
        @if ($related->count())
            <div class="mt-8 pt-8 border-t border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-gray-900">Benzer Ürünler</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($related as $item)
                        <a href="{{ route('product', $item->slug) }}" class="group block">
                            <div class="relative overflow-hidden bg-gray-50 mb-2" style="aspect-ratio:2/3;">
                                @if ($item->main_image)
                                    <img src="{{ asset('storage/' . $item->main_image) }}" alt="{{ $item->name }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy" />
                                @else
                                    <div class="w-full h-full bg-gray-100"></div>
                                @endif
                                @if ($item->compare_price && $item->compare_price > $item->price)
                                    <span
                                        class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                        %{{ $item->discount_percentage }} İndirim
                                    </span>
                                @endif
                                @php $itemStock = $item->total_stock; @endphp
                                @if ($itemStock <= 3 && $itemStock > 0)
                                    <span
                                        class="absolute top-2 right-2 bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                        Son {{ $itemStock }}
                                    </span>
                                @endif
                            </div>
                            <h3
                                class="text-xs text-gray-800 line-clamp-2 leading-snug mb-1 group-hover:text-orange-500 transition-colors">
                                {{ $item->name }}
                            </h3>
                            <div class="flex items-center gap-2">
                                @if ($item->compare_price && $item->compare_price > $item->price)
                                    <span class="text-[11px] text-gray-400 line-through">
                                        ₺{{ number_format($item->compare_price, 2, ',', '.') }}
                                    </span>
                                @endif
                                <span class="text-sm font-bold text-gray-900">
                                    ₺{{ number_format($item->price, 2, ',', '.') }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ── STRUCTURED DATA ── --}}
    @php
        $schemaName = $product->name;
        $schemaDesc = mb_substr(strip_tags($product->short_description ?? ($product->description ?? '')), 0, 500);
        $schemaImage = $product->main_image ? asset('storage/' . $product->main_image) : asset('images/og-default.jpg');
        $schemaSku = $product->sku ?? 'EA-' . $product->id;
        $schemaCategory = optional($product->category)->name ?? '';
        $schemaCatUrl = route('category', optional($product->category)->slug ?? '#');
        $schemaPrice = number_format($product->price, 2, '.', '');
        $schemaAvail = $product->total_stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $schemaUrl = route('product', $product->slug);
        $schemaImages = collect([$product->main_image])
            ->concat($product->images->pluck('image'))
            ->filter()
            ->map(fn($img) => asset('storage/' . $img))
            ->values()
            ->toArray();
        $schemaHighPrice = $product->variants->count()
            ? number_format($product->price + $product->variants->max('price_modifier'), 2, '.', '')
            : $schemaPrice;
        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $schemaName,
            'description' => $schemaDesc,
            'image' => count($schemaImages) > 1 ? $schemaImages : $schemaImages[0] ?? $schemaImage,
            'sku' => $schemaSku,
            'url' => $schemaUrl,
            'brand' => ['@type' => 'Brand', 'name' => 'Eren Abiye'],
            'category' => $schemaCategory,
            'offers' => [
                '@type' => 'Offer',
                'price' => $schemaPrice,
                'highPrice' => $schemaHighPrice,
                'priceCurrency' => 'TRY',
                'availability' => $schemaAvail,
                'url' => $schemaUrl,
                'seller' => ['@type' => 'Organization', 'name' => 'Eren Abiye', 'url' => config('app.url')],
            ],
        ];
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Ana Sayfa', 'item' => config('app.url')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $schemaCategory, 'item' => $schemaCatUrl],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $schemaName, 'item' => $schemaUrl],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($productSchema,    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>
</div>
