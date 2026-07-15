<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrokerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brokers = [
            "AD" => "Sukadana Prima Sekuritas", "AF" => "Harita Kencana Sekuritas", "AG" => "Kiwoom Sekuritas Indonesia", "AH" => "Shinhan Sekuritas Indonesia", "AI" => "Kay Hian Sekuritas", "AK" => "UBS Sekuritas Indonesia", "AO" => "ERDIKHA ELIT SEKURITAS", "AP" => "Pacific Sekuritas Indonesia", "AR" => "Binaartha Sekuritas", "AT" => "Phintraco Sekuritas", "AZ" => "Sucor Sekuritas", "BB" => "Verdhana Sekuritas Indonesia", "BF" => "Inti Fikasa Sekuritas", "BK" => "J.P. Morgan Sekuritas Indonesia", "BQ" => "Korea Investment and Sekuritas Indonesia", "BR" => "Trust Sekuritas", "BS" => "Equity Sekuritas Indonesia", "CC" => "MANDIRI SEKURITAS", "CD" => "Mega Capital Sekuritas", "CP" => "KB Valbury Sekuritas", "DD" => "Makindo Sekuritas", "DH" => "SINARMAS SEKURITAS", "DP" => "DBS Vickers Sekuritas Indonesia", "DR" => "RHB Sekuritas Indonesia", "DU" => "KAF Sekuritas Indonesia", "DX" => "Bahana Sekuritas", "EL" => "Evergreen Sekuritas Indonesia", "EP" => "MNC Sekuritas", "ES" => "EKOKAPITAL SEKURITAS", "FO" => "Forte Global Sekuritas", "FS" => "Yuanta Sekuritas Indonesia", "FZ" => "Waterfront Sekuritas Indonesia", "GA" => "BNC Sekuritas Indonesia", "GI" => "Webull Sekuritas Indonesia", "GR" => "PANIN SEKURITAS Tbk.", "HD" => "KGI Sekuritas Indonesia", "HP" => "Henan Putihrai Sekuritas", "IC" => "Integrity Capital Sekuritas", "ID" => "Anugerah Sekuritas Indonesia", "IF" => "SAMUEL SEKURITAS INDONESIA", "IH" => "Indo Harvest Sekuritas", "II" => "Danatama Makmur Sekuritas", "IN" => "INVESTINDO NUSANTARA SEKURITA", "IT" => "INTI TELADAN SEKURITAS", "IU" => "Indo Capital Sekuritas", "KI" => "Ciptadana Sekuritas Asia", "KK" => "Phillip Sekuritas Indonesia", "KZ" => "CLSA Sekuritas Indonesia", "LG" => "Trimegah Sekuritas Indonesia Tbk.", "LS" => "Reliance Sekuritas Indonesia Tbk.", "MG" => "Semesta Indovest Sekuritas", "MI" => "Victoria Sekuritas Indonesia", "MU" => "Minna Padi Investama Sekuritas", "NI" => "BNI Sekuritas", "OD" => "BRI Danareksa Sekuritas", "OK" => "NET SEKURITAS", "PC" => "FAC Sekuritas Indonesia", "PD" => "Indo Premier Sekuritas", "PF" => "Danasakti Sekuritas Indonesia", "PG" => "Panca Global Sekuritas", "PI" => "Magenta Kapital Sekuritas Indonesia", "PO" => "Pilarmas Investindo Sekuritas", "PP" => "Aldiracita Sekuritas Indonesia", "QA" => "Tuntun Sekuritas Indonesia", "RB" => "Ina Sekuritas Indonesia", "RF" => "Buana Capital Sekuritas", "RG" => "Profindo Sekuritas Indonesia", "RO" => "Pluang Maju Sekuritas", "RS" => "Yulie Sekuritas Indonesia Tbk.", "RX" => "Macquarie Sekuritas Indonesia", "SA" => "Elit Sukses Sekuritas", "SF" => "Surya Fajar Sekuritas", "SH" => "Artha Sekuritas Indonesia", "SQ" => "BCA Sekuritas", "SS" => "Supra Sekuritas Indonesia", "TF" => "Laba Sekuritas Indonesia", "TP" => "OCBC Sekuritas Indonesia", "TS" => "Dwidana Sakti Sekuritas", "XA" => "NH Korindo Sekuritas Indonesia", "XC" => "Ajaib Sekuritas Asia", "XL" => "Stockbit Sekuritas Digital", "YB" => "Yakin Bertumbuh Sekuritas", "YJ" => "Lotus Andalan Sekuritas", "YO" => "Amantara Sekuritas Indonesia", "YP" => "Mirae Asset Sekuritas Indonesia", "YU" => "CGS International Sekuritas Indonesia", "ZP" => "Maybank Sekuritas Indonesia", "ZR" => "Bumiputera Sekuritas"
        ];

        foreach ($brokers as $code => $name) {
            \App\Models\Broker::updateOrCreate(
                ['code' => $code],
                ['name' => $name]
            );
        }
    }
}
