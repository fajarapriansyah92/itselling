<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Katalog produk contoh untuk perusahaan IT konsultan.
     *
     * @var array<string, array{name: string, description: string, products: list<array{0: string, 1: ProductType, 2: ?string, 3: string, 4: int, 5: ?int, 6: string}>}>
     */
    private const CATALOG = [
        'jasa-it' => [
            'name' => 'Jasa Konfigurasi & Instalasi',
            'description' => 'Layanan instalasi, konfigurasi, dan pemeliharaan infrastruktur IT.',
            'products' => [
                // [nama, tipe, merek, satuan, harga, stok, deskripsi singkat]
                ['Instalasi Jaringan LAN & Wi-Fi Kantor', ProductType::Service, null, 'paket', 3500000, null, 'Penarikan kabel, setup switch, access point, dan dokumentasi jaringan hingga 20 titik.'],
                ['Konfigurasi Router MikroTik & Hotspot', ProductType::Service, null, 'paket', 1500000, null, 'Konfigurasi routing, firewall, manajemen bandwidth, dan hotspot voucher.'],
                ['Instalasi Windows Server & Active Directory', ProductType::Service, null, 'paket', 4500000, null, 'Setup domain controller, user & group policy, serta file sharing terpusat.'],
                ['Instalasi CCTV IP Camera 4 Titik', ProductType::Service, null, 'paket', 2750000, null, 'Pemasangan 4 kamera IP, NVR, dan akses pantau melalui ponsel.'],
                ['Maintenance IT Bulanan', ProductType::Service, null, 'bulan', 5000000, null, 'Kunjungan rutin, pengecekan server & jaringan, serta dukungan helpdesk.'],
                ['Audit Keamanan Jaringan', ProductType::Service, null, 'paket', 12000000, null, 'Vulnerability assessment jaringan dan server beserta laporan rekomendasi.'],
            ],
        ],
        'hardware-jaringan' => [
            'name' => 'Hardware Jaringan',
            'description' => 'Router, switch, access point, dan firewall untuk infrastruktur kantor.',
            'products' => [
                ['MikroTik hEX RB750Gr3', ProductType::Hardware, 'MikroTik', 'unit', 1150000, 25, 'Router 5 port gigabit untuk kantor kecil dan menengah.'],
                ['TP-Link Omada EAP225 Access Point', ProductType::Hardware, 'TP-Link', 'unit', 1050000, 30, 'Access point dual band AC1350 dengan manajemen terpusat Omada.'],
                ['Cisco Catalyst 1000 24-Port Switch', ProductType::Hardware, 'Cisco', 'unit', 9800000, 8, 'Managed switch 24 port gigabit dengan 4 uplink SFP.'],
                ['Fortinet FortiGate 40F Firewall', ProductType::Hardware, 'Fortinet', 'unit', 11500000, 6, 'Next-generation firewall untuk proteksi jaringan kantor cabang.'],
            ],
        ],
        'hardware-komputer' => [
            'name' => 'Hardware Komputer & Server',
            'description' => 'Laptop, server, penyimpanan, dan perangkat pendukung.',
            'products' => [
                ['Dell PowerEdge T150 Server', ProductType::Hardware, 'Dell', 'unit', 28500000, 4, 'Server tower Intel Xeon E-2314, RAM 16GB, untuk kebutuhan bisnis kecil.'],
                ['Lenovo ThinkPad E14 Gen 5', ProductType::Hardware, 'Lenovo', 'unit', 12999000, 12, 'Laptop bisnis Intel Core i5, RAM 16GB, SSD 512GB.'],
                ['Synology DiskStation DS224+', ProductType::Hardware, 'Synology', 'unit', 7200000, 7, 'NAS 2-bay untuk backup data dan file server kantor.'],
                ['APC Back-UPS 1200VA', ProductType::Hardware, 'APC', 'unit', 2100000, 15, 'UPS untuk melindungi server dan perangkat jaringan dari listrik padam.'],
            ],
        ],
        'software-keamanan' => [
            'name' => 'Software Keamanan',
            'description' => 'Lisensi antivirus dan endpoint security untuk bisnis.',
            'products' => [
                ['Kaspersky Endpoint Security for Business', ProductType::Software, 'Kaspersky', 'lisensi', 450000, 200, 'Proteksi endpoint 1 tahun per perangkat dengan konsol manajemen terpusat.'],
                ['ESET PROTECT Entry', ProductType::Software, 'ESET', 'lisensi', 380000, 200, 'Antivirus bisnis ringan dengan manajemen cloud, 1 tahun per perangkat.'],
                ['Bitdefender GravityZone Business Security', ProductType::Software, 'Bitdefender', 'lisensi', 420000, 150, 'Perlindungan anti-malware dan anti-ransomware 1 tahun per perangkat.'],
            ],
        ],
        'software-lisensi' => [
            'name' => 'Software Produktivitas',
            'description' => 'Lisensi sistem operasi dan aplikasi perkantoran resmi.',
            'products' => [
                ['Microsoft 365 Business Standard', ProductType::Software, 'Microsoft', 'lisensi', 2100000, 100, 'Office apps, email bisnis, dan Teams untuk 1 pengguna selama 1 tahun.'],
                ['Windows 11 Pro OEM', ProductType::Software, 'Microsoft', 'lisensi', 2650000, 50, 'Lisensi resmi Windows 11 Professional untuk 1 perangkat.'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skuNumber = 1;

        foreach (self::CATALOG as $slug => $categoryData) {
            $category = Category::create([
                'name' => $categoryData['name'],
                'slug' => $slug,
                'description' => $categoryData['description'],
            ]);

            foreach ($categoryData['products'] as [$name, $type, $brand, $unit, $price, $stock, $shortDescription]) {
                $category->products()->create([
                    'sku' => sprintf('ITS-%03d', $skuNumber++),
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'type' => $type,
                    'brand' => $brand,
                    'unit' => $unit,
                    'short_description' => $shortDescription,
                    'description' => $shortDescription."\n\nHarga sudah termasuk garansi resmi dan konsultasi teknis dari tim kami. Hubungi kami untuk kebutuhan dalam jumlah besar.",
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ]);
            }
        }
    }
}
