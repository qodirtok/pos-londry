<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic smoke test: the app boots and the root route exists.
     * (Root requires auth, so it redirects instead of returning 200.)
     */
    public function test_the_application_responds(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $this->assertNotEmpty($response->headers->get('Location'));
    }
}