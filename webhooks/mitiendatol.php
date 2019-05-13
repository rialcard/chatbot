<?php
//esto incluye la librería
include_once "../codigo/libreria_df.php";
//credenciales('cursobot','Dialog325486#!')
debug();

//me conecto a db
$mysqli = mysqli_connect("localhost", "Rialcard_cafetol", "Dialog325486#!", "Rialcard_cafetol");

if (!$mysqli) {
	echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
	die();
}

//demora
$demora_x_empanada = 0.5;

if (intent_recibido("imagen")) {

 	$url = obtener_imagen();

	agrega_imagen($url);
	enviar_texto("Imagen recibida, estará publicada en https://mitientatol.ml/imagenes.php");

}

if (intent_recibido("imagen2")) {

  if(origen()=="FACEBOOK" || origen()== "TELEGRAM"){

    $imagenes[0] = "http://static.diariosur.es/malagaenlamesa/multimedia/201809/25/media/cortadas/cafedos-kREE-U6010234603830GE-624x385@Diario%20Sur.jpg";
    $imagenes[1] = "https://ichef.bbci.co.uk/news/660/cpsprodpb/76B0/production/_105848303_gettyimages-996540050.jpg";
    $imagenes[2] = "https://static1.squarespace.com/static/5b4e2db3a9e028168f70635b/t/5b6f35a740ec9a4b5a75ef1b/1536535014887/IMG_8441.JPG?format=1500w";

   enviar_imagenes( $imagenes,origen() );
  }
}

// si el intent recibido es consultar precio
if (intent_recibido("consultar_precio")){
$p_irco = $precios['irco'];
$p_limon = $precios['limon'];
$p_hermosas = $precios['hermosas'];
enviar_texto("El café de Irco cuesta $p_irco El café del Limón cuesta $p_limon y la de las hermosas cuesta $p_hermosas ");

}
// si el intent recibido es tomar orden

if (intent_recibido("tomar_orden")){
  $cantidad1 = obtener_variables()['cantidad1'];
  $sabor1 = obtener_variables()['sabor1'];
  $disponibilidad1 = 0;
  $precio1 = 0;
  $subtotal1 = 0;
  if ($cantidad1 > 0){
    $precio1 = consulta_precio($sabor1);
    $disponibilidad1 = consulta_stock($sabor1);
    $subtotal1 = $cantidad1 * $precio1;
    if($cantidad1 > $disponibilidad1){
      enviar_texto("lo siento, no tenemos suficiente café $sabor1 en este momento, si deseas volver a ordenar el pedido simplemente di 'quiero ordenar' la cantidad que actualmente nos quedan es de $disponibilidad1 unidades");
      return;
    }
  }

  $cantidad2 = obtener_variables()['cantidad2'];
  $sabor2 = obtener_variables()['sabor2'];
  $disponibilidad2 = 0;
  $precio2 = 0;
  $subtotal2 = 0;
  if ($cantidad1 > 0){
    $precio2 = consulta_precio($sabor2);
    $disponibilidad2 = consulta_stock($sabor2);
      $subtotal2 = $cantidad2 * $precio2;
    if($cantidad2 > $disponibilidad2){
      enviar_texto("lo siento, no tenemos suficiente café $sabor2 en este momento, si deseas volver a ordenar el pedido simplemente di 'quiero ordenar' la cantidad que actualmente nos quedan es de $disponibilidad2 unidades");
      return;
    }
  }

  $cantidad3 = obtener_variables()['cantidad3'];
  $sabor3 = obtener_variables()['sabor3'];
  $disponibilidad3 = 0;
  $precio3 = 0;
  $subtotal3 = 0;
  if ($cantidad3 > 0){
    $precio3 = consulta_precio($sabor3);
    $disponibilidad3 = consulta_stock($sabor3);
    $subtotal3 = $cantidad3 * $precio3;
    if($cantidad3 > $disponibilidad3){
      enviar_texto("lo siento, no tenemos suficiente café $sabor3 en este momento, si deseas volver a ordenar el pedido simplemente di 'quiero ordenar' la cantidad que actualmente nos quedan es de $disponibilidad3 unidades");
      return;
    }
  }

  $total = $subtotal1 + $subtotal2 + $subtotal3;
  enviar_texto("Usted encargó: $cantidad1 $sabor1,$cantidad2 $sabor2,$cantidad3 $sabor3 y el total es de $ $total por favor dígame si desea confirmar este pedido");

  }

// si se confirma la ordenar
if (intent_recibido("orden_confirmada")) {
  $nombre = obtener_variables()['nombre'];
  $domicilio = obtener_variables()['domicilio'];
  $telefono = obtener_variables()['telefono'];

  $sabor1 = obtener_variables()['sabor1'];
  $cantidad1 =obtener_variables()['cantidad1'];
  $subtotal1 = 0;
  if ($cantidad1>0){
    $subtotal1 = $cantidad1 * consulta_precio($sabor1);
    descuenta_stock($cantidad1,$sabor1);
  }

  $sabor2 = obtener_variables() ['sabor2'];
  $cantidad2 = obtener_variables()['cantidad2'];
  $subtotal2 = 0;
  if ($cantidad2>0){
   $subtotal2 = $cantidad2 * consulta_precio($sabor2);
   descuenta_stock($cantidad2,$sabor2);
 }

 $sabor3 = obtener_variables() ['sabor3'];
 $cantidad3 = obtener_variables()['cantidad3'];
 $subtotal3 = 0;
 if ($cantidad3>0){
  $subtotal3 = $cantidad3 * consulta_precio($sabor3);
  descuenta_stock($cantidad3,$sabor3);
 }

 //enviamos mail,
 $total = $subtotal1 + $subtotal2 + $subtotal3;
 $mensaje = "Nueva orden para $nombre enviar: \n\n\n $sabor1 $cantidad1 \n\n $sabor2 $cantidad2 \n\n $sabor3 $cantidad3 \n\n enviar a: $domicilio \n\n Total a cobrar: $total";
 mail('racardenasgarcia@gmail.com', 'Nueva Orden desde Cafebot!', $mensaje);

 $cantidad_total = $cantidad1 + $cantidad2 + $cantidad3;
 $demora = $demora_x_cafe * $cantidad_total;
 enviar_texto("Listo! su orden está en camino, llegará a destino en aproximadamente $demora minutos. Gracias!");
  }

  //***************************
  //**** FUNCIONES ************
  //***************************

  function consulta_stock($sabor){
    global $mysqli;
    $resultado = $mysqli->query("SELECT $sabor FROM `stock` WHERE 1");
    $stock = mysqli_fetch_assoc($resultado);
    $cantidad = $stock[$sabor];
    return $cantidad;
  }

  function consulta_precio($sabor){
    global $mysqli;
    $resultado = $mysqli->query("SELECT $sabor FROM `precios` WHERE 1");
    $precios = mysqli_fetch_assoc($resultado);
    $precio = $precios[$sabor];
    return $precio;
  }

  function descuenta_stock($cantidad,$sabor){
  	  global $mysqli;
  		$mysqli->query("UPDATE `stock`  SET $sabor = $sabor - $cantidad ");
  }

  function agrega_stock($cantidad,$sabor){
  	  global $mysqli;
  		$mysqli->query("UPDATE `stock`  SET $sabor = $sabor + $cantidad ");
  }

  function agrega_imagen($url){
  	  global $mysqli;
  		$mysqli->query("INSERT INTO `imagenes` (`url`) VALUES ('$url')");
  }



   ?>
