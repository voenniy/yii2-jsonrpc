    <?php
/**
 * @var \yii\web\View $this
 */

?>

<h1>JsonRPC console</h1>

<form method="post" id="rpc_form">
<div class="row">
    <div class="col-lg-10">
        <textarea class="form-control" rows="10" name="test" id="rpc_textarea" spellcheck="false"></textarea>
    </div>
</div>
<div class="row">
    <div class="col-lg-10">
        <a href="#" class="rpc_help">Справка</a>
        <input type="submit" class="btn btn-info btn-flat" value="Go" />
    </div>
</div>

</form>

<hr>
<div>Время исполнения: <span class="badge clearInfo" id="timeResult"></span></div>
<hr>
<div class="row">
    <div class="col-lg-10">
        <div class="panel panel-danger rpc_error" style="display: none">
            <div class="panel-heading">Ошибка!</div>
            <div class="panel-body clearInfo" id="rpc_error">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-10">
        <div class="panel panel-primary">
            <div class="panel-heading">Результат для: <code class="clearInfo" id="rpc_method"></code></div>
            <div class="panel-body clearInfo" id="rpc_result"></div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-10">
        <a href="#" class="rpc_debug">Отладка</a>
        <div class="panel panel-warning rpc_debug_panel hide">
            <div class="panel-heading">Отладка</div>
            <div class="panel-body clearInfo" id="rpc_all"></div>
            <div class="panel-footer">Запрос: <kbd class="clearInfo" id="rpc_request"></kbd> </div>
        </div>
    </div>
</div>

