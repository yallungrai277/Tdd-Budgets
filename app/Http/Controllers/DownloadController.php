<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
    public function termsAndConditions()
    {
        $filePath = public_path('assets/pdf/terms-and-conditions.pdf');
        abort_if(!file_exists($filePath), 404, 'Not found');
        return response()->download(public_path('assets/pdf/terms-and-conditions.pdf'));
    }
}