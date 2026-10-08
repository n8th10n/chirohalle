<?php 



$naam_ouder = $_POST['naam_ouder'];
$naam_lid = $_POST['naam_lid'];
$email = $_POST['email'];
$geboortedatum = $_POST['geboortedatum'];
$adres = $_POST['adres'];
$geslacht = $_POST['geslacht'];
$gsm = $_POST['gsm'];
$foto = $_POST['foto'];
$fb = $_POST['fb'];
$formcontent="

Nieuwe Inschrijving:\n
Naam Ouder/Voogd: 
$naam_ouder \n
Naam Lid: 
$naam_lid \n
E-mail: 
$email \n
Geboortedatum: 
$geboortedatum  \n
Adres: 
$adres \n
Geslacht: 
$geslacht  \n
GSM-Nummer: 
$gsm   \n
Foto's maken? :$foto \n
Foto's Publiceren? : $fb\n



";
$recipient = "inschrijvingen@chirohalle.be";
$subject = "Inschrijving $naam_lid";
$mailheader = "Nieuwe inschrijving";
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

<h3 class='text-heading'>Inschrijving verstuurd!</h3>
<p class='sample-text'>
							
                    De inschrijving is succesvol verzonden naar ons, schrijf aub het inschrijvingsgeld ter waarde van <strong>25 EURO</strong> zo snel mogelijk over op <strong>BE39 7340 3206 9219</strong> om jouw inschrijving te bevestigen.
            
            <br>
            
                    Indien wij uw betaling hebben ontvangen sturen wij u eerstdaags een mail met de bevestiging van de inschrijving.

            
						</p>
            
            
            <a href='index.html' class='genric-btn success circle arrow'>Terugkeren<span class='lnr lnr-arrow-right'></span></a>

</div>


";




?>