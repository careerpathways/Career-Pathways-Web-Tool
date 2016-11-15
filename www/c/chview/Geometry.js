
/* GEOMETRY
 ******************************************************************************/
var Geometry = {
    ORIGIN: {
        x: 0,
        y: 0
    },

    bounds: function(a, b) {
        var points = $A(arguments);
        return new Geometry.Bounds(points);
    },

    /** A rectangle that surrounds a set of points. */
    Bounds: Class.create({
        /** Creates a boundary object for a set of points, with an optional
         *  fudge factor for containment checking.
         */
        initialize: function(points, fudge) {
            this.fudge = fudge || 0;
            var xCoords = points.pluck('x');
            var yCoords = points.pluck('y');

            this.left = this.x = Math.minArray(xCoords);
            this.top = this.y = Math.minArray(yCoords);

            this.topLeft = {
                x: this.x,
                y: this.y
            }

            this.right = Math.maxArray(xCoords);
            this.bottom = Math.maxArray(yCoords);

            this.bottomRight = {
                x: this.right,
                y: this.bottom
            }

            this.width = this.right - this.left;
            this.height = this.bottom - this.top;
        },

        /** Returns whether the point is within the fudge factor of the bounds. */
        contains: function(point) {
            if (this.fudge <= 0) {
                return Geometry.gte(point, this.topLeft) && Geometry.lte(point, this.bottomRight);
            } else {
                return Geometry.gte(point, Geometry.translatedPoint(this.topLeft, -this.fudge, -this.fudge)) && Geometry.lte(point, Geometry.translatedPoint(this.bottomRight, this.fudge, this.fudge));
            }
        },

        /** Returns a new bounds the includes the current bounds and the parameter. */
        compound: function(that) {
            return new Geometry.Bounds([
                this.topLeft,
                that.topLeft,
                this.bottomRight,
                that.bottomRight
            ]);
        },

        /** Returns a new bounds that include the current points and the new points. */
        expanded: function(points) {
            return new Geometry.Bounds(points).compound(this);
        },

        /** Returns the top left and bottom right points of the boundary. */
        getPoints: function() {
            return [this.topLeft, this.bottomRight];
        }
    }),

    /** Returns the difference between the start and and end points, as an x,y tuple. */
    deltas: function(start, end) {
        return {
            x: end.x - start.x,
            y: end.y - start.y
        };
    },

    /** Returns the distance from the point to the origin. */
    length: function(delta) {
        return Math.sqrt(delta.x * delta.x + delta.y * delta.y);
    },

    /** Returns the distance between two points. */
    abs: function(a, b) {
        return Geometry.length(Geometry.deltas(a, b));
    },

    /** Returns a point translated by another point or x and y amounts. */
    translatedPoint: function(point) {
        if (arguments.length == 3) {
            return {
                x: point.x + arguments[1],
                y: point.y + arguments[2]
            }
        } else {
            return {
                x: point.x + arguments[1].x,
                y: point.y + arguments[1].y
            }
        }
    },

    /** Returns a point scaled by a factor. */
    scaledPoint: function(point, factor) {
        return {
            x: point.x * factor,
            y: point.y * factor
        };
    },

    /** Returns whether the first argument is positioned the same or to the
     *  bottom right of the second argument.
     */
    gte: function(lhs, rhs) {
        return lhs.x >= rhs.x && lhs.y >= rhs.y;
    },

    /** Returns whether the first argument is positioned the same or to the
     *  top left of the second argument.
     */
    lte: function(lhs, rhs) {
        return lhs.x <= rhs.x && lhs.y <= rhs.y;
    }
};
