<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\API\LanguageController;
use App\Http\Requests\LanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LanguageControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $languageController;

    public function setUp(): void
    {
        parent::setUp();
        $this->languageController = new LanguageController();
    }

    public function testIndex()
    {
        // Create actual test records in the database
        $language1 = new Language(['name' => 'English', 'code' => 'en']);
        $language1->save();

        $language2 = new Language(['name' => 'Spanish', 'code' => 'es']);
        $language2->save();

        // Call the controller method with actual data
        $response = $this->languageController->index();

        // Assert response is correct type
        $this->assertInstanceOf(AnonymousResourceCollection::class, $response);

        // Get the underlying collection from resource collection
        $responseData = $response->resource;

        // Verify that the collection contains correct number of items
        $this->assertEquals(2, $responseData->count());
    }

    public function testStore()
    {
        // Create mock request with validated data
        $request = Mockery::mock(LanguageRequest::class);
        $validatedData = ['name' => 'French', 'code' => 'fr'];
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        // Call the controller method with the actual database
        $response = $this->languageController->store($request);

        // Assert that a resource is returned
        $this->assertInstanceOf(LanguageResource::class, $response);

        // Assert that the resource contains the correct data
        $this->assertEquals($validatedData['name'], $response->resource->name);
        $this->assertEquals($validatedData['code'], $response->resource->code);

        // Verify the record was actually created in the database
        $this->assertDatabaseHas('languages', $validatedData);
    }

    public function testUpdate()
    {
        // Create a partial mock of the Language model
        $language = Mockery::mock(Language::class)->makePartial();
        $language->id = 4; // Setting attributes now works
        $language->name = 'German';
        $language->code = 'de';

        // Create mock request with validated data
        $request = Mockery::mock(LanguageRequest::class);
        $validatedData = ['name' => 'German (Updated)', 'code' => 'de'];
        $request->shouldReceive('validated')
            ->once()
            ->andReturn($validatedData);

        // Mock update method
        $language->shouldReceive('update')
            ->once()
            ->with($validatedData)
            ->andReturn(true); // Simulate successful update

        // Mock fresh method to return updated language data
        $updatedLanguage = new Language(['id' => 4, 'name' => 'German (Updated)', 'code' => 'de']);
        $language->shouldReceive('fresh')
            ->once()
            ->andReturn($updatedLanguage);

        // Call the controller method
        $response = $this->languageController->update($request, $language);

        // Assert that a resource is returned
        $this->assertInstanceOf(LanguageResource::class, $response);

        // Assert that the resource contains the correct data
        $this->assertEquals($updatedLanguage->id, $response->resource->id);
        $this->assertEquals($validatedData['name'], $response->resource->name);
        $this->assertEquals($validatedData['code'], $response->resource->code);
    }

    public function testDestroy()
    {
        // Create a partial mock of the Language model
        $language = Mockery::mock(Language::class)->makePartial();

        // Mock delete method
        $language->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Call the controller method
        $response = $this->languageController->destroy($language);

        // Assert that the response is correct
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('{}', $response->getContent()); // Expect JSON empty object
        // Optionally, decode and check for empty array or object
        $this->assertEquals([], json_decode($response->getContent(), true));
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

