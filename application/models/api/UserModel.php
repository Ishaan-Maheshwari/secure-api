<?php

class UserModel extends CI_Model {

        public function __construct()
        {
                $this->load->library('mongo_db');
        }

        public function set_user($user_data){
            return $this->mongo_db->insert("user_records", $user_data);
    }

        public function delete_user($id){
            $id = ((array)$id)['$id'];
            return $this->mongo_db->where($id)->delete("user_records");
        }
        
        
        public function is_email_exists($email){
            if($email == ""){
                return null;
            }
            $user = $this->mongo_db->where(array("email"=>$email))->get("user_records");
            if (count($user) == 0){
                return null;
            }

            return $user[0];
        }

        public function update_user($newdata,$id){
            $id = ((array)$id)['$id'];
            return $this->mongo_db->where($id)->set((array)$newdata)->update("user_records");
        }
    }