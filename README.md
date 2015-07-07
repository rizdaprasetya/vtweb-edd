Veritrans Easy Digital Downloads Payment Gateway
=====================================

A WordPress plugin that let your Easy-Digital-Downloads store integrated with Veritrans payment gateway.

### Description

Veritrans payment gateway is an online payment gateway that is highly concerned with customer experience (UX). They strive to make payments simple for both the merchant and customers. With this plugin you can make your Easy Digital Downloads store integrated with Veritrans payment gateway.

Payment Method Feature:

- VT Web

### Installation

#### Minimum Requirements

* WordPress 3.9.1 or greater
* Easy Digital Downloads 2.0 or greater
* PHP version 5.4 or greater
* MySQL version 5.0 or greater

#### Manual Instalation

The manual installation method involves downloading our feature-rich plugin and uploading it to your webserver via your favourite FTP application..

1. Download the plugin file to your computer and unzip it
2. Using an FTP program, or your hosting control panel, upload the unzipped plugin folder to your WordPress installation `wp-content/plugins/` directory.
3. Activate **Easy Digital Downloads - Veritrans Gateway** plugin from Plugin menu in your WordPress admin page.
4. Insert `http://[YourWeb].com/?edd-listener=veritrans` link as the Payment Notification URL in your MAP configuration.
5. Insert `http://[YourWeb].com` (or your desired URL) link as Finish/Unfinish/Error Redirect URL in your MAP configuration.

#### Plugin COnfiguration
In order to configure Veritrans plug-in:

1. Access your WordPress admin page.
2. Go to **Downloads - Settings** menu in the WordPress admin page, click **Payment Gateways** tab.
3. In **Payment Gateways** option, scroll down to **Veritrans Gateway Settings**, then
4. Input required fields below. (alternatively you may refer to image below) 
  * **Checkout Label** : /<text that will be shown when customers pick payment options/>
  * **Production API Key**: /<your production server key/> (leave blank if you dont have production account)
  * **Sandbox API Key**: /<your sandbox server key/>
  * **Enable 3D Secure** : yes
5. In **Veritrans Payment Channel** group setting, enable each payment channel you wish to accept.
6. Optionally: scroll up and you may configure **Accepted Payment Method Icons** you wish to be shown.
7. Click **Save Changes**.

#### Get help

* [Veritrans sandbox login](https://my.sandbox.veritrans.co.id/)
* [Veritrans sandbox registration](https://my.sandbox.veritrans.co.id/register)
* [Veritrans registration](https://my.veritrans.co.id/register)
* [Veritrans documentation](http://docs.veritrans.co.id)
* [Veritrans Woocommerce Documentation](http://docs.veritrans.co.id/vtweb/integration_woocommerce.html)
* Technical support [support@veritrans.co.id](mailto:support@veritrans.co.id)
