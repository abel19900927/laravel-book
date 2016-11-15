<?php

namespace App\Http\Controllers\Service;

use App\Entity\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookController extends Controller
{
    public function getCategoryByParentId($parent_id)
    {
        $categorys = Category::where('parent_id', $parent_id)->get();

        return ['status' => 0, 'msg' => '返回成功', 'categorys' => $categorys];
    }
}