<?php

namespace Tests\Feature\Download;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DownloadTest extends TestCase
{
    public function test_it_can_download_terms_and_conditions_pdf()
    {
        $response = $this->get('/download/terms-and-conditions');
        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=terms-and-conditions.pdf');
    }
}