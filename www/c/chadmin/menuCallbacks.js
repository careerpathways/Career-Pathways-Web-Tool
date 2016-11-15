
var selectMenuItemWithOnclickObj = function(menu, value) {
    menu.getItems().each(function(item) {
        item.cfg.setProperty('checked', item.cfg.getProperty('onclick').obj == value);
    });
};

/** Called when the Edit Title menu item is chosen
 *  from the box context menu.
 */
var onEditTitleSelect = function() {
    Charts.contextMenuTarget.changeTitle();
};

var onEditTitleColorSelect = function(type, args, value) {
    Charts.contextMenuTarget.changeTitleColor(value);
    Charts.redraw();
};

/** Called when the Edit Content menu item is chosen
 *  from the box context menu.
 */
var onEditContentSelect = function() {
    Charts.contextMenuTarget.changeContent();
};

/** Called when the Link To menu item is chosen
 *  from the box context menu.
 */
var onLinkToSelect = function() {
    Charts.contextMenuTarget.connect();
    Charts.redraw();
};

/** Called when a program menu item is chosen
 *  from the box context menu.
 *  @param value the id of the selected program
 */
var onProgramSelect = function(type, args, value) {
    Charts.contextMenuTarget.setProgram(value);
    Charts.redraw();
};

/** Called then the delete menu item is chosen
 *  from the component context menu.
 */
var onDeleteSelect = function() {
    Charts.contextMenuTarget.remove();
};

/** Called when an anchor point is chosen from
 *  the connection context menu.
 */
var onAnchorPointSelect = function(type, args, value) {
    if (this === anchorSourceMenu) {
        Charts.contextMenuTarget.anchorSource(value);
    } else {
        Charts.contextMenuTarget.anchorDestination(value);
    }
    Charts.redraw();
};

/** Called when a source axis is chosen from
 *  the connection context menu.
 */
var onSourceAxisSelect = function(type, args, value) {
    Charts.contextMenuTarget.setSourceAxis(value);
    Charts.redraw();
};

/** Called when a number of segments is chosen from
 *  the connection context menu.
 */
var onNumSegmentsSelect = function(type, args, value) {
    Charts.contextMenuTarget.setNumSegments(value);
    Charts.redraw();
};
var onAutopositionSelect = function() {
    Charts.contextMenuTarget.autoposition();
    Charts.redraw();
};
var onColorSelect = function(type, args, value) {
    Charts.contextMenuTarget.setColor(value);
    Charts.redraw();
};
var onColorBackgroundSelect = function(type, args, value) {
    Charts.contextMenuTarget.setColorBackground(value);
    Charts.redraw();
};
var onDuplicateSelect = function() {
    Charts.contextMenuTarget.duplicate(Charts.redraw);
};
var changeThickness = function(type, args, value) {
    Charts.contextMenuTarget.changeThickness(value);
    Charts.redraw();
};
var changeLineDash = function(type, args, value) {
    Charts.contextMenuTarget.changeLineDash(value);
    Charts.redraw();
};
var onSnapToGridSelect = function() {
    Charts.snapToGrid = !Charts.snapToGrid;
};
var onDrawGridSelect = function() {
    Charts.drawGrid = !Charts.drawGrid;
    Charts.redraw();
};
var onNewLineSelect = function(type, args, value) {
    var options = value || {};
    var position = Charts.positionWithin(Charts.contextMenuPosition);

    Object.extend(options, {
        startPoint: {
            x: position.x,
            y: position.y
        },
        endPoint: {
            x: position.x + 100,
            y: position.y + 100
        }
    });

    Charts.createComponent(ChartLine, options, Charts.redraw.bind(Charts));
};
var onNewBoxSelect = function() {
    var position = Charts.positionWithin(Charts.contextMenuPosition);
    Charts.createComponent(ChartBox, {
        x: position.x,
        y: position.y
    }, Charts.redraw.bind(Charts));
}
var onNewCircleSelect = function() {
    var position = Charts.positionWithin(Charts.contextMenuPosition);
    Charts.createComponent(ChartCircle, {
        x: position.x,
        y: position.y
    }, Charts.redraw.bind(Charts));
}

var onGridSizeSelect = function() {
    var gridSize = prompt('Edit grid size', Charts.gridSize);
    if (gridSize != null) {
        Charts.gridSize = Math.max(2, parseInt(gridSize));

        if (Charts.drawGrid) {
            Charts.redraw();
        }
    }
};
