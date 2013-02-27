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
        Y.all('.decaf-awesome-bar ul.dropdown li > span').each(this.enhanceAwesomeBar, this);
        Y.all('.decaf-awesome-bar ul.dropdown li.clickable-with-children > a').each(this.enhanceAwesomeBar, this);
    },
    enhanceAwesomeBar : function(menuitem) {
        var level = 0;
        try {
            level = menuitem.ancestors('ul')._nodes.length;
        } catch(x) {
            // Old version of YUI - no ancestors() method
            var temp = menuitem;
            while(temp) {
                level++;
                temp = temp.ancestor('ul');
            }
        }
        menuitem = menuitem.ancestor('li');
        menuitem.on('mouseover', function(e, item) {
            if(this.prev[level]) {
                window.clearTimeout(this.prev[level].hovertimer);
                this.prev[level].removeClass('extended-hover');
            }
            this.prev[level] = menuitem;
            if(item.hovertimer) window.clearTimeout(item.hovertimer);
            item.addClass('extended-hover');
            if(level >= 2) { // don't try shifting initial dropdown
                var submenu = menuitem.one('ul');
                if(submenu == null) return;
                submenu.setStyle('top', '');
                var winbottom = Y.one("body").get("winHeight");
                var scroll = document.documentElement.scrollTop || document.body.scrollTop;
                var bottom = submenu.getY() + submenu.get('clientHeight') - scroll;
                if(bottom >= winbottom) {
                    var top = (-1*(bottom-winbottom)-1);
                    submenu.maxTop = -1*submenu.getY();
                    submenu.setStyle('top', top+'px');
                    submenu.minTop = top;
                    if(submenu.scrollInterval) window.clearTimeout(submenu.scrollInterval);
                    submenu.scrollInterval = 0;
                    if(top < submenu.maxTop) { // Submenu is taller than the viewport
                        submenu.on('mouseover', function(e) {e.stopPropagation()});
                        submenu.on('mousemove', this.hover, this, submenu);
                        submenu.on('mouseout', function(e, submenu) {
                            if (submenu.contains(e.target)) return;
                            if ((e.target._node===submenu || e.target._node===submenu.parentNode) && submenu.scrollInterval) {
                                window.clearInterval(submenu.scrollInterval);
                                submenu.scrollInterval = 0;
                            }
                        }, this, submenu);
                    }
                }
            }
        }, this, menuitem);
        menuitem.on('mouseout', function(e, item) {
            if (item.hovertimer) window.clearTimeout(item.hovertimer);
            item.hovertimer = window.setTimeout(function(){item.removeClass('extended-hover')}, 750);
        }, this, menuitem);
    },
    hover : function(e, submenu) {
        var vpHeight = Y.one("body").get("winHeight");
        var self = this;
        e.stopPropagation();
        if (e.clientY < 50) {
            if (submenu.scrollInterval) return;
            submenu.scrollInterval = window.setInterval(function(){self.hoverScroll(submenu, 'up')}, 25);
        } else if (e.clientY > (vpHeight-50)) {
            if (submenu.scrollInterval) return;
            submenu.scrollInterval = window.setInterval(function(){self.hoverScroll(submenu, 'down')}, 25);
        } else if (submenu.scrollInterval) {
            window.clearInterval(submenu.scrollInterval);
            submenu.scrollInterval = 0;
        }
    },
    hoverScroll : function(submenu, direction) {
        var top = parseInt(submenu.getStyle('top'));
        if (direction === 'up') {
            submenu.setStyle('top', Math.min(submenu.maxTop, top+5)+'px');
        } else if (direction === 'down') {
            submenu.setStyle('top', Math.max(submenu.minTop, top-5)+'px');
        }
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