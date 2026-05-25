<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

class ArticlesListingPaginator extends LengthAwarePaginator
{
    public function __construct(
        $items,
        int $total,
        int $perPage,
        int $currentPage,
        protected int $computedLastPage,
        array $options = [],
    ) {
        parent::__construct($items, $total, $perPage, $currentPage, $options);
    }

    public function lastPage(): int
    {
        return max(1, $this->computedLastPage);
    }
}
