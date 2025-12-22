<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function pdf()
    {
        $user = auth()->user();
        $data = [
            'title' => 'ข้อมูลพนักงาน',
            'date' => date('วันl ที่ j F Y เวลา : H:i:s'),
            'user' => $user
        ];
        
        $title = "ใบสมัครของ_{$user->userHasoneIdcard->name_th}";
        return view('pdf_form',compact('user','title'));





        
    }
}
