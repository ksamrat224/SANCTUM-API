<?php

namespace App\Http\Controllers\API;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();
        return $this->sendResponse($data, 'Posts retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]
        );

        if ($validateUser->fails()) {
            return $this->sendError('Validation Error', $validateUser->errors()->all());
        }

        $img = $request->file('image');
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path('uploads'), $imageName);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return $this->sendResponse($post, 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::select(
            'id',
            'title',
            'description',
            'image'
        )->where('id', $id)->first();

        return $this->sendResponse($data, 'Post retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'title' => 'required',
                'description' => 'required',
                'image' => 'sometimes|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()->all(),
            ], 401);
        }

        $post = Post::findOrFail($id);
        $imageName = $post->image;

        if ($request->hasFile('image')) {
            $path = public_path('uploads');
            if ($post->image && file_exists($path . '/' . $post->image)) {
                unlink($path . '/' . $post->image);
            }
            $img = $request->file('image');
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move($path, $imageName);
        }

        $post->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName,
        ]);

        return $this->sendResponse($post, 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::findOrFail($id);
        $filePath = public_path('uploads/' . $post->image);
        if ($post->image && file_exists($filePath)) {
            unlink($filePath);
        }

        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Your post has been removed',
            'post' => $post
        ], 200);
        return $this->sendResponse($post, 'Post deleted successfully');
    }
}