/***
 basic circle functions
***/
ChartCircle = Class.create(Widget, {
    getType: function() {
        return 'circle';
    },

    elem: null,

    getElem: function() {
        /* NOTE: putting the class in the constructor, i.e.
         * new Element('div', {'class': 'ctepathwaysCircle'}); doesn't work in IE
         * 8 with Prototype earlier than v1.6.1. See
         * https://prototype.lighthouseapp.com/projects/8886/tickets/529 */
        this.elem = new Element('div').addClassName('ctepathwaysCircle');

        if (this.w > 0) this.elem.style.width = (this.getWidth() - 20) + 'px';

        this.titleElement = new Element('div').addClassName('ctepathwaysCircleTitle');
        this.contentElement = new Element('div').addClassName('ctepathwaysCircleContent');

        //this.elem.appendChild(this.titleElement);
        this.elem.appendChild(this.contentElement);

        this.titleElement.update(this.config.title || '&nbsp;');
        this.contentElement.update(this.config.content_html);
        this.titleElement.style.color = '#' + this.config.color_title || '#ffffff';

        this.elem.style.zIndex = "0";
        return this.elem;
    },

    createShape: function() {
        this.borderThickness = 5;
        this.innerCircle = new Circle({
            x: 0,
            y: 0
        }, 0, 0, {
            fillColor: '#' + this.config.color_background,
            fill: true,
            bottomLeftRadius: this.borderThickness,
            bottomRightRadius: this.borderThickness
        });
        this.outerCircle = new Circle({
            x: 0,
            y: 0
        }, 0, 0, {
            fillColor: '#' + this.config.color,
            strokeColor: '#F00000',
            fill: true,
            strokeWidth: 2,
            radius: this.borderThickness * 2
        });

        return new CompoundShape([this.outerCircle, this.innerCircle]);
    },

    getLayer: function() {
        return 1;
    },

    getHeight: function() {
        return this.height;
    },

    /** Returns the position of the anchor point. */
    getAnchorPointPosition: function(anchorPoint) {
        var result;

        switch (anchorPoint.side) {
            case Side.TOP:
                result = {
                    y: this.getTop()
                };
                break;
            case Side.BOTTOM:
                result = {
                    y: this.getBottom()
                };
                break;
            case Side.LEFT:
                result = {
                    x: this.getLeft()
                };
                break;
            case Side.RIGHT:
                result = {
                    x: this.getRight()
                };
                break;
        }

        switch (anchorPoint.side) {
            case Side.TOP:
            case Side.BOTTOM:
                result.x = this.getLeft() + this.getWidth() * anchorPoint.position / 100;
                break;
            case Side.LEFT:
            case Side.RIGHT:
                result.y = this.getTop() + this.getHeight() * anchorPoint.position / 100;
                break;
        }

        return result;
    },

    repositionElement: function() {
        this.elem.style.top = (this.getTop()) * Charts.textSizeMultiplier + 'px';
        this.elem.style.left = (this.getLeft() + 10) * Charts.textSizeMultiplier + 'px';
        this.elem.style.width = (this.getWidth() - 20) * Charts.textSizeMultiplier + 'px';
        var titlePadding = 4 * Charts.textSizeMultiplier + 'px';
        this.titleElement.style.paddingTop = titlePadding;
        this.titleElement.style.paddingBottom = titlePadding;
    },

    _onContentChange: function() {
        this.titleHeight = this.titleElement.offsetHeight / Charts.textSizeMultiplier;
        this.contentHeight = this.contentElement.offsetHeight / Charts.textSizeMultiplier;
        this.height = this.titleHeight + this.contentHeight + 10;
    },

    reposition: function() {
        var posOuter = {
            x: this.getLeft(),
            y: this.getTop()
        };

        var thickness = this.borderThickness;
        this.outerCircle.reposition(posOuter, this.getWidth(), this.getHeight());

        var posInner = {
            x: this.getLeft() + thickness,
            y: this.getTop() + thickness
        };

        this.innerCircle.reposition(
            posInner,
            this.getWidth() - thickness * 2,
            this.getHeight() - (thickness * 2) - this.titleHeight
        );

        this.innerCircle.setStyles({
            bottomLeftRadius: this.borderThickness,
            bottomRightRadius: this.borderThickness
        });

        this.outerCircle.setStyle('radius', thickness * 2);

        this.shape.recalculateBounds();

        this.getConnections().invoke('reposition');
    }
});
