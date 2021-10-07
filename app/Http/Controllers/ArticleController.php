<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ArticleController extends Controller
{


    public function test(){

        $articles = Article::all();
        foreach($articles as $article){
            // $article->slug = Str::slug($article->title, '-');
            $article->excerpt = Str::words($article->description, 50);
            $article->update();
        }

    }

    public function index()
    {
        $articles = Article::when(isset(request()->search),function ($q){
            $search = request()->search;
            return $q->orwhere("title","like","%$search%")->orwhere("description","like","%$search%");

        })->with(['user','category'])->latest("id")->paginate(7);
//        return $articles;
        return view('article.index',compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('article.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "category" => "required|exists:categories,id",
            "title" => "required|min:5|max:200",
            "description" => "required|min:5",
            "feature_image" => "nullable|image|mimes:jpg,png|max:1000"
        ]);


        $article = new Article();
        $article->title = $request->title;
        $article->slug = Str::slug($request->title, '-');
        $article->category_id = $request->category;
        $article->description = $request->description;
        $article->excerpt = Str::words($request->description, 50, '...');

        if($request->hasFile('feature_image')){
            $dir = "public/articles/";
            $imgName = uniqid()."feature_image.".$request->file('feature_image')->getClientOriginalExtension();
            $request->file('feature_image')->storeAs($dir, $imgName);
            $article->feature_image = $imgName;
        }

        $article->user_id = Auth::id();
        $article->save();

        return redirect()->route('article.index')->with("message","New Article Created");

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        return view('article.show',compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        return view('article.edit',compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            "category" => "required|exists:categories,id",
            "title" => "required|min:5|max:200",
            "description" => "required|min:5"
        ]);

        $article->title = $request->title;
        $article->category_id = $request->category;
        $article->description = $request->description;
        $article->update();

        return redirect()->route('article.index')->with("message","Article Updated");

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {

        $article->delete();
        return redirect()->route('article.index',['page'=>request()->page])->with("message","Article deleted");

    }
}
