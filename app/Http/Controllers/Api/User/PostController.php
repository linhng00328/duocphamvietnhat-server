<?php

namespace App\Http\Controllers\Api\User;

use App\Helper\StringUtils;
use App\Helper\TypeFCM;
use App\Http\Controllers\Controller;
use App\Jobs\PushNotificationCustomerJob;
use App\Models\CategoryPost;
use App\Models\CategoryPostChild;
use App\Models\MsgCode;
use App\Models\NotificationCustomer;
use App\Models\Post;
use App\Models\PostCategoryPost;
use App\Models\PostCategoryPostChild;
use App\Services\UploadImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @group  User/Bài viết
 */
class PostController extends Controller
{
    /**
     * Tạo bài viết
     * @urlParam  store_code required Store code
     * @bodyParam title string required Tiêu đề bài viết
     * @bodyParam image_url string required Link ảnh hoặc có thể gửi data ảnh bằng bodyParam "image"
     * @bodyParam summary string required Nội dung vắn tắt
     * @bodyParam content string required Nội dung bài viết
     * @bodyParam published boolean required Ẩn hiện bài viết
     * @bodyParam category_id id category
     */
    public function create(Request $request, $id)
    {


        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), "", $request->image->getClientMimeType());
        } else {
            $imageUrl = $request->image_url;
        }

        $postHasName =  Post::where('store_id', $request->store->id,)
            ->where('title', $request->title)->first();
        if ($postHasName != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $postHasUrl =  Post::where('store_id', $request->store->id,)
            ->where('post_url', $request->post_url)->first();
        if ($postHasUrl != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => "POST_URL_ALREADY_EXIST",
                'msg' => "URL này đã được sử dụng",
            ], 400);
        }


        // if (StringUtils::description_contains_image_base64($request->content)) {
        //     return response()->json([
        //         'code' => 400,
        //         'success' => false,
        //         'msg_code' => MsgCode::DESCRIPTION_CANT_IMAGE_BAGE64[0],
        //         'msg' => MsgCode::DESCRIPTION_CANT_IMAGE_BAGE64[1],
        //     ], 400);
        // }

        $postCreate = Post::create(
            [
                'store_id' => $request->store->id,
                'title' => $request->title,
                'image_url' => $imageUrl,
                'summary' => $request->summary,
                'content' => $request->content,
                'published' => filter_var($request->published, FILTER_VALIDATE_BOOLEAN),
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'post_url' => $request->post_url ? $request->post_url : Str::slug($request->title),
            ]
        );

        if ($request->post_url == null) {
            $slug = Str::slug($request->title);
        } else {
            $slug = $request->post_url;
        }

        $slugCreate = DB::table('slugs')->insert([
            'type' => 'post',
            'value' => $slug,
        ]);


        if ($request->category_id != null) {

            PostCategoryPost::create(
                [
                    'post_id' => $postCreate->id,
                    'categorypost_id' => $request->category_id
                ]
            );
        }
        $category_parent = $request->category_parent;
        $category_children_ids = $request->category_children_ids;
        if ($category_parent !== null && count($category_parent) > 0) {
            foreach ($category_parent as $categoryId) {
                if (PostCategoryPost::where('post_id', $postCreate->id)->where('categorypost_id',  $categoryId)->first() == null) {
                    PostCategoryPost::create(
                        [
                            'post_id' => $postCreate->id,
                            'categorypost_id' => $categoryId
                        ]
                    );
                }
            }
        }

        if ($category_children_ids !== null && count($category_children_ids) > 0) {
            foreach ($category_children_ids as $categoryId) {

                $checkCategoryExists = PostCategoryPostChild::where(
                    'id',
                    $categoryId
                )->first();

                if ($checkCategoryExists != null) {

                    PostCategoryPostChild::create(
                        [
                            'post_id' => $postCreate->id,
                            'category_post_children_id' => $categoryId
                        ]
                    );
                }
            }
        }

        PushNotificationCustomerJob::dispatch(
            $request->store->id,
            null,
            $request->title,
            $request->summary,
            TypeFCM::NEW_POST,
            $postCreate->id
        );


        return response()->json([
            'code' => 201,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Post::where('id', $postCreate->id)->first()
        ], 201);
    }

    /**
     * Update bài viết
     * @urlParam  store_code required Store code
     * @urlParam  post_id required ID post cần cập nhật
     * @bodyParam title string required Tiêu đề bài viết
     * @bodyParam image_url string required Link ảnh hoặc có thể gửi data ảnh bằng bodyParam "image"
     * @bodyParam summary string required Nội dung vắn tắt
     * @bodyParam content string required Nội dung bài viết
     * @bodyParam published boolean required Ẩn hiện bài viết
     * @bodyParam category_id id category
     */
    public function updateOnePost(Request $request, $id)
    {


        $id = $request->route()->parameter('post_id');
        $postExists = Post::where('id', $id)->where('store_id', $request->store->id)->first();

        if (empty($postExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        $postHasName =  Post::where('id', '<>', $id)
            ->where('store_id', $request->store->id,)
            ->where('title', $request->title)->first();
        if ($postHasName != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::TITLE_ALREADY_EXISTS[0],
                'msg' => MsgCode::TITLE_ALREADY_EXISTS[1],
            ], 400);
        }

        $postHasUrl =  Post::where('id', '<>', $id)
            ->where('store_id', $request->store->id,)
            ->where('post_url', $request->post_url)->first();
        if ($postHasUrl != null) {
            return response()->json([
                'code' => 400,
                'success' => false,
                'msg_code' => "POST_URL_ALREADY_EXIST",
                'msg' => "Url này đã được sử dụng",
            ], 400);
        }

        $imageUrl = null;
        if (!empty($request->image)) {
            $imageUrl = UploadImageService::uploadImage($request->image->getRealPath(), "", $request->image->getClientMimeType());
        } else {
            $imageUrl = $request->image_url;
        }

        $slug = Str::slug($request->name);
        $slugExsits = null;
        if ($postExists->post_url == null) {
            $slugExsits = DB::table('slugs')
                ->where('type', 'post')
                ->where('value', Str::slug($postExists->title))->first();
        } else {
            $slugExsits = DB::table('slugs')
                ->where('type', 'post')
                ->where('value', $postExists->post_url)->first();
        }

        if ($slugExsits != null) {
            $slug = null;
            if ($request->post_url == null) {
                $slug = Str::slug($request->title);
            } else {
                $slug = $request->post_url;
            }

            DB::table('slugs')
                ->where('id', $slugExsits->id)->update([
                    'value' => $slug,
                ]);
        } else {
            $slug = null;
            if ($request->post_url == null) {
                $slug = Str::slug($request->title);
            } else {
                $slug = $request->post_url;
            }


            $slugCreate = DB::table('slugs')->insert([
                'type' => 'post',
                'value' => $slug,
            ]);
        }

        $postExists->update(
            [
                'store_id' => $request->store->id,
                'title' => $request->title,
                'image_url' => $imageUrl,
                'summary' => $request->summary,
                'content' => $request->content,
                'published' => filter_var($request->published, FILTER_VALIDATE_BOOLEAN),
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'post_url' => $request->post_url ? $request->post_url : Str::slug($request->title),
            ]
        );

        $cateExis = PostCategoryPost::where('post_id', $postExists->id)->first();

        if ($request->category_id != null &&  $cateExis == null) {
            PostCategoryPost::create(
                [
                    'post_id' => $postExists->id,
                    'categorypost_id' => $request->category_id
                ]
            );
        }

        if ($request->category_id != null &&  $cateExis != null && $cateExis->id != $request->category_id) {

            PostCategoryPost::where('post_id', $postExists->id)->delete();

            PostCategoryPost::create(
                [
                    'post_id' => $postExists->id,
                    'categorypost_id' => $request->category_id
                ]
            );
        }

        // $category_parent = $request->category_parent;
        // $category_children_ids = $request->category_children_ids;
        // if ($category_parent !== null && count($category_parent) > 0) {
        //     foreach ($category_parent as $categoryId) {
        //         if (PostCategoryPost::where('post_id', $postExists->id)->where('categorypost_id',  $categoryId['id'])->first() == null) {
        //             PostCategoryPost::create(
        //                 [
        //                     'post_id' => $postExists->id,
        //                     'categorypost_id' => $categoryId['id']
        //                 ]
        //             );
        //         } else {
        //             PostCategoryPost::where('post_id', $postExists->id)->delete();
        //             PostCategoryPost::create(
        //                 [
        //                     'post_id' => $postExists->id,
        //                     'categorypost_id' => $categoryId['id']
        //                 ]
        //             );
        //         }
        //     }
        // }

        // if ($category_children_ids !== null && count($category_children_ids) > 0) {
        //     foreach ($category_children_ids as $categoryId) {

        //         $checkCategoryExists = PostCategoryPostChild::where(
        //             'id',
        //             $categoryId
        //         )->first();

        //         if ($checkCategoryExists != null) {
        //             PostCategoryPostChild::where('post_id', $postExists->id)->delete();


        //             PostCategoryPostChild::create(
        //                 [
        //                     'post_id' => $postExists->id,
        //                     'category_post_children_id' => $categoryId
        //                 ]
        //             );
        //         }
        //     }
        // }

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => Post::where('id', $postExists->id)->first()
        ], 200);
    }

    /**
     * Danh sách bài viết
     * @urlParam  store_code required Store code
     * @queryParam  page Lấy danh sách bài viết ở trang {page} (Mỗi trang có 20 item)
     * @queryParam  search Tên cần tìm VD: covid 19
     * @queryParam  sort_by Sắp xếp theo VD: time
     * @queryParam  descending Giảm dần không VD: false 
     * @queryParam  category_ids Thuộc post category id nào VD: 1,2,3
     */
    public function getAll(Request $request, $id)
    {
        $categoryIds = request("category_ids") == null ? [] : explode(',', request("category_ids"));


        $posts = Post::where(
            'store_id',
            $request->store->id
        )
            ->when(request('sort_by'), function ($query) {
                $query->orderBy(request('sort_by'), filter_var(request('descending'), FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc');
            })
            ->when(count($categoryIds) > 0, function ($query) use ($categoryIds) {
                $query->whereHas('category_posts', function ($query) use ($categoryIds) {
                    $query->whereIn('categorypost.id', $categoryIds);
                });
            })->orderBy('created_at', 'desc')

            ->search(request('search'))

            ->paginate(request('limit') == null ? 20 : request('limit'));

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $posts,
        ], 200);
    }


    /**
     * Thông tin một bài viết
     * @urlParam  store_code required Store code cần lấy.
     * @urlParam  post_id required ID post cần lấy thông tin.
     */
    public function getOnePost(Request $request)
    {
        $id = $request->route()->parameter('post_id');
        $postExists = Post::where('id', $id)->where('store_id', $request->store->id)->first();

        if (empty($postExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        $des = DB::table('posts')
            ->where('id',  $postExists->id)
            ->select(
                'content',
            )->get();
        $proRes =   $postExists->toArray();
        $proRes["content"] = $des[0]->content;

        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' =>  $proRes,
        ], 200);
    }

    /**
     * xóa một bài viết
     * @urlParam  store_code required Store code cần xóa.
     * @urlParam  id required ID bài viết cần xóa thông tin.
     */
    public function deleteOnePost(Request $request)
    {
        $id = $request->route()->parameter('post_id');
        $postExists = Post::where('id', $id)->where(
            'store_id',
            $request->store->id
        )->first();

        if (empty($postExists)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'msg_code' => MsgCode::NO_POST_EXISTS[0],
                'msg' => MsgCode::NO_POST_EXISTS[1],
            ], 404);
        }

        $postExists->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => ['idDeleted' => $postExists->id],
        ], 200);
    }
}
