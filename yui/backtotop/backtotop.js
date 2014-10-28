YUI.add('moodle-theme_decaf-backtotop', function(Y) {

/**
 * Back to Top button for Decaf.
 */
var BackToTop = function() {
    BackToTop.superclass.constructor.apply(this, arguments);
};
BackToTop.prototype = {
    initializer : function(config) {
        var self = this;

        Y.one(window).on('scroll', self.onscroll);

        // Run on page init in case we're already scrolled down.
        self.onscroll();
    },
    onscroll : function(e) {
        var node = Y.one('#back-to-top');

        if (Y.one('window').get('docScrollY') > Y.one('#page-content').getY()) {
            node.setStyle('display', 'block');
        } else {
            node.setStyle('display', 'none');
        }
    }
};
// Make this into a fully fledged YUI module.
Y.extend(BackToTop, Y.Base, BackToTop.prototype, {
    NAME : 'Decaf theme Back to Top button',
    ATTRS : {
        // No attributes at present.
    }
});

M.theme_decaf = M.theme_decaf || {};
M.theme_decaf.initBackToTop = function(cfg) {
    return new BackToTop(cfg);
}

}, '@VERSION@', {requires:['base','node']});