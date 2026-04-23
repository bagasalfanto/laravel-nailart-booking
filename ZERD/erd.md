# Entity Relationship Diagram (ERD) - NailArt Booking System

## Daftar Tabel

1. [users](#users) - Data pengguna utama
2. [admins](#admins) - Profil admin
3. [nailists](#nailists) - Profil nailist (pekerja)
4. [customers](#customers) - Profil customer (pelanggan)
5. [treatment_katalogs](#treatment_katalogs) - Katalog treatment/layanan
6. [data_charms](#data_charms) - Data charm/dekorasi
7. [status_bookings](#status_bookings) - Status reservasi
8. [portfolios](#portfolios) - Portfolio karya nailist
9. [reservasis](#reservasis) - Reservasi/pemesanan
10. [penggunaan_charms](#penggunaan_charms) - Detail charm yang digunakan
11. [pembayarans](#pembayarans) - Data pembayaran
12. [activity_log](#activity_log) - Log aktivitas sistem

---

## Detail Tabel

### users
**Deskripsi:** Tabel utama untuk semua pengguna sistem (Admin, Nailist, Customer)

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk user |
| full_name | VARCHAR(255) | NOT NULL | Nama lengkap pengguna |
| username | VARCHAR(255) | NOT NULL | Username untuk login |
| avatar | TEXT | NULLABLE | URL atau path untuk avatar/foto profil |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email pengguna (unik) |
| phone_number | VARCHAR(255) | UNIQUE, NULLABLE | Nomor telepon (unik jika ada) |
| google_id | VARCHAR(255) | UNIQUE, NULLABLE | Google OAuth ID |
| password | VARCHAR(255) | NULLABLE | Password hash |
| remember_token | VARCHAR(100) | NULLABLE | Token untuk "remember me" |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- ONE-TO-ONE: `admins` (User → Admin)
- ONE-TO-ONE: `nailists` (User → Nailist)
- ONE-TO-ONE: `customers` (User → Customer)

---

### admins
**Deskripsi:** Profil admin yang memiliki relasi one-to-one dengan users

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk admin |
| user_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel users |
| kode_admin | VARCHAR(255) | UNIQUE, NOT NULL | Kode unik untuk admin |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `users` (Admin → User)
  - Foreign Key: `user_id`
  - Constraint: `CASCADE ON DELETE`

---

### nailists
**Deskripsi:** Profil nailist (pekerja salon) dengan spesialisasi

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk nailist |
| user_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel users |
| specialty | VARCHAR(255) | NULLABLE | Spesialisasi/keahlian nailist |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `users` (Nailist → User)
  - Foreign Key: `user_id`
  - Constraint: `CASCADE ON DELETE`
- ONE-TO-MANY: `portfolios` (Nailist ← Portfolio)
- ONE-TO-MANY: `reservasis` (Nailist ← Reservasi)

---

### customers
**Deskripsi:** Profil customer (pelanggan) yang melakukan pemesanan

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk customer |
| user_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel users |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `users` (Customer → User)
  - Foreign Key: `user_id`
  - Constraint: `CASCADE ON DELETE`
- ONE-TO-MANY: `reservasis` (Customer ← Reservasi)

---

### treatment_katalogs
**Deskripsi:** Katalog layanan/treatment yang ditawarkan salon

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk treatment |
| nama_jasa | VARCHAR(255) | NOT NULL | Nama jenis treatment |
| deskripsi | TEXT | NULLABLE | Deskripsi detail treatment |
| estimasi_harga | DECIMAL(12, 2) | NOT NULL | Harga estimasi (2 desimal) |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- ONE-TO-MANY: `reservasis` (TreatmentKatalog ← Reservasi)

---

### data_charms
**Deskripsi:** Data charm/dekorasi yang tersedia dan stoknya

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk charm |
| nama_charm | VARCHAR(255) | NOT NULL | Nama charm/dekorasi |
| stok | INTEGER | NOT NULL, DEFAULT 0 | Jumlah stok tersedia |
| harga | DECIMAL(12, 2) | NOT NULL | Harga per unit (2 desimal) |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- ONE-TO-MANY: `penggunaan_charms` (DataCharm ← PenggunaanCharm)

---

### status_bookings
**Deskripsi:** Master data untuk status reservasi

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk status |
| nama_status | VARCHAR(255) | NOT NULL | Nama status (Pending, Sukses, Dibatalkan, dll) |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- ONE-TO-MANY: `reservasis` (StatusBooking ← Reservasi)

---

### portfolios
**Deskripsi:** Portfolio/karya dari nailist

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk portfolio |
| nailist_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel nailists |
| gambar_url | VARCHAR(255) | NOT NULL | URL atau path gambar portfolio |
| deskripsi | TEXT | NULLABLE | Deskripsi karya |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `nailists` (Portfolio → Nailist)
  - Foreign Key: `nailist_id`
  - Constraint: `CASCADE ON DELETE`

---

### reservasis
**Deskripsi:** Tabel reservasi/pemesanan treatment dari customer

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk reservasi |
| customer_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel customers |
| nailist_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel nailists |
| treatment_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel treatment_katalogs |
| status_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel status_bookings |
| tanggal | DATE | NOT NULL | Tanggal reservasi |
| jam | TIME | NOT NULL | Jam/waktu reservasi |
| referensi_desain | VARCHAR(255) | NULLABLE | URL/path referensi desain |
| total_harga_final | DECIMAL(12, 2) | NULLABLE | Total harga akhir (2 desimal) |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `customers` (Reservasi → Customer)
  - Foreign Key: `customer_id`
- MANY-TO-ONE: `nailists` (Reservasi → Nailist)
  - Foreign Key: `nailist_id`
- MANY-TO-ONE: `treatment_katalogs` (Reservasi → TreatmentKatalog)
  - Foreign Key: `treatment_id`
- MANY-TO-ONE: `status_bookings` (Reservasi → StatusBooking)
  - Foreign Key: `status_id`
- ONE-TO-MANY: `penggunaan_charms` (Reservasi ← PenggunaanCharm)
- ONE-TO-ONE: `pembayarans` (Reservasi ← Pembayaran)

---

### penggunaan_charms
**Deskripsi:** Detail charm yang digunakan pada setiap reservasi (junction table)

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier |
| reservasi_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel reservasis |
| charm_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel data_charms |
| jumlah_dipakai | INTEGER | NOT NULL | Jumlah charm yang digunakan |
| subtotal | DECIMAL(12, 2) | NOT NULL | Subtotal harga (2 desimal) |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `reservasis` (PenggunaanCharm → Reservasi)
  - Foreign Key: `reservasi_id`
  - Constraint: `CASCADE ON DELETE`
- MANY-TO-ONE: `data_charms` (PenggunaanCharm → DataCharm)
  - Foreign Key: `charm_id`

---

### pembayarans
**Deskripsi:** Data pembayaran untuk setiap reservasi (integrasi Midtrans)

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk pembayaran |
| reservasi_id | UUID | FOREIGN KEY, NOT NULL | Referensi ke tabel reservasis |
| payment_url | VARCHAR(255) | NULLABLE | URL payment gateway (Midtrans) |
| payment_token | VARCHAR(255) | NULLABLE | Token untuk transaksi Midtrans |
| gateway_transaction_id | VARCHAR(255) | NULLABLE | ID transaksi dari payment gateway |
| jenis_pembayaran | VARCHAR(255) | NULLABLE | Jenis pembayaran (credit_card, transfer, dll) |
| nominal | DECIMAL(12, 2) | NOT NULL | Nominal pembayaran (2 desimal) |
| status_pembayaran | VARCHAR(255) | NOT NULL, DEFAULT 'pending' | Status pembayaran (pending, settlement, failed, dll) |
| batas_waktu_bayar | TIMESTAMP | NULLABLE | Batas waktu pembayaran |
| waktu_pembayaran | TIMESTAMP | NULLABLE | Waktu pembayaran dilakukan |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Relasi:**
- MANY-TO-ONE: `reservasis` (Pembayaran → Reservasi)
  - Foreign Key: `reservasi_id`
  - Constraint: `CASCADE ON DELETE`

---

### activity_log
**Deskripsi:** Log aktivitas untuk audit trail sistem

| Field | Tipe Data | Constraint | Deskripsi |
|-------|-----------|-----------|-----------|
| id | UUID | PRIMARY KEY, NOT NULL | Unique identifier untuk log |
| log_name | VARCHAR(255) | INDEX, NULLABLE | Nama log kategori |
| description | TEXT | NOT NULL | Deskripsi aktivitas |
| subject_id | VARCHAR(255) | NULLABLE | ID dari entitas yang dipengaruhi |
| subject_type | VARCHAR(255) | NULLABLE | Tipe model dari subject |
| event | VARCHAR(255) | NULLABLE | Event yang terjadi (created, updated, deleted) |
| causer_id | VARCHAR(255) | NULLABLE | ID user yang melakukan aksi |
| causer_type | VARCHAR(255) | NULLABLE | Tipe model dari causer |
| attribute_changes | JSON | NULLABLE | JSON data perubahan atribut |
| properties | JSON | NULLABLE | JSON properties tambahan |
| created_at | TIMESTAMP | NOT NULL | Waktu dibuat |
| updated_at | TIMESTAMP | NOT NULL | Waktu terakhir diupdate |

**Catatan:** Menggunakan polymorphic morph untuk subject dan causer, memungkinkan relasi ke berbagai model.

---

## Relationship Summary (Ringkasan Relasi)

### One-to-One (1:1)
- `users` ↔ `admins` (bergantung pada role)
- `users` ↔ `nailists` (bergantung pada role)
- `users` ↔ `customers` (bergantung pada role)
- `reservasis` ↔ `pembayarans`

### One-to-Many (1:N)
- `nailists` → `portfolios` (satu nailist punya banyak portfolio)
- `nailists` → `reservasis` (satu nailist handle banyak reservasi)
- `customers` → `reservasis` (satu customer buat banyak reservasi)
- `treatment_katalogs` → `reservasis` (satu treatment bisa di-book banyak kali)
- `status_bookings` → `reservasis` (satu status punya banyak reservasi)
- `reservasis` → `penggunaan_charms` (satu reservasi punya banyak charm yang digunakan)
- `data_charms` → `penggunaan_charms` (satu charm bisa digunakan di banyak reservasi)

### Many-to-Many (M:N) - Via Junction Table
- `reservasis` ↔ `data_charms` (melalui `penggunaan_charms`)
  - Satu reservasi bisa menggunakan banyak charm
  - Satu charm bisa digunakan di banyak reservasi

---

## Key Constraints Summary

### Primary Keys
Semua tabel menggunakan **UUID** sebagai primary key (bukan auto-increment integer)

### Foreign Keys dengan CASCADE ON DELETE
- `admins.user_id` → `users.id`
- `nailists.user_id` → `users.id`
- `customers.user_id` → `users.id`
- `portfolios.nailist_id` → `nailists.id`
- `reservasis.customer_id` → `customers.id`
- `reservasis.nailist_id` → `nailists.id`
- `penggunaan_charms.reservasi_id` → `reservasis.id`
- `pembayarans.reservasi_id` → `reservasis.id`

### Unique Constraints
- `users.email` - Email unik per user
- `users.phone_number` - No telepon unik (jika ada)
- `users.google_id` - Google OAuth ID unik
- `admins.kode_admin` - Kode admin unik
- `data_charms.nama_charm` - Nama charm unik (implisit dalam business logic)

### Indexed Fields
- `activity_log.log_name` - Untuk pencarian log cepat
- `users.email`, `users.phone_number`, `users.google_id` - Untuk index unik
- Foreign keys secara otomatis ter-index

---

## Data Types Summary

| Tipe | Penggunaan | Contoh |
|------|-----------|--------|
| **UUID** | Primary & Foreign Keys | id, user_id, nailist_id |
| **VARCHAR(N)** | Teks pendek | full_name, email, kode_admin |
| **TEXT** | Teks panjang | deskripsi, description |
| **INTEGER** | Angka bulat | stok, jumlah_dipakai |
| **DECIMAL(12,2)** | Angka desimal (uang) | harga, total_harga_final, nominal |
| **DATE** | Tanggal | tanggal reservasi |
| **TIME** | Waktu | jam reservasi |
| **TIMESTAMP** | Tanggal & Jam | created_at, updated_at |
| **JSON** | Data terstruktur | attribute_changes, properties |
| **BOOLEAN** (implicit) | Ya/Tidak | Tidak digunakan langsung |

---

## Default Values

- `data_charms.stok` = 0
- `pembayarans.status_pembayaran` = 'pending'

---

## Nullable Fields

Fields yang dapat NULL (optional):
- `users.avatar`, `users.phone_number`, `users.google_id`, `users.password`, `users.remember_token`
- `nailists.specialty`
- `treatment_katalogs.deskripsi`
- `portfolios.deskripsi`
- `reservasis.referensi_desain`, `reservasis.total_harga_final`
- `pembayarans.payment_url`, `pembayarans.payment_token`, `pembayarans.gateway_transaction_id`, `pembayarans.jenis_pembayaran`, `pembayarans.batas_waktu_bayar`, `pembayarans.waktu_pembayaran`
- `activity_log.log_name`, `activity_log.subject_id`, `activity_log.subject_type`, `activity_log.event`, `activity_log.causer_id`, `activity_log.causer_type`, `activity_log.attribute_changes`, `activity_log.properties`

---

## Database Statistics

- **Total Tables**: 12 (termasuk auxiliary tables seperti password_reset_tokens, sessions, dan permission tables)
- **Core Business Tables**: 10
- **System/Audit Tables**: 2
- **Total Relationships**: 16+
- **UUID Usage**: Semua custom tables menggunakan UUID
- **Timestamp Tracking**: Semua tables (except activity_log softly) memiliki created_at & updated_at
