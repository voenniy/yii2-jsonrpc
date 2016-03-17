<?php
/**
 * @var \yii\web\View $this
 */

?>



<form method="post">
<div class="row">
    <div class="col-lg-10">
        <textarea class="form-control" rows="10" name="test" id="textarea" spellcheck="false"></textarea>
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

    </div>
</div>
<div class="panel panel-danger rpc_error" style="display: none">
    <div class="panel-heading">Ошибка!</div>
    <div class="panel-body clearInfo" id="rpc_error">
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">Результат для: <code class="clearInfo" id="rpc_method"></code></div>
    <div class="panel-body clearInfo" id="rpc_result"></div>
</div>

<a href="#" class="rpc_debug">Отладка</a>
<div class="panel panel-warning rpc_debug_panel hide">
    <div class="panel-heading">Отладка</div>
    <div class="panel-body clearInfo" id="rpc_all"></div>
    <div class="panel-footer">Запрос: <kbd class="clearInfo" id="rpc_request"></kbd> </div>
</div>
