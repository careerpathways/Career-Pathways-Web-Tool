

/* ============================== Anchor/Connection Menus =========================== */
/** Convenience function to add menu items for both ends of connection. */
var addAnchorPointMenuItems = function(menu) {
    menu.addItems([
        [{
            text: 'Top',
            onclick: {
                fn: onAnchorPointSelect,
                scope: menu,
                obj: AnchorPoint.TOP_CENTER
            }
        }, {
            text: 'Bottom',
            onclick: {
                fn: onAnchorPointSelect,
                scope: menu,
                obj: AnchorPoint.BOTTOM_CENTER
            }
        }],
        [{
            text: 'Left',
            onclick: {
                fn: onAnchorPointSelect,
                scope: menu,
                obj: AnchorPoint.MIDDLE_LEFT
            }
        }, {
            text: 'Right',
            onclick: {
                fn: onAnchorPointSelect,
                scope: menu,
                obj: AnchorPoint.MIDDLE_RIGHT
            }
        }, ]
    ]);

    menu.subscribe('show', function() {
        var connection = Charts.contextMenuTarget;

        var anchorPoint = menu == anchorSourceMenu ? connection.sourceAnchorPoint : connection.destinationAnchorPoint;

        menu.getItems().each(function(item) {
            var obj = item.cfg.getProperty('onclick').obj;
            item.cfg.setProperty('checked', obj.side == anchorPoint.side && obj.position == anchorPoint.position);
        });
    });
};


// create the source and destination anchor point menus
var anchorSourceMenu = new YAHOO.widget.Menu('anchorSourceMenu');
addAnchorPointMenuItems(anchorSourceMenu);

var anchorDestinationMenu = new YAHOO.widget.Menu('anchorDestinationMenu');
addAnchorPointMenuItems(anchorDestinationMenu);

// create the connection source axis menu
var sourceAxisMenu = new YAHOO.widget.Menu('sourceAxisMenu');
sourceAxisMenu.addItems([{
    text: 'Horizontal',
    onclick: {
        fn: onSourceAxisSelect,
        obj: 'x'
    }
}, {
    text: 'Vertical',
    onclick: {
        fn: onSourceAxisSelect,
        obj: 'y'
    }
}]);

sourceAxisMenu.subscribe('show', function() {
    selectMenuItemWithOnclickObj(sourceAxisMenu, Charts.contextMenuTarget.sourceAxis);
});

// create number of connection segments menu
var numSegmentsMenu = new YAHOO.widget.Menu('numSegmentsMenu');
numSegmentsMenu.addItems([
    [{
        text: '1-Seg Line (Straight)',
        onclick: {
            fn: onNumSegmentsSelect,
            obj: 1
        }
    }, {
        text: '1-Seg Line (Diagonal)',
        onclick: {
            fn: onNumSegmentsSelect,
            obj: 0
        }
    }, {
        text: '2-Seg Line ("L")',
        onclick: {
            fn: onNumSegmentsSelect,
            obj: 2
        }
    }, {
        text: '3-Seg Line',
        onclick: {
            fn: onNumSegmentsSelect,
            obj: 3
        }
    }]
]);

numSegmentsMenu.subscribe('show', function() {
    selectMenuItemWithOnclickObj(numSegmentsMenu, Charts.contextMenuTarget.numSegments);
});

var connectionColorMenu = new YAHOO.widget.Menu('connectionColorMenu');
chColor.each(function(color) {
    connectionColorMenu.addItem({
        text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorSelect,
            obj: color,
            scope: connectionColorMenu
        }
    });
});

var sourceAxisMenuItem = new YAHOO.widget.MenuItem('Orientation', {
    submenu: sourceAxisMenu
});

var connectionThicknessMenuA = new YAHOO.widget.Menu('connectionThicknessMenuA');
connectionThicknessMenuA.addItem({
    text: 'Light (default)',
    onclick: {
        fn: changeThickness,
        obj: 5
    }
});
connectionThicknessMenuA.addItem({
    text: 'Medium',
    onclick: {
        fn: changeThickness,
        obj: 20
    }
});
connectionThicknessMenuA.addItem({
    text: 'Heavy',
    onclick: {
        fn: changeThickness,
        obj: 35
    }
});
var connectionLineStyleMenuA = new YAHOO.widget.Menu('connectionLineStyleMenuA');
connectionLineStyleMenuA.addItem({
    text: 'Solid (default)',
    onclick: {
        fn: changeLineDash,
        obj: "Solid"
    }
});
connectionLineStyleMenuA.addItem({
    text: 'Dashed - Short',
    onclick: {
        fn: changeLineDash,
        obj: "DashedShort"
    }
});
connectionLineStyleMenuA.addItem({
    text: 'Dashed - Long',
    onclick: {
        fn: changeLineDash,
        obj: "DashedLong"
    }
});
// create the connection context menu
Connection.contextMenu = new YAHOO.widget.ContextMenu('connectionContextMenu');
Connection.contextMenu.addItems([
    [{
            text: 'Start Point',
            submenu: anchorSourceMenu
        }, {
            text: 'End Point',
            submenu: anchorDestinationMenu
        },
        sourceAxisMenuItem, {
            text: 'Segments',
            submenu: numSegmentsMenu
        }, {
            text: 'Color',
            submenu: connectionColorMenu
        }, {
            text: 'Thickness',
            submenu: connectionThicknessMenuA
        }, {
            text: 'Line Style',
            submenu: connectionLineStyleMenuA
        }, {
            text: 'Auto Position',
            onclick: {
                fn: onAutopositionSelect
            }
        }
    ],
    [{
        text: 'Delete',
        onclick: {
            fn: onDeleteSelect
        }
    }]
]);

Connection.contextMenu.subscribe('show', function() {
    // disable the source axis menu if using direct line
    sourceAxisMenuItem.cfg.setProperty('disabled', Charts.contextMenuTarget.numSegments == 0);
});



//TODO move this to a more appropriate spot
var widgetColorMenu = new YAHOO.widget.Menu('widgetColorMenu');
chColor.each(function(color) {
    widgetColorMenu.addItem({
        text: '<span style="background-color: #' + color + '">&nbsp;&nbsp;&nbsp;&nbsp;</span>',
        onclick: {
            fn: onColorSelect,
            obj: color,
            scope: widgetColorMenu
        }
    });
});

var connectionThicknessMenuB = new YAHOO.widget.Menu('connectionThicknessMenuB');
connectionThicknessMenuB.addItem({
    text: 'Light (default)',
    onclick: {
        fn: changeThickness,
        obj: 5
    }
});
connectionThicknessMenuB.addItem({
    text: 'Medium',
    onclick: {
        fn: changeThickness,
        obj: 20
    }
});
connectionThicknessMenuB.addItem({
    text: 'Heavy',
    onclick: {
        fn: changeThickness,
        obj: 35
    }
});
var connectionLineStyleMenuB = new YAHOO.widget.Menu('connectionLineStyleMenuB');
connectionLineStyleMenuB.addItem({
    text: 'Solid',
    onclick: {
        fn: changeLineDash,
        obj: "Solid"
    }
});
connectionLineStyleMenuB.addItem({
    text: 'Dashed - Short',
    onclick: {
        fn: changeLineDash,
        obj: "DashedShort"
    }
});
connectionLineStyleMenuB.addItem({
    text: 'Dashed - Long',
    onclick: {
        fn: changeLineDash,
        obj: "DashedLong"
    }
});



var widgetContextMenu = new YAHOO.widget.ContextMenu('widgetContextMenu');
widgetContextMenu.addItems([
    [{
        text: 'Color',
        submenu: widgetColorMenu
    }, {
        text: 'Thickness',
        submenu: connectionThicknessMenuB
    }, {
        text: 'Line Style',
        submenu: connectionLineStyleMenuB
    }, {
        text: 'Duplicate',
        onclick: {
            fn: onDuplicateSelect
        }
    }, ],
    [{
        text: 'Delete',
        onclick: {
            fn: onDeleteSelect
        }
    }]
]);
