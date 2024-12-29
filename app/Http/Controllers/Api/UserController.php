<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $users = User::with('roles')
                ->when($request->get('role'), function ($query) use ($request) {
                    $query->whereHas('roles', function ($query) use ($request) {
                        $query->where('name', $request->get('role'));
                    });
                })
                ->when($request->get('search'), function ($query) use ($request) {
                    $query->where(function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->get('search') . '%')
                              ->orWhere('email', 'like', '%' . $request->get('search') . '%');
                    });
                })
                ->paginate(10);

            return response()->json([
                'status' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with('roles', 'permissions')->findOrFail($id);
            
            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($request->role);

            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:15',
                'job_title' => 'nullable|string|max:100',
                'gender' => 'nullable|in:male,female',
                'country' => 'nullable|string|max:100',
                'social_links' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'is_online' => 'boolean'
            ]);

            $user->update($request->all());

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePermissionsRoles(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }
            
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            }

            return response()->json([
                'status' => true,
                'message' => 'User permissions and roles updated successfully',
                'data' => $user->load('roles', 'permissions')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating user permissions and roles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfilePhoto(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            // تسجيل معلومات الطلب للتشخيص
            \Log::info('Request Files:', [
                'hasFile_image' => $request->hasFile('image'),
                'hasFile_profile_photo' => $request->hasFile('profile_photo'),
                'allFiles' => $request->allFiles(),
                'all' => $request->all()
            ]);

            // التحقق من الملف
            if (!$request->hasFile('profile_photo') && !$request->hasFile('image')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No image file provided',
                    'error' => 'Image file is required',
                    'debug' => [
                        'files' => $request->allFiles(),
                        'all' => $request->all()
                    ]
                ], 400);
            }

            // استخدام الملف المتوفر (image أو profile_photo)
            $file = $request->hasFile('image') ? $request->file('image') : $request->file('profile_photo');

            // التحقق من صحة الملف
            if (!$file->isValid()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid file uploaded',
                    'error' => $file->getErrorMessage()
                ], 400);
            }

            try {
                // حذف الصورة القديمة إذا كانت موجودة
                if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                    Storage::disk('public')->delete($user->profile_photo_path);
                }

                // تخزين الصورة الجديدة
                $path = $file->store('profile-photos', 'public');
                
                // تحديث مسار الصورة للمستخدم مباشرة باستخدام DB
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'profile_photo_path' => $path,
                        'updated_at' => now()
                    ]);

                // إعادة تحميل المستخدم للحصول على البيانات المحدثة
                $user = User::find($user->id);

                return response()->json([
                    'status' => true,
                    'message' => 'Profile photo updated successfully',
                    'data' => [
                        'profile_photo_url' => Storage::url($path),
                        'user' => $user
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::error('Error storing profile photo: ' . $e->getMessage());
                return response()->json([
                    'status' => false,
                    'message' => 'Error storing profile photo',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Error in updateProfilePhoto: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error updating profile photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
