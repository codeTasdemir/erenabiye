<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;

new #[Layout('layouts.app')] #[Title('Sepetim — Eren Abiye')] class extends Component {
    public string $couponCode = '';
    public ?string $couponMessage = null;
    public bool $couponSuccess = false;

    public function getCartItemsProperty()
    {
        if (auth()->check()) {
            return CartItem::where('user_id', auth()->id())
                ->with(['product', 'variant.color', 'variant.size'])
                ->get()
                ->map(
                    fn($item) => [
                        'id' => $item->id,
                        'product' => $item->product,
                        'variant' => $item->variant,
                        'quantity' => $item->quantity,
                        'price' => $item->variant ? $item->product->price + $item->variant->price_modifier : $item->product->price,
                        'image' => $item->variant?->image ?? $item->product->main_image,
                        'variant_info' => $item->variant?->label,
                        'source' => 'db',
                    ],
                );
        }

        // Misafir — session
        return collect(session()->get('cart', []))
            ->map(function ($item, $key) {
                $product = Product::find($item['product_id']);
                $variant = $item['product_variant_id'] ? ProductVariant::with(['color', 'size'])->find($item['product_variant_id']) : null;

                if (!$product) {
                    return null;
                }

                return [
                    'id' => $key,
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'variant_info' => $item['variant_info'],
                    'source' => 'session',
                ];
            })
            ->filter()
            ->values();
    }

    #[Computed]
    public function subtotal(): float
    {
        return $this->cartItems->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    #[Computed]
    public function shippingCost(): float
    {
        $threshold = (float) \App\Models\Setting::get('free_shipping_threshold', 500);
        $cost = (float) \App\Models\Setting::get('shipping_cost', 49.9);
        return $this->subtotal >= $threshold ? 0 : $cost;
    }

    #[Computed]
    public function discountAmount(): float
    {
        if (!session('applied_coupon')) {
            return 0;
        }
        $coupon = Coupon::where('code', session('applied_coupon'))->first();
        return $coupon ? $coupon->calculateDiscount($this->subtotal) : 0;
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal + $this->shippingCost - $this->discountAmount);
    }

    public function updateQuantity(string|int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeItem($itemId);
            return;
        }

        if (auth()->check()) {
            $cartItem = CartItem::where('id', $itemId)
                ->where('user_id', auth()->id())
                ->with(['variant', 'product'])
                ->first();

            if (!$cartItem) {
                return;
            }

            // Stok kontrolü
            $stock = $cartItem->variant?->stock ?? ($cartItem->product?->stock ?? 0);

            if ($quantity > $stock) {
                $this->dispatch('cart-stock-error', [
                    'message' => "Bu üründen en fazla {$stock} adet ekleyebilirsiniz.",
                ]);
                return;
            }

            $cartItem->update(['quantity' => $quantity]);
        } else {
            $cart = session()->get('cart', []);

            if (!isset($cart[$itemId])) {
                return;
            }

            $variant = $cart[$itemId]['product_variant_id'] ? ProductVariant::find($cart[$itemId]['product_variant_id']) : null;
            $product = Product::find($cart[$itemId]['product_id']);
            $stock = $variant?->stock ?? ($product?->stock ?? 0);

            if ($quantity > $stock) {
                $this->dispatch('cart-stock-error', [
                    'message' => "Bu üründen en fazla {$stock} adet ekleyebilirsiniz.",
                ]);
                return;
            }

            $cart[$itemId]['quantity'] = $quantity;
            session()->put('cart', $cart);
        }

        $this->dispatch('cart-updated');
    }

    public function removeItem(string|int $itemId): void
    {
        if (auth()->check()) {
            CartItem::where('id', $itemId)
                ->where('user_id', auth()->id())
                ->delete();
        } else {
            $cart = session()->get('cart', []);
            unset($cart[$itemId]);
            session()->put('cart', $cart);
        }

        $this->dispatch('cart-updated');
    }

    public function applyCoupon(): void
    {
        $this->couponMessage = null;
        $this->couponSuccess = false;

        $coupon = Coupon::where('code', trim($this->couponCode))->first();

        if (!$coupon) {
            $this->couponMessage = 'Geçersiz kupon kodu.';
            return;
        }

        if (!$coupon->isValid($this->subtotal)) {
            if ($coupon->expires_at?->isPast()) {
                $this->couponMessage = 'Bu kuponun süresi dolmuş.';
            } elseif ($coupon->minimum_order > $this->subtotal) {
                $this->couponMessage = 'Bu kupon için minimum sipariş tutarı ₺' . number_format($coupon->minimum_order, 2, ',', '.') . ' olmalıdır.';
            } else {
                $this->couponMessage = 'Bu kupon kullanılamaz.';
            }
            return;
        }

        session()->put('applied_coupon', $coupon->code);
        $this->couponSuccess = true;
        $this->couponMessage = 'Kupon uygulandı! ₺' . number_format($coupon->calculateDiscount($this->subtotal), 2, ',', '.') . ' indirim kazandınız.';
    }

    public function removeCoupon(): void
    {
        session()->forget('applied_coupon');
        $this->couponCode = '';
        $this->couponMessage = null;
        $this->couponSuccess = false;
    }

    public function proceedToCheckout()
    {
        if ($this->cartItems->isEmpty()) {
            return;
        }

        if (!auth()->check()) {
            return $this->redirect(route('login') . '?redirect=checkout');
        }

        return $this->redirect(route('checkout'));
    }

    public function incrementQuantity(string|int $itemId): void
    {
        $currentQuantity = $this->getCurrentQuantity($itemId);
        $this->updateQuantity($itemId, $currentQuantity + 1);
    }

    public function decrementQuantity(string|int $itemId): void
    {
        $currentQuantity = $this->getCurrentQuantity($itemId);
        $this->updateQuantity($itemId, $currentQuantity - 1);
    }

    private function getCurrentQuantity(string|int $itemId): int
    {
        if (auth()->check()) {
            return CartItem::where('id', $itemId)
                ->where('user_id', auth()->id())
                ->value('quantity') ?? 1;
        }

        $cart = session()->get('cart', []);
        return $cart[$itemId]['quantity'] ?? 1;
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
                <span class="text-ink">Sepetim</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-10">
        <h1 class="font-display text-4xl font-light text-ink mb-10">
            Sepetim
            @if ($this->cartItems->count())
                <span class="font-body text-lg text-smoke font-normal ml-2">
                    ({{ $this->cartItems->count() }} ürün)
                </span>
            @endif
        </h1>

        {{-- Stok hata mesajı --}}
        <div x-data="{ message: '' }"
            x-on:cart-stock-error.window="message = $event.detail.message; setTimeout(() => message = '', 3000)">
            <div x-show="message" x-transition
                class="bg-red-50 border border-red-200 text-red-600 font-body text-xs px-4 py-3 mb-4">
                ⚠️ <span x-text="message"></span>
            </div>
        </div>

        @if ($this->cartItems->isEmpty())
            {{-- Boş Sepet --}}
            <div class="text-center py-24">
                <div class="text-6xl mb-6">🛍️</div>
                <h2 class="font-display text-3xl font-light text-ink mb-3">Sepetiniz boş</h2>
                <p class="font-body text-sm text-smoke mb-8">
                    Sepetinize ürün eklemek için alışverişe başlayın.
                </p>
                <a href="{{ route('home') }}"
                    class="inline-block bg-ink text-cream font-body text-xs tracking-widest2
                          uppercase px-10 py-4 hover:bg-smoke transition-colors">
                    Alışverişe Başla
                </a>
            </div>
        @else
            <div class="grid lg:grid-cols-3 gap-10">

                {{-- ── SOL: SEPET ÜRÜNLERİ ── --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($this->cartItems as $item)
                        <div class="flex gap-4 p-4 bg-white border border-sand/30">
                            {{-- Görsel --}}
                            <a href="{{ route('product', $item['product']->slug) }}"
                                class="flex-shrink-0 w-24 aspect-[3/4] overflow-hidden bg-blush-light">
                                @if ($item['image'])
                                    <img src="{{ asset('storage/' . $item['image']) }}"
                                        alt="{{ $item['product']->name }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blush-light to-sand-light"></div>
                                @endif
                            </a>

                            {{-- Bilgiler --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between gap-2">
                                    <div>
                                        <a href="{{ route('product', $item['product']->slug) }}"
                                            class="font-body text-sm text-ink hover:text-smoke
                                                  transition-colors line-clamp-2">
                                            {{ $item['product']->name }}
                                        </a>
                                        @if ($item['variant_info'])
                                            <p class="font-body text-xs text-smoke mt-1">
                                                {{ $item['variant_info'] }}
                                            </p>
                                        @endif
                                    </div>
                                    <button wire:click="removeItem('{{ $item['id'] }}')"
                                        class="text-smoke hover:text-ink transition-colors flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex items-center border border-sand">
                                        @php
                                            $stock = $item['variant']?->stock ?? ($item['product']->stock ?? 0);
                                        @endphp

                                        <button wire:click="decrementQuantity('{{ $item['id'] }}')"
                                            class="w-8 h-8 flex items-center justify-center hover:bg-blush-light transition-colors font-body">
                                            −
                                        </button>

                                        <span class="w-8 text-center font-body text-xs">
                                            {{ $item['quantity'] }}
                                        </span>

                                        <button wire:click="incrementQuantity('{{ $item['id'] }}')"
                                            class="w-8 h-8 flex items-center justify-center hover:bg-blush-light transition-colors font-body
            {{ $item['quantity'] >= $stock ? 'opacity-30 cursor-not-allowed' : '' }}"
                                            {{ $item['quantity'] >= $stock ? 'disabled' : '' }}>
                                            +
                                        </button>
                                    </div>

                                    {{-- Fiyat --}}
                                    <span class="font-body text-sm font-medium text-ink">
                                        ₺{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- ── SAĞ: ÖZET ── --}}
                <div class="space-y-4">

                    {{-- Kupon --}}
                    <div class="bg-white border border-sand/30 p-5">
                        <h3 class="font-body text-xs tracking-widest uppercase text-ink mb-4">
                            İndirim Kodu
                        </h3>
                        @if (session('applied_coupon'))
                            <div class="flex items-center justify-between bg-green-50 px-3 py-2 mb-3">
                                <span class="font-body text-xs text-green-700 font-medium">
                                    ✓ {{ session('applied_coupon') }}
                                </span>
                                <button wire:click="removeCoupon"
                                    class="font-body text-xs text-smoke hover:text-ink underline">
                                    Kaldır
                                </button>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input wire:model="couponCode" type="text" placeholder="Kupon kodu"
                                    class="flex-1 border border-sand px-3 py-2 font-body text-xs
                                              focus:outline-none focus:border-ink bg-transparent " />
                                <button wire:click="applyCoupon"
                                    class="bg-ink text-cream font-body text-xs px-4 py-2
                                               hover:bg-smoke transition-colors">
                                    Uygula
                                </button>
                            </div>
                        @endif

                        @if ($couponMessage)
                            <p
                                class="font-body text-xs mt-2
                                      {{ $couponSuccess ? 'text-green-600' : 'text-red-500' }}">
                                {{ $couponMessage }}
                            </p>
                        @endif
                    </div>

                    {{-- Sipariş Özeti --}}
                    <div class="bg-white border border-sand/30 p-5 space-y-3">
                        <h3 class="font-body text-xs tracking-widest uppercase text-ink mb-4">
                            Sipariş Özeti
                        </h3>

                        <div class="flex justify-between font-body text-sm">
                            <span class="text-smoke">Ara Toplam</span>
                            <span>₺{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                        </div>

                        @if ($this->discountAmount > 0)
                            <div class="flex justify-between font-body text-sm text-green-600">
                                <span>İndirim</span>
                                <span>-₺{{ number_format($this->discountAmount, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between font-body text-sm">
                            <span class="text-smoke">Kargo</span>
                            <span class="{{ $this->shippingCost === 0.0 ? 'text-green-600' : '' }}">
                                {{ $this->shippingCost === 0.0 ? 'Ücretsiz' : '₺' . number_format($this->shippingCost, 2, ',', '.') }}
                            </span>
                        </div>

                        @if ($this->shippingCost > 0)
                            @php
                                $threshold = (float) \App\Models\Setting::get('free_shipping_threshold', 500);
                                $remaining = $threshold - $this->subtotal;
                            @endphp
                            <p class="font-body text-xs text-smoke bg-blush-light/50 px-3 py-2">
                                Ücretsiz kargo için
                                <span class="text-ink font-medium">
                                    ₺{{ number_format($remaining, 2, ',', '.') }}
                                </span>
                                daha alışveriş yapın.
                            </p>
                        @endif

                        <div class="border-t border-sand/30 pt-3 flex justify-between font-body">
                            <span class="text-sm font-medium text-ink">Toplam</span>
                            <span class="text-lg font-medium text-ink">
                                ₺{{ number_format($this->total, 2, ',', '.') }}
                            </span>
                        </div>

                        <button wire:click="proceedToCheckout"
                            class="w-full bg-ink text-cream font-body text-xs tracking-widest2
                                       uppercase py-4 hover:bg-smoke transition-colors mt-2">
                            Ödemeye Geç
                        </button>

                        <a href="{{ route('home') }}"
                            class="block text-center font-body text-xs text-smoke hover:text-ink
                                  underline transition-colors mt-2">
                            Alışverişe Devam Et
                        </a>
                    </div>

                    {{-- Güvenli Ödeme --}}
                    <div class="bg-white border border-sand/30 p-4 text-center">
                        <p class="font-body text-xs text-smoke">
                            🔒 256-bit SSL ile güvenli ödeme
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
