<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Auth;
use DataTables;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Storage;
use Image;

/**
 * Class CategoryController
 *
 * @package App\Http\Controllers\Admin
 */
class CategoryController extends Controller
{
    public function __construct()
    {
        /* User Permissions */
        $this->middleware(
            'permission:category-list|category-create|category-edit|category-delete',
            ['only' => ['index', 'store']]
        );
        $this->middleware('permission:category-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:category-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:category-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|JsonResponse
     *
     * @throws Exception
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::with(['parent']);

            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name . ' <a href="'.$row->detail_url.'" target="_blank"><i class="fas fa-external-link-alt"></i></a>';
                })
                ->addColumn(
                    'action',
                    function ($row) {
                        $btn = '';
                        if (Auth::user()->can('category-edit')) {
                            $btn = '<a href="' . route('admin.categories.edit', $row->id) . '"
                            data-original-title="Edit"
                            class="edit btn btn-primary btn-sm">Edit</a>';
                        }
                        if (Auth::user()->can('category-delete')) {
                            $btn = $btn . ' <a href="javascript:void(0)"
                            data-toggle="tooltip"
                            data-id="' . $row->id . '"
                            data-action="' . route(
                                'admin.categories.destroy',
                                ['category' => $row->id]
                            ) . '"
                            data-original-title="Delete"
                            class="btn btn-danger btn-sm deleteCategory">Delete</a>';
                        }

                        return $btn;
                    }
                )
                ->addColumn(
                    'parent',
                    function ($row) {
                        return $row->parent ? $row->parent->name . ' <a href="'.$row->parent->detail_url.'" target="_blank"><i class="fas fa-external-link-alt"></i></a>' : '';
                    }
                )
                ->addColumn(
                    'parent_parent',
                    function ($row) {
                        $parent = $row->parent;
                        if($parent) {
                            $parent_parent = $parent->parent;
                            return $parent_parent ? $parent_parent->name . ' <a href="'.$parent_parent->detail_url.'" target="_blank"><i class="fas fa-external-link-alt"></i></a>' : '';
                        }
                        return '';
                    }
                )
                ->addColumn(
                    'image',
                    function ($row) {
                        return $row->image
                            ? '<a href="' . $row->image_url . '"
                        target="_blank"> <img class="image-table-thumb" src="' . $row->image_url . '"/></a>'
                            : '';
                    }
                )
                ->rawColumns(['action', 'image', 'name', 'parent', 'parent_parent'])
                ->make(true);
        }

        return view('admin.category.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::user()->id;
        $image = $request->image_file;
        if ($image) {
            $title = pathinfo($image->getClientOriginalName(), \PATHINFO_FILENAME);
            $imageName = $title."-".time() . '.webp';
            $imgFile = Image::make($image->getRealPath());
            /*
            $imgFile->trim();
            if ($imgFile->width() > 250) {
                $imgFile->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            if ($imgFile->height() > 250) {
                $imgFile->resize(null, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            $canvas = Image::canvas(250, 250, '#ffffff');
            */
            $canvas = Image::canvas($imgFile->width(), $imgFile->height(), '#ffffff');
            $canvas->insert($imgFile, 'center')->encode('webp');

            Storage::put(config('constants.CATEGORY_IMAGE_PATH').$imageName, $canvas->stream());
            $data['image'] = $imageName;
        }
        if(@$data['parent_parent_id'] && @$data['parent_id'] == "") {
            $data['parent_id'] = $data['parent_parent_id'];
        }

        Category::create($data);

        if($request->ajax()) {
            session()->flash('success', 'Category created successfully');
            return response()->json(['redirectUrl' => route('admin.categories.index')]);
        }
        return redirect(route('admin.categories.index'))->with('success', 'Category created successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $categories = Category::whereNull('parent_id')->get();
        // dd($categories->pluck('id'));
        $subCategories = Category::whereIn('parent_id', $categories->pluck('id'))->get();
        // dd($subCategories[0]);
        $category = new Category();

        return view('admin.category.form', compact('category', 'categories', 'subCategories'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Application|Factory|View
     */
    public function edit(Category $category)
    {
        $categories = Category::where(function ($q) use ($category) {
            $q->whereNull('parent_id')->where('id', '!=', $category->id);
        })->get();

        $subCategories = '';
        $parentCategory = '';
        if(!empty($category['parent_id'])) {
            $parentCategory = Category::where('id', $category['parent_id'])->first(); 
            $subCategories = Category::where('parent_id', $parentCategory->id)->get();
        }
        $parentParentCategory = '';
        if(isset($parentCategory['parent_id'])) {
            $parentParentCategory = Category::where('id', $parentCategory['parent_id'])->first();
            $subCategories = Category::where('parent_id', $parentCategory->parent_id)->get();
        }
        // dd($subCategories);

        return view('admin.category.form', compact('category', 'categories', 'parentCategory', 'parentParentCategory', 'subCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function update(CategoryRequest $request, Category $category)
    {
        // dd($request->all());
        $data = $request->validated();
        // if($category->import_batch_id) {
        //     unset($data['name']);
        // }
        $image = $request->image_file;
        if ($image) {
            $title = pathinfo($image->getClientOriginalName(), \PATHINFO_FILENAME);
            $imageName = $title."-".time() . '.webp';
            $imgFile = Image::make($image->getRealPath());
            $imgFile->trim();
            /*
            if ($imgFile->width() > 250) {
                $imgFile->resize(250, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            if ($imgFile->height() > 250) {
                $imgFile->resize(null, 250, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            */
            $canvas = Image::canvas($imgFile->width(), $imgFile->height(), '#ffffff');
            $canvas->insert($imgFile, 'center')->encode('webp');
            Storage::put(config('constants.CATEGORY_IMAGE_PATH').$imageName, $canvas->stream());
            $data['image'] = $imageName;
        }
        if(@$data['parent_parent_id'] && @$data['parent_id'] == "") {
            $data['parent_id'] = $data['parent_parent_id'];
        }
        $category->update($data);

        if($request->ajax()) {
            session()->flash('success', 'Category updated successfully');
            return response()->json(['redirectUrl' => route('admin.categories.index')]);
        }
        return redirect(route('admin.categories.index'))->with('success', 'Category updated successfully');
    }

    /**
     * @return JsonResponse
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['success' => 'Category deleted successfully']);
    }

    public function getCategories(Request $request)
    {

        $parent_id = $request->input('parent_id');

        $categories = $parent_id ? Category::where('parent_id', $parent_id)->get() : Category::whereNull('parent_id')->get();
        
        return response()->json($categories);
    }


}
