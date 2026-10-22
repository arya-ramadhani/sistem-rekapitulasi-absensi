<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OCRController extends Controller
{
    public function form()
    {
        return view('upload');
    }

    public function process(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png',
        ]);

        // Simpan file ke storage/app/temp
        $path = $request->file('image')->store('temp');

        // Dapatkan path absolut ke file tersebut
        $fullPath = storage_path('app/' . $path);

        // Jalankan Tesseract
        $ocr = (new TesseractOCR($fullPath))
            ->executable('C:\Users\warar\AppData\Local\Programs\Tesseract-OCR\tesseract.exe')
            ->run();

        return view('result', ['text' => $ocr]);
    }
}

