{**
* @author   Twispay
* @version  1.4.1
*}

{if $action && $inputs}
  <form accept-charset="UTF-8" id="twispay_payment_form" action="{$action|escape:'quotes'}" method="POST">
    <input type="hidden" name="jsonRequest" value="{$inputs['jsonRequest']|escape:'htmlall':'UTF-8'}" />
    <input type="hidden" name="checksum" value="{$inputs['checksum']|escape:'htmlall':'UTF-8'}" />

  </form>

  {if $isIframe}
    <script type="text/javascript">
      var twispayButtonsSelectors = [
        '.ps-shown-by-js button'
	    ];
      var twispayFormSelectors = [
        '#twispay_payment_form'
      ];
    </script>
    <script type="text/javascript" src="{$iframeScriptUrl}"></script>
  {/if}
{/if}

