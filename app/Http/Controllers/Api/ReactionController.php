<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    /**
     * عرض قائمة التفاعلات لتعليق معين
     */
    public function index($database, Comment $comment)
    {
        try {
            // التحقق من أن التعليق من نفس قاعدة البيانات
            if ($comment->database !== $database) {
                return response()->json([
                    'message' => 'التعليق غير موجود'
                ], 404);
            }

            $reactions = $comment->reactions()->with('user')->get();
            
            return response()->json([
                'reactions' => $reactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في جلب التفاعلات',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * إضافة تفاعل جديد
     */
    public function store(Request $request, $database, Comment $comment)
    {
        try {
            $request->validate([
                'type' => 'required|string|in:like,love,haha,wow,sad,angry'
            ]);

            // التحقق من أن التعليق من نفس قاعدة البيانات
            if ($comment->database !== $database) {
                return response()->json([
                    'message' => 'التعليق غير موجود'
                ], 404);
            }

            // التحقق من عدم وجود تفاعل سابق لنفس المستخدم
            $existingReaction = $comment->reactions()
                ->where('user_id', auth()->id())
                ->first();

            if ($existingReaction) {
                // إذا كان نوع التفاعل نفسه، نقوم بحذفه
                if ($existingReaction->type === $request->type) {
                    $existingReaction->delete();
                    return response()->json([
                        'message' => 'تم إزالة التفاعل'
                    ]);
                }
                // إذا كان نوع التفاعل مختلف، نقوم بتحديثه
                $existingReaction->update(['type' => $request->type]);
                return response()->json([
                    'message' => 'تم تحديث التفاعل',
                    'reaction' => $existingReaction->load('user')
                ]);
            }

            // إنشاء تفاعل جديد
            $reaction = $comment->reactions()->create([
                'user_id' => auth()->id(),
                'type' => $request->type,
                'database' => $database
            ]);

            return response()->json([
                'message' => 'تم إضافة التفاعل بنجاح',
                'reaction' => $reaction->load('user')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في إضافة التفاعل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاعل معين
     */
    public function show($database, Comment $comment, Reaction $reaction)
    {
        try {
            // التحقق من أن التعليق والتفاعل من نفس قاعدة البيانات
            if ($comment->database !== $database || $reaction->database !== $database) {
                return response()->json([
                    'message' => 'التفاعل غير موجود'
                ], 404);
            }

            // التحقق من أن التفاعل ينتمي للتعليق
            if ($reaction->comment_id !== $comment->id) {
                return response()->json([
                    'message' => 'التفاعل غير موجود'
                ], 404);
            }

            return response()->json([
                'reaction' => $reaction->load('user')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في جلب التفاعل',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * حذف تفاعل
     */
    public function destroy($database, Comment $comment, Reaction $reaction)
    {
        try {
            // التحقق من أن التعليق والتفاعل من نفس قاعدة البيانات
            if ($comment->database !== $database || $reaction->database !== $database) {
                return response()->json([
                    'message' => 'التفاعل غير موجود'
                ], 404);
            }

            // التحقق من أن التفاعل ينتمي للتعليق
            if ($reaction->comment_id !== $comment->id) {
                return response()->json([
                    'message' => 'التفاعل غير موجود'
                ], 404);
            }

            // التحقق من أن المستخدم هو صاحب التفاعل
            if (auth()->id() !== $reaction->user_id) {
                return response()->json([
                    'message' => 'غير مصرح لك بحذف هذا التفاعل'
                ], 403);
            }

            $reaction->delete();

            return response()->json([
                'message' => 'تم حذف التفاعل بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'فشل في حذف التفاعل',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
