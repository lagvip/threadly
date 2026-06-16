<?php

namespace App\Services\Contact;

use App\Contracts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;

class ContactService
{
    public function __construct(protected ContactRepositoryInterface $contacts) {}

    public function adminIndexData(): array
    {
        return [
            'contacts' => $this->contacts->newestPaginated(),
        ];
    }

    public function create(array $data): void
    {
        $this->contacts->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
        ]);
    }

    public function toggleReplied(Contact $contact): void
    {
        $newRepliedStatus = ! $contact->replied;

        $this->contacts->update($contact, [
            'replied' => $newRepliedStatus,
            'replied_at' => $newRepliedStatus ? now() : null,
        ]);
    }
}
