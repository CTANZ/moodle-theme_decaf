YUI.add('moodle-theme_decaf-awesomebar', function(Y) {

/**
 * Splash theme colour switcher class.
 * Initialise this class by calling M.theme_splash.init
 */
var AwesomeBar = function() {
    AwesomeBar.superclass.constructor.apply(this, arguments);
};
AwesomeBar.prototype = {
    prev : [],
    initializer : function(config) {
        Y.all('.decaf-awesome-bar ul.dropdown li span').each(this.enhanceAwesomeBar, this);
        Y.all('.decaf-awesome-bar ul.dropdown li.clickable-with-children a').each(this.enhanceAwesomeBar, this);
    },
    enhanceAwesomeBar : function(menuitem) {
        var level = menuitem.ancestors('ul')._nodes.length;
        menuitem = menuitem.ancestor('li');
        menuitem.on('mouseover', function(e, item) {
            if(this.prev[level]) {
                window.clearTimeout(this.prev[level].hovertimer);
                this.prev[level].removeClass('extended-hover');
            }
            this.prev[level] = menuitem;
            if(item.hovertimer) window.clearTimeout(item.hovertimer);
            item.addClass('extended-hover');
        }, this, menuitem);
        menuitem.on('mouseout', function(e, item) {
            item.hovertimer = window.setTimeout(function(){item.removeClass('extended-hover')}, 500);
        }, this, menuitem);
    }
};
// Make the AwesomeBar enhancer a fully fledged YUI module
Y.extend(AwesomeBar, Y.Base, AwesomeBar.prototype, {
    NAME : 'Decaf theme AwesomeBar enhancer',
    ATTRS : {
        // No attributes at present
    }
});
// Our splash theme namespace
M.theme_decaf = M.theme_decaf || {};
// Initialisation function for the AwesomeBar enhancer
M.theme_decaf.initAwesomeBar = function(cfg) {
    return new AwesomeBar(cfg);
}

}, '@VERSION@', {requires:['base','node']});