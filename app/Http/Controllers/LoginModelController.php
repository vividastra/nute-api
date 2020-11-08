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
        $data = DB::table('registered_users')->whwre('user_unique_id',$unique_id)->where('access_token',$token)-get();
        if(count($data) > 0){
            return 1;
        }else{
            return 0;
        }
    }

    

    public function storeAdmin(Request $request){
        $code = rand(1000,9999);
        

        $validator = Validator::make($request->all(),[
            // 'email' => 'required|unique:registered_users',
            'phone' => 'required|unique:registered_users',
        ]);
        if ($validator->fails()) {
            $response = [
                'message' => "User Exist",
                'key' => 0,
            ];
            return response()->json($validator->errors(), 400);
        //    return json_encode($response);
        }
        else {
            $data = array(
                // 'email'=>  $request->email,
                'phone' => $request->phone,
                'code' => $code,
            );
        $to_phone_number = $request->phone;
        $sid    = getenv("TWILIO_SID");
        $token  = getenv("TWILIO_AUTH_TOKEN"); 
        $from_phone_number  =  getenv("TWILIO_NUMBER");
        $msg_service_id  =  getenv("TWILIO_SERVICE_ID");
        $twilio = new Client($sid, $token);
        $message = $twilio->messages
        ->create("+91".$to_phone_number, // to
        [
            "body" => "Your Nute Verification Code is".$code,
            "from" => $from_phone_number,
           
        ]
        );
        $response = [
            'message' => "Verification Code Sent Successfully",
            'key' => 1,
        ];
        // echo $response;
        // print($message->status);
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
                        'user_unique_id' => $verified,
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
                        ];

                        return json_encode($response);
                    }
            }
            
        }
 }


/* public function add_user_as_registerd(Request $request)
 {
     $validator = Validator::make($request->All(),
        [     
            'name'=> 'required',
            'mobile_number'=>'required',
            'email'=>'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } else {
            $data =array(
                'code' => $request->code,
                'number' => $request->number,
                'name' => $request->name,
                'email' => $request->email
            );
            $register = $this->model->insert_data_into_registerd($data);

            if($register){
                $response = [
                    'message' => 'User Registered Successfully',
                    'key' => 1,
                ];
                return json_encode($response);
            } else{
                $response = [
                    'message' => 'Oops Something went wrong try after some time',
                    'key' => 0,
                ];
                return json_encode($response);
            }
        }
 }*/



public function login(Request $request){
            $code = rand(1000,9999);
            $phone = $request->phone;
            
            $check = $this->model->check_user($phone, $code);
            if($check === 1){
                $to_phone_number = $request->phone;
                $sid    = getenv("TWILIO_SID");
                $token  = getenv("TWILIO_AUTH_TOKEN"); 
                $from_phone_number  =  getenv("TWILIO_NUMBER");
                $msg_service_id  =  getenv("TWILIO_SERVICE_ID");
                $twilio = new Client($sid, $token);
                $message = $twilio->messages
                ->create("+91".$to_phone_number, // to
                [
                    "body" => "Your Nute Verification Code is".$code,
                    "from" => $from_phone_number,
                    
                ]
                );
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
        $access_token = $_GET['access_token'];

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
            $access_token = $_GET['access_token'];


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

            $access_token = $_GET['access_token'];

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
     

}