<?php 

require APPPATH."libraries/REST_Controller.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

class User extends REST_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model("api/UserModel");
        $this->load->helper(array(
            "authorization",
            "jwt"
        ));
    }

     //validate token method
    protected function user_details(){
        $headers = $this->input->request_headers();
        
        $token = $headers['Authorization'];
        try{
            $user_data = authorization::validateToken($token);
            if($user_data == FALSE){
                $this->response(array(
                    "status" => 0,
                    "message" => "Unauthorize access"
                ),parent::HTTP_UNAUTHORIZED);
            }else{
                return $user_data->data;
            }
        }catch(Exception $ex){
            $this->response(array(
                "status" => 0,
                "message" => $ex->getMessage()
            ),parent::HTTP_INTERNAL_SERVER_ERROR);
        }
        return FALSE;
    }
    // create a user
    public function register_post(){

        $data = json_decode(file_get_contents("php://input"));
        if(isset($data->name) && isset($data->email) && isset($data->password) && isset($data->age) && isset($data->gender) && isset($data->DOB)){
            
            $user_data = array(
                "name" => $data->name,
                "email" => $data->email,
                "age" => $data->age,
                "gender" => $data->gender,
                "DOB" =>$data->DOB,
                "password" => password_hash($data->password, PASSWORD_DEFAULT)
            );

            if($this->UserModel->set_user($user_data) != null){
                $this->response(array(
                    "status" => 1,
                    "message" => "User Successfully registered"
                ),parent::HTTP_OK);
            }else{
                $this->response(array(
                    "status" => 0,
                    "message" => "Failed to register user."
                ),parent::HTTP_OK);
            }

        }else{
            $this->response(array(
                "status"=>0,
                "message"=>"All filds are needed"
            ), parent::HTTP_NOT_FOUND);
        }
    }

    //login api method
    public function login_post(){
        $data = json_decode(file_get_contents("php://input"));

        if(isset($data->email) && isset($data->password)){
            
            $email = $data->email;
            $password = $data->password;
            $user_data = $this->UserModel->is_email_exists($email);
            if($user_data){
                if(password_verify($password, $user_data['password'])){
                    $token = authorization::generateToken((array)$user_data);
                    $this->response(array(
                        "status"=>1,
                        "message"=>"login successfully",
                        "token"=>$token
                    ),parent::HTTP_OK);


                }else{
                    $this->response(array(
                        "status"=>0,
                        "message"=>"Password didn't match."
                    ), parent::HTTP_NOT_FOUND);
                }

            }else{
                $this->response(array(
                    "status" => 0,
                    "message" => "Email not found"
                ),parent::HTTP_NOT_FOUND);
            }

        }else{
            $this->response(array(
                "status"=>0,
                "message"=>"Login Details are needed."
            ), parent::HTTP_NOT_FOUND);
        }
    }

    //update user details
    public function delete_get(){
        $data = $this->user_details();
        if($data != FALSE){
            if($this->UserModel->is_email_exists($data->email)){
            $result = $this->UserModel->delete_user($data->_id);
            if($result->getDeletedCount() > 0){
                $this->response(array(
                    "status"=>1,
                    "message"=>"User deleted successfully",
                    ),parent::HTTP_OK);
            }else{
            $this->response(array(
                "status"=>0,
                "message"=>"Cannot Delete User."
            ), parent::HTTP_NOT_FOUND);
        }
        }else{
                $this->response(array(
                    "status"=>0,
                    "message"=>"Cannot Find User."
                ), parent::HTTP_NOT_FOUND);
            }
        }
        
    }

    public function details_get(){
        $data = $this->user_details();
        unset($data->password);
        if( $data != FALSE){
            $this->response(array(
                "status"=>1,
                "message"=>"User Data",
                "data" => $data
            ),parent::HTTP_OK);
        }
        
    }

    public function update_post(){
        $data = $this->user_details();
        if($this->UserModel->is_email_exists($data->email)){
            $newdata = json_decode(file_get_contents("php://input"));
            $result = $this->UserModel->update_user($newdata,$data->_id);
            if($result->getModifiedCount() > 0 || $result->getMatchedCount()){
                $this->response(array(
                    "status"=>1,
                    "message"=>"User details updated successfully",
                    ),parent::HTTP_OK);
            }else{
                $this->response(array(
                    "status"=>0,
                    "message"=>"Cannot Update User."
                ), parent::HTTP_NOT_FOUND);
            }
        }else{
            $this->response(array(
                "status"=>0,
                "message"=>"Cannot Find User."
            ), parent::HTTP_NOT_FOUND);
        }
    }



}