<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/books
    // -------------------------------------------------------------------------

    public function test_index_returns_paginated_list(): void
    {
        Book::factory(3)->create();

        $response = $this->getJson('/api/books');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'authors', 'publisher', 'year'],
                ],
                'meta',
                'links',
            ]);
    }

    public function test_index_does_not_expose_detail_fields(): void
    {
        Book::factory()->create();

        $response = $this->getJson('/api/books');

        $response->assertOk();

        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('description', $item);
        $this->assertArrayNotHasKey('isbn', $item);
        $this->assertArrayNotHasKey('genres', $item);
    }

    // -------------------------------------------------------------------------
    // GET /api/books/{id}
    // -------------------------------------------------------------------------

    public function test_show_returns_all_fields(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/books/{$book->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'title', 'authors', 'publisher', 'year',
                    'description', 'edition', 'pages', 'format',
                    'country', 'isbn', 'genres',
                ],
            ]);
    }

    public function test_show_returns_404_for_missing_book(): void
    {
        $this->getJson('/api/books/999')->assertNotFound();
    }

    // -------------------------------------------------------------------------
    // POST /api/books
    // -------------------------------------------------------------------------

    public function test_store_creates_book(): void
    {
        $publisher = Publisher::factory()->create();
        $authors   = Author::factory(2)->create();
        $genres    = Genre::factory(2)->create();

        $payload = [
            'title'          => 'New Book Title',
            'description'    => 'Some description',
            'published_date' => '2020-01-01',
            'pages'          => 300,
            'publisher_id'   => $publisher->id,
            'author_ids'     => $authors->pluck('id')->toArray(),
            'genre_ids'      => $genres->pluck('id')->toArray(),
        ];

        $response = $this->postJson('/api/books', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New Book Title');

        $this->assertDatabaseHas('books', ['title' => 'New Book Title']);
    }

    public function test_store_requires_title(): void
    {
        $this->postJson('/api/books', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_publisher_exists(): void
    {
        $this->postJson('/api/books', [
            'title'        => 'Book',
            'publisher_id' => 9999,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['publisher_id']);
    }

    // -------------------------------------------------------------------------
    // PUT /api/books/{id}
    // -------------------------------------------------------------------------

    public function test_update_modifies_book(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title']);

        $response = $this->putJson("/api/books/{$book->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
    }

    public function test_update_syncs_authors(): void
    {
        $book    = Book::factory()->create();
        $authors = Author::factory(2)->create();

        $this->putJson("/api/books/{$book->id}", [
            'title'      => $book->title,
            'author_ids' => $authors->pluck('id')->toArray(),
        ])->assertOk();

        $this->assertCount(2, $book->fresh()->authors);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/books/{id}
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_book(): void
    {
        $book = Book::factory()->create();

        $this->deleteJson("/api/books/{$book->id}")->assertNoContent();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_destroy_returns_404_for_missing_book(): void
    {
        $this->deleteJson('/api/books/999')->assertNotFound();
    }
}
