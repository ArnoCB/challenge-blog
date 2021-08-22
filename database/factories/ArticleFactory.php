<?php

namespace Database\Factories;

use App\Models\Article;
use App\Services\SlugFromString;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Article::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $title = $this->faker->text(50);

        return [
            'title' => $title,
            'slug' => SlugFromString::slugify($title),
            'summary' => $this->faker->text(200),
            'content' => $this->faker->text(2000)
        ];
    }
}
