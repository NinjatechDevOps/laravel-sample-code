@extends('layouts.frontend')

@section('content')

    <div class="breadcrumb-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{url('/')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Search results for: '{{ request()->s }}'</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>


    <section class="all-product-category-main">
        <div class="container">

            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="product-category">
                        <h4>Search result for "{{ request()->s }}"</h4>
                    </div>
                </div>
            </div>


            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header"></div>

                        <div class="card-body">
                            @foreach($products as $product)
                                <li class="nav-item">
                                    <a class="nav-link"
                                       href="{{ $product->detail_url }}">{{ $product->part_number }}
                                    </a>
                                </li>
                            @endforeach
                            {{ $products->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </section>

@endsection
