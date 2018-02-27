<?php
/******************************************************************************************************
 * @author 	Carlos Alonso de Linaje
 * @email carlos@magentodesarrollo.com
 * @author 	Miguel Angel
 * @email miguel@magentodesarrollo.com
 *  
 * Client choose method.
 * 
 * @since 		2016-01-12
 * @modified	
 * @version 	1.0
 * @category    block
 * @package     default_default
 ******************************************************************************************************/
class Magentodesarrollo_Fintonic_Block_Payment_Redirect extends Mage_Core_Block_Abstract
{
	
	protected function _toHtml()
    {
		$session = Mage::getSingleton('checkout/session');
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
		
		$total = $order->getGrandTotal();
		
		$terms = Mage::getModel('fintonic/payment')->loadTerms($total);
		
		$tae1 = $terms[1]["tae"]*100;
		$tae1 = number_format($tae1,2,'.','');
		
		$tae0 = $terms[0]["tae"]*100;
		$tae0 = number_format($tae0,2,'.','');
        
        $total = number_format($total,2,'.','');
       
		$html = '
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago a plazos</title>
    <style>
    body {
      margin: 0;
      padding: 0;
      font-family: arial;
      font-size: 15px;
    }
    iframe {
      width: 100%;
      height: 100%;
      position: fixed;
    }
    .window-fixed {
      background-color: #cccccc;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 10;
      overflow: scroll;
    }
    .modal {
      background-color: #fff;
      position: absolute;
      margin: 0 auto;
      width: 700px;
      height: auto;
      left: 50%;
      top: 0;
      margin-left: -350px;
      margin-top: 0;
      padding: 35px;
      box-sizing: border-box;
      transition: all .5s ease;
      -moz-transition: all .5s ease;
      -webkit-transition: all .5s ease;
      box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.11), 0px 6px 30px rgba(0, 0, 0, 0.14);
      -webkit-box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.11), 0px 6px 30px rgba(0, 0, 0, 0.14);
      -moz-box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.11), 0px 6px 30px rgba(0, 0, 0, 0.14);
    }
    .modal H3 {
      font-weight: 300;
      border-bottom: 1px solid #ccc;
      margin: 0 0 15px;
      padding-bottom: 15px;
      font-size: 18px;
    }
    .close-window {
      padding: 15px;
      position: absolute;
      right: 0;
      top: 0;
      font-size: 18px;
      line-height: 15px;
      cursor: pointer;
      background-color: transparent;
      border: none;
    }
    .box {
      border: 1px solid #ccc;
      cursor: pointer;
      width: 44%;
      margin: 3%;
      padding: 15px;
      box-sizing: border-box;
      text-align: center;
    }
    .box:hover {
      background-color: #eee;
    }
    .box.select {
      background-color: #eee;
    }
    .box.select:before {
      background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAArCAYAAAAOnxr+AAAACXBIWXMAAAsSAAALEgHS3X78AAADJklEQVRYw9WZsWrbUBSGPwkXEnAaEwIJpBAXOmRIG6UNZGghd9VUv4HdJ6gfwXmCuk8Q9wniTFqVoUOgpHKaIdAONmQJhGDHBhtiUAdfuY5ly5Is2ekBLdKV/en859x77rmKbdtMa4appABt1DNd2CYRmBIGVIJl5CWA5Qmv1AATKOvCLscOaphKGigA2Smc0wCKQFEXdj1SUOnB4pSAI4F1YRciATVMJQOUfMgb1ipAThe25TVInQBZBI5jhATYAUzDVHKhPGqYSiliqf3YJ13YJd8enRMkwNE4z7o8aphKHvjCfG13OGYfgRqmogE/mb81gPTg9DUsfYmnYctyOnTHqJR8J26CxYU0KynhZ2jWMJX+wMTAg0LckM+TGu/3epHVbFW4uMpx3/KcPgtyie55VGZanHMlzxIp3mz9i6yl5A772sR65UAu233pc3F781W6wFIyVGTlAVS5jh/ECbmSEqRffHbd/131FW0Zx6OZWUru2F39lOp10c9PbBqmklaBdNySLy5sPrrX7Ta4uAoUbZrqZNWsJW93qoFBY5P83XZ5GsldRUksifR6q0QiseyS/PwyVEoINYynPuxZ6MIemSQAa6sZ1lY/uu5fXOV46NZDfbgqCwDfkPua2Z8PN9azLthxWX5ze8LNbTm0Qipg+R28lNRck/Yw7DjJfwXL8mEzE0FGN1sWzVZlJGzPa+XIJR/0qO8GwUO3zpklaLYqrmcb61nebh9HLrnj0UDSD8K2O7WJY9ud2rSSO2YF8ugg7Pllhm7XOw+jkByo6MKuq7LcPwn69n3L4swSY2Gr11+5q0fSdioNlnmhgsiBHQ6DdqfGH3+VkR8r90HlXroWFvb7D+1RgkUkOcA3XdjVR7tQWeUf8bTspQPaX0KlV0+fEOShAzlqu5wPsqTGaJWx22XpVcvZo8y5+ZAb7p26qicZAodzBB3ZgvxvunmejdwZzgQNQHg1cz0LZ/l1u2HnWJ92Sq8h5llzBOnh5+UVVUelBhTGSR0KdARwDticBWAo0CFo54xJm7BBbMhS0qR3zmSF+T8lipO7AW8Pn95ZQc6SvOwvucBy9a3hn/0AAAAASUVORK5CYII=");
      content: "";
      display: block;
      width: 42px;
      height: 42px;
      position: absolute;
      margin-left: 238px;
      margin-top: -38px;
    }
    .box.left {
      float: left;
    }
    .box.right {
      float: right;
    }
    .import-quota {
      margin-bottom: 15px;
    }
    .import-quota .line-one {
      font-weight: bold;
      font-size: 20px;
      margin: 0;
    }
    .import-quota .line-two {
      margin: 0;
    }
    .import-total {
      margin-bottom: 15px;
    }
    .import-total .line-one {
      margin: 0;
      font-size: 14px;
    }
    .import-total .line-two {
      margin: 0;
      font-size: 20px;
      font-weight: bold;
    }
    .commission {

    }
    .commission .line-one {
      margin: 0;
      font-size: 14px;
    }
    .commission .line-two {
      margin: 0;
      font-size: 14px;
    }
    .legal {
      margin: 20px 0 40px;
      display: inline-block;
      width: 100%;
    }
    .legal a {
      color: #000;
    }
    .btn-submit {
      background-color: #D52B1E;
      border: 1px solid #D52B1E;
      color: #fff;
      padding: 10px 15px;
      border-radius: 3px;
      -moz-border-radius: 3px;
      -webkit-border-radius: 3px;
      font-size: 14px;
      cursor: pointer;
      text-shadow: none;
    }
    .btn-submit:disabled{
      background-color: #E35C52;
      border: 1px solid #E35C52;
    }
    .logoFintonic {
      width: 120px;
      position: relative;
      margin-top: -20px;
      margin-bottom: 10px;
    }

    /* ****************************************************** */
    /*                       RESPONSIVE 	                    */
    /* ****************************************************** */

    @media (max-width: 767px) {
      .modal {
        width: 90%;
        height: auto;
        margin: 0 5%;
        left: 0;
        top: 0;
        margin-top: 5%;
        margin-bottom: 5%;
      }
      .box.select:before {
        margin-left: 0;
        margin-right: 28px;
        right: 0;
      }
    }
    @media (max-width: 520px) {
      .modal {
        width: 90%;
        height: auto;
        margin: 0 5%;
        left: 0;
        top: 0;
        margin-top: 5%;
        margin-bottom: 5%;
      }
      .box {
        width: 100%;
        margin: 5% 0;
      }
      .box.select:before {
        margin-left: 0;
        margin-right: 15px;
        right: 0;
      }
      .btn-submit {
        width: 100%;
      }
    }
    </style>
    <script>
      window.addEventListener("resize", heightModal);
      function heightModal(){
        var modal, modalHeight, marginTop;
        if(window.outerWidth > 767){
          modal = document.getElementById("modal");
          modalHeight = modal.offsetHeight;
          marginTop = modalHeight / 2;
          modal.style.top = "50%";
          modal.style.marginTop = "-"+marginTop+"px";
        }else{
          modal = document.getElementById("modal");
          modal.style.top = "0";
          modal.style.marginTop = "5%";
        }
      }
      function selectBox(paramBox) {
        var boxLeft = document.getElementById("boxLeft");
        var boxRight = document.getElementById("boxRight");
        if(paramBox == 1){
          boxLeft.className = "box left select";
          boxRight.className = "box right";
          document.getElementById("term").value = 6;
        }else{
          boxLeft.className = "box left";
          boxRight.className = "box right select";
          document.getElementById("term").value = 9;
        }
      }
      function legalButton() {
        document.getElementById("btnSubmit").disabled = !document.getElementById("legal").checked;
      }
      function startFin() {
        document.getElementById("startFin").submit();
      }
      function canceltFin() {
        document.getElementById("canceltFin").submit();
      }
    </script>
  </head>
  <body onload="heightModal()">
    <iframe src="https://oportunidades.dia.es"></iframe>
    <div class="window-fixed">
        <div id="modal" class="modal">
            <button class="close-window" onclick="canceltFin()" type="submit">X</button>
            <img class="logoFintonic" src="data:image/jpeg;base64,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b2wAAAAAAEVtbERib29sAAAAAABJbnRyYm9vbAAAAAAAQmNrZ09iamMAAAABAAAAAAAAUkdCQwAAAAMAAAAAUmQgIGRvdWJAb+AAAAAAAAAAAABHcm4gZG91YkBv4AAAAAAAAAAAAEJsICBkb3ViQG/gAAAAAAAAAAAAQnJkVFVudEYjUmx0AAAAAAAAAAAAAAAAQmxkIFVudEYjUmx0AAAAAAAAAAAAAAAAUnNsdFVudEYjUHhsQFIAAAAAAAAAAAAKdmVjdG9yRGF0YWJvb2wBAAAAAFBnUHNlbnVtAAAAAFBnUHMAAAAAUGdQQwAAAABMZWZ0VW50RiNSbHQAAAAAAAAAAAAAAABUb3AgVW50RiNSbHQAAAAAAAAAAAAAAABTY2wgVW50RiNQcmNAWQAAAAAAAAAAABBjcm9wV2hlblByaW50aW5nYm9vbAAAAAAOY3JvcFJlY3RCb3R0b21sb25nAAAAAAAAAAxjcm9wUmVjdExlZnRsb25nAAAAAAAAAA1jcm9wUmVjdFJpZ2h0bG9uZwAAAAAAAAALY3JvcFJlY3RUb3Bsb25nAAAAAAA4QklNA+0AAAAAABAASAAAAAEAAgBIAAAAAQACOEJJTQQmAAAAAAAOAAAAAAAAAAAAAD+AAAA4QklNBA0AAAAAAAQAAABaOEJJTQQZAAAAAAAEAAAAHjhCSU0D8wAAAAAACQAAAAAAAAAAAQA4QklNJxAAAAAAAAoAAQAAAAAAAAACOEJJTQP1AAAAAABIAC9mZgABAGxmZgAGAAAAAAABAC9mZgABAKGZmgAGAAAAAAABADIAAAABAFoAAAAGAAAAAAABADUAAAABAC0AAAAGAAAAAAABOEJJTQP4AAAAAABwAAD/////////////////////////////A+gAAAAA/////////////////////////////wPoAAAAAP////////////////////////////8D6AAAAAD/////////////////////////////A+gAADhCSU0EAAAAAAAAAgAAOEJJTQQCAAAAAAACAAA4QklNBDAAAAAAAAEBADhCSU0ELQAAAAAABgABAAAAAjhCSU0ECAAAAAAAEAAAAAEAAAJAAAACQAAAAAA4QklNBB4AAAAAAAQAAAAAOEJJTQQaAAAAAANNAAAABgAAAAAAAAAA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MS0yOVQxMDowMzo1MyswMTowMCIgeG1wOk1ldGFkYXRhRGF0ZT0iMjAxNi0xMS0yOVQxMDowMzo1MyswMTowMCIgeG1wOk1vZGlmeURhdGU9IjIwMTYtMTEtMjlUMTA6MDM6NTMrMDE6MDAiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NDY2NmIzMjUtYmU1ZS00MTBmLThkNmMtYWIzZDVlYzAyMTkwIiB4bXBNTTpEb2N1bWVudElEPSJhZG9iZTpkb2NpZDpwaG90b3Nob3A6MjdhZTg0YTItZjZhMS0xMTc5LWEwY2MtYjQxNGIwYmVlNjI2IiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6YzM4OGM0NzMtZDNlNC00MWFjLTkzNGEtMzcxOGIyNzUyNGEzIiBkYzpmb3JtYXQ9ImltYWdlL2pwZWciIHBob3Rvc2hvcDpDb2xvck1vZGU9IjMiIHBob3Rvc2hvcDpJQ0NQcm9maWxlPSJBZG9iZSBSR0IgKDE5OTgpIj4gPHhtcE1NOkhpc3Rvcnk+IDxyZGY6U2VxPiA8cmRmOmxpIHN0RXZ0OmFjdGlvbj0iY3JlYXRlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDpjMzg4YzQ3My1kM2U0LTQxYWMtOTM0YS0zNzE4YjI3NTI0YTMiIHN0RXZ0OndoZW49IjIwMTYtMTEtMjlUMTA6MDM6NTMrMDE6MDAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChNYWNpbnRvc2gpIi8+IDxyZGY6bGkgc3RFdnQ6YWN0aW9uPSJzYXZlZCIgc3RFdnQ6aW5zdGFuY2VJRD0ieG1wLmlpZDo0NjY2YjMyNS1iZTVlLTQxMGYtOGQ2Yy1hYjNkNWVjMDIxOTAiIHN0RXZ0OndoZW49IjIwMTYtMTEtMjlUMTA6MDM6NTMrMDE6MDAiIHN0RXZ0OnNvZnR3YXJlQWdlbnQ9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE1IChNYWNpbnRvc2gpIiBzdEV2dDpjaGFuZ2VkPSIvIi8+IDwvcmRmOlNlcT4gPC94bXBNTTpIaXN0b3J5PiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg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dJaCVtSxmFpVWiA0Fev/092D5Q/F7ZHyh2PDtzcc8+A3PgZqjJbF31jYIp8rtXLTxLFPemmZIctgspHGsdfQSssdTEoIaOVIpUehmaFqjKniPXqbPYz3z5n9jOaJN52aJLvY7tVjvrGRisV1EpqvcKmKeIktBOoLRsTUPGzo2vL3D8RvkN0nX1lNu7rbMbi27E0iwb56/wAfW7s2nX0oLqKmpgoIp87t8yohLQVtOpQcB3HqJpHPFIO1qH0OD12A9uvvCez/ALm2ltNy9zpbWe8MBqsb+RLS6jbHarOVguKEgB4ZDXzVT2gPsx2j3FvvaG0OpMznt+7r2dsiaH+5uyP7sZeqfG1EEElHj0Y0uDGWy74qlmeChWreX7OJikduLWCIrNIAAx4noXbdyL7c8q8xcxe4O27VtNhzHuin6y9+phUSKzB5CNU/hRCVgHnMSr4zAM9c1M90B/L4717myNFXb0wuV6Y65dopchndzU6Um+snRExtJS7U2lN5anH1lVC9krsmsUMAJdYZmUIWZbqOMEKdT/y/M/5uoP8Adv73ftX7bWd1a8tbnb8y85AERwWzF7GJ8gNdXYosiKR3Q2xZ3+FpIlJYbDfWnW2zOodj7d666/w0OB2ptihWhxdBEzyyEF2mqa2tqpS1RXZLIVUjz1NRKzSzzyM7Ek+yt3Z2Lse49cfudedOZPcPmjeOcebdya73++l1yyGgHABURRRUjjUBI41AVEVVUADpde69Bbr3v3Xuve/de697917r3v3Xuve/de697917r3v3Xuve/de697917r//1N/j37r3XvfuvdNNL/xcKv8A5C/6GHvfkOjCf/cO36dveui/r3v3Xuve/de697917r3v3Xuve/de697917r3v3Xuve/de697917r3v3Xuve/de6//9k=" />
            <h3>Fintonic te ofrece estas opciones de financiación para tu compra de <b>'.$total.'€</b></h3>
            <div class="content">
                <div id="boxLeft" class="box left" onclick="selectBox(1)">
                    <div class="import-quota">
                        <p class="line-one">
                          '.$terms[1]["term"].'€/mes
                        </p>
                        <p class="line-two">
                          '.$terms[1]["month"].' CUOTAS
                        </p>
                    </div>
                    <div class="import-total">
                        <p class="line-one">
                          Al final pagas
                        </p>
                        <p class="line-two">
                          '.$terms[1]["total"].'
                        </p>
                    </div>
                    <div class="commission">
                        <p class="line-one">
                          Sin comisiones
                        </p>
                        <p class="line-two">
                          '.$tae0.' TAE
                        </p>
                    </div>
                </div>
                <div id="boxRight" class="box right select" onclick="selectBox(2)">
                    <div class="import-quota">
                        <p class="line-one">
                          '.$terms[0]["term"].'€/mes
                        </p>
                        <p class="line-two">
                          '.$terms[0]["month"].' CUOTAS
                        </p>
                    </div>
                    <div class="import-total">
                        <p class="line-one">
                          Al final pagas
                        </p>
                        <p class="line-two">
                          '.$terms[0]["total"].'
                        </p>
                    </div>
                    <div class="commission">
                        <p class="line-one">
                          Sin comisiones
                        </p>
                        <p class="line-two">
                          '.$tae0.' TAE
                        </p>
                    </div>
                </div>
                <div class="legal clear">
                    <input id="legal" onchange="legalButton()" type="checkbox" value="" name="" /><label for="legal">Acepto las <a href="https://s3-eu-west-1.amazonaws.com/testonline01/qr_ecom/docs/Condiciones+generales.pdf" target="_blank">condiciones legales</a></label>
                </div>
                <button id="btnSubmit" onclick="startFin()"  class="btn-submit" type="submit" disabled>Solicitar financiación</button>
            </div>
        </div>
        <form id="canceltFin" action="'.Mage::getUrl("fintonic/payment/cancel", array("_secure" => true)).'" method="post">

        </form>
        <form id="startFin" action="'.Mage::getUrl("fintonic/payment/confirm", array("_secure" => true)).'" method="post">
			<input type="hidden" value="9" id="term" name="term" />
        </form>
    </div>
  </body>
  </html>

';

        return $html;
    }



    
}
