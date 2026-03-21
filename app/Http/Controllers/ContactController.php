<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        return view('pages.contact');
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        // Lưu vào DB
        $contact = Contact::create($data);

        // Gửi email cho admin
        Mail::send([], [], function ($message) use ($contact) {
            $message->to('senhongocopp@gmail.com')
                ->subject('Tin nhắn mới từ khách hàng')
                ->html(
                    "Tên: {$contact->name}<br>
             Email: {$contact->email}<br>
             Nội dung: {$contact->message}"
                );
        });


        return back()->with('success', 'Gửi tin nhắn thành công!');
    }
}
