# White Box Testing — SIPUS

Dokumen ini mendokumentasikan White Box Testing pada business logic utama:
`validateLoan()`, `calculateFine()`, dan `returnBook()`, ditambah `checkBookAvailability()`.

File implementasi: `app/Services/LoanService.php`

---

## 1. calculateFine($returnDate, $dueDate)

### Flowchart (pseudocode)

```
START
  |
  v
[returnDate <= dueDate] --(Ya)--> [return 0] --> STOP
  |
 (Tidak)
  |
  v
[lateDays = |returnDate - dueDate| (hari)]
  |
  v
[return lateDays x 1000]
  |
  v
STOP
```

### Flow Graph

```
        (1,2) returnDate <= dueDate
         /     \
       Ya       Tidak
       /         \
     (3)         (4) lateDays = abs(diff)
  return 0        |
       \          v
        \       (5) return lateDays * 1000
         \        /
          \      /
           v    v
           (6) entity END
```

### Cyclomatic Complexity

- Jumlah decision predicate: 1
- `V(G) = P + 1 = 2`
- Perhitungan dengan rusuk dan simpul: `V(G) = E - N + 2 = 5 - 5 + 2 = 2`

### Independent Path

1. `returnDate <= dueDate` → return 0 (tidak terlambat)
2. `returnDate > dueDate` → hitung lateDays → return lateDays × 1000 (terlambat)

### Basis Path Test

| Path | Input | Hasil yang diharapkan |
|------|-------|------------------------|
| 1 | return 09-09-2026, due 09-09-2026 | 0 |
| 1 | return 08-09-2026, due 09-09-2026 | 0 |
| 2 | return 10-09-2026, due 09-09-2026 | 1.000 |
| 2 | return 12-09-2026, due 09-09-2026 | 3.000 |

---

## 2. checkBookAvailability($book, $quantity)

### Flowchart

```
START
  |
  v
[quantity > 0] --(Tidak)--> return false --> STOP
   |
 (Ya)
   v
[available_stock >= quantity] --(Ya)--> return true --> STOP
   |
 (Tidak)
   v
return false
   |
STOP
```

### Cyclomatic Complexity

- Jumlah predicate: 2 → `V(G) = P + 1 = 3`

### Independent Path

1. `quantity <= 0` → false
2. `quantity > 0` tetapi `available_stock < quantity` → false
3. `quantity > 0` dan `available_stock >= quantity` → true

### Basis Path Test

| Path | Stok tersedia | Quantity | Hasil |
|------|---------------|----------|-------|
| 1 | 5 | 0 | false |
| 1 | 5 | -1 | false |
| 2 | 2 | 3 | false |
| 3 | 5 | 5 | true |

---

## 3. validateLoan($member, $items)

### Flowchart

```
START
  |
  v
[member aktif?] --(Tidak)--> [errors += "anggota tidak aktif"]
   |
 (Ya)
   v
[items tidak kosong?] --(Tidak)--> [errors += "pilih minimal satu buku"]
   |
 (Ya)
   v
[ untuk setiap buku B di items ]
   |        |
   |        v
   |  [jumlah(B) > 0?] --(Tidak)--> [errors += "jumlah harus > 0"]
   |        |
   |       (Ya)
   |        v
   |  [stok tersedia(B) > 0?] --(Tidak)--> [errors += "buku tidak tersedia"]
   |        |
   |       (Ya)
   |        v
   |  [jumlah(B) <= stok tersedia(B)?] --(Tidak)--> [errors += "melebihi stok"]
   |        |
   |       (Ya)
   |        +-------> lanjut buku berikutnya
   |
   v
[errors kosong?] --(Ya)--> valid = true
   |
 (Tidak)
   v
valid = false
   |
STOP
```

### Flow Graph / Predicate

- Predicate member aktif: 1
- Predicate items kosong: 1
- Predicate per buku: 3 (jumlah > 0, stok > 0, jumlah <= stok)
- Untuk 1 kelompok buku: `P = 2 + 3 = 5` → `V(G) = P + 1 = 6`

### Independent Path

1. Anggota aktif + items ada + semua aturan buku terpenuhi → valid
2. Anggota tidak aktif → error anggota tidak aktif
3. Items kosong → error pilih minimal satu buku
4. Jumlah buku <= 0 → error jumlah harus > 0
5. Stok tersedia buku == 0 → error buku tidak tersedia
6. Jumlah buku > stok tersedia → error melebihi stok

### Basis Path Test

| Path | Member | Buku (stok) | Qty | Hasil |
|------|--------|-------------|-----|-------|
| 1 | aktif | tersedia (5) | 2 | valid |
| 2 | tidak aktif | tersedia (5) | 1 | tidak valid |
| 3 | aktif | - (items kosong) | - | tidak valid |
| 4 | aktif | tersedia (5) | 0 | tidak valid |
| 5 | aktif | habis (0) | 1 | tidak valid |
| 6 | aktif | tersedia (2) | 3 | tidak valid |

---

## 4. returnBook($loan, $returnDate)

### Flowchart

```
START
  |
  v
[status == "dikembalikan"?] --(Ya)--> return [success=false, "sudah dikembalikan"] --> STOP
   |
 (Tidak)
   v
[fine = calculateFine(returnDate, due_date)]
  |
  v
[fine > 0?] --(Ya)--> [lateDays = jumlah hari terlambat]
   |                  |
 (Tidak)              v
   |              [lateDays = 0]
   v                  |
[transaction: kembalikan stok semua item, simpan return_date,
 status = "dikembalikan", total_fine = fine]
  |
  v
return [success=true, lateDays, fine]
  |
STOP
```

### Cyclomatic Complexity

- Predicate 1: `status == dikembalikan`
- Predicate 2: `fine > 0`
- `V(G) = P + 1 = 3`

### Independent Path

1. Transaksi sudah dikembalikan → ditolak (stok tidak berubah)
2. Pengembalian tepat waktu/lebih awal → fine = 0, lateDays = 0
3. Pengembalian terlambat → fine = lateDays × 1000, lateDays > 0

### Basis Path Test

| Path | Skenario | Hasil |
|------|----------|-------|
| 1 | Kembalikan transaksi yang sudah berstatus "dikembalikan" | success = false, stok tidak berubah |
| 2 | Pinjam 02-09-2026, due 09-09-2026, kembali 09-09-2026 | lateDays = 0, fine = 0, stok kembali |
| 3 | Pinjam 02-09-2026, due 09-09-2026, kembali 12-09-2026 | lateDays = 3, fine = 3.000, stok kembali |

---

## Ringkasan Cyclomatic Complexity

| Fungsi | Predicate (P) | V(G) |
|--------|---------------|------|
| calculateFine() | 1 | 2 |
| checkBookAvailability() | 2 | 3 |
| validateLoan() (1 buku) | 5 | 6 |
| returnBook() | 2 | 3 |

Catatan: Semua fungsi dijaga sederhana (V(G) rendah) agar mudah diuji dan dirawat.

## Implementasi Otomatis

Seluruh Basis Path Test di atas diimplementasikan sebagai automated test pada:
`tests/Unit/LoanServiceTest.php` (White Box) dan `tests/Feature/LoanFlowTest.php`,
`tests/Feature/MemberTest.php`, `tests/Feature/BookTest.php` (Black Box).

Jalankan: `php artisan test`