<?php
ini_set('display_errors', 1);

$query = $_POST["search"];

$api_key = '2bcd0f3a7c604857afb56d70e1d07c5a';

$url = 'https://api.instagram.com/v1/';
$url.= 'tags/'.urlencode($query);
$url.= '/media/recent?';
$url.= 'client_id='.$api_key;
$requestMethod = 'POST';


$requestMethod = 'GET';
$response = file_get_contents($url);
//echo $response;

//connect to server, connect to database
    $connect = new mysqli('localhost', 'root', 'root','instagram');

        $json = json_decode($response);
        //Writes the beginning of the one replace statement
        //we use replace instead of insert because if we insert a post that already exists we will get
        //a duplicate key error. Replace will delete the old row and replace it with a new row if we attempt to
        //insert row with a key that was already used
        $insert = "REPLACE INTO instagram.results (tag, created_time,img_url,location,caption,link, username) VALUES ";

        //loops through each post in the JSON file
        foreach($json->data as $postInformation){

            //concatenates all of the information needed for a post in between parenthesis and separated by comments
            //It writes the part of the insert statement that we need for each post
            //because title can apostrophes in it, we put it in a variable first so that we can put it in a function
            //that escapes the apostrophes
            $values  = "('".$query."' , '";
            //$values .= $postInformation->tag."' , '";
            $values .= $postInformation->created_time."' , '";
            $values .= $postInformation->images->low_resolution->url."' , '";
            $values .= 'filler'."' , '";;//$postInformation->location."' , '";
            $values .= mysqli_real_escape_string($connect,$postInformation->caption->text)."' , '";
            $values .= $postInformation->link."' , '";
            $values .= $postInformation->user->username."'),";
            //appends each post to the replace statement so that we can send all of the posts to the database
            //at once instead of one at a time
            $insert = $insert.$values;
        }//end foreach loop

        //We concatenated a comma at the end each of the posts $value statement to compose our one query.
        //However for the one, we do not want that comma, so we get rid of it and add a semicolon to the end
        //to complete our insert query.

        $insert2 = substr($insert,0, -1).";";
        //echo $insert2;

        //Run the query that was written or show an error if it can't run
        //$insertComplete = mysql_query($insert2,$connect)or die('Tried to run the insert, here was the error I received: '.mysql_error());
        if (mysqli_query($connect, $insert2)) {
            //echo "New record created successfully";
        } else {
            echo "Error: " . $insert2 . "<br>" . mysqli_error($connect);
        }
        
        echo "<h2>".$query ."</h2>";
        $searchresults = "select a.img_url
                    from instagram.results a
                    where a.tag = '".$query."' and img_url is not null and trim(img_url) <> '' order by last_update desc;";
        if(!$result = $connect->query($searchresults)){
            die('There was an error running the query [' . $connect->error . ']');
        } else {
            while($row = $result->fetch_assoc()){
                //echo $row['id_str'] . '<br />' . $row['created_at'] . '<br />' . $row['user_id'] . '<br />' . $row['textf']. '<br />';
                echo '<img src="'.$row['img_url'].'" alt="'.$query.'" style="width:304px;height:304px">';
            }
            $result->free();
        }
        
        
//close database connection
   mysqli_close($connect);
  