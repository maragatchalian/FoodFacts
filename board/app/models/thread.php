<?php

/*
* Thread Model
* This is where the database manipulation and validation happens
*/

class Thread extends AppModel {
    
    /*
    *   The following constants are declared to avoid magic numbers.
    *   Avoid using magic numbers so others couuld
    *   understand what that number is all about.
    */
    const MIN_TITLE_LENGTH = 1;
    const MAX_TITLE_LENGTH = 30;

    //Thread Length Validation
    public $validation = array(
    'title' => array(
        'length' => array(
            'validate_between', self::MIN_TITLE_LENGTH, self::MAX_TITLE_LENGTH
            ),
        ),

    //Category Validation
    'category' => array(
        'length' => array(
            'validate_between', self::MIN_TITLE_LENGTH, self::MAX_TITLE_LENGTH
            ),
        ),
    );

    public function create(Comment $comment) 
    {
        $this->validate();
        $comment->validate();
        
        if ($this->hasError() || $comment->hasError()) {
            throw new ValidationException('Invalid thread or comment');
        }
            
        try {
            $db = DB::conn();
            $date_created = date("Y-m-d H:i:s");
            $db->begin();
            $params = array(  //$params is the variable name of this set. (title, created)
                'title' => $this->title,   //input will be stored in the column 'title'
                'created'=> $date_created, 
                'user_id'=> $this->user_id,
                'category' => $this->category
                );
            $db->insert('thread', $params); 
            $this->id = $db->lastInsertId();
            $comment->write($comment, $this->id); //<--write first comment at the same time
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
        }
    }

    public static function getAll($offset, $limit) 
    {
        $threads = array();
        $db = DB::conn();
        $rows = $db->rows("SELECT * FROM thread LIMIT {$offset}, {$limit}");
            
        foreach($rows as $row) {
            $threads[] = new self($row);
        }
        return $threads;
    }

    public static function countAll() 
    {
        $db = DB::conn();
        return (int) $db->value('SELECT COUNT(*) FROM thread');
    }

    public function countComments() 
    {
        $db = DB::conn();
        return (int) $db->value("SELECT COUNT(*) FROM comment WHERE thread_id = ? ", array($this->id));
    }
    
    public static function get($id) 
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM thread WHERE id = ?', array($id));
            
        if (!$row) {
            throw new RecordNotFoundException('No Record Found');
        }
        return new self($row);
    }
    
    public function getComments($offset, $limit) 
    {
        $comments = array();
        $db = DB::conn();
        $rows = $db->rows("SELECT * FROM comment WHERE thread_id = ? ORDER BY created ASC LIMIT {$offset}, {$limit}", array($this->id));
    
        foreach ($rows as $row) {
            $comments[] = new Comment($row);
        }
        return $comments;
    }

    //Sorting of Threads (All Threads)
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

    //Sorting of Threads (By Category)
    public static function getByCategory($offset, $limit, $category) 
    {
        $threads = array();
        $db = DB::conn();
        $rows = $db->rows("SELECT * FROM thread WHERE category = ? LIMIT {$offset}, {$limit}",array($category));
            
        foreach($rows as $row) {
            $threads[] = new self($row);
        }
         return $threads;
    }

    public static function getAllCategory() 
    {
        $db = DB::conn();
        $rows = $db->rows('SELECT DISTINCT category FROM thread');
        $categories = array();
        
        foreach ($rows as $row) {
            if (!empty($row['category'])) {
                $categories[] = $row['category'];
            }
        }
        return $categories;
    }
} //end
