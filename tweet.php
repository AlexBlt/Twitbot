<?php 
// date_default_timezone_set('GMT');
// require('TwitterBot.php'); 
// header('Content-Type: text/html; charset=utf-8'); 


//1 - Settings (please update to math your own)
$consumer_key='CONSUMER_KEY'; //Provide your application consumer key
$consumer_secret='CONSUMER_SECRET'; //Provide your application consumer secret
$oauth_token = 'oAuthToken'; //Provide your oAuth Token
$oauth_token_secret = 'oAuthTokenSecret'; //Provide your oAuth Token Secret

//2 - Include @abraham's PHP twitteroauth Library
require "vendor/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

//3 - Authentication
/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);

//4 - Start Querying

$tweet = $connection->get("search/tweets", ["count" => NUMBER_OF_TWEETS, "q"=>"KEYWORDS", "tweet_mode" => "extended", "is_quote_status" => false]);
echo '<pre>'; print_r($tweet); echo '</pre>';

echo sizeof($tweet->statuses);
$id_me = "YOUR_ID"; // provide your id

for($i=0;$i<sizeof($tweet->statuses);$i++) {


	$id_tweet = $tweet->statuses[$i]->id;

	//Retrieve organizer id 
	$id_concours = array_key_exists(("retweeted_status"), $tweet->statuses[$i]) ? $tweet->statuses[$i]->retweeted_status->user->id_str : $tweet->statuses[$i]->user->id_str;

	if(!(isFollowing($connection, $id_me, $id_concours))){
		followUser($connection, $id_concours);
	}

	//user_mentions full display depends on retweeted status
	$mentions = array_key_exists(("retweeted_status"), $tweet->statuses[$i]) ?  $tweet->statuses[$i]->retweeted_status->entities->user_mentions : $tweet->statuses[$i]->entities->user_mentions;

	// echo (count($mentions));
		for($j=0;$j<count($mentions);$j++) {


				$following = isFollowing($connection, $id_me, $mentions[$j]->id_str);

				if(!$following) {
					followUser($connection, $mentions[$j]->id_str);
				}
				else {
					//do nothing
				}

		}

		retweet($connection, $id_tweet);
}




//check whether or not user1 follows user2
function isFollowing($connection, $user1, $user2) {
	$friendship = $connection->get("friendships/show", ["source_id" => $user1, "target_id" => $user2]);
	//3017389047 and 87701473
	//echo '<pre>'; print_r($friendship); echo '</pre>';
	$following = $friendship->relationship->source->following;
	
	return $following==1;
}

//follow a given user
function followUser($connection, $user_to_follow) {
	$follow = $connection->post("friendships/create", ["user_id" => $user_to_follow]);
}

//retweet a given tweet
function retweet($connection, $id_tweet) {
	$rt = $connection->post("statuses/retweet", ["id" => $id_tweet]);
	return $rt;
}

?>