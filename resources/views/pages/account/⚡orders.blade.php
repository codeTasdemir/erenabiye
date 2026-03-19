<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\Address;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] #[Title('Hesabım — Eren Abiye')] class extends Component {
    use WithPagination;

    public string $activeTab    = 'orders';
    public ?int $activeOrderId  = null;

    public bool $showAddressForm = false;
    public ?int $editAddressId   = null;

    public string $addrTitle     = 'Ev';
    public string $addrFirstName = '';
    public string $addrLastName  = '';
    public string $addrPhone     = '';
    public string $addrCity      = '';
    public string $addrDistrict  = '';
    public string $addrAddress   = '';
    public string $addrZipCode   = '';
    public bool   $addrIsDefault = false;

    public function mount(): void
    {
        if (!auth()->check()) {
            $this->activeTab = 'wishlist';
        }
        if (request('tab')) {
            $this->activeTab = request('tab');
    }
    }

    // ── SİPARİŞ ──
    public function toggleOrder(int $orderId): void
    {
        $this->activeOrderId = $this->activeOrderId === $orderId ? null : $orderId;
    }

    // ── ADRES ──
    public function openAddressForm(?int $addressId = null): void
    {
        $this->editAddressId   = $addressId;
        $this->showAddressForm = true;

        if ($addressId) {
            $address = Address::where('user_id', auth()->id())->findOrFail($addressId);
            $this->addrTitle     = $address->title;
            $this->addrFirstName = $address->first_name;
            $this->addrLastName  = $address->last_name;
            $this->addrPhone     = $address->phone;
            $this->addrCity      = $address->city;
            $this->addrDistrict  = $address->district;
            $this->addrAddress   = $address->address;
            $this->addrZipCode   = $address->zip_code ?? '';
            $this->addrIsDefault = $address->is_default;
        } else {
            $this->resetAddressForm();
        }
    }

    public function closeAddressForm(): void
    {
        $this->showAddressForm = false;
        $this->editAddressId   = null;
        $this->resetAddressForm();
    }

    public function resetAddressForm(): void
    {
        $this->addrTitle     = 'Ev';
        $this->addrFirstName = '';
        $this->addrLastName  = '';
        $this->addrPhone     = '';
        $this->addrCity      = '';
        $this->addrDistrict  = '';
        $this->addrAddress   = '';
        $this->addrZipCode   = '';
        $this->addrIsDefault = false;
    }

    public function saveAddress(): void
    {
        $this->validate([
            'addrTitle'     => 'required|string|max:50',
            'addrFirstName' => 'required|string|max:100',
            'addrLastName'  => 'required|string|max:100',
            'addrPhone'     => 'required|string|max:20',
            'addrCity'      => 'required|string|max:100',
            'addrDistrict'  => 'required|string|max:100',
            'addrAddress'   => 'required|string',
        ], [
            'addrTitle.required'     => 'Adres başlığı zorunludur.',
            'addrFirstName.required' => 'Ad zorunludur.',
            'addrLastName.required'  => 'Soyad zorunludur.',
            'addrPhone.required'     => 'Telefon zorunludur.',
            'addrCity.required'      => 'Şehir zorunludur.',
            'addrDistrict.required'  => 'İlçe zorunludur.',
            'addrAddress.required'   => 'Açık adres zorunludur.',
        ]);

        if ($this->addrIsDefault) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $data = [
            'user_id'    => auth()->id(),
            'title'      => $this->addrTitle,
            'first_name' => $this->addrFirstName,
            'last_name'  => $this->addrLastName,
            'phone'      => $this->addrPhone,
            'city'       => $this->addrCity,
            'district'   => $this->addrDistrict,
            'address'    => $this->addrAddress,
            'zip_code'   => $this->addrZipCode,
            'is_default' => $this->addrIsDefault,
        ];

        if ($this->editAddressId) {
            Address::where('user_id', auth()->id())
                ->where('id', $this->editAddressId)
                ->update($data);
        } else {
            $count = Address::where('user_id', auth()->id())->count();
            if ($count === 0) $data['is_default'] = true;
            Address::create($data);
        }

        $this->closeAddressForm();
        $this->dispatch('address-saved');
    }

    public function deleteAddress(int $addressId): void
    {
        Address::where('user_id', auth()->id())->where('id', $addressId)->delete();
    }

    public function setDefaultAddress(int $addressId): void
    {
        Address::where('user_id', auth()->id())->update(['is_default' => false]);
        Address::where('user_id', auth()->id())->where('id', $addressId)->update(['is_default' => true]);
    }

    // ── WİSHLİST ──
    public function removeFromWishlist(int $productId): void
    {
        Wishlist::forCurrentUser()->where('product_id', $productId)->delete();
    }

    // ── ÇIKIŞ ──
    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('home'));
    }

    public function with(): array
    {
        $wishlistQuery = Wishlist::forCurrentUser()->with(['product.images', 'product.variants.color']);
        $wishlistItems = $wishlistQuery->get();
        $wishlistCount = $wishlistItems->count();

        return [
            'orders' => auth()->check()
                ? Order::where('user_id', auth()->id())->with('items')->latest()->paginate(10)
                : collect(),
            'addresses' => auth()->check()
                ? Address::where('user_id', auth()->id())
                    ->orderBy('is_default', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect(),
            'user'           => auth()->user(),
            'wishlistItems'  => $wishlistItems,
            'wishlistCount'  => $wishlistCount,
            'isGuest'        => !auth()->check(),
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
                <span class="text-ink">{{ $isGuest ? 'Favorilerim' : 'Hesabım' }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-screen-xl mx-auto px-6 py-10">
        <div class="grid lg:grid-cols-4 gap-10">

            {{-- ── SOL: MENÜ ── --}}
            <aside class="lg:col-span-1">
                <div class="bg-white border border-sand/30 p-5 sticky top-28">

                    @if(!$isGuest)
                        {{-- Kullanıcı Bilgisi --}}
                        <div class="text-center pb-5 border-b border-sand/30 mb-5">
                            <div class="w-14 h-14 bg-blush rounded-full flex items-center justify-center mx-auto mb-3">
                                <span class="font-display text-2xl text-ink">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            </div>
                            <p class="font-body text-sm text-ink font-medium">{{ $user->name }}</p>
                            <p class="font-body text-xs text-smoke mt-0.5">{{ $user->email }}</p>
                        </div>
                    @else
                        {{-- Misafir Bilgisi --}}
                        <div class="text-center pb-5 border-b border-sand/30 mb-5">
                            <div class="w-14 h-14 bg-sand/30 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-smoke" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </div>
                            <p class="font-body text-sm text-ink font-medium">Misafir</p>
                            <a href="{{ route('login') }}"
                               class="font-body text-xs text-smoke underline hover:text-ink transition-colors mt-0.5 block">
                                Giriş Yap
                            </a>
                        </div>
                    @endif

                    <nav class="space-y-1">
                        @if(!$isGuest)
                            {{-- Siparişlerim --}}
                            <button wire:click="$set('activeTab', 'orders')"
                                class="w-full flex items-center gap-3 px-3 py-2.5 font-body text-xs
                                       tracking-widest uppercase transition-colors
                                       {{ $activeTab === 'orders'
                                           ? 'bg-blush-light text-ink'
                                           : 'text-smoke hover:text-ink hover:bg-blush-light/50' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/>
                                </svg>
                                Siparişlerim
                            </button>
                        @endif

                        {{-- Favorilerim --}}
                        <button wire:click="$set('activeTab', 'wishlist')"
                            class="w-full flex items-center gap-3 px-3 py-2.5 font-body text-xs
                                   tracking-widest uppercase transition-colors
                                   {{ $activeTab === 'wishlist'
                                       ? 'bg-blush-light text-ink'
                                       : 'text-smoke hover:text-ink hover:bg-blush-light/50' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                            </svg>
                            Favorilerim
                            @if($wishlistCount > 0)
                                <span class="ml-auto bg-ink text-cream rounded-full w-4 h-4
                                             flex items-center justify-center text-[10px]">
                                    {{ $wishlistCount }}
                                </span>
                            @endif
                        </button>

                        @if(!$isGuest)
                            {{-- Adreslerim --}}
                            <button wire:click="$set('activeTab', 'addresses')"
                                class="w-full flex items-center gap-3 px-3 py-2.5 font-body text-xs
                                       tracking-widest uppercase transition-colors
                                       {{ $activeTab === 'addresses'
                                           ? 'bg-blush-light text-ink'
                                           : 'text-smoke hover:text-ink hover:bg-blush-light/50' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z"/>
                                </svg>
                                Adreslerim
                            </button>

                            {{-- Sepetim --}}
                            <a href="{{ route('cart') }}"
                                class="flex items-center gap-3 px-3 py-2.5 font-body text-xs
                                       tracking-widest uppercase text-smoke hover:text-ink
                                       hover:bg-blush-light/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                                </svg>
                                Sepetim
                            </a>

                            {{-- Çıkış --}}
                            <button wire:click="logout"
                                class="w-full flex items-center gap-3 px-3 py-2.5 font-body text-xs
                                       tracking-widest uppercase text-smoke hover:text-ink
                                       hover:bg-blush-light/50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                                </svg>
                                Çıkış Yap
                            </button>
                        @endif
                    </nav>
                </div>
            </aside>

            {{-- ── SAĞ: İÇERİK ── --}}
            <div class="lg:col-span-3">

                {{-- SİPARİŞLERİM SEKMESİ  --}}
                @if($activeTab === 'orders' && !$isGuest)
                    <h1 class="font-display text-3xl font-light text-ink mb-8">Siparişlerim</h1>

                    @if($orders->isEmpty())
                        <div class="text-center py-20 bg-white border border-sand/30">
                            <div class="text-5xl mb-4">📦</div>
                            <h2 class="font-display text-2xl font-light text-ink mb-3">Henüz siparişiniz yok</h2>
                            <p class="font-body text-sm text-smoke mb-6">
                                İlk siparişinizi vermek için alışverişe başlayın.
                            </p>
                            <a href="{{ route('home') }}"
                                class="inline-block bg-ink text-cream font-body text-xs tracking-widest2
                                       uppercase px-8 py-3 hover:bg-smoke transition-colors">
                                Alışverişe Başla
                            </a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($orders as $order)
                                <div class="bg-white border border-sand/30">
                                    <button wire:click="toggleOrder({{ $order->id }})"
                                        class="w-full flex items-center justify-between p-5
                                               hover:bg-blush-light/20 transition-colors">
                                        <div class="flex items-center gap-6 text-left">
                                            <div>
                                                <p class="font-body text-xs text-smoke">Sipariş No</p>
                                                <p class="font-body text-sm font-medium text-ink mt-0.5">
                                                    {{ $order->order_number }}
                                                </p>
                                            </div>
                                            <div class="hidden sm:block">
                                                <p class="font-body text-xs text-smoke">Tarih</p>
                                                <p class="font-body text-sm text-ink mt-0.5">
                                                    {{ $order->created_at->format('d.m.Y') }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="font-body text-xs text-smoke">Toplam</p>
                                                <p class="font-body text-sm font-medium text-ink mt-0.5">
                                                    ₺{{ number_format($order->total, 2, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4">
                                            @php
                                                $statusColors = [
                                                    'pending'    => 'bg-yellow-100 text-yellow-700',
                                                    'confirmed'  => 'bg-blue-100 text-blue-700',
                                                    'processing' => 'bg-blue-100 text-blue-700',
                                                    'shipped'    => 'bg-purple-100 text-purple-700',
                                                    'delivered'  => 'bg-green-100 text-green-700',
                                                    'cancelled'  => 'bg-red-100 text-red-700',
                                                    'refunded'   => 'bg-gray-100 text-gray-700',
                                                ];
                                                $statusLabels = [
                                                    'pending'    => 'Beklemede',
                                                    'confirmed'  => 'Onaylandı',
                                                    'processing' => 'Hazırlanıyor',
                                                    'shipped'    => 'Kargoda',
                                                    'delivered'  => 'Teslim Edildi',
                                                    'cancelled'  => 'İptal',
                                                    'refunded'   => 'İade',
                                                ];
                                            @endphp
                                            <span class="font-body text-xs px-3 py-1 rounded-full
                                                         {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $statusLabels[$order->status] ?? $order->status }}
                                            </span>
                                            <svg class="w-4 h-4 text-smoke transition-transform
                                                        {{ $activeOrderId === $order->id ? 'rotate-180' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      stroke-width="1.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                            </svg>
                                        </div>
                                    </button>

                                    @if($activeOrderId === $order->id)
                                        <div class="border-t border-sand/30" x-data="{ tab: 'items' }">
                                            <div class="flex border-b border-sand/30">
                                                <button @click="tab = 'items'"
                                                    :class="tab === 'items' ? 'border-b-2 border-ink text-ink' : 'text-smoke hover:text-ink'"
                                                    class="font-body text-xs tracking-widest uppercase px-6 py-3 transition-colors">
                                                    Ürünler
                                                </button>
                                                <button @click="tab = 'cargo'"
                                                    :class="tab === 'cargo' ? 'border-b-2 border-ink text-ink' : 'text-smoke hover:text-ink'"
                                                    class="font-body text-xs tracking-widest uppercase px-6 py-3 transition-colors">
                                                    Kargo Takip
                                                </button>
                                                <button @click="tab = 'address'"
                                                    :class="tab === 'address' ? 'border-b-2 border-ink text-ink' : 'text-smoke hover:text-ink'"
                                                    class="font-body text-xs tracking-widest uppercase px-6 py-3 transition-colors">
                                                    Teslimat
                                                </button>
                                                <button @click="tab = 'summary'"
                                                    :class="tab === 'summary' ? 'border-b-2 border-ink text-ink' : 'text-smoke hover:text-ink'"
                                                    class="font-body text-xs tracking-widest uppercase px-6 py-3 transition-colors">
                                                    Fiyat
                                                </button>
                                            </div>

                                            <div x-show="tab === 'items'" class="p-5 space-y-3">
                                                @foreach($order->items as $item)
                                                    <div class="flex gap-4">
                                                        <div class="w-16 aspect-square bg-blush-light flex-shrink-0 overflow-hidden">
                                                            @if($item->product_image)
                                                                <img src="{{ asset('storage/' . $item->product_image) }}"
                                                                     alt="{{ $item->product_name }}"
                                                                     class="w-full h-full object-cover" />
                                                            @endif
                                                        </div>
                                                        <div class="flex-1">
                                                            <p class="font-body text-sm text-ink">{{ $item->product_name }}</p>
                                                            @if($item->variant_info)
                                                                <p class="font-body text-xs text-smoke mt-0.5">{{ $item->variant_info }}</p>
                                                            @endif
                                                            <div class="flex justify-between mt-1">
                                                                <span class="font-body text-xs text-smoke">{{ $item->quantity }} adet</span>
                                                                <span class="font-body text-xs font-medium">
                                                                    ₺{{ number_format($item->total_price, 2, ',', '.') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div x-show="tab === 'cargo'" class="p-5">
                                                @if($order->cargo_tracking_number)
                                                    <div class="bg-blush-light/50 px-5 py-4 mb-6">
                                                        <div class="grid grid-cols-2 gap-4">
                                                            <div>
                                                                <p class="font-body text-xs text-smoke mb-1">Kargo Firması</p>
                                                                <p class="font-body text-sm font-medium text-ink">{{ $order->cargo_company ?? '—' }}</p>
                                                            </div>
                                                            <div>
                                                                <p class="font-body text-xs text-smoke mb-1">Takip No</p>
                                                                <p class="font-body text-sm font-medium text-ink">{{ $order->cargo_tracking_number }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $trackingUrls = [
                                                            'Yurtiçi Kargo' => 'https://www.yurticikargo.com/tr/online-islemler/gonderi-sorgula?code=' . $order->cargo_tracking_number,
                                                            'Aras Kargo'    => 'https://kargotakip.araskargo.com.tr/?intlTrackId=' . $order->cargo_tracking_number,
                                                            'MNG Kargo'     => 'https://www.mngkargo.com.tr/gonderi-takibi?trackingNo=' . $order->cargo_tracking_number,
                                                            'PTT Kargo'     => 'https://gonderitakip.ptt.gov.tr/Track/Verify?q=' . $order->cargo_tracking_number,
                                                            'Sürat Kargo'   => 'https://www.suratkargo.com.tr/KargoTakip/?TrackingNumber=' . $order->cargo_tracking_number,
                                                            'HepsiJet'      => 'https://www.hepsijet.com/kargo-takip?trackingNumber=' . $order->cargo_tracking_number,
                                                        ];
                                                        $trackingUrl = $trackingUrls[$order->cargo_company] ?? null;
                                                    @endphp
                                                    @if($trackingUrl)
                                                        <div class="text-center">
                                                            <a href="{{ $trackingUrl }}" target="_blank"
                                                               class="inline-flex items-center gap-2 bg-ink text-cream
                                                                      font-body text-xs tracking-widest2 uppercase
                                                                      px-8 py-3 hover:bg-smoke transition-colors">
                                                                🔍 Kargo Takip Sayfasına Git
                                                            </a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="text-center py-10">
                                                        <span class="text-4xl block mb-3">📦</span>
                                                        <p class="font-body text-sm font-medium text-ink mb-1">Kargo Bilgisi Henüz Yok</p>
                                                        <p class="font-body text-xs text-smoke">
                                                            @if($order->status === 'pending') Siparişiniz onay bekliyor.
                                                            @elseif($order->status === 'confirmed') Siparişiniz onaylandı, hazırlanıyor.
                                                            @elseif($order->status === 'processing') Siparişiniz hazırlanıyor.
                                                            @else Kargo bilgisi eklendiğinde burada görünecek.
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <div x-show="tab === 'address'" class="p-5">
                                                <div class="bg-blush-light/30 p-5 space-y-2">
                                                    <p class="font-body text-xs tracking-widest uppercase text-smoke mb-3">Teslimat Adresi</p>
                                                    <p class="font-body text-sm font-medium text-ink">{{ $order->shipping_name }}</p>
                                                    <p class="font-body text-sm text-smoke">{{ $order->shipping_phone }}</p>
                                                    <p class="font-body text-sm text-smoke">{{ $order->shipping_address }}</p>
                                                    <p class="font-body text-sm text-smoke">{{ $order->shipping_district }} / {{ $order->shipping_city }}</p>
                                                </div>
                                            </div>

                                            <div x-show="tab === 'summary'" class="p-5">
                                                <div class="max-w-xs space-y-2">
                                                    <div class="flex justify-between font-body text-xs">
                                                        <span class="text-smoke">Ara Toplam</span>
                                                        <span>₺{{ number_format($order->subtotal, 2, ',', '.') }}</span>
                                                    </div>
                                                    @if($order->discount_amount > 0)
                                                        <div class="flex justify-between font-body text-xs text-green-600">
                                                            <span>İndirim</span>
                                                            <span>-₺{{ number_format($order->discount_amount, 2, ',', '.') }}</span>
                                                        </div>
                                                    @endif
                                                    <div class="flex justify-between font-body text-xs">
                                                        <span class="text-smoke">Kargo</span>
                                                        <span>{{ $order->shipping_amount > 0 ? '₺'.number_format($order->shipping_amount, 2, ',', '.') : 'Ücretsiz' }}</span>
                                                    </div>
                                                    <div class="flex justify-between font-body text-sm font-medium pt-3 border-t border-sand/30">
                                                        <span>Toplam</span>
                                                        <span>₺{{ number_format($order->total, 2, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if($orders->hasPages())
                            <div class="mt-8 flex justify-center">{{ $orders->links() }}</div>
                        @endif
                    @endif
                @endif

                {{-- ══════════════════════════════ --}}
                {{-- FAVORİLERİM SEKMESİ            --}}
                {{-- ══════════════════════════════ --}}
                @if($activeTab === 'wishlist')
                    <div class="flex items-center justify-between mb-8">
                        <h1 class="font-display text-3xl font-light text-ink">Favorilerim</h1>
                        @if($wishlistCount > 0)
                            <span class="font-body text-xs text-smoke">{{ $wishlistCount }} ürün</span>
                        @endif
                    </div>

                    {{-- Misafir uyarısı --}}
                    @if($isGuest)
                        <div class="flex items-start gap-4 bg-blush-light/60 border border-sand/40 px-5 py-4 mb-6">
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

                    @if($wishlistItems->isEmpty())
                        <div class="text-center py-20 bg-white border border-sand/30">
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
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-5">
                            @foreach($wishlistItems as $item)
                                @if($item->product)
                                    <div class="group" wire:key="wish-{{ $item->id }}">
                                        {{-- Ürün Görseli --}}
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

                                        {{-- Ürün Bilgisi --}}
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

                                            {{-- Renk noktaları --}}
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
                @endif

                {{-- ADRESLERİM SEKMESİ             --}}
                @if($activeTab === 'addresses' && !$isGuest)
                    <div class="flex items-center justify-between mb-8">
                        <h1 class="font-display text-3xl font-light text-ink">Adreslerim</h1>
                        @if(!$showAddressForm)
                            <button wire:click="openAddressForm()"
                                class="flex items-center gap-2 bg-ink text-cream font-body
                                       text-xs tracking-widest2 uppercase px-5 py-2.5
                                       hover:bg-smoke transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                Yeni Adres
                            </button>
                        @endif
                    </div>

                    @if($showAddressForm)
                        <div class="bg-white border border-sand/30 p-6 mb-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="font-body text-xs tracking-widest uppercase text-ink">
                                    {{ $editAddressId ? 'Adresi Düzenle' : 'Yeni Adres Ekle' }}
                                </h2>
                                <button wire:click="closeAddressForm" class="text-smoke hover:text-ink transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="font-body text-xs text-smoke block mb-2">Adres Başlığı *</label>
                                    <div class="flex gap-2">
                                        @foreach(['Ev', 'İş', 'Diğer'] as $title)
                                            <button type="button"
                                                    wire:click="$set('addrTitle', '{{ $title }}')"
                                                    class="px-4 py-2 border font-body text-xs transition-all
                                                           {{ $addrTitle === $title
                                                               ? 'bg-ink text-cream border-ink'
                                                               : 'border-sand text-smoke hover:border-ink' }}">
                                                {{ $title }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">Ad *</label>
                                    <input wire:model="addrFirstName" type="text"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                    @error('addrFirstName') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">Soyad *</label>
                                    <input wire:model="addrLastName" type="text"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                    @error('addrLastName') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">Telefon *</label>
                                    <input wire:model="addrPhone" type="tel" placeholder="05XX XXX XX XX"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                    @error('addrPhone') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">Şehir *</label>
                                    <input wire:model="addrCity" type="text"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                    @error('addrCity') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">İlçe *</label>
                                    <input wire:model="addrDistrict" type="text"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                    @error('addrDistrict') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="font-body text-xs text-smoke block mb-1.5">Posta Kodu</label>
                                    <input wire:model="addrZipCode" type="text"
                                           class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                  focus:outline-none focus:border-ink bg-transparent" />
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="font-body text-xs text-smoke block mb-1.5">Açık Adres *</label>
                                    <textarea wire:model="addrAddress" rows="3"
                                              class="w-full border border-sand px-3 py-2.5 font-body text-sm
                                                     focus:outline-none focus:border-ink bg-transparent resize-none">
                                    </textarea>
                                    @error('addrAddress') <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input wire:model="addrIsDefault" type="checkbox" class="w-4 h-4 border-sand rounded" />
                                        <span class="font-body text-xs text-smoke">Varsayılan adres olarak ayarla</span>
                                    </label>
                                </div>

                                <div class="sm:col-span-2 flex gap-3">
                                    <button wire:click="saveAddress"
                                        class="bg-ink text-cream font-body text-xs tracking-widest2
                                               uppercase px-8 py-3 hover:bg-smoke transition-colors">
                                        <span wire:loading.remove wire:target="saveAddress">
                                            {{ $editAddressId ? 'Güncelle' : 'Kaydet' }}
                                        </span>
                                        <span wire:loading wire:target="saveAddress">Kaydediliyor...</span>
                                    </button>
                                    <button wire:click="closeAddressForm"
                                        class="border border-sand text-smoke font-body text-xs tracking-widest2
                                               uppercase px-8 py-3 hover:border-ink hover:text-ink transition-colors">
                                        İptal
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($addresses->isEmpty() && !$showAddressForm)
                        <div class="text-center py-20 bg-white border border-sand/30">
                            <div class="text-5xl mb-4">📍</div>
                            <h2 class="font-display text-2xl font-light text-ink mb-3">Kayıtlı adres yok</h2>
                            <p class="font-body text-sm text-smoke mb-6">Alışverişi hızlandırmak için adresinizi kaydedin.</p>
                            <button wire:click="openAddressForm()"
                                class="inline-block bg-ink text-cream font-body text-xs tracking-widest2
                                       uppercase px-8 py-3 hover:bg-smoke transition-colors">
                                Adres Ekle
                            </button>
                        </div>
                    @else
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($addresses as $addr)
                                <div class="bg-white border-2 p-5 relative
                                            {{ $addr->is_default ? 'border-ink' : 'border-sand/30' }}">
                                    @if($addr->is_default)
                                        <span class="absolute top-3 right-3 font-body text-[10px]
                                                     tracking-widest uppercase bg-ink text-cream px-2 py-0.5">
                                            Varsayılan
                                        </span>
                                    @endif
                                    <div class="flex items-center gap-2 mb-3">
                                        <span class="font-body text-[10px] tracking-widest uppercase
                                                     bg-sand/30 text-smoke px-2 py-0.5">
                                            {{ $addr->title }}
                                        </span>
                                    </div>
                                    <p class="font-body text-sm font-medium text-ink">{{ $addr->first_name }} {{ $addr->last_name }}</p>
                                    <p class="font-body text-xs text-smoke mt-1">{{ $addr->phone }}</p>
                                    <p class="font-body text-xs text-smoke/70 mt-1 leading-relaxed">
                                        {{ $addr->address }}<br>
                                        {{ $addr->district }} / {{ $addr->city }}
                                        @if($addr->zip_code) — {{ $addr->zip_code }} @endif
                                    </p>
                                    <div class="flex items-center gap-3 mt-4 pt-4 border-t border-sand/20">
                                        <button wire:click="openAddressForm({{ $addr->id }})"
                                            class="font-body text-xs text-smoke hover:text-ink underline transition-colors">
                                            Düzenle
                                        </button>
                                        @if(!$addr->is_default)
                                            <button wire:click="setDefaultAddress({{ $addr->id }})"
                                                class="font-body text-xs text-smoke hover:text-ink underline transition-colors">
                                                Varsayılan Yap
                                            </button>
                                            <button wire:click="deleteAddress({{ $addr->id }})"
                                                wire:confirm="Bu adresi silmek istediğinize emin misiniz?"
                                                class="font-body text-xs text-red-400 hover:text-red-600 underline transition-colors ml-auto">
                                                Sil
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
</div>