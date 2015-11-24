/**
 * Вызов js функций в стиле rpc
 *
 * @param data Передается объект, либо массив объектов вида {method:"имя функции или метода объекта", params: [a1,"a2",a3]} Будет вызов имя_функции(a1,"a2",a3)
 * Либо вместо объекта можно передать любое значение, тогда оно передастся в виде параметра в callback, если callback указан
 *
 * @param callback функция, куда будут переданы все не rpc параметры
 * @returns {*}
 */
function rpc_callback(data, callback){
    var oData = data, callbackData=[];

    if(typeof data == 'string'){
        try {
            oData = JSON.parse(data);
        }catch (e){

        }

    }
    if (oData != null && typeof oData == 'object') {
        if(oData.length === undefined) {
            oData = [oData];
        }
        for (var i = 0; i <oData.length; i++) {
            var cData = oData[i];
            if (cData && cData.method !== undefined) {
                var func = cData.method + "(";
                if (cData.params !== undefined) {
                    if (typeof cData.params != 'object') {
                        cData.params = [cData.params];
                    }
                    var params = "";
                    for (var k = 0; k < cData.params.length; k++) {
                        params += typeof cData.params[k] == 'number' ? cData.params[k] : "'" + cData.params[k] + "'";
                        params += ',';
                    }
                    params = params.replace(new RegExp(',$'), "");
                    func += params;
                }
                func += ")";
                try {
                    eval(func);
                } catch(e) {

                }

            } else{
                callbackData.push(cData);
            }
        }
        if(typeof callback == 'function' && callbackData){
            callback(callbackData);
        }
        return true;
    } else if(typeof callback == 'function'){
        callback(data);
        return true;
    } {
        return false;
    }
};