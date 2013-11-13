<?php

function pageLoader(){

	echo '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../bootstrap/assets/ico/favicon.png">

    <title>OpenBook Results</title>


    <!-- Bootstrap core CSS -->
    <link href="../bootstrap/dist/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="starter-template.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="signin.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="../bootstrap/assets/js/html5shiv.js"></script>
      <script src="../bootstrap/assets/js/respond.min.js"></script>
    <![endif]-->
  </head>
<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="index.php">Open Library Search Project</a>
        </div>
        <form action="openLib.php" method="post" class="navbar-form navbar-right">
            <div class="form-group">
              <input type="text" placeholder="Search ISBN" name="isbn" class="form-control">
            </div>
            <button type="submit" class="btn btn-success">Search</button>
          </form>
        </div><!--/.nav-collapse -->
      </div>
    </div>
    </div>
        <div class="jumbotron">

	    <div class="container">';

	    echo '<div class="container-fluid">
  	<div class="row-fluid">
    <div class="span2">';
}

function GetURL($book_isbn, $select){

	if($select == 'openlibrary'){
		$URLConstruct = 'http://openlibrary.org/api/books?bibkeys=ISBN:';
		$URLConstruct .= $book_isbn;
		$URLConstruct .='&jscmd=data&format=json';
	}else if($select == 'cuny'){
		$URLConstruct = 'http://lookup.cunylibraries.org/nycity/isbn/';
		$URLConstruct .= $book_isbn;
		$URLConstruct .= '?format=json';
	}

return $URLConstruct;

}

function openURL($URLtoOpen){
	// 1. initialize
$ch = curl_init();

// 2. set the options, including the url
curl_setopt($ch, CURLOPT_URL, $URLtoOpen);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);

// 3. execute and fetch the resulting HTML output
$fileDump = curl_exec($ch);

curl_close($ch);

return $fileDump;
}


function GetTitle($bookInJson, $book_isbn){

	return $bookInJson['ISBN:'.$book_isbn]['title'];
}

function GetAuthor($bookInJson, $book_isbn){
	if(($bookInJson['ISBN:'.$book_isbn]['by_statement'])){
		return $bookInJson['ISBN:'.$book_isbn]['by_statement'];

	}else if($bookInJson['ISBN:'.$book_isbn]['authors']){
		$max = sizeof($bookInJson['ISBN:'.$book_isbn]['authors']);
			//echo 'Max: '.$max.'<br>';
			if($max == 1){ 
				return $bookInJson['ISBN:'.$book_isbn]['authors'][0]['name'];
				} //only one author
			}else{
				for($i = 0; $i < $max; $i++){
				$manyAuthors .= $bookInJson['ISBN:'.$book_isbn]['authors'][$i]['name'];
					
					if(!(($max - 1) == $i))
						$manyAuthors .= ' and ';
					//echo $bookInJson['ISBN:'.$book_isbn]['authors'][$i]['name'].'<br>';
				}
			//echo 'Many: '.$manyAuthors.'<br>';
		return $manyAuthors;
		}
	
}

function GetPub($bookInJson, $book_isbn){
	return $bookInJson['ISBN:'.$book_isbn]['publishers'][0]['name'];
}

function GetYear($bookInJson, $book_isbn){
	$year = $bookInJson['ISBN:'.$book_isbn]['publish_date'];

	return $year;

}

function GetPublishPlace($bookInJson, $book_isbn){
	return $bookInJson['ISBN:'.$book_isbn]['publish_places'][0]['name'];
}

function twoAuthors($name){

	$pos = strrpos($name, 'and');

	if($pos === false){
		return $name;
	}

	$twoAuthors = explode('and', $name);

		//var_dump($twoAuthors);
		return $twoAuthors;
}

function checkTwoAuthors($authorString){

	if (strpos($authorString,' and ') !== false) {
		//echo 'Author record contains an and<br>';
    	return 'true';
	}
}

function split_name($name, $prefix='')
{
	//Author: Matt Humphrey
	//URL:http://forrst.com/posts/PHP_Split_a_full_name_string_into_first_middle-I6C
  $pos = strrpos($name, ' ');

  if ($pos === false) {
    return array(
     $prefix . 'firstname' => $name,
     $prefix . 'surname' => null
    );
  }

  $firstname = substr($name, 0, $pos + 1);
  $surname = substr($name, $pos);

  return array(
    $prefix . 'firstname' => $firstname,
    $prefix . 'surname' => $surname
  );
}

function problemReport($theTitle,$theAuthor,$thePub,$theYear,$thePlace)
{
	if($theTitle == NULL){
		$flag = 1;
		$moreProblems .= 'Missing Title<br>';

	}

	if($theAuthor == NULL){
		$flag = 2;
		$moreProblems .= 'Missing Author><br>';

	}

	if($thePub == NULL){
		$flag = 3;
		$moreProblems .= 'Missing Publisher<br>';

	}

	if($theYear == NULL){
		$flag = 4;
		$moreProblems .= 'Missing Year<br>';

	}

	if($thePlace == NULL){
		$flag = 5;
		$moreProblems .= 'Missing Publisher Place';

	}

	if($flag > 0){
		$problems = '<p style="color:red;">Warning: Problems With Open Library Record<br>';
		}

	$problems .= $moreProblems;
	$problems .= '</p>';
	echo $problems;
}


function createAPA($theTitle,$theAuthor,$thePub,$theYear,$thePlace){

	
		$theAuthor = split_name($theAuthor);

		$citation = $theAuthor[surname].', '.$theAuthor[firstname][0].'. ';	

		if($theYear != NULL){
			$citation .= '('.$theYear.'). ';
		}

		$citation .= '<em>'.$theTitle.'</em>. ';
		
		if($thePlace != NULL)
			$citation .= $thePlace.': ';
		
		if($thePub != NULL)
			$citation .= $thePub.'.';

		return $citation;
	
	//}
}

function createMLA($theTitle,$theAuthor,$thePub,$theYear,$thePlace){
	//Last, First M. Book. City: Publisher, Year Published. Print.

	$theAuthor = split_name($theAuthor);

	$citation = $theAuthor[surname].', '.$theAuthor[firstname];
	$citation .= '<em>'.$theTitle.'</em>. ';
	
	if($thePlace != NULL){
		$citation .= $thePlace.': ';
	}

	if($thePub != NULL){
		$citation .= $thePub.', ';
	}
	
	if($theYear != NULL){
		$citation .= $theYear.'. ';
	}

	$citation .= 'Print.';

	return $citation;	

}

function chicagoStyle($theTitle,$theAuthor,$thePub,$theYear,$thePlace){

	//Pollan, Michael. The Omnivore’s Dilemma: A Natural History of Four Meals. New York: Penguin, 2006.

$theAuthor = split_name($theAuthor);

	$citation = $theAuthor[surname].', '.$theAuthor[firstname];
	$citation .= '<em>'.$theTitle.'</em>. ';
	
	if($thePlace != NULL){
		$citation .= $thePlace.': ';
	}

	if($thePub != NULL){
		$citation .= $thePub.', ';
	}

	if($theYear != NULL){
		$citation .= $theYear.'. ';
	}

	return $citation;	
}

$isbnQ = $_POST['isbn']; //get ISBN from web form

$URLISBN = GetURL($isbnQ, 'openlibrary'); //grab URL 

$output = openURL($URLISBN);

//$URLALEPH = GetURL($isbnQ, 'cuny');

//$cunyOutput = openURL($URLALEPH);

$decodedJ->isbn = $isbnQ;

$decodedJ = json_decode($output,true);

//$cunyDecoded = json_decode($cunyOutput, true);

//var_dump($decodedJ);

$bookTitle = GetTitle($decodedJ,$isbnQ);
$bookAuthor = GetAuthor($decodedJ,$isbnQ);
$bookAuthor = trim($bookAuthor, '.');
$bookPub = GetPub($decodedJ,$isbnQ);
$bookYear = GetYear($decodedJ,$isbnQ);
$bookPlace = GetPublishPlace($decodedJ,$isbnQ);

pageLoader();



/*echo '<p>Publisher: '.$bookPub;
echo '<br>Title: '.$bookTitle;
echo '<br> Author: '.$bookAuthor;
echo '<br> Year: '.$bookYear;
echo '<br> Publishing Place: '.$bookPlace.'</p>';*/

echo '<p><img src="'.$decodedJ['ISBN:'.$isbnQ]['cover']['medium'].'"></p>';
	
echo	'</div> <!--sidebar-->';
//Author, A. A. (Year of publication). Title of work: Capital letter also for subtitle. 
//Location: Publisher

echo '<div class="span10">
      <!--Body content-->';

//problemReport($bookTitle,$bookAuthor,$bookPub,$bookYear,$bookPlace);

$APA = createAPA($bookTitle,$bookAuthor,$bookPub,$bookYear,$bookPlace);

$MLA = createMLA($bookTitle,$bookAuthor,$bookPub,$bookYear,$bookPlace);

$chicago = chicagoStyle($bookTitle,$bookAuthor,$bookPub,$bookYear,$bookPlace);

echo '<h2>ISBN: '.$isbnQ.' <br> '.$bookTitle.'</h2>';

problemReport($bookTitle,$bookAuthor,$bookPub,$bookYear,$bookPlace);

echo '<h3>APA Citation: </h3>';
echo $APA;

echo '<h3>MLA Citation</h3>';
echo $MLA;

echo '<h3>Chicago Style</h3>';
echo $chicago;

echo '</div>
  </div>
</div>';

echo '
</div>
</div> <!--container --> 

</body>
</html>
';
?>

