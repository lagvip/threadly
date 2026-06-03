<?php

namespace App\View\Composers;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use Illuminate\View\View;

class HeaderComposer
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
    ) {
    }

    public function compose(View $view): void
    {
        $view->with('headerCategories', $this->categories->rootTree());
    }
}
