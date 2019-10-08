{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
*  @author    Active Design <office@activedesign.ro>
*  @copyright 2017 Active Design
*  @license   LICENSE.txt
*}

<div class="row">
	<div class="col-xs-12">
		<p class="payment_module" id="twispay_payment_button">
			<a href="{$link->getModuleLink('twispay', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay by credit or debit card' mod='twispay'}">
				{l s='Pay by credit or debit card' mod='twispay'}
				<img src="{$logos_folder|escape:'htmlall':'UTF-8'}/visa.png" alt="{l s='Pay by credit or debit card' mod='twispay'}" height="17px" />
				<img src="{$logos_folder|escape:'htmlall':'UTF-8'}/mastercard.png" alt="{l s='Pay by credit or debit card' mod='twispay'}" height="17px" />
				<span class="twispay_float_right">
					<img src="{$logos_folder|escape:'htmlall':'UTF-8'}/twispay.png" alt="{l s='Pay by credit or debit card' mod='twispay'}" height="17px" />
					<span class="twispay_secure_div">
						<img src="{$logos_folder|escape:'quotes'}secure.png" height="15px"/> <span>{l s='Secure' mod='twispay'}</span>
					</span>
					<span class="twispay_pay_now_button">
						{l s='Pay now' mod='twispay'}
					</span>
				</span>
			</a>
		</p>
	</div>
</div>
