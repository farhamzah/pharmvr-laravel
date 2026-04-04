# PharmVR News Module API Specification

Modul News menyediakan informasi terkini seputar industri farmasi, regulasi, dan riset kesehatan yang relevan bagi pengguna PharmVR.

## 1. Route Definition
- **List News**: `GET /api/v1/news`
- **Detail News**: `GET /api/v1/news/{slug}`
- **Auth**: `Bearer Token (Sanctum)`

---

## 2. Query Parameters (Filtering & Search)

| Parameter | Type | Description |
| :--- | :--- | :--- |
| `category` | string | Filter berdasarkan kategori (e.g., `Industry`, `Regulation`, `Research`). |
| `search` | string | Mencari di judul, ringkasan, atau isi berita. |
| `is_featured` | boolean | Jika `true`, hanya menampilkan berita unggulan (untuk Hero/Banner). |
| `per_page` | integer | Jumlah item per halaman (default: 10). |

---

## 3. Response Contract (Success Example)

### List Response (`GET /api/v1/news`)
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Perkembangan Teknologi CPOB 4.0",
            "slug": "perkembangan-teknologi-cpob-4-0",
            "summary": "Implementasi Industri 4.0 dalam standar CPOB...",
            "image_url": "https://api.pharmvr.pro/storage/news/1.jpg",
            "category": "Industry",
            "is_featured": true,
            "published_at": "2026-03-11T04:40:00Z"
        }
    ],
    "links": { ... },
    "meta": { ... }
}
```

### Detail Response (`GET /api/v1/news/{slug}`)
Menampilkan field `content` lengkap untuk pembacaan artikel.

---

## 4. Frontend Integration Notes
1. **News Tab**: Gunakan pagination untuk memuat berita lama saat user scroll ke bawah.
2. **Home Preview**: Gunakan `?is_featured=1&per_page=2` untuk mendapatkan berita pilihan yang akan ditampilkan di Home Screen.
3. **Date Formatting**: Gunakan field `published_at` (ISO8601) untuk formatting tanggal lokal di Flutter.
4. **Search**: Implementasikan real-time search dengan *debounce* pada textfield pencarian.

---

## 5. Testing Checklist
- [ ] **Search Logic**: Memastikan kata kunci dalam isi berita (content) juga terdeteksi.
- [ ] **Featured Toggle**: Verifikasi `?is_featured=1` hanya mengembalikan berita dengan flag `is_featured` aktif.
- [ ] **Slug Detail**: Memastikan hit ke `/news/{slug}` mengembalikan detail lengkap berita tersebut.
- [ ] **Auth Protected**: Memastikan endpoint tidak dapat diakses tanpa login.
