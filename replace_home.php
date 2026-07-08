<?php
$file = 'app/Http/Controllers/HomeController.php';
$contents = file_get_contents($file);

$newMethod = <<<METHOD
    /**
     * Download Katalog PDF with Watermark
     */
    public function downloadKatalogPdf(\$id)
    {
        \$research = \App\Models\Research::findOrFail(\$id);

        \$isSubscriber = false;
        \$isUnlocked = false;

        if (auth()->check()) {
            \$profile = \Illuminate\Support\Facades\DB::table('user_profiles')->where('user_id', auth()->id())->first();
            if (\$profile && \$profile->is_subscriber && \$profile->subscription_ends_at && \Carbon\Carbon::parse(\$profile->subscription_ends_at)->isFuture()) {
                \$isSubscriber = true;
            }
            \$hasUnlocked = \Illuminate\Support\Facades\DB::table('unlocked_researches')
                ->where('user_id', auth()->id())
                ->where('research_id', \$research->id)
                ->exists();
            if (\$hasUnlocked) {
                \$isUnlocked = true;
            }
        }

        if (\$research->is_paid && !\$isSubscriber && !\$isUnlocked) {
            abort(403, 'Anda belum memiliki akses untuk mengunduh laporan ini.');
        }

        if (!\$research->pdf_path) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        \$relativePath = str_replace('/storage/', '', \$research->pdf_path);
        \$absolutePath = storage_path('app/public/' . \$relativePath);

        if (!file_exists(\$absolutePath)) {
            abort(404, 'File PDF tidak ditemukan di server.');
        }

        \$pdf = new \App\Services\AlphaFpdi();
        
        try {
            \$pageCount = \$pdf->setSourceFile(\$absolutePath);
        } catch (\Exception \$e) {
            // Jika ada error (misal versi PDF terlalu baru), kirim file asli saja
            return response()->download(\$absolutePath, \$research->slug . '.pdf');
        }

        \$logoPath = public_path('images/logo.png');
        \$userEmail = auth()->check() ? auth()->user()->email : 'Guest';
        \$userName = auth()->check() ? auth()->user()->name : '';

        for (\$pageNo = 1; \$pageNo <= \$pageCount; \$pageNo++) {
            \$templateId = \$pdf->importPage(\$pageNo);
            \$size = \$pdf->getTemplateSize(\$templateId);

            \$orientation = \$size['width'] > \$size['height'] ? 'L' : 'P';
            \$pdf->AddPage(\$orientation, [\$size['width'], \$size['height']]);
            \$pdf->useTemplate(\$templateId);

            // Watermark text di bagian bawah
            \$pdf->SetAlpha(0.6);
            \$pdf->SetFont('Arial', '', 9);
            \$pdf->SetTextColor(100, 100, 100);
            \$pdf->SetXY(15, \$size['height'] - 15);
            \$pdf->Cell(0, 10, 'Licensed to: ' . \$userName . ' (' . \$userEmail . ') - Avenir Research', 0, 0, 'L');

            // Logo watermark (Lynk.id style - diagonal repeating grid)
            if (file_exists(\$logoPath)) {
                \$pdf->SetAlpha(0.08); // Sangat transparan
                \$pdf->Rotate(45, \$size['width'] / 2, \$size['height'] / 2);
                
                \$logoWidth = 40;
                
                // Mulai dari luar batas halaman agar penuh
                for (\$x = -150; \$x < \$size['width'] + 150; \$x += 80) {
                    for (\$y = -150; \$y < \$size['height'] + 150; \$y += 60) {
                        \$pdf->Image(\$logoPath, \$x, \$y, \$logoWidth);
                    }
                }
                \$pdf->Rotate(0); // Reset rotasi
            }
            
            \$pdf->SetAlpha(1); // Reset alpha
        }

        \$filename = \$research->slug ? \$research->slug . '.pdf' : 'Katalog-Riset.pdf';
        
        // Output ke browser sebagai download
        \$pdf->Output('D', \$filename);
        exit;
    }
METHOD;

// Sisipkan sebelum function cleanHtmlForDarkMode
\$contents = preg_replace('/(\s*\/\*\*\s*\*\s*Cleans up dark inline text colors)/', "\n\n\$newMethod\n\n$1", \$contents);

file_put_contents(\$file, \$contents);
echo "Method added.\n";
