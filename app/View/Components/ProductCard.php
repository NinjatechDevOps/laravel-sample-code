<?php

namespace App\View\Components;

use App\Models\Product;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProductCard extends Component
{
    /**
     * ProductCard constructor.
     */
    public function __construct(
        public Product $product
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): Application|Factory|View
    {
        return view('components.product-card');
    }
}
