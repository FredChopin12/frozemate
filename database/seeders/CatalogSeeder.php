<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // [nama, deskripsi, tone, art]
        $categories = [
            'nugget' => ['Nugget', 'Renyah di luar, lembut di dalam.', 'amber', 'nugget'],
            'sosis' => ['Sosis', 'Tinggal bakar, goreng, atau tumis.', 'red', 'sosis'],
            'dimsum' => ['Dimsum', 'Kukus 10 menit, langsung santap.', 'jade', 'dimsum'],
            'bakso' => ['Bakso & Olahan Ikan', 'Teman kuah, suki, dan tumisan.', 'blue', 'bakso'],
            'kentang' => ['Kentang & Camilan', 'Camilan goreng buat ramai-ramai.', 'yellow', 'kentang'],
            'ayam' => ['Ayam Siap Masak', 'Lauk utama tanpa repot bumbu.', 'orange', 'ayam'],
            'kudapan' => ['Kudapan Manis & Gurih', 'Isi toples dan piring suguhan.', 'plum', 'kudapan'],
        ];

        $ids = [];

        foreach ($categories as $key => [$name, $description, $tone, $art]) {
            $ids[$key] = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                compact('name', 'description', 'tone', 'art') + [
                    'is_side' => in_array($key, ['kentang', 'kudapan'], true),
                ],
            )->id;
        }

        // [kategori, nama, harga, stok, gram, porsi, cara masak, tags, unggulan, deskripsi]
        $products = [
            ['nugget', 'Nugget Ayam Original', 25000, 20, 250, 3, 'Goreng 3 menit', ['kos', 'bekal', 'keluarga'], true,
                'Nugget ayam klasik dengan daging dada pilihan. Bumbunya ringan, cocok buat lauk harian atau isi bekal.'],
            ['nugget', 'Nugget Ayam Keju', 29000, 14, 250, 3, 'Goreng 3 menit', ['bekal', 'kos'], false,
                'Nugget ayam dengan lelehan keju di tengah. Favorit anak-anak untuk bekal sekolah.'],
            ['nugget', 'Nugget Stik Sayur', 27000, 12, 250, 3, 'Goreng 4 menit', ['bekal', 'keluarga', 'mealprep'], false,
                'Stik nugget dengan wortel dan jagung. Cara halus menyelipkan sayur ke menu si kecil.'],
            ['nugget', 'Nugget Ayam Family Pack', 85000, 8, 1000, 12, 'Goreng 3 menit', ['keluarga', 'acara'], false,
                'Kemasan 1 kg untuk stok serumah. Lebih hemat per porsi dibanding kemasan kecil.'],

            ['sosis', 'Sosis Ayam Mini', 18000, 15, 250, 3, 'Goreng 2 menit', ['kos', 'bekal'], true,
                'Sosis ayam ukuran sekali gigit. Enak digoreng, ditumis saus, atau jadi topping nasi goreng.'],
            ['sosis', 'Sosis Sapi Jumbo', 38000, 10, 500, 5, 'Bakar 6 menit', ['keluarga', 'acara', 'kos'], false,
                'Sosis sapi besar isi 10. Pas untuk sosis bakar atau isian roti.'],
            ['sosis', 'Bratwurst Lada Hitam', 42000, 4, 300, 3, 'Panggang 8 menit', ['acara', 'mealprep'], false,
                'Bratwurst bertekstur kasar dengan lada hitam. Tinggal panggang untuk barbeque kecil-kecilan.'],

            ['dimsum', 'Dimsum Ayam', 22000, 10, 200, 2, 'Kukus 10 menit', ['kos', 'bekal', 'keluarga'], true,
                'Siomay ayam isi 10 dengan kulit tipis. Kukus sebentar, cocol saus sambal.'],
            ['dimsum', 'Dimsum Udang', 28000, 9, 200, 2, 'Kukus 10 menit', ['keluarga', 'acara'], false,
                'Hakau udang isi 10 dengan potongan udang utuh di dalamnya.'],
            ['dimsum', 'Siomay Ikan Tenggiri', 26000, 11, 300, 3, 'Kukus 12 menit', ['keluarga', 'kos'], false,
                'Siomay ikan tenggiri ala Bandung isi 12, sudah termasuk bumbu kacang.'],
            ['dimsum', 'Dimsum Mix Party', 65000, 5, 600, 7, 'Kukus 12 menit', ['acara', 'keluarga'], false,
                'Tiga puluh dimsum campur: ayam, udang, dan lumpia kulit tahu. Satu kotak untuk satu meja.'],

            ['bakso', 'Fish Ball', 16000, 18, 250, 3, 'Rebus 4 menit', ['kos', 'mealprep', 'bekal'], false,
                'Bakso ikan kenyal untuk kuah, suki, atau tusuk bakar.'],
            ['bakso', 'Bakso Sapi Halus', 32000, 13, 500, 5, 'Rebus 5 menit', ['keluarga', 'kos', 'mealprep'], true,
                'Bakso sapi halus isi 25. Stok aman untuk kuah bakso dadakan.'],
            ['bakso', 'Chikuwa', 19000, 7, 250, 3, 'Goreng 3 menit', ['kos', 'mealprep'], false,
                'Olahan ikan berbentuk tabung. Enak digoreng tepung atau masuk kuah.'],
            ['bakso', 'Crab Stick', 21000, 3, 250, 3, 'Tanpa masak', ['bekal', 'mealprep', 'acara'], false,
                'Stik rasa kepiting untuk salad, sushi rumahan, atau isian onigiri.'],
            ['bakso', 'Otak-otak Ikan', 20000, 12, 250, 3, 'Goreng 4 menit', ['kos', 'acara'], false,
                'Otak-otak ikan isi 10, gurih dengan aroma daun bawang.'],

            ['kentang', 'Kentang Goreng Shoestring', 19000, 22, 500, 4, 'Goreng 4 menit', ['kos', 'keluarga', 'acara', 'bekal'], true,
                'Kentang potong tipis panjang. Renyah lebih lama, tinggal tabur garam.'],
            ['kentang', 'Kentang Wedges Berbumbu', 26000, 9, 500, 4, 'Air fryer 12 menit', ['acara', 'keluarga'], false,
                'Wedges berkulit dengan bumbu paprika. Paling enak di air fryer.'],
            ['kentang', 'Hash Brown', 30000, 6, 600, 5, 'Goreng 5 menit', ['bekal', 'keluarga', 'mealprep'], false,
                'Hash brown isi 10. Sarapan cepat atau pendamping telur.'],
            ['kentang', 'Cireng Rujak', 15000, 16, 400, 4, 'Goreng 5 menit', ['kos', 'acara'], false,
                'Cireng isi 20 lengkap dengan bumbu rujak pedas manis.'],

            ['ayam', 'Chicken Karaage', 39000, 10, 400, 4, 'Goreng 5 menit', ['mealprep', 'keluarga', 'bekal'], true,
                'Ayam goreng ala Jepang dengan marinasi jahe dan kecap asin.'],
            ['ayam', 'Chicken Katsu', 36000, 12, 500, 5, 'Goreng 5 menit', ['mealprep', 'bekal', 'kos'], false,
                'Lima lembar fillet ayam berbalut tepung roti. Satu lembar, satu porsi.'],
            ['ayam', 'Spicy Wings', 45000, 7, 500, 4, 'Air fryer 15 menit', ['acara', 'keluarga'], false,
                'Sayap ayam berbumbu pedas. Camilan nonton bola yang cepat habis.'],
            ['ayam', 'Ayam Ungkep Bumbu Kuning', 58000, 5, 900, 6, 'Goreng 6 menit', ['keluarga', 'mealprep'], false,
                'Satu ekor ayam potong 8 yang sudah diungkep. Tinggal goreng sampai kecokelatan.'],

            ['kudapan', 'Risoles Mayo', 24000, 10, 400, 5, 'Goreng 4 menit', ['acara', 'bekal'], true,
                'Risoles isi smoked beef, telur, dan mayones. Isi 10.'],
            ['kudapan', 'Pisang Cokelat', 18000, 14, 350, 5, 'Goreng 3 menit', ['acara', 'kos', 'bekal'], false,
                'Piscok isi 10 dengan cokelat yang meleleh saat digigit.'],
            ['kudapan', 'Roti Maryam', 14000, 0, 400, 5, 'Panggang 3 menit', ['kos', 'keluarga'], false,
                'Roti canai isi 5. Makan polos, pakai kari, atau tabur gula.'],
        ];

        foreach ($products as [$category, $name, $price, $stock, $grams, $servings, $cook, $tags, $featured, $description]) {
            Product::updateOrCreate(['slug' => Str::slug($name)], [
                'category_id' => $ids[$category],
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'weight_grams' => $grams,
                'servings' => $servings,
                'cook_method' => $cook,
                'tags' => $tags,
                'is_active' => true,
                'is_featured' => $featured,
            ]);
        }
    }
}
