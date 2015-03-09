<!--Comment Format --> 
<font color = "black">
<h1><?php eh($thread->title) ?></h1>
  <?php foreach($comments as $k=>$v): ?>
    <div class="comment">                        
      <div class="meta">
        <h4> <ul style="list-style-type:square">
        <li> <?php eh($v->username) ?></h4> <?php eh($v->created) ?> </li>
      </div>
    <div><?php echo($v->body) ?></div>
    </div>
<br /> 
<?php endforeach ?>

<!--Pagination --> 
<?php if($pagination->current > 1): ?>
    <a class ="btn btn-small" href='?thread_id=<?php eh($thread->id) ?>&page=<?php echo $pagination->prev ?>'>Previous</a>
    <?php else: ?> 
         Previous
    <?php endif ?>

<?php for($i = 1; $i <= $pages; $i++): ?>
    <?php if($i == $page): ?>
      <?php echo $i ?>
    <?php else: ?>
        <a class ="btn btn-small" href='?thread_id=<?php eh($thread->id) ?>&page=<?php echo $i ?>'><?php echo $i ?></a>
    <?php endif; ?>
<?php endfor; ?>

<?php if(!$pagination->is_last_page): ?>
    <a class ="btn btn-small" href='?thread_id=<?php eh($thread->id) ?>&page=<?php echo $pagination->next ?>'>Next</a>
    <?php else: ?>  Next
<?php endif ?>


<hr>            
<form class="well" method="post" action="<?php eh(url('thread/write')) ?>">
  <label>Your name</label>
  <input type="text" class="span2" name="username" value="<?php eh(Param::get('username')) ?>">
  
  <label>Comment</label>
  <textarea name="body"><?php eh(Param::get('body')) ?></textarea>
  <br />

  <input type="hidden" name="thread_id" value="<?php eh($thread->id) ?>">
  <input type="hidden" name="page_next" value="write_end">
  <button type="submit" class="btn btn-medium btn-info">Submit</button>
</font>      
</form>
