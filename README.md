# MIKBILL - Mikrotik Billing & PPPoE Management

![MIKBILL Logo](https://via.placeholder.com/800x200?text=MIKBILL+System)

**MIKBILL** adalah aplikasi berbasis web yang dibangun menggunakan **Laravel** untuk membantu pengusaha ISP / RT RW Net dalam mengelola pelanggan PPPoE, tagihan bulanan, serta otomatisasi isolir (pemutusan koneksi) bagi pelanggan yang menunggak.

Aplikasi ini terintegrasi langsung dengan Mikrotik melalui API dan dilengkapi dengan fitur WhatsApp Gateway untuk notifikasi otomatis.

---

## 🚀 Fitur Utama

### 📡 Manajemen & Monitoring
- **Real-time Monitoring:** Melihat status online/offline pelanggan PPPoE.
- **Sinkronisasi Mikrotik:** Import/Sync data Secret dari Mikrotik ke Database.
- **CRUD Pelanggan:** Tambah, Edit, Hapus user PPPoE langsung dari web.
- **Kick & Disable:** Memutus koneksi user secara paksa atau menonaktifkan akun.

### 💰 Billing & Keuangan
- **Generate Tagihan Massal:** Membuat invoice bulanan untuk semua pelanggan aktif.
- **Tagihan Manual:** Membuat invoice perorangan (pro-rata/pemasangan baru).
- **Cetak Invoice:** Invoice profesional (PDF Ready) dengan Logo & Tanda Tangan perusahaan.
- **Laporan Keuangan:** Rekapitulasi omset, uang masuk, dan piutang.

### 🤖 Otomatisasi (Scheduler)
- **Auto Isolir:** Otomatis men-disable user PPPoE yang melewati jatuh tempo.
- **Auto Aktif:** Otomatis mengaktifkan user (Enable Secret) setelah status tagihan lunas.

### 📱 Notifikasi & Frontend
- **WhatsApp Gateway:** Kirim notifikasi tagihan, pembayaran diterima, dan broadcast info.
- **Halaman Cek Tagihan:** Pelanggan bisa cek tagihan & download invoice tanpa login.
- **Multi-Role:** Hak akses Admin (Full) dan Operator (Terbatas pada wilayah/tanggung jawab).

---

## 🛠️ Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL / MariaDB
- Mikrotik RouterOS (API Port Enabled)
- Koneksi Internet (Untuk WhatsApp API)

---

## 📦 Instalasi

Ikuti langkah-langkah berikut untuk menjalankan MIKBILL di komputer lokal atau server:

### 1. Clone Repositori
```bash
git clone [https://github.com/username/mikbill.git](https://github.com/username/mikbill.git)
cd mikbill

