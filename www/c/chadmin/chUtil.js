
var chUtil = {};
chUtil.ajax = function(post, callback) {
    post.version_id = Charts.versionId;
    var params = chUtil.toPost(post);

    if (window.XMLHttpRequest) var ajax = new XMLHttpRequest();
    else if (window.ActiveXObject) var ajax = new ActiveXObject("Microsoft.XMLHTTP");

    ajax.onreadystatechange = function() {
        chUtil.ajaxRsc(ajax, callback);
    }

    ajax.open('POST', 'chserv.php', true);
    ajax.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    if (document.cookie) ajax.setRequestHeader('Cookie', document.cookie);
    ajax.setRequestHeader('Content-length', params.length);
    ajax.setRequestHeader('Connection', 'close');
    ajax.send(params);
}

chUtil.ajaxRsc = function(ajax, callback) {
    if (ajax.readyState == 4) {
        if (typeof(callback) !== 'undefined') {
            if (ajax.status == 200) callback(ajax);
            else callback(false);
        }
    }
}

chUtil.toPost = function(obj, path, new_path) {
    if (typeof(path) == 'undefined') var path = [];
    if (typeof(new_path) != 'undefined') path.push(new_path);
    var post_str = [];
    if (typeof(obj) == 'array' || typeof(obj) == 'object')
        for (var n in obj) post_str.push(chUtil.toPost(obj[n], path, n));
    else {
        var base = path.shift();
        post_str.push(base + (path.length > 0 ? '[' + path.join('][') + ']' : '') + '=' + encodeURIComponent(obj).replace(/&/g, '%26'));
        path.unshift(base);
    }
    path.pop();
    return post_str.join('&');
}
