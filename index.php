<?php
//start session
session_start();

// Include config file and twitter PHP Library by Abraham Williams (abraham@abrah.am)
include_once("config.php");
include "includes/functions.php";
include_once("inc/twitteroauth.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Twitter API Example</title>
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<script src="js/jquery-1.10.2.js" ></script>
	<script src="js/jquery.validate.js" ></script>
	<script src="js/bootstrap.js" ></script>
    <style type="text/css">
	.wrapper{width:600px; margin-left:auto;margin-right:auto;}
	.welcome_txt{
		margin: 20px;
		background-color: #EBEBEB;
		padding: 10px;
		border: #D6D6D6 solid 1px;
		-moz-border-radius:5px;
		-webkit-border-radius:5px;
		border-radius:5px;
		height:65px;
	}
	.tweet_box{
		margin: 20px;
		background-color: #FFF0DD;
		padding: 10px;
		border: #F7CFCF solid 1px;
		-moz-border-radius:5px;
		-webkit-border-radius:5px;
		border-radius:5px;
	}
	.tweet_box textarea{
		width: 500px;
		border: #F7CFCF solid 1px;
		-moz-border-radius:5px;
		-webkit-border-radius:5px;
		border-radius:5px;
	}
	.tweet_list{
		margin: 20px;
		padding:20px;
		background-color: #E2FFF9;
		border: #CBECCE solid 1px;
		-moz-border-radius:5px;
		-webkit-border-radius:5px;
		border-radius:5px;
	}
	.tweet_list ul{
		padding: 0px;
		font-family: verdana;
		font-size: 12px;
		color: #5C5C5C;
	}
	.tweet_list li{
		border-bottom: silver solid 1px;
		list-style: none;
		padding: 5px;
	}
	.proimg
	{
		float: right;
		height: 45px;
		border-radius: 50%;
		width: 45px;
	}
	.divclose
	{
		float: right;
	}
	
	</style>
</head>
<body>
<?php
	if(isset($_SESSION['status']) && $_SESSION['status'] == 'verified') 
	{
		
		//Retrive variables
		$screen_name 		= $_SESSION['request_vars']['screen_name'];
		$twitter_id			= $_SESSION['request_vars']['user_id'];
		$oauth_token 		= $_SESSION['request_vars']['oauth_token'];
		$oauth_token_secret = $_SESSION['request_vars']['oauth_token_secret'];

		//user info
		$connec=new Users();
		$result=$connec->getUserInfo($twitter_id);
		//print_r($result["username"]);
	
		//Show welcome message
		echo '<div class="welcome_txt">Welcome <strong>'.$screen_name.'</strong> (Twitter ID : '.$twitter_id.'). <a href="logout.php?logout">Logout</a>!<img src="'.$result["picture"].'" class="proimg"></div>';
		$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
		//print_r($connection);die();
		
		//If user wants to tweet using form.
		if(isset($_POST["updateme"])) 
		{
			//Post text to twitter
			$my_update = $connection->post('statuses/update', array('status' => $_POST["updateme"]));
			die('<script type="text/javascript">window.top.location="index.php"</script>'); //redirect back to index.php
		}
		
		//show tweet form
		echo '<div class="tweet_box">';
		echo '<form method="post" action="index.php"><table width="200" border="0" cellpadding="3">';
		echo '<tr>';
		echo '<td><textarea name="updateme" cols="60" rows="4"></textarea></td>';
		echo '</tr>';
		echo '<tr>';
		echo '<td><input type="submit" value="Tweet" /></td>';
		echo '</tr></table></form>';
		echo '</div>';
		
		
		//if you user want like
		//If user wants to tweet using form.
		if(isset($_POST["likemeid"])) 
		{
			//Post text to twitter
			//print_r($_POST["likemeid"]);
			$my_update = $connection->post('favorites/create', array('id' => $_POST["likemeid"]));
			die('<script type="text/javascript">window.top.location="index.php"</script>'); //redirect back to index.php
		}
		
		
		//Get latest tweets
		$my_tweets = $connection->get('statuses/user_timeline', array('screen_name' => $screen_name, 'count' => 5));
		
		echo '<div class="tweet_list"><strong>Latest Tweets : </strong><button class="divclose" id="cmytweets" data_close=1>close</button>';
		echo '<ul id="mytweets">';
		foreach ($my_tweets  as $my_tweet) {
			echo '<li>'.$my_tweet->text.' <strong>like count:<span class="likecount">'.$my_tweet->favorite_count.'</span></strong>';
			echo '<form method="post" action="index.php">';
			echo '<input type="hidden" name="likemeid" value="'.$my_tweet->id_str.'"></input>';
				if($my_tweet->favorited==false)
					echo '<input type="submit" value="LikeMe" /></form>';
				else
					echo '<input type="submit" value="Liked" disabled/></form>';
			echo '<br />-<i>'.$my_tweet->created_at.'</i></li>';
		}
		echo '</ul></div>';
		
		
		//get home_timeline tweets 
		$home = $connection->get('statuses/home_timeline', array('stringify_ids' => 621456134, 'count' => 5));
		//print"<pre>";print_r($home);die();
		echo '<div class="tweet_list" ><strong>Home Tweets : </strong><button class="divclose" id="chometweets" data_close=1>close</button>';
		echo '<ul id="hometweets">';
		foreach ($home  as $h_tweet) {
			//print_r($h_tweet->retweeted_status);
			if(isset($h_tweet->retweeted_status))
			{
				echo '<li>'.$h_tweet->text.' <strong>like count:<span class="likecount">'.$h_tweet->retweeted_status->favorite_count.'</span></strong>';
				echo '<form method="post" action="index.php">';
				echo '<input type="hidden" name="likemeid" value="'.$h_tweet->id_str.'"></input>';
				if($h_tweet->favorited==false)
					echo '<input type="submit" value="LikeMe" /></form>';
				else
					echo '<input type="submit" value="Liked" disabled/></form>';
				echo '<br />-<i>'.$h_tweet->created_at.'</i></li>';
			}
			else
			{
				echo '<li>'.$h_tweet->text.' <strong>like count:<span class="likecount">'.$h_tweet->favorite_count.'</span></strong>';
				echo '<form method="post" action="index.php">';
				echo '<input type="hidden" name="likemeid" value="'.$h_tweet->id_str.'"></input>';
				if($h_tweet->favorited==false)
					echo '<input type="submit" value="LikeMe" /></form>';
				else
					echo '<input type="submit" value="Liked" disabled/></form>';
				echo '<br />-<i>'.$h_tweet->created_at.'</i></li>';
			}
			
		}
		echo '</ul></div>';
		
		
		
		//get friends ids 
		$friends_id = $connection->get('friends/ids', array('stringify_ids' => 621456134));
		//print"<pre>";print_r($friends_id);die();
		echo '<div class="tweet_list"><strong>My Friends Ids : </strong><button class="divclose" id="cfriends_ids" data_close=1>close</button>';
		echo '<ul id="friends_ids">';
		foreach ($friends_id->ids  as $fids) {
			echo '<li>'.$fids.' </li>';
		}
		echo '</ul></div>';
			
	}else{
		//Display login button
		echo '<a href="process.php"><img src="images/sign-in-with-twitter.png" width="151" height="24" border="0" /></a>';
	}
	
?>  
<script>
$(document).ready(function(){

	//like api call
	/*$.ajax({
		url:,
		type:,
		data:{}
	}).done(function(data){
	
	
	});*/
	//lattest tweet
	$(document).on('click','#chometweets',function(){
		status=$(this).attr('data_close');
		if(status==1)
		{
			$('#hometweets').hide();
			$(this).attr('data_close',0);
			$(this).html('show');
		}
		else
		{
			$('#hometweets').show();
			$(this).attr('data_close',1);
			$(this).html('close');
		}
	
	});
	//home tweet
	$(document).on('click','#cmytweets',function(){
		status=$(this).attr('data_close');
		if(status==1)
		{
			$('#mytweets').hide();
			$(this).attr('data_close',0);
			$(this).html('show');
		}
		else
		{
			$('#mytweets').show();
			$(this).attr('data_close',1);
			$(this).html('close');
		}
	
	});
	//friends id list
	$(document).on('click','#cfriends_ids',function(){
		status=$(this).attr('data_close');
		if(status==1)
		{
			$('#friends_ids').hide();
			$(this).attr('data_close',0);
			$(this).html('show');
		}
		else
		{
			$('#friends_ids').show();
			$(this).attr('data_close',1);
			$(this).html('close');
		}
	
	});

});
</script>
</body>
</html>