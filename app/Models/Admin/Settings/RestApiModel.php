<?php

namespace App\Models\Admin\Settings;


use App\Models\Admin\BaseModel;
use Illuminate\Support\Facades\DB;

class RestApiModel extends BaseModel
{
    // get active user
    public static function getUserActiveApi() {
        return DB::table('personal_access_tokens as a')
                ->select('b.user_id','b.user_name','d.role_name','e.name as branch_name')
                ->selectRaw('COUNT(a.token) AS jumlah_token')
                ->join('app_user as b', 'a.tokenable_id', '=', 'b.user_id')
                ->join('app_role_user as c', 'b.user_id', '=', 'c.user_id')
                ->join('app_role as d', 'c.role_id', '=', 'd.role_id')
                ->leftJoin('master_branch as e','b.branch_id','=','e.id')
                ->groupBy('b.user_id','b.user_name','d.role_name','e.name')
                ->orderBy('b.user_name')        
                ->paginate(20);
    }

    public static function getUserActiveApiSearch($user_name) {
        return DB::table('personal_access_tokens as a')
                ->select('b.user_id','b.user_name','d.role_name','e.name as branch_name')
                ->selectRaw('COUNT(a.token) AS jumlah_token')
                ->join('app_user as b', 'a.tokenable_id', '=', 'b.user_id')
                ->join('app_role_user as c', 'b.user_id', '=', 'c.user_id')
                ->join('app_role as d', 'c.role_id', '=', 'd.role_id')
                ->leftJoin('master_branch as e','b.branch_id','=','e.id')
                ->where('b.user_name', 'LIKE','%'.$user_name.'%')
                ->groupBy('b.user_id','b.user_name','d.role_name','e.name')
                ->orderBy('b.user_name')        
                ->paginate(20);
    }

    public static function delete($id) {
        return DB::table('personal_access_tokens')->where('tokenable_id', $id)->delete();
    }
}
