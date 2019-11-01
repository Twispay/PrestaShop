prestashop-Twispay_Payments
=========================

The official [Twispay Payment Gateway][twispay] extension for Prestashop.

At the time of purchase, after checkout confirmation, the customer will be redirected to the secure Twispay Payment Gateway.

All payments will be processed in a secure PCI DSS compliant environment so you don't have to think about any such compliance requirements in your web shop.

Install
=======

### Automatic
1. Connect to the BackOffice of your PrestaShop shop.
2. Go to the Modules tab.
3. Click on the Add a new module link.
4. Download the archive of the registered module on your computer.
5. In the line of the new module, click on Install.
6. Click on Configure.
7. Select **YES** under **Live Mode**. _(Unless you are testing)_
8. Enter your **Live Site ID**. _(Twispay Live Site ID)_
9. Enter your **Live Private key**. _(Twispay Live Private key)_
10. Save your changes.

### Manually
1. Unzip (decompress) the module archive file.
2. Using your FTP software.
3. Place the folder in your PrestaShop /modules folder.
4. Connect to the BackOffice of your shop.
5. Go to Back Office >> Modules.
6. Locate the new module in the list, scrolling down if necessary.
5. In the line of the new module, click on Install.
7. Click on Configure.
8. Select **YES** under **Live Mode**. _(Unless you are testing)_
9. Enter your **Live Site ID**. _(Twispay Live Site ID)_
10. Enter your **Live Private key**. _(Twispay Live Private key)_
11. Save your changes.

Changelog
=========

= 1.0.1 =
* Updated the way requests are sent to the Twispay server.
* Updated the server response handling to process all the possible server response statuses.
* Added support for refunds.

= 1.0.0 =
* Initial Plugin version

<!-- Other Notes
===========

A functional description of the extension can be found on the [wiki page][doc] -->

[twispay]: http://twispay.com
[marketplace]: https://addons.prestashop.com
[github]: https://github.com/Twispay/PrestaShop
