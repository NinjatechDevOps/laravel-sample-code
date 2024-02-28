<div class="manufacure-listing">
    <div class="row flex-wrap category-list-wrapper">
    @foreach($categories as $key=>$category)
        <div class="col {{$category->count() >= 9 && $key >= 7  ? 'show-desk' : ''}}">
            <h4>
                <a class="bg-white-radius body2-text d-flex align-items-center"
                   href="{!! $category->detail_url !!}" title="{!! $category->name !!}">
                    <!-- <span class="{{$category->icon_class}}"></span> -->
                    <span class="d-flex">{!! $category->name !!}</span>
                    <span class="icon-right-arrow"></span>
                </a>
            </h4>
        </div>

    @endforeach

    @if(!$categories->count())
        <p class="search-nodata">No data found.</p>
    @endif
    </div>
</div>
