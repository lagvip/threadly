<?php

namespace App\Contracts\Repositories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

interface ContactRepositoryInterface
{
    public function newestQuery(): Builder;

    public function create(array $data): Contact;
}
