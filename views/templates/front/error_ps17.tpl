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

{extends file='page.tpl'}
{block name="page_content"}
	<div>
		<h3>{l s='An error occurred' mod='twispay'}:</h3>
		<ul class="alert alert-danger">
			<li>{l s='The payment could not be processed. You should have received more informations on the previous page' mod='twispay'}</li>
		</ul>
	</div>
{/block}