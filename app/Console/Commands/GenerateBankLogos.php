<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateBankLogos extends Command
{
    protected $signature = 'logos:generate';
    protected $description = 'Generate placeholder SVG logos for all banks';

    public function handle()
    {
        $banks = [
            "Alliance Finance Co PLC" => "AFC",
            "Amana Bank" => "AB",
            "American Express Bank Ltd" => "AX",
            "Asia Asset Finance PLC" => "AAF",
            "Bank of China" => "BOC",
            "CDB" => "CDB",
            "Cargils Bank Ltd" => "CB",
            "Central Bank of Sri Lanka" => "CBL",
            "Central Finance PLC" => "CF",
            "City Bank" => "CB",
            "Commercial Credit" => "CC",
            "Cooperative Regional Rural Bank LTD" => "CRB",
            "Deutsche Bank" => "DB",
            "Dialog Finance PLC" => "DF",
            "Fintrex Finance Limited" => "FF",
            "HDFC Bank" => "HDFC",
            "HNB Finance PLC" => "HNB",
            "Indian Bank" => "IB",
            "Indian Overseas Bank" => "IOB",
            "Kanrich Finance Bank" => "KF",
            "LB Finance" => "LBF",
            "LOLC Development Finance Plc" => "LOLC",
            "LOLC Finance Plc" => "LOLC",
            "Lanka Credit and Business Finance Limited" => "LCBF",
            "MBSL" => "MBSL",
            "MCB" => "MCB",
            "Mercantile Investment" => "MI",
            "NSB" => "NSB",
            "Peoples Leasing and Finance PLC" => "PLF",
            "Pan Asia Bank" => "PAB",
            "Public Bank Berhad" => "PB",
            "RDB" => "RDB",
            "Richard Pieris Finance" => "RPF",
            "SDB" => "SDB",
            "SENKADAGALA FINANCE" => "SF",
            "SMIB" => "SMIB",
            "Sarvodaya Development Finace LTD" => "SDF",
            "Singer Finance(Lanka) Bank" => "SF",
            "Siyapatha Finance PLC" => "SP",
            "Softlogic Finance PLC" => "SLF",
            "State Bank of India" => "SBI",
            "Union Bank" => "UB",
        ];

        $colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E2', '#F38181', '#AA96DA'];
        $logosDir = public_path('images/banks');

        if (!is_dir($logosDir)) {
            mkdir($logosDir, 0755, true);
        }

        $logoMap = [
            "Alliance Finance Co PLC" => "alliance_finance_logo.svg",
            "Amana Bank" => "amana_bank_logo.svg",
            "American Express Bank Ltd" => "amex_bank_logo.svg",
            "Asia Asset Finance PLC" => "asia_asset_finance_logo.svg",
            "Bank of China" => "bank_of_china_logo.svg",
            "CDB" => "cdb_logo.svg",
            "Cargils Bank Ltd" => "cargils_bank_logo.svg",
            "Central Bank of Sri Lanka" => "central_bank_logo.svg",
            "Central Finance PLC" => "central_finance_logo.svg",
            "City Bank" => "city_bank_logo.svg",
            "Commercial Credit" => "commercial_credit_logo.svg",
            "Cooperative Regional Rural Bank LTD" => "cooperative_rural_bank_logo.svg",
            "Deutsche Bank" => "deutsche_bank_logo.svg",
            "Dialog Finance PLC" => "dialog_finance_logo.svg",
            "Fintrex Finance Limited" => "fintrex_finance_logo.svg",
            "HDFC Bank" => "hdfc_bank_logo.svg",
            "HNB Finance PLC" => "hnb_finance_logo.svg",
            "Indian Bank" => "indian_bank_logo.svg",
            "Indian Overseas Bank" => "indian_overseas_bank_logo.svg",
            "Kanrich Finance Bank" => "kanrich_finance_logo.svg",
            "LB Finance" => "lb_finance_logo.svg",
            "LOLC Development Finance Plc" => "lolc_dev_finance_logo.svg",
            "LOLC Finance Plc" => "lolc_finance_logo.svg",
            "Lanka Credit and Business Finance Limited" => "lanka_credit_business_finance_logo.svg",
            "MBSL" => "mbsl_logo.svg",
            "MCB" => "mcb_logo.svg",
            "Mercantile Investment" => "mercantile_investment_logo.svg",
            "NSB" => "nsb_logo.svg",
            "Peoples Leasing and Finance PLC" => "peoples_leasing_finance_logo.svg",
            "Pan Asia Bank" => "pan_asia_bank_logo.svg",
            "Public Bank Berhad" => "public_bank_logo.svg",
            "RDB" => "rdb_logo.svg",
            "Richard Pieris Finance" => "richard_pieris_finance_logo.svg",
            "SDB" => "sdb_logo.svg",
            "SENKADAGALA FINANCE" => "senkadagala_finance_logo.svg",
            "SMIB" => "smib_logo.svg",
            "Sarvodaya Development Finace LTD" => "sarvodaya_dev_finance_logo.svg",
            "Singer Finance(Lanka) Bank" => "singer_finance_logo.svg",
            "Siyapatha Finance PLC" => "siyapatha_finance_logo.svg",
            "Softlogic Finance PLC" => "softlogic_finance_logo.svg",
            "State Bank of India" => "state_bank_india_logo.svg",
            "Union Bank" => "union_bank_logo.svg",
        ];

        $created = 0;
        $skipped = 0;

        foreach ($logoMap as $bankName => $filename) {
            $filepath = $logosDir . '/' . $filename;

            if (file_exists($filepath)) {
                $this->info("Skipped: $filename (already exists)");
                $skipped++;
                continue;
            }

            $colorIndex = abs(crc32($bankName)) % count($colors);
            $color = $colors[$colorIndex];
            $initials = $banks[$bankName] ?? substr($bankName, 0, 3);

            $svg = $this->generateSvg($bankName, $initials, $color);
            file_put_contents($filepath, $svg);

            $this->info("Created: $filename");
            $created++;
        }

        $this->newLine();
        $this->info("✓ Bank logos generation complete!");
        $this->info("  Created: $created logos");
        $this->info("  Skipped: $skipped logos (already exist)");
    }

    private function generateSvg($bankName, $initials, $color)
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$color};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$this->adjustBrightness($color, -20)};stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="200" height="200" fill="url(#grad)"/>
    <circle cx="100" cy="100" r="80" fill="none" stroke="white" stroke-width="2" opacity="0.3"/>
    <text x="100" y="120" font-size="48" font-weight="bold" fill="white" text-anchor="middle" font-family="Arial, sans-serif">{$initials}</text>
    <text x="100" y="160" font-size="11" fill="white" text-anchor="middle" font-family="Arial, sans-serif" opacity="0.8">{$bankName}</text>
</svg>
SVG;
    }

    private function adjustBrightness($color, $adjustment)
    {
        // Adjust hex color brightness
        $hex = ltrim($color, '#');
        $rgb = [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];

        foreach ($rgb as &$value) {
            $value = max(0, min(255, $value + $adjustment));
        }

        return '#' . sprintf('%02x%02x%02x', ...$rgb);
    }
}
