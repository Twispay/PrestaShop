{**
* @author   Twistpay
* @version  1.0.1
*}

<div id="twispay_order_info" class="panel">
  <div class="panel-heading"><i class="icon-credit-card"></i> {l s='Twispay' mod='twispay'}</div>
  <div class="tab-content">
    <table class="table">
      <tr>
        <td>
          <strong>{l s='Payment status:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.status|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Payment amount:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.amount|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Payment currency:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.currency|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Twispay orderId:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.orderId|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Twispay transactionId:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.transactionId|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Twispay customerId:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.customerId|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Twispay transactionKind:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.transactionKind|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Twispay cardId:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.cardId|escape:'html':'utf-8'}
        </td>
      </tr>
      <tr>
        <td>
          <strong>{l s='Transaction date:' mod='twispay'}</strong>
        </td>
        <td>
          {$data.date|escape:'html':'utf-8'}
        </td>
      </tr>
    </table>
  </div>
</div>
