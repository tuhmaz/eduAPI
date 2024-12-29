<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|string',
        ]);

        try {
            // الحصول على قاعدة البيانات الحالية
            $database = session('database', 'jo');

            // التحقق من وجود المحتوى في نفس قاعدة البيانات
            $contentExists = DB::connection($database)
                ->table(strtolower(class_basename($validated['commentable_type'])) . 's')
                ->where('id', $validated['commentable_id'])
                ->exists();

            if (!$contentExists) {
                return response()->json([
                    'message' => 'المحتوى غير موجود في قاعدة البيانات الحالية.',
                ], 404);
            }

            $comment = Comment::create([
                'body' => $validated['body'],
                'user_id' => auth()->id(),
                'commentable_id' => $validated['commentable_id'],
                'commentable_type' => $validated['commentable_type'],
                'database' => $database // حفظ اسم قاعدة البيانات مع التعليق
            ]);

            return response()->json([
                'message' => 'تم إضافة التعليق بنجاح!',
                'comment' => $comment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في إضافة التعليق.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request, $type, $id)
    {
        try {
            $database = session('database', 'jo');
            
            // جلب التعليقات المرتبطة بالمحتوى في قاعدة البيانات الحالية فقط
            $comments = Comment::where([
                'commentable_type' => $type,
                'commentable_id' => $id,
                'database' => $database
            ])->with('user')->latest()->get();

            return response()->json([
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في جلب التعليقات.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
