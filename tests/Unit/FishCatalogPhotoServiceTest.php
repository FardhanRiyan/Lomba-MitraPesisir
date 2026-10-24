<?php

namespace Tests\Unit;

use App\Services\FishCatalogPhotoService;
use PHPUnit\Framework\TestCase;

class FishCatalogPhotoServiceTest extends TestCase
{
    public function test_names_match_ignoring_case_whitespace_and_punctuation(): void
    {
        $this->assertSame(100, FishCatalogPhotoService::similarity('  Ikan-Tenggiri ', 'ikan tenggiri'));
    }

    public function test_small_typo_in_a_long_fish_name_is_recognized(): void
    {
        $this->assertGreaterThanOrEqual(90, FishCatalogPhotoService::similarity('Tenggiri', 'Tengiri'));
    }

    public function test_short_or_different_names_do_not_fuzzy_match(): void
    {
        $this->assertSame(0, FishCatalogPhotoService::similarity('Ikan', 'IkAnx'));
        $this->assertLessThan(90, FishCatalogPhotoService::similarity('Tenggiri', 'Tongkol'));
    }
}
