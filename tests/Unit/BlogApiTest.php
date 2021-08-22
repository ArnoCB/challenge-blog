<?php

namespace Tests\Unit;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public string $user;
    public string $password;
    public string $token;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpFaker();

        $this->user = 'arno44@test.nl';
        $this->password = 'test';
    }

    public function test_user_register_login_logout(): void
    {
        $payload = [
            'name' => $this->user,
            'password' => $this->password
        ];

        $this->json('post', '/register', $payload)
            // Arno: I would expect 201, but this is what Tymon\JWTAuth returns
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                    "access_token",
                    "token_type",
                    "expires_in"
            ]);

        // test login
        $payload = [
            'name' => $this->user,
            'password' => $this->password
        ];

        $content = $this->json('post', '/login', $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                "access_token",
                "token_type",
                "expires_in"
            ])->getContent();

        $this->token = json_decode($content, true)['access_token'];

        // test logout
        $this->json('post', '/logout', ['token' => $this->token])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson([
                "message" => "Successfully logged out"
            ]);
    }

    public function test_blog_post_life_cycle(): void
    {
        $payload = [
            'name' => $this->user,
            'password' => $this->password
        ];

        $content = $this->json('post', '/register', $payload)
            ->getContent();

        $this->token = json_decode($content, true)['access_token'];

        $faker = Factory::create();

        $payload = [
            "title" => "this is a blog post",
            "summary" => $faker->text(),
            "content" => $faker->text(),
            "token" => $this->token
        ];

        // test make blog post
        $this->json('post', '/blogs', $payload)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertExactJson([
                "slug" => "this-is-a-blog-post"
            ])
            ->getContent();

        // test list blog posts
        $this->json('get', '/blogs',  ['token' => $this->token])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                '*' => [
                    'title',
                    'slug',
                    'summary'
                ]
            ]);

        // update the blog title
        $payload = [
            "title" => "This is a new title",
            "token" => $this->token
        ];

        $this->json('patch', '/blogs/' . "this-is-a-blog-post", $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(['message' => 'Article updated']);

        // test get single blog post
        $content = $this->json('get', '/blogs/' . "this-is-a-blog-post", ['token' => $this->token])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'title',
                'slug',
                'summary',
                'content',
                'createdOn'
            ])->content();

        // check if the title was updated
        $this->assertEquals(json_decode($content, true)['title'], "This is a new title" );

        // test get delete blog post
        $this->json('delete', '/blogs/' . "this-is-a-blog-post", ['token' => $this->token])
            ->assertStatus(Response::HTTP_OK)
            ->assertExactJson(['message' => 'Article deleted']);
    }
}
