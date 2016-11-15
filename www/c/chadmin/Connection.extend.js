
Connection.addMethods({
    changeThickness: function(thickness) {
        this.shape.style.lineWidth = thickness;
        this.onPropertyChange({
            'thickness': thickness
        });
    },
    changeLineDash: function(lineDashStyle) {
        this.shape.style.lineDashStyle = lineDashStyle;
        this.onPropertyChange({
            'lineDashStyle': lineDashStyle
        });
    },
    remove: function() {
        if (!Charts.confirmDelete('connection')) {
            return;
        }

        this.disconnect();

        Charts.redraw();
    },

    /** Disconnect this connection. */
    disconnect: function() {
        //remove from database
        chUtil.ajax({
            source_id: this.source.id,
            destination_id: this.destination.id,
            a: 'disconnect'
        });

        //remove from source and destination widgets
        this.source.unregisterOutgoingConnection(this);
        this.destination.unregisterIncomingConnection(this);

        Charts.unregisterComponent(this);
    },

    /** Sets the anchor point on the source widget. */
    anchorSource: function(anchorPoint) {
        this.sourceAnchorPoint = anchorPoint;
        this.onPropertyChange({
            'source_side': anchorPoint.side,
            'source_position': anchorPoint.position
        });
    },

    /** Sets the anchor point on the destination widget. */
    anchorDestination: function(anchorPoint) {
        this.destinationAnchorPoint = anchorPoint;
        this.onPropertyChange({
            'destination_side': anchorPoint.side,
            'destination_position': anchorPoint.position
        });
    },

    /** Sets the number of line segments used to draw the connection.
     *  Only values 1-3 are supported.
     */
    setNumSegments: function(numSegments) {
        this.numSegments = numSegments;
        this.onPropertyChange({
            'num_segments': numSegments
        });
    },

    /** Sets the axis of the line extending from the source anchor point.
     *  @param sourceAxis either 'x' or 'y'
     */
    setSourceAxis: function(sourceAxis) {
        this.sourceAxis = sourceAxis;
        this.onPropertyChange({
            'source_axis': sourceAxis
        });
    },

    setColor: function(color) {
        if (color !== 'transparent') {
            this.color = color;
            this.shape.setStyle('color', '#' + color);
            this.onPropertyChange({
                'color': color
            });
        }
    },

    autoposition: function() {
        var data = Connection.determineDefaultConnectionData(this.source, this.destination);
        this.sourceAnchorPoint = {
            side: data.source_side,
            position: data.source_position
        };
        this.destinationAnchorPoint = {
            side: data.destination_side,
            position: data.destination_position
        };
        this.sourceAxis = data.source_axis;
        this.numSegments = data.num_segments;
        this.onPropertyChange(data);
    },

    onPropertyChange: function(properties) {
        var data = {
            a: 'update',
            type: 'connection',
            id: this.id
        };
        for (key in properties) {
            data[key] = properties[key];
        }
        chUtil.ajax(data);

        this.reposition();
    },

    getControlPoints: function() {
        var startControlPoint = Object.clone(this.startPoint);
        startControlPoint.applyPosition = this.applyAnchorPointPosition.bind(this, this.source, this.sourceAnchorPoint, this.startPoint);
        startControlPoint.color = START_COLOR;

        if (this.numSegments != 1) {
            var endControlPoint = Object.clone(this.endPoint);
            endControlPoint.applyPosition = this.applyAnchorPointPosition.bind(this, this.destination, this.destinationAnchorPoint, this.endPoint);
            endControlPoint.color = END_COLOR;
            return [startControlPoint, endControlPoint];
        } else {
            return [startControlPoint];
        }
    },

    applyAnchorPointPosition: function(control, anchorPoint, point, position) {
        var translated = Geometry.translatedPoint(position, -control.getLeft(), -control.getTop());
        if (Side.isHorizontal(anchorPoint.side)) {
            anchorPoint.position = Math.max(0, Math.min(100, translated.x * 100 / control.getWidth()));
        } else {
            anchorPoint.position = Math.max(0, Math.min(100, translated.y * 100 / control.getHeight()));
        }
        this.reposition();
        position.x = point.x;
        position.y = point.y;
    },

    onReshape: function() {
        this.onPropertyChange({
            'source_side': this.sourceAnchorPoint.side,
            'source_position': this.sourceAnchorPoint.position,
            'destination_side': this.destinationAnchorPoint.side,
            'destination_position': this.destinationAnchorPoint.position
        });
    }
});
Side.isHorizontal = function(side) {
    return side == Side.TOP || side == Side.BOTTOM;
};

Connection.determineDefaultConnectionData = function(source, destination) {
    var up = source.getTop() - destination.getBottom();
    var down = destination.getTop() - source.getBottom();
    var left = source.getLeft() - destination.getRight();
    var right = destination.getLeft() - source.getRight();

    switch (Math.max(up, down, left, right)) {
        case up:
            data = {
                source_side: Side.TOP,
                destination_side: Side.BOTTOM,
                source_axis: 'y'
            };
            break;
        case down:
            data = {
                source_side: Side.BOTTOM,
                destination_side: Side.TOP,
                source_axis: 'y'
            };
            break;
        case right:
            data = {
                source_side: Side.RIGHT,
                destination_side: Side.LEFT,
                source_axis: 'x'
            };
            break;
        case left:
            data = {
                source_side: Side.LEFT,
                destination_side: Side.RIGHT,
                source_axis: 'x'
            };
            break;
    }

    data.num_segments = 1;
    data.source_position = 50;
    data.destination_position = 50;

    if (typeof source.config.color !== 'string' || source.config.color === 'transparent' || source.config.color.indexOf('rgba') > -1) {
        source.config.color = '000000';
    }
    data.color = source.config.color.replace('#', '');

    return data;
};
