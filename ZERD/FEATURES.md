# Daftar Fitur — nailby.hilda

Recap fitur aplikasi `nailby.hilda` (Laravel 12 + Tailwind v3 + Alpine.js)

---

## Landing Page (Publik, Tanpa Login)

1. Home / halaman utama (`/`) — featured portfolio, treatment unggulan, FAQ, testimonial.
2. Pricelist (`/pricelist`) — daftar kategori treatment + harga per layanan.
3. Daftar Nail Artist (`/naillist`) — list semua nailist aktif beserta specialty.
4. Detail Nail Artist (`/naillist/{nailist}`) — bio, portfolio, review dari customer.
5. Schedule (`/schedule`) — kalender (FullCalendar) booking publik.
6. Schedule events JSON (`/schedule/events`) — endpoint data kalender (filter role-based).
7. Schedule export (`/schedule/export`) — ekspor jadwal *(staff only: admin/superadmin/nailist)*.

---

## Auth & Verifikasi OTP

1. Register (`/register`) — pendaftaran customer baru.
2. Login (`/login`) — autentikasi email + password.
3. Logout (`POST /logout`).
4. Login dengan Google OAuth (`/auth/google/redirect` & `/auth/google/callback`) via Laravel Socialite.
5. Forgot Password (`/forgot-password`) — kirim link reset password ke email.
6. Reset Password (`/reset-password/{token}`) — form ganti password via token.
7. Update Password user yang sudah login (`PUT /password`).
8. Form OTP (`GET /verify-otp`) — input kode 6 digit.
9. Verify OTP (`POST /verify-otp`) — validasi kode (rate limit: 10 attempts, lockout 60 detik).
10. Resend OTP (`POST /verify-otp/resend`) — kirim ulang kode (throttle 6/menit).
11. Email verification alias (`/email/verify`) — redirect ke `/verify-otp` (kompatibel middleware `verified` Laravel).
12. Backup Email — store, resend, delete, verify lewat signed URL (`/account/backup-email/*`).

---

## Profile & Account Setup (Semua Role yang Login)

1. Edit profil (`/profile`) — update nama, username, nomor telpon, avatar; specialty (untuk nailist).
2. Update profil (`PATCH /profile`).
3. Hapus akun (`DELETE /profile`).
4. Account Setup wizard (`/account/setup`) — onboarding awal setelah register.

---

## Customer (Landing & Booking)

1. My Appointments (`/appointments`) — tab Upcoming, Completed, Canceled.
2. Booking Step 1 — pilih nailist (`/book/`).
3. Booking Step 2 — pilih treatment (`/book/review`).
4. Booking Step 3 — review & konfirmasi (`/book/payment`).
5. Payment Waiting (`/book/payment/waiting`).
6. Payment Success (`/book/payment/success`).
7. Payment Failed (`/book/payment/failed`).

> Catatan: flow booking masih **UI-only**. Belum simpan `Reservasi`, belum integrasi Midtrans Snap end-to-end.

---

## Dashboard Customer

1. Home dashboard — ringkasan booking terbaru + total booking.
2. My Reviews — list review yang pernah ditulis.
3. Tulis review baru untuk booking yang sudah selesai.
4. Edit review milik sendiri.
5. Hapus review milik sendiri.

---

## Dashboard Nailist

1. Home dashboard — clients today, custom designs, today earnings, pending confirmations, upcoming appointments, "up next" booking.
2. Bookings — list semua booking yang ditugaskan ke nailist tsb.
3. Detail booking + update status.
4. Hapus booking.
5. Portfolio — list portfolio milik nailist.
6. Tambah portfolio baru (upload foto + caption).
7. Edit portfolio.
8. Hapus portfolio.

---

## Dashboard Admin

1. Home dashboard — revenue metrics, total booking, jumlah customer & nailist, nailist aktif hari ini, trend 6 bulan & 7 hari.
2. Payment Monitoring — list pembayaran (DataTables) dengan filter status.
3. Konfirmasi pembayaran manual.
4. Stats payment — total, paid, pending, expired, revenue summary.
5. Reviews Management — list semua review (DataTables).
6. Toggle featured review (tampilkan di landing).
7. Hapus review.
8. Treatment Settings — kelola kategori treatment (CRUD + reorder drag-drop).
9. Treatment Settings — kelola item treatment per kategori (CRUD + reorder drag-drop).
10. Charm Management — CRUD charm/aksesori.
11. Portfolio Moderation — toggle portfolio yang di-feature di home.
12. Specialties Management — CRUD specialty nailist.
13. Web Settings — kelola konten landing (hero title/subtitle, CTA, FAQ title, dll.).
14. User Management (shared dengan superadmin) — list user, lihat detail, generate ulang password.

---

## Dashboard Superadmin

1. Home dashboard — total users, jumlah roles, jumlah permissions, users per role, recent users.
2. Users — list semua user (DataTables) + filter.
3. Detail user.
4. Tambah user baru.
5. Regenerate password user.
6. Edit user (full akses, termasuk role assignment).
7. Roles Management — list role.
8. Tambah role baru.
9. Edit role + assign permissions.
10. Hapus role.
11. Activity Log — list semua aksi user di sistem (Spatie Activitylog).
12. Detail activity log entry.
13. System Settings — konfigurasi sistem (mis. allowed email domain saat register).

---

## Cross-cutting (Berlaku Lintas Role)

1. Role-based access control (Spatie Permission) — `customer`, `nailist`, `admin`, `superadmin`.
2. Middleware `verified` — wajib lewat OTP sebelum akses dashboard.
3. Activity logging otomatis di semua model domain (trait `Loggable` + Spatie Activitylog).
4. Caching per model (trait `MakeCacheable` / ElipZis).
5. UUID sebagai primary key di semua tabel domain (trait `HasUuid`).
6. Sidebar dinamis di-seed dari `SidebarMenuSeeder`, conditional render via `@role` Blade directive.
7. Toast notification global (`partials/toast.blade.php`).
8. Layout terpisah: `<x-layouts.landing>` (publik) & `<x-app-layout>` (dashboard).

---

## Status Implementasi

- ✅ **Sudah jalan**: auth + OTP + Google OAuth, profile, semua dashboard role, CRUD treatment / charm / specialty / role / user / web-setting, portfolio nailist, review moderation, activity log, schedule view + export.
- ⚠️ **UI-only / belum end-to-end**: flow booking customer (`/book/*`) — belum simpan `Reservasi`, belum panggil Midtrans Snap. Payment Monitoring sudah tampil tapi sumber data masih dari seed/manual.
- 🔧 **Belum ada di UI**: pengaturan availability/jam kerja nailist, notification center, customer charm wallet/balance.
