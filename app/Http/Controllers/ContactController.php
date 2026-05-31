<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contact\StoreContactRequest;
use App\Services\Contact\ContactService;

class ContactController extends Controller
{
    public function __construct(protected ContactService $contacts)
    {
    }

    public function index()
    {
        return view('client.contact');
    }

    public function store(StoreContactRequest $request)
    {
        $this->contacts->create($request->validated());

        return redirect()
            ->route('contact.index')
            ->with('success', 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.');
    }
}
