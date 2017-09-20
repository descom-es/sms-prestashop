<h1>DescomSMS</h1>

<div class="panel panel-default">
    <div class="panel-heading">Información general</div>
    <div class="panel-body" style="font-size: 1.2em;">
        <p><strong>Usuario:</strong> {$user}</p>
        <p><strong>Créditos disponibles:</strong> {$credits}</p>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Selección de remitente</div>
    <div class="panel-body">
      <p>Si necesitas añadir nuevos remitentes puedes ponerte en <a href="https://www.descom.es/contacto" target="_blank">contacto</a> con nosotros para darlos de alta.</p><br>
        <form id="formSender" action="{$link->getAdminLink('AdminDescomsms')|escape:'htmlall':'utf-8'}" method="post">
            <div class="form-group">
                <label for="selectSender">Remitente</label>
                <select id="selectSender" name="selectSender" class="form-control">
                    {foreach from=$senders item=sender_item}
                        <option {if $sender==$sender_item}selected{/if}>{$sender_item}</option>
                    {/foreach}
                </select>
            </div>
            <button name="submit_sender" type="submit" class="btn btn-default">Guardar</button>
        </form>
    </div>
</div>

<h2>Envío SMS a clientes</h2>
<div class="panel panel-default">
    <div class="panel-heading">Aviso pago pedido realizado</div>
    <div class="panel-body">
        <form action="{$link->getAdminLink('AdminDescomsms')|escape:'htmlall':'utf-8'}" method="post">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input id="checkPay" name="checkPay" type="checkbox" {if $check_order_pay=='on'}checked{/if}>Enviar SMS al cliente cuando se formalice el pago de un pedido.
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="textareaPago">Texto SMS</label>
                <textarea id="textareaPay" name="textareaPay" class="form-control" rows="3">{$text_order_pay}</textarea>
                <br>
                <pre>Variables admitidas: [shop_name] => Nombre de la tienda, [order_id] => Identificador del pedido</pre>
            </div>
            <button name="submit_pay" type="submit" class="btn btn-default">Guardar</button>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Aviso envío pedido realizado</div>
    <div class="panel-body">
        <form action="{$link->getAdminLink('AdminDescomsms')|escape:'htmlall':'utf-8'}" method="post">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input id="checkSend" name="checkSend" type="checkbox" {if $check_order_send=='on'}checked{/if}>Enviar SMS al cliente cuando se realice el envío de un pedido.
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="textareaEnvio">Texto SMS</label>
                <textarea id="textareaSend" name="textareaSend" class="form-control" rows="3">{$text_order_send}</textarea>
                <br>
                <pre>Variables admitidas: [shop_name] => Nombre de la tienda, [order_id] => Identificador del pedido</pre>
            </div>
            <button name="submit_send" type="submit" class="btn btn-default">Guardar</button>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Aviso stock producto repuesto</div>
    <div class="panel-body">
      <p><strong>Importante:</strong> Requiere el módulo <a href="https://github.com/PrestaShop/mailalerts" target="_blank">mailalerts</a> instalado y activado, así como que el cliente esté registrado y haya facilitado un número móvil.</p><br>
        <form action="{$link->getAdminLink('AdminDescomsms')|escape:'htmlall':'utf-8'}" method="post">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input id="checkStock" name="checkStock" type="checkbox" {if $check_product_stock=='on'}checked{/if}>Enviar SMS a los clientes que han solicitado que se les avise cuando se reponga el stock de un producto determinado.
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="textareaStock">Texto SMS</label>
                <textarea id="textareaStock" name="textareaStock" class="form-control" rows="3">{$text_product_stock}</textarea>
                <br>
                <pre>Variables admitidas: [shop_name] => Nombre de la tienda, [product_name] => Nombre del producto, [product_stock] => Stock del producto</pre>
            </div>
            <button name="submit_stock" type="submit" class="btn btn-default">Guardar</button>
        </form>
    </div>
</div>


<div class="clear"></div>
