<?php

class Ulogin{
    private $nickname;
    private $first_name;
    private $last_name;
    private $photo;
    private $email;
    private $identity;
    private $bdate;
    private $verifi;
    private $CI;
    private $redirectCurPage;
    private $ComUsId;

    function __construct($user){
        $this->nickname = isset($user['nickname']) ? $user['nickname'] : '';
        $this->first_name = isset($user['first_name']) ? $user['first_name'] : '';
        $this->last_name = isset($user['last_name']) ? $user['last_name'] : '';
        $this->photo = isset($user['photo_big']) ? $user['photo_big'] : '';
        $this->email  = isset($user['email']) ? $user['email'] : 0;
        $this->identity = isset($user['identity']) ? $user['identity'] : 0;
        $this->bdate = isset($user['bdate']) ? $user['bdate'] : '';
        $this->verifi = isset($user['verified_email']) ? $user['verified_email'] : '';
        $this->CI =& get_instance();
        $this->ComUsId = 0;
        $this->redirectCurPage = mso_url_get();
        if ($this->redirectCurPage == getinfo('site_url')) $this->redirectCurPage = false;
        if (isset($this->bdate)) {
            $this->bdate=explode('.',$this->bdate);
            $this->bdate = (isset($this->bdate[2]) ? $this->bdate[2] : '00') .'-'.(isset($this->bdate[1]) ? $this->bdate[1] : '00').'-'.(isset($this->bdate[0]) ? $this->bdate[0] : '0000').' 00:00:00';
        }

        if(empty($this->nickname)){
            $this->nickname = $this->first_name.'_'.$this->last_name;
        }
    }
    private function selUserFrB($nameTable, $email = array(),$prefx = 0) {

        $this->CI->db->select($nameTable.'_id');
        $this->CI->db->where($nameTable.'_email', $email[0].($prefx ? '+'.$prefx : '').'@'.$email[1]);
        $query = $this->CI->db->get($nameTable);
        if ($query->num_rows()){
            $this->ComUsId = $query->row_array(1);
            $this->ComUsId = $this->ComUsId[$nameTable.'_id'];
            return true;
        }
        else
            return false;
    }
    private function authUser(){

        $this->CI->db->select('id');
        $this->CI->db->where('user_id', $this->ComUsId);
        $query = $this->CI->db->get('ulogintable');
        if ($query->num_rows() or $this->verifi==1)   {
            $this->mso_comuser_auth_ulogin();
            mso_redirect(getinfo('site_url'),true,301);
        }
        else{
            die(t('Произошла ошибка регистрации. Email уже используется.'));
        }
    }
    private function insertTouloginIfNotExists($comuser_id){
        $this->CI->db->select('id');
        $this->CI->db->where('user_id',$comuser_id);
        $query = $this->CI->db->get('ulogintable');

        if ($query->num_rows() ==  0){
            $this->CI->db->query("INSERT INTO ". $this->CI->db->dbprefix ."ulogintable (user_id)
				VALUES ('$comuser_id')");
        }
    }

    private function mso_comuser_auth_ulogin(){

//        /require_once(getinfo('common_dir') . 'comments.php');

        $this->CI->db->select('comusers_id, comusers_password, comusers_email, comusers_nik, comusers_url, comusers_avatar_url, comusers_last_visit');
        $this->CI->db->where('comusers_id', $this->ComUsId);
        $query = $this->CI->db->get('comusers');

        if ($query->num_rows())
        {
            $comuser_info = $query->row_array(1);

            $this->CI->db->where('comusers_id', $comuser_info['comusers_id']);
            $this->CI->db->update('comusers', array('comusers_last_visit'=>date('Y-m-d H:i:s')));

            $expire  = time() + 60 * 60 * 24 * 365; // 365 дней

            $name_cookies = 'maxsite_comuser';
            $value = serialize($comuser_info);

            mso_add_to_cookie($name_cookies, $value, $expire, $this->redirectCurPage); // в куку для всего сайта

            $this->insertTouloginIfNotExists($comuser_info['comusers_id']);

        }
        else
        {
            if ( !mso_get_option('allow_comment_comusers', 'general', '1') )
            {
                die(t('На сайте запрещена регистрация.'));
            }

            $pass = substr(mso_md5($this->email), 1, 9);

            $ins_data = array (
                'comusers_email' => $this->email,
                'comusers_password' => mso_md5($pass)
            );
            $ins_data['comusers_date_birth'] = isset($this->bdate) ? $this->bdate : '';

            $ins_data['comusers_activate_key'] = mso_md5(rand());
            $ins_data['comusers_date_registr'] = date('Y-m-d H:i:s');
            $ins_data['comusers_last_visit'] = date('Y-m-d H:i:s');
            $ins_data['comusers_ip_register'] = $_SERVER['REMOTE_ADDR'];
            $ins_data['comusers_notify'] = '1';

            if (isset($this->photo) && !empty($this->photo)){
                if ($comusers_avatar_url = mso_clean_str($this->photo))
                    $ins_data['comusers_avatar_url'] = $comusers_avatar_url;
            }
            else $comusers_avatar_url = '';

            if ($comusers_nik = mso_clean_str($this->nickname, 'base|not_url')){
                $ins_data['comusers_nik'] = $comusers_nik;
            }

            $ins_data['comusers_activate_string'] = $ins_data['comusers_activate_key'];

            $res = ($this->CI->db->insert('comusers', $ins_data)) ? '1' : '0';

            if ($res)
            {
                $comusers_id = $this->CI->db->insert_id();

                $this->CI->db->where('meta_table', 'comusers');
                $this->CI->db->where('meta_id_obj', $comusers_id);
                $this->CI->db->where('meta_key', 'subscribe_my_comments');
                $this->CI->db->delete('meta');

                $ins_data2 = array(
                    'meta_table' => 'comusers',
                    'meta_id_obj' => $comusers_id,
                    'meta_key' => 'subscribe_my_comments',
                    'meta_value' => '1'
                );

                $this->CI->db->insert('meta', $ins_data2);

                $comuser_info = array(
                    'comusers_id' => $comusers_id,
                    'comusers_password' => mso_md5($pass),
                    'comusers_email' => $this->email,
                    'comusers_nik' => $this->nickname,
                    'comusers_url' => '',
                    'comusers_avatar_url' => $comusers_avatar_url,
                    'comusers_last_visit' => '',
                );

                $value = serialize($comuser_info);

                $expire  = time() + 60 * 60 * 24 * 365;
                $name_cookies = 'maxsite_comuser';

                mso_add_to_cookie($name_cookies, $value, $expire, $this->redirectCurPage);

                $this->insertTouloginIfNotExists($comusers_id);
            }
            else
            {
                die(t('Произошла ошибка регистрации'));
            }
        }

        return false;
    }

    public function initAutorith(){
        if ($this->email and $this->identity)
            if (mso_valid_email($this->email)) {
                $arMail= explode('@', $this->email);
                if ($this->selUserFrB('comusers', $arMail, 0)){
                    $this->authUser();
                }
                elseif ($this->selUserFrB('comusers', $arMail, 1)){
                    $this->email=$arMail[0].'+1@'.$arMail[1];
                    $this->authUser();
                }
                else {
                    if ($this->selUserFrB('users', $arMail, 0)){
                        if($this->verifi==1){
                            $this->ComUsId = 0;
                            $this->email=$arMail[0].'+1@'.$arMail[1];
                            $this->authUser();
                        }
                        else
                            die(t('Произошла ошибка регистрации. Email уже используется.'));
                    }
                    else
                        $this->authUser();
                }

            }
    }
}