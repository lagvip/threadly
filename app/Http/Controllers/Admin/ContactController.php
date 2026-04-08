<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Support\Carbon;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function toggleReplied(Contact $contact)
    {
        $newRepliedStatus = !$contact->replied;
        
        $contact->update([
            'replied' => $newRepliedStatus,
            'replied_at' => $newRepliedStatus ? Carbon::now() : null,
        ]);

        return redirect()->route('listContact.show', $contact->id)
            ->with('success', 'Đã cập nhật trạng thái liên hệ');
    }
}
