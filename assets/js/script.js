function print_r( array, return_val ) {
    var output = "", pad_char = " ", pad_val = 4;

    var formatArray = function (obj, cur_depth, pad_val, pad_char) {
        if(cur_depth > 0)
            cur_depth++;

        var base_pad = repeat_char(pad_val*cur_depth, pad_char);
        var thick_pad = repeat_char(pad_val*(cur_depth+1), pad_char);
        var str = "";

        if(typeof obj=='object' || typeof obj=='array' || (obj.length>0 && typeof obj!='string' && typeof obj!='number')) {
            if(!(typeof obj=='object' || typeof obj=='array'))str = '\n'+obj.toString()+'\n';
            str += '[\n';//"Array\n" + base_pad + "(\n";
            for(var key in obj) {
                if(typeof obj[key]=='object' || typeof obj[key]=='array' || (obj.length>0 && typeof obj!='string' && typeof obj!='number')) {
                    str += thick_pad + ""+key+": "+((!(typeof obj=='object' || typeof obj=='array'))?'\n'+obj[key]+'\n':'')+formatArray(obj[key], cur_depth+1, pad_val, pad_char)+'\n';
                } else {
                    str += thick_pad + ""+key+": " + obj[key] + "\n";
                }
            }
            str += base_pad + "]\n";
        } else {
            str = obj.toString();
        };

        return str;
    };

    var repeat_char = function (len, char) {
        var str = "";
        for(var i=0; i < len; i++) { str += char; };
        return str;
        return str;
    };

    output = formatArray(array, 0, pad_val, pad_char);
    return "<pre>" + output + "</pre>";

}

$(function(){
    $('textarea').textcomplete([
        {
            match: /(^|\s)({['"])$/,
            search: function (term, callback) {
                var rpc = {method : "", params : [], id:1};
                callback([JSON.stringify(rpc)]);
            },
            replace: function (word) {
                return ['$1{"method":"', '", "params":[], "id":1}'];
            }
        },{
            match: /(^|\s)({.+)$/,
            search: function (term, callback) {
                term  = term.replace("{", "");
                $.jsonRpc('man', '', function (m) {
                    callback($.map(m, function (word) {
                        return word.indexOf(term) === 0 ? word : null;
                    }));
                })
            },
            replace: function (word) {
                var func = word.replace(/\(.*$/,'');
                var params = word.replace(/^.*\(/,'').replace(')','');
                return ['$1{"method":"'+func, '", "params":['+params+'], "id":1}'];
            }
        },{
            match: /(^|\b)([a-zA-Z].*)?$/,
            search: function (term, callback) {
                //console.log(term);
                $.jsonRpc('man', '', function (m) {
                    callback($.map(m, function (word) {
                        return word.indexOf(term) === 0 ? word : null;
                    }));
                })
            },
            replace: function (word) {
                return word;
            }

        }]);
    function RpcConsole(){
        this.method= '';
        this.params = [];
        this.jsonObject = false;
        this.run = function(){

            this.method = decodeURIComponent(window.location.hash.substr(1));

            if(localStorage.inputJsonRPC != undefined){
                $('#rpc_textarea').val(localStorage.inputJsonRPC);
            }

            this.workSpace();

            $(".rpc_debug").click(function(){
                $(".rpc_debug_panel").toggleClass('hide');
                return false;
            });

            var _this = this;
            $("#rpc_form").on("submit", function(){
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

            $('.rpc_format').on('click', function(){
                var val = $('textarea').val();
                // Разбиваем по строкам
                var lines = val.replace('\r','').split('\n');
                lines = lines.sort();
                var prev = '';
                var out = [];
                for(i in lines){
                    if(prev == '') {
                        prev = lines[i];
                        continue;
                    }
                    var current = lines[i].trim();
                    var part = current.match(/[a-z]+/i);
                    if(part && part.length > 0){
                        part = part[0];
                        //console.log(part, prev, prev.indexOf(part));
                        if(prev.indexOf(part) === -1){
                            out.push('');
                        }
                    }

                    out.push(current);
                    prev = current;
                }

                $('textarea').val(out.join('\n'));

            });
        };

        this.workSpace = function() {
            var  _this = this;
            $('#rpc_textarea').on("click keyup", function(){
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
                    $('#rpc_form').submit();
                }
            });
        };

        this.parseMethod = function(method){
            method = method || this.method;
            if(!method) return false;
            try {
                this.jsonObject = JSON.parse(method);
                this.command = this.jsonObject.method || '';
                this.params = this.jsonObject.params || '';
                window.location.hash = encodeURIComponent(JSON.stringify(this.jsonObject));
                return this;
            } catch (e){
                this.jsonObject = false;
                window.location.hash = encodeURIComponent(method);
            }

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
            localStorage.inputJsonRPC = $('#rpc_textarea').val();
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
            var cmd = '';
            if(this.jsonObject){
                cmd = JSON.stringify(this.jsonObject);
            } else {
                cmd = this.command+"("+this.params.join(",") + ")";
            }
            var intervalId  = setInterval('$("#timeResult").text(((new Date()).getTime() - dateStart)/1000 + "s")', 100);
            var _this = this;
            $rpcInfo = $("<a href='#' title='Справка' style='margin-left: 5px'></a>").addClass("glyphicon glyphicon-question-sign").data('rpcinfo', this.command).click(function(){
                _this.command = 'man';
                _this.params = [$(this).data('rpcinfo')];
                _this.clear();
                _this.send();
                return false;
            });

            $rpcInTab = $("<a  style='margin-left: 5px' href='/jsonrpc/view?command=" + cmd + "' target='_blank' title='Открыть в новом окне'></a>").addClass("glyphicon glyphicon-new-window");


            $rpcReload = $("<a href='#'title='Обновить' style='margin-left: 5px'></a>").addClass("glyphicon glyphicon-transfer").click(function(){
                _this.clear();
                _this.send();
                return false;
            });
            $('#rpc_method').html(cmd).append($rpcInfo).append($rpcReload).append($rpcInTab);
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
                        if(m.responseJSON.error.data){
                            $('#rpc_error').html('<b>' + m.responseJSON.error.message + print_r(m.responseJSON.error.data) + '</b>');
                        } else {
                            $('#rpc_error').html('<b>' + m.responseJSON.error.message + '</b>');
                        }

                    }
                }
            })
            return this;
        }


    }
    rpcConsole = new RpcConsole();
    rpcConsole.run();
});