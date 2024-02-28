@extends('layouts.frontend')

@section('metaTitle', metaReplace('Electronic Components Distributor - {Website-name}'))
@section('metaDescription', metaReplace('{Website-name} is a top electronic components distributor with distribution centres in the ✔️USA, ✔️Europe, ✔️UK, ✔️South Africa, ✔️Australia, ✔️Asia, ✔️China and ✔️India - Electronic Parts Distributor - {Website-name}'))

@section('header')

    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "CreativeWorkSeries",
            "name": "Supplier of electronic components - BD Electronics Ltd Europe",
            "aggregateRating": {
                "@type": "AggregateRating",
                "ratingValue": "4.5",
                "bestRating": "5",
                "ratingCount": "1019"
            }
        }
    </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@graph": [{
                "@type": "Organization",
                "@id": "{{url('/')}}#organization",
                "name": "{{siteName()}}",
                "url": "{{url('/')}}",
                "logo": {
                    "@type": "ImageObject",
                    "@id": "{{url('/')}}#logo",
                    "url": "{{siteLogo()}}",
                    "contentUrl": "{{siteLogo()}}",
                    "caption": "{{siteName()}}",
                    "inLanguage": "en-US",
                    "width": "303",
                    "height": "70"
                }
            }]
        }
    </script>

@endsection

@section('content')
    <!--home middle -->
    <section class="middle-section">
        <div class="container">
            <div class="row">
                <!-- leftbar start -->
                <div class="col-xl-3 col-lg-4 col-md-3 sidebar home-sidebar">
                    <div class="inner-sidebar bg-white-radius">
                        <div class="nav menu-style sidebarmenu sidebarmenu-wrapper">
                            <h3><a href="{!! route('categories.index') !!}">PRODUCTS</a></h3>
                            <ul>
                                @foreach(categories() as $category)
                                @if(isset($category['children']) && is_array($category['children']) && count($category['children']) > 0)
                                <li class="menu-item-has-children">
                                    <a class="menu-toggle" href="{{route('categories.show', $category['slug'])}}">
                                        <!-- <span class="{{$category['icon_class']}}"></span> -->
                                        {{$category['name']}}
                                        @if(array_key_exists('children',$category))
                                            <span class="icon-right-arrow"></span>
                                        @endif
                                    </a>
                                    @if(array_key_exists('children',$category))
                                    <div class="sidebarmenu-wrapper">
                                        <ul class="">
                                            <li class="menu-subtitle">
                                                <span> {{$category['name']}}</span>
                                                <!-- <i class="{{$category['icon_class']}}"></i> -->
                                            </li>

                                            @foreach($category['children'] as $subCategory)

                                            <li class="menu-item-has-children">
                                                <a class="menu-toggle"
                                                   href="{{route('subcategories.show', [$category['slug'], $subCategory['slug']])}}">
                                                    {{$subCategory['name']}}
                                                    @if(array_key_exists('children',$subCategory) && is_array($subCategory['children']) && count($subCategory['children']))
                                                        <span class="icon-right-arrow"></span>
                                                    @endif

                                                </a>
                                                @if(array_key_exists('children',$subCategory) && is_array($subCategory['children']) && count($subCategory['children']))

                                                    <ul class="sub-menu sidebarmenu-wrapper">
                                                        @foreach($subCategory['children'] as $subSubCategory)
                                                            <li class="menu-item-has-children">
                                                                <a class="menu-toggle"
                                                                   href="{{route('subSubCategories.show', [$category['slug'], $subCategory['slug'], $subSubCategory['slug']])}}">
                                                                    {{$subSubCategory['name']}}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </li>
                                @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- leftbar end -->
                <!-- rightbar start -->
                <div class="col-xl-9 col-lg-8 rightbar home-rightbar">
                    {!! homePageSliderSection() !!}
                    <div class="leading-search-main" id="divHomeSearch">
                        <div class="leading-search bg-white-radius d-flex align-items-center" id="leading-search-home">
                            <span class="search-icon-leading"><span class="icon-serach-icon"></span></span>
                            <input type="text" class="form-control search-desk txtHomeSearch" placeholder="{!! $searchPlaceholder !!}">
                            <input type="text" class="form-control search-mobile txtHomeSearch" placeholder="Search..." tabindex="-1">
                            <span class="icon-close"></span>
                            <button type="button" class="btn btn-primary btnHomeSearch">Search</button>
                        </div>
                        <div class="search-products-main" id="search-products-main-home">
                            <div class="divSearchResult"></div>
                            <div class="divRecentSearch">
                                <div class="search-head body1-text">Recent Searches</div>
                                {!! getRecentSearchHtml() !!}
                            </div>
                        </div>
                    </div>


                    <!-- explore category start -->
                    <x-popular-category></x-popular-category>
                    <!-- explore category end -->

                    <!-- popular manufacture start -->
                    <x-popular-manufacturer></x-popular-manufacturer>
                    <!-- popular manufacture end -->
                    <h1 class="h2 mt-2" align="center">Leading Distributor of Electronic Components</h1>
                    <!-- recent technology start -->
                    @if(count($recentTechnologies))
                    <div class="recent-technology">
                        <h3 class="h2">Authorized Partners</h3>
                        <ul class="owl-carousel products-cards-slider">
                            @foreach($recentTechnologies as $technology)
                                <x-recent-technology :technology="$technology"></x-recent-technology>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <!-- recent technology end -->

                    <!-- latest news start -->
                    <div class="latest-news-main">
                        <x-latest-news :news="$latestNews"></x-latest-news>
                    </div>
                    <!-- latest news end -->

                    <!-- email form start -->
                    <div class="email-form-main bg-white-radius">
                        <div class="container">
                            <x-contact-form main-class=""></x-contact-form>
                        </div>
                    </div>
                    <!-- email form end -->

                </div>
                <!-- rightbar end -->
            </div>
        </div>
    </section>
@endsection
