<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ContactRepositoryInterface;
use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ContactRepository implements ContactRepositoryInterface
{
    public function newestQuery(): Builder
    {
        return Contact::orderBy('created_at', 'desc');
    }

    public function newestPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Contact::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Contact
    {
        return Contact::create($data);
    }

    public function update(Contact $contact, array $data): bool
    {
        return $contact->update($data);
    }
}
