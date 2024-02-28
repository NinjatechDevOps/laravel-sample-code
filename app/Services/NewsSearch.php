<?php

namespace App\Services;

use App\Models\CmsContent;

/**
 * Class NewsSearch
 *
 * @package App\Services
 */
class NewsSearch
{
    /**
     * News Search
     *
     * @param  $param
     * @param  int $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function search($param, $pageSize = 5)
    {
        $news = CmsContent::where('template', CmsContent::TYPE_NEWS);

        if (array_key_exists('news_category_id', $param) && $param['news_category_id']) {
            $news = $news->where('news_category_id', $param['news_category_id']);
        }
        if (array_key_exists('sort_on', $param) && $param['sort_on'] && array_key_exists('sort_by', $param) && $param['sort_by']) {
            $news = $news->orderBy($param['sort_on'],$param['sort_by']);
        }
        else
        {
            $news = $news->orderByDesc('created_at');
        }

        return $news->paginate($pageSize);
    }
}
