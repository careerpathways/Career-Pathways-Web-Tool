
ChartLine = Class.create(Widget, {
    getType: function() {
        return 'line';
    },

    setup: function() {
        var isVertical = this.config.direction && (this.config.direction == VERTICAL || this.config.direction == 'n' || this.config.direction == 's');
        var isOldVersion = false;
        if (!this.startPoint) {
            var a = {
                x: this.getLeft() + (isVertical ? 7 : 0),
                y: this.getTop() + (isVertical ? 0 : 7)
            };
            var b = Geometry.translatedPoint(a, isVertical ? 0 : this.getWidth(), isVertical ? this.getHeight() : 0);

            if (this.config.direction == 'n' || this.config.direction == 'w') {
                this.startPoint = b;
                this.endPoint = a;
            } else {
                this.startPoint = a;
                this.endPoint = b;
            }
        } else {
            this.startPoint = Widget.toIntegerPoint(this.startPoint);
            this.endPoint = Widget.toIntegerPoint(this.endPoint);
        }
    },

    getStartPoint: function() {
        return this.startPoint;
    },

    getEndPoint: function() {
        return this.endPoint;
    },

    createShape: function() {
        return new Path(
            [Geometry.ORIGIN, Geometry.ORIGIN], {
                color: '#' + this.config.color,
                lineWidth: this.config.thickness || 5,
                lineDashStyle: this.config.lineDashStyle || 'solid',
                arrowheadAtEnd: this.arrowheadAtEnd
            }
        );
    },

    reposition: function() {
        this.shape.setPoints([
            this.getStartPoint(),
            this.getEndPoint()
        ]);

        var bounds = this.shape.getBounds();
        this.x = bounds.x;
        this.y = bounds.y;
    }
});

ChartLine.THICKNESS = 3;
