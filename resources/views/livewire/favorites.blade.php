<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Wishlist;

new #[Layout('layouts.app')] #[Title('Favorilerim — Eren Abiye')] class extends Component {

    public function removeFromWishlist(int $productId): void
    {
        Wishlist::forCurrentUser()->where('product_id', $productId)->delete();
    }

    public function with(): array
    {
        $wishlistItems = Wishlist::forCurrentUser()
            ->with(['product.images', 'product.variants.color'])
            ->get();

        return [
            'wishlistItems' => $wishlistItems,
            'wishlistCount' => $wishlistItems->count(),
            'isGuest'       => !auth()->check(),
        ];
    }
};
?>

<div>
    {{-- Breadcrumb --}}
    <div class="bg-blush-light/50 border-b border-sand/20">
        <div class="max-w-screen-xl mx-auto px-6 py-3">
            <nav class="flex items-center gap-2 font-body text-xs text-smoke">
                <a href="{{ route('home') }}" class="hover:text-ink transition-colors">Ana Sayfa</a>
                <span>/</span>
                <span class="text-ink">Favorilerim</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <h1 class="font-display text-3xl font-light text-ink">Favorilerim</h1>
            @if($wishlistCount > 0)
                <span class="font-body text-xs text-smoke">{{ $wishlistCount }} ürün</span>
            @endif
        </div>

        {{-- Misafir uyarısı --}}
        @if($isGuest)
            <div class="flex items-start gap-4 bg-blush-light/60 border border-sand/40 px-5 py-4 mb-8">
                <svg class="w-5 h-5 text-smoke flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <div>
                    <p class="font-body text-sm text-ink font-medium mb-1">
                        Favorileriniz yalnızca bu cihazda saklanıyor
                    </p>
                    <p class="font-body text-xs text-smoke leading-relaxed">
                        Giriş yaparak favorilerinizi tüm cihazlarınızdan erişebilir, kaybetmeden saklayabilirsiniz.
                    </p>
                    <div class="flex gap-3 mt-3">
                        <a href="{{ route('login') }}"
                           class="font-body text-xs tracking-widest uppercase bg-ink text-cream
                                  px-5 py-2 hover:bg-smoke transition-colors">
                            Giriş Yap
                        </a>
                        <a href="{{ route('register') }}"
                           class="font-body text-xs tracking-widest uppercase border border-ink text-ink
                                  px-5 py-2 hover:bg-ink hover:text-cream transition-colors">
                            Kayıt Ol
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Boş durum --}}
        @if($wishlistItems->isEmpty())
            <div class="text-center py-24 bg-white border border-sand/30">
                <svg class="w-12 h-12 text-sand mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
                <h2 class="font-display text-2xl font-light text-ink mb-3">Favori ürün yok</h2>
                <p class="font-body text-sm text-smoke mb-6">
                    Beğendiğiniz ürünleri kalp ikonuna tıklayarak favorilerinize ekleyin.
                </p>
                <a href="{{ route('all-products') }}"
                    class="inline-block bg-ink text-cream font-body text-xs tracking-widest2
                           uppercase px-8 py-3 hover:bg-smoke transition-colors">
                    Alışverişe Başla
                </a>
            </div>

        {{-- Ürün grid --}}
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-5">
                @foreach($wishlistItems as $item)
                    @if($item->product)
                        <div class="group" wire:key="wish-{{ $item->id }}">
                            <div class="relative aspect-[3/4] overflow-hidden bg-blush-light mb-3">
                                <a href="{{ route('product', $item->product->slug) }}"
                                   class="block absolute inset-0">
                                    @if($item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover transition-transform
                                                    duration-700 group-hover:scale-105"/>
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                                    @endif
                                </a>

                                {{-- Favoriden çıkar --}}
                                <button wire:click="removeFromWishlist({{ $item->product->id }})"
                                    title="Favorilerden çıkar"
                                    class="absolute top-2 right-2 z-10 w-8 h-8 flex items-center
                                           justify-center rounded-full bg-white text-red-500
                                           shadow-md hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>

                                @if($item->product->compare_price)
                                    <span class="absolute bottom-2 left-2 bg-ink text-cream pointer-events-none
                                                 font-body text-[10px] tracking-widest uppercase px-2 py-0.5">
                                        %{{ $item->product->discount_percentage }} İndirim
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-1">
                                <h3 class="font-body text-xs text-ink leading-snug line-clamp-2">
                                    <a href="{{ route('product', $item->product->slug) }}"
                                       class="hover:text-smoke transition-colors">
                                        {{ $item->product->name }}
                                    </a>
                                </h3>
                                <div class="flex items-center gap-2">
                                    @if($item->product->compare_price)
                                        <span class="font-body text-xs text-smoke line-through">
                                            ₺{{ number_format($item->product->compare_price, 2, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="font-body text-sm font-medium text-ink">
                                        ₺{{ number_format($item->product->price, 2, ',', '.') }}
                                    </span>
                                </div>

                                @php
                                    $wColors = $item->product->variants->pluck('color')->filter()->unique('id')->take(5);
                                @endphp
                                @if($wColors->count())
                                    <div class="flex items-center gap-1 pt-0.5">
                                        @foreach($wColors as $wc)
                                            @if($wc->hex_code)
                                                <span class="w-3.5 h-3.5 rounded-full flex-shrink-0"
                                                      style="background-color: {{ $wc->hex_code }}; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.12)"
                                                      title="{{ $wc->name }}"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <a href="{{ route('product', $item->product->slug) }}"
                                   class="block mt-2 text-center border border-ink text-ink
                                          font-body text-xs tracking-widest uppercase py-2
                                          hover:bg-ink hover:text-cream transition-all">
                                    Ürüne Git
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</div>