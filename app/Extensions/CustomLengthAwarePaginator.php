<?php

namespace App\Extensions;

use Illuminate\Pagination\LengthAwarePaginator;

class CustomLengthAwarePaginator extends LengthAwarePaginator
{

    protected $appends = [];

    public function append($key, $value)
    {
        $this->appends[$key] = $value;
        return $this;
    }

    public function url($page)
    {
        if ($page <= 0 || $page > $this->lastPage()) {
            return null;
        }

        $parameters = [$this->pageName => $page] + $this->query + $this->appends;

        unset($parameters['page']);

        $params = http_build_query($parameters);

        $path = parse_url(url()->current(), PHP_URL_PATH);
        $path = preg_replace('/\/page-\d+/', '', $path);

        $stringEndWith = false;
        if(str_ends_with($path, '.html')){
            $stringEndWith = true;
            $path = substr($path,0, strlen($path) - 5);
        }

        if($page == 1){
            return $path . ($stringEndWith ? '.html' : '') . ($params ? ('?' . $params) : '');
        }
        return $path. '/page-' . $page . ($stringEndWith ? '.html' : '') . ($params ? ('?' . $params) : '');

        //return url()->current().'?'.http_build_query($parameters);
    }
}
