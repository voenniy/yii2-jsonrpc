<script type="text/javascript">
$(function(){
    function RpcConsole(){
        this.method= '';
        this.params = [];
        this.run = function(){

            this.method = decodeURIComponent(window.location.hash.substr(1));

            if(localStorage.inputJsonRPC != undefined){
                $('#textarea').val(localStorage.inputJsonRPC);
            }

            this.workSpace();

            $(".rpc_debug").click(function(){
                $(".rpc_debug_panel").toggleClass('hide');
                return false;
            });

            var _this = this;
            $("form").on("submit", function(){
                 _this.saveHistory();
                 _this.clear();
                 if(_this.parseMethod()){
                 _this.send();
                 }
                 return false;
            }).submit();

            $('.rpc_help').on('click', function(){
                _this.command = 'man';
                _this.params = [];
                _this.clear();
                _this.send();
                return false;
            })
        };

        this.workSpace = function() {
            var  _this = this;
            $('#textarea').on("click keyup", function(){
                function caretPosition(el){
                    if(el.selectionStart){
                        return 1+el.selectionStart;
                    }
                    if (document.selection) { // IE
                        var sel = document.selection.createRange();
                        var clone = sel.duplicate();
                        sel.collapse(true);
                        clone.moveToElementText(el);
                        clone.setEndPoint('EndToEnd', sel);
                        return 1+clone.text.length;
                    }

                    return 1;
                }

                var pos = caretPosition($(this)[0]);
                var val = $(this).val();
                // Разбиваем по строкам
                var lines = val.replace('\r','').split('\n');
                // Ищем в какой мы строке
                var sumPad = 0;

                for(i in lines){
                    sumPad += lines[i].length+1;
                    if(sumPad >= pos){
                        break;
                    }
                }
                _this.method =  lines[i].trim();
                //_this.clear();
                //_this.parseMethod();
                //_this.send();

            }).on('keypress', function(e){
                if(event.keyCode==10||(event.ctrlKey && event.keyCode==13)){
                    $(this).parents('form:eq(0)').submit();
                }
            });
        };

        this.parseMethod = function(method){
            method = method || this.method;
            if(!method) return false;
            window.location.hash = encodeURIComponent(method);
            var params = method.match(/\((.*)\)/);
            if(params && params[1]){
                params = params[1].split(",");
                this.params = $.map(params, function(v, i){ v= v.trim(); v = v.replace(/^["']/,'').replace(/["']$/,''); return v});
            } else {
                this.params = [];
            }

            var command = method.match(/(.*)\(/);
            if(command && command[1]){
                this.command = command[1];
            } else {
                this.command = '';
            }
            return this;
        };

        this.saveHistory = function(){
            localStorage.inputJsonRPC = $('#textarea').val();
            return this;
        };

        this.clear = function(){
            $('.clearInfo').text(''); $('.rpc_error').hide();
            return this;
        };

        this.send = function(){
            if(!this.command){
                return false;
            }
            dateStart = (new Date()).getTime();
            var intervalId  = setInterval('$("#timeResult").text(((new Date()).getTime() - dateStart)/1000 + "s")', 100);
            var _this = this;
            $rpcInfo = $("<a href='#' title='Справка' style='margin-left: 5px'></a>").addClass("glyphicon glyphicon-question-sign").data('rpcinfo', this.command).click(function(){
                _this.command = 'man';
                _this.params = [$(this).data('rpcinfo')];
                _this.clear();
                _this.send();
                return false;
            });
            $rpcInTab = $("<a  style='margin-left: 5px' href='/jsonrpc/view?command="+this.command+"("+this.params.join(",")+")' target='_blank' title='Открыть в новом окне'></a>").addClass("glyphicon glyphicon-new-window");


            $rpcReload = $("<a href='#'title='Обновить' style='margin-left: 5px'></a>").addClass("glyphicon glyphicon-transfer").click(function(){
                _this.clear();
                _this.send();
                return false;
            });
            $('#rpc_method').html(this.command + '(' + this.params.join(',') + ')').append($rpcInfo).append($rpcReload).append($rpcInTab);
            $.jsonRpc(this.command, this.params, function(m){
                $("#rpc_result").html(print_r(m));
            }, {
                beforeSend : function(x, o){
                    $('#rpc_request').html(o.data);
                },
                complete : function(m){
                    clearInterval(intervalId);
                    $("#timeResult").text(((new Date()).getTime() - dateStart)/1000 + "s");
                    $("#rpc_all").html(print_r(m['responseText']));
                    if(m.responseJSON.error){
                        $('.rpc_error').show();
                        $('#rpc_error').html('<b>' + m.responseJSON.error.message + '</b>');
                    }
                }
            })
            return this;
        }


    }
    rpcConsole = new RpcConsole();
    rpcConsole.run();
})

</script>

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
