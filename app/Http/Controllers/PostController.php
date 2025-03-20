<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    // Create a new post
    public function createPost(Request $request)
    {
        
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the user is a candidate
        $candidate = $user->candidate; // Assuming a relationship exists between User and Candidate
        if (!$candidate) {
            return response()->json(['message' => 'Only candidates can create posts.'], 403);
        }

        $request->validate([
            //'candidate_id' => 'required|exists:candidates,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

         // Prepare the data for creating the post
        $data = $request->only(['title', 'content']);

        // Automatically set the candidate_id from the authenticated user's candidate relationship
        $data['candidate_id'] = $candidate->id;

        // Handle the image upload if provided
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('post_images', 'public'); // Save in storage/app/public/post_images
            $data['image'] = $path;
        }

        // Default is_approved to false
        $data['is_approved'] = false;

        // Create the post
        $post = Post::create($data);

        return response()->json([
            'message' => 'Post created successfully.',
            'post' => $post,
        ], 201);
    }

    // Fetch a specific post
    public function getPost($id)
    {
        $post = Post::with('candidate.user', 'candidate.position', 'candidate.partylist', 'candidate.department')->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        return response()->json([
            'post' => $post,
        ], 200);
    }

    // Fetch all posts
    public function getAllPosts()
    {
        $posts = Post::with('candidate')->get();

        return response()->json([
            'posts' => $posts,
        ], 200);
    }

    // Update a post
    public function updatePost(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        // Only allow updates for the post owner (candidate)
        if ($post->candidate_id !== $user->candidate->id) {
            return response()->json(['message' => 'Forbidden: Not your post.'], 403);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Update the post data
        if ($request->has('title')) $post->title = $request->title;
        if ($request->has('content')) $post->content = $request->content;

        // Handle image update
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($post->image) {
                $oldPath = public_path('storage/' . $post->image);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $file = $request->file('image');
            $path = $file->store('post_images', 'public'); // Save in storage/app/public/post_images
            $post->image = $path;
        }

        $post->save();

        return response()->json([
            'message' => 'Post updated successfully.',
            'post' => $post,
        ], 200);
    }

    // Delete a post
    public function deletePost($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        // Only allow deletion for the post owner (candidate)
        if ($post->candidate_id !== $user->candidate->id) {
            return response()->json(['message' => 'Forbidden: Not your post.'], 403);
        }

        // Delete the image file if it exists
        if ($post->image) {
            $path = public_path('storage/' . $post->image);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ], 200);
    }

    //get approved posts
    public function getApprovedPosts()
    {
        $posts = Post::where('is_approved', true)
                ->with([
                    'candidate' => function ($query) {
                        $query->select('id', 'user_id', 'profile_photo', 'position_id', 'party_list_id')
                            ->with([
                                'user:id,name',
                                'partylist:id,name,description',
                                'position:id,name'
                            ]);
                    }
                ])
                ->get();

            return response()->json($posts);

        return response()->json($posts);
    }

        // Fetch all posts with pagination
        public function getAllPostsPaginated(Request $request)
        {
            // Set default per page value, or use query parameter
            $perPage = $request->query('per_page', 2); // Default to 10 posts per page
    
            // Fetch paginated posts with relationships
            $posts = Post::where('is_approved', true)->with([
                'candidate' => function ($query) {
                    $query->select('id', 'user_id', 'profile_photo', 'position_id', 'party_list_id')
                        ->with([
                            'user:id,name',
                            'position:id,name',
                            'partylist:id,name',
                            'department:id,name'
                        ]);
                }
            ])->paginate($perPage);
    
            return response()->json([
                'posts' => $posts->items(), // Current page items
                'pagination' => [
                    'total' => $posts->total(),
                    'per_page' => $posts->perPage(),
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'from' => $posts->firstItem(),
                    'to' => $posts->lastItem(),
                    'next_page_url' => $posts->nextPageUrl(),
                    'prev_page_url' => $posts->previousPageUrl(),
                ],
            ], 200);
        }

    //get approved posts of a specific candidate
    public function getApprovedPostsByCandidate($candidateId)
    {
        $posts = Post::where('is_approved', true)
                    ->where('candidate_id', $candidateId)
                    ->with([
                        'candidate' => function ($query) {
                            $query->select('id', 'user_id', 'profile_photo', 'position_id', 'party_list_id')
                                ->with([
                                    'user:id,name',
                                    'partylist:id,name,description',
                                    'position:id,name'
                                ]);
                        }
                    ])
                    ->get();
        return response()->json($posts);
    }



}
