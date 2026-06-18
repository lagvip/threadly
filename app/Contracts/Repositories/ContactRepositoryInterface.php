<?php

namespace App\Contracts\Repositories;

use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ContactRepositoryInterface
{
    public function newestPaginated(int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Contact;

    public function update(Contact $contact, array $data): bool;
}
