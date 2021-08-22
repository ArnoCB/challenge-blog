<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\SlugFromString;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * The overview is a get request that expects an array of blogs with the following fields:
     * ["title", "slug", "summary"]
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {

            $articles = Article::all('title', 'slug', 'summary');
        } catch (Exception $e) {

            // Don't return the error to the client.
            return response()->json(['error' => 'Unknown database error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

       return response()->json($articles, Response::HTTP_OK);
    }

    /**
     * Store a newly created blogpost. Return the slug of the new blogpost.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // we check the if the title is unique, as to avoid a non-unique slug
        $validator = Validator::make($request->all() ,[
            'title' => 'required|unique:articles|max:255',
            'summary' => 'required|max:65535',
            'content' => 'required|max:65535',
        ]);

        if ($validator->fails()) {

            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        try {

            $article = new Article();
            $article->title = $request->title;
            $article->slug = SlugFromString::slugify($article->title);
            $article->summary = $request->summary;
            $article->content = $request->content;
            $article->save();
        } catch (Exception $e) {

            // Don't return the error to the client.
            return response()->json(['error' => 'Unknown database error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['slug' => $article->slug], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * The detail returns ALL the fields of the blog in a get request.
     *
     * @param string $slugId
     * @return JsonResponse
     */
    public function show(string $slugId): JsonResponse
    {
        try {

            $article = Article::select('title', 'slug', 'summary', 'content', 'created_at')
                ->where('slug', $slugId)
                ->first();
        } catch (Exception $e) {

            // Don't return the error to the client.
            return response()->json(['error' => 'Unknown database error'],
            Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (!$article) {

            return response()->json(['error' => 'No article with this slug found'],
                Response::HTTP_NOT_FOUND);
        }

        $responseFields = [
          'title' => $article->title,
          'slug'  => $article->slug,
          'summary' => $article->summary,
          'content' => $article->content,
          'createdOn' => $article->created_at
        ];

        return response()->json($responseFields, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * This request can update the blog based on the slug in the url.
     * I should be able to update all fields EXCEPT for the auto generated fields (slug and createdOn).
     *
     * 1. Check if the slug exists
     * 2. Filter and check the request
     * 3. Try to do the update
     *
     * @param Request $request
     * @param string $slug
     * @return JsonResponse
     */
    public function update(Request $request, string $slug): JsonResponse
    {
        // 1. Check if the slug exists
        $id = Article::where('slug', $slug)->pluck('id');

        if (!$id) {

            return response()->json(['error' => 'No article with this slug found'],
                Response::HTTP_NOT_FOUND);
        }

        // 2. Filter and check the request
        $filtered_request = $request->only(['title', 'summary', 'content']);

        $validator = Validator::make($filtered_request ,[
            'title' => 'max:255',
            'summary' => 'max:65535',
            'content' => 'max:65535',
        ]);

        if ($validator->fails()) {

            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        // 3. Try to do the update
        try {

            $article = Article::whereId($id)->update($filtered_request);
        } catch (Exception $e) {

            // Don't return the error to the client.
            return response()->json(['error' => 'Unknown database error'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // success
        return response()->json(['message' => 'Article updated'], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     * 1. Check if the slug exists.
     * 2. Try to delete the blog post
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function destroy(string $slug): JsonResponse
    {
        // 1. Check if the slug exists
        $slugId = Article::where('slug', $slug)->pluck('id');

        if (!$slugId) {

           return response()->json(['error' => 'No article with this slug found'],
               Response::HTTP_NOT_FOUND);
       }

       // 2. Try to delete the blog post
       try {

           Article::destroy($slugId);
       } catch (Exception $e) {

           // Don't return the error to the client.
           return response()->json(['error' => 'Unknown database error'],
               Response::HTTP_INTERNAL_SERVER_ERROR);
       }

        // success
        return response()->json(['message' => 'Article deleted'], Response::HTTP_OK);
    }
}
