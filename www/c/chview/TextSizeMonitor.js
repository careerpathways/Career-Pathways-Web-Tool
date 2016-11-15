/* TEXT SIZE MONITOR
 ******************************************************************************/

var TextSizeMonitor = Class.create({
    initialize: function(parentElement) {
        this.parentElement = $(parentElement);

        this.element = document.createElement('span');
        this.element.id = 'textSizeMonitor' + TextSizeMonitor.index++;
        this.element.innerHTML = '&nbsp;';
        this.element.style.position = 'absolute';
        this.element.style.left = '-10000px';

        this.parentElement.insertBefore(this.element, this.parentElement.firstChild);
        this.currentSize = this.getSize();
        this.baseSize = this.currentSize;
    },

    start: function() {
        if (!this.interval) {
            this.interval = window.setInterval(this._check.bind(this), TextSizeMonitor.DELAY);
        }
    },

    stop: function() {
        if (this.interval) {
            window.clearInterval(this.interval);
            this.interval = null;
        }
    },

    getBaseSize: function() {
        return this.baseSize;
    },

    getSize: function() {
        return this.element.offsetHeight;
    },

    _check: function() {
        var newSize = this.getSize();
        if (newSize !== this.currentSize) {
            var previousSize = this.currentSize;
            this.currentSize = newSize;
            this.parentElement.fire('text:resized', {
                previousSize: previousSize,
                currentSize: newSize,
                baseSize: this.baseSize,
                monitor: this
            });
        }
    }
});

TextSizeMonitor.index = 1;
TextSizeMonitor.DELAY = 500;
