/**
 * First event binding function from chadmin.js
 */

// prevents internet explorer from selecting text
document.onselectstart = function() {
    return false;
};

document.observe('chart:drawn', function(e) {
    var toolbar = document.createElement('div');
    Charts.toolbar = toolbar;
    toolbar.className = 'toolbar';

    // firefox needs the 'return false' to cancel the delete
    document.onkeydown = function(e) {
        if (e.keyCode == Event.KEY_BACKSPACE && !document.editingBox && !document.editingTitle) {
            /* TODO something like this is needed for firefox on mac
            if (Prototype.Browser.Gecko && navigator.appVersion.indexOf("Mac") !=- 1) {
                document.location.href += '#';
            }
            */
            return false;
        }
    }.bindAsEventListener();

    document.observe('keydown', function(evt) {
        var captured = true;
        //deletion request
        if (evt.keyCode == Event.KEY_DELETE && Charts.selectedComponent && !document.editingBox && !document.editingTitle) {
            Charts.selectedComponent.remove();
            Charts.selectedComponent = null;
        }
        //clipboard request (cut, copy, paste, etc.)
        else if ((evt.ctrlKey || evt.metaKey) && Charts.selectedComponent && !document.editingBox && !document.editingTitle) {
            switch (evt.keyCode) {
                case 67: // ctrl+c
                    document.chClipboard = Charts.selectedComponent;
                    break;
                case 86: // ctrl+v
                    if (document.chClipboard) {
                        document.chClipboard.duplicate(Charts.redraw);
                    }
                    break;
                case 88: // ctrl+x
                    document.chClipboard = Charts.selectedComponent;
                    document.chClipboard.remove();
                    break;
                default:
                    captured = false;
            }
        } else if (evt.keyCode == 27 && Charts.selectedComponent) {
            Charts.selectedComponent.mUp();
        } else {
            captured = false;
        }

        if (captured) {
            Event.stop(evt);
        }
    });

    chColor.each(function(color) {
        var cButton = document.createElement('div');
        cButton.className = 'button color';
        cButton.color = color;
        cButton.style.backgroundColor = '#' + color;
        cButton.title = '#' + color;
        Event.observe(cButton, 'click', function(e) {
            if (Charts.selectedComponent && Charts.selectedComponent.setColor) {
                Charts.selectedComponent.setColor(color);
                Charts.redraw();
            }
            e.stop();
            return false;
        });
        toolbar.appendChild(cButton);
    });

    var clear = document.createElement('div');
    clear.style.clear = 'both';
    toolbar.appendChild(clear);

    var ddhelp = document.createElement('div');
    ddhelp.className = "tiny";
    ddhelp.innerHTML = "Select a box, line, or connection and click a color";
    toolbar.appendChild(ddhelp);

    Charts.toolbarContainer.appendChild(toolbar);

    // greybox overlay for tinymce (replaced fckeditor)

    Charts.editor = document.createElement('div');
    Charts.editor.className = "editorWindow";

    Charts.editor.myfck = document.createElement('textarea');
    Charts.editor.myfck.id = "mceBox";
    Charts.editor.myfck.name = "mceBox";
    Charts.editor.appendChild(Charts.editor.myfck);

    var okbtn = document.createElement('div');
    okbtn.className = "fckOK";
    okbtn.innerHTML = "OK";
    Charts.editor.appendChild(okbtn);
    var self = this;
    Event.observe(okbtn, 'mousedown', function() {
        Charts.insertFCKcontent(Charts);
    });

    // add the vertical page divider
    var pageVDivider = document.createElement('div');
    pageVDivider.className = 'chVDivider';
    pageVDivider.innerHTML = '';
    Charts.element.appendChild(pageVDivider);

    // add the horizontal page divider
    var pageHDivider = document.createElement('div');
    pageHDivider.className = 'chHDivider';
    pageHDivider.innerHTML = '';
    Charts.element.appendChild(pageHDivider);

    Charts.fck = {
        Config: {}
    }; //new FCKeditor("PathwaysEditor");

    Charts.element.observe('contextmenu', function(e) {
        var pointer = e.pointer();
        var position = Charts.positionWithin(pointer);

        var shape = Charts.getShapeContaining(position);
        if (shape && shape.widget) {
            Charts.contextMenuTarget = shape.widget;
        } else {
            Charts.contextMenuTarget = null;
        }

        var menu;

        if (Charts.contextMenuTarget) {
            switch (Charts.contextMenuTarget.type) {
                case 'box':
                    menu = ChartBox.contextMenu;
                    break;
                case 'circle':
                    menu = ChartCircle.contextMenu;
                    break;
                case 'line':
                    menu = widgetContextMenu;
                    break;
                case 'connection':
                    menu = Connection.contextMenu;
                    break;
            }
        } else {
            menu = Charts.contextMenu;
        }

        Charts.contextMenuPosition = {
            x: pointer.x,
            y: pointer.y
        };

        menu.cfg.setProperty('x', Charts.contextMenuPosition.x);
        menu.cfg.setProperty('y', Charts.contextMenuPosition.y);
        menu.show();

        e.stop();
    });

    Charts.element.observe('mousedown', function(e) {
        if (document.editingTitle || !e.isLeftClick()) {
            return;
        }

        var pointer = e.pointer();
        var position = Charts.positionWithin(pointer);

        Charts.activeControl = Charts.getControlPointContaining(position);
        Charts.mouseDownPosition = position;

        if (Charts.activeControl) {
            Charts.positionDeltas = Geometry.deltas(position, Charts.activeControl);
            e.stop();
            return;
        }
        var shape = Charts.getShapeContaining(position);

        if (!shape || (shape.widget && shape.widget != Charts.selectedComponent)) {
            if (shape) {
                Charts._select(shape.widget);

            } else {
                Charts._deselect();
            }

            Charts.redraw();
        }

        if (Charts.selectedComponent) {
            Charts.activeControl = Charts.selectedComponent;
            Charts.positionDeltas = Geometry.deltas(position, Charts.activeControl);
        }

        if (shape) {
            e.stop();
        }
    });

    // TODO should only be registered when selected
    Charts.element.observe('mousemove', function(e) {
        // if there is an active control and the control can be moved
        if (Charts.activeControl && Charts.activeControl.applyPosition) {
            Charts.controlReshaped = true;

            var position = Charts.positionWithin(e.pointer());
            var offsetPosition = Geometry.translatedPoint(position, Charts.positionDeltas);

            if (Charts.snapToGrid && !e.altKey) {
                offsetPosition.x = Charts.gridSize * Math.round(offsetPosition.x / Charts.gridSize);
                offsetPosition.y = Charts.gridSize * Math.round(offsetPosition.y / Charts.gridSize);
            }

            offsetPosition.x = Math.max(Charts.drawingArea.x, offsetPosition.x);
            offsetPosition.y = Math.max(Charts.drawingArea.y, offsetPosition.y);

            var maxX = Charts.drawingArea.bottomRight.x;
            var maxY = Charts.drawingArea.bottomRight.y;
            if (Charts.activeControl.getShape) {
                maxX -= Charts.activeControl.getShape().getBounds().width;
                maxY -= Charts.activeControl.getShape().getBounds().height;
            }

            offsetPosition.x = Math.min(maxX, offsetPosition.x);
            offsetPosition.y = Math.min(maxY, offsetPosition.y);

            Charts.activeControl.applyPosition(offsetPosition, e.shiftKey);
            Charts.activeControl.x = offsetPosition.x;
            Charts.activeControl.y = offsetPosition.y;
            Charts.redraw();
        }
    });

    Charts.element.observe('mouseup', function(e) {
        if (Charts.activeControl) {
            if (Charts.controlReshaped) {
                Charts.selectedComponent.onReshape();
            }
            Charts.activeControl = null;

            // the selected component could have moved, so update the control points
            Charts._select(Charts.selectedComponent);

            Charts.redraw();
        }

        Charts.controlReshaped = false;
    });

    Charts.drawingArea = new Geometry.Bounds([Geometry.ORIGIN, Geometry.translatedPoint(Geometry.ORIGIN, Charts.element.offsetWidth, Charts.element.offsetHeight)]);

    Charts.snapToGrid = true;
});
