<?php

namespace Phobrv\Brvagency\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Phobrv\BrvCore\Repositories\PostRepository;
use Phobrv\BrvCore\Repositories\TermRepository;
use Phobrv\BrvCore\Repositories\UserRepository;
use Phobrv\BrvCore\Services\HandleMenuServices;
use Phobrv\BrvCore\Services\UnitServices;
use Phobrv\BrvCore\Services\PostServices;

class AgencyController extends Controller
{
    protected $unitService;
    protected $termRepository;
    protected $postRepository;
    protected $userRepository;
    protected $handleMenuService;
    protected $type;
    protected $taxonomy;
    protected $postService;

    public function __construct(
        UserRepository $userRepository,
        TermRepository $termRepository,
        PostRepository $postRepository,
        PostServices $postService,
        HandleMenuServices $handleMenuService,
        UnitServices $unitService
    ) {
        $this->handleMenuService = $handleMenuService;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->termRepository = $termRepository;
        $this->unitService = $unitService;
        $this->postService = $postService;

        $this->type = config('option.post_type.agency');
        $this->taxonomy = config('term.taxonomy.province');
    }

    public function index()
    {
        $data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
            [
                ['text' => 'Quản lý đại lý', 'href' => ''],
            ]
        );
        try {
            $data = $this->takeAgencyByProvinceSelect($data);

            $data['submit_label'] = "Create";
            return view('phobrv::agency.index')->with('data', $data);
        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }

    public function takeAgencyByProvinceSelect($data)
    {
        $user = Auth::user();
        $data['provinces'] = $this->termRepository->getArrayTerms('province');
        $data['provinces'][0] = 'All';
        $data['select'] = $this->userRepository->getMetaValueByKey($user, 'province_select');
        $term = $this->termRepository->with('posts')->findWhere(['id' => $data['select']])->first();
        if ($term) {
            $data['posts'] = $this->handleMenuService->handleMenuItem($term->posts->sortBy('order'));
        } else {
            $data['posts'] = $this->handleMenuService->handleMenuItem($this->postRepository->where('type', $this->type)->get());
        }

        return $data;

    }

    public function setGroupSelect($id)
    {
        $user = Auth::user();
        $this->userRepository->insertMeta($user, array('province_select' => $id));
        return redirect()->route('agency.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->merge(['slug' => $this->unitService->renderSlug($request->title)]);

        $request->validate(
            [
                'slug' => 'required|unique:posts',
            ],
            [
                'slug.unique' => 'Name đã tồn tại',
                'slug.required' => 'Name không được phép để rỗng',
            ]
        );
        try {
            $data = $request->all();
            $term = $this->termRepository->find($data['term_id']);
            $data['order'] = ($term->posts->count() > 0) ? (($term->posts->sortByDesc('order')->first()['order']) + 1) : 1;
            $data['user_id'] = $user->id;
            $data['status'] = '1';
            $data['type'] = $this->type;
            $agency = $this->postRepository->create($data);
            $agency->terms()->sync($data['term_id']);

            $msg = __('Create agency success!');
            return redirect()->route('agency.index')->with('alert_success', $msg);

        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['breadcrumbs'] = $this->unitService->generateBreadcrumbs(
            [
                ['text' => 'Manager Agency', 'href' => ''],
            ]
        );

        try {
            $data = $this->takeAgencyByProvinceSelect($data);

            $data['post'] = $this->postRepository->with('postMetas')->find($id);

            $data['term'] = $data['post']->terms()->where('taxonomy', $this->taxonomy)->first();
            $data['select'] = $data['term']->id ?? '0';
            $data['submit_label'] = "Update";
            $data['meta'] = $this->postService->getMeta($data['post']->postMetas);

            return view('phobrv::agency.index')->with('data', $data);
        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->merge(['slug' => $this->unitService->renderSlug($request->title)]);
        $request->validate(
            [
                'slug' => 'required|unique:posts,slug,' . $id,
            ],
            [
                'slug.unique' => 'Name đã tồn tại',
                'slug.required' => 'Name không được phép để rỗng',
            ]
        );
        try {
            $data = $request->all();
            $agency = $this->postRepository->update($data, $id);
            $agency->terms()->sync($data['term_id']);
            $this->handleMeta($agency, $data);

            $msg = __('Update agency success!');
            if (isset($request->typeSubmit) && $request->typeSubmit == 'update') {
                return redirect()->route('agency.edit', ['agency' => $id])
                ->with('alert_success', $msg);
            } else {
                return redirect()->route('agency.index')
                ->with('alert_success', $msg);
            }

        } catch (Exception $e) {
            return back()->with('alert_danger', $e->getMessage());
        }
    }
    public function updateUserSelect(Request $request)
    {
        $user = Auth::user();
        $this->userRepository->insertMeta($user, array('province_select' => $request->select));
        return redirect()->route('agency.index');
    }
    public function changeOrder(Request $request, $menu_id, $type)
    {

        $menu = $this->postRepository->find($menu_id);
        $term = $menu->terms->first();
        $this->postRepository->resetOrderPostByTermID($term->id);
        $parent = $menu->parent;
        $curOrder = $menu->order;
        if ($type == 'plus') {
            if ($parent == 0) {
                $menuReplace = $term->posts()->where('parent', '0')->where('order', '<', $curOrder)->orderBy('order', 'desc')->first();
            } else {
                $menuReplace = $term->posts()->where('parent', $parent)->where('order', '<', $curOrder)->orderBy('order', 'desc')->first();
            }
        } else {
            if ($parent == 0) {
                $menuReplace = $term->posts()->where('parent', '0')->where('order', '>', $curOrder)->orderBy('order')->first();
            } else {
                $menuReplace = $term->posts()->where('parent', $parent)->where('order', '>', $curOrder)->orderBy('order')->first();
            }
        }

        if ($menuReplace) {
            $newOrder = $menuReplace->order;
            $this->postRepository->update(['order' => $newOrder], $menu->id);
            $this->postRepository->update(['order' => $curOrder], $menuReplace->id);
        }

        return redirect()->route('agency.index')->with('alert_success', __('Change menu item order success'));
    }

    public function destroy($id)
    {
        $this->postRepository->destroy($id);
        $msg = __("Delete agency success!");
        return redirect()->route('agency.index')->with('alert_success', $msg);
    }

    public function handleMeta($agency, $data)
    {

    }
}
