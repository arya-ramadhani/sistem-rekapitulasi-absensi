<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Matkul;
use App\Models\Kelas;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Absensi;



class AbsensiController extends Controller
{

    // Threshold minimum untuk matching - dibuat lebih ketat
    private const NORMAL_THRESHOLD = 65;  // Untuk nama dan data umum
    private const LOOSE_THRESHOLD = 55;   // Untuk fallback matching
    private const STRICT_THRESHOLD = 75;  // Untuk matching yang ketat

    private function findMatkulMatch($input, $matkuls, $threshold = self::NORMAL_THRESHOLD)
    {
        if (empty($input) || strlen($input) < 2) {
            return null;
        }

        $bestMatch = null;
        $highestPercent = 0;

        // Normalisasi input - hapus spasi dan karakter khusus, tapi pertahankan angka dan huruf
        $normalizedInput = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($input)));

        // Juga coba dengan spasi untuk matching yang lebih fleksibel
        $inputWithSpaces = strtoupper(trim($input));
        $inputWords = preg_split('/\s+/', $inputWithSpaces);

        Log::info("Mencari mata kuliah dengan input: '$input' -> normalized: '$normalizedInput'");

        foreach ($matkuls as $matkul) {
            // Format kombinasi yang mungkin untuk matching
            $matkulFormats = [
                // Format tanpa spasi
                $matkul->kode,                                    // STK103
                preg_replace('/[^A-Z0-9]/', '', $matkul->kode),   // STK103 (cleaned)

                // Format dengan spasi
                $matkul->kode,                                    // STK 103 (jika ada spasi)

                // Format nama (fallback dengan prioritas rendah)
                $matkul->nama,
            ];

            foreach ($matkulFormats as $index => $format) {
                $normalizedFormat = strtoupper($format);
                $normalizedFormatNoSpace = strtoupper(preg_replace('/[^A-Z0-9]/', '', $format));

                // === PRIORITAS 1: EXACT MATCH ===
                // Exact match tanpa spasi (prioritas tertinggi untuk kode)
                if ($normalizedInput === $normalizedFormatNoSpace && $index <= 1) {
                    Log::info("EXACT MATCH ditemukan: {$matkul->kode} - {$matkul->nama}");
                    return $matkul;
                }

                // Exact match dengan spasi
                if ($inputWithSpaces === $normalizedFormat && $index <= 1) {
                    Log::info("EXACT MATCH dengan spasi: {$matkul->kode} - {$matkul->nama}");
                    return $matkul;
                }

                // === PRIORITAS 2: SIMILARITY MATCH UNTUK KODE (tanpa spasi) ===
                if ($index <= 1) { // Hanya untuk format kode
                    similar_text($normalizedInput, $normalizedFormatNoSpace, $percent);
                    if ($percent >= $threshold && $percent > $highestPercent) {
                        $highestPercent = $percent;
                        $bestMatch = $matkul;
                        Log::info("SIMILARITY MATCH (kode): {$matkul->kode} - Score: $percent");
                    }
                }

                // === PRIORITAS 3: SIMILARITY MATCH UNTUK KODE (dengan spasi) ===
                if ($index <= 1) { // Hanya untuk format kode
                    similar_text($inputWithSpaces, $normalizedFormat, $percentWithSpace);
                    if ($percentWithSpace >= $threshold && $percentWithSpace > $highestPercent) {
                        $highestPercent = $percentWithSpace;
                        $bestMatch = $matkul;
                        Log::info("SIMILARITY MATCH dengan spasi (kode): {$matkul->kode} - Score: $percentWithSpace");
                    }
                }

                // === PRIORITAS 4: CONTAINS MATCH UNTUK KODE ===
                if ($index <= 1) { // Hanya untuk format kode
                    if (stripos($normalizedFormatNoSpace, $normalizedInput) !== false) {
                        $containsScore = 90; // Nilai tinggi untuk contains match
                        if ($containsScore >= $threshold && $containsScore > $highestPercent) {
                            $highestPercent = $containsScore;
                            $bestMatch = $matkul;
                            Log::info("CONTAINS MATCH (kode): {$matkul->kode} contains '$normalizedInput'");
                        }
                    }

                    if (stripos($normalizedInput, $normalizedFormatNoSpace) !== false) {
                        $reverseContainsScore = 85; // Nilai tinggi untuk reverse contains
                        if ($reverseContainsScore >= $threshold && $reverseContainsScore > $highestPercent) {
                            $highestPercent = $reverseContainsScore;
                            $bestMatch = $matkul;
                            Log::info("REVERSE CONTAINS MATCH (kode): '$normalizedInput' contains {$matkul->kode}");
                        }
                    }
                }
            }

            // === PRIORITAS 5: WORD-BY-WORD MATCHING (untuk kode yang ada spasi) ===
            if (count($inputWords) >= 1) {
                $matkulWords = preg_split('/[\s\-]+/', strtoupper($matkul->kode));
                $matchingWords = 0;
                $totalWords = count($matkulWords);

                foreach ($matkulWords as $matkulWord) {
                    foreach ($inputWords as $inputWord) {
                        if (strtoupper($matkulWord) === strtoupper($inputWord)) {
                            $matchingWords++;
                            break;
                        }

                        // Fuzzy match untuk setiap kata
                        similar_text(strtoupper($matkulWord), strtoupper($inputWord), $wordPercent);
                        if ($wordPercent >= 85) { // Threshold tinggi untuk word matching
                            $matchingWords++;
                            break;
                        }
                    }
                }

                // Jika mayoritas kata cocok
                if ($matchingWords >= 1 && $matchingWords >= ($totalWords * 0.6)) {
                    $wordMatchPercent = ($matchingWords / $totalWords) * 100;
                    if ($wordMatchPercent > $highestPercent) {
                        $highestPercent = $wordMatchPercent;
                        $bestMatch = $matkul;
                        Log::info("WORD MATCHING (kode): {$matkul->kode} - Score: $wordMatchPercent");
                    }
                }
            }

            // === PRIORITAS 6: FALLBACK KE NAMA (prioritas rendah) ===
            if ($highestPercent < 50) { // Hanya jika belum ada match bagus
                if (stripos($matkul->nama, $input) !== false) {
                    $nameMatchScore = 45; // Nilai rendah untuk nama
                    if ($nameMatchScore > $highestPercent) {
                        $highestPercent = $nameMatchScore;
                        $bestMatch = $matkul;
                        Log::info("FALLBACK NAME MATCH: {$matkul->nama} contains '$input'");
                    }
                }
            }
        }

        if ($bestMatch) {
            Log::info("BEST MATA KULIAH MATCH: {$bestMatch->kode} - {$bestMatch->nama} dengan score: $highestPercent");
        } else {
            Log::info("TIDAK ADA MATA KULIAH MATCH untuk: '$input'");
        }

        return $bestMatch;
    }

    private function extractPokokPembahasan($text)
    {
        $lines = explode("\n", $text);
        $pokok_pembahasan = '';
        $foundPokokBahasan = false;
        $pokokLineIndex = -1;

        Log::info("=== DEBUGGING POKOK PEMBAHASAN EXTRACTION ===");
        Log::info("Total lines to analyze: " . count($lines));

        for ($i = 0; $i < count($lines); $i++) {
            $cleanLine = $this->cleanText($lines[$i]);

            // Pattern untuk mendeteksi baris yang mengandung "POKOK BAHASAN"
            if (preg_match('/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN|MATERI|TOPIK)/i', $cleanLine)) {
                Log::info("Found POKOK BAHASAN keyword in line $i: '$cleanLine'");
                $foundPokokBahasan = true;
                $pokokLineIndex = $i;

                // Method 1: Coba ekstrak langsung dari baris yang sama
                $sameLine = $this->extractPokokFromSameLine($cleanLine);
                if ($sameLine) {
                    $pokok_pembahasan = $sameLine;
                    Log::info("Extracted from same line: '$pokok_pembahasan'");
                    break;
                }

                // Method 2: Cari di baris-baris berikutnya (dalam jarak 3 baris)
                $nextLines = $this->extractPokokFromNextLines($lines, $i, 3);
                if ($nextLines) {
                    $pokok_pembahasan = $nextLines;
                    Log::info("Extracted from next lines: '$pokok_pembahasan'");
                    break;
                }

                // Method 3: Cari di kolom kanan dengan memisahkan teks berdasarkan posisi
                $columnRight = $this->extractPokokFromColumn($cleanLine);
                if ($columnRight) {
                    $pokok_pembahasan = $columnRight;
                    Log::info("Extracted from column: '$pokok_pembahasan'");
                    break;
                }

                // Method 3.5: BARU - Cari di baris-baris sekitar
                $nearbyLines = $this->extractPokokFromNearbyLines($lines, $i);
                if ($nearbyLines) {
                    $pokok_pembahasan = $nearbyLines;
                    Log::info("Extracted from nearby lines: '$pokok_pembahasan'");
                    break;
                }
            }
        }

        // Method 4: Jika belum ketemu, cari pattern teks yang panjang di area tengah/bawah dokumen
        if (empty($pokok_pembahasan)) {
            $pokok_pembahasan = $this->extractPokokFromPattern($lines);
            if ($pokok_pembahasan) {
                Log::info("Extracted from pattern: '$pokok_pembahasan'");
            }
        }

        // Bersihkan hasil
        $pokok_pembahasan = $this->cleanPokokPembahasan($pokok_pembahasan);

        Log::info("Final POKOK PEMBAHASAN result: '$pokok_pembahasan'");

        return $pokok_pembahasan;
    }


    private function extractPokokFromSameLine($line)
    {
        $patterns = [
            // Pattern untuk format "POKOK BAHASAN : teks" - TURUNKAN minimum dari 10 ke 3
            '/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN|MATERI)\s*:?\s*(.{3,})/i',

            // Pattern untuk format dengan tab atau spasi banyak - TURUNKAN minimum dari 5 ke 3
            '/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN)\s{2,}(.{3,})/i',

            // Pattern untuk format yang dipisah dengan karakter khusus - TURUNKAN minimum
            '/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN)[\s\.\-_:]{1,}(.{3,})/i',

            // Pattern untuk format tabel - TAMBAH pattern baru
            '/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN)\s*(.{3,})/i',

            // Pattern untuk menangkap apapun setelah keyword tanpa syarat spasi
            '/(?:POKOK\s*BAHASAN|POKOK\s*PEMBAHASAN)(.{3,})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $match)) {
                $extracted = trim($match[1]);

                // PERLONGGAR validasi - turunkan minimum dari 3 ke 2
                if (strlen($extracted) >= 2 && strlen($extracted) <= 200) {
                    // Jangan ambil jika hanya angka atau karakter khusus
                    if (!preg_match('/^[\d\s\.\-_:]+$/', $extracted)) {
                        Log::info("extractPokokFromSameLine SUCCESS: '$extracted' from pattern: $pattern");
                        return $extracted;
                    }
                }
            }
        }

        Log::info("extractPokokFromSameLine FAILED for line: '$line'");
        return null;
    }


    private function extractPokokFromNextLines($lines, $startIndex, $maxLines = 3)
    {
        $candidates = [];

        for ($i = $startIndex + 1; $i <= min($startIndex + $maxLines, count($lines) - 1); $i++) {
            $nextLine = trim($this->cleanText($lines[$i]));

            // Skip baris kosong atau yang terlalu pendek
            if (strlen($nextLine) < 3) {
                continue;
            }

            // Skip baris yang mengandung keyword lain yang tidak relevan
            $skipKeywords = ['NPM', 'NAMA', 'MAHASISWA', 'HADIR', 'SEMESTER', 'KETERANGAN', 'PARAF'];
            $shouldSkip = false;
            foreach ($skipKeywords as $keyword) {
                if (stripos($nextLine, $keyword) !== false) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            // Ambil baris yang mengandung teks bermakna
            if (preg_match('/^[A-Za-z].{2,100}$/', $nextLine)) {
                $candidates[] = $nextLine;
            }
        }

        // Gabungkan candidates jika ada beberapa
        if (!empty($candidates)) {
            return implode(' ', $candidates);
        }

        return null;
    }

    private function extractPokokFromColumn($line)
    {
        Log::info("extractPokokFromColumn analyzing: '$line'");

        // Coba pisahkan berdasarkan posisi karakter (asumsi POKOK BAHASAN di kiri, isi di kanan)
        $pokokPos = stripos($line, 'POKOK');
        if ($pokokPos !== false) {
            // COBA berbagai offset, tidak hanya 15
            $offsets = [15, 12, 10, 8, 6, 4];

            foreach ($offsets as $offset) {
                $afterPokok = substr($line, $pokokPos + $offset);

                // Bersihkan karakter pemisah di awal - LEBIH AGRESIF
                $afterPokok = preg_replace('/^[\s\.\-_:\|]+/', '', $afterPokok);

                // PERLONGGAR validasi - turunkan minimum dari 5 ke 2
                if (strlen($afterPokok) >= 2 && strlen($afterPokok) <= 200) {
                    // Pastikan bukan hanya angka atau simbol
                    if (preg_match('/[A-Za-z]/', $afterPokok)) {
                        Log::info("extractPokokFromColumn SUCCESS with offset $offset: '$afterPokok'");
                        return trim($afterPokok);
                    }
                }
            }
        }

        Log::info("extractPokokFromColumn FAILED for line: '$line'");
        return null;
    }

    private function extractPokokFromPattern($lines)
    {
        $candidates = [];

        Log::info("extractPokokFromPattern analyzing " . count($lines) . " lines");

        // PERLUAS area pencarian - dari 30% ke 20% dan dari 90% ke 95%
        $startFrom = max(0, count($lines) * 0.2); // Mulai dari 20% dokumen
        $endAt = min(count($lines), count($lines) * 0.95); // Sampai 95% dokumen

        for ($i = $startFrom; $i < $endAt; $i++) {
            $line = trim($this->cleanText($lines[$i]));

            // PERLONGGAR batasan panjang - turunkan minimum dari 10 ke 5
            if (strlen($line) < 5 || strlen($line) > 200) {
                continue;
            }

            // TAMBAH pattern yang lebih fleksibel
            $patterns = [
                // Pattern untuk teks dengan kata kunci akademik - LEBIH FLEKSIBEL
                '/^.*((?:tugas|karya|ilmiah|penelitian|studi|analisis|pembahasan|materi|pembelajaran|praktek|praktik|teori|konsep|metode|pembuatan|akhir).{2,100})$/i',

                // Pattern untuk teks yang dimulai dengan huruf kapital
                '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]*){1,10})$/',

                // Pattern untuk menangkap frasa lengkap seperti "Karya Ilmiah Pembuatan Tugas Akhir"
                '/^([A-Z][a-zA-Z\s]{5,100})$/',

                // Pattern khusus untuk kombinasi kata akademik
                '/^.*((?:Karya|Tugas|Ilmiah|Penelitian|Studi|Analisis|Materi|Pembelajaran|Praktik|Teori|Konsep|Metode|Pembuatan|Akhir).{5,100})$/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $line, $match)) {
                    // Validasi tambahan: pastikan bukan baris yang mengandung data mahasiswa
                    if (
                        !preg_match('/\d{6,}/', $line) && // Tidak mengandung NPM
                        !preg_match('/^(No|NPM|Nama|HADIR|SEMESTER|KETERANGAN|PARAF)/i', $line) && // Tidak dimulai dengan header
                        !preg_match('/^\d+\s+\d+/', $line) // Tidak dimulai dengan angka-angka
                    ) {
                        $candidate = isset($match[1]) ? trim($match[1]) : trim($line);
                        Log::info("extractPokokFromPattern CANDIDATE found: '$candidate' from pattern: $pattern");
                        $candidates[] = $candidate;
                    }
                }
            }
        }
        // Pilih kandidat terbaik (yang terpanjang dan paling masuk akal)
        if (!empty($candidates)) {
            // Sort berdasarkan panjang, ambil yang terpanjang
            usort($candidates, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            Log::info("extractPokokFromPattern SUCCESS: Found " . count($candidates) . " candidates, selected: '{$candidates[0]}'");
            return $candidates[0];
        }

        Log::info("extractPokokFromPattern FAILED: No candidates found");
        return null;
    }


    private function extractPokokFromNearbyLines($lines, $pokokLineIndex)
    {
        Log::info("extractPokokFromNearbyLines: Searching around line $pokokLineIndex");

        // Cari di 5 baris sebelum dan sesudah
        $searchRange = 5;
        $startIndex = max(0, $pokokLineIndex - $searchRange);
        $endIndex = min(count($lines) - 1, $pokokLineIndex + $searchRange);

        $candidates = [];

        for ($i = $startIndex; $i <= $endIndex; $i++) {
            if ($i === $pokokLineIndex) continue; // Skip baris "POKOK BAHASAN" itu sendiri

            $line = trim($this->cleanText($lines[$i]));

            // Skip baris yang terlalu pendek atau mengandung data tidak relevan
            if (
                strlen($line) < 3 ||
                preg_match('/\d{6,}/', $line) || // Mengandung NPM
                preg_match('/^(No|NPM|Nama|HADIR|SEMESTER)/i', $line)
            ) {
                continue;
            }

            // Cari baris yang mengandung kata-kata akademik
            if (preg_match('/(tugas|karya|ilmiah|penelitian|studi|analisis|pembahasan|materi|pembelajaran|praktek|praktik|teori|konsep|metode|pembuatan|akhir)/i', $line)) {
                Log::info("extractPokokFromNearbyLines CANDIDATE: '$line' at line $i");
                $candidates[] = $line;
            }
        }

        if (!empty($candidates)) {
            // Ambil yang terpanjang
            usort($candidates, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            Log::info("extractPokokFromNearbyLines SUCCESS: '{$candidates[0]}'");
            return $candidates[0];
        }

        Log::info("extractPokokFromNearbyLines FAILED");
        return null;
    }


    private function cleanPokokPembahasan($text)
    {
        if (empty($text)) {
            return '';
        }

        // Hapus karakter khusus di awal dan akhir
        $text = trim($text, " \t\n\r\0\x0B.:_-");

        // Hapus multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Hapus karakter non-printable
        $text = preg_replace('/[^\x20-\x7E\x{00A0}-\x{FFFF}]/u', '', $text);

        // Batasi panjang maksimal
        if (strlen($text) > 200) {
            $text = substr($text, 0, 200);
            $text = trim($text);
        }

        return $text;
    }


    // Ekstraksi kemungkinan nama/kode mata kuliah dari line OCR //
    private function extractPossibleMatkul($line)
    {
        $candidates = [];

        // Pattern untuk kode mata kuliah (PRIORITAS UTAMA)
        $kodePatterns = [
            '/\b([A-Z]{2,4}\d{3,4})\b/',      // STK103, MTP107, etc
            '/\b([A-Z]{3,5}\d{2,3})\b/',      // alternatif format  
            '/\b([A-Z]{2,4})\s*\d{2,4}\b/',   // Format dengan spasi
            '/\b([A-Z]{2,4}\d{1,4})\b/',      // Format yang lebih fleksibel
            '/\b([A-Z]{2,4})\b/',             // Untuk kode yang hanya huruf (fallback)
        ];

        foreach ($kodePatterns as $pattern) {
            if (preg_match_all($pattern, $line, $matches)) {
                foreach ($matches[1] as $match) {
                    $candidates[] = trim($match);
                }
            }
        }

        // Pattern untuk nama mata kuliah (kata dengan huruf kapital) - prioritas rendah
        $namaPatterns = [
            '/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,5})\b/', // Format nama proper
            '/\b([A-Z][a-z]+(?:\s+dan\s+[A-Z][a-z]+)+)\b/', // "Statistika dan Probabilitas"
        ];

        foreach ($namaPatterns as $pattern) {
            if (preg_match_all($pattern, $line, $matches)) {
                foreach ($matches[1] as $match) {
                    $cleanMatch = trim($match);
                    if (strlen($cleanMatch) >= 5 && strlen($cleanMatch) <= 50) {
                        $candidates[] = $cleanMatch;
                    }
                }
            }
        }

        return array_unique($candidates);
    }

    /**
     * Pencarian kelas dengan format yang lebih akurat
     */
    private function findKelasMatch($input, $kelass, $threshold = self::NORMAL_THRESHOLD)
    {
        if (empty($input) || strlen($input) < 1) {
            return null;
        }

        $bestMatch = null;
        $highestPercent = 0;

        // Normalisasi input - hapus spasi dan karakter khusus, tapi pertahankan angka dan huruf
        $normalizedInput = strtoupper(preg_replace('/[^A-Z0-9]/', '', trim($input)));

        // Juga coba dengan spasi untuk matching yang lebih fleksibel
        $inputWithSpaces = strtoupper(trim($input));
        $inputWords = preg_split('/\s+/', $inputWithSpaces);

        // Pastikan ada data kelas sebelum melanjutkan
        if (empty($kelass)) {
            Log::error("Kelas list is empty.");
            return null; // Handle the case where there are no classes to match
        }

        foreach ($kelass as $kelas) {
            // Pastikan kelas ada sebelum mencoba mencocokkan
            if (!$kelas) {
                Log::error("Kelas data is not valid.");
                continue; // Skip invalid kelas data
            }

            // Format kombinasi yang mungkin untuk matching
            $kelasFormats = [
                $kelas->tingkat . $kelas->prodi . $kelas->nama,    // 2TRPLB
                $kelas->tingkat . $kelas->prodi,                  // 2TRPL
                $kelas->tingkat . $kelas->nama,                   // 2B
                $kelas->prodi . $kelas->nama,                     // TRPLB
                $kelas->nama,                                     // B

                // Format dengan spasi
                $kelas->tingkat . ' ' . $kelas->prodi . ' ' . $kelas->nama,  // 2 TRPL B
                $kelas->tingkat . ' ' . $kelas->prodi,                      // 2 TRPL
                $kelas->tingkat . ' ' . $kelas->nama,                       // 2 B
                $kelas->prodi . ' ' . $kelas->nama,                         // TRPL B
            ];

            foreach ($kelasFormats as $format) {
                $normalizedFormat = strtoupper($format);
                $normalizedFormatNoSpace = strtoupper(preg_replace('/[^A-Z0-9]/', '', $format));

                // === PRIORITAS 1: EXACT MATCH ===
                // Exact match tanpa spasi
                if ($normalizedInput === $normalizedFormatNoSpace) {
                    return $kelas;
                }

                // Exact match dengan spasi
                if ($inputWithSpaces === $normalizedFormat) {
                    return $kelas;
                }

                // === PRIORITAS 2: SIMILARITY MATCH TANPA SPASI ===
                similar_text($normalizedInput, $normalizedFormatNoSpace, $percent);
                if ($percent >= $threshold && $percent > $highestPercent) {
                    $highestPercent = $percent;
                    $bestMatch = $kelas;
                }

                // === PRIORITAS 3: SIMILARITY MATCH DENGAN SPASI ===
                similar_text($inputWithSpaces, $normalizedFormat, $percentWithSpace);
                if ($percentWithSpace >= $threshold && $percentWithSpace > $highestPercent) {
                    $highestPercent = $percentWithSpace;
                    $bestMatch = $kelas;
                }
            }

            // === PRIORITAS 4: WORD-BY-WORD MATCHING ===
            // Untuk input seperti "KELAS 2 TRPL B" atau "2 TRPL B"
            if (count($inputWords) >= 2) {
                $kelasWords = [$kelas->tingkat, $kelas->prodi_singkatan, $kelas->nama];
                $matchingWords = 0;
                $totalWords = count($kelasWords);

                foreach ($kelasWords as $kelasWord) {
                    foreach ($inputWords as $inputWord) {
                        if (strtoupper($kelasWord) === strtoupper($inputWord)) {
                            $matchingWords++;
                            break;
                        }

                        // Fuzzy match untuk setiap kata
                        similar_text(strtoupper($kelasWord), strtoupper($inputWord), $wordPercent);
                        if ($wordPercent >= 85) { // Threshold tinggi untuk word matching
                            $matchingWords++;
                            break;
                        }
                    }
                }

                // Jika mayoritas kata cocok
                if ($matchingWords >= 2 && $matchingWords >= ($totalWords * 0.6)) {
                    $wordMatchPercent = ($matchingWords / $totalWords) * 100;
                    if ($wordMatchPercent > $highestPercent) {
                        $highestPercent = $wordMatchPercent;
                        $bestMatch = $kelas;
                    }
                }
            }

            // === PRIORITAS 5: PATTERN MATCHING KHUSUS ===
            // Untuk format "KELAS 2 TRPL B" atau "DAFTAR HADIR PERKULIAHAN KELAS 2 TRPL B"
            if (preg_match('/(?:KELAS|CLASS)\s+(.+)/i', $inputWithSpaces, $matches)) {
                $kelasExtracted = trim($matches[1]);
                $kelasPattern = $kelas->tingkat . ' ' . $kelas->prodi_singkatan . ' ' . $kelas->nama;

                similar_text(strtoupper($kelasExtracted), strtoupper($kelasPattern), $patternPercent);
                if ($patternPercent >= $threshold && $patternPercent > $highestPercent) {
                    $highestPercent = $patternPercent;
                    $bestMatch = $kelas;
                }
            }
        } // End of foreach ($kelass as $kelas)

        return $bestMatch;
    }

    private function extractKelasFromHeader($text, $allKelass)
    {
        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $cleanLine = $this->cleanText($line);

            // Pattern untuk mendeteksi kelas dari header
            $kelasPatterns = [
                '/DAFTAR\s+HADIR\s+PERKULIAHAN\s+KELAS\s+(.+?)(?:\s|$)/i',
                '/(?:KELAS|CLASS)[\s\/:]*([A-Z0-9\s\-]+)/i',
                '/(?:TINGKAT|LEVEL)[\s\/:]*(\d+[A-Z]*\s*[A-Z]*\s*[A-Z0-9]*)/i',
            ];

            foreach ($kelasPatterns as $pattern) {
                if (preg_match($pattern, $cleanLine, $match)) {
                    $kelasCandidate = trim($match[1]);

                    // Coba cocokkan dengan database
                    $kelasMatch = $this->findKelasMatch($kelasCandidate, $allKelass);
                    if ($kelasMatch) {
                        return $kelasMatch;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Pencarian dosen dengan matching yang lebih akurat dan fleksibel
     */
    private function findDosenMatch($input, $dosens, $threshold = 50) // Turunkan threshold default
    {
        if (empty($input) || strlen($input) < 2) { // Turunkan minimum length
            return null;
        }

        $bestMatch = null;
        $highestPercent = 0;
        $normalizedInput = $this->normalizeDosenText($input);

        // Log untuk debugging
        Log::info("Mencari dosen dengan input: '$input' -> normalized: '$normalizedInput'");

        foreach ($dosens as $dosen) {
            $normalizedDosen = $this->normalizeDosenText($dosen->nama);
            $matchScore = 0;

            // === PRIORITAS 1: EXACT MATCH ===
            if (strtolower($normalizedInput) === strtolower($normalizedDosen)) {
                Log::info("EXACT MATCH ditemukan: {$dosen->nama}");
                return $dosen;
            }

            // === PRIORITAS 2: CONTAINS MATCH ===
            // Cek apakah input terkandung dalam nama dosen atau sebaliknya
            if (stripos($normalizedDosen, $normalizedInput) !== false) {
                $matchScore = 95;
                Log::info("CONTAINS MATCH: '$normalizedInput' found in '{$dosen->nama}' - Score: $matchScore");
            } elseif (stripos($normalizedInput, $normalizedDosen) !== false) {
                $matchScore = 90;
                Log::info("REVERSE CONTAINS: '{$dosen->nama}' found in '$normalizedInput' - Score: $matchScore");
            }

            // === PRIORITAS 3: WORD-BY-WORD MATCHING ===
            $dosenWords = array_filter(explode(' ', strtolower($normalizedDosen)));
            $inputWords = array_filter(explode(' ', strtolower($normalizedInput)));

            $wordMatches = 0;
            $totalInputWords = count($inputWords);
            $totalDosenWords = count($dosenWords);

            foreach ($inputWords as $inputWord) {
                if (strlen($inputWord) >= 2) {
                    $bestWordMatch = 0;

                    foreach ($dosenWords as $dosenWord) {
                        if (strlen($dosenWord) >= 2) {
                            // Exact word match
                            if ($inputWord === $dosenWord) {
                                $wordMatches += 3; // Bonus tinggi untuk exact match
                                $bestWordMatch = 100;
                                break;
                            }

                            // Inisial matching (satu huruf vs kata lengkap)
                            if (strlen($inputWord) === 1 && strtolower($inputWord) === strtolower(substr($dosenWord, 0, 1))) {
                                $wordMatches += 2;
                                $bestWordMatch = max($bestWordMatch, 85);
                            } elseif (strlen($dosenWord) === 1 && strtolower($dosenWord) === strtolower(substr($inputWord, 0, 1))) {
                                $wordMatches += 2;
                                $bestWordMatch = max($bestWordMatch, 85);
                            }

                            // Similarity matching
                            similar_text($inputWord, $dosenWord, $wordPercent);
                            if ($wordPercent >= 70) { // Turunkan threshold
                                $wordMatches += ($wordPercent / 100);
                                $bestWordMatch = max($bestWordMatch, $wordPercent);
                            }

                            // Partial string matching
                            if (stripos($dosenWord, $inputWord) !== false || stripos($inputWord, $dosenWord) !== false) {
                                $wordMatches += 1.5;
                                $bestWordMatch = max($bestWordMatch, 75);
                            }
                        }
                    }

                    Log::info("Word match for '$inputWord': best score $bestWordMatch");
                }
            }

            // Hitung score berdasarkan word matching
            if ($totalInputWords > 0) {
                $wordMatchScore = min(100, ($wordMatches / $totalInputWords) * 100);
                $matchScore = max($matchScore, $wordMatchScore);
            }

            // === PRIORITAS 4: SIMILARITY KESELURUHAN ===
            similar_text(strtolower($normalizedInput), strtolower($normalizedDosen), $overallPercent);
            $matchScore = max($matchScore, $overallPercent);

            // === PRIORITAS 5: LEVENSHTEIN DISTANCE ===
            $maxLen = max(strlen($normalizedInput), strlen($normalizedDosen));
            if ($maxLen > 0) {
                $distance = levenshtein(strtolower($normalizedInput), strtolower($normalizedDosen));
                $levenScore = max(0, (1 - ($distance / $maxLen)) * 100);
                $matchScore = max($matchScore, $levenScore);
            }

            Log::info("Dosen: '{$dosen->nama}' -> Final Score: $matchScore (threshold: $threshold)");

            if ($matchScore >= $threshold && $matchScore > $highestPercent) {
                $highestPercent = $matchScore;
                $bestMatch = $dosen;
            }
        }

        if ($bestMatch) {
            Log::info("BEST MATCH ditemukan: {$bestMatch->nama} dengan score: $highestPercent");
        } else {
            Log::info("TIDAK ADA MATCH ditemukan untuk: '$input'");
        }

        return $bestMatch;
    }

    /**
     * Normalisasi khusus untuk nama dosen
     */
    private function normalizeDosenText($text)
    {
        // Bersihkan karakter khusus tapi pertahankan titik untuk inisial
        $text = preg_replace('/[^\p{L}\p{N}\s\.]/u', '', $text);

        // Hapus multiple dots yang bukan inisial
        $text = preg_replace('/\.{2,}/', ' ', $text);

        // Hapus spasi berlebih
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    private function findDosenExactMatch($input, $dosens)
    {
        $normalizedInput = $this->normalizeDosenText($input);

        Log::info("Mencari exact match untuk: '$input' -> normalized: '$normalizedInput'");

        foreach ($dosens as $dosen) {
            $normalizedDosen = $this->normalizeDosenText($dosen->nama);

            // Exact match (case insensitive)
            if (strtolower($normalizedInput) === strtolower($normalizedDosen)) {
                Log::info("EXACT MATCH ditemukan: {$dosen->nama}");
                return $dosen;
            }

            // Exact match untuk nama pendek (tanpa gelar)
            $inputParts = array_filter(explode(' ', strtolower($normalizedInput)));
            $dosenParts = array_filter(explode(' ', strtolower($normalizedDosen)));

            // Cek apakah semua kata dari input ada di nama dosen
            if (count($inputParts) >= 2) {
                $allWordsMatch = true;
                foreach ($inputParts as $inputWord) {
                    $found = false;
                    foreach ($dosenParts as $dosenWord) {
                        if ($inputWord === $dosenWord) {
                            $found = true;
                            break;
                        }
                        // Juga cek inisial
                        if (strlen($inputWord) === 1 && strtolower($inputWord) === strtolower(substr($dosenWord, 0, 1))) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $allWordsMatch = false;
                        break;
                    }
                }

                if ($allWordsMatch) {
                    Log::info("WORD-BY-WORD EXACT MATCH: {$dosen->nama} dengan input: '$input'");
                    return $dosen;
                }
            }

            // Cek apakah input adalah subset dari nama dosen
            if (count($inputParts) >= 2 && count($dosenParts) >= count($inputParts)) {
                $isSubset = true;
                foreach ($inputParts as $inputWord) {
                    $wordFound = false;
                    foreach ($dosenParts as $dosenWord) {
                        if (
                            $inputWord === $dosenWord ||
                            (strlen($inputWord) === 1 && strtolower($inputWord) === strtolower(substr($dosenWord, 0, 1)))
                        ) {
                            $wordFound = true;
                            break;
                        }
                    }
                    if (!$wordFound) {
                        $isSubset = false;
                        break;
                    }
                }

                if ($isSubset) {
                    Log::info("SUBSET MATCH: {$dosen->nama} contains all words from '$input'");
                    return $dosen;
                }
            }
        }

        Log::info("Tidak ada exact match untuk: '$input'");
        return null;
    }

    /**
     * Debug method untuk troubleshooting mata kuliah
     */
    public function debugMatkulMatching(Request $request)
    {
        // Menggunakan input kosong jika tidak ada input
        $testInput = $request->input('test_input', '');
        if (empty($testInput)) {
            return response()->json(['success' => false, 'message' => 'Input kosong, tidak ada data untuk dicocokkan.']);
        }

        $allMatkuls = Matkul::all();

        Log::info("=== DEBUG MATA KULIAH MATCHING ===");
        Log::info("Test input: '$testInput'");
        Log::info("Total mata kuliah di database: " . $allMatkuls->count());

        foreach ($allMatkuls as $matkul) {
            Log::info("Matkul: {$matkul->kode} - {$matkul->nama}");
        }

        $result = $this->findMatkulMatch($testInput, $allMatkuls, 40);

        if ($result) {
            Log::info("HASIL: Match ditemukan - {$result->kode} - {$result->nama}");
            return response()->json([
                'success' => true,
                'matched' => [
                    'kode' => $result->kode,
                    'nama' => $result->nama,
                    'display' => "{$result->nama} / {$result->kode}"
                ]
            ]);
        } else {
            Log::info("HASIL: Tidak ada match");
            return response()->json(['success' => false, 'message' => 'Tidak ada match']);
        }
    }

    /**
     * Pencarian mahasiswa berdasarkan NPM dengan threshold yang ketat
     */
    private function findMahasiswaByNPM($npm, $mahasiswas)
    {
        // Exact match first - prioritas utama
        foreach ($mahasiswas as $mahasiswa) {
            if ($mahasiswa->npm === $npm) {
                return $mahasiswa;
            }
        }

        // Fuzzy match hanya untuk NPM yang sangat mirip
        $cleanNpm = preg_replace('/[^0-9]/', '', $npm);
        if (strlen($cleanNpm) >= 7) {
            foreach ($mahasiswas as $mahasiswa) {
                $dbNpm = preg_replace('/[^0-9]/', '', $mahasiswa->npm);
                $similarity = 0;
                similar_text($cleanNpm, $dbNpm, $similarity);
                // Threshold sangat tinggi untuk NPM (90%)
                if ($similarity >= 90) {
                    return $mahasiswa;
                }
            }
        }

        return null;
    }

    /**
     * Normalisasi teks untuk matching yang lebih baik
     */
    private function normalizeText($text)
    {
        // Hapus karakter non-alfanumerik kecuali spasi
        $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        // Multiple spaces ke single space
        $text = preg_replace('/\s+/', ' ', $text);
        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Bersihkan teks OCR
     */
    private function cleanText($text)
    {
        // Hapus karakter non-alfanumerik kecuali spasi, titik, dan koma
        $text = preg_replace('/[^\p{L}\p{N}\s\/:.\-,]/u', '', $text);

        // Khusus untuk nama dosen, pertahankan titik untuk inisial
        $text = preg_replace('/\.{2,}/', ' ', $text); // Ganti multiple dots dengan spasi
        $text = preg_replace('/\s+/', ' ', $text); // Multiple spaces ke single space

        return trim($text);
    }

    private function debugDosenExtraction($text, $allDosens)
    {
        $lines = explode("\n", $text);
        $debugInfo = [];

        Log::info("=== DEBUGGING DOSEN EXTRACTION ===");
        Log::info("Total dosen di database: " . $allDosens->count());

        foreach ($allDosens as $dosen) {
            Log::info("DB Dosen: {$dosen->nama}");
        }

        foreach ($lines as $lineNumber => $line) {
            $cleanLine = $this->cleanText($line);

            // Cek berbagai keyword yang mungkin mengandung nama dosen
            $dosenKeywords = ['PENGAJAR', 'DOSEN', 'LECTURER', 'OLEH'];
            $containsDosenKeyword = false;

            foreach ($dosenKeywords as $keyword) {
                if (stripos($cleanLine, $keyword) !== false) {
                    $containsDosenKeyword = true;
                    break;
                }
            }

            if ($containsDosenKeyword) {
                $extractedNames = $this->extractPossibleNames($cleanLine);

                $debugInfo[] = [
                    'line_number' => $lineNumber + 1,
                    'original_line' => $line,
                    'cleaned_line' => $cleanLine,
                    'extracted_names' => $extractedNames
                ];

                Log::info("Line {$lineNumber}: '$cleanLine'");
                Log::info("Extracted names: " . json_encode($extractedNames));

                // Test matching untuk setiap nama yang diekstrak
                foreach ($extractedNames as $name) {
                    $match = $this->findDosenMatch($name, $allDosens, 40);
                    if ($match) {
                        Log::info("MATCH FOUND for '$name': {$match->nama}");
                    } else {
                        Log::info("NO MATCH for '$name'");
                    }
                }
            }
        }

        Log::info('Dosen extraction debug summary:', $debugInfo);

        return $debugInfo;
    }

    /**
     * Bersihkan nama dosen dari hasil OCR
     */
    private function cleanDosenName($name)
    {
        // Hapus titik-titik berlebihan di awal dan akhir
        $name = preg_replace('/^\.+/', '', $name);
        $name = preg_replace('/\.+$/', '', $name);

        // Hapus karakter yang tidak dibutuhkan
        $name = preg_replace('/[^\p{L}\p{N}\s\.]/u', '', $name);

        // Hapus kata-kata yang biasanya bukan nama
        $stopWords = ['PENGAJAR', 'DOSEN', 'LECTURER', 'NAMA', 'OLEH', 'BY'];
        foreach ($stopWords as $stopWord) {
            $name = preg_replace('/\b' . $stopWord . '\b/i', '', $name);
        }

        // Bersihkan spasi berlebih
        $name = preg_replace('/\s+/', ' ', $name);

        return trim($name);
    }

    /**
     * Ekstraksi kemungkinan nama dari line OCR
     */
    private function extractPossibleNames($line)
    {
        $names = [];

        // Pattern untuk mendeteksi nama yang mungkin
        $namePatterns = [
            // Nama dengan format Kapital diikuti huruf kecil
            '/\b([A-Z][a-z]{2,}(?:\s+[A-Z][a-z]*\.?){1,4})\b/',
            // Nama dengan inisial
            '/\b([A-Z][a-z]+(?:\s+[A-Z]\.?){1,2})\b/',
            // Nama lengkap dengan gelar
            '/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})\b/',
        ];

        foreach ($namePatterns as $pattern) {
            if (preg_match_all($pattern, $line, $matches)) {
                foreach ($matches[1] as $match) {
                    $cleanName = $this->cleanDosenName($match);
                    if (strlen($cleanName) >= 3 && strlen($cleanName) <= 50) {
                        $names[] = $cleanName;
                    }
                }
            }
        }

        // Hapus duplikat
        return array_unique($names);
    }

    /**
     * Ekstraksi NPM dari baris teks
     */
    private function extractNPMFromLine($line)
    {
        $patterns = [
            '/\b(10\d{5})\b/',           // 7-digit starting with 10
            '/\b(10\d{6})\b/',           // 8-digit starting with 10
            '/\b(\d{7})\b/',             // Any 7-digit number
            '/\b(1[0-9]{6})\b/',         // 7-digit starting with 1
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Ekstraksi tanda kehadiran
     */

    private function cleanAttendancePart($attendancePart)
    {
        // Hapus nama mahasiswa yang mungkin masih tersisa
        $attendancePart = preg_replace('/^[A-Za-z\s\.]{3,50}/', '', $attendancePart);

        // Hapus angka NPM yang mungkin muncul lagi
        $attendancePart = preg_replace('/\b10\d{5,6}\b/', '', $attendancePart);

        // Bersihkan spasi berlebih tapi pertahankan struktur
        $attendancePart = preg_replace('/\s{3,}/', '  ', $attendancePart);

        return trim($attendancePart);
    }

    private function detectAttendancePattern($attendancePart)
    {
        // Split berdasarkan spasi atau separator lain yang konsisten
        $segments = preg_split('/\s{2,}/', $attendancePart);

        $detectedMarks = [];

        foreach ($segments as $index => $segment) {
            $segment = trim($segment);

            if (empty($segment)) continue;

            // Analisis setiap segment
            if (preg_match('/[✓✔√∨vVνH]/u', $segment)) {
                $detectedMarks[$index] = 'hadir';
            } elseif (preg_match('/[×xX✗✖TSA]/i', $segment)) {
                $detectedMarks[$index] = 'tidak_hadir';
            } elseif (preg_match('/[−–—\-_]/u', $segment)) {
                $detectedMarks[$index] = 'kosong';
            }
        }

        return $detectedMarks;
    }

    private function extractAttendanceMarks($line, $npmPosition, $npm)
    {
        $jamKuliah = [
            '07.30-08.20',
            '08.20-09.10',
            '09.30-10.20',
            '10.20-11.10',
            '11.10-12.00',
            '13.00-13.50',
            '13.50-14.40',
            '14.40-15.30'
        ];

        // Ambil bagian setelah NPM untuk analisis kehadiran
        $attendancePart = substr($line, $npmPosition + strlen($npm));

        // Bersihkan bagian attendance dengan lebih teliti
        $attendancePart = $this->cleanAttendancePart($attendancePart);

        Log::info("Analyzing attendance for NPM $npm: '$attendancePart'");
        Log::info("Original line: '$line'");

        $marks = [];

        // METODE 1: ANALISIS BERDASARKAN POSISI KARAKTER DENGAN JARAK TETAP
        $cleanPart = preg_replace('/\s+/', ' ', $attendancePart);
        $chars = preg_split('//u', $cleanPart, -1, PREG_SPLIT_NO_EMPTY);

        $markPositions = [];
        foreach ($chars as $index => $char) {
            if (preg_match('/[✓✔√∨vVνH]/u', $char)) {
                $markPositions[] = $index;
            }
        }

        // Map posisi ke jam kuliah
        foreach ($markPositions as $index => $position) {
            if ($index < count($jamKuliah)) {
                $marks[$jamKuliah[$index]] = '✓';
            }
        }

        // METODE 2: ANALISIS BERDASARKAN SEGMENT/KOLOM
        if (empty($marks)) {
            $segments = preg_split('/\s{2,}/', $attendancePart);
            $markIndex = 0;

            foreach ($segments as $segment) {
                $segment = trim($segment);
                if (empty($segment)) continue;

                if (preg_match('/[✓✔√∨vVνH]/u', $segment)) {
                    if ($markIndex < count($jamKuliah)) {
                        $marks[$jamKuliah[$markIndex]] = '✓';
                    }
                }
                $markIndex++;

                if ($markIndex >= count($jamKuliah)) break;
            }
        }

        // METODE 3: REGEX PATTERN MATCHING DENGAN POSISI
        if (empty($marks)) {
            preg_match_all('/([✓✔√∨vVνH])/u', $attendancePart, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as $index => $match) {
                if ($index < count($jamKuliah)) {
                    $marks[$jamKuliah[$index]] = '✓';
                }
            }
        }

        // METODE 4: ANALISIS BERDASARKAN JARAK KARAKTER (untuk tabel yang rapi)
        if (empty($marks)) {
            $charArray = str_split($attendancePart);
            $currentJamIndex = 0;
            $charCount = 0;

            foreach ($charArray as $char) {
                if (preg_match('/[✓✔√∨vVνH]/u', $char)) {
                    if ($currentJamIndex < count($jamKuliah)) {
                        $marks[$jamKuliah[$currentJamIndex]] = '✓';
                        $currentJamIndex++;
                    }
                } elseif (preg_match('/\s/', $char)) {
                    $charCount++;
                    // Setiap 3-5 spasi mungkin kolom baru
                    if ($charCount >= 3) {
                        $currentJamIndex++;
                        $charCount = 0;
                    }
                }

                if ($currentJamIndex >= count($jamKuliah)) break;
            }
        }

        // VALIDASI DAN KOREKSI
        // Jika hanya ada 1 tanda, berikan minimal 2 jam (sesuai umumnya kuliah)
        if (count($marks) === 1) {
            $existingJams = array_keys($marks);
            $lastJamIndex = array_search(end($existingJams), $jamKuliah);

            if ($lastJamIndex !== false && $lastJamIndex + 1 < count($jamKuliah)) {
                $marks[$jamKuliah[$lastJamIndex + 1]] = '✓';
            }
        }

        // Jika tidak ada tanda sama sekali, coba deteksi pattern lain
        if (empty($marks)) {
            // Cek apakah ada huruf 'H' untuk hadir
            if (preg_match('/\bH\b/i', $attendancePart)) {
                $marks[$jamKuliah[0]] = '✓';
                $marks[$jamKuliah[1]] = '✓';
            }

            // Cek pola spasi yang mungkin menunjukkan kolom kosong vs berisi
            $spacingPattern = preg_split('/\S+/', $attendancePart);
            if (count($spacingPattern) >= 3) {
                // Ada kemungkinan tabel dengan spacing
                for ($i = 0; $i < min(2, count($jamKuliah)); $i++) {
                    $marks[$jamKuliah[$i]] = '✓';
                }
            }
        }

        Log::info("Final marks for $npm: " . json_encode($marks));
        return $marks;
    }

    /**
     * Debug function untuk testing deteksi kehadiran
     */
    public function debugAttendanceDetection(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|max:2048',
        ]);

        try {
            // Proses OCR
            $uploadedFile = $request->file('gambar');
            $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            $tempDir = storage_path('app/temp');

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $uploadedFile->move($tempDir, $filename);
            $fullPath = $tempDir . '/' . $filename;

            $ocr = new TesseractOCR($fullPath);
            $ocr->executable('C:\Users\warar\AppData\Local\Programs\Tesseract-OCR\tesseract.exe')
                ->lang('ind+eng')
                ->psm(6)
                ->oem(1);

            $text = $ocr->run();
            $text = $this->cleanOCRText($text);
            $lines = explode("\n", $text);

            // Analisis struktur tabel
            $tableStructure = $this->analyzeTableStructure($lines);

            $debugResults = [
                'table_structure' => $tableStructure,
                'npm_detections' => [],
                'raw_lines' => []
            ];

            // Simpan semua baris untuk analisis
            foreach ($lines as $index => $line) {
                $debugResults['raw_lines'][$index] = [
                    'line' => $line,
                    'cleaned' => $this->cleanText($line)
                ];
            }

            // Analisis setiap NPM yang terdeteksi
            foreach ($lines as $lineIndex => $line) {
                $npm = $this->extractNPMFromLine($line);

                if ($npm) {
                    $npmPosition = strpos($line, $npm);
                    $attendancePart = substr($line, $npmPosition + strlen($npm));
                    $cleanedPart = $this->cleanAttendancePart($attendancePart);

                    // Test berbagai metode
                    $standardMarks = $this->extractAttendanceMarks($line, $npmPosition, $npm);
                    $columnMarks = $this->extractAttendanceByColumnPosition($line, $npm, $tableStructure);

                    $debugResults['npm_detections'][] = [
                        'line_number' => $lineIndex + 1,
                        'npm' => $npm,
                        'original_line' => $line,
                        'attendance_part_raw' => $attendancePart,
                        'attendance_part_cleaned' => $cleanedPart,
                        'standard_method_result' => $standardMarks,
                        'column_method_result' => $columnMarks,
                        'character_analysis' => $this->analyzeCharacters($cleanedPart),
                        'spacing_analysis' => $this->analyzeSpacing($cleanedPart)
                    ];
                }
            }

            // Cleanup
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'success' => true,
                'debug_results' => $debugResults,
                'ocr_text' => $text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Analisis karakter dalam attendance part
     */
    private function analyzeCharacters($text)
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $analysis = [];

        foreach ($chars as $index => $char) {
            $analysis[$index] = [
                'char' => $char,
                'unicode' => bin2hex(mb_convert_encoding($char, 'UTF-32', 'UTF-8')),
                'is_checkmark' => preg_match('/[✓✔√∨vVνH]/u', $char) ? true : false,
                'is_space' => preg_match('/\s/', $char) ? true : false,
                'is_dash' => preg_match('/[−–—\-_]/', $char) ? true : false
            ];
        }

        return $analysis;
    }

    /**
     * Analisis spacing pattern
     */
    private function analyzeSpacing($text)
    {
        // Split berdasarkan non-space characters
        $segments = preg_split('/\S+/', $text);
        $words = preg_split('/\s+/', trim($text));

        return [
            'total_length' => strlen($text),
            'space_segments' => $segments,
            'word_segments' => $words,
            'space_count' => substr_count($text, ' '),
            'consecutive_spaces' => preg_match_all('/\s{2,}/', $text, $matches) ? $matches[0] : []
        ];
    }


    private function analyzeTableStructure($lines)
    {
        $headerInfo = [
            'jam_columns' => [],
            'column_positions' => [],
            'table_start_line' => -1
        ];

        $jamPattern = '/(\d{2}\.\d{2}-\d{2}\.\d{2})/';
        foreach ($lines as $lineIndex => $line) {
            if (preg_match_all($jamPattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
                $headerInfo['table_start_line'] = $lineIndex;
                foreach ($matches[0] as $match) {
                    $jam = $match[0];
                    $position = $match[1];
                    $headerInfo['jam_columns'][] = $jam;
                    $headerInfo['column_positions'][$jam] = $position;
                }
                // Asumsi ada minimal 8 kolom jam (sesuai gambar)
                if (count($headerInfo['jam_columns']) >= 8) {
                    break;
                }
            }
        }

        // Jika tidak terdeteksi, gunakan default jam kuliah
        if (empty($headerInfo['jam_columns'])) {
            $headerInfo['jam_columns'] = [
                '07.30-08.20',
                '08.20-09.10',
                '09.30-10.20',
                '10.20-11.10',
                '11.10-12.00',
                '13.00-13.50',
                '13.50-14.40',
                '14.40-15.30'
            ];
            $headerInfo['column_positions'] = array_fill_keys($headerInfo['jam_columns'], 0); // Placeholder
        }

        Log::info("Table structure: " . json_encode($headerInfo));
        return $headerInfo;
    }

    private function extractAttendanceByColumnPosition($line, $npm, $tableStructure)
    {
        $marks = [];

        if (empty($tableStructure['column_positions'])) {
            Log::warning("No table structure found, falling back to basic extraction");
            return $this->extractAttendanceMarks($line, strpos($line, $npm), $npm);
        }

        $npmPosition = strpos($line, $npm);
        if ($npmPosition === false) {
            return $marks;
        }

        // Analisis setiap kolom jam berdasarkan posisi
        foreach ($tableStructure['column_positions'] as $jam => $basePosition) {
            // Estimasi posisi kolom pada baris mahasiswa
            // Asumsi: posisi kolom relatif sama dengan header
            $estimatedPosition = $npmPosition + ($basePosition - $npmPosition) + 20; // offset untuk nama

            // Ambil karakter di sekitar posisi estimasi (toleransi ±3 karakter)
            $startPos = max(0, $estimatedPosition - 3);
            $endPos = min(strlen($line), $estimatedPosition + 3);

            if ($startPos < $endPos) {
                $columnContent = substr($line, $startPos, $endPos - $startPos);

                // Cek tanda kehadiran di area kolom ini
                if (preg_match('/[✓✔√∨vVνH]/u', $columnContent)) {
                    $marks[$jam] = '✓';
                    Log::info("Mark found for NPM $npm at jam $jam: '$columnContent'");
                }
            }
        }

        return $marks;
    }


    private function analyzeClassSchedulePattern($allAttendanceMarks)
    {
        $jamKuliah = [
            '07.30-08.20',
            '08.20-09.10',
            '09.30-10.20',
            '10.20-11.10',
            '11.10-12.00',
            '13.00-13.50',
            '13.50-14.40',
            '14.40-15.30'
        ];

        $jamFrequency = [];

        // Hitung frekuensi setiap jam dari semua mahasiswa
        foreach ($allAttendanceMarks as $npm => $marks) {
            foreach ($marks as $jam => $mark) {
                if (!isset($jamFrequency[$jam])) {
                    $jamFrequency[$jam] = 0;
                }
                $jamFrequency[$jam]++;
            }
        }

        // Urutkan berdasarkan frekuensi
        arsort($jamFrequency);

        // Ambil jam yang paling sering muncul sebagai pattern kelas
        $classPattern = array_keys($jamFrequency);

        // Minimal 2 jam, maksimal 4 jam
        $classPattern = array_slice($classPattern, 0, min(4, max(2, count($classPattern))));

        Log::info("Class schedule pattern: " . json_encode($classPattern));
        Log::info("Jam frequency: " . json_encode($jamFrequency));

        return $classPattern;
    }

    private function extractSpecialAttendance($line, $npmPosition, $npm)
    {
        $attendancePart = substr($line, $npmPosition + strlen($npm));
        $attendancePart = preg_replace('/^[a-zA-Z\s]+/', '', $attendancePart);

        $sakit = 0;
        $izin = 0;
        $alpha = 0;

        // Hitung kemunculan huruf S, I, A
        $sakit = substr_count(strtolower($attendancePart), 's');
        $izin = substr_count(strtolower($attendancePart), 'i');
        $alpha = substr_count(strtolower($attendancePart), 'a');

        // Cari juga pattern angka setelah huruf
        if (preg_match('/s\s*(\d+)/i', $attendancePart, $match)) {
            $sakit = max($sakit, intval($match[1]));
        }
        if (preg_match('/i\s*(\d+)/i', $attendancePart, $match)) {
            $izin = max($izin, intval($match[1]));
        }
        if (preg_match('/a\s*(\d+)/i', $attendancePart, $match)) {
            $alpha = max($alpha, intval($match[1]));
        }

        Log::info("Special attendance for $npm - S:$sakit, I:$izin, A:$alpha");

        return [
            'sakit' => min($sakit, 8), // Max 8 jam per hari
            'izin' => min($izin, 8),
            'alpha' => min($alpha, 8)
        ];
    }

    public function index()
    {
        // Ambil data kelas dengan urutan yang sama seperti di KelasController
        $kelasList = Kelas::orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return view('absensi', compact('kelasList'));
    }

    public function scan(Request $request)
    {
        // Log untuk debugging
        Log::info('Request method: ' . $request->method());

        // Tampilkan form jika request GET
        if ($request->isMethod('get')) {
            return view('absensi');
        }

        $request->validate([
            'gambar' => 'required|image|max:2048',
        ]);

        try {
            $uploadedFile = $request->file('gambar');
            $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            $tempDir = storage_path('app/temp');

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $originalPath = $tempDir . '/' . $filename;
            $uploadedFile->move($tempDir, $filename);

            Log::info("Original Path: $originalPath");
            if (!file_exists($originalPath)) {
                throw new \Exception("File gambar tidak ditemukan di: $originalPath");
            }

            $processedPath = $tempDir . '/processed_' . $filename;

            // Preprocessing gambar menggunakan GD
            $imageResource = imagecreatefromstring(file_get_contents($originalPath));
            if ($imageResource === false) {
                throw new \Exception("Gagal membuat resource gambar dari: $originalPath");
            }
            imagefilter($imageResource, IMG_FILTER_GRAYSCALE);           // Konversi ke hitam putih
            imagefilter($imageResource, IMG_FILTER_BRIGHTNESS, 10);      // Tambah brightness
            imagefilter($imageResource, IMG_FILTER_CONTRAST, -20);       // Tingkatkan kontras (negatif untuk kontras lebih tajam)
            imagejpeg($imageResource, $processedPath);                   // Simpan sebagai JPEG
            imagedestroy($imageResource);                                // Bebaskan memori

            if (!file_exists($processedPath)) {
                throw new \Exception("Gagal memproses gambar di: $processedPath");
            }

            $ocr = new TesseractOCR($processedPath);
            $ocr->executable('C:\Users\warar\AppData\Local\Programs\Tesseract-OCR\tesseract.exe')
                ->lang('ind+eng')
                ->psm(6)
                ->oem(1)
                ->config('--dpi 300', '-c preserve_interword_spaces=1');

            $text = $ocr->run();
            $text = $this->cleanOCRText($text);

            $extractedData = $this->extractDataFromOCR($text);

            if (file_exists($originalPath)) unlink($originalPath);
            if (file_exists($processedPath)) unlink($processedPath);

            if (empty($extractedData['npmList'])) {
                $extractedData = array_merge([
                    'error' => 'Tidak dapat mendeteksi mahasiswa yang terdaftar dari gambar. Pastikan gambar jelas dan mengandung NPM yang valid.',
                    'mata_kuliah' => '',
                    'kode_mk' => '',
                    'mata_kuliah_display' => '',
                    'dosen' => '',
                    'tanggal' => date('Y-m-d'),
                    'minggu' => '1',
                    'kelas' => '',
                    'kelas_id' => null,
                    'pokok_pembahasan' => '',
                    'npmList' => [],
                    'namaList' => [],
                    'jamListFiltered' => [],
                    'absenOCR' => [],
                    'sakitOCR' => [],
                    'izinOCR' => [],
                    'alpaOCR' => [],
                ], $extractedData);

                return view('absensi_koreksi', $extractedData);
            }

            $extractedData['npmList'] = collect($extractedData['npmList'])->sort()->values()->all();

            return view('absensi_koreksi', $extractedData);
        } catch (\Exception $e) {
            Log::error("Error in scan: " . $e->getMessage());
            return view('absensi_koreksi', [
                'error' => 'Terjadi kesalahan saat memproses gambar: ' . $e->getMessage(),
                'mata_kuliah' => '',
                'kode_mk' => '',
                'mata_kuliah_display' => '',
                'dosen' => '',
                'tanggal' => date('Y-m-d'),
                'minggu' => '1',
                'kelas' => '',
                'kelas_id' => null,
                'pokok_pembahasan' => '',
                'npmList' => [],
                'namaList' => [],
                'jamListFiltered' => [],
                'absenOCR' => [],
                'sakitOCR' => [],
                'izinOCR' => [],
                'alpaOCR' => [],
            ]);
        }
    }

    private function cleanOCRText($text)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n", $text);
        return trim($text);
    }

    private function extractDataFromOCR($text)
    {
        $lines = explode("\n", $text);
        $semester = null;

        // Load database untuk pencocokan
        $allMahasiswas = Mahasiswa::all();
        $allDosens = Dosen::all();
        $allMatkuls = Matkul::all();
        $allKelass = Kelas::all();

        // Variabel hasil deteksi
        $detectedMatkul = null;
        $detectedDosen = null;
        $detectedKelas = null;
        $tanggal = '';
        $minggu = '';
        $pokok_pembahasan = '';

        // === EKSTRAKSI HEADER DATA ===
        foreach ($lines as $line) {
            $cleanLine = $this->cleanText($line);

            // Ekstraksi Mata Kuliah - IMPROVED LOGIC mengikuti pattern Kelas
            if (!$detectedMatkul) {
                $matkulPatterns = [
                    '/(?:KODE\s*MK|KODE\s*MATA\s*KULIAH|MK)[\s\/:]*(.+)/i',
                    '/(?:MATA\s*KULIAH|MATKUL|COURSE)[\s\/:]*(.+)/i',
                    '/(?:SUBJECT)[\s\/:]*(.+)/i',
                ];

                foreach ($matkulPatterns as $pattern) {
                    if (preg_match($pattern, $cleanLine, $match)) {
                        $matkulRaw = trim($match[1]);

                        Log::info("Mata kuliah pattern matched: $pattern");
                        Log::info("Mata kuliah candidate: '$matkulRaw' from line: '$cleanLine'");

                        // Validasi panjang
                        if (strlen($matkulRaw) >= 2 && strlen($matkulRaw) <= 100) {
                            $matkulMatch = $this->findMatkulMatch($matkulRaw, $allMatkuls);
                            if ($matkulMatch) {
                                Log::info("MATA KULIAH MATCH FOUND: {$matkulMatch->kode} - {$matkulMatch->nama}");
                                $detectedMatkul = $matkulMatch;
                                break; // Hanya break dari loop pattern, bukan loop line
                            } else {
                                Log::info("NO MATA KULIAH MATCH for: '$matkulRaw'");
                            }
                        }
                    }
                }

                // Jika masih belum ketemu, coba ekstraksi manual dari seluruh line
                if (!$detectedMatkul) {
                    $detectedMatkul = null;
                    $possibleMatkuls = $this->extractPossibleMatkul($cleanLine);
                    foreach ($possibleMatkuls as $candidate) {
                        $matkulMatch = $this->findMatkulMatch($candidate, $allMatkuls);
                        if ($matkulMatch) {
                            Log::info("MANUAL EXTRACTION MATA KULIAH MATCH: {$matkulMatch->kode} - {$matkulMatch->nama} from: '$candidate'");
                            $detectedMatkul = $matkulMatch;
                            break; // Break dari loop possible matkuls saja
                        }
                    }
                }
            }

            if (empty($semester)) {
                if (preg_match('/(?:SEMESTER|SMT)[\s\/:]*(\d+)/i', $cleanLine, $match)) {
                    $semesterNum = intval($match[1]);
                    if ($semesterNum >= 1 && $semesterNum <= 8) {
                        $semester = $semesterNum;
                        Log::info("Semester berhasil diekstrak: '$semester' dari baris: '$cleanLine'");
                    }
                }
            }

            // Ekstraksi Dosen - Improved dengan pattern yang lebih fleksibel
            if (!$detectedDosen) {
                $dosenPatterns = [
                    // Pattern untuk format "PENGAJAR : ... Nama Dosen ..."
                    '/PENGAJAR\s*:?\s*\.+\s*([A-Za-z\s\.]+?)\s*\.*/i',
                    '/PENGAJAR\s*:?\s*([A-Za-z\s\.]{3,50}?)(?:\s+[A-Z]{2,}|$)/i',
                    '/PENGAJAR\s*:?\s*([A-Za-z\s\.]{3,50})/i',

                    // Pattern alternatif
                    '/(?:DOSEN|LECTURER)\s*:?\s*([A-Za-z\s\.]{3,50})/i',
                    '/(?:NAMA\s*DOSEN)\s*:?\s*([A-Za-z\s\.]{3,50})/i',
                    '/(?:PENGAJAR|DOSEN)\s*[\s\/:]*([A-Za-z\s\.]{3,50})/i',

                    // Pattern untuk menangkap nama di tengah kalimat
                    '/(?:oleh|by)\s+([A-Za-z\s\.]{3,50}?)(?:\s|$)/i',
                    '/([A-Z][a-z]+(?:\s+[A-Z][a-z]*\.?){1,3})\s+(?:S\.Pd|M\.Pd|Dr|Prof)/i',
                ];

                foreach ($dosenPatterns as $pattern) {
                    if (preg_match($pattern, $cleanLine, $match)) {
                        $dosenRaw = trim($match[1]);

                        // Bersihkan nama dosen
                        $dosenRaw = $this->cleanDosenName($dosenRaw);

                        Log::info("Pattern matched: $pattern");
                        Log::info("Raw extracted: '$dosenRaw' from line: '$cleanLine'");

                        // Validasi panjang nama
                        if (strlen($dosenRaw) >= 3 && strlen($dosenRaw) <= 100) {
                            // Coba exact match dulu
                            $dosenMatch = $this->findDosenExactMatch($dosenRaw, $allDosens);

                            if (!$dosenMatch) {
                                // Kalau tidak ada exact match, coba fuzzy match dengan threshold rendah
                                $dosenMatch = $this->findDosenMatch($dosenRaw, $allDosens, 40);
                            }

                            if ($dosenMatch) {
                                Log::info("DOSEN MATCH FOUND: {$dosenMatch->nama} from input: '$dosenRaw'");
                                $detectedDosen = $dosenMatch;
                                break; // Hanya break dari loop pattern, lanjutkan ke line berikutnya
                            } else {
                                Log::info("NO MATCH for: '$dosenRaw'");
                                // Coba dengan kata pertama dan kedua saja
                                $words = explode(' ', $dosenRaw);
                                if (count($words) >= 2) {
                                    $shortName = $words[0] . ' ' . $words[1];
                                    $dosenMatch = $this->findDosenMatch($shortName, $allDosens, 40);
                                    if ($dosenMatch) {
                                        Log::info("SHORT NAME MATCH: {$dosenMatch->nama} from: '$shortName'");
                                        $detectedDosen = $dosenMatch;
                                        break; // Hanya break dari loop pattern
                                    }
                                }
                            }
                        }
                    }
                }

                // Jika masih belum ketemu, coba ekstraksi manual dari seluruh line
                if (!$detectedDosen) {
                    $possibleNames = $this->extractPossibleNames($cleanLine);
                    foreach ($possibleNames as $name) {
                        $dosenMatch = $this->findDosenMatch($name, $allDosens, 40);
                        if ($dosenMatch) {
                            Log::info("MANUAL EXTRACTION MATCH: {$dosenMatch->nama} from: '$name'");
                            $detectedDosen = $dosenMatch;
                            break; // Break dari loop possible names saja
                        }
                    }
                }
            }

            // Ekstraksi Kelas - Diperbaiki agar tidak terganggu ekstraksi dosen
            if (!$detectedKelas) {
                $kelasPatterns = [
                    '/(?:KELAS|CLASS)[\s\/:]*([A-Z0-9\s\-]+)/i',
                    '/(?:TINGKAT)[\s\/:]*(\d+[A-Z]*\s*[A-Z]*\s*[A-Z0-9]*)/i',
                    '/DAFTAR\s+HADIR\s+PERKULIAHAN\s+KELAS\s+(.+?)(?:\s|$)/i',
                ];

                foreach ($kelasPatterns as $pattern) {
                    if (preg_match($pattern, $cleanLine, $match)) {
                        $kelasCandidate = trim($match[1]);

                        Log::info("Kelas pattern matched: $pattern");
                        Log::info("Kelas candidate: '$kelasCandidate' from line: '$cleanLine'");

                        $kelasMatch = $this->findKelasMatch($kelasCandidate, $allKelass);
                        if ($kelasMatch) {
                            Log::info("KELAS MATCH FOUND: {$kelasMatch->tingkat} {$kelasMatch->prodi_singkatan} {$kelasMatch->nama}");
                            $detectedKelas = $kelasMatch;
                            break; // Hanya break dari loop pattern
                        } else {
                            Log::info("NO KELAS MATCH for: '$kelasCandidate'");
                        }
                    }
                }
            }

            // Ekstraksi Tanggal
            if (empty($tanggal)) {
                if (preg_match('/(?:TANGGAL|DATE)[\s\/:]*(.+)/i', $cleanLine, $match)) {
                    $tanggal = $this->parseFlexibleDate($match[1]);
                    Log::info("Tanggal extracted: '$tanggal' from line: '$cleanLine'");
                }
            }

            // Ekstraksi Minggu
            if (empty($minggu)) {
                if (preg_match('/(?:MINGGU\s*PROGRAM\s*KE?|PERTEMUAN)[\s\/:]*(\d+)/i', $cleanLine, $match)) {
                    $mingguNum = intval($match[1]);
                    if ($mingguNum >= 1 && $mingguNum <= 16) {
                        $minggu = $mingguNum;
                        Log::info("Minggu extracted: '$minggu' from line: '$cleanLine'");
                    }
                }
            }
        }

        $pokok_pembahasan = $this->extractPokokPembahasan($text);
        // Debug: jika tidak ada mata kuliah terdeteksi
        if (!$detectedMatkul) {
            Log::info("TIDAK ADA MATA KULIAH TERDETEKSI - Debug info:");
            foreach ($lines as $lineIndex => $line) {
                $cleanLine = $this->cleanText($line);
                // Cek line yang mungkin mengandung mata kuliah
                if (
                    stripos($cleanLine, 'MATA') !== false ||
                    stripos($cleanLine, 'KULIAH') !== false ||
                    stripos($cleanLine, 'MATKUL') !== false ||
                    stripos($cleanLine, 'COURSE') !== false ||
                    stripos($cleanLine, 'KODE') !== false ||
                    preg_match('/\b[A-Z]{2,4}\d{3,4}\b/', $cleanLine)
                ) {

                    Log::info("Line $lineIndex dengan keyword mata kuliah: '$cleanLine'");

                    // Test ekstraksi pada line ini
                    $candidates = $this->extractPossibleMatkul($cleanLine);
                    Log::info("Candidates found: " . json_encode($candidates));

                    foreach ($candidates as $candidate) {
                        $testMatch = $this->findMatkulMatch($candidate, $allMatkuls, 30); // threshold lebih rendah untuk debug
                        if ($testMatch) {
                            Log::info("DEBUG MATCH found: {$testMatch->kode} - {$testMatch->nama} for candidate: '$candidate'");
                        }
                    }
                }
            }
        }

        // Debug: jika tidak ada kelas terdeteksi
        if (!$detectedKelas) {
            Log::info("TIDAK ADA KELAS TERDETEKSI - Debug info:");
            foreach ($lines as $lineIndex => $line) {
                $cleanLine = $this->cleanText($line);
                if (stripos($cleanLine, 'KELAS') !== false || stripos($cleanLine, 'CLASS') !== false) {
                    Log::info("Line $lineIndex dengan 'KELAS': '$cleanLine'");
                }
            }
        }

        // Debug: jika tidak ada dosen terdeteksi, jalankan debugging
        if (!$detectedDosen) {
            Log::info("TIDAK ADA DOSEN TERDETEKSI - Menjalankan debug");
            $this->debugDosenExtraction($text, $allDosens);
        }

        // === ANALISIS STRUKTUR TABEL TERLEBIH DAHULU ===
        $tableStructure = $this->analyzeTableStructure($lines);
        Log::info("Table structure analysis result: " . json_encode($tableStructure));

        // === EKSTRAKSI DATA MAHASISWA ===
        $npmList = [];
        $namaList = [];
        $absenOCR = [];
        $sakitOCR = [];
        $izinOCR = [];
        $alpaOCR = [];
        $kelasMahasiswa = null;
        $detectedStudents = [];
        $allAttendanceMarks = [];

        foreach ($lines as $lineIndex => $line) {
            $npm = $this->extractNPMFromLine($line);

            if ($npm) {
                $mahasiswa = $this->findMahasiswaByNPM($npm, $allMahasiswas);

                // HANYA proses jika mahasiswa DITEMUKAN di database
                if ($mahasiswa) {
                    // Avoid duplicate
                    if (in_array($mahasiswa->npm, $detectedStudents)) {
                        continue;
                    }

                    $detectedStudents[] = $mahasiswa->npm;

                    // Set kelas reference dari mahasiswa pertama
                    if (!$kelasMahasiswa) {
                        $kelasMahasiswa = $mahasiswa->kelas_id;
                        // Update collection mahasiswa hanya dari kelas yang sama
                        $allMahasiswas = Mahasiswa::where('kelas_id', $kelasMahasiswa)->get();
                    }

                    // Gunakan data dari database
                    $npmList[] = $mahasiswa->npm;
                    $namaList[$mahasiswa->npm] = $mahasiswa->nama;

                    // IMPROVED: Ekstraksi kehadiran per mahasiswa
                    // IMPROVED: Ekstraksi kehadiran per mahasiswa dengan analisis struktur tabel
                    $npmPosition = strpos($line, $npm);

                    // Coba metode berdasarkan struktur tabel dulu
                    $attendanceMarks = $this->extractAttendanceByColumnPosition($line, $mahasiswa->npm, $tableStructure);

                    // Fallback ke metode lama jika tidak berhasil
                    if (empty($attendanceMarks)) {
                        $attendanceMarks = $this->extractAttendanceMarks($line, $npmPosition, $mahasiswa->npm);
                    }

                    Log::info("Final attendance marks for {$mahasiswa->npm}: " . json_encode($attendanceMarks));

                    $specialAttendance = $this->extractSpecialAttendance($line, $npmPosition, $mahasiswa->npm);

                    $absenOCR[$mahasiswa->npm] = $attendanceMarks;
                    $allAttendanceMarks[$mahasiswa->npm] = $attendanceMarks;

                    $sakitOCR[$mahasiswa->npm] = $specialAttendance['sakit'];
                    $izinOCR[$mahasiswa->npm] = $specialAttendance['izin'];
                    $alpaOCR[$mahasiswa->npm] = $specialAttendance['alpha'];
                }
            }
        }

        // Tambahkan mahasiswa lain dari kelas yang sama (jika sudah ada referensi kelas)
        if ($kelasMahasiswa) {
            $allMahasiswasKelas = Mahasiswa::where('kelas_id', $kelasMahasiswa)->get();

            foreach ($allMahasiswasKelas as $mahasiswa) {
                if (!in_array($mahasiswa->npm, $npmList)) {
                    $npmList[] = $mahasiswa->npm;
                    $namaList[$mahasiswa->npm] = $mahasiswa->nama;
                    $absenOCR[$mahasiswa->npm] = [];
                    $sakitOCR[$mahasiswa->npm] = 0;
                    $izinOCR[$mahasiswa->npm] = 0;
                    $alpaOCR[$mahasiswa->npm] = 0;
                }
            }
        }

        if (empty($semester)) {
            if (preg_match('/(?:SEMESTER|SMT)[\s\/:]*(\d+)/i', $cleanLine, $match)) {
                $semesterNum = intval($match[1]);
                if ($semesterNum >= 1 && $semesterNum <= 8) {
                    $semester = $semesterNum;
                    Log::info("Semester extracted: '$semester' from line: '$cleanLine'");
                }
            }
        }

        // Validasi: harus ada minimal 1 mahasiswa
        if (empty($npmList)) {
            return [
                'error' => 'Tidak ada mahasiswa yang terdeteksi di database.',
                'mata_kuliah' => $detectedMatkul ? $detectedMatkul->nama : '',
                'kode_mk' => $detectedMatkul ? $detectedMatkul->kode : '',
                'mata_kuliah_display' => $detectedMatkul ? "{$detectedMatkul->nama} / {$detectedMatkul->kode}" : '',
                'dosen' => $detectedDosen ? $detectedDosen->nama : '',
                'tanggal' => date('Y-m-d'),
                'minggu' => '1',
                'semester' => $semester ?: '',
                'kelas' => $detectedKelas ? "{$detectedKelas->tingkat} {$detectedKelas->prodi_singkatan} {$detectedKelas->nama}" : '',
                'kelas_id' => $kelasMahasiswa,
                'pokok_pembahasan' => $pokok_pembahasan,
                'npmList' => [],
                'namaList' => [],
                'jamListFiltered' => [],
                'absenOCR' => [],
                'sakitOCR' => [],
                'izinOCR' => [],
                'alpaOCR' => [],
            ];
        }

        // === PROCESSING JAM KULIAH ===
        $jamKuliah = [
            '07.30-08.20',
            '08.20-09.10',
            '09.30-10.20',
            '10.20-11.10',
            '11.10-12.00',
            '13.00-13.50',
            '13.50-14.40',
            '14.40-15.30'
        ];

        $jamListFiltered = [];
        if (!empty($allAttendanceMarks)) {
            $jamListFiltered = $this->analyzeClassSchedulePattern($allAttendanceMarks);
        }

        // // Fallback: gunakan 2 jam pertama jika tidak ada pattern terdeteksi
        // if (empty($jamListFiltered)) {
        //     $jamListFiltered = array_slice($jamKuliah, 0, 2);
        // }

        // IMPROVED: Sesuaikan kehadiran mahasiswa dengan pattern kelas
        foreach ($npmList as $npm) {
            // Jika mahasiswa tidak memiliki data kehadiran, berikan default berdasarkan pattern
            if (empty($absenOCR[$npm])) {
                // Hitung jam hadir berdasarkan sakit/izin/alpha
                $totalSpecial = $sakitOCR[$npm] + $izinOCR[$npm] + $alpaOCR[$npm];
                $maxJamHadir = count($jamListFiltered) - $totalSpecial;

                // Berikan jam hadir sebanyak maxJamHadir
                $jamCount = 0;
                foreach ($jamListFiltered as $jam) {
                    if ($jamCount < max(0, $maxJamHadir)) {
                        $absenOCR[$npm][$jam] = '✓';
                        $jamCount++;
                    }
                }
            } else {
                // Sesuaikan dengan pattern kelas sambil mempertahankan data individual
                $currentMarks = $absenOCR[$npm];
                $newMarks = [];

                // Pertahankan jam yang sudah terdeteksi
                foreach ($currentMarks as $jam => $mark) {
                    if (in_array($jam, $jamListFiltered)) {
                        $newMarks[$jam] = $mark;
                    }
                }

                // Tambahkan jam lain jika diperlukan (dengan mempertimbangkan sakit/izin/alpha)
                $totalSpecial = $sakitOCR[$npm] + $izinOCR[$npm] + $alpaOCR[$npm];
                $maxJamHadir = count($jamListFiltered) - $totalSpecial;

                if (count($newMarks) < $maxJamHadir) {
                    $jamTersisa = array_diff($jamListFiltered, array_keys($newMarks));
                    $jamTambahan = array_slice($jamTersisa, 0, $maxJamHadir - count($newMarks));

                    foreach ($jamTambahan as $jam) {
                        $newMarks[$jam] = '✓';
                    }
                }

                $absenOCR[$npm] = $newMarks;
            }
        }

        // Validasi dan koreksi data kehadiran
        $this->validateAndCorrectAttendance($npmList, $absenOCR, $sakitOCR, $izinOCR, $alpaOCR, $jamListFiltered);

        // Deteksi anomali untuk logging
        $anomalies = $this->detectAnomalousPatterns($npmList, $absenOCR, $sakitOCR, $izinOCR, $alpaOCR);


        // === FINALISASI DATA ===
        return [
            'ocr_text' => $text,
            'mata_kuliah' => $detectedMatkul ? $detectedMatkul->nama : '',
            'kode_mk' => $detectedMatkul ? $detectedMatkul->kode : '',
            'mata_kuliah_display' => $detectedMatkul ? "{$detectedMatkul->nama} / {$detectedMatkul->kode}" : '',
            'dosen' => $detectedDosen ? $detectedDosen->nama : '',
            'tanggal' => $tanggal ?: date('Y-m-d'),
            'minggu' => $minggu ?: '1',
            'semester' => $semester ?: '1',
            'kelas' => $detectedKelas ? "{$detectedKelas->tingkat} {$detectedKelas->prodi_singkatan} {$detectedKelas->nama}" : '',
            'kelas_id' => $kelasMahasiswa,
            'pokok_pembahasan' => $pokok_pembahasan,
            'npmList' => $npmList,
            'namaList' => $namaList,
            'jamListFiltered' => $jamListFiltered,
            'absenOCR' => $absenOCR,
            'sakitOCR' => $sakitOCR,
            'izinOCR' => $izinOCR,
            'alpaOCR' => $alpaOCR,

            // Debug info
            'detected_info' => [
                'matkul_detected' => $detectedMatkul ? true : false,
                'dosen_detected' => $detectedDosen ? true : false,
                'kelas_detected' => $detectedKelas ? true : false,
                'matkul_info' => $detectedMatkul ? "{$detectedMatkul->kode} - {$detectedMatkul->nama}" : null,
                'dosen_info' => $detectedDosen ? $detectedDosen->nama : null,
                'kelas_info' => $detectedKelas ? "{$detectedKelas->tingkat} {$detectedKelas->prodi_singkatan} {$detectedKelas->nama}" : null,
            ]
        ];
    }

    private function validateAndCorrectAttendance($npmList, &$absenOCR, &$sakitOCR, &$izinOCR, &$alpaOCR, $jamListFiltered)
    {
        foreach ($npmList as $npm) {
            $totalJamKuliah = count($jamListFiltered);
            $jamHadir = count($absenOCR[$npm] ?? []);
            $sakit = $sakitOCR[$npm] ?? 0;
            $izin = $izinOCR[$npm] ?? 0;
            $alpha = $alpaOCR[$npm] ?? 0;

            $totalAbsensi = $jamHadir + $sakit + $izin + $alpha;

            // Koreksi jika total melebihi jam kuliah
            if ($totalAbsensi > $totalJamKuliah) {
                Log::warning("Total absensi melebihi jam kuliah untuk NPM $npm: $totalAbsensi > $totalJamKuliah");

                // Prioritas: pertahankan jam hadir, kurangi yang lain
                $sisaJam = $totalJamKuliah - $jamHadir;

                if ($sisaJam <= 0) {
                    // Jika jam hadir sudah melebihi, reset sakit/izin/alpha
                    $sakitOCR[$npm] = 0;
                    $izinOCR[$npm] = 0;
                    $alpaOCR[$npm] = 0;
                } else {
                    // Distribusikan sisa jam ke sakit/izin/alpha secara proporsional
                    $totalSpecial = $sakit + $izin + $alpha;
                    if ($totalSpecial > 0) {
                        $sakitOCR[$npm] = min($sakit, floor($sisaJam * ($sakit / $totalSpecial)));
                        $izinOCR[$npm] = min($izin, floor($sisaJam * ($izin / $totalSpecial)));
                        $alpaOCR[$npm] = min($alpha, $sisaJam - $sakitOCR[$npm] - $izinOCR[$npm]);
                    }
                }
            }

            // Koreksi jika jam hadir kurang dari yang seharusnya
            $expectedJamHadir = $totalJamKuliah - $sakitOCR[$npm] - $izinOCR[$npm] - $alpaOCR[$npm];
            if ($jamHadir < $expectedJamHadir) {
                // Tambahkan jam hadir yang kurang
                $kurangJam = $expectedJamHadir - $jamHadir;
                $jamTersedia = array_diff($jamListFiltered, array_keys($absenOCR[$npm] ?? []));

                $jamTambahan = array_slice($jamTersedia, 0, $kurangJam);
                foreach ($jamTambahan as $jam) {
                    $absenOCR[$npm][$jam] = '✓';
                }
            }

            Log::info("Validation result for NPM $npm: Hadir=" . count($absenOCR[$npm] ?? []) .
                ", Sakit={$sakitOCR[$npm]}, Izin={$izinOCR[$npm]}, Alpha={$alpaOCR[$npm]}");
        }
    }

    /**
     * Deteksi pola absensi yang tidak wajar untuk koreksi otomatis
     */
    private function detectAnomalousPatterns($npmList, $absenOCR, $sakitOCR, $izinOCR, $alpaOCR)
    {
        $anomalies = [];

        foreach ($npmList as $npm) {
            $jamHadir = count($absenOCR[$npm] ?? []);
            $sakit = $sakitOCR[$npm] ?? 0;
            $izin = $izinOCR[$npm] ?? 0;
            $alpha = $alpaOCR[$npm] ?? 0;

            // Deteksi anomali
            $issues = [];

            // Tidak ada kehadiran sama sekali
            if ($jamHadir === 0 && $sakit === 0 && $izin === 0 && $alpha === 0) {
                $issues[] = 'no_attendance_data';
            }

            // Terlalu banyak sakit
            if ($sakit > 4) {
                $issues[] = 'excessive_sick_leave';
            }

            // Kombinasi aneh (hadir + sakit/izin/alpha di waktu bersamaan)
            if ($jamHadir > 0 && ($sakit > 0 || $izin > 0 || $alpha > 0)) {
                $issues[] = 'conflicting_attendance';
            }

            if (!empty($issues)) {
                $anomalies[$npm] = $issues;
            }
        }

        if (!empty($anomalies)) {
            Log::warning("Anomalous attendance patterns detected: " . json_encode($anomalies));
        }

        return $anomalies;
    }

    /**
     * Ekstraksi tanda kehadiran dengan analisis posisi kolom yang lebih akurat
     */
    private function extractAttendanceMarksAdvanced($line, $npmPosition, $npm)
    {
        $jamKuliah = [
            '07.30-08.20',
            '08.20-09.10',
            '09.30-10.20',
            '10.20-11.10',
            '11.10-12.00',
            '13.00-13.50',
            '13.50-14.40',
            '14.40-15.30'
        ];

        // Analisis struktur kolom berdasarkan spasi
        $attendancePart = substr($line, $npmPosition + strlen($npm));

        // Hapus nama mahasiswa (biasanya setelah NPM)
        $namePattern = '/^[\s]*[A-Za-z\s\.]{3,50}[\s]*/';
        $attendancePart = preg_replace($namePattern, '', $attendancePart);

        Log::info("Advanced analysis for NPM $npm: '$attendancePart'");

        $marks = [];

        // Metode 1: Analisis berdasarkan posisi karakter dengan jarak tertentu
        $cleanPart = preg_replace('/\s+/', ' ', $attendancePart);
        $characters = str_split(trim($cleanPart));

        $markIndex = 0;
        foreach ($characters as $char) {
            if ($markIndex >= count($jamKuliah)) break;

            if (in_array($char, ['✓', '✔', 'v', 'V', '√', '∨'])) {
                $marks[$jamKuliah[$markIndex]] = '✓';
                $markIndex++;
            } elseif (in_array($char, ['−', '–', '—', '-', '_', ' '])) {
                // Skip untuk jam ini
                $markIndex++;
            } elseif (preg_match('/[a-zA-Z]/', $char)) {
                // Karakter huruf mungkin nama, skip
                continue;
            }
        }

        // Metode 2: Jika metode 1 tidak berhasil, gunakan regex pattern
        if (empty($marks)) {
            // Cari semua tanda check dalam urutan
            preg_match_all('/([✓✔vV√∨])/', $attendancePart, $matches, PREG_OFFSET_CAPTURE);

            $markIndex = 0;
            foreach ($matches[0] as $match) {
                if ($markIndex < count($jamKuliah)) {
                    $marks[$jamKuliah[$markIndex]] = '✓';
                    $markIndex++;
                }
            }
        }

        // Pastikan minimal 1 jam jika ada tanda kehadiran
        if (!empty($marks) && count($marks) < 1) {
            $marks[$jamKuliah[0]] = '✓';
        }

        return $marks;
    }

    public function debugAttendanceExtraction(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|max:2048',
        ]);

        try {
            // Proses OCR seperti biasa
            $uploadedFile = $request->file('gambar');
            $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            $tempDir = storage_path('app/temp');

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $uploadedFile->move($tempDir, $filename);
            $fullPath = $tempDir . '/' . $filename;

            $ocr = new TesseractOCR($fullPath);
            $ocr->executable('C:\Users\warar\AppData\Local\Programs\Tesseract-OCR\tesseract.exe')
                ->lang('ind+eng')
                ->psm(6)
                ->oem(1);

            $text = $ocr->run();
            $text = $this->cleanOCRText($text);
            $lines = explode("\n", $text);

            // Debug info
            $debugInfo = [
                'total_lines' => count($lines),
                'npm_detections' => [],
                'attendance_analysis' => []
            ];

            // Analisis struktur tabel
            $tableStructure = $this->analyzeTableStructure($lines);

            foreach ($lines as $lineIndex => $line) {
                $npm = $this->extractNPMFromLine($line);

                if ($npm) {
                    $npmPosition = strpos($line, $npm);
                    $attendancePart = substr($line, $npmPosition + strlen($npm));
                    $cleanedPart = $this->cleanAttendancePart($attendancePart);

                    // Test berbagai metode
                    $standardMarks = $this->extractAttendanceMarks($line, $npmPosition, $npm);
                    $columnMarks = $this->extractAttendanceByColumnPosition($line, $npm, $tableStructure);
                    $advancedMarks = $this->extractAttendanceMarksAdvanced($line, $npmPosition, $npm);
                    $specialAttendance = $this->extractSpecialAttendance($line, $npmPosition, $npm);

                    $debugInfo['npm_detections'][] = [
                        'line_number' => $lineIndex + 1,
                        'npm' => $npm,
                        'original_line' => $line,
                        'attendance_part_raw' => $attendancePart,
                        'attendance_part_cleaned' => $cleanedPart,
                        'standard_method_result' => $standardMarks,
                        'column_method_result' => $columnMarks,
                        'advanced_method_result' => $advancedMarks,
                        'special_attendance' => $specialAttendance,
                        'character_analysis' => $this->analyzeCharacters($cleanedPart),
                        'spacing_analysis' => $this->analyzeSpacing($cleanedPart)
                    ];
                }
            }

            // Cleanup
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'success' => true,
                'debug_info' => $debugInfo,
                'ocr_text' => $text
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Juga tambahkan method untuk debug khusus pokok pembahasan
    public function debugPokokPembahasan(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|max:2048',
        ]);

        try {
            // Proses file gambar
            $uploadedFile = $request->file('gambar');
            $filename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
            $tempDir = storage_path('app/temp');

            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $uploadedFile->move($tempDir, $filename);
            $fullPath = $tempDir . '/' . $filename;

            // OCR
            $ocr = new TesseractOCR($fullPath);
            $ocr->executable('C:\Users\warar\AppData\Local\Programs\Tesseract-OCR\tesseract.exe')
                ->lang('ind+eng')
                ->psm(6)
                ->oem(1);

            $text = $ocr->run();
            $text = $this->cleanOCRText($text);

            // Debug ekstraksi pokok pembahasan
            $pokokPembahasan = $this->extractPokokPembahasan($text);

            // Bersihkan file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return response()->json([
                'success' => true,
                'pokok_pembahasan' => $pokokPembahasan,
                'ocr_text' => $text,
                'lines' => explode("\n", $text)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function parseFlexibleDate($dateString)
    {
        try {
            if (!$dateString || strlen($dateString) < 5) {
                return date('Y-m-d');
            }

            // Filter non-date patterns
            if (
                preg_match('/^\d{7,}$/', $dateString) ||
                preg_match('/^[A-Z0-9]{2,}-[A-Z0-9]{3,}/', $dateString)
            ) {
                return date('Y-m-d');
            }

            $dateString = strtolower(trim($dateString));
            $dateString = preg_replace('/[,\.]/', '', $dateString);

            $months = [
                'januari' => '01',
                'februari' => '02',
                'maret' => '03',
                'april' => '04',
                'mei' => '05',
                'juni' => '06',
                'juli' => '07',
                'agustus' => '08',
                'september' => '09',
                'oktober' => '10',
                'november' => '11',
                'desember' => '12',
                'jan' => '01',
                'feb' => '02',
                'mar' => '03',
                'apr' => '04',
                'may' => '05',
                'jun' => '06',
                'jul' => '07',
                'aug' => '08',
                'sep' => '09',
                'oct' => '10',
                'nov' => '11',
                'dec' => '12'
            ];

            $patterns = [
                '/(\d{1,2})[\-\/](\d{1,2})[\-\/](\d{4})/',
                '/(\d{4})[\-\/](\d{1,2})[\-\/](\d{1,2})/',
                '/(\d{1,2})\s+(\w+)\s+(\d{4})/',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $dateString, $matches)) {
                    if ($pattern === '/(\d{4})[\-\/](\d{1,2})[\-\/](\d{1,2})/') {
                        $year = intval($matches[1]);
                        $month = intval($matches[2]);
                        $day = intval($matches[3]);
                    } else if ($pattern === '/(\d{1,2})\s+(\w+)\s+(\d{4})/') {
                        $day = intval($matches[1]);
                        $monthText = strtolower($matches[2]);
                        $year = intval($matches[3]);

                        if (!isset($months[$monthText])) continue;
                        $month = intval($months[$monthText]);
                    } else {
                        $day = intval($matches[1]);
                        $month = intval($matches[2]);
                        $year = intval($matches[3]);
                    }

                    // Validasi
                    if (
                        $year >= 2020 && $year <= 2030 &&
                        $month >= 1 && $month <= 12 &&
                        $day >= 1 && $day <= 31
                    ) {
                        $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

                        try {
                            $carbonDate = Carbon::createFromFormat('Y-m-d', $formattedDate);
                            if ($carbonDate && $carbonDate->format('Y-m-d') === $formattedDate) {
                                return $formattedDate;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                }
            }

            return date('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Date parsing failed: ' . $e->getMessage());
            return date('Y-m-d');
        }
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'mata_kuliah' => 'required|string|max:255',
                'kode_mk' => 'nullable|string|max:20',
                'dosen' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'minggu' => 'required|string|max:10',
                'kelas' => 'required|string|max:50',
                'kelas_id' => 'required|integer|exists:kelass,id',
                'semester' => 'required|integer|min:1|max:8',
                'pokok_pembahasan' => 'nullable|string|max:500',
                'mahasiswa' => 'required|array|min:1',
                'mahasiswa.*.npm' => 'required|string',
                'mahasiswa.*.nama' => 'required|string',
                'mahasiswa.*.absensi' => 'required|array',
                'mahasiswa.*.sakit' => 'required|integer|min:0|max:8',
                'mahasiswa.*.izin' => 'required|integer|min:0|max:8',
                'mahasiswa.*.alpa' => 'required|integer|min:0|max:8',
            ]);

            foreach ($request->mahasiswa as $mahasiswaData) {
                Absensi::create([
                    'npm' => $mahasiswaData['npm'],
                    'nama' => $mahasiswaData['nama'],
                    'mata_kuliah' => $validated['mata_kuliah'],
                    'kode_mk' => $validated['kode_mk'],
                    'dosen' => $validated['dosen'],
                    'tanggal' => $validated['tanggal'],
                    'minggu' => $validated['minggu'],
                    'kelas' => $validated['kelas'],
                    'kelas_id' => $validated['kelas_id'],
                    'semester' => $validated['semester'],
                    'pokok_pembahasan' => $validated['pokok_pembahasan'] ?? null,
                    'jam_hadir' => json_encode($mahasiswaData['absensi']),
                    'sakit' => $mahasiswaData['sakit'],
                    'izin' => $mahasiswaData['izin'],
                    'alpa' => $mahasiswaData['alpa'],
                ]);
            }

            return redirect()->route('admin.absensi.index')
                ->with('success', 'Data absensi berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Error saving attendance: ' . $e->getMessage());

            return back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }



    /**
     * Tampilkan data absensi untuk kelas tertentu
     */
    public function show($kelas_id)
    {
        // Ambil data kelas
        $kelas = Kelas::findOrFail($kelas_id);

        // Ambil semua data absensi untuk kelas ini, diurutkan berdasarkan tanggal dan minggu terbaru
        $absensiData = Absensi::where('kelas_id', $kelas_id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('minggu', 'desc')
            ->orderBy('mata_kuliah')
            ->get();

        // Group data berdasarkan tanggal, minggu, dan mata kuliah untuk menampilkan per pertemuan
        $groupedAbsensi = $absensiData->groupBy(function ($item) {
            return $item->tanggal . '_' . $item->minggu . '_' . $item->mata_kuliah;
        });

        // Ambil semua mahasiswa di kelas ini untuk referensi
        $allMahasiswa = Mahasiswa::where('kelas_id', $kelas_id)->get();

        // Statistik
        $totalPertemuan = $groupedAbsensi->count();
        $totalMataKuliah = $absensiData->unique('mata_kuliah')->count();
        $totalMahasiswa = $absensiData->unique('npm')->count();

        return view('admin.data_absensi.index', compact(
            'kelas',
            'absensiData',
            'groupedAbsensi',
            'allMahasiswa',
            'totalPertemuan',
            'totalMataKuliah',
            'totalMahasiswa'
        ));
    }
}
