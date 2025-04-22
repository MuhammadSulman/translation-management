<?php

namespace Tests\Unit\Http\Controllers;

use App\Contracts\TranslationCacheContract;
use App\Contracts\TranslationSearchContract;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class TranslationControllerUnitTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexUsesSearchService(): void
    {
        $request = Request::create('/api/translations', 'GET');
        $searchService = Mockery::mock(TranslationSearchContract::class);
        $cacheService = Mockery::mock(TranslationCacheContract::class);

        // Simulate paginated result
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 15,
            currentPage: 1,
            options: ['path' => $request->url(), 'query' => $request->query()]
        );

        $searchService->shouldReceive('search')
            ->once()
            ->with($request)
            ->andReturn($paginator);

        $controller = new \App\Http\Controllers\API\TranslationController($searchService, $cacheService);

        $response = $controller->index($request);

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $response);
    }

}
