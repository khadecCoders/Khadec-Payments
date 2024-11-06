<?php 
require_once '../autoloader.php';

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
$url = "https://";   
else  
$url = "http://";   
// Append the host(domain name, ip) to the URL.   
$url.= $_SERVER['HTTP_HOST'];   

// Append the requested resource location to the URL   
$url.= $_SERVER['REQUEST_URI'];    

// Use parse_url() function to parse the URL
// and return an associative array which
// contains its various components
$url_components = parse_url($url);
 
// Use parse_str() function to parse the
// string passed via URL
parse_str($url_components['query'], $params);
     
// Display result
// echo ' Price is '.$params['price'];

// $paynow = new Paynow\Payments\Paynow(
//     '13287',
//     '86fefa08-b0b8-4b80-9d19-a297b73a889c',
//     'http://127.0.0.1:5500/payment-success.html?proceed|'.$params['first_name'].'|'.$params['last_name'].'|'.$params['address'].'|'.$params['email'].'|'.$params['city'].'|'.$params['paymentMethod'].'|'.$params['totalAmount'].'',
//     'http://sacubana.web.app'
// );

$paynow = new Paynow\Payments\Paynow(
    '16998',
    '90fb94ab-411f-4b60-ba6b-4387e909d5cc',
    'http://127.0.0.1:5500/payment-success.html?proceed|'.$params['first_name'].'|'.$params['last_name'].'|'.$params['address'].'|'.$params['email'].'|'.$params['city'].'|'.$params['paymentMethod'].'|'.$params['totalAmount'].'',
    'http://sacubana.web.app'
);

$payment = $paynow->createPayment('Invoice', $params['email']);

$payment->add('Order Placement', ($params['totalAmount']));

$response = $paynow->send($payment);

?>

<?php if(!$response->success): ?>
    <div style="width: 100%; height: 95vh; display: flex; align-items: center; justify-content: center;  background-color: aliceblue; ">
        <div style="width: 550px; height: 200px; display: flex; justify-content: center; background-color: rgb(255, 255, 255); padding: 10px 10px;">
           <div class="" style="text-align: center;">
            <h1 style="color: rgb(222, 53, 53); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">We're Sorry :(</h1>
            <p style="color: rgb(228, 26, 26); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px;">An error occured while communicating with Paynow</p>
            <p style="color: rgb(228, 26, 26); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px;"><?= $response->error ?></p>
            <div>
                <button style="padding: 8px; background-color: rgb(224, 90, 90); border: none;"><a href="" style="text-decoration: none; font-size: 15px; color: white;">Try Again</a></button>
            </div>
           </div>
        </div>
    </div>

<?php else: ?>
    <?php 
    session_start();

    if(isset($_SESSION['pollUrl'])){

        $pollUrl=$_SESSION['pollUrl'];
       
        $status = $paynow->pollTransaction($pollUrl);
        if($status->paid()) {     
            echo "Paid Successfully!";

            unset($_SESSION['pollUrl']);
              } else {
                echo('Your payment was unsuccessful');
                unset($_SESSION['pollUrl']);
            }   

    }
    else{
        $pollUrl = $response->pollUrl(); 
        $_SESSION['pollUrl']=$pollUrl;
        }   

    ?>

    <div style="width: 100%; height: 95vh; display: flex; align-items: center; justify-content: center;  background-color: aliceblue; ">
        <div style="width: 400px; height: 200px; display: flex; justify-content: center; background-color: rgb(255, 255, 255); padding: 10px 10px;">
           <div class="" style="text-align: center;">
            <p style="color: rgb(78, 78, 78); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px;">You are making a payment of</p>
            <h1 style="color: rgb(78, 78, 78); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">US$ <?= $payment->total ?></h1>
            <p style="color: rgb(78, 78, 78); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 18px;">For an order placed on Sacubana Solar & Gas Solutions.</p>
            <div>
                <button style="padding: 8px; background-color: rgb(224, 90, 90); border: none;"><a href="../../check-out.html" style="text-decoration: none; font-size: 15px; color: white;">Cancel</a></button>
                <button style="padding: 8px; background-color: rgb(90, 224, 161); border: none;"><a href="<?= ($response->redirectUrl())."?a=".$pollUrl?>" style="text-decoration: none; font-size: 15px; color: rgb(0, 0, 0);">Proceed</a></button>
            </div>
           </div>
        </div>
    </div>


<?php endif; ?>


<?php if(isset($_GET['paynow-return'])): ?>
<script>
    alert('Thank you for your payment!');
</script>
<?php endif; ?>