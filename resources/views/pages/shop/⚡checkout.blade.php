<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Address;
use App\Models\Setting;

new #[Layout('layouts.app')] #[Title('Ödeme — Eren Abiye')] class extends Component {

    // Teslimat Bilgileri
    #[Validate('required|string|max:100')]
    public string $firstName = '';

    #[Validate('required|string|max:100')]
    public string $lastName = '';

    #[Validate('required|string|max:20')]
    public string $phone = '';

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|max:100')]
    public string $city = '';

    #[Validate('required|string|max:100')]
    public string $district = '';

    #[Validate('required|string')]
    public string $address = '';

    #[Validate('nullable|string|max:20')]
    public string $zipCode = '';

    public string $notes    = '';
    public string $currency = 'TRY';

    // Adres yönetimi
    public ?int  $selectedAddressId = null;
    public bool  $saveAddress       = false;
    public string $addressTitle     = 'Ev';
    public bool  $showNewAddressForm = false;

    public function mount(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        if ($this->cartItems->isEmpty()) {
            $this->redirect(route('cart'));
            return;
        }

        $user        = auth()->user();
        $this->email = $user->email ?? '';

        // Varsayılan adresi otomatik yükle
        $defaultAddress = Address::where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($defaultAddress) {
            $this->fillFromAddress($defaultAddress);
            $this->selectedAddressId = $defaultAddress->id;
        } else {
            // Ad soyad parçala
            $nameParts       = explode(' ', $user->name, 2);
            $this->firstName = $nameParts[0] ?? '';
            $this->lastName  = $nameParts[1] ?? '';
        }
    }

    public function fillFromAddress(Address $address): void
    {
        $this->firstName = $address->first_name;
        $this->lastName  = $address->last_name;
        $this->phone     = $address->phone;
        $this->city      = $address->city;
        $this->district  = $address->district;
        $this->address   = $address->address;
        $this->zipCode   = $address->zip_code ?? '';
        $this->showNewAddressForm = false;
    }

    public function selectAddress(int $addressId): void
    {
        $address = Address::where('user_id', auth()->id())->find($addressId);
        if ($address) {
            $this->selectedAddressId  = $addressId;
            $this->showNewAddressForm = false;
            $this->fillFromAddress($address);
        }
    }

    public function useNewAddress(): void
    {
        $this->selectedAddressId  = null;
        $this->showNewAddressForm = true;
        $this->firstName = '';
        $this->lastName  = '';
        $this->phone     = '';
        $this->city      = '';
        $this->district  = '';
        $this->address   = '';
        $this->zipCode   = '';
        $this->saveAddress = true;
    }

    #[Computed]
    public function cartItems()
    {
        return CartItem::where('user_id', auth()->id())
            ->with(['product', 'variant.color', 'variant.size'])
            ->get();
    }

    #[Computed]
    public function savedAddresses()
    {
        return Address::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return $this->cartItems->sum(function ($item) {
            $price = $item->variant
                ? $item->product->price + $item->variant->price_modifier
                : $item->product->price;
            return $price * $item->quantity;
        });
    }

    #[Computed]
    public function shippingCost(): float
    {
        $threshold = (float) Setting::get('free_shipping_threshold', 500);
        $cost      = (float) Setting::get('shipping_cost', 49.90);
        return $this->subtotal >= $threshold ? 0 : $cost;
    }

    #[Computed]
    public function discountAmount(): float
    {
        if (!session('applied_coupon')) return 0;
        $coupon = Coupon::where('code', session('applied_coupon'))->first();
        return $coupon ? $coupon->calculateDiscount($this->subtotal) : 0;
    }

    #[Computed]
    public function total(): float
    {
        return max(0, $this->subtotal + $this->shippingCost - $this->discountAmount);
    }

    public function placeOrder()
    {
        $this->validate();

        if ($this->cartItems->isEmpty()) {
            return $this->redirect(route('cart'));
        }

        // Yeni adresi kaydet
        if ($this->saveAddress && $this->showNewAddressForm) {
            $isFirst = $this->savedAddresses->isEmpty();
            Address::create([
                'user_id'    => auth()->id(),
                'title'      => $this->addressTitle ?: 'Adresim',
                'first_name' => $this->firstName,
                'last_name'  => $this->lastName,
                'phone'      => $this->phone,
                'city'       => $this->city,
                'district'   => $this->district,
                'address'    => $this->address,
                'zip_code'   => $this->zipCode,
                'is_default' => $isFirst,
            ]);
        }

        // Kupon
        $coupon         = null;
        $discountAmount = 0;
        if (session('applied_coupon')) {
            $coupon = Coupon::where('code', session('applied_coupon'))->first();
            if ($coupon && $coupon->isValid($this->subtotal)) {
                $discountAmount = $coupon->calculateDiscount($this->subtotal);
                $coupon->increment('used_count');
            }
        }

        // Siparişi oluştur
        $order = Order::create([
            'user_id'           => auth()->id(),
            'status'            => 'pending',
            'payment_status'    => 'pending',
            'payment_method'    => 'credit_card',
            'currency'          => $this->currency,
            'subtotal'          => $this->subtotal,
            'discount_amount'   => $discountAmount,
            'shipping_amount'   => $this->shippingCost,
            'total'             => $this->total,
            'coupon_id'         => $coupon?->id,
            'notes'             => $this->notes,
            'shipping_name'     => $this->firstName . ' ' . $this->lastName,
            'shipping_phone'    => $this->phone,
            'shipping_email'    => $this->email,
            'shipping_city'     => $this->city,
            'shipping_district' => $this->district,
            'shipping_address'  => $this->address,
        ]);

        // Sipariş kalemlerini oluştur
        foreach ($this->cartItems as $cartItem) {
            $price = $cartItem->variant
                ? $cartItem->product->price + $cartItem->variant->price_modifier
                : $cartItem->product->price;

            OrderItem::create([
                'order_id'           => $order->id,
                'product_id'         => $cartItem->product_id,
                'product_variant_id' => $cartItem->product_variant_id,
                'product_name'       => $cartItem->product->name,
                'variant_info'       => $cartItem->variant?->label,
                'product_image'      => $cartItem->product->main_image,
                'quantity'           => $cartItem->quantity,
                'unit_price'         => $price,
                'total_price'        => $price * $cartItem->quantity,
            ]);
        }

        // Sepeti temizle
        CartItem::where('user_id', auth()->id())->delete();
        session()->forget('applied_coupon');

        // PayTR ödeme sayfasına yönlendir
        return $this->redirect(route('payment.show', $order));
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
                <a href="{{ route('cart') }}" class="hover:text-ink transition-colors">Sepetim</a>
                <span>/</span>
                <span class="text-ink">Ödeme Bilgileri</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-10">
        <h1 class="font-display text-4xl font-light text-ink mb-10">Ödeme Bilgileri</h1>

        <div class="grid lg:grid-cols-3 gap-10">

            {{-- ── SOL: FORM ── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Kayıtlı Adresler --}}
                @if($this->savedAddresses->count())
                    <div class="bg-white border border-sand/30 p-5">
                        <h2 class="font-body text-xs tracking-widest uppercase text-ink mb-4">
                            Kayıtlı Adreslerim
                        </h2>
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach($this->savedAddresses as $addr)
                                <button wire:click="selectAddress({{ $addr->id }})"
                                        class="text-left p-4 border-2 transition-all relative
                                               {{ $selectedAddressId === $addr->id
                                                   ? 'border-ink bg-blush-light/30'
                                                   : 'border-sand/30 hover:border-smoke' }}">

                                    {{-- Seçili işareti --}}
                                    @if($selectedAddressId === $addr->id)
                                        <span class="absolute top-2 right-2 w-5 h-5 bg-ink rounded-full
                                                     flex items-center justify-center">
                                            <svg class="w-3 h-3 text-cream" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    @endif

                                    {{-- Adres başlığı --}}
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-body text-[10px] tracking-widest uppercase
                                                     bg-sand/30 text-smoke px-2 py-0.5">
                                            {{ $addr->title }}
                                        </span>
                                        @if($addr->is_default)
                                            <span class="font-body text-[10px] tracking-widest uppercase
                                                         bg-ink text-cream px-2 py-0.5">
                                                Varsayılan
                                            </span>
                                        @endif
                                    </div>

                                    <p class="font-body text-sm font-medium text-ink">
                                        {{ $addr->first_name }} {{ $addr->last_name }}
                                    </p>
                                    <p class="font-body text-xs text-smoke mt-0.5">
                                        {{ $addr->phone }}
                                    </p>
                                    <p class="font-body text-xs text-smoke/70 mt-1 line-clamp-2 leading-relaxed">
                                        {{ $addr->address }}, {{ $addr->district }}/{{ $addr->city }}
                                    </p>
                                </button>
                            @endforeach

                            {{-- Yeni Adres Butonu --}}
                            <button wire:click="useNewAddress"
                                    class="text-left p-4 border-2 border-dashed transition-all
                                           flex flex-col items-center justify-center gap-2
                                           {{ $showNewAddressForm
                                               ? 'border-ink bg-blush-light/30'
                                               : 'border-sand/40 hover:border-smoke' }}">
                                <svg class="w-6 h-6 text-smoke" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                <span class="font-body text-xs text-smoke">Yeni Adres Ekle</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Teslimat Formu --}}
                <div class="bg-white border border-sand/30 p-5">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="font-body text-xs tracking-widest uppercase text-ink">
                            {{ $selectedAddressId ? 'Teslimat Bilgileri' : 'Yeni Adres' }}
                        </h2>
                        @if($selectedAddressId)
                            <span class="font-body text-xs text-green-600 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Kayıtlı adresinizden dolduruldu
                            </span>
                        @endif
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">

                        {{-- Adres Başlığı  --}}
                        @if($showNewAddressForm || !$this->savedAddresses->count())
                            <div class="sm:col-span-2">
                                <label class="font-body text-xs text-smoke block mb-1.5">
                                    Adres Başlığı
                                </label>
                                <div class="flex gap-2">
                                    @foreach(['Ev', 'İş', 'Diğer'] as $title)
                                        <button type="button"
                                                wire:click="$set('addressTitle', '{{ $title }}')"
                                                class="px-4 py-2 border font-body text-xs transition-all
                                                       {{ $addressTitle === $title
                                                           ? 'bg-ink text-cream border-ink'
                                                           : 'border-sand text-smoke hover:border-ink' }}">
                                            {{ $title }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Ad *</label>
                            <input wire:model="firstName" type="text"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent
                                          {{ $selectedAddressId ? 'bg-blush-light/20' : '' }}" />
                            @error('firstName')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Soyad *</label>
                            <input wire:model="lastName" type="text"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                            @error('lastName')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Telefon *</label>
                            <input wire:model="phone" type="tel"
                                   placeholder="05XX XXX XX XX"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                            @error('phone')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">E-posta *</label>
                            <input wire:model="email" type="email"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                            @error('email')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Şehir *</label>
                            <input wire:model="city" type="text"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                            @error('city')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">İlçe *</label>
                            <input wire:model="district" type="text"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                            @error('district')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-body text-xs text-smoke block mb-1.5">Açık Adres *</label>
                            <textarea wire:model="address" rows="3"
                                      class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                             focus:outline-none focus:border-ink bg-transparent resize-none">
                            </textarea>
                            @error('address')
                                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Posta Kodu</label>
                            <input wire:model="zipCode" type="text"
                                   class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                          focus:outline-none focus:border-ink bg-transparent" />
                        </div>

                        <div>
                            <label class="font-body text-xs text-smoke block mb-1.5">Para Birimi</label>
                            <select wire:model="currency"
                                    class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                           focus:outline-none focus:border-ink bg-transparent cursor-pointer">
                                <option value="TRY">Türk Lirası (₺)</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="font-body text-xs text-smoke block mb-1.5">Sipariş Notu</label>
                            <textarea wire:model="notes" rows="2"
                                      placeholder="Siparişinizle ilgili not ekleyebilirsiniz..."
                                      class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                             focus:outline-none focus:border-ink bg-transparent resize-none">
                            </textarea>
                        </div>

                        @if($showNewAddressForm || !$this->savedAddresses->count())
                            <div class="sm:col-span-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input wire:model="saveAddress" type="checkbox"
                                           class="w-4 h-4 border-sand rounded" />
                                    <span class="font-body text-xs text-smoke">
                                        Bu adresi kaydet
                                    </span>
                                </label>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

            {{-- ── SAĞ: SİPARİŞ ÖZETİ ── --}}
            <div>
                <div class="bg-white border border-sand/30 p-5 sticky top-28">
                    <h2 class="font-body text-xs tracking-widest uppercase text-ink mb-5">
                        Sipariş Özeti
                    </h2>

                    {{-- Ürünler --}}
                    <div class="space-y-3 mb-5 max-h-64 overflow-y-auto">
                        @foreach($this->cartItems as $item)
                            @php
                                $price = $item->variant
                                    ? $item->product->price + $item->variant->price_modifier
                                    : $item->product->price;
                            @endphp
                            <div class="flex gap-3">
                                <div class="w-14 aspect-square overflow-hidden bg-blush-light flex-shrink-0">
                                    @if($item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                             alt="{{ $item->product->name }}"
                                             class="w-full h-full object-cover" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-body text-xs text-ink line-clamp-2">
                                        {{ $item->product->name }}
                                    </p>
                                    @if($item->variant)
                                        <p class="font-body text-xs text-smoke mt-0.5">
                                            {{ $item->variant->label }}
                                        </p>
                                    @endif
                                    <div class="flex justify-between mt-1">
                                        <span class="font-body text-xs text-smoke">x{{ $item->quantity }}</span>
                                        <span class="font-body text-xs font-medium">
                                            ₺{{ number_format($price * $item->quantity, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-sand/30 pt-4 space-y-2">
                        <div class="flex justify-between font-body text-sm">
                            <span class="text-smoke">Ara Toplam</span>
                            <span>₺{{ number_format($this->subtotal, 2, ',', '.') }}</span>
                        </div>

                        @if($this->discountAmount > 0)
                            <div class="flex justify-between font-body text-sm text-green-600">
                                <span>İndirim</span>
                                <span>-₺{{ number_format($this->discountAmount, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between font-body text-sm">
                            <span class="text-smoke">Kargo</span>
                            <span class="{{ $this->shippingCost === 0.0 ? 'text-green-600' : '' }}">
                                {{ $this->shippingCost === 0.0
                                    ? 'Ücretsiz'
                                    : '₺' . number_format($this->shippingCost, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="border-t border-sand/30 pt-3 flex justify-between items-end">
                            <span class="font-body text-sm font-medium">Toplam</span>
                            <span class="font-display text-2xl font-light">
                                ₺{{ number_format($this->total, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    {{-- Sipariş Ver --}}
                    <button wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            class="w-full bg-ink text-cream font-body text-xs tracking-widest2
                                   uppercase py-4 hover:bg-smoke transition-colors mt-5
                                   disabled:opacity-70 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="placeOrder">
                            Siparişi Onayla & Ödemeye Geç
                        </span>
                        <span wire:loading wire:target="placeOrder">
                            Sipariş oluşturuluyor...
                        </span>
                    </button>

                    <p class="font-body text-[10px] text-smoke text-center mt-3">
                        🔒 Güvenli ödeme — 256-bit SSL
                    </p>

                    <p class="font-body text-[10px] text-smoke/60 text-center mt-2 leading-relaxed">
                        Siparişi onaylayarak
                        <a href="{{ route('page', 'sartlar-kosullar') }}" class="underline hover:text-smoke">
                            Şartlar & Koşullar
                        </a>
                        ve
                        <a href="{{ route('page', 'mesafeli-satis') }}" class="underline hover:text-smoke">
                            Mesafeli Satış Sözleşmesi
                        </a>'ni kabul etmiş sayılırsınız.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>