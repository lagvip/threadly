<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class ContactRepository implements ContactRepositoryInterface
{
    public function newestQuery(): Builder
    {
        return Contact::orderBy('created_at', 'desc');
    }

    public function create(array $data): Contact
    {
        return Contact::create($data);
    }
}
