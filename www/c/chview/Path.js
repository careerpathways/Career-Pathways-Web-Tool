
/* PATH
 ******************************************************************************/
var Path = Class.create(AbstractShape, {
    initialize: function(points, style) {
        this.setPoints(points);
        this.style = style;
    },

    /** Sets the points that comprise the path. */
    setPoints: function(points) {
        this.points = points;

        this.refreshPoints();
    },

    /** Refreshes the points if they have changed outside of the path. */
    refreshPoints: function() {
        var points = this.points;
        if (points.length > 0) {
            var previousPoint = points[0];

            var point;

            for (var i = 1, len = points.length; i < len; ++i) {
                point = points[i];
                point.delta = Geometry.deltas(previousPoint, point);
                point.length = Geometry.length(point.delta);
                point.theta = Math.atan2(point.delta.y, point.delta.x);

                previousPoint = point;
            }
        }

        this.bounds = new Geometry.Bounds(points, 6);
    },

    /** Returns the path's points. */
    getPoints: function() {
        return this.points;
    },

    draw: function(context) {
        var arrowheadAtEnd = this.style.arrowheadAtEnd;
        var arrowheadAtStart = this.style.arrowheadAtStart;
        context.lineWidth = parseInt(this.style.lineWidth) || 5; //lineWidth is stored in DB as "thickness"

        //default
        context.strokeStyle = context.fillStyle = this.style.color;


        switch (this.style.lineDashStyle) {
            case 'DashedShort':
                context.lineCap = 'square';
                //setLineDash only supported in IE 11+
                if (context.setLineDash) {
                    context.setLineDash([(.1) * context.lineWidth, (2) * context.lineWidth]);
                }
                break;
            case 'DashedLong':
                context.lineCap = 'square';
                if (context.setLineDash) {
                    context.setLineDash([(1) * context.lineWidth, (2) * context.lineWidth]);
                }
                break;
            default:
                context.lineCap = 'round';
                if (context.setLineDash) {
                    context.setLineDash([]);
                }
                break;
        }


        context.beginPath();

        var previousPoint = this.points[0];
        var point;

        context.save();
        if (context.lineWidth % 2 == 1) {
            context.translate(-.5, -.5);
        }

        context.moveTo(Math.round(previousPoint.x), Math.round(previousPoint.y));

        if (arrowheadAtEnd) {
            var arrowLength = context.lineWidth * ARROW_LENGTH_MULTIPLIER;
            var arrowThickness = context.lineWidth * ARROW_THICKNESS_MULTIPLIER;
        }

        var arrowheadThisSegment;

        for (var k = 1, len = this.points.length; k < len; ++k) {
            point = this.points[k];
            arrowheadThisSegment = (k == len - 1 && arrowheadAtEnd);

            var endPoint;
            if (arrowheadThisSegment) {
                endPoint = {
                    x: previousPoint.x + Math.cos(point.theta) * (point.length - arrowLength),
                    y: previousPoint.y + Math.sin(point.theta) * (point.length - arrowLength)
                }
            } else {
                endPoint = point;
            }
            context.lineTo(Math.round(endPoint.x), Math.round(endPoint.y));

            previousPoint = point;
        }

        context.stroke();

        if (arrowheadAtEnd) {
            context.translate(previousPoint.x, previousPoint.y);
            context.rotate(point.theta);
            context.translate(-previousPoint.x, -previousPoint.y);

            context.beginPath();
            context.moveTo(point.x - arrowLength, point.y - arrowThickness / 2);
            context.lineTo(point.x, point.y);
            context.lineTo(point.x - arrowLength, point.y + arrowThickness / 2);
            context.fill();
        }

        context.restore();
    },

    contains: function(point) {
        if (this.bounds.contains(point)) {
            // FIXME implement
        }
    },

    // TODO test
    intersects: function(line) {
        if (this.start.x == line.start.x && this.start.y == line.start.y ||
            this.start.x == line.end.y && this.start.y == line.end.y ||
            this.end.x == line.start.x && this.start.y == line.start.y ||
            this.end.x == line.end.y && this.start.y == line.end.y) {
            return false;
        }

        var a = this.start;
        var b = Geometry.translatedPoint(this.end, -a.x, -a.y);
        var c = Geometry.translatedPoint(line.start, -a.x, -a.y);
        var d = Geometry.translatedPoint(line.end, -a.x, -a.y);

        var cos = b.x / this.length;
        var sin = b.y / this.length;

        c = {
            x: c.x * cos + c.y * sin,
            y: c.y * cos - c.x * sin
        };

        d = {
            x: d.x * cos + d.y * sin,
            y: d.y * cos + d.x * sin
        };

        if (c.x < 0 && d.x < 0 || c.x > 0 && d.x > 0) {
            return false;
        }

        var position = d.x + (c.x - d.x) * d.y / (d.y - c.y);

        if (position < 0 || position > this.length) {
            return false;
        }
    }
});
