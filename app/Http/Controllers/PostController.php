<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use Response;
use \App\Models\Post;
class PostController extends Controller
{
    public $user;

    public function __construct()
    {
       $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index(Request $request){
        $page_size = $request->page_size ?? 20;
        if ($page_size > 50){
            $page_size = 50;
        }

        $posts = Post::paginate($page_size);
        return $posts;
    }

    public function store(Request $request){
        $data = $request->only('title', 'content');
        $validator = Validator::make($data, [
            'title' => 'required',
            'content' => 'required',
            'tags' => 'array',
            'status' => 'boolean'
        ]);

        if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 200);
        }
        $post = new Post();

        $post->title = $request->title;
        $post->content = $request->content;

        $post->author_id = $this->user->id;

        if ($request->tags){
            $tags_string = '';
            foreach ($request->tags as $tag){
                $tag_obj = Tag::where('name',$tag, '=');
                $tag_obj->frequency = $tag_obj->frequency + 1;
                $tag_obj->save();

                $tags_string = $tags_string . $tag . ',';
            }
            $post->tags = $tags_string;
        }

        if ($request->status){
            $post->status = $request->status;
        }
        $post->save();

        return response()->json(
            [
                'success' => TRUE,
            ]
            );
    }
    public function me(){
        $user = $this->user;
        $posts = $user->posts()->paginate(10);
        return response()->json([
            "posts" => $posts,
        ]);
    }

    public function destroy(Request $request){
        $data = $request->only('post_id');
        $validator = Validator::make($data, [
            'post_id' => 'required|integer|exists:posts,id',
        ]);

        if($this->user->posts->where('post_id',$request->post_id,'=') == NULL)
        {
            return response()->json(['errors' => 'Permission denied'], 403);
        }


        $post = Post::find($request->post_id);
        if(!$post){
            return response()->json(['errors' => 'Invalid Post'], 401);
        }
        $post->delete();
        return response()->json(['success' => TRUE], 200);

    }

    public function update(Request $request, $post_id){
        $data = $request->only('content', 'title');
        $validator = Validator::make($data, [
            'content' => 'string|required',
            'title' => 'string|required'
        ]);

       if ($validator->fails()){
            return response()->json(['error' => $validator->messages()], 200);
        }
        $post = Post::find($post_id);
        if (!$post){
            return response()->json([
                'success' => 'false',
                'error' => 'no such post',
            ]);
        }
        $post->title = $request->title;
        $post->content = $request->content;
        $post->save();
         return response()->json(
            [
                'success' => TRUE,
            ]
        );


    }
}
