<?php

namespace App\View\Composers;

use App\Services\Client\Layout\ClientLayoutService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct(
        protected ClientLayoutService $layout,
    ) {}

    public function compose(View $view): void
    {
        $view->with($this->layout->viewData(Auth::id()));
    }
}
