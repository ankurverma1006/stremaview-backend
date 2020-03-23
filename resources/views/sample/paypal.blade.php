
<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
    <input name = "cmd" value = "_cart" type = "hidden">
    <input name = "upload" value = "1" type = "hidden">
    <input name = "no_note" value = "0" type = "hidden">
    <input name = "bn" value = "PP-BuyNowBF" type = "hidden">
    <input name = "tax" value = "0" type = "hidden">
    <input name = "rm" value = "2" type = "hidden">
 
    <input name = "business" value = "jacksonshcg@gmail.com" type = "hidden">
    <input name = "handling_cart" value = "0" type = "hidden">
    <input name = "currency_code" value = "USD" type = "hidden">
    <input name = "lc" value = "GB" type = "hidden">
    <input name = "return" value = "http://localhost:8000/user/payment/status" type = "hidden">
    <input name = "cbt" value = "Return to My Site" type = "hidden">
    <input name = "cancel_return" value = "http://localhost:8000/user/payment/status" type = "hidden">
    <input name = "custom" value = "" type = "hidden">
 
    <div id = "item_1" class = "itemwrap">
        <input name = "item_name_1" value = "Admin Payout - Moderator" type = "hidden">
        <input name = "quantity_1" value = "1" type = "hidden">
        <input name = "amount_1" value = "10" type = "hidden">
        <input name = "shipping_1" value = "0" type = "hidden">
    </div>

     <input type="image"
    src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" alt="Checkout">

    <img alt="" src="https://paypalobjects.com/en_US/i/scr/pixel.gif"
    width="1" height="1">

</form>