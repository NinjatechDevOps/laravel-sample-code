@if ($news && is_array($news) && count($news))

    <h3 class="h2">Latest News</h3>
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <div class="latest-news-big">
                <div class="latest-news-img-left">
                    <img src="{!! $news[0]['feature_image'] !!}" title="{!! $news[0]['page_title'] !!}"
                        alt="{!! $news[0]['page_title'] !!}" />
                </div>
                <div class="latest-news-content-left">
                    <h4 class="h3"><a href="{!! $news[0]['link'] !!}" title="{!! $news[0]['page_title'] !!}">{!! $news[0]['page_title'] !!}</a></h4>
                    <span class="body1-text">{!! $news[0]['short_content'] !!}</span>
                    <a class="small-link small-link-states" href="{!! $news[0]['link'] !!}" title="Read more">Read more</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-12">
            <div class="latest-news-small">
                <ul>
                    @foreach ($news as $key => $singleNews)
                        @if ($key > 0 && $key <= 3)
                            <li class="d-flex align-items-center flex-direction-row">
                                <a href="{!! $singleNews['link'] !!}" class="d-flex align-items-center flex-direction-row">
                                    <div class="thumb-img">
                                        <img src="{!! $singleNews['feature_image'] !!}" alt="{!! $singleNews['page_title'] !!}" title="{!! $singleNews['page_title'] !!}">
                                    </div>
                                    <div class="latest-news-content-small">
                                        <h4>{!! $singleNews['page_title'] !!}</h4>
                                        <span class="small-link small-link-states" href="{!! $singleNews['link'] !!}" title="Read more">Read more</span>
                                    </div>
                                </a>
                            </li>
                        @endif
                    @endforeach

                </ul>
            </div>
        </div>
    </div>

@endif
