<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

new #[Layout('layouts.app')] #[Title('Kayıt Ol — Eren Abiye')] class extends Component {
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|min:8|confirmed')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation  = '';

    #[Validate('accepted')]
    public bool $terms = false;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect(route('home'));
        }
    }

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        $cart = session()->get('cart', []);
        foreach ($cart as $item) {
            \App\Models\CartItem::create([
                'user_id' => $user->id,
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
            ]);
        }
        session()->forget('cart');

        session()->regenerate();
        $this->redirect(route('home'));
    }
};
?>

<div class="min-h-screen flex items-center justify-center py-16 px-6">
    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="text-center mb-10">
            <a href="{{ route('home') }}" class="font-display text-4xl font-light text-ink">
                Eren Abiye
            </a>
            <p class="font-body text-xs tracking-widest2 uppercase text-smoke mt-3">
                Yeni Hesap Oluşturun
            </p>
        </div>

        <div class="bg-white border border-sand/30 p-8">
            <div class="space-y-5">

                <div>
                    <label class="font-body text-xs text-smoke block mb-1.5">Ad Soyad *</label>
                    <input wire:model="name" type="text" autocomplete="name"
                        class="w-full border border-sand px-4 py-3 font-body text-sm
                                  focus:outline-none focus:border-ink bg-transparent transition-colors" />
                    @error('name')
                        <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-body text-xs text-smoke block mb-1.5">E-posta *</label>
                    <input wire:model="email" type="email" autocomplete="email"
                        class="w-full border border-sand px-4 py-3 font-body text-sm
                                  focus:outline-none focus:border-ink bg-transparent transition-colors" />
                    @error('email')
                        <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-body text-xs text-smoke block mb-1.5">Şifre *</label>
                    <input wire:model="password" type="password" autocomplete="new-password"
                        class="w-full border border-sand px-4 py-3 font-body text-sm
                                  focus:outline-none focus:border-ink bg-transparent transition-colors" />
                    @error('password')
                        <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="font-body text-xs text-smoke block mb-1.5">Şifre Tekrar *</label>
                    <input wire:model="password_confirmation" type="password" autocomplete="new-password"
                        class="w-full border border-sand px-4 py-3 font-body text-sm
                                  focus:outline-none focus:border-ink bg-transparent transition-colors" />
                </div>

                <div>
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input wire:model="terms" type="checkbox" class="w-4 h-4 border-sand mt-0.5 flex-shrink-0" />
                        <span class="font-body text-xs text-smoke leading-relaxed">
                            <a href="/sartlar-kosullar" class="underline hover:text-ink">Şartlar & Koşullar</a>
                            ve
                            <a href="/gizlilik-ilkeleri" class="underline hover:text-ink">Gizlilik Politikası</a>'nı
                            okudum ve kabul ediyorum. *
                        </span>
                    </label>
                    @error('terms')
                        <p class="font-body text-xs text-red-500 mt-1">Devam etmek için kabul etmelisiniz.</p>
                    @enderror
                </div>

                <button wire:click="register" wire:loading.attr="disabled"
                    class="w-full bg-ink text-cream font-body text-xs tracking-widest2
                               uppercase py-4 hover:bg-smoke transition-colors
                               disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="register">Kayıt Ol</span>
                    <span wire:loading wire:target="register">Kayıt olunuyor...</span>
                </button>
            </div>

            <div class="border-t border-sand/30 mt-6 pt-6 text-center">
                <p class="font-body text-xs text-smoke">
                    Zaten hesabınız var mı?
                    <a href="{{ route('login') }}" class="text-ink underline hover:text-smoke transition-colors ml-1">
                        Giriş Yapın
                    </a>
                </p>
            </div>
        </div>

        <p class="text-center font-body text-xs text-smoke/50 mt-6">
            <a href="{{ route('home') }}" class="hover:text-smoke transition-colors">
                ← Ana Sayfaya Dön
            </a>
        </p>
    </div>

    {{-- Structured Data --}}
{{--     <script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebPage",
    "name": "Giriş Yap — Eren Abiye",
    "url": "{{ route('login') }}",
    "isPartOf": {
        "@type": "WebSite",
        "name": "Eren Abiye",
        "url": "{{ config('app.url') }}"
    }
} --}}
</script>
</div>
