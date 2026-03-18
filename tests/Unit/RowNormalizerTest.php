<?php

namespace Tests\Unit;

use App\Services\Import\RowNormalizer;
use Tests\TestCase;

class RowNormalizerTest extends TestCase
{
    private RowNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new RowNormalizer();
    }

    public function test_normalize_returns_dto_with_correct_fields(): void
    {
        $row = [
            'Title'       => '  My Book  ',
            'ISBN'        => '9781234567890',
            'Description' => 'Some text',
            'Edition'     => '2',
            'Year'        => '2020',
            'Pages'       => '300',
            'Format'      => 'Hardcover',
            'Country'     => 'Ukraine',
            'Publisher'   => 'Publisher Name',
            'Authors'     => 'John Doe;Jane Doe',
            'Genre'       => 'Fiction;Drama',
        ];

        $dto = $this->normalizer->normalize($row);

        $this->assertNotNull($dto);
        $this->assertSame('My Book', $dto->title);
        $this->assertSame('9781234567890', $dto->isbn);
        $this->assertSame(2, $dto->edition);
        $this->assertSame(2020, $dto->year);
        $this->assertSame(300, $dto->pages);
        $this->assertSame(['John Doe', 'Jane Doe'], $dto->authors);
        $this->assertSame(['Fiction', 'Drama'], $dto->genres);
    }

    public function test_normalize_returns_null_when_title_is_empty(): void
    {
        $dto = $this->normalizer->normalize(['Title' => '   ']);

        $this->assertNull($dto);
    }

    public function test_normalize_returns_null_when_title_is_missing(): void
    {
        $dto = $this->normalizer->normalize([]);

        $this->assertNull($dto);
    }

    public function test_normalize_ignores_non_numeric_edition(): void
    {
        $row = ['Title' => 'Book', 'Edition' => 'abc'];

        $dto = $this->normalizer->normalize($row);

        $this->assertNull($dto->edition);
    }

    public function test_normalize_ignores_non_numeric_year(): void
    {
        $row = ['Title' => 'Book', 'Year' => 'unknown'];

        $dto = $this->normalizer->normalize($row);

        $this->assertNull($dto->year);
    }

    public function test_normalize_splits_authors_by_semicolon(): void
    {
        $row = ['Title' => 'Book', 'Authors' => 'Alice; Bob ; Charlie'];

        $dto = $this->normalizer->normalize($row);

        $this->assertSame(['Alice', 'Bob', 'Charlie'], $dto->authors);
    }

    public function test_normalize_returns_empty_arrays_when_authors_and_genres_missing(): void
    {
        $dto = $this->normalizer->normalize(['Title' => 'Book']);

        $this->assertSame([], $dto->authors);
        $this->assertSame([], $dto->genres);
    }
}
