
/* every menu item created should do nothing when clicked */
// TODO there should be a better way to do this.
YAHOO.widget.MenuItem.prototype.init_old = YAHOO.widget.MenuItem.prototype.init;
YAHOO.widget.MenuItem.prototype.init = function(p_oObject, p_oConfig) {
    p_oConfig.url = 'javascript:Prototype.emptyFunction()';
    YAHOO.widget.MenuItem.prototype.init_old.apply(this, arguments);
}

var typeMenu = new YAHOO.widget.Menu('typeMenu');
types.each(function(type) {
    typeMenu.addItem({
        text: type.description,
        onclick: {
            fn: onProgramSelect,
            obj: type.id
        }
    });
});
typeMenu.subscribe('show', function() {
    var box = Charts.contextMenuTarget;
    selectMenuItemWithOnclickObj(typeMenu, box.config.program);
});
