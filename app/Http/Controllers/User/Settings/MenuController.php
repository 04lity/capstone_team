<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin\Settings\Menu;

class MenuController extends BaseController
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // authorize
        Menu::authorize('R');
        
        // get data with pagination
        $rs_menu = Menu::getAllPaginate();
        // data
        $data = ['rs_menu' => $rs_menu];
        // view
        return view('admin.settings.menu.index', $data );
    }

     /**
     * Search data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // authorize
        Menu::authorize('R');

        // data request
        $menu_name = $request->menu_name;
        // new search or reset
        if($request->action == 'search') {
            // get data with pagination
            $rs_menu = Menu::getAllSearch($menu_name);
            // data
            $data = ['rs_menu' => $rs_menu, 'menu_name'=>$menu_name];
            // view
            return view('admin.settings.menu.index', $data );
        }
        else {
            return redirect('/admin/settings/menu');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {
        // authorize
        Menu::authorize('C');

        // get data
        $rs_menu = Menu::getAll();

        // data
        $data = [
            'rs_menu' => $rs_menu,
        ];
        //view
        return view('admin.settings.menu.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addProcess(Request $request)
    {
        // authorize
        Menu::authorize('C');

        // Validate & auto redirect when fail
        $rules = [
            'parent_menu_id' => 'required',
            'menu_name' => 'required',
            'menu_description' => 'required',
            'menu_url' => 'required',
            'menu_sort' => 'required',
            'menu_group' => 'required',
            'menu_active' => 'required',
            'menu_display' => 'required',
        ];
        $this->validate($request, $rules );

        // cek if new menu
        if($request->parent_menu_id == 'parent') {
            $parent_menu_id = NULL;
        }
        else {
            $parent_menu_id = $request->parent_menu_id;
        }

        $menu_id = Menu::makeShortId();

        // params
        $params =[
            'menu_id' => $menu_id,
            'parent_menu_id' => $parent_menu_id,
            'menu_name' => $request->menu_name,
            'menu_description' => $request->menu_description,
            'menu_url' => $request->menu_url,
            'menu_sort' => $request->menu_sort,
            'menu_group' => $request->menu_group,
            'menu_icon' => $request->menu_icon,
            'menu_active' => $request->menu_active,
            'menu_display' => $request->menu_display,
            'created_by'   => Auth::user()->user_id,
            'created_date'  => date('Y-m-d H:i:s')
        ];

        // process
        if (Menu::insert($params)) {
            // insert to app role menu
            // system adminitrator
            $params = [
                'role_id' => '01',
                'menu_id' => $menu_id,
            ];
            Menu::insert_role_menu($params);

            // flash message
            session()->flash('success', 'Data berhasil disimpan.');
            return redirect('/admin/settings/menu/add');
        }
        else {
            // flash message
            session()->flash('danger', 'Data gagal disimpan.');
            return redirect('/admin/settings/menu/add');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // authorize
        Menu::authorize('U');

        // get data
        $menu = Menu::getById($id);
        $rs_menu = Menu::getAll();

        // data
        $data = [
            'menu'  => $menu,
            'rs_menu' => $rs_menu,
        ];
        //view
        return view('admin.settings.menu.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editProcess(Request $request)
    {
        // authorize
        Menu::authorize('U');

        // Validate & auto redirect when fail
        $rules = [
            'menu_id' => 'required',
            'parent_menu_id' => 'required',
            'menu_name' => 'required',
            'menu_description' => 'required',
            'menu_url' => 'required',
            'menu_sort' => 'required',
            'menu_group' => 'required',
            'menu_active' => 'required',
            'menu_display' => 'required',
        ];
        $this->validate($request, $rules );

        // cek if new menu
        if($request->parent_menu_id == 'parent') {
            $parent_menu_id = NULL;
        }
        else {
            $parent_menu_id = $request->parent_menu_id;
        }

        // params
        $params =[
            'parent_menu_id' => $parent_menu_id,
            'menu_name' => $request->menu_name,
            'menu_description' => $request->menu_description,
            'menu_url' => $request->menu_url,
            'menu_sort' => $request->menu_sort,
            'menu_group' => $request->menu_group,
            'menu_icon' => $request->menu_icon,
            'menu_active' => $request->menu_active,
            'menu_display' => $request->menu_display,
            'modified_by'   => Auth::user()->user_id,
            'modified_date'  => date('Y-m-d H:i:s')
        ];

        // process
        if (Menu::update($request->menu_id,$params)) {
            // flash message
            session()->flash('success', 'Data berhasil disimpan.');
            return redirect('/admin/settings/menu');
        }
        else {
            // flash message
            session()->flash('danger', 'Data gagal disimpan.');
            return redirect('/admin/settings/menu/edit/'.$request->menu_id);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteProcess($id)
    {
        // authorize
        Menu::authorize('D');

        // get data
        $menu = Menu::getById($id);
    
        // if exist
        if(!empty($menu)) {
            // cek sub menu
            if(Menu::cekSubMenu($id)){
                // process
                if(Menu::delete($id)) {
                    // flash message
                    session()->flash('success', 'Data berhasil dihapus.');
                    return redirect('/admin/settings/menu');
                }
                else {
                    // flash message
                    session()->flash('danger', 'Data gagal dihapus.');
                    return redirect('/admin/settings/menu');
                }
            }
            else {
                // flash message
                session()->flash('danger', 'Data gagal dihapus,silahkan hapus sub-menu terlebih dahulu.');
                return redirect('/admin/settings/menu');
            }
        }
        else {
            // flash message
            session()->flash('danger', 'Data tidak ditemukan.');
            return redirect('/admin/settings/menu');
        }
    }

    /**
     * Show the form for add the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function roleMenu($id)
    {
        // authorize
        Menu::authorize('C');

        // get data
        $rs_role = Menu::getRole();
        $rs_role_menu = Menu::getRoleMenu($id);
        $menu = Menu::getMenuById($id);

        // data
        $data = [
            'menu'  => $menu,
            'rs_role' => $rs_role,
            'rs_role_menu' => $rs_role_menu->toArray()
        ];

        //view
        return view('admin.settings.menu.rolemenu', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function roleMenuProcess(Request $request)
    {
        // authorize
        Menu::authorize('C');

        // data
        $role_id = $request->role_id;

        // delete role menu
        Menu::delete_role_menu($request->menu_id);

        if(!empty($role_id)) {
            // looping insert
            for($i=0; $i < count($role_id); $i++) {
                // params
                $params =[
                    'role_id' => $role_id[$i],
                    'menu_id' => $request->menu_id,
                ];
    
                // upsert
                Menu::insert_role_menu($params);
            }
        }

        // flash message
        session()->flash('success', 'Data berhasil disimpan.');
        return redirect('/admin/settings/menu');
    }
}
