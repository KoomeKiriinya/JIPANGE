<?php
// Reads the variables sent via POST from our gateway
$sessionId   = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text        = $_POST["text"];
require_once('AfricasTalkingGateway.php');
function printText($response){
    echo $response;
}
//function defition
function sendSMS($phn,$amnt){
    $username   = "sandbox";
    $apikey     = 'e77ec9dfcfdb8f99972804e7689b329f3893c6dae872056ff82aca241cf63112';
    $recipients = "$phn";
    $message    = "Thank you for using our services your total finances are ".$amnt;
    $gateway    = new AfricasTalkingGateway($username, $apikey);
    $gateway->sendMessage($recipients, $message);

}
function initialMenu(){
    $menu = "CON Reply with\n";
    $menu .= "1. Enter finances\n";
    $menu .= "2. View finances\n";
    printText($menu);
}
function chooseExp(){
    $menu = "CON Choose type of expenditure\n";
    $menu .= "1. Food\n";
    $menu .= "2. Fare\n";
    $menu .= "3. Credit\n";
    $menu .= "4. Others\n";
    printText($menu);
}
// returns final reply, connects to database and enters data
function submit($amount,$phno,$type){
    $date = date('Y-m-d H:i:s');
    $db=mysqli_connect("localhost","root","","financerecords") or die(mysql_errno().errror);
    $insert = "INSERT INTO `myfinances`(`Phonenumber`, `Type`, `Amount`, `Date`) VALUES ('$phno','$type','$amount','$date')";
    mysqli_query($db,$insert);
    $menu = "END Thankyou for using our services you have submitted Ksh ".$amount ." expenditure of ".$type." successfuly \n";
    printText($menu);
}
function enterExp(){
    $menu  = "CON Please enter your expenditure\n";
    printText($menu);
}
//what you want to display when the user presses two
function secMenu(){
    $menu = "CON Choose what you want to view\n";
    $menu .= "1. Total expenditure\n";
    $menu .= "2. Respective expenditures\n";
    printText($menu);
}
function dispTotal($phn){
    $db=mysqli_connect("localhost","root","","financerecords") or die(mysql_errno().errror);
    $disp   = "SELECT SUM(Amount) FROM myfinances WHERE `Phonenumber`='$phn' ";
    $result = mysqli_query($db,$disp);
        while ($row = $result->fetch_assoc()) {
            if($row['SUM(Amount)']!= 0) {
                $menu = "END Your total finances will be sent to you shortly via SMS"."\n";
                printText($menu);
                $amount =$row['SUM(Amount)'];
//                $menu = "END Thank you for using our services your total finances thus far are " . $row['SUM(Amount)'] . "\n";
                sendSMS($phn,$amount);

            }else{
                $menu = "END Thank you for using our services. You have no expenditure thus far" . "\n";
                printText($menu);
            }

        }
}
// Selects from database and shows all records partaining a certain value in field of type
function showexpType($mty,$myp)
{
    $db = mysqli_connect("localhost", "root", "", "financerecords") or die(mysql_errno() . errror);
    $select = "SELECT * FROM `myfinances` WHERE `Type`='$mty' AND `Phonenumber`='$myp'";
    $result = mysqli_query($db, $select);

    $i=0;
    $rwc = mysqli_num_rows($result);
    if ($rwc == 0) {
        $menu = "END Thank you for using our services. You have no expenditure on " . $mty . " thus far" . "\n";
        printText($menu);
    }else{
        while ($row = $result->fetch_assoc()) {
            if ($i == 0) {
                    $menu = " END ";
                    printText($menu);
                }
                $i++;
                $response = "You spent " . $row['Amount'] . " worth of " . $mty . " on " . $row['Date'] . "\n";
                printText($response);
            }

        }

}
if($text == ""){
    //function call to initial menu
    initialMenu();
} else if($text == "1") {
    //shows a choice of the first menu
    chooseExp();
    // code below shows second menu
}else if($text == "1*1") {
    enterExp();
}
//code below shows the final reply and calls submit
else if ( strpos($text,"1*1*")!== false){
    $mytxt = explode("*",$text);
    $type1 = "Food";
    submit($mytxt[2],$phoneNumber,$type1);
}else if($text == "1*2"){
    enterExp();
}
else if(strpos($text,"1*2*")!== false){
    $mytxt = explode("*",$text);
    $type1 = "Fare";
    submit($mytxt[2],$phoneNumber,$type1);
}else if($text == "1*3"){
    enterExp();
}
else if(strpos($text,"1*3*")!== false){
    $mytxt = explode("*",$text);
    $type1 = "Credit";
    submit($mytxt[2],$phoneNumber,$type1);
}else if($text == "1*4"){
    enterExp();
}
else if(strpos($text,"1*4*")!== false){
    $mytxt = explode("*",$text);
    $type1 = "Others";
    submit($mytxt[2],$phoneNumber,$type1);
}
else if($text =="2"){
    secMenu();
}
else if($text=="2*1") {
    dispTotal($phoneNumber);
}
else if($text=="2*2"){
    chooseExp();
}
else if($text=="2*2*1"){
    $mtype = "Food";
    showexpType($mtype,$phoneNumber);
}
else if($text=="2*2*2"){
    $mtype = "Fare";
    showexpType($mtype,$phoneNumber);
}
else if($text=="2*2*3"){
    $mtype = "Credit";
    showexpType($mtype,$phoneNumber);
}
else if($text=="2*2*4"){
    $mtype = "Others";
    showexpType($mtype,$phoneNumber);
}
?>