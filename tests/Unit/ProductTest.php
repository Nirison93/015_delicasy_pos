<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_product_has_sale_items_relationship(): void
    {
        $product = new Product();

        $this->assertInstanceOf(HasMany::class, $product->saleItems());
    }
}
