<div align="center">

# 👔 Eren Abiye E-Ticaret Platformu

**Giyim sektörüne özel geliştirilmiş, modern ve ölçeklenebilir online satış çözümü.**

![PHP](https://img.shields.io/badge/PHP-8.3-7747CC?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5-FB923C?style=for-the-badge&logo=filament&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-4E9EF4?style=for-the-badge&logo=livewire&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38BDF8?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![PayTR](https://img.shields.io/badge/PayTR-iFrame_API-FF6584?style=for-the-badge)

`E-Ticaret` &nbsp;•&nbsp; `Giyim` &nbsp;•&nbsp; `Online Ödeme` &nbsp;•&nbsp; `Admin Panel`

</div>

---

> 🇬🇧 [Click here for English README](#-eren-abi-e-commerce-platform)

---

## 📌 Proje Hakkında

Bu proje, **Eren Abiye firmasının** e-ticaret altyapısını hayata geçirmek amacıyla geliştirilmiştir. Giyim sektöründe online satış yapmak isteyen firmaların kolayca adapte edebileceği şekilde tasarlanmış, modern teknoloji yığını üzerine inşa edilmiş kapsamlı bir e-ticaret çözümüdür. Müşteri arayüzü, yönetim paneli ve güvenli ödeme entegrasyonunu tek çatı altında sunar.

---

## ⚙️ Kullanılan Teknolojiler

| Teknoloji | Versiyon | Kullanım Amacı |
|-----------|----------|----------------|
| 🐘 PHP | 8.3 | Sunucu tarafı dil |
| 🔴 Laravel | 12 | Ana uygulama framework'ü |
| 🔶 Filament | 5 | Admin panel arayüzü |
| ⚡ Livewire | 4 | Reaktif, dinamik UI bileşenleri |
| 🎨 Tailwind CSS | — | Utility-first CSS framework |
| 🗄️ PostgreSQL | — | İlişkisel veritabanı |
| 💳 PayTR | iFrame API | Online ödeme altyapısı |

---

## ✨ Öne Çıkan Özellikler

- 🛍️ **Ürün Yönetimi** — Kategori, beden, renk ve stok yönetimi ile kapsamlı ürün kataloğu
- 🛒 **Sepet & Sipariş** — Gerçek zamanlı sepet yönetimi, sipariş takibi ve durum bildirimleri
- 💳 **Güvenli Ödeme** — PayTR iFrame API ile PCI-DSS uyumlu, güvenli online ödeme akışı
- 🖥️ **Admin Panel** — Filament 5 tabanlı modern, kullanıcı dostu yönetim arayüzü
- ⚡ **Reaktif Arayüz** — Livewire 4 ile sayfa yenilemeden çalışan dinamik kullanıcı deneyimi
- 📱 **Responsive Tasarım** — Tailwind CSS ile tüm cihazlarda kusursuz görüntüleme

---

## 🚀 Kurulum

**1. Repoyu klonlayın**
```bash
git clone https://github.com/KULLANICI/REPO.git
cd REPO
```

**2. Bağımlılıkları yükleyin**
```bash
composer install
npm install && npm run build
```

**3. Ortam değişkenlerini ayarlayın**
```bash
cp .env.example .env
php artisan key:generate
```

**4. `.env` dosyasında veritabanı bağlantısını yapılandırın**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=veritabani_adi
DB_USERNAME=kullanici_adi
DB_PASSWORD=sifre
```

**5. Veritabanını oluşturun**
```bash
php artisan migrate --seed
```

**6. Uygulamayı başlatın**
```bash
php artisan serve
```

---

## 💳 Ödeme Altyapısı

Ödeme altyapısı olarak **PayTR iFrame API** kullanılmaktadır. Müşteri kart bilgilerini doğrudan PayTR'nin güvenli ortamına girerek ödeme yapar; bu sayede kart bilgileri hiçbir zaman uygulama sunucusuna ulaşmaz ve yüksek güvenlik standardı sağlanır.

> 🔐 PayTR entegrasyonu için `.env` dosyasına `PAYTR_MERCHANT_ID`, `PAYTR_MERCHANT_KEY` ve `PAYTR_MERCHANT_SALT` değerlerini eklemeniz gerekmektedir.

---

<div align="center">

Bu proje **Eren Abiye Firması** için geliştirilmiştir.
Giyim sektöründeki tüm e-ticaret ihtiyaçları gözetilerek tasarlanmıştır.

</div>

---
---

<div align="center">

# 👔 Eren Abiye E-Commerce Platform

**A modern, scalable online sales solution built specifically for the fashion & clothing industry.**

![PHP](https://img.shields.io/badge/PHP-8.3-7747CC?style=for-the-badge&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-5-FB923C?style=for-the-badge&logo=filament&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-4-4E9EF4?style=for-the-badge&logo=livewire&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38BDF8?style=for-the-badge&logo=tailwind-css&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)
![PayTR](https://img.shields.io/badge/PayTR-iFrame_API-FF6584?style=for-the-badge)

`E-Commerce` &nbsp;•&nbsp; `Fashion` &nbsp;•&nbsp; `Online Payment` &nbsp;•&nbsp; `Admin Panel`

</div>

---

> 🇹🇷 [Türkçe README için tıklayın](#-eren-abi-e-ticaret-platformu)

---

## 📌 About the Project

This project was developed to bring the e-commerce infrastructure of **Eren Abiye Company** to life. It is a comprehensive e-commerce solution built on a modern technology stack, designed to be easily adaptable for businesses looking to sell clothing online. It provides a customer-facing storefront, an administration panel, and secure payment integration — all under one roof.

---

## ⚙️ Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| 🐘 PHP | 8.3 | Server-side language |
| 🔴 Laravel | 12 | Core application framework |
| 🔶 Filament | 5 | Admin panel interface |
| ⚡ Livewire | 4 | Reactive, dynamic UI components |
| 🎨 Tailwind CSS | — | Utility-first CSS framework |
| 🗄️ PostgreSQL | — | Relational database |
| 💳 PayTR | iFrame API | Online payment infrastructure |

---

## ✨ Key Features

- 🛍️ **Product Management** — Full product catalog with category, size, color, and stock management
- 🛒 **Cart & Orders** — Real-time cart management, order tracking, and status notifications
- 💳 **Secure Payments** — PCI-DSS compliant, secure online payment flow via PayTR iFrame API
- 🖥️ **Admin Panel** — Modern, user-friendly management interface powered by Filament 5
- ⚡ **Reactive UI** — Dynamic, page-reload-free user experience powered by Livewire 4
- 📱 **Responsive Design** — Flawless display across all devices with Tailwind CSS

---

## 🚀 Getting Started

**1. Clone the repository**
```bash
git clone https://github.com/USERNAME/REPO.git
cd REPO
```

**2. Install dependencies**
```bash
composer install
npm install && npm run build
```

**3. Configure environment variables**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Set up your database connection in `.env`**
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password
```

**5. Run migrations and seeders**
```bash
php artisan migrate --seed
```

**6. Start the application**
```bash
php artisan serve
```

---

## 💳 Payment Infrastructure

The payment infrastructure is built on **PayTR iFrame API**. Customers enter their card details directly into PayTR's secure environment, meaning sensitive payment data never touches the application server — ensuring a high standard of security and compliance.

> 🔐 For PayTR integration, add `PAYTR_MERCHANT_ID`, `PAYTR_MERCHANT_KEY` and `PAYTR_MERCHANT_SALT` to your `.env` file.

---

<div align="center">

This project was developed for **Eren Abiye Company**.
Designed to meet all e-commerce needs in the clothing industry.

</div>
