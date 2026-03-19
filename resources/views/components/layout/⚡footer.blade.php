<?php
use Livewire\Component;
use App\Models\Setting;

new class extends Component {
    public string $newsletterEmail = '';
    public bool $newsletterSent = false;

    public function subscribeNewsletter(): void
    {
        $this->validate(['newsletterEmail' => 'required|email']);
        $this->newsletterEmail = '';
        $this->newsletterSent = true;
    }

    public function with(): array
    {
        return [
            'siteName' => \App\Models\Setting::get('site_name', 'Eren Abiye'),
            'phone' => \App\Models\Setting::get('contact_phone', ''),
            'email' => \App\Models\Setting::get('contact_email', ''),
            'address' => \App\Models\Setting::get('contact_address', ''),
            'instagram' => \App\Models\Setting::get('social_instagram', ''),
            'facebook' => \App\Models\Setting::get('social_facebook', ''),
            'youtube' => \App\Models\Setting::get('social_youtube', ''),
            'whatsapp' => \App\Models\Setting::get('social_whatsapp', ''),
        ];
    }
};
?>

<div>
    {{-- ── SOSYAL MEDYA & BÜLTEN BANDI ── --}}
    <div class="bg-white border-t border-sand/30 py-8">
        <div class="max-w-screen-xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center justify-center gap-6">

                {{-- Sol: Sosyal Medya --}}
                <div class="flex flex-col items-center md:items-center gap-3">
                    <div class="flex items-center gap-3">
                        <div class="h-px w-16 bg-sand/50"></div>
                        <p class="font-body text-xs tracking-widest uppercase text-smoke">Bizi Takip Edin</p>
                        <div class="h-px w-16 bg-sand/50"></div>
                    </div>
                    <p class="font-body text-xs text-smoke/60">Sizin İçin Buradayız</p>
                    <div class="flex gap-3">
                        @if ($facebook)
                            <a href="{{ $facebook }}" target="_blank"
                                class="w-10 h-10 bg-[#1877f2] rounded-full flex items-center justify-center
                                  hover:opacity-80 transition-opacity">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                </svg>
                            </a>
                        @endif
                        @if ($instagram)
                            <a href="{{ $instagram }}" target="_blank"
                                class="w-10 h-10 bg-gradient-to-br from-[#f09433] via-[#e6683c] via-[#dc2743] via-[#cc2366] to-[#bc1888]
                                  rounded-full flex items-center justify-center hover:opacity-80 transition-opacity">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                </svg>
                            </a>
                        @endif
                        @if ($youtube)
                            <a href="{{ $youtube }}" target="_blank"
                                class="w-10 h-10 bg-[#ff0000] rounded-full flex items-center justify-center
                                  hover:opacity-80 transition-opacity">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                                </svg>
                            </a>
                        @endif
                        @if ($whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank"
                                class="w-10 h-10 bg-[#25d366] rounded-full flex items-center justify-center
                                  hover:opacity-80 transition-opacity">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Sağ: E-Bülten --}}
                {{-- <div class="flex items-center gap-3">
                <label class="font-body text-xs tracking-widest uppercase text-smoke whitespace-nowrap">
                    E-Bülten Aboneliği :
                </label>
                @if ($newsletterSent)
                    <p class="font-body text-xs text-green-600">Abone oldunuz!</p>
                @else
                    <div class="flex">
                        <input wire:model="newsletterEmail"
                               type="email"
                               placeholder="E-Posta Adresinizi Giriniz"
                               class="border border-sand/50 px-4 py-2.5 font-body text-xs w-64
                                      focus:outline-none focus:border-ink bg-transparent" />
                        <button wire:click="subscribeNewsletter"
                                class="bg-ink text-cream font-body text-xs tracking-widest uppercase
                                       px-5 py-2.5 hover:bg-smoke transition-colors whitespace-nowrap">
                            Üye Ol
                        </button>
                    </div>
                @endif
            </div> --}}
            </div>
        </div>
    </div>

    {{-- ── ANA FOOTER ── --}}
    <footer class="bg-[#f5f5f5] border-t border-sand/30">
        <div class="max-w-screen-xl mx-auto px-6 py-12">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8">

                {{-- Marka & İletişim --}}
                <div class="col-span-2 md:col-span-1 space-y-4">
                    <a href="{{ route('home') }}">
                        @php $logo = \App\Models\Setting::get('site_logo'); @endphp

                        @if ($logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}"
                                alt="{{ config('app.name') }}" class="h-40 pb-4 w-auto" />
                        @else
                            <span class="font-display text-3xl font-light tracking-wide text-ink">
                                {{ config('app.name', 'Eren Abiye') }}
                            </span>
                        @endif
                    </a>

                    @if ($phone)
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-smoke" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                                <p class="font-body text-[10px] tracking-widest uppercase text-smoke">Çağrı Merkezi</p>
                            </div>
                            <p class="font-body text-lg font-medium text-ink">{{ $phone }}</p>
                        </div>
                    @endif

                    @if ($whatsapp)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#25d366]" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                            <p class="font-body text-sm text-ink">{{ $whatsapp }}</p>
                        </div>
                    @endif

                    <div class="pt-2">
                        <p class="font-body text-xs text-smoke/60">©{{ date('Y') }} {{ $siteName }}</p>
                        <p class="font-body text-xs text-smoke/60">Tüm Hakları Saklıdır.</p>
                    </div>
                </div>

                {{-- Menü Kolonları --}}
                @php
                    $footerMenus = [
                        ['location' => 'footer_1', 'default' => 'Kurumsal'],
                        ['location' => 'footer_2', 'default' => 'Hızlı Erişim'],
                        ['location' => 'footer_3', 'default' => 'Kategoriler'],
                    ];
                @endphp

                @foreach ($footerMenus as $menuConfig)
                    @php
                        $menu = \App\Models\Menu::where('location', $menuConfig['location'])
                            ->where('is_active', true)
                            ->first();
                    @endphp

                    <div>
                        <h4
                            class="font-body text-xs tracking-widest uppercase text-ink font-semibold mb-4 pb-2 border-b border-sand/40">
                            {{ $menu?->name ?? $menuConfig['default'] }}
                        </h4>
                        @if ($menu)
                            <ul class="space-y-2">
                                @foreach ($menu->items->where('is_active', true)->sortBy('sort_order') as $item)
                                    <li>
                                        <a href="{{ $item->url }}" target="{{ $item->target ?? '_self' }}"
                                            class="flex items-center gap-1.5 font-body text-xs text-smoke
                                              hover:text-ink transition-colors group">
                                            <span class="text-sand group-hover:text-ink transition-colors">›</span>
                                            {{ $item->label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>

        {{-- Alt Bar: Ödeme Logoları --}}
        <div class="border-t border-sand/30 bg-white py-4">
            <div class="max-w-screen-xl mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center justify-center gap-4">

                    {{-- Banka Logoları --}}


                    {{-- Güvenlik Logoları --}}
                    <div class="flex text-center  gap-3 flex-wrap ">
                        <img src="{{ asset('storage/card.png') }}" alt="">
                    </div>

                </div>

                {{-- Yasal Linkler --}}
                <div class="flex flex-wrap items-center justify-center gap-4 mt-4 pt-4 border-t border-sand/20">
                    <a href="{{ route('page', 'gizlilik-ilkeleri') }}"
                        class="font-body text-[10px] text-smoke/50 hover:text-smoke transition-colors">
                        Gizlilik İlkeleri
                    </a>
                    <span class="text-sand/40">|</span>
                    <a href="{{ route('page', 'mesafeli-satis') }}"
                        class="font-body text-[10px] text-smoke/50 hover:text-smoke transition-colors">
                        Mesafeli Satış Sözleşmesi
                    </a>
                    <span class="text-sand/40">|</span>
                    <a href="{{ route('page', 'kvkk') }}"
                        class="font-body text-[10px] text-smoke/50 hover:text-smoke transition-colors">
                        KVKK
                    </a>
                    <span class="text-sand/40">|</span>
                    <a href="{{ route('page', 'iade-kosullari') }}"
                        class="font-body text-[10px] text-smoke/50 hover:text-smoke transition-colors">
                        İade Koşulları
                    </a>
                </div>
            </div>
        </div>
    </footer>
</div>
