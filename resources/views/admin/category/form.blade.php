@extends('layouts.admin.app')
<!-- Dynamic Title -->
@section('title') {{ $category->id ? __('Edit Category') :  __('Create Category')}} @endsection
<!-- Bread crumb -->
@section('breadcrumb-content')
<li class="breadcrumb-item"><a href="{{ route('admin.home') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">{{ __('Categories') }}</a></li>
<li class="breadcrumb-item active">{{ $category->id ? __('Edit Category') :  __('Create Category') }}</li>
@endsection
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <!-- <div class="card-header">{{ $category->id ? __('Edit Category') :  __('Create Category')}}</div> -->
                <div class="card-body">
                    <form id="frmAddUpdate" action="{{ $category->id ? route('admin.categories.update', $category->id) :  route('admin.categories.store')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        @if($category->id)
                        @method('PUT')
                        @endif

                        <div class="row mb-3">
                            <label for="parent_id" class="col-md-2 col-form-label">{{ __('Select Main Category') }}</label>
                            <div class="col-md-10">
                                @if(auth()->user()->hasRole('Admin') || !($category->id))
                                <select id="parent_id" class="form-select @error('parent_id') is-invalid @enderror" name="parent_parent_id">
                                    <option value=""></option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" 
                                        @if(
                                            (!empty($parentParentCategory) && old('parent_parent_id', $parentParentCategory->id) == $cat->id) ||
                                            (!empty($parentCategory) && old('parent_id', $parentCategory->id) == $cat->id)
                                        ) selected 
                                        @endif>{{ $cat->name }}
                                    </option>                                    
                                    @endforeach
                                </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $category->parent->name ?? "" }}" readonly>
                                @endif
                                @error('parent_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="parent_parent_id" class="col-md-2 col-form-label">{{ __('Select Sub Category') }}</label>
                            <div class="col-md-10">
                                @if(auth()->user()->hasRole('Admin') || !($category->id))
                                <select id="parent_parent_id" class="form-select @error('parent_id') is-invalid @enderror" name="parent_id">
                                    <option value=""></option>
                                    @if((!empty($subCategories) && !empty($category->id)))
                                        @foreach($subCategories as $subCat)
                                        <option value="{{ $subCat->id }}" @if(isset($parentCategory) && old('parent_id', $parentCategory->id) == $subCat->id) selected @endif>{{ $subCat->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @else
                                    <input type="text" class="form-control" value="{{ $category->parent->name ?? "" }}" readonly>
                                @endif
                                @error('parent_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="name" class="col-md-2 col-form-label">{{ __('Name') }}<span style="color: red;">*</span> </label>
                            <div class="col-md-10">
                                <input id="name" type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" value="{{ old('name', $category->name) }}" autofocus>
                                @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="slug" class="col-md-2 col-form-label">{{ __('Slug') }}<span style="color: red;">*</span> </label>
                            <div class="col-md-10">
                                <input id="slug" type="text" class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" name="slug" value="{{ old('slug', $category->slug) }}">
                                @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="description" class="col-md-2 col-form-label">{{ __('Description') }}</label>
                            <div class="col-md-10">
                                <textarea id="description" rows="10" type="text" class="ckeditor form-control @error('description') is-invalid @enderror" name="description">{{ old('description', $category->description) }} </textarea>
                                @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="meta_title" class="col-md-2 col-form-label">{{ __('Meta Title') }}</label>
                            <div class="col-md-10">
                                <input id="meta_title" type="text" class="form-control @error('meta_title') is-invalid @enderror" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}">
                                @error('meta_title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="meta_description" class="col-md-2 col-form-label">{{ __('Meta Description') }}</label>
                            <div class="col-md-10">
                                <textarea id="meta_description" type="text" class="form-control @error('meta_description') is-invalid @enderror" name="meta_description">{{ old('meta_description', $category->meta_description) }} </textarea>
                                @error('meta_description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="image_file" class="col-md-2 col-form-label">{{ __('Image') }}</label>
                            <div class="col-md-10">
                                <input  accept="image/jpeg,image/png,image/jpg,image/webp,image/svg" id="image_file" type="file" class="form-control @error('image_file') is-invalid @enderror" name="image_file">
                                @error('image_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                                @if($category->image_url && $category->image)
                                    <div><img src="{!!$category->image_url!!}" class="mt-2" style="max-width: 100px;" id="image_file_preview"></div>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="icon-selector" class="col-md-2 col-form-label">{{ __('Icon Selector') }}</label>
                            <div class="col-md-10">
                                <input type="text" class="form-control iconpicker {{ $errors->has('icon_name') ? 'is-invalid' : '' }}" placeholder=" Icon Picker" aria-label="Icone Picker" aria-describedby="basic-addon1" value="{{ old('icon_name', $category->icon_name) }}" name="icon_name" autocomplete="off" />
                                @error('icon_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-md-12 text-center">
                                <a href="{{ route('admin.categories.index') }}" class="btn common-back-button-link">{{ __('Back') }}</a>
                                <button type="submit" class="btn btn-primary">
                                    {{ $category->id ? __('Update') :  __('Create')}}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript" src="{!! asset('assets/admin/js/pages/categories.js') !!}"></script>
@endsection
