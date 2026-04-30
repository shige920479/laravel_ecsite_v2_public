<?php

namespace Tests\Feature\Services\Customer;

use App\Models\Category;
use App\Models\ItemCategory;
use App\Models\SubCategory;
use App\Services\Customer\Item\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;
    
    #[Test]
    public function getTree_カテゴリー3階層のコレクションを取得する(): void
    {
        Cache::flush();
        
        $category = Category::factory()->create();
        $subCate = SubCategory::factory()->for($category)->create();
        $itemCate = ItemCategory::factory()->for($subCate)->create();

        $res = (new CategoryService())->getTree();

        $this->assertEquals($category->slug, $res->first()['slug']);
        $this->assertEquals($subCate->slug, $res->first()['children'][0]['slug']);
        $this->assertEquals($itemCate->slug, $res->first()['children'][0]['children'][0]['slug']);
    }
}
