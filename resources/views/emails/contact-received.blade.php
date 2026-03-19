<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #333; border-bottom: 2px solid #000; padding-bottom: 10px;">
         {{env('APP_NAME')}} Yeni İletişim Formu
    </h2>

    <p style="color: #666; margin: 20px 0;">
        <strong>Ad Soyad:</strong> {{ $contact->name }}
    </p>

    <p style="color: #666; margin: 20px 0;">
        <strong>Email:</strong> {{ $contact->email }}
    </p>

    <p style="color: #666; margin: 20px 0;">
        <strong>Telefon:</strong> {{ $contact->phone ?? '-' }}
    </p>

    <p style="color: #666; margin: 20px 0;">
        <strong>Konu:</strong> {{ $contact->subject }}
    </p>

    <div style="background-color: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #000;">
        <strong>Mesaj:</strong>
        <p style="color: #333; white-space: pre-wrap;">{{ $contact->message }}</p>
    </div>

    <p style="color: #999; font-size: 12px; margin-top: 30px;">
        Gönderim Tarihi: {{ $contact->created_at->format('d.m.Y H:i') }}
    </p>
</div>