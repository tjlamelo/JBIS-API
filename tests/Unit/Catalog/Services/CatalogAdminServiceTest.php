<?php

declare(strict_types=1);

namespace Tests\Unit\Catalog\Services;

use App\Core\Domain\Catalog\Models\Benefit;
use App\Core\Domain\Catalog\Models\Category;
use App\Core\Domain\Catalog\Services\CatalogAdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\SqliteTestCase;

class CatalogAdminServiceTest extends SqliteTestCase
{
    use RefreshDatabase;

    private CatalogAdminService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CatalogAdminService::class);
    }

    #[Test]
    public function it_lists_configured_resources(): void
    {
        $resources = $this->service->listResources();

        $this->assertNotEmpty($resources);
        $this->assertContains('benefits', array_column($resources, 'key'));
        $this->assertContains('trades', array_column($resources, 'key'));
    }

    #[Test]
    public function it_throws_for_unknown_resource(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->service->resolve('unknown-resource');
    }

    #[Test]
    public function it_creates_a_benefit_with_auto_slug(): void
    {
        $item = $this->service->create('benefits', [
            'name' => ['fr' => 'Transport inclus', 'en' => 'Transport included'],
        ]);

        $this->assertInstanceOf(Benefit::class, $item);
        $this->assertSame('transport-included', $item->slug);
        $this->assertSame('Transport inclus', $item->getTranslation('name', 'fr'));
        $this->assertDatabaseHas('benefits', ['slug' => 'transport-included']);
    }

    #[Test]
    public function it_updates_translatable_fields(): void
    {
        $item = $this->service->create('benefits', [
            'name' => ['fr' => 'Prime', 'en' => 'Bonus'],
            'slug' => 'prime-'.Str::lower(Str::random(4)),
        ]);

        $updated = $this->service->update('benefits', $item, [
            'name' => ['fr' => 'Prime annuelle', 'en' => 'Annual bonus'],
        ]);

        $this->assertSame('Prime annuelle', $updated->getTranslation('name', 'fr'));
    }

    #[Test]
    public function it_deletes_a_benefit(): void
    {
        $item = $this->service->create('benefits', [
            'name' => ['fr' => 'À supprimer', 'en' => 'To delete'],
            'slug' => 'delete-me-'.Str::lower(Str::random(4)),
        ]);

        $this->service->delete('benefits', $item);

        $this->assertDatabaseMissing('benefits', ['id' => $item->id]);
    }

    #[Test]
    public function it_creates_a_trade_linked_to_category(): void
    {
        $category = Category::query()->create([
            'name' => ['fr' => 'BTP', 'en' => 'Construction'],
            'slug' => 'btp-'.Str::lower(Str::random(4)),
            'is_active' => true,
        ]);

        $trade = $this->service->create('trades', [
            'category_id' => $category->id,
            'name' => ['fr' => 'Maçon', 'en' => 'Mason'],
            'is_active' => true,
        ]);

        $this->assertSame($category->id, $trade->category_id);
        $this->assertSame('macon', $trade->slug);
        $this->assertDatabaseHas('trades', [
            'category_id' => $category->id,
            'slug' => 'macon',
        ]);
    }
}
