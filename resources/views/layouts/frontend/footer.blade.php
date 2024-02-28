<!-- footer start -->
<footer>
    <div class="show-mobile">
        <a id="scroll">
          <span class="icon-backtop-arrow"></span>
        </a>
        </div>
    <div class="container">
        <div class="row">
            <div class="col footer-logo-info">
                <div class="footer-logo">
                    <a href="{!! route('homepage') !!}" title="{!!siteName() !!}">
                        <img src="{!! getSetting('site_logo_white') !!}" alt="{!! siteName() !!}" title="{!! siteName() !!}">
                    </a>
                </div>
                <span class="address-email">
                    <span class="locations d-flex  body1-text"><span class="icon-location"></span> <i>{!! getSetting('contact_address') !!}</i></span>
                    <span class="footer-email d-flex align-items-center">
                        <span class="icon-msg-icon-footer"><span class="path1"></span><span class="path2"></span><span
                        class="path3"></span></span>
                        <!-- <a class="body1-text" href="mailto:{!! getContactEmail() !!}" title="mailus">Contact Us</a> -->
                        <a class="body1-text show-desk" href="#" title="Contact Us" data-toggle="modal" data-target="#contact-us-popup">Contact Us</a>
                        <a class="body1-text show-mobile" href="{!! route('single_slug_page', ['contact-us']) !!}" title="Contact Us">Contact Us</a>
                    </span>
            </span>
            </div>
            <div class="col footer-links">
                <div class="caption"><strong>General</strong></div>
                <ul>
                    <li>
                        <a class="button-text" href="{!! route('categories.index') !!}" title="Products">Products</a>
                    </li>
                    <li>
                        <a class="button-text" href="{!! route('manufacturer.index') !!}" title="Manufacturers">Manufacturers</a>
                    </li>
                    <li>
                        <a class="button-text" href="{!! route('news.index') !!}" title="News">News</a>
                    </li>
                    <li>
                        <a class="button-text" href="{!! route('single_slug_page', ['about-us']) !!}" title="About Us">About Us</a>
                    </li>
                </ul>
            </div>
            @php
                $lineCardPdf = getLineCardPdf();
            @endphp
            <div class="col footer-links">
                <div class="caption"><strong>Legal</strong></div>
                <ul>
                    <li>
                        <a class="button-text" href="{!! route('single_slug_page', ['terms-conditions']) !!}" title="Terms of Use">Terms of Use</a>
                    </li>
                    <li>
                        <a class="button-text" href="{!! route('single_slug_page', ['privacy-policy']) !!}" title="Privacy Policy">Privacy Policy</a>
                    </li>
                    <li>
                        <a class="button-text" href="{!! route('single_slug_page', ['shipping-policy']) !!}" title="Shipment">Shipment</a>
                    </li>
                    <li>
                        @if ($lineCardPdf !== null)
                        <a class="button-text" target="_blank" href="{{ getLineCardPdf() }}" title="Line Card">Line Card</a>
                        @endif
                    </li>
                </ul>
            </div>
            <div class="col footer-links">
                <div class="caption"><strong>Services</strong></div>
                <ul>
                    <li>
                        <a class="button-text" href="{!! route('request-quote') !!}" title="Request a Quotation">Request Quote</a>
                    </li>
                    <li>
                        <a class="button-text show-desk" href="#" title="Contact Us" data-toggle="modal" data-target="#contact-us-popup">Contact Us</a>
                        <a class="button-text show-mobile" href="{!! route('single_slug_page', ['contact-us']) !!}" title="Contact Us">Contact Us</a>
                    </li>
                </ul>
            </div>
            <div class="col contact-info">
                <div class="caption"><strong>Social media</strong></div>
                <ul>
                    @if($insta = getSetting('social_insta'))
                        <li>
                            <a href="{!! $insta !!}" rel="nofollow" target="_blank" title="Instagram">
                                <span class="icon-insta"></span>
                            </a>
                        </li>
                    @endif
                    @if($fb = getSetting('social_facebook'))
                        <li>
                            <a href="{!! $fb !!}" rel="nofollow" target="_blank" title="Facebook">
                                <span class="icon-facebook"></span>
                            </a>
                        </li>
                    @endif
                    @if($linkedIn = getSetting('social_linkedin'))
                        <li>
                            <a href="{!! $linkedIn !!}" rel="nofollow" target="_blank" title="Linkedinn">
                                <span class="icon-linkedin"></span>
                            </a>
                        </li>
                    @endif
                    @if($twitter = getSetting('social_twitter'))
                        <li>
                            <a href="{!! $twitter !!}" rel="nofollow" target="_blank" title="Twitter">
                                <span class="icon-twitter"></span>
                            </a>
                        </li>
                    @endif
                </ul>
                @php
                    $footerData = getFooterLogoAndUrl();
                @endphp
                @if ($footerData)
                    <ul class="footer-iso-logo-link">
                        @foreach ($footerData as $item)
                            <li>
                                @if ($item['link'])
                                    <a href="{{ $item['link'] }}"><img src="{{ $item['logo'] }}" alt="Footer Logo"></a>
                                @else
                                    <a href="javascript:void(0)"><img src="{{ $item['logo'] }}" alt="Footer Logo"></a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="currency-lang d-mobile">
            <ul>
                @foreach(exchangeRates() as $exchangeRate)
                    <li>
                        <a rel="nofollow"
                            href="?currency={!! $exchangeRate->abbr!!}"
                           title="{!! $exchangeRate->abbr!!} - {!! $exchangeRate->name!!}"
                           class="small-link {!!currentCurrency()->abbr == $exchangeRate->abbr ? 'active' : ''!!}">
                            <span class="{!! $exchangeRate->icon_class!!}"></span><i>{!! $exchangeRate->abbr!!} - {!! $exchangeRate->name!!}</i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="row copyright">
            <div class="col-lg-12 col-md-12">
                <div class="copyrght text-center">
                    <strong class="caption">© {!! now()->format('Y') !!} <a class="small-link" href="javascript:void(0);" title="{!! siteName() !!}">{!! siteName() !!}</a>. All rights reserved.</strong>
                </div>
            </div>
        </div>
    </div>
</footer>
<!-- Modal -->
<div class="modal fade" id="contact-us-popup" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content bg-white-radius">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true" class="icon-close"></span>
            </button>

            <!-- email form start -->
            <div class="email-form-main">
                <div class="container">
                    <x-contact-form main-class=""></x-contact-form>
                </div>
            </div>
            <!-- email form end -->

        </div>
    </div>
</div>
<!--modal end-->
<!-- footer end -->
