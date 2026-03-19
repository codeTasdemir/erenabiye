<?php

use Livewire\Component;
use App\Models\Contact;
use App\Mail\ContactReceived;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';
    public bool $submitted = false;

    public function submit()
    {
        $this->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:2000',
        ]);

        $contact = Contact::create([
            'name'    => $this->name,
            'email'   => $this->email,
            'phone'   => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
        ]);

        Mail::to(config('mail.from.address'))->send(new ContactReceived($contact));

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        $this->submitted = true;
        $this->dispatch('hideSuccess', delay: 5000);
    }

    #[\Livewire\Attributes\On('hideSuccess')]
    public function hideSuccess()
    {
        $this->submitted = false;
    }
};

?>

<div class="max-w py-6">
    <h6 class="font-display text-2xl text-center font-light text-ink mb-2">İletişim Formu</h6>

    @if($submitted)
        <div class="bg-green-50 border border-green-200 p-6 rounded mb-8 animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="font-body text-green-800">
                    Mesajınız başarıyla gönderildi. En kısa zamanda sizinle iletişime geçeceğiz.
                </p>
            </div>
        </div>
    @endif

    <form wire:submit="submit" class="bg-white border border-sand/30 p-8">
        <div class="grid sm:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="font-body text-sm text-smoke block mb-2">Ad Soyad *</label>
                <input 
                    wire:model="name" 
                    type="text"
                    class="w-full border border-sand px-4 py-2.5 font-body text-sm
                           focus:outline-none focus:border-ink bg-transparent">
                @error('name')
                    <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="font-body text-sm text-smoke block mb-2">E-posta *</label>
                <input 
                    wire:model="email" 
                    type="email"
                    class="w-full border border-sand px-4 py-2.5 font-body text-sm
                           focus:outline-none focus:border-ink bg-transparent">
                @error('email')
                    <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="font-body text-sm text-smoke block mb-2">Telefon</label>
                <input 
                    wire:model="phone" 
                    type="tel"
                    placeholder="05XX XXX XX XX"
                    class="w-full border border-sand px-4 py-2.5 font-body text-sm
                           focus:outline-none focus:border-ink bg-transparent">
            </div>

            <div>
                <label class="font-body text-sm text-smoke block mb-2">Konu *</label>
                <input 
                    wire:model="subject" 
                    type="text"
                    class="w-full border border-sand px-4 py-2.5 font-body text-sm
                           focus:outline-none focus:border-ink bg-transparent">
                @error('subject')
                    <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-6">
            <label class="font-body text-sm text-smoke block mb-2">Mesaj *</label>
            <textarea 
                wire:model="message" 
                rows="6"
                class="w-full border border-sand px-4 py-2.5 font-body text-sm
                       focus:outline-none focus:border-ink bg-transparent resize-none">
            </textarea>
            @error('message')
                <p class="font-body text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button 
            type="submit"
            wire:loading.attr="disabled"
            class="w-full bg-ink text-cream font-body text-sm tracking-widest uppercase
                   py-3 hover:bg-smoke transition-colors disabled:opacity-70">
            <span wire:loading.remove>Gönder</span>
            <span wire:loading>Gönderiliyor...</span>
        </button>
    </form>
</div>