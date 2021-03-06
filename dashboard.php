<!DOCTYPE html>
<?php
  session_start();

  $data = $_SESSION["data"];
  $aktuellerSchritt = $_SESSION["aktuellerSchritt"];
  $nextSchritt = $aktuellerSchritt + 1;
  $anzahlRasten = $_SESSION["anzahlRasten"];
  $fertigBei = $anzahlRasten + 1;
  $buttonPressed = $_SESSION["buttonPressed"]
?>

<html>

  <head>
    <meta charset="utf-8">
    <title>Brau-o-mat 2.0</title>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="assets/css/materialize.min.css"  media="screen,projection"/>

      <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style media="screen">
    .header{
      color: #009688;
      font-weight: 300;
    }
    th{
      font-weight: 300;

    }
    .transparent{
      opacity: 0;
      height: 0px;
    }
    </style>

    <script>
      var i = 0;
      window.onload = function(){
        // Bei Seiten start werden Informationen aus Php extrahiert

        var aktuellerSchritt = <?php echo $aktuellerSchritt; ?>;
        var anzahlRasten = <?php echo $anzahlRasten; ?>;
        var buttonPressed = <?php echo $buttonPressed; ?>;

        if (buttonPressed == 1) {
          // Hintergrundfarbe des Timers wird weiß
          document.getElementById("Timer").classList.remove('teal');
          document.getElementById("TimerContent").innerHTML = "Bitte erhitzen Sie die Temperatur um den Timer zu starten";
	//Wenn der Button vor der Aktualisierung gedrückt wurde, wird der Timer bereit gemacht
        }
      }
      var buttonPressed1 = <?php echo $buttonPressed; ?>;
      console.log(buttonPressed1);
      var timerStart = 0;
      var countDownDate = 0;

      var richtTemp = <?php if(0 < $aktuellerSchritt and $aktuellerSchritt < $fertigBei) {
        echo $data["richtTemp".$aktuellerSchritt];
      }else{
        echo "\"keineRT\"";} ?>;
      //Wenn eine Richttemperatur vorhanden is wird diese eingesetzt

      var buffer = <?php echo $_SESSION["buffer"]; ?>;
      //Buffer wird eingesetzt

      if(richtTemp != "keineRT"){
        var minTemp = richtTemp - buffer;
        var maxTemp = richtTemp + buffer;
      }


//####################################################################################

      function makeButton(){
        var card = document.getElementById("TimerContent");
        card.innerHTML="";
	//Text wird aus dem Timerfeld entfernt

        var button = document.createElement("button");
        var buttonText = document.createTextNode("Weiter");
        button.setAttribute("onclick", "NextStep()");
        button.setAttribute("type", "button");
        button.classList.add("btn");
        button.classList.add("waves-effect");
        button.classList.add("waves-light");
	//Button wird erstellt und mit Attributen modifiziert

        button.appendChild(buttonText);
        card.appendChild(button);
	//Text wird in Button eingesetzt und Button ins Feld
        }
//####################################################################################

      //AJAX liest per php script Temperatur aus
      function refreshTemp(){
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if(this.readyState == 4 && this.status == 200){
            document.getElementById("AktTemp").innerHTML = this.responseText;
          }
        };
        xmlhttp.open("GET","php/tempAuslesen.php",true);
        xmlhttp.send();
        i++;

        //Temperatur wird zu float umgewandelt
        var temperaturStr = document.getElementById("AktTemp").textContent;
        var temperatur = parseFloat(temperaturStr);

        if(richtTemp != "keineRT"){
	//Prüfen, ob eine Richttemperatur vorhanden ist
          if(temperatur > maxTemp){
	    document.getElementById("warningHot").classList.remove("transparent");
            setRightTemp(1);
 	    //Warnung wird angezeigt und Timer gestartet

          }else if(temperatur < minTemp){
	    document.getElementById("warningCold").classList.remove("transparent");
		  //Warnung wird angezeigt

          }else if(minTemp< temperatur && temperatur < maxTemp){
	    document.getElementById("warningHot").classList.add("transparent");
            document.getElementById("warningCold").classList.add("transparent");
            //Warnungen werden verborgen und Timer gestartet
            setRightTemp(1);
          }
        }
      }

      // Aktualisiert Temperatur im SekundenTakt
      setInterval(refreshTemp, 1000);

//####################################################################################
      function conTime(){

        if(getActiveTimer() == 1){
          var endZeit = getCountDownDate();
          var jetztZeit = new Date().getTime();
	  var dauer = endZeit * 1000 - jetztZeit;
	  //Die Differenz zwischen Endzeitpunkt und aktueller Zeit wird gebildet

          var minuten = Math.floor(dauer  / (1000 *60));
          var sekunden = Math.floor((dauer % (1000 * 60)) / 1000);
	  //Die Zeit wird in Minuten und Sekunden umgewandelt


          document.getElementById("TimerContent").innerHTML = minuten + "m " + sekunden + "s ";
	  //Das Ergebnis wird in gemischter Schreibweise ausgegeben

          if (dauer < 0) {
            clearInterval(zeitInterval);
	    //Timer wird gestoppt

	    document.getElementById("Timer").classList.add("teal");
            makeButton();
	    //Button wird erzeugt
          }
        }
        if(getActiveTimer()==0 && getRightTemp()==1){
          getStartTime();
   	//Wenn der Timer noch nicht gestartet wurde, aber die Temperatur stimmt, wir der Endzeitpunkt geladen
        }

      }

      function getStartTime(){
        console.log("Los gehts");
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if(this.readyState == 4 && this.status == 200){
            setCountDownDate(this.responseText);
		setActiveTimer(1)
          }
        };
        xmlhttp.open("GET","php/getStartTime.php",true);
        xmlhttp.send();

      }

      var zeitInterval = setInterval(conTime, 1000);


//####################################################################################

      // Bei Knopfdruck werden die Daten des nächsten Schritt geladen
      function NextStep(){

        // Nächster Schritt wird gestartet
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if(this.readyState == 4 && this.status == 200){
            //document.getElementById("brand-logo").innerHTML = this.responseText;
		refreshPage();
          }
        };
        xmlhttp.open("GET","php/NextStep.php",true);
        xmlhttp.send();

      }

      function refreshPage(){
        // Lädt Seite neu
        window.location.replace("/Brau-o-mat/dashboard.php");
      }
//##################################################################################
      var activeTimer = 0;
      function setActiveTimer(value){
        activeTimer = value;
      }
      function getActiveTimer(){
        return activeTimer;
      }
      var rightTemp = 0;
      function setRightTemp(value){
        rightTemp = value;
      }
      function getRightTemp(){
        return rightTemp;
      }
      function setCountDownDate(value){
        countDownDate = value;
      }
      function getCountDownDate(){
        return countDownDate;
      }
//##################################################################################

    </script>
  </head>

  <body>

    <nav>
      <div class="nav-wrapper teal">
        <div class="row">
          <div class="col s0 m1 l1"></div>
          <div class="col m5">
            <a href="#" class="brand-logo" id="brand-logo">Brau-o-mat</a>
          </div>
          <div class="col m6">
            <ul id="nav-mobile" class="right hide-on-med-and-down">
              <li class="active"><a href="#">Dashboard</a></li>
              <li><a href="graph.php">Graph</a></li>
              <li><a href="settings.php">Einstellungen</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="container">

      <div class="card">
        <div class="card-content">
          <h2 class="header" id="AktSchrittName">
            <?php
            if($aktuellerSchritt == 0) {
              echo "Willkommen zum Dashboard";
            }elseif ($aktuellerSchritt >= $fertigBei) {
              echo "Brauvorgang beendet";
            }
            else{
              echo $data["schrittName".$aktuellerSchritt];
            };
            ?>
         </h2>
          <p class="flow-text" id="AktSchrittInfo">
            <?php
            if($aktuellerSchritt == 0) {
              echo "Hier erhalten Sie alle Informationen zum aktuellen Brauvorgang";
            }elseif ($aktuellerSchritt >= $fertigBei) {
              echo "Über die Navigationsleiste können Sie sich nun den Vorgang Graphisch anzeigen lassen";
            }
            else{
              echo "Schritt ".$aktuellerSchritt." von ".$anzahlRasten;
            };
            ?>
          </p>
        </div>
      </div>
      <div class="row">

        <div class="col s12 m6 l4">
          <div class="card">
            <div class="card-content">
              <table>
                <tbody id="Temperatur">
                  <tr>
                    <th class="header">Aktuelle Temperatur:</th>
                    <th id="AktTemp"></th>
                  </tr>
                  <?php
                    if(0 < $aktuellerSchritt && $aktuellerSchritt < $fertigBei){
                      echo "<tr><th class=\"header\">Richttemperatur:</th>";
                      echo "<th>".$data["richtTemp".$aktuellerSchritt]." °C</th></tr>";

                    }
                  ?>
                  <tr>
                    <th class="header">Buffer:</th>
                    <th><?php echo $_SESSION["buffer"]; ?></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col s12 m6 l4">
          <div class="card teal" id="Timer">
            <div class="card-content" id="TimerContent">
              <button type="button" name="button" onclick="NextStep()" class="btn waves-effect waves-light">
                Brauvorgang starten
              </button>
            </div>
          </div>
        </div>

        <div class="col s12 m6 l4">
          <div class="card">
            <div class="card-content" id="NächsterSchrittInfo">

              <?php
              if($aktuellerSchritt < $anzahlRasten){
                echo "<h5 class=\"header\">Nächster Schritt:</h5>";

                echo "<table><tbody>";

                echo "<tr><th class=\"header\">Name:</th><th>".$data["schrittName".$nextSchritt]."</th></tr>";
                echo "<tr><th class=\"header\">Richttemperatur:</th><th>".$data["richtTemp".$nextSchritt]." °C</th></tr>";
                echo "<tr><th class=\"header\">Dauer:</th><th>".$data["zeit".$nextSchritt]/60 ." min</th></tr>";
                echo "</tbody></table>";
              }else{
                echo "<h5 class=\"header\">Keine weiteren Schritte</h5>";
              }
              ?>
            </div>
          </div>
        </div>
      </div>
      <div class="card red darken-4 transparent" id="warningHot">
        <div class="card-content red darken-1">
          <h5 class="white-text center">Gemisch zu heiß!</h5>
          <p class="white-text center">Bitte Temperatur senken</p>
        </div>
      </div>
      <div class="card red darken-4 transparent" id="warningCold">
        <div class="card-content red darken-1">
          <h5 class="white-text center">Gemisch zu kalt!</h5>
          <p class="white-text center">Bitte Temperatur erhöhen</p>
        </div>
      </div>
      <div class="transparent" id="transport">

      </div>
    </div>
  </body>
</html>
