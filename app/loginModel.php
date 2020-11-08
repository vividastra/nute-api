<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Http\LoginModelController;
use Illuminate\Support\Facades\Schema;
class loginModel extends Model
{

    public function getdata(){
        $data = DB::table('users')->select('*')->get();
        return $data;
    }

    public function adminAdd($data){
        $getId =    DB::table('users')->insertgetID($data);
    return $getId;
    }

    public  function verfiyCode_for_signup($data){  
        $datas = DB::table('users')->select('code')->where('code',$data['code'])->where('phone',$data['phone'])->first();
        if($datas){
            
            if($data['code'] == $datas->code){
                return 1;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    public  function verfiyCode_for_login($data){
       
        $datas = DB::table('registered_users')->select('*')->where('code',$data['code'])->where('phone',$data['phone'])->first();
        // print_r($datas);exit;
        if($datas){
            if($data['code'] == $datas->code){

                $response_data = array(
                  'user_unique_id' => $datas->user_unique_id,
                  'access_token'  =>  $datas->access_token, 
                );
                return $response_data;
            }
            else {
                return 0;
            }
        }
        else {
            return 0;
        }
    }

    public function check_user($phone, $code){
        $user = DB::table('registered_users')->where('phone',$phone)->first();
        if(!$user){
          return 0;
        }
        if($user){

            DB::table('registered_users')->where('phone', $phone)->update(['code' => $code]);
            return 1;    
     }
     
    }
    public function insert_data_into_registerd($data)
    {
        $getId = DB::table('registered_users')->insertgetID($data);
        return $getId;
    }


    public function storeGroupModel($tableCode, $data){

        if (!Schema::hasTable($tableCode.'_group')) {
            Schema::create($tableCode.'_group', function($table){
                   $table->engine = 'InnoDB';
                   $table->increments('id');
                   $table->string('group_name', 255);
                   $table->text('group_desc',1025);
                   $table->timestamps();
           });
           $insertgetID =    DB::table($tableCode.'_group')->insertgetID($data);     
           return $insertgetID;
       }
       else {
        $insertgetID =    DB::table($tableCode.'_group')->insertgetID($data);
        return $insertgetID;  
       }
    }

    public function insert_member_to_group($tableCode, $data)
    {

        if (!Schema::hasTable($tableCode.'_members')) {
            Schema::create($tableCode.'_members', function($table){
                   $table->engine = 'InnoDB';
                   $table->increments('id');
                   $table->string('group_id',1025);
                   $table->string('member_name', 255);
                   $table->string('member_number', 255);
                   $table->timestamps();
           }); 
           $members = DB::table($tableCode.'_members')->where('member_number',$data['member_number'])->get();
           if(count($members) > 0){
            $gouparray = explode(',',$members[0]->group_id);
            if( array_search($data['group_id'],$gouparray)){
                $group_id = $members[0]->group_id;
               }
               else{

                   $group_id = $members[0]->group_id.','.$data['group_id'];
               }
            $update_member_data = DB::table($tableCode.'_members')->where('member_number',$data['member_number'])->update(['group_id' => $group_id]);
        } else {
            $insert_member =    DB::table($tableCode.'_members')->insert($data);
        }
        } 
        else {
            $members = DB::table($tableCode.'_members')->where('member_number',$data['member_number'])->get();
            if(count($members) > 0){
                $gouparray = explode(',',$members[0]->group_id);
                if( array_search($data['group_id'],$gouparray) ){
                    $group_id = $members[0]->group_id;
                   }
                   else{
    
                       $group_id = $members[0]->group_id.','.$data['group_id'];
                   }
                $update_member_data = DB::table($tableCode.'_members')->where('member_number',$data['member_number'])->update(['group_id' => $group_id]);
            } else {
                $insert_member =    DB::table($tableCode.'_members')->insert($data);
            }
        }

        return 1;
    }
}