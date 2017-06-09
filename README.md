# Wilddog SMS SDK (野狗短信SDK)

### Quick Sample Usage

You need to introduce all the packages

```php
/**
 * initialization
 * new luoyy\WilddogSmsSdk\WilddogSms(); OR luoyy\WilddogSmsSdk\WilddogSms::send() ....
 * If you initialize the parameters, you can ignore the parameters when calling subsequent methods
 */
$WilddogSms = new luoyy\WilddogSmsSdk\WilddogSms([$mobile null,[$templateId = null,[$params = []]]]);\


/**
 * Send a verification code message
 * $WilddogSms->sendcode([$mobile = null,[$templateId = null,[$params = []]]]);
 * $mobile +> The value of $mobile must be a string, $templateId => The value of $templateId must be predefined, If the variable is the default template ID, you can ignore third parameters. $params => The characters in the template that need to be replaced.
 * The specific content to see official website address: <https://docs.wilddog.com/sms/api/sendcode.html>
 */
$return = $WilddogSms->sendcode('13800831500','100000', ['123']);
var_dump($return);
$return = luoyy\WilddogSmsSdk\WilddogSms::sendcode('13800831500','100000', ['123']);
var_dump($return);

/**
 * Send a notification text message
 * $WilddogSms->send([$mobiles = null,[$templateId = null,[$params = []]]]);
 * $mobiles +> The value of $mobile must be a array, $templateId => The value of $templateId must be predefined. $params => The characters in the template that need to be replaced.
 * The specific content to see official website address: <https://docs.wilddog.com/sms/api/send.html>
 */
$return = $WilddogSms->send(['1380083150','13800138006'],'100000', ['456789']);
var_dump($return);
$return = luoyy\WilddogSmsSdk\WilddogSms::send(['1380083150','13800138006'],'100000', ['456789']);
var_dump($return);

/**
 * Verification code
 * $WilddogSms->checkCode([$code =  null, [$mobile = null]]);
 * $code => The value of the variable $code needs to be detected only when the default template is used, $mobile => The default use of the instantiation of the call, if it is called the notification class SMS need to pass the parameters
 * The verification verification code interface can not verify the interface sent by the custom authentication code template.
 * The specific content to see official website address: <https://docs.wilddog.com/sms/api/checkcode.html>
 */
$return = $WilddogSms->checkCode('566034', '1380083150');
var_dump($return);
$return = luoyy\WilddogSmsSdk\WilddogSms::checkCode('566034', '1380083150');
var_dump($return);

/**
 * Query send status
 * $WilddogSms->getStatus([$rrid = null]);
 * $rrid => When this parameter is not present, the value obtained after the message is sent is used
 * The specific content to see official website address: <https://docs.wilddog.com/sms/api/status.html>
 */
$return = $WilddogSms->getStatus('12222**************');
var_dump($return);
$return1 = luoyy\WilddogSmsSdk\WilddogSms::getStatus('12222**************');
var_dump($return);

/**
 * Check the account balance
 */
$return = $WilddogSms->getBalance();
var_dump($return);
$return = luoyy\WilddogSmsSdk\WilddogSms::getBalance();
var_dump($return);
```
