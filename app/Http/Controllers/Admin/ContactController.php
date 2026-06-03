<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Services\Contact\ContactService;

class ContactController extends Controller
{
    public function __construct(protected ContactService $contacts)
    {
    }

    public function index()
    {
        return view('admin.contacts.index', $this->contacts->adminIndexData());
    }

    public function show(Contact $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function toggleReplied(Contact $contact)
    {
        $this->contacts->toggleReplied($contact);

        return redirect()
            ->route('listContact.show', $contact->id)
            ->with('success', 'Đã cập nhật trạng thái liên hệ');
    }
}
