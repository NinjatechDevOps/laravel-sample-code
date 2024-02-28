<header>
    <div class="container">
        <div class="col-md-12 col-lg-12">
            <div class="row justify-content-between align-items-center">
                <div class="header-left d-flex align-items-center">
                    <div class="logo">
                        <a href="{!!url('/')!!}" title="{!!siteName()!!}">
                            <img src="{!! siteLogo() !!}" title="{!! siteName() !!}" alt="{!! siteName() !!}">
                        </a>
                    </div>
                    <div class="header-search-main">
                        <i class="header-search show-desk"></i>
                        <span class="button-text show-desk">{{request()->q ?? 'Search...'}}</span>
                        <span class="icon-serach-header show-mobile"></span>
                    </div>
                    <nav class="nav-wrapper">
                        <div class="header-item-center">
                            <div class="menu">
                                <a href="javascript:void(0);" class="js-nav-toggle">
                                    <span></span>
                                </a>
                                <nav class="scroll-top">
                                    <div class="nav-toggle">
                                        <span class="nav-back"></span>
                                        <span class="nav-title">Product</span>
                                        <span class="nav-close"></span>
                                    </div>
                                    <ul class="">
                                        <li class="menu-item-has-children has-dropdown product-main-menu-items @if(request()->routeIs('categories.*') || request()->routeIs('product*')) active @endif">
                                            <a href="{!!route('categories.index')!!}">Products <span class="icon-down-arrow"></span></a>
                                            <div class="menu-subs menu-mega menu-column-4">
                                                <div class="list-item menu-style header-menu-category-item">
                                                    <div class="loader">
                                                        <div class="loader__figure"></div>
                                                        <p class="loader__label"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="@if(request()->routeIs('manufacturer.*')) active @endif"><a href="{!!route('manufacturer.index')!!}" title="Manufacturers">Manufacturers</a>
                                        </li>
                                        <li class="@if(request()->routeIs('news.*')) active @endif"><a href="{!!route('news.index')!!}" title="News">News</a></li>
                                        <li class="@if(request()->segment(1) == 'about-us') active @endif"><a href="{!! route('single_slug_page', ['about-us']) !!}" title="About Us">About Us</a></li>
                                        <li class="show-mobile"><a href="{!! route('single_slug_page', ['contact-us']) !!}" title="Contact Us">contact Us</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
                <div class="header-right">
                    <div class="request-quote">
                        <ul>
                            <li class="header-re-quote">
                                <a href="{!! route('request-quote') !!}" title="" class="btn btn-secondary  btn-sm-rounded btn-xs-icon"><span id="span_cart_item">@if(count(Session::get('inquiryList', []))) My Inquiries ({!! count(Session::get('inquiryList', [])) !!}) @else Request Quote @endif</span> <i class="icon-quote-header"></i>
                                <div class="show-mobile" id="div_cart_item_mobile">@if(count(Session::get('inquiryList', [])))<span class="product-notification">{!! count(Session::get('inquiryList', [])) !!}</span>@endif</div>
                                </a>
                            </li>
                            <li class="contact-h">
                                <a href="javascript:void(0);" title="Contact Us"
                                   class="btn btn-primary btn-sm-rounded btn-xs-icon" data-toggle="modal"
                                   data-target="#contact-us-popup"><span>Contact Us</span><i class="icon-contactus"></i></a>
                            </li>
                            <li class="currency-dropdown">
                                <a data-toggle="collapse" href="#currency-info" role="button" aria-expanded="false"
                                   aria-controls="currency-info" class="currencyinfo small-link small-link-mobile collapsed" rel="nofollow"><span class="{!!currentCurrency()->icon_class!!}"></span>{!!currentCurrency()->abbr!!}
                                    <span class="currency-arrow icon-currency-icon"></span>
                                </a>
                                <div class="collapse radius4" id="currency-info">
                                    <ul>
                                        @foreach(exchangeRates() as $exchangeRate)
                                            <li class="{!!$exchangeRate->abbr == 'EUR' ? 'euro' : ''!!} {!!currentCurrency()->abbr == $exchangeRate->abbr ? 'selected-currency' : ''!!}">
                                                <a rel="nofollow" href="?currency={!!$exchangeRate->abbr!!}" title="{!!$exchangeRate->abbr!!}" class="body2-text d-flex justify-content-between"><span class="{!!$exchangeRate->icon_class!!}"></span> {!!$exchangeRate->abbr!!}<span></span></a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header end -->

<div class="header-search-window" style="display: none">
    <div class="search-popup-overlay close-search-main"></div>
    <div class="container">
        <div class="header-search-popup" id="divTopSearch">
            <div class="search-input-submit d-flex align-items-center radius4">
                <span class="submit-icon"></span>
                <input type="search" id="search_term" class="form-control" placeholder="Search..." value="{{request()->q}}" autocomplete="off" tabindex="-1">
                <div class="close-search close-search-main">
                    <a href="javascript:void(0);"><span class="icon-close"></span></a>
                </div>
            </div>
            <div class="search-products-main">
                <div class="divSearchResult"></div>
                <div class="divRecentSearch">
                    <div class="search-head body1-text">Recent Searches</div>
                    {!! getRecentSearchHtml() !!}
                </div>
            </div>
        </div>
    </div>
</div>
