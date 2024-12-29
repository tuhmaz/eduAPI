<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontendNewsController extends Controller
{
    public function setDatabase(Request $request)
    {
        $request->validate([
            'database' => 'required|string|in:jo,sa,eg,ps'
        ]);

        $request->session()->put('database', $request->input('database'));

        return response()->json(['message' => 'Database connection set successfully']);
    }

    private function getConnection($database = null)
    {
        if ($database && in_array($database, ['jo', 'sa', 'eg', 'ps'])) {
            return $database;
        }
        return 'jo';
    }

    public function index(Request $request, $database)
    {
        try {
            $connection = $this->getConnection($database);

            // جلب الفئات من قاعدة البيانات المحددة
            $categories = Category::on($connection)->select('id', 'name', 'slug')->get();

            // إنشاء استعلام الأخبار
            $query = News::on($connection);

            if ($request->has('category') && !empty($request->input('category'))) {
                $categorySlug = $request->input('category');
                $category = Category::on($connection)->where('slug', $categorySlug)->first();

                if ($category) {
                    $query->where('category_id', $category->id);
                } else {
                    $query->whereNull('category_id');
                }
            }

            // جلب الأخبار مع الفئات
            $news = $query->get()->map(function ($newsItem) use ($connection) {
                // جلب الفئة من نفس قاعدة البيانات
                $newsItem->category = Category::on($connection)->find($newsItem->category_id);
                return $newsItem;
            });

            // تطبيق الترقيم
            $perPage = 10;
            $page = $request->input('page', 1);
            $pagedNews = $news->forPage($page, $perPage);
            
            return response()->json([
                'news' => $pagedNews->values(), 
                'categories' => $categories, 
                'database' => $connection,
                'total' => $news->count(),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($news->count() / $perPage)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in FrontendNewsController@index: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الأخبار.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $database, string $id)
    {
        try {
            $connection = $this->getConnection($database);

            // جلب الخبر من قاعدة البيانات المحددة
            $news = News::on($connection)->findOrFail($id);

            // جلب الفئة من نفس قاعدة البيانات
            $news->category = Category::on($connection)->find($news->category_id);
            
            // جلب الكلمات المفتاحية من نفس قاعدة البيانات
            $news->keywords = DB::connection($connection)
                ->table('news_keyword')
                ->join('keywords', 'news_keyword.keyword_id', '=', 'keywords.id')
                ->where('news_keyword.news_id', $news->id)
                ->select('keywords.*')
                ->get();

            // جلب المؤلف من قاعدة البيانات الرئيسية
            $news->author = User::find($news->author_id);

            // جلب التعليقات المرتبطة بالخبر من نفس قاعدة البيانات
            $news->comments = Comment::where([
                'commentable_type' => News::class,
                'commentable_id' => $id,
                'database' => $connection
            ])->with('user')->latest()->get();

            // معالجة الكلمات الدلالية
            $news->description = $this->replaceKeywordsWithLinks($news->description, $news->keywords, $connection);
            $news->description = $this->createInternalLinks($news->description, $news->keywords, $connection);

            return response()->json([
                'news' => $news,
                'database' => $connection
            ]);
        } catch (\Exception $e) {
            Log::error('Error in FrontendNewsController@show: ' . $e->getMessage());
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب الخبر.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function replaceKeywordsWithLinks($description, $keywords, $database)
    {
        if (is_string($keywords)) {
            $keywords = array_map('trim', explode(',', $keywords));
        }

        foreach ($keywords as $keyword) {
            $keywordText = $keyword->keyword ?? $keyword;
            $keywordLink = route('keywords.indexByKeyword', ['database' => $database, 'keywords' => $keywordText]);
            $description = preg_replace('/\b' . preg_quote($keywordText, '/') . '\b/', '<a href="' . $keywordLink . '">' . $keywordText . '</a>', $description);
        }

        return $description;
    }

    private function createInternalLinks($description, $keywords, $database)
    {
        if (is_string($keywords)) {
            $keywordsArray = array_map('trim', explode(',', $keywords));
        } else {
            $keywordsArray = $keywords->pluck('keyword')->toArray();
        }

        foreach ($keywordsArray as $keyword) {
            $keywordLink = route('keywords.indexByKeyword', ['database' => $database, 'keywords' => $keyword]);
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/';
            $replacement = '<a href="' . $keywordLink . '">' . $keyword . '</a>';
            $description = preg_replace($pattern, $replacement, $description);
        }

        return $description;
    }

    public function category(Request $request, $translatedCategory)
    {
        $connection = $this->getConnection($request);

        $category = Category::on($connection)->where('name', $translatedCategory)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $categories = Category::on($connection)->pluck('name', 'id');
        $news = News::on($connection)->where('category_id', $category->id)->paginate(10);

        return response()->json(['news' => $news, 'categories' => $categories, 'category' => $category]);
    }

    public function filterNewsByCategory(Request $request)
    {
        $connection = $this->getConnection($request);

        $categorySlug = $request->input('category');

        $category = Category::on($connection)->where('slug', $categorySlug)->first();

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $news = News::on($connection)
            ->where('category_id', $category->id)
            ->paginate(10);

        if ($news->isEmpty()) {
            return response()->json(['message' => 'No news found for the selected category'], 404);
        }

        return response()->json(['news' => $news]);
    }
}
