<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Request as req;
use App\loginModel;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;
use DB;
use Illuminate\Support\Facades\Schema;
use Twilio\TwiML\MessagingResponse;

class LoginModelController extends Controller
{

    public function __construct(){

       $this->model = new loginModel;
    }
    //
    public function getData(Request $request)
    {

            $returnData = $this->model->getdata();
            return json_encode($returnData);
    }

    public function verify_access_token($unique_id,$token)
    {
        $data = DB::table('registered_users')->where('user_unique_id',$unique_id)->where('access_token',$token)->get();
        if(count($data) > 0){
            return 1;
        }else{
            return 0;
        }
    }

    public function sendSMS($toNumber, $code){
        $sid    = 'AC6804451da956c941dd88226a0f2c3847';
        $token  = '7984c5a2a90cc31d6288c167f1258644'; 
        $from_phone_number  = '+1 918 376 7404';
        
        $client = new Client($sid, $token);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
            "+91".$toNumber,
            array(
                'from' => $from_phone_number,
                "body" => "Your Nute Verification Code is"." ".$code,
            )
        );
    }

    

    public function storeAdmin(Request $request){
        $code = rand(1000,9999);
        $validator = Validator::make($request->all(),[
            'phone' => 'required|unique:registered_users',
        ]);
        if ($validator->fails()) {
            $response = [
                'message' => "User Exist",
                'key' => 0,
            ];
            return response()->json($validator->errors(), 400);
        }
        else {
            $data = array(
                'phone' => $request->phone,
                'code' => $code,
            );
            $toNumber = $request->phone;        
            $this->sendSMS($toNumber,$code);
        $response = [
            'message' => "Verification Code Sent Successfully",
            'key' => 1,
        ];

         $insert = $this->model->adminAdd($data);
         return $response;
    }
    }
 public function verifyCode(Request $request){
       
        $validator = Validator::make($request->All(),
        [     
            'code'=> 'required|min:4|numeric',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        else {
            if($request->flag == 'L'){
                $data = [
                    'code' => $request->code,
                    'phone' => $request->phone,
                ];
                $userCode = 
                $verified = $this->model->verfiyCode_for_login($data);
                if($verified == 0){
                    $response = [
                        'message' => 'Invalid Verification Code',
                        'key' => 0,
                    ];
                    return json_encode($response);
                }
                elseif($verified){
                    $response = [
                        'message' => 'Verification Done Succesfully!',
                        'key' => 1,
                        'user_unique_id' => $verified['user_unique_id'],
                        'access_token' => $verified['access_token'],
                    ];
                    return json_encode($response);
                }
            } 
            elseif($request->flag == 'S'){
                    $data = [
                        'code' => $request->code,
                        'phone' => $request->phone,
                    ];      
                    $verified = $this->model->verfiyCode_for_signup($data);
                    if($verified == 0){
                        $response = [
                            'message' => 'Invalid Verification Code',
                            'key' => 0,
                        ];
                        return json_encode($response);
                    }
                    elseif($verified == 1){
                        $user_unique_id = rand(1000,9999);
                        $register_data = [
                            'code' => $request->code,
                            'phone' => $request->phone,
                            'email' => $request->email,
                            'user_unique_id' => $user_unique_id,
                            'access_token'   => md5(uniqid($user_unique_id, true)),
                        ];

                        $register = $this->model->insert_data_into_registerd($register_data);
                        $response = [
                            'message' => 'Verification Done Succesfully!',
                            'key' => 1,
                            'access_token' => $register_data['access_token'],
                             'adminId' => $register_data['user_unique_id'],
                        ];

                        return json_encode($response);
                    }
            }
            
        }
 }

public function login(Request $request){
            $code = rand(1000,9999);
            $phone = $request->phone;
            
            $check = $this->model->check_user($phone, $code);
            if($check === 1){
                $toNumber = $request->phone;
                $this->sendSMS($toNumber,$code);
                
                $response = [
                    'message' => "Verification Code Sent Successfully",
                    'key' => 1,
                ];
                return json_encode($response);
            }
            elseif($check === 0 ) {
                $response = [
                    'message' => "user Does not exsist",
                    'key' => 0,
                ];
                return json_encode($response);
            }
}

 public function get_countries(){
    $countries = DB::table('countries')->select('countryCode','country','dialCode')->get();
    
    echo json_encode($countries);
}


public function storeGroup(Request $request){
    // $jsondata = $request->all();
    $jsondata =    $request->json()->all();
        $Gname = $jsondata['group_name'];
        $Gdesc = $jsondata['group_desc'];
        $tableCode = $jsondata['createdby'];
        $members = $jsondata['members'];
        $access_token = $request->header('access_token');

    $access_token_verification = $this->verify_access_token($tableCode,$access_token);
   

   if($access_token_verification == 1)
   {
        $validator = Validator::make($request->All(),
        [
        'group_name'=> 'required',
        'group_desc'=> 'required',
        'createdby'=> 'required',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
            
        }
        else {
            $today = date("Y-m-d H:i:s");      
            $data = [
                'id' => null,
                'group_name' => $Gname,
                'group_desc' =>  $Gdesc,
               'updated_at' =>$today,
            ];
            $group_id = $this->model->storeGroupModel($tableCode,$data);
            foreach ($members as $key => $member) {
                $member_data = array(
                    'id' => null,
                    'group_id' =>   $group_id,
                    'member_name' => $member['Name'],
                    'member_number' => $member['phone'],
                    'created_at'    => $today,
                    'updated_at'    => $today,
                );
                    $insert_member = $this->model->insert_member_to_group($tableCode,$member_data);
            }
            return 1;
        }
   } 
   else 
   {
        $response = [
                    'message' => 'Incorrect Access Token',
                    'key'=> 0,
                ];

            return json_encode($response);                
   }
    
    
}

        public function allMembers(Request $request){
            $tableCode = $request->adminId;
            $access_token = $request->header('access_token');
            $access_token_verification = $this->verify_access_token($tableCode,$access_token);

            if($access_token_verification == 1)
            {
                $response = DB::table($tableCode.'_members')->select('id','group_id','member_name','member_number')->get();
            
                if(count($response) > 0){
                  
                    return json_encode($response);
                }
                else {
                    $response = [
                        'message' => 'No Members Added Please Add Groups',
                        'key'=> 0,
                    
                    ];
                    return json_encode($response);
                }
            } 
            else 
           {
                $response = [
                            'message' => 'Incorrect Access Token',
                            'key'=> 0,
                        ];

                    return json_encode($response);                
           }

            
        }
        public function allGroups(Request $request){
            $tableCode = $request->adminId;

            $access_token = $request->header('access_token');
            $access_token_verification = $this->verify_access_token($tableCode,$access_token);
            if($access_token_verification == 1)
            {
                $response = DB::table($tableCode.'_group')->select('id','group_name','group_desc')->get();
                if(count($response) > 0){
                  
                    return json_encode($response);
                }
                else {
                    $response = [
                        'message' => 'No Groups Added Please Add Groups',
                        'key'=> 0,
                    
                    ];
                    return json_encode($response);
                }
            }
            else
            {
                $response = [
                            'message' => 'Incorrect Access Token',
                            'key'=> 0,
                        ];

                    return json_encode($response);
            }
            
        }



        public function add_single_member_to_group(Request $request)
        {
            $today = date("Y-m-d H:i:s");  
            $jsondata =    $request->json()->all();

            $access_token = $_GET['access_token'];

            $group_id = $jsondata['group_id'];
            $tableCode = $jsondata['createdby'];
            $members = $jsondata['members'];

            $access_token_verification = $this->verify_access_token($tableCode,$access_token);
            if($access_token_verification == 1)
            {
                foreach ($members as $key => $member) {
                    $member_data = array(
                        'id' => null,
                        'group_id' =>   $group_id,
                        'member_name' => $member['Name'],
                        'member_number' => $member['phone'],
                        'created_at'    => $today,
                        'updated_at'    => $today,
                    );
                        $insert_member = $this->model->insert_member_to_group($tableCode,$member_data);
                }

                $response = [
                            'message' => 'Member Added Successfully',
                            'key'=> 1,
                        ];

                    return $response;
            } else{
                $response = [
                            'message' => 'Incorrect Access Token',
                            'key'=> 0,
                        ];

                    return json_encode($response);
            }
        }
     

}
