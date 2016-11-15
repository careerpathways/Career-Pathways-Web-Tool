/**
  Connection -- a linking between two chart widgets, which can be a
  composite of different view components
  {generic chUtil} source
  {generic chUtil} destination
*/
var Connection = Class.create(Component, {
    sourceAnchorPoint: AnchorPoint.TOP_LEFT,
    destinationAnchorPoint: AnchorPoint.TOP_LEFT,
    numSegments: 3,
    sourceAxis: 'x',

    getType: function() {
        return 'connection';
    },

    initialize: function(source, destination, data) {
        this.data = data;
        this.source = source;
        this.destination = destination;
        this.color = this.source.config.color;
        this.startPoint = {};
        this.endPoint = {};

        this.chart = Charts;

        if (data) {
            this.id = data.id;
            this.sourceAnchorPoint = {
                side: data.source_side,
                position: data.source_position
            };
            this.destinationAnchorPoint = {
                side: data.destination_side,
                position: data.destination_position
            };
            this.numSegments = parseInt(data.num_segments);
            this.sourceAxis = data.source_axis;
            this.color = data.color;
            this.thickness = data.thickness;
            this.lineDashStyle = data.lineDashStyle;
        }

        if (typeof this.color !== 'string' || this.color === 'transparent' || this.color.indexOf('rgba') > -1) {
            this.color = '000000';
        }
        //connections must be registered with thier respective boxes
        this.source.registerOutgoingConnection(this);
        this.destination.registerIncomingConnection(this);

        this.type = 'connection';
    },

    createShape: function() {
        return new Path(
            [Geometry.ORIGIN, Geometry.ORIGIN], {
                arrowheadAtEnd: true,
                lineWidth: this.thickness || 5,
                lineDashStyle: this.lineDashStyle || 5,
                color: '#' + this.color
            }
        );
    },

    reposition: function() {
        var startPoint = this.source.getAnchorPointPosition(this.sourceAnchorPoint);
        var endPoint = this.destination.getAnchorPointPosition(this.destinationAnchorPoint);
        var midPoint;

        if (this.numSegments < 3 && this.numSegments > 0) {
            if (this.sourceAxis == 'x') {
                midPoint = {
                    x: endPoint.x,
                    y: startPoint.y
                };
            } else {
                var midPoint = {
                    x: startPoint.x,
                    y: endPoint.y
                };
            }
        }

        var points;

        if (this.numSegments == 0) {
            points = [startPoint, endPoint];
        } else if (this.numSegments == 1) {
            points = [startPoint, midPoint];
        } else {
            if (this.numSegments == 2) {
                points = [startPoint, midPoint, endPoint];
            } else if (this.numSegments == 3) {
                if (this.sourceAxis == 'x') {
                    var jointPoint1 = {
                        x: (startPoint.x + endPoint.x) / 2,
                        y: startPoint.y
                    };
                    var jointPoint2 = {
                        x: jointPoint1.x,
                        y: endPoint.y
                    };
                } else {
                    var jointPoint1 = {
                        x: startPoint.x,
                        y: (startPoint.y + endPoint.y) / 2
                    };
                    var jointPoint2 = {
                        x: endPoint.x,
                        y: jointPoint1.y
                    };
                }
                points = [startPoint, jointPoint1, jointPoint2, endPoint];
            }
        }

        this.shape.setPoints(points);

        this.bounds = this.shape.getBounds();

        this.startPoint.x = startPoint.x;
        this.startPoint.y = startPoint.y;

        this.endPoint.x = endPoint.x;
        this.endPoint.y = endPoint.y;
    }
});
