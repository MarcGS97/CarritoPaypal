<?php
	session_start();
?>
<?php
	include('php/Config.php');
	include('php/Conexion.php');
	include('php/Carrito.php');
	include('php/Header.php');
?>
<?php
	if($_POST){
		$Total = 0;
		$SSID = session_id();
		$Correo = $_POST['Correo'];
		foreach ($_SESSION['Carrito'] as $key => $Producto) {
			$Total = $Total + ($Producto['Precio'] * $Producto['Cantidad']);
		}
		//echo '<h3>' . $Total . '</h3>';
		$s = $pdo->prepare('INSERT INTO tventas (CveTransaccion,DatosPago,Fecha,Correo,Total,Status) VALUES(:CVE,:DP,NOW(),:CORREO,:TOTAL,:ST);');
		$s->bindParam(':CVE', $SSID);
		$s->bindParam(':DP', $SSID);
		$s->bindParam(':CORREO', $Correo);
		$s->bindParam(':TOTAL', $Total);
		$s->bindParam(':ST', $SSID);
		$s->execute();

		$IdVenta = $pdo->lastInsertId();


		foreach ($_SESSION['Carrito'] as $key => $Producto) {
			$s = $pdo->prepare('INSERT INTO tdetalleventa (IdVenta,IdProducto,PrecioUnitario,Cantidad,Descargado) VALUES(:IDV,:IDP,:PU,:CANT,0);');
			$s->bindParam(':IDV', $IdVenta);
			$s->bindParam(':IDP', $Producto['Id']);
			$s->bindParam(':PU', $Producto['Precio']);
			$s->bindParam(':CANT', $Producto['Cantidad']);
			$s->execute();
		}
	}
?>

	<style>
        /* Media query for mobile viewport */
        @media screen and (max-width: 400px) {
            #paypal-button-container {
                width: 100%;
            }
        }
        
        /* Media query for desktop viewport */
        @media screen and (min-width: 400px) {
            #paypal-button-container {
                width: 250px;
            }
        }
    </style>
    	<div class="jumbotron text-center">
    		<h1 class="display-4">¡Paso Final!</h1>
    		<hr class="my-4">
    		<p class="lead">Estas a punto de pagar con paypal la cantidad de : <h4>$<?php echo number_format($Total,2); ?></h4> </p>
    		<div class="row">
    			<div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 offset-md-3 offset-lg-4" align="center">
    				<div id="paypal-button-container"></div>
    			</div>
    		</div>
    		<p>Los productos podran ser descargados una vez que se procese el pago
    			<strong>Para aclaraciones Marco@outlook.com</strong>
    		</p>
    	</div>

		<?php
			include('php/Scripts.php');
		?>

		<!-- Include the PayPal JavaScript SDK -->
		<!--<script src="https://www.paypal.com/sdk/js?client-id=sb&currency=USD"></script>-->
		<script type="text/javascript" src="https://www.paypalobjects.com/api/checkout.js"></script>

		<script>
			// Render the PayPal button into #paypal-button-container
			//paypal.Buttons().render('#paypal-button-container');
			$(document).ready(function(){
				
			});

			paypal.Button.render({
					env:'sandbox',
					style:{
						label:'checkout',
						size:'responsive',
						shape:'pill',
						color:'gold'
					},
					client:{
						sandbox:'AS3G22vXdgKE3Bl6vLiMS8M8g1ZVLPIuzfy3hFfrq6WQKAt3cknvXSAzBdLiDkCMtQn-0CJok_8KlHYl',
						//production:'ELGW18uMT4wfrgQ4EkHCsifq7yelWd_8QkUAyKIbedJujzhAmV7q_I9VfMiEuY3t_VxlMxWPEpUyxjE2'
						production:''
					},
					payment: function(data, actions){
						return actions.payment.create({
							payment:{
								transactions : [
									{
										amount : {total : '<?php echo $Total; ?>', currency : 'MXN'},
										description: 'Compra de productos a XROM SYSTEMS : <?php echo number_format($Total,2); ?>',
										custom: '<?php echo $SSID; ?>#<?php echo openssl_encrypt($IdVenta, COD, KEY); ?>'
									}
								]
							}
						});
					},
					onAuthorize : function(data, actions){
						return actions.payment.execute().then(function(){
							console.log(data);
							window.location = 'Verificador.php?paymentToken=' + data.paymentToken + '&paymentID=' + data.paymentID;
						});
					}
				},'#paypal-button-container');
		</script>

<?php
	include('php/Footer.php');
?>