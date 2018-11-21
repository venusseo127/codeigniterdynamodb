<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>vc9</title>
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>    

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	

<style>
body {
  padding-top: 54px;
}

@media (min-width: 992px) {
  body {
    padding-top: 56px;
  }
}

.pagination {
  margin-bottom: 15px;
}
.backg{
    background-image: url("../../images/homepage.png");
}
</style>

  </head>

  <body>



    <!-- Page Content -->
    <div class="container">

<?php 
    $path ="https://mysite.com"; 
			 if(count($rooms) > 0):

				 foreach($rooms as $room => $fields):
                 ?>
				 <p>
                    <a href="<?php echo site_url(array('welcome','view', $fields['roomid'])); ?>">View <?php echo $fields['roomid']; ?></a> |
                    <a href="<?php echo site_url(array('welcome', 'add')); ?>">Add a Room</a> |
					 <a href="<?php echo site_url(array('welcome','edit', $fields['roomid'])); ?>" style="font-size:12px;">Edit</a>  |
                     <a href="<?php echo site_url(array('welcome','delete', $fields['roomid'])); ?>" style="font-size:12px;">Delete</a></p>
				
				 <p>
                 <?php echo isset($fields['title'])?$fields['title']:'';?><br>
                 <?php echo isset($fields['owner'])?$fields['owner']:""; ?><br />
                 <?php //echo isset($fields['exitRightid'])?"exitRightid:".$fields['exitRightid']:""?><br />
                 <?php //echo isset($fields['exitLeftid'])?"exitLeftid:".$fields['exitLeftid']:""; ?><br />
                 <?php //echo isset($fields['exitRearid'])?"exitRearid:".$fields['exitRearid']:""; ?><br />
                 <?php
                 if($fields['background']<>""){?>
                 <a href="<?php echo site_url(array('welcome','view', $fields['roomid'])); ?>"><img src="<?=$path.$fields['background']?>"  width="200" ></a>
                 <?php }?>
                 <br />
                 </em></p>
				
				 <hr />
				
				 <?php
				 endforeach;
			else:?>
				<p>No users to date</p>				 
			<?php endif;
		 ?>


    </div>
    <!-- /.container -->



    <!-- Bootstrap core JavaScript -->
    <!--
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>-->

  </body>

</html>
