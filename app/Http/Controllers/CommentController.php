<?php

namespace App\Http\Controllers;
use \App\Models\Comment;
use \App\Models\Post;


use Illuminate\Http\Request;
use JWTAuth;
use Validator;
use Response;
class CommentController extends Controller
{
    public $user;

    public function __construct()
    {
       $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index(Request $request, $post_id){
        $post = Post::find($post_id);
        if(!$post){
            return response()->json(['error' => 'permission denied' ], 200);
        }
        return Comment::where('post_id',$post_id,'=')->get();;

    }

    public function store(Request $request, $post_id){
        $data = $request->all();


        $validator = Validator::make($data,
        [
            "comment" => "required|string",
        ]
        );

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()],200);
        }
        $post = Post::find($post_id);
        if(!$post){
            return response()->json(
                [
                    'success' => FALSE,
                    'errors' => 'invalid post',
                ],
                200
            );
        }
        $comment = new comment();

        $comment->content = $request->comment;
        $comment->author_id = $this->user->id;
        $comment->post_id = $request->post_id;
        $comment->save();
        return response()->json([
            'comment' => $comment,
        ]);

    }
    public function destroy(Request $request, $comment_id){
        $data = ["comment_id" => $comment_id];
        $validator = validator::make($data, [
            'comment_id' => 'required|exists:comments,id',
        ]);

        $comment = $this->user->comments()->where('id',$comment_id,'=')->first();
        if (!$comment){
            return response()->json([
                'success' => FALSE,
                'errors' => 'unauthorized: permission denied'
            ], 401);
        }
        $comment->delete();
        return response()->json(['success' => TRUE], 200);
    }
    public function update(Request $request, $post_id, $comment_id){
        $data = ["comment" => $request->comment , 'post_id'=>$post_id, "comment_id" => $comment_id];

        $validator = Validator::make($data,
        [
            "comment" => "required|string",
            "post_id" => "required|exists:posts,id",
            "comment_id" => "required|exists:comments,id",
        ]
        );

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()],200);
        }

        $comment = Comment::where('author_id', $this->user->id, '=')->where('id',$comment_id,'=')->first();
        if (!$comment){
            return response()->json([
                'success' => FALSE,
                'errors' => 'unauthorized: permission denied'
            ], 401);
        }

        $comment->content = $request->comment;
        $comment->save();
        return response()->json([
            'comment' => $comment,
        ]);

    }
}
