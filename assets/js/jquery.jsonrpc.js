(function($){
    $.jsonRpc = $.jsonRpc || function(method, params, callback, options) {
        method = method || false;
        params = params || [];
        if(typeof params != 'object'){
            params = [params];
        }
        if(!method) return $;
        callback = callback || null;
        options = options||{};
        var ajaxOptions = {
            url : '/v1',
            contentType: 'application/json-rpc; charset=utf-8',
            dataType:  'json',
            type    : 'POST',
            success : options.success||function(m){
                var data = m.result;
                rpc_callback(data, callback);
                return m;
            }
        };

        var data = {
            version: options.version || '2.0',
            method: method || 'listMethods',
            params: params || [],
            id  : options.id || 1
        };
        $.each(data, function(i){ delete options[i] });

        function send() {
            options.data = JSON.stringify(data);
            return $.ajax($.extend(ajaxOptions, options));
        }

        var a = {};
        if (typeof JSON == 'undefined') {
            $.getScript('/js/json2.js', function(){ a =  send() });
        } else {
            a = send();
        }



        return a;
    };

})(jQuery);
 