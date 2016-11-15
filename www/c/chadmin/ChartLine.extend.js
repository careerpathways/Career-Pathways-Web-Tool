ChartLine.addMethods(WidgetAdmin);
ChartLine.DEFAULT_OPTIONS = {
    startPoint: {
        x: 100,
        y: 100
    },
    endPoint: {
        x: 200,
        y: 200
    }
};

ChartLine.addMethods({
    getControlPoints: function() {
        var startControlPoint = Object.clone(this.getStartPoint());
        startControlPoint.applyPosition = this.applyPointPosition.bind(this, this.startPoint, this.endPoint);
        startControlPoint.color = START_COLOR;

        var endControlPoint = Object.clone(this.getEndPoint());
        endControlPoint.applyPosition = this.applyPointPosition.bind(this, this.endPoint, this.startPoint);
        endControlPoint.color = END_COLOR;
        return [
            startControlPoint,
            endControlPoint
        ];
    },
    applyPointPosition: function(point, otherPoint, position, constrain) {
        if (constrain) {
            var deltas = Geometry.deltas(position, otherPoint);
            if (Math.abs(deltas.x) < Math.abs(deltas.y)) {
                position.x = otherPoint.x;
            } else {
                position.y = otherPoint.y;
            }
        }
        point.x = position.x;
        point.y = position.y;
        this.reposition();
    },
    duplicate: function(callback) {
        return Charts.createComponent(ChartLine, {
            startPoint: Geometry.translatedPoint(this.startPoint, 0, 30),
            endPoint: Geometry.translatedPoint(this.endPoint, 0, 30),
            arrowheadAtEnd: this.arrowheadAtEnd,
            config: {
                color: this.config.color
            }
        }, callback);
    },
    onReshape: function() {
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                startPoint: {
                    x: this.startPoint.x,
                    y: this.startPoint.y
                },
                endPoint: {
                    x: this.endPoint.x,
                    y: this.endPoint.y
                }
            }
        });
    },
    changeLineDash: function(lineDashStyle) {
        this.shape.style.lineDashStyle = lineDashStyle;
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    lineDashStyle: lineDashStyle
                }
            }
        });
    },
    changeThickness: function(thickness) {
        this.shape.style.lineWidth = thickness;
        chUtil.ajax({
            id: this.id,
            a: 'update',
            content: {
                config: {
                    thickness: thickness
                }
            }
        });
    },
    applyPosition: function(position) {
        var deltas = Geometry.deltas(this, position);
        this.startPoint = Geometry.translatedPoint(this.startPoint, deltas);;
        this.endPoint = Geometry.translatedPoint(this.endPoint, deltas);

        this.reposition();
    }
});
