<?php 



$naam = $_POST['naam'];
$email = $_POST['email'];
$vraag = $_POST['vraag'];

$formcontent="

Nieuw contactaanvraag:\n
Naam: 
$naam \n
Email adres: 
$email \n
Vraag: 
$vraag 

";

$recipient = "contact@chirohalle.be";
$subject = "Nieuw contactaanvraag van $naam";
$mailheader = "Iemand wilt contact opnemen";
mail($recipient, $subject, $formcontent, $mailheader) or die("Error!");



echo "



<head>
	<!-- Mobile Specific Meta -->
	<meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>
	<!-- Favicon-->
	<link rel='shortcut icon' href='img/fav.png'>
	<!-- Author Meta -->
	<meta name='author' content='Nathan Mistiaen'>
	<!-- Meta Description -->
	<meta name='description' content=''>
	<!-- Meta Keyword -->
	<meta name='keywords' content=''>
	<!-- meta character set -->
	<meta charset='UTF-8'>
	<!-- Site Title -->
	<title>Bevestiging</title>

	<link href='https://fonts.googleapis.com/css?family=Poppins:300,500,600' rel='stylesheet'>
	
		<link rel='stylesheet' href='css/linearicons.css'>
		<link rel='stylesheet' href='css/font-awesome.min.css'>
		<link rel='stylesheet' href='css/nice-select.css'>
		<link rel='stylesheet' href='css/magnific-popup.css'>
		<link rel='stylesheet' href='css/bootstrap.css'>
		<link rel='stylesheet' href='css/main.css'>
	</head>



<div class='section-top-border'>

<h3 class='text-heading'>Contactformulier verstuurd!</h3>
<p class='sample-text'>
							
                  Het contactformulier is succesvol verstuurt naar ons, wij proberen dit zo snel mogelijk te beantwoorden!
            
						</p>
            
            
            <a href='index.html' class='genric-btn success circle arrow'>Terugkeren<span class='lnr lnr-arrow-right'></span></a>

</div>


";




?>