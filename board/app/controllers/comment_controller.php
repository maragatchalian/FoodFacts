<?php

class CommentController extends AppController 
{

const WRITE_COMMENT = 'write';
const WRITE_COMMENT_END = 'write_end';
const MAX_USER_IN_NEWSFEED = 1;

    public function write() 
    {
        $thread_id = Param::get('thread_id');
        $thread = Thread::get($thread_id);
        $current_page = Param::get(PAGE_NEXT, self::WRITE_COMMENT);
    
        switch($current_page) { 
            case self::WRITE_COMMENT:
                break;  
                
            case self::WRITE_COMMENT_END:
                $params = array(
                'body' => Param::get('body'),
                'username' => $_SESSION['username'],
                'user_id' => $_SESSION['user_id']
                );
                $comment = new Comment($params);
                
                try {
                    $comment->write($comment, $thread_id);
                } catch (ValidationException $e) {
                    $current_page = self::WRITE_COMMENT;
                }    
                break;

            default:
                throw new NotFoundException("{$current_page} is not found");
                break;
        }
        $this->set(get_defined_vars());
        $this->render($current_page);
    }

    public function delete()
    { 
        $comment = Comment::get(Param::get('comment_id'));
        $comment->delete($_SESSION['user_id']);
        $this->set(get_defined_vars());
        $this->render('comment/delete');
    }  

    /*
    * Displays the homepage
    */
    public function home() 
    {
        $per_page = self::MAX_USER_IN_NEWSFEED; 
        $current_page = Param::get(PAGE, 1); 
        $pagination = new SimplePagination($current_page, $per_page); 

        $user_id = $_SESSION['user_id'];
        $following = Comment::getFollowing($pagination->start_index -1, $pagination->count + 1, $user_id);
        
        $thread_id = Param::get('thread_id');
        $comments = Comment::newsfeed($thread_id);

        $pagination->checkLastPage($following);
        $total = Follow::countNewsfeed($user_id);
        $pages = ceil($total / $per_page);
        $this->set(get_defined_vars());
    }
} //end