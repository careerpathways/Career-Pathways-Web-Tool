/*
Copyright (c) 2010, Yahoo! Inc. All rights reserved.
Code licensed under the BSD License:
http://developer.yahoo.net/yui/license.txt
version: 2.4.1r2
*/
/**
 * The selector module provides helper methods allowing CSS3 Selectors to be used with DOM elements.
 * @module selector
 * @title Selector Utility
 * @namespace YAHOO.util
 * @requires yahoo, dom
 */

(function() {
/**
 * Provides helper methods for collecting and filtering DOM elements.
 * @namespace YAHOO.util
 * @class Selector
 * @static
 */
var Selector = function() {};

var Y = YAHOO.util;

var X = {
    IDENT: '-?[_a-z]+[-\\w]*',
    BEGIN: '^',
    END: '$',
    OR: '|',
    SP: '\\s+'
};

var CHARS = {
    SIMPLE: '-+\\w_\\[\\]\\.\\|\\*\\\'\\(\\)#:^~=$!"',
    COMBINATORS: ',>+~'
};

X.CAPTURE_IDENT = '(' + X.IDENT + ')';
X.BEGIN_SPACE = '(?:' + X.BEGIN + X.OR + X.SP +')';
X.END_SPACE = '(?:' + X.SP + X.OR + X.END + ')';
X.SELECTOR = '^(' + X.CAPTURE_IDENT + '?([' + CHARS.SIMPLE + ']*)?\\s*([' + CHARS.COMBINATORS + ']?)?\\s*).*$';
X.SIMPLE = '(' + X.CAPTURE_IDENT + '?([' + CHARS.SIMPLE + ']*)*)?';
X.ATTRIBUTES = '\\[([a-z]+\\w*)+([~\\|\\^\\$\\*!=]=?)?"?([^\\]"]*)"?\\]';
X.CAPTURE_ATTRIBUTES = '(' + X.ATTRIBUTES  + ')';
X.PSEUDO = ':' + X.CAPTURE_IDENT + '(?:\\({1}' + X.SIMPLE + '\\){1})*';
X.NTH_CHILD = '^(?:(\\d*)(n){1}|(odd|even)$)*([-+]?\\d*)$';
X.URL_ATTR = '^href|url$';
Selector.prototype = {
    /**
     * Default document for use queries 
     * @property document
     * @type object
     * @default window.document
     */
    document: window.document,
    /**
     * Mapping of attributes to aliases, normally to work around HTMLAttributes
     * that conflict with JS reserved words.
     * @property attrAliases
     * @type object
     */
    attrAliases: {
        'for': 'htmlFor',
        'class': 'className'
    },

    /**
     * Mapping of shorthand tokens to corresponding attribute selector 
     * @property shorthand
     * @type object
     */
    shorthand: {
        //'(?:(?:[^\\)\\]\\s*>+~,]+)(?:-?[_a-z]+[-\\w]))+#(-?[_a-z]+[-\\w]*)': '[id=$1]',
        '\\#(-?[_a-z]+[-\\w]*)': '[id=$1]',
        '\\.(-?[_a-z]+[-\\w]*)': '[className~=$1]'
    },

    /**
     * List of operators and corresponding boolean functions. 
     * These functions are passed the attribute and the current node's value of the attribute.
     * @property operators
     * @type object
     */
    operators: {
        '=': function(attr, val) { return attr === val; }, // Equality
        '!=': function(attr, val) { return attr !== val; }, // Inequality
        '~=': function(attr, val) { // Match one of space seperated words 
            var str = X.BEGIN_SPACE + val + X.END_SPACE;
            regexCache[str] = regexCache[str] || new RegExp(str); // skip getRegExp call for perf boost

            //return getRegExp(X.BEGIN_SPACE + val + X.END_SPACE).test(attr);
            return regexCache[str].test(attr);
        },
        '|=': function(attr, val) { return getRegExp(X.BEGIN + val + '[-]?').test(attr); }, // Match start with value followed by optional hyphen
        '^=': function(attr, val) { return attr.indexOf(val) === 0; }, // Match starts with value
        '$=': function(attr, val) { return attr.lastIndexOf(val) === attr.length - val.length; }, // Match ends with value
        '*=': function(attr, val) { return attr.indexOf(val) > -1; }, // Match contains value as substring 
        '': function(attr, val) { return attr; } // Just test for existence of attribute
    },

    /**
     * List of pseudo-classes and corresponding boolean functions. 
     * These functions are called with the current node, and any value that was parsed with the pseudo regex.
     * @property pseudos
     * @type object
     */
    pseudos: {
        'root': function(node) {
            return node === node.ownerDocument.documentElement;
        },

        'nth-child': function(node, val) {
            return getNth(node, val);
        },

        'nth-last-child': function(node, val) {
            return getNth(node, val, null, true);
        },

        'nth-of-type': function(node, val) {
            return getNth(node, val, node.tagName);
        },
         
        'nth-last-of-type': function(node, val) {
            return getNth(node, val, node.tagName, true);
        },
         
        'first-child': function(node) {
            return getChildren(node.parentNode)[0] === node;
        },

        'last-child': function(node) {
            var children = getChildren(node.parentNode);
            return children[children.length - 1] === node;
        },

        'first-of-type': function(node, val) {
            return getChildren(node.parentNode, node.tagName.toLowerCase())[0];
        },
         
        'last-of-type': function(node, val) {
            var children = getChildren(node.parentNode, node.tagName.toLowerCase());
            return children[children.length - 1];
        },
         
        'only-child': function(node) {
            var children = getChildren(node.parentNode);
            return children.length === 1 && children[0] === node;
        },

        'only-of-type': function(node) {
            return getChildren(node.parentNode, node.tagName.toLowerCase()).length === 1;
        },

        'empty': function(node) {
            return node.childNodes.length === 0;
        },

        'not': function(node, simple) {
            return !Selector.test(node, simple);
        },

        'contains': function(node, str) {
            return node.innerHTML.indexOf(str) > -1;
        },
        'checked': function(node) {
            return node.checked === true;
        }
    },

    /**
     * Test if the supplied node matches the supplied selector.
     * @method test
     *
     * @param {HTMLElement | String} node An id or node reference to the HTMLElement being tested.
     * @param {string} selector The CSS Selector to test the node against.
     * @return{boolean} Whether or not the node matches the selector.
     * @static
    
     */
    test: function(node, selector) {
        node = Selector.document.getElementById(node) || node;
        var groups = selector.split(',');
        if (groups.length) {
            for (var i = 0, len = groups.length; i < len; ++i) {
                if ( rTestNode(node, groups[i]) ) { // passes if ANY group matches
                    return true;
                }
            }
            return false;
        }
        return rTestNode(node, selector);
    },

    /**
     * Filters a set of nodes based on a given CSS selector. 
     * @method filter
     *
     * @param {array}  A set of nodes/ids to filter. 
     * @param {string} selector The selector used to test each node.
     * @return{array} An array of nodes from the supplied array that match the given selector.
     * @static
     */
    filter: function(arr, selector) {
        if (!arr || !selector) {
            YAHOO.log('filter: invalid input, returning array as is', 'warn', 'Selector');
        }
        var node,
            nodes = arr,
            result = [],
            tokens = tokenize(selector);

        if (!nodes.item) { // if not HTMLCollection, handle arrays of ids and/or nodes
            YAHOO.log('filter: scanning input for HTMLElements/IDs', 'info', 'Selector');
            for (var i = 0, len = arr.length; i < len; ++i) {
                if (!arr[i].tagName) { // tagName limits to HTMLElements 
                    node = Selector.document.getElementByid(arr[i]);
                    if (node) { // skip IDs that return null 
                        nodes[nodes.length] = node;
                    } else {
                        YAHOO.log('filter: skipping invalid node', 'warn', 'Selector');
                    }
                }
            }
        }
        result = rFilter(nodes, tokenize(selector)[0]);
        clearParentCache();
        YAHOO.log('filter: returning:' + result.length, 'info', 'Selector');
        return result;
    },

    /**
     * Retrieves a set of nodes based on a given CSS selector. 
     * @method query
     *
     * @param {string} selector The CSS Selector to test the node against.
     * @param {HTMLElement | String} root optional An id or HTMLElement to start the query from. Defaults to Selector.document.
     * @param {Boolean} firstOnly optional Whether or not to return only the first match.
     * @return {Array} An array of nodes that match the given selector.
     * @static
     */
    query: function(selector, root, firstOnly) {
        var result = query(selector, root, firstOnly);
        YAHOO.log('query: returning ' + result.length + ' nodes', 'info', 'Selector');
        return result;
    }
};

var query = function(selector, root, firstOnly, deDupe) {
    if (!selector) {
        return []; // no nodes for you
    }
    var result = [];
    var groups = selector.split(',');

    if (groups.length > 1) {
        for (var i = 0, len = groups.length; i < len; ++i) {
            result = result.concat( arguments.callee(groups[i], root, firstOnly, true) ); 
        }
        clearFoundCache();
        return result;
    }

    if (root && !root.tagName) {
        root = Selector.document.getElementById(root);
        if (!root) {
            YAHOO.log('invalid root node provided', 'warn', 'Selector');
            return [];
        }
    }

    root = root || Selector.document;
    var tokens = tokenize(selector);
    var idToken = tokens[getIdTokenIndex(tokens)],
        nodes = [],
        node,
        id,
        token = tokens.pop();
        
    if (idToken) {
        id = getId(idToken.attributes);
    }
    // if no root alternate root is specified use id shortcut
    if (id) {
        if (id === token.id) { // only one target
            nodes = [Selector.document.getElementById(id)] || root;
        } else { // reset root to id node if passes
            node = Selector.document.getElementById(id);
            if (root === Selector.document || contains(node, root)) {
                if ( node && rTestNode(node, null, idToken) ) {
                    root = node; // start from here
                }
            } else {
                return [];
            }
        }
    }

    if (root && !nodes.length) {
        nodes = root.getElementsByTagName(token.tag);
    }

    if (nodes.length) {
        result = rFilter(nodes, token, firstOnly, deDupe); 
    }
    clearParentCache();
    return result;
};

var contains = function() {
    if (document.documentElement.contains && !YAHOO.env.ua.webkit < 420)  { // IE & Opera, Safari < 3 contains is broken
        return function(needle, haystack) {
            return haystack.contains(needle);
        };
    } else if ( document.documentElement.compareDocumentPosition ) { // gecko
        return function(needle, haystack) {
            return !!(haystack.compareDocumentPosition(needle) & 16);
        };
    } else  { // Safari < 3
        return function(needle, haystack) {
            var parent = needle.parentNode;
            while (parent) {
                if (needle === parent) {
                    return true;
                }
                parent = parent.parentNode;
            } 
            return false;
        }; 
    }
}();

var rFilter = function(nodes, token, firstOnly, deDupe) {
    var result = [],
        node;

    for (var i = 0, len = nodes.length; i < len; ++i) {
        node = nodes[i];
        if ( !rTestNode(node, null, token) || (deDupe && node._found) ) {
            continue;
        }
        if (firstOnly) {
            return [node];
        }
        if (deDupe) {
            node._found = true;
            foundCache[foundCache.length] = node;
        }

        result[result.length] = node;
    }

    return result;
};

var rTestNode = function(node, selector, token) {
    token = token || tokenize(selector).pop();

    if (!node || node._found || (token.tag != '*' && node.tagName.toLowerCase() != token.tag)) {
        return false; // tag match failed
    } 

    var ops = Selector.operators,
        ps = Selector.pseudos,
        attributes = token.attributes,
        attr,
        pseudos = token.pseudos,
        prev = token.previous;

    for (var i = 0, len = attributes.length; i < len; ++i) {
        attr = (getRegExp(X.URL_ATTR).test(attributes[i][0])) ?
                node.getAttribute(attributes[i][0], 2) : // preserve relative urls
                node[attributes[i][0]];

        if (ops[attributes[i][1]] && !ops[attributes[i][1]](attr, attributes[i][2])) {
            return false;
        }
    }
    for (var i = 0, len = pseudos.length; i < len; ++i) {
        if (ps[pseudos[i][0]] &&
                !ps[pseudos[i][0]](node, pseudos[i][1])) {
            return false;
        }
    }

    if (prev) {
        if (prev.combinator !== ',') {
            return combinators[prev.combinator](node, token);
        }
    }
    return true;

};

var foundCache = [];
var parentCache = [];
var regexCache = {};

var clearFoundCache = function() {
    YAHOO.log('getBySelector: clearing found cache of ' + foundCache.length + ' elements');
    for (var i = 0, len = foundCache.length; i < len; ++i) {
        try { // IE no like delete
            delete foundCache[i]._found;
        } catch(e) {
            foundCache[i].removeAttribute('_found');
        }
    }
    foundCache = [];
    YAHOO.log('getBySelector: done clearing foundCache');
};

var clearParentCache = function() {
    if (!document.documentElement.children) { // caching children lookups for gecko
        return function() {
            for (var i = 0, len = parentCache.length; i < len; ++i) {
                delete parentCache[i]._children;
            }
            parentCache = [];
        };
    } else return function() {}; // do nothing
}();

var getRegExp = function(str, flags) {
    flags = flags || '';
    if (!regexCache[str + flags]) {
        regexCache[str + flags] = new RegExp(str, flags);
    }
    return regexCache[str + flags];
};

var trim = function(str) {
    return str.replace(getRegExp(X.BEGIN + X.SP + X.OR + X.SP + X.END, 'g'), "");
};

var combinators = {
    ' ': function(node, token) {
        node = node.parentNode;
        while (node && node.tagName) {
            if (rTestNode(node, null, token.previous)) {
                return true;
            }
            node = node.parentNode;
        }  
        return false;
    },

    '>': function(node, token) {
        return rTestNode(node.parentNode, null, token.previous);
    },
    '+': function(node, token) {
        var sib = node.previousSibling;
        while (sib && sib.nodeType !== 1) {
            sib = sib.previousSibling;
        }

        if (sib && rTestNode(sib, null, token.previous)) {
            return true; 
        }
        return false;
    },

    '~': function(node, token) {
        var sib = node.previousSibling;
        while (sib) {
            if (sib.nodeType === 1 && rTestNode(sib, null, token.previous)) {
                return true;
            }
            sib = sib.previousSibling;
        }

        return false;
    }
};

var getChildren = function() {
    if (document.documentElement.children) { // document for capability test
        return function(node, tag) {
            return tag ? node.children.tags(tag) : node.children;
        };
    } else {
        return function(node, tag) {
            if (node._children) {
                return node._children;
            }
            var children = [],
                childNodes = node.childNodes;

            for (var i = 0, len = childNodes.length; i < len; ++i) {
                if (childNodes[i].tagName) {
                    if (!tag || childNodes[i].tagName.toLowerCase() === tag) {
                        children[children.length] = childNodes[i];
                    }
                }
            }
            node._children = children;
            parentCache[parentCache.length] = node;
            return children;
        };
    }
}();

/*
    an+b = get every _a_th node starting at the _b_th
    0n+b = no repeat ("0" and "n" may both be omitted (together) , e.g. "0n+1" or "1", not "0+1"), return only the _b_th element
    1n+b =  get every element starting from b ("1" may may be omitted, e.g. "1n+0" or "n+0" or "n")
    an+0 = get every _a_th element, "0" may be omitted 
*/
var getNth = function(node, expr, tag, reverse) {
    if (tag) tag = tag.toLowerCase();
    var re = regexCache[X.NTH_CHILD] = regexCache[X.NTH_CHILD] || new RegExp(X.NTH_CHILD);
    re.test(expr);
    var a = parseInt(RegExp.$1, 10), // include every _a_ elements (zero means no repeat, just first _a_)
        n = RegExp.$2, // "n"
        oddeven = RegExp.$3, // "odd" or "even"
        b = parseInt(RegExp.$4, 10) || 0, // start scan from element _b_
        result = [];

    if ( isNaN(a) ) {
        a = (n) ? 1 : 0;
    }

    if (oddeven) {
        a = 2; // always every other
        op = '+';
        n = 'n';
        b = (oddeven === 'odd') ? 1 : 0;
    }

    var siblings = getChildren(node.parentNode, tag);
    if (!siblings) {
        return false;
    }
    if (a === 0) { // just the first
        if (siblings[b - 1] === node) {
            return true;
        } else {
            return false;
        }
    }

    if (!reverse) {
        for (var i = b - 1, len = siblings.length; i < len; i += a) {
            if ( i >= 0 && siblings[i] === node ) {
                return true;
            }
        }
    } else {
        for (var i = siblings.length - b, len = siblings.length; i >= 0; i -= a) {
            if ( i < len && siblings[i] === node ) {
                return true;
            }
        }
    }
    return false;
};

var getId = function(attr) {
    for (var i = 0, len = attr.length; i < len; ++i) {
        if (attr[i][0] == 'id' && attr[i][1] === '=') {
            return attr[i][2];
        }
    }
};

var getIdTokenIndex = function(tokens) {
    for (var i = 0, len = tokens.length; i < len; ++i) {
        if (getId(tokens[i].attributes)) {
            return i;
        }
    }
    return -1;
};

var tokenize = function(selector) {
    if (!selector) return [];
        var token,
        tokens = [],
        m,
        aliases = Selector.attrAliases,
        attr,
        reAttr = getRegExp(X.ATTRIBUTES, 'g'),
        rePseudo = getRegExp(X.PSEUDO, 'g');

    selector = replaceShorthand(selector);
    // break selector into simple selector units
    while ( selector.length && getRegExp(X.SELECTOR).test(selector) ) {
        token = {
            previous: token,
            simple: RegExp.$1,
            tag: RegExp.$2.toLowerCase() || '*',
            predicate: RegExp.$3,
            attributes: [],
            pseudos: [],
            combinator: RegExp.$4
        };

        // Parse pseudos first, then strip from predicate to 
        // avoid false positive from :not.
        while (m = rePseudo.exec(token.predicate)) {
            token.predicate = token.predicate.replace(m[0], '');
            token.pseudos[token.pseudos.length] = m.slice(1);
        }
        
        while (m = reAttr.exec(token.predicate)) { // parse attributes
            if (aliases[m[1]]) { // convert reserved words, etc
                m[1] = aliases[m[1]];
            }
            attr = m.slice(1); // capture attribute tokens
            if (attr[1] === undefined) {
                attr[1] = ''; // test for existence if no operator
            }
            token.attributes[token.attributes.length] = attr;
        }
        
        token.id = getId(token.attributes);
        if (token.previous) {
            token.previous.combinator = token.previous.combinator || ' ';
        }
        tokens[tokens.length] = token;
        selector = trim(selector.substr(token.simple.length));
    } 
    return tokens;
};

var replaceShorthand = function(selector) {
    var shorthand = Selector.shorthand;
    var attrs = selector.match(getRegExp(X.CAPTURE_ATTRIBUTES, 'g')); // pull attributes to avoid false pos on "." and "#"
    if (attrs) {
        selector = selector.replace(getRegExp(X.CAPTURE_ATTRIBUTES, 'g'), 'REPLACED_ATTRIBUTE');
    }
    for (var re in shorthand) {
        selector = selector.replace(getRegExp(re, 'g'), shorthand[re]);
    }

    if (attrs)
        for (var i = 0, len = attrs.length; i < len; ++i) {
            selector = selector.replace('REPLACED_ATTRIBUTE', attrs[i]);
        }
    return selector;
};

Selector = new Selector();
Selector.CHARS = CHARS;
Selector.TOKENS = X;
Y.Selector = Selector;
})();
YAHOO.register("selector", YAHOO.util.Selector, {version: "2.4.1r2", build: "742"});
