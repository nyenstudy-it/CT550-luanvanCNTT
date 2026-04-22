<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminContactController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Contact::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $contacts = $query->orderByRaw("CASE 
            WHEN status = 'pending' THEN 0
            WHEN status = 'read' THEN 1
            WHEN status = 'replied' THEN 2
            END ASC")
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.contacts.index', compact('contacts', 'status'));
    }

    public function show(Contact $contact)
    {
        if ($contact->status === 'pending') {
            $contact->update(['status' => 'read']);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function reply(Request $request, Contact $contact)
    {
        $request->validate([
            'reply' => 'required|string|max:5000',
            'send_email' => 'nullable|boolean',
        ]);

        $contact->update([
            'reply' => $request->reply,
            'reply_by' => Auth::id(),
            'replied_at' => now(),
            'status' => 'replied',
        ]);

        if ($request->input('send_email')) {
            try {
                \Illuminate\Support\Facades\Mail::send('mail.contact-reply', [
                    'contact' => $contact,
                    'adminName' => Auth::user()->name,
                    'replyText' => $request->reply,
                ], function ($message) use ($contact) {
                    $message->to($contact->email)
                        ->subject('Phản hồi từ cửa hàng OCOP - Liên hệ ' . $contact->id);
                });
            } catch (\Exception $e) {
                //
            }
        }

        return back()->with('success', 'Đã gửi phản hồi thành công!');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return back()->with('success', 'Đã xóa liên hệ thành công!');
    }

    public function markAsRead(Contact $contact)
    {
        if ($contact->status === 'pending') {
            $contact->update(['status' => 'read']);
        }

        return back()->with('success', 'Đã đánh dấu đã xem!');
    }

    public function statistics()
    {
        $pending = Contact::where('status', 'pending')->count();
        $read = Contact::where('status', 'read')->count();
        $replied = Contact::where('status', 'replied')->count();
        $total = Contact::count();

        return view('admin.contacts.statistics', compact('pending', 'read', 'replied', 'total'));
    }
}
