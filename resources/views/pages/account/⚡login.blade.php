<?php
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] #[Title('Giriş Yap — Eren Abiye')] class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public bool $remember = false;
    public string $errorMessage = '';

    public function mount(): void
    {
        if (auth()->check()) {
            $this->redirect(route('home'));
        }
    }

    public function login(): void
    {
        $this->validate();
        $this->errorMessage = '';

        if (
            Auth::attempt(
                [
                    'email' => $this->email,
                    'password' => $this->password,
                ],
                $this->remember,
            )
        ) {
            // Misafir sepetini DB'ye aktar
            $this->mergeGuestCart();

            session()->regenerate();

            $redirect = request()->get('redirect', 'home');
            $this->redirect(route($redirect));
            return;
        }

        $this->errorMessage = 'E-posta veya şifre hatalı.';
    }

    private function mergeGuestCart(): void
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return;
        }

        foreach ($cart as $item) {
            $existing = \App\Models\CartItem::where('user_id', auth()->id())
                ->where('product_id', $item['product_id'])
                ->where('product_variant_id', $item['product_variant_id'])
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item['quantity']);
            } else {
                \App\Models\CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        session()->forget('cart');
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
                Hesabınıza Giriş Yapın
            </p>
        </div>

        <div class="bg-white border border-sand/30 p-8">

            {{-- Hata Mesajı --}}
            @if ($errorMessage)
                <div class="bg-red-50 border border-red-200 px-4 py-3 mb-6">
                    <p class="font-body text-xs text-red-600">{{ $errorMessage }}</p>
                </div>
            @endif

            <div class="space-y-5">
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
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="font-body text-xs text-smoke">Şifre *</label>
                        <a href="/sifremi-unuttum"
                            class="font-body text-xs text-smoke hover:text-ink underline transition-colors">
                            Şifremi Unuttum
                        </a>
                    </div>
                    <input wire:model="password" type="password" autocomplete="current-password"
                        class="w-full border border-sand px-4 py-3 font-body text-sm
                                  focus:outline-none focus:border-ink bg-transparent transition-colors" />
                    @error('password')
                        <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input wire:model="remember" type="checkbox" id="remember" class="w-4 h-4 border-sand" />
                    <label for="remember" class="font-body text-xs text-smoke cursor-pointer">
                        Beni hatırla
                    </label>
                </div>

                <button wire:click="login" wire:loading.attr="disabled"
                    class="w-full bg-ink text-cream font-body text-xs tracking-widest2
                               uppercase py-4 hover:bg-smoke transition-colors
                               disabled:opacity-70 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="login">Giriş Yap</span>
                    <span wire:loading wire:target="login">Giriş yapılıyor...</span>
                </button>
            </div>

            <div class="border-t border-sand/30 mt-6 pt-6 text-center">
                <p class="font-body text-xs text-smoke">
                    Hesabınız yok mu?
                    <a href="{{ route('register') }}"
                        class="text-ink underline hover:text-smoke transition-colors ml-1">
                        Kayıt Olun
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
