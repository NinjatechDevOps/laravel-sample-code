@php
    $manufacturer = $product->cache_manufacturer;
@endphp
@if($manufacturer)
    <div class="catalog-card">
        <a href="{!! $product->detail_url !!}"
           title="{!! $product->full_name!!}" class="catalog-category-img text-center">
            <img src="{!! $product->image_full_url!!}" alt="{!! $manufacturer->name!!} {!! $product->full_name!!}"
                 title="{!! $manufacturer->name!!} {!! $product->full_name!!}">
        </a>
        <div class="cat-logo">
            <img src="{!! $manufacturer->image_url!!}" alt="{!! $manufacturer->name!!}"
                 title="{!! $manufacturer->name!!}">
        </div>
        <div class="category-cat-desc">
            <h4 class="catalog-cat-name">
                <a href="{!! $product->detail_url !!}"
                   title="{!! $product->full_name!!}">{!! $product->full_name !!}
                    <strong>{!! $product->part_number !!}</strong>
                </a>
            </h4>
            <div class="catalog-price-main d-flex justify-content-between align-items-center">
                <div class="catalog-price common-price">
                    @if($product->formatted_frontend_price)
                        <div class="price">
                            <strong>{!! $product->formatted_frontend_price !!}</strong>
                            <!-- <span class="price-info">Target Price</span> -->
                        </div>
                    @else
                        <div class="price">
                            <strong>Available on Request</strong>
                        </div>
                    @endif
                </div>
                <a href="{!! $product->detail_url !!}"
                   class="btn btn-secondary btn-rounded">
                    <span class="icon-catalog-card-arrow"></span>
                </a>
            </div>
        </div>
    </div>
@endif
