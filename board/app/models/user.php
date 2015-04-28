<?php

class User extends AppModel {

/*
* constants are used to avoid magic numbers
*/

const MIN_USERNAME_LENGTH = 2;
const MIN_FIRST_NAME_LENGTH = 2;
const MIN_LAST_NAME_LENGTH = 2;
const MIN_EMAIL_LENGTH = 4;
const MIN_PASSWORD_LENGTH = 8;

const MAX_USERNAME_LENGTH = 20;
const MAX_FIRST_NAME_LENGTH = 30;
const MAX_LAST_NAME_LENGTH = 30;
const MAX_EMAIL_LENGTH = 30;
const MAX_PASSWORD_LENGTH = 20;
  
    public function validate_username($username)
    {
        $valid = array('-', '_', '');    
        return ctype_alnum(str_replace($valid, '', $username));
    }

    public function validate_name($string)
    {
        $valid = array('-', ' ');    
        return ctype_alpha(str_replace($valid, '', $string));
    }

    /*
    * Registration Length Validation.
    * Limits a certain field into a specific number of characters
    * Checks if the characters are valid. (e.g., dot, hyphen)
    */
    public $validation = array(
        'username' => array(
            'length' => array(
                'validate_between', self::MIN_USERNAME_LENGTH, self::MAX_USERNAME_LENGTH
            ),
            'valid' => array(
                'validate_username'
            ),
            'exist' => array(
                'is_username_exist'
            ),
        ),

        'first_name' => array(
            'length' => array(
                'validate_between', self::MIN_FIRST_NAME_LENGTH, self::MAX_FIRST_NAME_LENGTH
            ),
            'valid' => array(
                'validate_name'
            ),
        ),
        
        'last_name' => array(
            'length' => array(
                'validate_between', self::MIN_LAST_NAME_LENGTH, self::MAX_LAST_NAME_LENGTH
            ),
            'valid' => array(
                'validate_name'
            ),
        ),

        'email' => array(
            'length' => array(
                'validate_between', self::MIN_EMAIL_LENGTH, self::MAX_EMAIL_LENGTH
            ),

        'exist' => array(
            'is_email_exist',
            )
        ),

        'password' => array(
            'length' => array(
                'validate_between', self::MIN_PASSWORD_LENGTH, self::MAX_PASSWORD_LENGTH
            )
        ),
        'confirm_password' => array(
            'match' => array(
                'is_password_match',
            )
        )
    );

    public function register()
    {
        if (!$this->validate()) {
            throw new ValidationException('Invalid Input!');
        }

        try {
            $db = DB::conn(); 
            $db->begin();
            $params = array( //what is to be inserted when $params is called
                'username' => $this->username,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => strtolower($this->email),
                'password' => md5($this->password)
            );
            $db->insert('user', $params); //to insert values of $params in table 'user'
            $db->commit();
        } catch(Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function login() 
    {
        $db = DB::conn();
        $params = array(
            'username' => $this->username,
            'password' => md5($this->password)
        );
        $user = $db->row("SELECT id, username FROM user WHERE BINARY username = :username AND BINARY password = :password", $params);

        if(!$user) {
            throw new RecordNotFoundException('No Record Found');      
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    }

    public function is_password_match()
    {
        return $this->password == $this->confirm_password;
    }

    public function is_username_exist()
    {
        $db = DB::conn();
        $username_exist = $db->row("SELECT username FROM user WHERE username = ?", array($this->username));
        return (!$username_exist);
    }

    public function is_email_exist()
    {
        $db = DB::conn();
        $username_exist = $db->row("SELECT email FROM user where email = ?", array($this->email));
        return (!$username_exist);
    }

    public static function get($user_id) 
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM user WHERE id = ?', array($user_id));
        return $row;
    } 

    /*public static function pagination($id) 
    {
        $db = DB::conn();
        $users = $db->value('SELECT COUNT(*) FROM user WHERE id != ?', array($id));     
        return $users;
    }*/

    public static function getAllMyThread($offset, $limit, $id) 
    {
        $threads = array();
        $db = DB::conn();
        $rows = $db->rows("SELECT * FROM thread WHERE user_id = ? LIMIT {$offset}, {$limit}",array($id));
        
        foreach($rows as $row) {
            $threads[] = new self($row);
        }
        return $threads;
    }

    public static function getData($user_id)
    {
        $db = DB::conn();
        $row = $db->row("SELECT * FROM user WHERE id = ?", array($user_id));

        if (!$row) {
            throw new RecordNotFoundException('no record found');
        }
        return new self($row);
    }

    public function update()
    {
        if (!$this->validate()) {
            throw new ValidationException('Invalid user credentials');
        }

        try {
            $db = DB::conn();
            $db->begin();
            $db->update(
                'user', array(
                    'username' => $this->username,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'password' => $this->password
                    ),
                array('id' =>$this->user_id) 
                );
            $db->commit();
        } catch(Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

   /* public static function getOtherUsers($offset, $limit, $id) 
    {
        $users = array();
        $db = DB::conn();
        $rows = $db->rows("SELECT * FROM user where id != ? LIMIT {$offset}, {$limit}", array($id));
            
        foreach($rows as $row) {
            $users[] = new self($row);
        }

        return $users;
    }*/

//tryyyyyyyyy**************
    public static function getByUserId($user_id)
    {
        $rows = $db->rows('SELECT * FROM user WHERE id = ?', array($user_id));
        if ($rows == null)
            throw new RecordNotFoundException;
    
    $user = array();
    foreach ($rows as $row)
        $user[] = new self($row); // Each row is now an instance of Thread

    return $user;
}
//===================**********

/*
   
    /*
    *Follow and Unfollow
    //
    public function addFollowing()
    { 
        try {
            $db = DB::conn();
            $db->begin();
            $params = array(
            'username' => $this->username,
            'user_id' => $this->user_id
            );
        
            $db->insert('follow', $params);
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
        }
    }

    public function removeFollowing() 
    {
        try {
            $db = DB::conn();
            $db->begin();
            $params = array(
                $this->username,
                $this->user_id
            );
            $db->query('DELETE FROM follow WHERE username = ? AND user_id = ?', $params);
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
        }
    }
    
    public function isUserFollowing() 
    {
        $db = DB::conn();
        $params = array(
            $this->username,
            $this->user_id     
        );
        $user_following = $db->row('SELECT * FROM follow WHERE username = ? AND user_id = ?', $params);
        return !$user_following;
    }

    public static function countFollowing($user_id) 
    {
        $db = DB::conn();
        $total_following = $db->value('SELECT COUNT(*) FROM follow WHERE user_id = ?', array($user_id)); 
        return $total_following;
    }
       
    public static function getAllFollowing($offset, $limit, $user_id) 
    {
        $following = array();
        $db = DB::conn();
                        
        $rows = $db->rows("SELECT * FROM follow WHERE user_id = ? LIMIT {$offset}, {$limit}", array($user_id));

        foreach($rows as $row) {
            $following[] = new self($row);
        }
        return $following;
    }
*/


} //end