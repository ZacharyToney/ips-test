<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }


    public function testPostWithNoParameters()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', []);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function testPostWithInvalidEmail()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => 'no']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid email given.'
            ]);
    }

    public function testEmailThatIsNotInDatabaseOrInfusionsot()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => 'test@test.com']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function testEmailThatIsInInfusionsoftButNotInDatabase()
    {
        $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => '5b113a088c8a4@test.com']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    //Do not run test if you haven't migrated and seeded the database with the example in ApiController
    // public function testValidEmailAndInDatabase()
    // {
    //     $response = $this->json('POST', '/api/module_reminder_assigner', ['contact_email' => '5b2a829e6306b@test.com']);
    //     $response
    //         ->assertStatus(200)
    //         ->assertJson([
    //             'success' => true,
    //             'message' => 'Contact: 210 Has had the tag: 120 attached to their account.',
    //         ]);
    // }
}
