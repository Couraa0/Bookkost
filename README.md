# BookKost 🏠✨

**BookKost** adalah platform digital modern untuk mencari, membandingkan, dan memesan kost secara online di seluruh Indonesia. BookKost memudahkan pencari kost dan pemilik kost untuk terhubung secara **aman**, **cepat**, dan **transparan**.

---

## 🚀 Fitur Utama

- 🔍 **Pencarian Kost Canggih:**  
  Temukan kost berdasarkan lokasi, harga, fasilitas, dan filter lainnya.
- 🏷️ **Booking & Pembayaran Online:**  
  Booking kost langsung dari aplikasi dan lakukan pembayaran dengan metode yang mudah.
- ⭐ **Ulasan & Rating Penghuni Asli:**  
  Lihat review dan rating dari penghuni sebelumnya untuk keputusan yang lebih baik.
- ❤️ **Favorit Kost:**  
  Simpan kost favorit Anda untuk akses cepat di kemudian hari.
- 🏢 **Dashboard Pemilik Kost:**  
  Pemilik dapat mengelola listing kost, melihat booking, dan memantau statistik.
- 🛡️ **Verifikasi Pemilik Kost:**  
  Proses verifikasi dokumen untuk keamanan dan kepercayaan pengguna.
- 🚩 **Laporan Listing Palsu/Menyesatkan:**  
  Pengguna dapat melaporkan listing yang tidak valid untuk menjaga kualitas platform.
- ☎️ **Dukungan Pelanggan 24/7:**  
  Tim support siap membantu Anda kapan saja.

---

## 🛠️ Cara Instalasi

1. **Clone repository ini** ke folder `htdocs` XAMPP Anda:
   ```bash
   git clone https://github.com/username/BookKost.git
   ```
2. **Buat database MySQL** dan import file SQL:
   - Buka **phpMyAdmin**, buat database baru (misal: `booking_kost`)
   - Import file `booking_kost.sql` jika ada
3. **Konfigurasi koneksi database** di `config/database.php`:
   - Sesuaikan host, username, password, dan nama database sesuai server Anda
4. **Jalankan XAMPP** dan akses aplikasi melalui browser:
   ```
   http://localhost/APSI/main.php
   ```
5. **Login** sebagai user, owner, atau admin:
   - 👤 **User:** Daftar melalui aplikasi
   - 🏠 **Owner:** Hubungi admin via WhatsApp dan kirimkan dokumen persyaratan
   - 🛡️ **Admin:** Login menggunakan akun admin yang sudah terdaftar

---

## 📁 Struktur Folder

- `main.php` — Entry point aplikasi
- `pages/` — Semua halaman utama (home, search, about, dashboard, admin, dll)
- `img/` — Folder gambar kost, user, dokumen KTP/selfie
- `includes/` — Fungsi, helper, dan autentikasi
- `config/` — Konfigurasi database dan pengaturan aplikasi
- `assets/` — (Opsional) CSS, JS, dan file statis lainnya

---

## 🔄 Alur Penggunaan

### 👤 Untuk Pencari Kost (User)
1. Daftar akun sebagai user
2. Login dan cari kost sesuai kebutuhan
3. Lihat detail, ulasan, dan rating kost
4. Booking kost dan lakukan pembayaran
5. Berikan ulasan setelah masa sewa selesai

### 🏠 Untuk Pemilik Kost (Owner)
1. Hubungi admin via WhatsApp dan kirimkan dokumen persyaratan:
    - Nama Lengkap
    - Username yang diinginkan
    - Email aktif
    - No. Telepon
    - Password yang diinginkan
    - Foto KTP (upload file)
    - Foto diri sambil memegang KTP (upload file)
2. Tunggu proses verifikasi dari admin
3. Setelah akun aktif, login sebagai owner
4. Tambahkan dan kelola kost Anda melalui dashboard owner

### 🛡️ Untuk Admin
1. Login sebagai admin
2. Verifikasi permintaan owner baru
3. Kelola data user, kost, booking, dan laporan
4. Pantau statistik dan aktivitas aplikasi

---

## 👨‍💻 Tim Pengembang

BookKost dikembangkan oleh tim berikut:

| Nama                  | NPM              | GitHub                                      |
|-----------------------|------------------|---------------------------------------------|
| 🧕 Alifia Nur Huda       | 2310631250005    | [@alifiafia](https://github.com/alifiafia)         |
| 👨‍💻 M Rakha Syamputra     | 2310631250024    | [@couraa0](https://github.com/couraa0)     |
| 👨‍💻 Rizky Azhari Putra    | 2310631250028    | [@rizky161004](https://github.com/rizky161004)   |
| 👩 Yuuka Natasya Aji     | 2310631250079    | [@yuukanatasyaa](https://github.com/yuukanatasyaa)     |
| 👦 Farhan Ramadhan       | 2310631250089    | [@kecuppaang](https://github.com/kecuppaang)       |
| 👩 Rizka Amaniah         | 2310631250076    | [@rizkaamaniah](https://github.com/rizkaamaniah)           |

---

## 📞 Kontak & Dukungan

- 📧 **Email:** muhammadrakhasyamputra@gmail.com
- 📱 **WhatsApp:** [+62 878-7131-0560](https://wa.me/62878771310560)
- 📸 **Instagram:** [@couraa0](https://instagram.com/couraa0)

---

## ℹ️ Informasi Tambahan

- BookKost dapat digunakan di berbagai perangkat (mobile & desktop).
- Jika menemukan bug atau ingin berkontribusi, silakan hubungi tim pengembang melalui kontak di atas.

---

## 📝 Lisensi

Aplikasi ini dikembangkan untuk keperluan Project Akhir Mata Kuliah Analisa dan Perancangan Sistem Informasi.  
Hak cipta &copy; BookKost Team.

---
