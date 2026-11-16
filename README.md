# FrozeMate

**Smart Frozen Food Management & Recommendation System**  
*Web Programming Project — Laravel • PHP • MySQL • Blade • Bootstrap*

> *"Tell us what you need, we’ll build your bundle."*

---

## 1. Product Overview

**FrozeMate** adalah website untuk UMKM frozen food yang membantu pelanggan menemukan produk berdasarkan kebutuhan, jumlah orang, budget, dan jumlah makanan. Sistem kemudian memberikan rekomendasi **Smart Frozen Food Bundle** yang dapat langsung dibeli atau dikustomisasi.

### Value Proposition
Berbeda dari website frozen food biasa yang hanya menampilkan katalog, FrozeMate membantu pelanggan menentukan kombinasi produk yang sesuai dengan kebutuhan dan budget.

---

## 2. Problem Statement

- Customer harus mencari produk satu per satu.
- Customer tidak selalu tahu produk apa yang cocok dengan kebutuhannya.
- Customer kesulitan menentukan kombinasi produk berdasarkan budget.
- Customer harus menghitung sendiri total harga kombinasi produk.
- Belum tersedia rekomendasi paket berdasarkan jumlah orang atau kebutuhan.
- Dari sisi UMKM, pengelolaan produk, stok, dan pesanan perlu dilakukan secara lebih terorganisir.

---

## 3. Product Goals

| Area | Goal |
| :--- | :--- |
| **Customer** | Mempermudah pencarian dan pemilihan frozen food. |
| **Customer** | Memberikan rekomendasi produk berdasarkan kebutuhan dan budget. |
| **Customer** | Membantu customer membuat dan menyesuaikan bundle. |
| **Customer** | Mempermudah proses pemesanan. |
| **Admin/Owner** | Mengelola produk, kategori, stok, dan pesanan. |
| **Admin/Owner** | Melihat ringkasan penjualan melalui dashboard. |
| **Project** | Menerapkan konsep Laravel, CRUD, validation, session, middleware, authentication, dan collection. |

---

## 4. Target Users

| User | Deskripsi | Kebutuhan Utama |
| :--- | :--- | :--- |
| **Customer** | Orang yang ingin membeli frozen food. | Mencari produk, memperoleh rekomendasi bundle, checkout, dan melihat pesanan. |
| **Admin/Owner** | Pemilik atau pengelola UMKM. | Mengelola produk, kategori, stok, pesanan, dan ringkasan penjualan. |

---

## 5. Core Feature — Smart Frozen Food Bundle

**Smart Frozen Food Bundle** merupakan fitur utama FrozeMate. Customer tidak harus memilih produk satu per satu. Customer cukup memberikan beberapa parameter, kemudian sistem menghasilkan kombinasi produk yang sesuai.

### Input Customer

| Parameter | Contoh |
| :--- | :--- |
| **Kebutuhan** | Anak Kos / Keluarga / Bekal Sekolah / Meal Prep / Acara |
| **Jumlah orang** | 1, 2, 3, 4, 5+ |
| **Budget** | Rp25.000–50.000 / Rp50.000–75.000 / Rp75.000–100.000 / > Rp100.000 |
| **Jumlah makanan** | 3 / 5 / 7 / 10 kali makan |

### Contoh Output
- **Input:** Anak Kos, 1 orang, budget Rp75.000, target 5 kali makan.
- **Recommendation:** *Kos Saver Bundle*

| Produk | Qty |
| :--- | :---: |
| Nugget | 1 |
| Sosis | 1 |
| Dimsum | 1 |
| Kentang | 1 |

**Total contoh:** Rp72.000.

---

## 6. Smart Bundle Recommendation Logic

Untuk MVP, rekomendasi tidak membutuhkan AI. Sistem menggunakan **rule-based recommendation** yang memanfaatkan data produk, kategori, harga, stok, dan parameter kebutuhan customer.

- **Contoh aturan:** Jika kebutuhan = `"Anak Kos"`, jumlah orang = `1`, dan budget = `Rp50.000–75.000`, maka sistem mencari kombinasi produk yang relevan dan total harganya tidak melebihi budget.
- **Laravel Collection** digunakan untuk filtering, sorting, dan perhitungan data produk sebelum sistem memilih kombinasi bundle.

---

## 7. Custom Bundle

Setelah menerima rekomendasi, customer dapat mengubah isi bundle. Customer dapat mengganti, menghapus, atau menambahkan produk. Sistem menghitung ulang total harga secara otomatis.

- **Contoh:** Kentang → diganti menjadi Fish Ball.

---

## 8. Product Management

Admin dapat melakukan CRUD terhadap produk dan kategori serta memperbarui stok.

| Product | Category | Price | Stock | Action |
| :--- | :--- | :--- | :---: | :--- |
| Nugget | Nugget | Rp25.000 | 20 | Edit / Delete |
| Sosis | Sosis | Rp18.000 | 15 | Edit / Delete |
| Dimsum | Dimsum | Rp22.000 | 10 | Edit / Delete |

---

## 9. Shopping Cart

Customer dapat memasukkan produk atau bundle ke shopping cart. Quantity dapat diubah dan item dapat dihapus. Cart menggunakan **session**.

---

## 10. Checkout

Customer mengisi data yang diperlukan untuk membuat pesanan:
- Nama
- Nomor telepon
- Alamat
- Metode pembayaran

Setelah checkout, data `order` dan `order_items` disimpan ke database.

---

## 11. Order Management

| Order ID | Customer | Total | Status |
| :--- | :--- | :--- | :--- |
| `#ORD001` | Customer A | Rp72.000 | Pending |
| `#ORD002` | Customer B | Rp95.000 | Processing |
| `#ORD003` | Customer C | Rp60.000 | Completed |

**Alur Status Order:** `Pending` → `Processing` → `Ready` → `Completed`. Admin juga dapat membatalkan order (`Cancelled`) bila diperlukan.

---

## 12. Admin Dashboard

Dashboard menampilkan informasi ringkas seperti:
- Total Products
- Total Orders
- Today's Sales
- Low Stock Products
- Recent Orders
- Ringkasan penjualan

---

## 13. Main User Flow

### Customer Flow
```text
Landing Page → Browse Products → Smart Bundle → Input Requirement → Recommendation → Customize Bundle → Add to Cart → Checkout → Order Confirmation → Order History
```

### Admin Flow
```text
Login → Admin Dashboard → Manage Products → Manage Categories → Manage Stock → Manage Orders → Sales Summary
```

---

## 14. Functional Requirements

| ID | Requirement |
| :--- | :--- |
| **FR-01** | Customer dapat melihat daftar produk. |
| **FR-02** | Customer dapat melihat detail produk. |
| **FR-03** | Customer dapat mencari produk. |
| **FR-04** | Customer dapat memfilter produk. |
| **FR-05** | Customer dapat menggunakan Smart Bundle. |
| **FR-06** | Sistem dapat memberikan rekomendasi bundle. |
| **FR-07** | Customer dapat melakukan customize bundle. |
| **FR-08** | Customer dapat memasukkan produk ke cart. |
| **FR-09** | Customer dapat melakukan checkout. |
| **FR-10** | Customer dapat melihat riwayat pesanan. |
| **FR-11** | Admin dapat login. |
| **FR-12** | Admin dapat menambah produk. |
| **FR-13** | Admin dapat mengubah produk. |
| **FR-14** | Admin dapat menghapus produk. |
| **FR-15** | Admin dapat mengelola kategori. |
| **FR-16** | Admin dapat mengelola stok. |
| **FR-17** | Admin dapat melihat order. |
| **FR-18** | Admin dapat mengubah status order. |
| **FR-19** | Admin dapat melihat dashboard. |
| **FR-20** | Admin dapat melihat ringkasan penjualan. |

---

## 15. Non-Functional Requirements

| Category | Requirement |
| :--- | :--- |
| **Performance** | Operasi umum seperti membuka produk dan pencarian harus memberikan response yang wajar. |
| **Security** | Authentication, password security, CSRF protection, dan input validation. |
| **Usability** | Interface sederhana dan mudah dipahami oleh customer. |
| **Responsive** | Website dapat digunakan pada desktop, tablet, dan mobile. |

---

## 16. Database Overview

Tabel utama:
- `users`
- `categories`
- `products`
- `orders`
- `order_items`

Untuk MVP, Smart Bundle menggunakan data produk dan rule-based logic tanpa membuat struktur database yang terlalu kompleks.

---

## 17. Technology Stack

| Technology | Penggunaan |
| :--- | :--- |
| **Laravel** | Backend dan framework utama |
| **PHP** | Programming language |
| **MySQL** | Database |
| **Blade** | Frontend templating |
| **Bootstrap / CSS** | UI dan responsive layout |
| **HTML** | Struktur halaman |
| **JavaScript** | Interaksi sederhana |

---

## 18. Project Scope

### Must Have
- Authentication
- Product CRUD
- Category CRUD
- Product browsing
- Smart Bundle
- Customize Bundle
- Shopping Cart
- Checkout
- Order Management
- Stock Management
- Admin Dashboard

### Nice to Have
- Product reviews
- Wishlist
- Promo code
- Delivery tracking
- Sales chart
- Recommendation berdasarkan riwayat pembelian

### Out of Scope untuk MVP
- Online payment gateway
- Real-time delivery tracking
- AI recommendation
- Mobile application
- Complex warehouse management

---

## 19. Success Criteria

1. Customer dapat menemukan produk melalui katalog.
2. Customer dapat memasukkan kebutuhan ke Smart Bundle.
3. Sistem menghasilkan bundle sesuai parameter customer.
4. Customer dapat mengubah bundle.
5. Customer dapat checkout.
6. Order tersimpan di database.
7. Admin dapat mengelola produk dan stok.
8. Admin dapat mengelola order.
9. Admin dapat melihat ringkasan penjualan.
10. Fitur utama berjalan menggunakan Laravel.

---

## 20. Product Positioning

FrozeMate bukan hanya website untuk membeli frozen food, tetapi membantu pelanggan menentukan kombinasi produk yang sesuai dengan kebutuhan, jumlah orang, jumlah makanan, dan budget melalui fitur **Smart Frozen Food Bundle**.

---

## 21. Future Development

- Personalized recommendation berdasarkan riwayat pembelian
- Promo dan voucher
- Wishlist
- Review dan rating
- Integrasi payment gateway
- Analitik penjualan yang lebih lengkap

---

## 22. Kesesuaian dengan Materi Web Programming

| Materi | Penerapan pada FrozeMate |
| :--- | :--- |
| **Routing & View** | Routing halaman customer, admin, produk, bundle, cart, dan order. |
| **Blade Template** | Layout, navbar, halaman produk, dashboard, dan form. |
| **Migration & Eloquent ORM** | Database `products`, `categories`, `users`, `orders`, dan `order_items`. |
| **Form, Validation & CRUD** | Product CRUD, category CRUD, checkout, dan pengelolaan order. |
| **Collection** | Filtering, sorting, perhitungan, dan penyusunan rekomendasi bundle. |
| **Session & Middleware** | Shopping cart berbasis session dan pembatasan akses admin. |
| **Authentication** | Login dan akses berdasarkan user/admin. |
| **Localization** | Dukungan bahasa Indonesia (`lang/id`). |

---

## Cara Menjalankan Proyek (Local Setup)

1. **Clone repository & masuk ke direktori proyek:**
   ```bash
   git clone https://github.com/FredChopin12/frozemate.git
   cd frozemate
   ```
2. **Install dependency PHP:**
   ```bash
   composer install
   ```
3. **Salin konfigurasi environment & generate app key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Konfigurasi database di `.env`, lalu jalankan migrasi & seeder:**
   ```bash
   php artisan migrate --seed
   ```
5. **Jalankan server aplikasi:**
   ```bash
   php artisan serve
   ```
   *(Atau jalankan `start.bat` pada Windows jika menggunakan XAMPP).*

### Akun Demo (Hasil Seeder)
- **Admin:** `admin@frozemate.test` / password: `password`
- **Customer:** `dinda@frozemate.test` / password: `password`
