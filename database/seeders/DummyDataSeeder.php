<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Seed Users & Profiles
        $adminId = Str::uuid()->toString();
        DB::table('users')->insert([
            'id' => $adminId,
            'name' => 'Admin Avenir',
            'email' => 'admin@avenir.test',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_profiles')->insert([
            'user_id' => $adminId,
            'first_name' => 'Admin',
            'last_name' => 'Avenir',
            'phone_number' => '08123456789',
            'is_subscriber' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Seed Tickers
        DB::table('tickers')->insert([
            [
                'symbol' => 'BBRI',
                'company_name' => 'Bank Rakyat Indonesia',
                'sector' => 'Financials',
                'description' => 'Salah satu bank BUMN terbesar di Indonesia yang fokus pada pembiayaan UMKM.',
                'current_price' => 4500.00,
                'target_price' => 6000.00,
                'recommendation' => 'bullish',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'symbol' => 'DMAS',
                'company_name' => 'Puradelta Lestari Tbk',
                'sector' => 'Property & Real Estate',
                'description' => 'Pengembang kawasan industri Kota Deltamas.',
                'current_price' => 160.00,
                'target_price' => 200.00,
                'recommendation' => 'bullish',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Seed Articles (based on old files like artikel-private-credit-bubble-risk.html)
        DB::table('articles')->insert([
            [
                'title' => 'BBRI: Bedah Orderbook Transaksi Institusi',
                'slug' => Str::slug('BBRI Bedah Orderbook Transaksi Institusi'),
                'category' => 'emiten',
                'badge' => 'DEEP DIVE',
                'excerpt' => 'Analisis mendalam mengenai akumulasi asing pada saham BBRI pasca koreksi.',
                'content' => '<p>Bank Rakyat Indonesia (BBRI) mengalami fase tekanan jual asing dalam dua bulan terakhir. Namun data orderbook menunjukkan adanya akumulasi institusi di level support kuat 4.200...</p><h2>Thesis Investasi</h2><p>Kami melihat ada potensi upside 30% dalam 12 bulan ke depan mengingat yield dividen yang mencapai 7%.</p>',
                'author' => 'Tim Avenir Research',
                'published_at' => now(),
                'is_paid' => true,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Private Credit Bubble Risk 2026',
                'slug' => Str::slug('Private Credit Bubble Risk 2026'),
                'category' => 'makro',
                'badge' => 'MACRO OUTLOOK',
                'excerpt' => 'Apakah ledakan private credit global akan memicu krisis finansial berikutnya?',
                'content' => '<p>Ekspansi massive pasar <em>private credit</em> yang kini mencapai triliunan dolar mengingatkan pada siklus subprime mortgage...</p><h2>Dampak ke IHSG</h2><p>Pengetatan likuiditas global akan berdampak pada yield SBN dan akhirnya menekan valuasi saham perbankan domestik.</p>',
                'author' => 'Tim Avenir Research',
                'published_at' => now()->subDays(2),
                'is_paid' => false,
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
