<?php

namespace App\Http\Controllers;

class PDFController extends Controller
{
    public function pdf($name_doc)
    {   
        $name = [
            'applicant_form' => 'ใบสมัครของ',
            'employment_form' => 'สัญญาจ้างงานของ',
            'non_disclosure_form' => 'สัญญาไม่เปิดเผยข้อมูลของบริษัทของ',
        ];
        $user = auth()->user();

        $title = "{$name[$name_doc]}_{$user->userHasoneIdcard->name_th}";
        return view("documents.{$name_doc}", compact('user','title'));

    }
}
